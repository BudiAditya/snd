<?php
class AwalCasController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userAccYear;
    private $userAccMonth;

	protected function Initialize() {
		require_once(MODEL . "tvd/awalcas.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userAccYear = $this->persistence->LoadState("acc_year");
        $this->userAccMonth = $this->persistence->LoadState("acc_month");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        //$settings["columns"][] = array("name" => "a.cabang_code", "display" => "Lokasi", "width" => 70);
		$settings["columns"][] = array("name" => "a.wh_code", "display" => "Gudang", "width" => 80);
        //$settings["columns"][] = array("name" => "a.op_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 100);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 250);
        $settings["columns"][] = array("name" => "a.s_uom_code", "display" => "Satuan", "width" => 50);
        $settings["columns"][] = array("name" => "a.op_qty", "display" => "QTY", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.hpp,2)", "display" => "HPP", "width" => 80, "align" => "right");

		$settings["filters"][] = array("name" => "a.cabang_code", "display" => "Cabang");
		$settings["filters"][] = array("name" => "a.wh_code", "display" => "Gudang");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Stock Awal Barang Castrol ".$this->userAccYear;

			if ($acl->CheckUserAccess("tvd.awalcas", "add")) {
				$settings["actions"][] = array("Text" => "Create", "Url" => "tvd.awalcas/create", "Class" => "bt_edit", "ReqId" => 0,"Confirm" => "Buat Stock Awal Castrol?");
                $settings["actions"][] = array("Text" => "Add", "Url" => "tvd.awalcas/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("tvd.awalcas", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "tvd.awalcas/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih awalcas terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu awalcas.",
					"Confirm" => "Apakah anda mau menghapus data awalcas yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_cas_ic_saldoawal AS a ";
            $settings["where"] = "a.company_id = ".$this->userCompanyId." And Year(a.op_date) = ".$this->userAccYear;
            $settings["order_by"] = "a.wh_code, a.item_code";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(AwalCas $awal) {

		return true;
	}

	public function add() {
	    require_once (MODEL . "master/warehouse.php");
        require_once (MODEL . "inventory/items.php");
	    $awal = new AwalCas();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $awal->WarehouseId = $this->GetPostValue("WarehouseId");
            $awal->OpDate = $this->GetPostValue("OpDate");
            $awal->ItemId = $this->GetPostValue("ItemId");
            $awal->OpQty = $this->GetPostValue("OpQty");
            $awal->Hpp = $this->GetPostValue("Hpp");
            if ($this->ValidateData($awal)) {
                $awal->CreatebyId = $this->userUid;
                $flagSuccess = true;
                $this->connector->BeginTransaction();
                $awal->TrxNo = $awal->GetTrxNo($this->userCabangId);
                $rs = $awal->Insert();
                if ($rs > 0) {
                    //simpan ke master stock
                    $items = new Items($awal->ItemId);
                    if ($items != null) {
                        require_once(MODEL . "tvd/stockcas.php");
                        $stock = new StockCas();
                        $stock = $stock->FindByItemId($this->userAccYear,$awal->WarehouseId,$awal->ItemId);
                        if ($stock == null) {
                            $stock = new StockCas();
                            $stock->WarehouseId = $awal->WarehouseId;
                            $stock->ItemId = $awal->ItemId;
                            $stock->TrxYear = $this->userAccYear;
                            $stock->Price = $awal->Hpp;
                            $stock->OpQty = $awal->OpQty;
                            $rs = $stock->Insert();
                        }else{
                            $id = $stock->Id;
                            $stock->OpQty += $awal->OpQty;
                            $rs = $stock->Update($id);
                        }
                        if ($rs > 0){
                            $flagSuccess = true;
                            $log = $log->UserActivityWriter($this->userCabangId,'tvd.awalcas','Add New Item Stock Awal -> Stock Awal: '.$awal->ItemId.' - '.$awal->OpQty,'-','Success');
                            $this->persistence->SaveState("info", sprintf("Data Stock Awal: %s (%s) sudah berhasil disimpan", $awal->ItemId, $awal->OpQty));
                        }else{
                            $flagSuccess = false;
                            $log = $log->UserActivityWriter($this->userCabangId,'tvd.awalcas','Add New Item Stock Awal -> Stock Awal: '.$awal->ItemId.' - '.$awal->OpQty,'-','Failed');
                            $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                        }
                    }
                } else {
                    $flagSuccessc= false;
                    $log = $log->UserActivityWriter($this->userCabangId,'tvd.awalcas','Add New Item Stock Awal -> Stock Awal: '.$awal->ItemId.' - '.$awal->OpQty,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
                if ($flagSuccess){
                    $this->connector->CommitTransaction();
                    redirect_url("tvd.awalcas");
                }else{
                    $this->connector->RollbackTransaction();
                }
            }
        }else{
            $awal->OpDate =  mktime(0, 0, 0, 1, 1, $this->userAccYear);
        }
        $loader = new Items();
        $items  = $loader->LoadAll($this->userCompanyId,$this->userCabangId);
        $this->Set("items", $items);
        $loader = new Warehouse();
        $whs  = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("whs", $whs);
        $this->Set("awalcas", $awal);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("tvd.awalcas");
        }
        $log = new UserAdmin();
        $awal = new AwalCas();
        $awal = $awal->LoadById($id);
        if ($awal == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("tvd.awalcas");
        }
        /** @var $awal AwalCas */
        $flagSuccess = true;
        $this->connector->BeginTransaction();
        $rs = $awal->Delete($id);
        if ($rs > 0) {
            require_once(MODEL . "tvd/stockcas.php");
            $stock = new StockCas();
            $stock = $stock->FindByItemId($this->userAccYear,$awal->WarehouseId,$awal->ItemId);
            if ($stock != null){
                $id = $stock->Id;
                $stock->OpQty -= $awal->OpQty;
                $rs = $stock->Update($id);
            }
            if (!$rs){
                $flagSuccess = false;
            }
        }else{
            $flagSuccess = false;
        }
        if ($flagSuccess){
            $this->connector->CommitTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'tvd.awalcas','Delete Item Stock Awal -> Stock Awal: '.$awal->ItemId.' - '.$awal->OpQty,'-','Success');
            $this->persistence->SaveState("info", sprintf("Stock Awal Barang: %s (%s) sudah dihapus", $awal->ItemId, $awal->OpQty));
        } else {
            $this->connector->RollbackTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'tvd.awalcas','Delete Item Stock Awal -> Stock Awal: '.$awal->ItemId.' - '.$awal->OpQty,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $awal->ItemId, $awal->OpQty, $this->connector->GetErrorMessage()));
        }
		redirect_url("tvd.awalcas");
	}

	public function create(){
	    $awal = new AwalCas();
	    $awal = $awal->CreateStockAwalCas($this->userAccYear);
        redirect_url("tvd.awalcas");
    }
}

// End of file: awalcas_controller.php
