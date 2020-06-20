<?php
class WarehouseController extends AppController {
	private $userUid;
	private $userCompanyId;
    private $userCabangId;
    private $userLevel;

	protected function Initialize() {
		require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.kode", "display" => "Cabang", "width" => 80);
        $settings["columns"][] = array("name" => "a.wh_code", "display" => "Kode", "width" => 80);
		$settings["columns"][] = array("name" => "a.wh_name", "display" => "Gudang", "width" => 200);
        $settings["columns"][] = array("name" => "a.wh_pic", "display" => "P I C", "width" => 200);

		$settings["filters"][] = array("name" => "a.wh_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.wh_name", "display" => "Gudang");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Data Gudang";

			if ($acl->CheckUserAccess("master.warehouse", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.warehouse/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.warehouse", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.warehouse/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih data terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu data.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.warehouse", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.warehouse/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih data terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu warehouse.",
					"Confirm" => "Apakah anda mau menghapus data gudang yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_warehouse AS a Join m_cabang AS b ON a.cabang_id = b.id";
			if ($this->userLevel < 4) {
                $settings["where"] = "a.cabang_id = " . $this->userCabangId;
            }else{
                $settings["where"] = "b.company_id = " . $this->userCompanyId;
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Warehouse $warehouse) {

		return true;
	}

	public function add() {
        require_once(MODEL . "master/cabang.php");
        $warehouse = new Warehouse();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $warehouse->CabangId = $this->GetPostValue("CabangId");
            $warehouse->WhCode = $this->GetPostValue("WhCode");
            $warehouse->WhName = $this->GetPostValue("WhName");
            $warehouse->WhPic = $this->GetPostValue("WhPic");
            $warehouse->WhStatus = $this->GetPostValue("WhStatus");
            $warehouse->IsTrx = $this->GetPostValue("IsTrx");
            if ($this->ValidateData($warehouse)) {
                $warehouse->CreatebyId = $this->userUid;
                $rs = $warehouse->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.warehouse','Add New Item WhCode -> WhCode: '.$warehouse->WhCode.' - '.$warehouse->WhName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data WhCode: %s (%s) sudah berhasil disimpan", $warehouse->WhName, $warehouse->WhCode));
                    redirect_url("master.warehouse");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.warehouse','Add New Item WhCode -> WhCode: '.$warehouse->WhCode.' - '.$warehouse->WhName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new Cabang();
        if ($this->userLevel > 3) {
            $cabangs = $loader->LoadByCompanyId($this->userCompanyId);
        }else{
            $cabangs = $loader->LoadById($this->userCabangId);
        }
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("cabangs", $cabangs);
        $this->Set("warehouse", $warehouse);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.warehouse");
        }
        require_once(MODEL . "master/cabang.php");
        $log = new UserAdmin();
        $warehouse = new Warehouse();
        if (count($this->postData) > 0) {
            $warehouse->Id = $id;
            $warehouse->CabangId = $this->GetPostValue("CabangId");
            $warehouse->WhCode = $this->GetPostValue("WhCode");
            $warehouse->WhName = $this->GetPostValue("WhName");
            $warehouse->WhPic = $this->GetPostValue("WhPic");
            $warehouse->WhStatus = $this->GetPostValue("WhStatus");
            $warehouse->IsTrx = $this->GetPostValue("IsTrx");
            if ($this->ValidateData($warehouse)) {
                $warehouse->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $warehouse->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.warehouse','Update Item WhCode -> WhCode: '.$warehouse->WhCode.' - '.$warehouse->WhName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data gudang: %s (%s) sudah berhasil disimpan", $warehouse->WhName, $warehouse->WhCode));
                    redirect_url("master.warehouse");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.warehouse','Update Item WhCode -> WhCode: '.$warehouse->WhCode.' - '.$warehouse->WhName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data gudang. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $warehouse = $warehouse->LoadById($id);
            if ($warehouse == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.warehouse");
            }
        }
        $loader = new Cabang();
        if ($this->userLevel > 3) {
            $cabangs = $loader->LoadByCompanyId($this->userCompanyId);
        }else{
            $cabangs = $loader->LoadById($this->userCabangId);
        }
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("cabangs", $cabangs);
        $this->Set("warehouse", $warehouse);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.warehouse");
        }
        $log = new UserAdmin();
        $warehouse = new Warehouse();
        $warehouse = $warehouse->LoadById($id);
        if ($warehouse == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.warehouse");
        }
        $rs = $warehouse->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.warehouse','Delete Item WhCode -> WhCode: '.$warehouse->WhCode.' - '.$warehouse->WhName,'-','Success');
            $this->persistence->SaveState("info", sprintf("WhCode Barang: %s (%s) sudah dihapus", $warehouse->WhName, $warehouse->WhCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.warehouse','Delete Item WhCode -> WhCode: '.$warehouse->WhCode.' - '.$warehouse->WhName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $warehouse->WhName, $warehouse->WhCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.warehouse");
	}
}

// End of file: warehouse_controller.php
