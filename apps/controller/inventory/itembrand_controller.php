<?php
class ItemBrandController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itembrand.php");
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
        $settings["columns"][] = array("name" => "a.brand_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.brand_name", "display" => "Brand", "width" => 200);
        $settings["columns"][] = array("name" => "c.sup_name", "display" => "Principal", "width" => 300);

		$settings["filters"][] = array("name" => "a.brand_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.brand_name", "display" => "Brand");
        $settings["filters"][] = array("name" => "c.sup_name", "display" => "Principal");
        $settings["filters"][] = array("name" => "b.entity_code", "display" => "Entitas");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Brand Barang";

			if ($acl->CheckUserAccess("inventory.itembrand", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itembrand/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itembrand", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itembrand/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itembrand terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itembrand.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itembrand", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itembrand/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itembrand terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itembrand.",
					"Confirm" => "Apakah anda mau menghapus data itembrand yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_item_brand AS a Left Join m_item_entity b ON a.entity_id = b.id Left Join m_supplier c ON a.supplier_id = c.id";
            $settings["where"] = "a.is_deleted = 0 And b.company_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemBrand $itembrand) {

		return true;
	}

	public function add() {
	    require_once (MODEL . "inventory/itementity.php");
        require_once (MODEL . "ap/supplier.php");
        $itembrand = new ItemBrand();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itembrand->EntityId = $this->GetPostValue("EntityId");
            $itembrand->BrandCode = $this->GetPostValue("BrandCode");
            $itembrand->BrandName = $this->GetPostValue("BrandName");
            $itembrand->SupplierId = $this->GetPostValue("SupplierId");
            if ($this->ValidateData($itembrand)) {
                $itembrand->CreatebyId = $this->userUid;
                $rs = $itembrand->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itembrand','Add New Item Divisi -> Divisi: '.$itembrand->BrandCode.' - '.$itembrand->BrandName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Divisi: %s (%s) sudah berhasil disimpan", $itembrand->BrandName, $itembrand->BrandCode));
                    redirect_url("inventory.itembrand");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itembrand','Add New Item Divisi -> Divisi: '.$itembrand->BrandCode.' - '.$itembrand->BrandName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new ItemEntity();
        $entities = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itembrand", $itembrand);
        $this->Set("entities", $entities);
        $loader = new Supplier();
        $principals = $loader->LoadPrincipal();
        $this->Set("principals", $principals);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itembrand");
        }
        require_once (MODEL . "inventory/itementity.php");
        require_once (MODEL . "ap/supplier.php");
        $log = new UserAdmin();
        $itembrand = new ItemBrand();
        if (count($this->postData) > 0) {
            $itembrand->Id = $id;
            $itembrand->EntityId = $this->GetPostValue("EntityId");
            $itembrand->BrandCode = $this->GetPostValue("BrandCode");
            $itembrand->BrandName = $this->GetPostValue("BrandName");
            $itembrand->SupplierId = $this->GetPostValue("SupplierId");
            if ($this->ValidateData($itembrand)) {
                $itembrand->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itembrand->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itembrand','Update Item Divisi -> Divisi: '.$itembrand->BrandCode.' - '.$itembrand->BrandName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Divisi: %s (%s) sudah berhasil disimpan", $itembrand->BrandName, $itembrand->BrandCode));
                    redirect_url("inventory.itembrand");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itembrand','Update Item Divisi -> Divisi: '.$itembrand->BrandCode.' - '.$itembrand->BrandName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Divisi. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itembrand = $itembrand->LoadById($id);
            if ($itembrand == null || $itembrand->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itembrand");
            }
        }
        $loader = new ItemEntity();
        $entities = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("itembrand", $itembrand);
        $this->Set("entities", $entities);
        $loader = new Supplier();
        $principals = $loader->LoadPrincipal();
        $this->Set("principals", $principals);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itembrand");
        }
        $log = new UserAdmin();
        $itembrand = new ItemBrand();
        $itembrand = $itembrand->LoadById($id);
        if ($itembrand == null || $itembrand->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itembrand");
        }
        $rs = $itembrand->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itembrand','Delete Item Divisi -> Divisi: '.$itembrand->BrandCode.' - '.$itembrand->BrandName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Divisi Barang: %s (%s) sudah dihapus", $itembrand->BrandName, $itembrand->BrandCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itembrand','Delete Item Divisi -> Divisi: '.$itembrand->BrandCode.' - '.$itembrand->BrandName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itembrand->BrandName, $itembrand->BrandCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itembrand");
	}
}

// End of file: itembrand_controller.php
