<?php
class SupTypeController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ap/suptype.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.type_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.type_name", "display" => "Jenis", "width" => 150);
        $settings["columns"][] = array("name" => "b.kode", "display" => "Akun Hutang", "width" => 100);
        $settings["columns"][] = array("name" => "b.perkiraan", "display" => "Nama Perkiraan", "width" => 200);

		$settings["filters"][] = array("name" => "a.type_code", "display" => "Jenis Barang");
		$settings["filters"][] = array("name" => "a.type_name", "display" => "Jenis");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Type Supplier";

			if ($acl->CheckUserAccess("ap.suptype", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ap.suptype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ap.suptype", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ap.suptype/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih suptype terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu suptype.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ap.suptype", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ap.suptype/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih suptype terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu suptype.",
					"Confirm" => "Apakah anda mau menghapus data suptype yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
		    $sql = "m_supplier_type AS a Left Join m_account b On a.ap_acc_id = b.id";
			$settings["from"] = $sql;
            $settings["where"] = " a.company_id = ".$this->userCompanyId." And a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(SupType $suptype) {

		return true;
	}

	public function add() {
        require_once(MODEL . "master/coadetail.php");
        $suptype = new SupType();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $suptype->CompanyId = $this->userCompanyId;
            $suptype->TypeCode = $this->GetPostValue("TypeCode");
            $suptype->TypeName = $this->GetPostValue("TypeName");
            $suptype->ApAccId = $this->GetPostValue("ApAccId");
            if ($this->ValidateData($suptype)) {
                $suptype->CreatebyId = $this->userUid;
                $rs = $suptype->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.suptype','Add New Item Jenis -> Jenis: '.$suptype->TypeCode.' - '.$suptype->TypeName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Jenis: %s (%s) sudah berhasil disimpan", $suptype->TypeName, $suptype->TypeCode));
                    redirect_url("ap.suptype");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.suptype','Add New Item Jenis -> Jenis: '.$suptype->TypeCode.' - '.$suptype->TypeName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new CoaDetail();
        $ivtCoa = $loader->LoadAll($this->userCompanyId);
        $this->Set("ivtcoa", $ivtCoa);
        $this->Set("suptype", $suptype);
	}

	public function edit($id = null) {
        require_once(MODEL . "master/coadetail.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ap.suptype");
        }
        $log = new UserAdmin();
        $suptype = new SupType();
        if (count($this->postData) > 0) {
            $suptype->Id = $id;
            $suptype->CompanyId = $this->userCompanyId;
            $suptype->TypeCode = $this->GetPostValue("TypeCode");
            $suptype->TypeName = $this->GetPostValue("TypeName");
            $suptype->ApAccId = $this->GetPostValue("ApAccId");
            if ($this->ValidateData($suptype)) {
                $suptype->UpdatebyId = $this->userUid;
                $rs = $suptype->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.suptype','Update Item Jenis -> Jenis: '.$suptype->TypeCode.' - '.$suptype->TypeName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $suptype->TypeName, $suptype->TypeCode));
                    redirect_url("ap.suptype");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.suptype','Update Item Jenis -> Jenis: '.$suptype->TypeCode.' - '.$suptype->TypeName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $suptype = $suptype->LoadById($id);
            if ($suptype == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("ap.suptype");
            }
        }
        $loader = new CoaDetail();
        $ivtCoa = $loader->LoadAll($this->userCompanyId);
        $this->Set("ivtcoa", $ivtCoa);
        $this->Set("suptype", $suptype);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("ap.suptype");
        }
        $log = new UserAdmin();
        $suptype = new SupType();
        $suptype = $suptype->LoadById($id);
        if ($suptype == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ap.suptype");
        }
        $rs = $suptype->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.suptype','Delete Item Jenis -> Jenis: '.$suptype->TypeCode.' - '.$suptype->TypeName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Jenis Barang: %s (%s) sudah dihapus", $suptype->TypeName, $suptype->TypeCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.suptype','Delete Item Jenis -> Jenis: '.$suptype->TypeCode.' - '.$suptype->TypeName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis barang: %s (%s). Error: %s", $suptype->TypeName, $suptype->TypeCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("ap.suptype");
	}
}

// End of file: suptype_controller.php
