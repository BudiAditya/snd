<?php
class ItemUomController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itemuom.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.uom_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.uom_name", "display" => "Satuan", "width" => 250);

		$settings["filters"][] = array("name" => "a.uom_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.uom_name", "display" => "Satuan");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Satuan Barang";

			if ($acl->CheckUserAccess("inventory.itemuom", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemuom/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itemuom", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemuom/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemuom terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemuom.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itemuom", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemuom/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemuom terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemuom.",
					"Confirm" => "Apakah anda mau menghapus data itemuom yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_item_uom AS a ";
            $settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemUom $itemuom) {

		return true;
	}

	public function add() {
	    $itemuom = new ItemUom();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itemuom->UomCode = $this->GetPostValue("UomCode");
            $itemuom->UomName = $this->GetPostValue("UomName");
            if ($this->ValidateData($itemuom)) {
                $itemuom->CreatebyId = $this->userUid;
                $rs = $itemuom->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemuom','Add New Item Satuan -> Satuan: '.$itemuom->UomCode.' - '.$itemuom->UomName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Satuan: %s (%s) sudah berhasil disimpan", $itemuom->UomName, $itemuom->UomCode));
                    redirect_url("inventory.itemuom");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemuom','Add New Item Satuan -> Satuan: '.$itemuom->UomCode.' - '.$itemuom->UomName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("itemuom", $itemuom);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itemuom");
        }
        $log = new UserAdmin();
        $itemuom = new ItemUom();
        if (count($this->postData) > 0) {
            $itemuom->Id = $id;
            $itemuom->UomCode = $this->GetPostValue("UomCode");
            $itemuom->UomName = $this->GetPostValue("UomName");
            if ($this->ValidateData($itemuom)) {
                $itemuom->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemuom->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemuom','Update Item Satuan -> Satuan: '.$itemuom->UomCode.' - '.$itemuom->UomName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Satuan: %s (%s) sudah berhasil disimpan", $itemuom->UomName, $itemuom->UomCode));
                    redirect_url("inventory.itemuom");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemuom','Update Item Satuan -> Satuan: '.$itemuom->UomCode.' - '.$itemuom->UomName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemuom = $itemuom->LoadById($id);
            if ($itemuom == null || $itemuom->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itemuom");
            }
        }
        $this->Set("itemuom", $itemuom);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itemuom");
        }
        $log = new UserAdmin();
        $itemuom = new ItemUom();
        $itemuom = $itemuom->LoadById($id);
        if ($itemuom == null || $itemuom->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itemuom");
        }
        $rs = $itemuom->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemuom','Delete Item Satuan -> Satuan: '.$itemuom->UomCode.' - '.$itemuom->UomName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Satuan Barang: %s (%s) sudah dihapus", $itemuom->UomName, $itemuom->UomCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemuom','Delete Item Satuan -> Satuan: '.$itemuom->UomCode.' - '.$itemuom->UomName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemuom->UomName, $itemuom->UomCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itemuom");
	}
}

// End of file: itemuom_controller.php
