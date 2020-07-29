<?php
class CusTypeController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.type_code", "display" => "Kode", "width" => 100);
		$settings["columns"][] = array("name" => "a.type_name", "display" => "TYpe", "width" => 250);

		$settings["filters"][] = array("name" => "a.type_code", "display" => "Jenis Barang");
		$settings["filters"][] = array("name" => "a.type_name", "display" => "Jenis");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Kategori Customer/Outlet";

			if ($acl->CheckUserAccess("ar.custype", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ar.custype/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ar.custype", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ar.custype/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih custype terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu custype.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ar.custype", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ar.custype/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih custype terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu custype.",
					"Confirm" => "Apakah anda mau menghapus data custype yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
		    $sql = "m_customer_type AS a";
			$settings["from"] = $sql;
            $settings["where"] = " a.company_id = ".$this->userCompanyId." And a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(CusType $custype) {

		return true;
	}

	public function add() {
        $custype = new CusType();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $custype->CompanyId = $this->userCompanyId;
            $custype->TypeCode = $this->GetPostValue("TypeCode");
            $custype->TypeName = $this->GetPostValue("TypeName");
            $custype->TrxId = $this->GetPostValue("TrxId");
            if ($this->ValidateData($custype)) {
                $custype->CreatebyId = $this->userUid;
                $rs = $custype->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.custype','Add New Item Jenis -> Jenis: '.$custype->TypeCode.' - '.$custype->TypeName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Jenis: %s (%s) sudah berhasil disimpan", $custype->TypeName, $custype->TypeCode));
                    redirect_url("ar.custype");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.custype','Add New Item Jenis -> Jenis: '.$custype->TypeCode.' - '.$custype->TypeName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("custype", $custype);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ar.custype");
        }
        $log = new UserAdmin();
        $custype = new CusType();
        if (count($this->postData) > 0) {
            $custype->Id = $id;
            $custype->CompanyId = $this->userCompanyId;
            $custype->TypeCode = $this->GetPostValue("TypeCode");
            $custype->TypeName = $this->GetPostValue("TypeName");
            $custype->TrxId = $this->GetPostValue("TrxId");
            if ($this->ValidateData($custype)) {
                $custype->UpdatebyId = $this->userUid;
                $rs = $custype->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.custype','Update Item Jenis -> Jenis: '.$custype->TypeCode.' - '.$custype->TypeName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $custype->TypeName, $custype->TypeCode));
                    redirect_url("ar.custype");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.custype','Update Item Jenis -> Jenis: '.$custype->TypeCode.' - '.$custype->TypeName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $custype = $custype->LoadById($id);
            if ($custype == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("ar.custype");
            }
        }
        $this->Set("custype", $custype);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("ar.custype");
        }
        $log = new UserAdmin();
        $custype = new CusType();
        $custype = $custype->LoadById($id);
        if ($custype == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ar.custype");
        }
        $rs = $custype->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.custype','Delete Item Jenis -> Jenis: '.$custype->TypeCode.' - '.$custype->TypeName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Jenis Barang: %s (%s) sudah dihapus", $custype->TypeName, $custype->TypeCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.custype','Delete Item Jenis -> Jenis: '.$custype->TypeCode.' - '.$custype->TypeName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis barang: %s (%s). Error: %s", $custype->TypeName, $custype->TypeCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("ar.custype");
	}
}

// End of file: custype_controller.php
