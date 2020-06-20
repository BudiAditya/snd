<?php
class ItemDivisionController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itemdivision.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "b.entity_code", "display" => "Entitas", "width" => 50);
        $settings["columns"][] = array("name" => "a.division_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.division_name", "display" => "Nama Divisi", "width" => 250);

		$settings["filters"][] = array("name" => "a.division_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.division_name", "display" => "Nama Divisi");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Divisi Barang";

			if ($acl->CheckUserAccess("inventory.itemdivision", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemdivision/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itemdivision", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemdivision/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemdivisi terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemdivisi.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itemdivision", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemdivision/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemdivisi terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemdivisi.",
					"Confirm" => "Apakah anda mau menghapus data itemdivisi yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_item_division AS a Left Join m_item_entity b ON a.entity_id = b.id";
            $settings["where"] = "a.is_deleted = 0 And b.company_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemDivision $itemdivisi) {

		return true;
	}

	public function add() {
	    require_once (MODEL . "inventory/itementity.php");
        $itemdivisi = new ItemDivision();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itemdivisi->EntityId = $this->GetPostValue("EntityId");
            $itemdivisi->DivisionCode = $this->GetPostValue("DivisionCode");
            $itemdivisi->DivisionName = $this->GetPostValue("DivisionName");
            if ($this->ValidateData($itemdivisi)) {
                $itemdivisi->CreatebyId = $this->userUid;
                $rs = $itemdivisi->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemdivision','Add New Item Divisi -> Divisi: '.$itemdivisi->DivisionCode.' - '.$itemdivisi->DivisionName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Divisi: %s (%s) sudah berhasil disimpan", $itemdivisi->DivisionName, $itemdivisi->DivisionCode));
                    redirect_url("inventory.itemdivision");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemdivision','Add New Item Divisi -> Divisi: '.$itemdivisi->DivisionCode.' - '.$itemdivisi->DivisionName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new ItemEntity();
        $entities = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itemdivisi", $itemdivisi);
        $this->Set("entities", $entities);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itemdivision");
        }
        require_once (MODEL . "inventory/itementity.php");
        $log = new UserAdmin();
        $itemdivisi = new ItemDivision();
        if (count($this->postData) > 0) {
            $itemdivisi->Id = $id;
            $itemdivisi->EntityId = $this->GetPostValue("EntityId");
            $itemdivisi->DivisionCode = $this->GetPostValue("DivisionCode");
            $itemdivisi->DivisionName = $this->GetPostValue("DivisionName");
            if ($this->ValidateData($itemdivisi)) {
                $itemdivisi->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemdivisi->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemdivision','Update Item Divisi -> Divisi: '.$itemdivisi->DivisionCode.' - '.$itemdivisi->DivisionName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Divisi: %s (%s) sudah berhasil disimpan", $itemdivisi->DivisionName, $itemdivisi->DivisionCode));
                    redirect_url("inventory.itemdivision");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemdivision','Update Item Divisi -> Divisi: '.$itemdivisi->DivisionCode.' - '.$itemdivisi->DivisionName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Divisi. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemdivisi = $itemdivisi->LoadById($id);
            if ($itemdivisi == null || $itemdivisi->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itemdivision");
            }
        }
        $loader = new ItemEntity();
        $entities = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itemdivisi", $itemdivisi);
        $this->Set("entities", $entities);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itemdivision");
        }
        $log = new UserAdmin();
        $itemdivisi = new ItemDivision();
        $itemdivisi = $itemdivisi->LoadById($id);
        if ($itemdivisi == null || $itemdivisi->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itemdivision");
        }
        $rs = $itemdivisi->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemdivision','Delete Item Divisi -> Divisi: '.$itemdivisi->DivisionCode.' - '.$itemdivisi->DivisionName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Divisi Barang: %s (%s) sudah dihapus", $itemdivisi->DivisionName, $itemdivisi->DivisionCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemdivision','Delete Item Divisi -> Divisi: '.$itemdivisi->DivisionCode.' - '.$itemdivisi->DivisionName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemdivisi->DivisionName, $itemdivisi->DivisionCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itemdivision");
	}
}

// End of file: itemdivisi_controller.php
