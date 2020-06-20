<?php
class ItemPrincipalController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itemprincipal.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.sup_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.sup_name", "display" => "Nama Principal", "width" => 300);

		$settings["filters"][] = array("name" => "a.sup_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.sup_name", "display" => "Nama Principal");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Principal";
            /*
			if ($acl->CheckUserAccess("inventory.itemprincipal", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemprincipal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itemprincipal", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemprincipal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itemprincipal terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itemprincipal.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itemprincipal", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemprincipal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itemprincipal terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itemprincipal.",
					"Confirm" => "Apakah anda mau menghapus data itemprincipal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}
            */
			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_supplier AS a";
            $settings["where"] = "a.is_deleted = 0 And a.is_principal = 1";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemPrincipal $itemprincipal) {

		return true;
	}

	public function add() {
	    $itemprincipal = new ItemPrincipal();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itemprincipal->CompanyId = $this->userCompanyId;
            $itemprincipal->PrincipalCode = $this->GetPostValue("PrincipalCode");
            $itemprincipal->PrincipalName = $this->GetPostValue("PrincipalName");
            if ($this->ValidateData($itemprincipal)) {
                $itemprincipal->CreatebyId = $this->userUid;
                $rs = $itemprincipal->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprincipal','Add New Item Divisi -> Divisi: '.$itemprincipal->PrincipalCode.' - '.$itemprincipal->PrincipalName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Divisi: %s (%s) sudah berhasil disimpan", $itemprincipal->PrincipalName, $itemprincipal->PrincipalCode));
                    redirect_url("inventory.itemprincipal");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprincipal','Add New Item Divisi -> Divisi: '.$itemprincipal->PrincipalCode.' - '.$itemprincipal->PrincipalName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("itemprincipal", $itemprincipal);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itemprincipal");
        }
        $log = new UserAdmin();
        $itemprincipal = new ItemPrincipal();
        if (count($this->postData) > 0) {
            $itemprincipal->Id = $id;
            $itemprincipal->CompanyId = $this->userCompanyId;
            $itemprincipal->PrincipalCode = $this->GetPostValue("PrincipalCode");
            $itemprincipal->PrincipalName = $this->GetPostValue("PrincipalName");
            if ($this->ValidateData($itemprincipal)) {
                $itemprincipal->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $itemprincipal->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprincipal','Update Item Divisi -> Divisi: '.$itemprincipal->PrincipalCode.' - '.$itemprincipal->PrincipalName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Divisi: %s (%s) sudah berhasil disimpan", $itemprincipal->PrincipalName, $itemprincipal->PrincipalCode));
                    redirect_url("inventory.itemprincipal");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprincipal','Update Item Divisi -> Divisi: '.$itemprincipal->PrincipalCode.' - '.$itemprincipal->PrincipalName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Divisi. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itemprincipal = $itemprincipal->LoadById($id);
            if ($itemprincipal == null || $itemprincipal->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itemprincipal");
            }
        }
        $this->Set("itemprincipal", $itemprincipal);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itemprincipal");
        }
        $log = new UserAdmin();
        $itemprincipal = new ItemPrincipal();
        $itemprincipal = $itemprincipal->LoadById($id);
        if ($itemprincipal == null || $itemprincipal->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itemprincipal");
        }
        $rs = $itemprincipal->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprincipal','Delete Item Divisi -> Divisi: '.$itemprincipal->PrincipalCode.' - '.$itemprincipal->PrincipalName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Divisi Barang: %s (%s) sudah dihapus", $itemprincipal->PrincipalName, $itemprincipal->PrincipalCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprincipal','Delete Item Divisi -> Divisi: '.$itemprincipal->PrincipalCode.' - '.$itemprincipal->PrincipalName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $itemprincipal->PrincipalName, $itemprincipal->PrincipalCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itemprincipal");
	}
}

// End of file: itemprincipal_controller.php
