<?php
class ItemSubCategoryController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itemsubcategory.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a1.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "c.entity_code", "display" => "Entitas", "width" => 50);
        $settings["columns"][] = array("name" => "b.division_name", "display" => "Divisi", "width" => 150);
        $settings["columns"][] = array("name" => "a.category_name", "display" => "Kategori", "width" => 150);
        $settings["columns"][] = array("name" => "a1.subcategory_code", "display" => "Kode", "width" => 50);
        $settings["columns"][] = array("name" => "a1.subcategory_name", "display" => "Sub Kategori", "width" => 150);

		$settings["filters"][] = array("name" => "a1.subcategory_code", "display" => "Kode");
        $settings["filters"][] = array("name" => "a.category_name", "display" => "Kategori");
		$settings["filters"][] = array("name" => "b.division_name", "display" => "Nama Category");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Sub Kategori Barang";

			if ($acl->CheckUserAccess("inventory.itemsubcategory", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemsubcategory/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itemsubcategory", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemsubcategory/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemsubcategory terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemsubcategory.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itemsubcategory", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemsubcategory/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemsubcategory terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemsubcategory.",
					"Confirm" => "Apakah anda mau menghapus data itemsubcategory yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 4;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_item_subcategory AS a1 Join m_item_category AS a ON a1.category_id = a.id Join m_item_division b ON a.division_id = b.id JOIN m_item_entity c ON b.entity_id = c.id";
            $settings["where"] = "a1.is_deleted = 0 And c.company_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemSubCategory $itemsubcategory) {

		return true;
	}

	public function add() {
	    require_once (MODEL . "inventory/itemcategory.php");
        $itemsubcategory = new ItemSubCategory();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itemsubcategory->CategoryId = $this->GetPostValue("CategoryId");
            $itemsubcategory->SubCategoryCode = $this->GetPostValue("SubCategoryCode");
            $itemsubcategory->SubCategoryName = $this->GetPostValue("SubCategoryName");
            if ($this->ValidateData($itemsubcategory)) {
                $itemsubcategory->CreatebyId = $this->userUid;
                $rs = $itemsubcategory->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemsubcategory','Add New Item Category -> Category: '.$itemsubcategory->SubCategoryCode.' - '.$itemsubcategory->SubCategoryName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Category: %s (%s) sudah berhasil disimpan", $itemsubcategory->SubCategoryName, $itemsubcategory->SubCategoryCode));
                    redirect_url("inventory.itemsubcategory");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemsubcategory','Add New Item Category -> Category: '.$itemsubcategory->SubCategoryCode.' - '.$itemsubcategory->SubCategoryName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new ItemCategory();
        $categories = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itemsubcategory", $itemsubcategory);
        $this->Set("categories", $categories);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itemsubcategory");
        }
        require_once (MODEL . "inventory/itemcategory.php");
        $log = new UserAdmin();
        $itemsubcategory = new ItemSubCategory();
        if (count($this->postData) > 0) {
            $itemsubcategory->Id = $id;
            $itemsubcategory->CategoryId = $this->GetPostValue("CategoryId");
            $itemsubcategory->SubCategoryCode = $this->GetPostValue("SubCategoryCode");
            $itemsubcategory->SubCategoryName = $this->GetPostValue("SubCategoryName");
            if ($this->ValidateData($itemsubcategory)) {
                $itemsubcategory->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemsubcategory->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemsubcategory','Update Item Category -> Category: '.$itemsubcategory->SubCategoryCode.' - '.$itemsubcategory->SubCategoryName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Category: %s (%s) sudah berhasil disimpan", $itemsubcategory->SubCategoryName, $itemsubcategory->SubCategoryCode));
                    redirect_url("inventory.itemsubcategory");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemsubcategory','Update Item Category -> Category: '.$itemsubcategory->SubCategoryCode.' - '.$itemsubcategory->SubCategoryName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Category. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemsubcategory = $itemsubcategory->LoadById($id);
            if ($itemsubcategory == null || $itemsubcategory->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itemsubcategory");
            }
        }
        $loader = new ItemCategory();
        $categories = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itemsubcategory", $itemsubcategory);
        $this->Set("categories", $categories);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itemsubcategory");
        }
        $log = new UserAdmin();
        $itemsubcategory = new ItemSubCategory();
        $itemsubcategory = $itemsubcategory->LoadById($id);
        if ($itemsubcategory == null || $itemsubcategory->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itemsubcategory");
        }
        $rs = $itemsubcategory->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemsubcategory','Delete Item Category -> Category: '.$itemsubcategory->SubCategoryCode.' - '.$itemsubcategory->SubCategoryName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Category Barang: %s (%s) sudah dihapus", $itemsubcategory->SubCategoryName, $itemsubcategory->SubCategoryCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemsubcategory','Delete Item Category -> Category: '.$itemsubcategory->SubCategoryCode.' - '.$itemsubcategory->SubCategoryName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemsubcategory->SubCategoryName, $itemsubcategory->SubCategoryCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itemsubcategory");
	}
}

// End of file: itemsubcategory_controller.php
