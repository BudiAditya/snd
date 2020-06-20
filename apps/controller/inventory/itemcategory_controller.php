<?php
class ItemCategoryController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itemcategory.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "c.entity_code", "display" => "Entitas", "width" => 50);
        $settings["columns"][] = array("name" => "b.division_name", "display" => "Divisi", "width" => 150);
		$settings["columns"][] = array("name" => "a.category_code", "display" => "Kode", "width" => 50);
        $settings["columns"][] = array("name" => "a.category_name", "display" => "Kategori", "width" => 200);

		$settings["filters"][] = array("name" => "a.category_code", "display" => "Kode");
        $settings["filters"][] = array("name" => "a.category_name", "display" => "Kategori");
		$settings["filters"][] = array("name" => "b.division_name", "display" => "Nama Category");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Kategori Barang";

			if ($acl->CheckUserAccess("inventory.itemcategory", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemcategory/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itemcategory", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemcategory/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemcategory terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemcategory.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itemcategory", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemcategory/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemcategory terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemcategory.",
					"Confirm" => "Apakah anda mau menghapus data itemcategory yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 3;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_item_category AS a Join m_item_division b ON a.division_id = b.id JOIN m_item_entity c ON b.entity_id = c.id";
            $settings["where"] = "a.is_deleted = 0 And c.company_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemCategory $itemcategory) {

		return true;
	}

	public function add() {
	    require_once (MODEL . "inventory/itemdivision.php");
        $itemcategory = new ItemCategory();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itemcategory->DivisionId = $this->GetPostValue("DivisionId");
            $itemcategory->CategoryCode = $this->GetPostValue("CategoryCode");
            $itemcategory->CategoryName = $this->GetPostValue("CategoryName");
            if ($this->ValidateData($itemcategory)) {
                $itemcategory->CreatebyId = $this->userUid;
                $rs = $itemcategory->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemcategory','Add New Item Category -> Category: '.$itemcategory->CategoryCode.' - '.$itemcategory->CategoryName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Category: %s (%s) sudah berhasil disimpan", $itemcategory->CategoryName, $itemcategory->CategoryCode));
                    redirect_url("inventory.itemcategory");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemcategory','Add New Item Category -> Category: '.$itemcategory->CategoryCode.' - '.$itemcategory->CategoryName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new ItemDivision();
        $divisions = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itemcategory", $itemcategory);
        $this->Set("divisions", $divisions);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itemcategory");
        }
        require_once (MODEL . "inventory/itemdivision.php");
        $log = new UserAdmin();
        $itemcategory = new ItemCategory();
        if (count($this->postData) > 0) {
            $itemcategory->Id = $id;
            $itemcategory->DivisionId = $this->GetPostValue("DivisionId");
            $itemcategory->CategoryCode = $this->GetPostValue("CategoryCode");
            $itemcategory->CategoryName = $this->GetPostValue("CategoryName");
            if ($this->ValidateData($itemcategory)) {
                $itemcategory->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemcategory->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemcategory','Update Item Category -> Category: '.$itemcategory->CategoryCode.' - '.$itemcategory->CategoryName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Category: %s (%s) sudah berhasil disimpan", $itemcategory->CategoryName, $itemcategory->CategoryCode));
                    redirect_url("inventory.itemcategory");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemcategory','Update Item Category -> Category: '.$itemcategory->CategoryCode.' - '.$itemcategory->CategoryName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Category. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemcategory = $itemcategory->LoadById($id);
            if ($itemcategory == null || $itemcategory->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itemcategory");
            }
        }
        $loader = new ItemDivision();
        $divisions = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itemcategory", $itemcategory);
        $this->Set("divisions", $divisions);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itemcategory");
        }
        $log = new UserAdmin();
        $itemcategory = new ItemCategory();
        $itemcategory = $itemcategory->LoadById($id);
        if ($itemcategory == null || $itemcategory->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itemcategory");
        }
        $rs = $itemcategory->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemcategory','Delete Item Category -> Category: '.$itemcategory->CategoryCode.' - '.$itemcategory->CategoryName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Category Barang: %s (%s) sudah dihapus", $itemcategory->CategoryName, $itemcategory->CategoryCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemcategory','Delete Item Category -> Category: '.$itemcategory->CategoryCode.' - '.$itemcategory->CategoryName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemcategory->CategoryName, $itemcategory->CategoryCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itemcategory");
	}
}

// End of file: itemcategory_controller.php
