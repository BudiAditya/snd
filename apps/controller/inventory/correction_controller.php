<?php
class CorrectionController extends AppController {
    private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userAccYear;
    private $userAccMonth;

    protected function Initialize() {
        require_once(MODEL . "inventory/correction.php");
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
        //$settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.wh_code", "display" => "Gudang", "width" => 80);
        $settings["columns"][] = array("name" => "a.corr_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.corr_no", "display" => "Trx. No.", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 100);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 250);
        $settings["columns"][] = array("name" => "a.satuan", "display" => "Satuan", "width" => 50);
        $settings["columns"][] = array("name" => "a.sys_qty", "display" => "System", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.whs_qty", "display" => "Gudang", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.corr_qty", "display" => "Koreksi", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.corr_amt,2)", "display" => "Nilai", "width" => 80, "align" => "right");

        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Cabang");
        $settings["filters"][] = array("name" => "a.wh_code", "display" => "Gudang");

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Stock Opname & Koreksi";

            if ($acl->CheckUserAccess("inventory.correction", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "inventory.correction/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.correction", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.correction/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
                    "Error" => "Mohon memilih correction terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu correction.",
                    "Confirm" => "Apakah anda mau menghapus data correction yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }

            $settings["def_order"] = 4;
            $settings["def_filter"] = 0;
            $settings["singleSelect"] = true;

        } else {
            $settings["from"] = "vw_ic_stock_correction AS a ";
            $settings["where"] = "a.cabang_id = ".$this->userCabangId." And Year(a.corr_date) = ".$this->userAccYear;
            $settings["order_by"] = "a.wh_code, a.item_code";
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    private function ValidateData(Correction $correction) {
        if ($correction->CorrQty == 0 || $correction->CorrQty == null || $correction->CorrQty == ''){
            $this->Set("error", "Koreksi Stock tidak valid (Koreksi = 0)!");
            return false;
        }else {
            return true;
        }
    }

    public function add() {
        require_once (MODEL . "master/warehouse.php");
        $correction = new Correction();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $correction->WarehouseId = $this->GetPostValue("WarehouseId");
            $correction->CorrDate = $this->GetPostValue("CorrDate");
            $correction->ItemId = $this->GetPostValue("ItemId");
            $correction->SysQty = $this->GetPostValue("SysQty");
            $correction->WhsQty = $this->GetPostValue("WhsQty");
            $correction->CorrQty = $this->GetPostValue("CorrQty");
            $correction->CorrReason = 'Selisih Stock';
            $correction->CorrStatus = 0;
            $correction->CorrAmt = 0;
            if ($this->ValidateData($correction)) {
                $correction->CreatebyId = $this->userUid;
                $correction->CorrNo = $correction->GetCorrectionDocNo($this->userCabangId);
                $rs = $correction->Insert();
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Add New Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Stock Correction: %s (%s) sudah berhasil disimpan", $correction->ItemId, $correction->SysQty));
                    redirect_url("inventory.correction");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Add New Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new Warehouse();
        $whs  = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("whs", $whs);
        $this->Set("correction", $correction);
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.correction");
        }
        $log = new UserAdmin();
        $correction = new Correction();
        $correction = $correction->LoadById($id);
        if ($correction == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.correction");
        }
        $rs = $correction->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Delete Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Success');
            $this->persistence->SaveState("info", sprintf("Stock Correction Barang: %s (%s) sudah dihapus", $correction->ItemId, $correction->SysQty));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Delete Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $correction->ItemId, $correction->SysQty, $this->connector->GetErrorMessage()));
        }
        redirect_url("inventory.correction");
    }
}

// End of file: correction_controller.php
