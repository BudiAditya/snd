<?php
class BankController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/bank.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.bank_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.bank_name", "display" => "Nama Bank", "width" => 250);

		$settings["filters"][] = array("name" => "a.bank_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.bank_name", "display" => "Nama Bank");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Nama Bank";

			if ($acl->CheckUserAccess("master.bank", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.bank/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.bank", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.bank/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih bank terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu bank.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.bank", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.bank/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih bank terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu bank.",
					"Confirm" => "Apakah anda mau menghapus data bank yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_bank AS a ";
            $settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Bank $bank) {

		return true;
	}

	public function add() {
	    $bank = new Bank();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $bank->BankCode = $this->GetPostValue("BankCode");
            $bank->BankName = $this->GetPostValue("BankName");
            if ($this->ValidateData($bank)) {
                $bank->CreatebyId = $this->userUid;
                $rs = $bank->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.bank','Add New Item Nama Bank -> Nama Bank: '.$bank->BankCode.' - '.$bank->BankName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Nama Bank: %s (%s) sudah berhasil disimpan", $bank->BankName, $bank->BankCode));
                    redirect_url("master.bank");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.bank','Add New Item Nama Bank -> Nama Bank: '.$bank->BankCode.' - '.$bank->BankName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("bank", $bank);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.bank");
        }
        $log = new UserAdmin();
        $bank = new Bank();
        if (count($this->postData) > 0) {
            $bank->Id = $id;
            $bank->BankCode = $this->GetPostValue("BankCode");
            $bank->BankName = $this->GetPostValue("BankName");
            if ($this->ValidateData($bank)) {
                $bank->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $bank->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.bank','Update Item Nama Bank -> Nama Bank: '.$bank->BankCode.' - '.$bank->BankName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Nama Bank: %s (%s) sudah berhasil disimpan", $bank->BankName, $bank->BankCode));
                    redirect_url("master.bank");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.bank','Update Item Nama Bank -> Nama Bank: '.$bank->BankCode.' - '.$bank->BankName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Nama Bank. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $bank = $bank->LoadById($id);
            if ($bank == null || $bank->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.bank");
            }
        }
        $this->Set("bank", $bank);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.bank");
        }
        $log = new UserAdmin();
        $bank = new Bank();
        $bank = $bank->LoadById($id);
        if ($bank == null || $bank->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.bank");
        }
        $rs = $bank->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.bank','Delete Item Nama Bank -> Nama Bank: '.$bank->BankCode.' - '.$bank->BankName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Nama Bank Barang: %s (%s) sudah dihapus", $bank->BankName, $bank->BankCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.bank','Delete Item Nama Bank -> Nama Bank: '.$bank->BankCode.' - '.$bank->BankName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $bank->BankName, $bank->BankCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.bank");
	}
}

// End of file: bank_controller.php
