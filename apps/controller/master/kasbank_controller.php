<?php

class KasBankController extends AppController {
	private $userCompanyId;
	private $userCabangId;
	private $userLevel;

	protected function Initialize() {
		require_once(MODEL . "master/kasbank.php");
		require_once(MODEL . "master/user_admin.php");
		$this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userCabangId = $this->persistence->LoadState("cabang_id");
		$this->userLevel = $this->persistence->LoadState("user_lvl");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		//$settings["columns"][] = array("name" => "a.kode", "display" => "Cabang", "width" => 100);
		$settings["columns"][] = array("name" => "a.bank_name", "display" => "Nama Kas/Bank", "width" => 150);
		$settings["columns"][] = array("name" => "a.branch", "display" => "Bank Cabang", "width" => 100);
		$settings["columns"][] = array("name" => "a.rek_no", "display" => "No. Rekening", "width" => 100);
		$settings["columns"][] = array("name" => "a.currency_cd", "display" => "Mata Uang", "width" => 60);
		$settings["columns"][] = array("name" => "concat(a.trx_acc_code,' - ',a.trx_acc_name)", "display" => "Akun Kontrol", "width" => 200);
		$settings["columns"][] = array("name" => "concat(a.cost_acc_code,' - ',a.cost_acc_name)", "display" => "Akun Biaya", "width" => 200);
		$settings["columns"][] = array("name" => "concat(a.rev_acc_code,' - ',a.rev_acc_name)", "display" => "Akun Pendapatan", "width" => 200);

		$settings["filters"][] = array("name" => "a.bank_name", "display" => "Bank");
		$settings["filters"][] = array("name" => "a.branch", "display" => "Cabang");
		$settings["filters"][] = array("name" => "a.rek_no", "display" => "No. Rekening");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Kas & Bank";

			if ($acl->CheckUserAccess("master.kasbank", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.kasbank/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.kasbank", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.kasbank/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih kas/bank terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu kas/bank.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.kasbank", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.kasbank/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih kas/bank terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu kas/bank.",
					"Confirm" => "Apakah anda mau menghapus data kas/bank yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_m_kasbank AS a";
			$settings["where"] = "a.is_deleted = 0 AND a.company_id = " . $this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

        private function ValidateData(KasBank $kasbank) {
		if ($kasbank->BankName == null) {
			$this->Set("error", "Mohon memasukkan nama kas/bank terlebih dahulu.");
			return false;
		}
		if ($kasbank->CurrencyCode == null) {
			$this->Set("error", "Mohon memasukkan mata uang rekening kas/bank terlebih dahulu.");
			return false;
		}
		if ($kasbank->TrxAccId == null) {
			$this->Set("error", "Mohon memilih akun kontrol terlebih dahulu.");
			return false;
		}

		if ($kasbank->CostAccId == "") {
			$kasbank->CostAccId = null;
		}
		if ($kasbank->RevAccId == "") {
			$kasbank->RevAccId = null;
		}

		return true;
	}

	public function add() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coadetail.php");
		require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/bank.php");
		$kasbank = new KasBank();
		$log = new UserAdmin();
		if (count($this->postData) > 0) {
			$kasbank->BankName = $this->GetPostValue("Name");
			$kasbank->Branch = $this->GetPostValue("Branch");
            $kasbank->AtsNama = $this->GetPostValue("AtsNama");
			$kasbank->Address = $this->GetPostValue("Address");
			$kasbank->NoRekening = $this->GetPostValue("NoRek");
			$kasbank->CurrencyCode = $this->GetPostValue("CurrencyCode");
			$kasbank->TrxAccId = $this->GetPostValue("TrxAccId");
			$kasbank->CostAccId = $this->GetPostValue("CostAccId");
			$kasbank->RevAccId = $this->GetPostValue("RevAccId");
            $kasbank->BankId = $this->GetPostValue("BankId");
			if ($this->ValidateData($kasbank)) {
				$kasbank->CompanyId = $this->userCompanyId;
				$kasbank->CabangId = $this->userCabangId;
				$kasbank->CreateById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $kasbank->Insert();
				if ($rs == 1) {
					$log = $log->UserActivityWriter($this->userCabangId,'master.kasbank','Add New Kas/Bank - Name: '.$kasbank->BankName,'-','Success');
					$this->persistence->SaveState("info", sprintf("Data kas/bank: %s (%s) sudah berhasil disimpan", $kasbank->BankName, $kasbank->Branch));
					redirect_url("master.kasbank");
				} else {
					$log = $log->UserActivityWriter($this->userCabangId,'master.kasbank','Add New Kas/Bank - Name: '.$kasbank->BankName,'-','Failed');
					$this->Set("error", "Gagal pada saat menyimpan data kas/bank. Message: " . $this->connector->GetErrorMessage());
				}
			}
		}
		$cabang = new Cabang();
		$cabang = $cabang->LoadById($this->userCabangId);
		$cabCode = $cabang->Kode;
		$accounts = new CoaDetail();
        $accounts = $accounts->LoadAll($this->userCompanyId);
		$this->Set("cabCode", $cabCode);
		$this->Set("kasbank", $kasbank);
		$this->Set("accounts", $accounts);
		$loader = new Bank();
		$banks = $loader->LoadAll();
        $this->Set("banks", $banks);
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Harap memilih kas/bank terlebih dahulu sebelum melakukan proses edit.");
			redirect_url("master.kasbank");
		}
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coadetail.php");
		require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/bank.php");
		$kasbank = new KasBank();
		$log = new UserAdmin();
		if (count($this->postData) > 0) {
			$kasbank->Id = $id;
			$kasbank->BankName = $this->GetPostValue("Name");
			$kasbank->Branch = $this->GetPostValue("Branch");
            $kasbank->AtsNama = $this->GetPostValue("AtsNama");
			$kasbank->Address = $this->GetPostValue("Address");
			$kasbank->NoRekening = $this->GetPostValue("NoRek");
			$kasbank->CurrencyCode = $this->GetPostValue("CurrencyCode");
			$kasbank->TrxAccId = $this->GetPostValue("TrxAccId");
			$kasbank->CostAccId = $this->GetPostValue("CostAccId");
			$kasbank->RevAccId = $this->GetPostValue("RevAccId");
            $kasbank->BankId = $this->GetPostValue("BankId");
			if ($this->ValidateData($kasbank)) {
				$kasbank->CompanyId = $this->userCompanyId;
				$kasbank->CabangId = $this->userCabangId;
				$kasbank->UpdateById = AclManager::GetInstance()->GetCurrentUser()->Id;
				$rs = $kasbank->Update($kasbank->Id);
				if ($rs == 1) {
					$log = $log->UserActivityWriter($this->userCabangId,'master.kasbank','Update Kas/Bank - Name: '.$kasbank->BankName,'-','Success');
					$this->persistence->SaveState("info", sprintf("Perubahan data kas/bank: %s (%s) sudah berhasil disimpan", $kasbank->BankName, $kasbank->Branch));
					redirect_url("master.kasbank");
				} else {
					$log = $log->UserActivityWriter($this->userCabangId,'master.kasbank','Update Kas/Bank - Name: '.$kasbank->BankName,'-','Failed');
					$this->Set("error", "Gagal pada saat merubah data kas/bank. Message: " . $this->connector->GetErrorMessage());
				}
			}
		} else {
			$kasbank = $kasbank->LoadById($id);
			if ($kasbank == null || $kasbank->IsDeleted) {
				$this->persistence->SaveState("error", "Maaf kas/bank yang diminta tidak dapat ditemukan atau sudah dihapus.");
				redirect_url("master.kasbank");
			}
		}
		$cabang = new Cabang();
		$cabang = $cabang->LoadById($this->userCabangId);
		$cabCode = $cabang->Kode;
		$accounts = new CoaDetail();
		$accounts = $accounts->LoadAll($this->userCompanyId);
		$this->Set("cabCode", $cabCode);
		$this->Set("kasbank", $kasbank);
		$this->Set("accounts", $accounts);
        $loader = new Bank();
        $banks = $loader->LoadAll();
        $this->Set("banks", $banks);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Marap memilih kas/bank terlebih dahulu sebelum melakukan proses penghapusan data.");
			redirect_url("master.kasbank");
		}
		$log = new UserAdmin();
		$kasbank = new KasBank();
		$kasbank = $kasbank->LoadById($id);
		if ($kasbank == null || $kasbank->IsDeleted) {
			$this->persistence->SaveState("error", "Maaf kas/bank yang diminta tidak dapat ditemukan atau sudah dihapus.");
			redirect_url("master.kasbank");
		}
		$rs = $kasbank->Delete($kasbank->Id);
		if ($rs == 1) {
			$log = $log->UserActivityWriter($this->userCabangId,'master.kasbank','Delete Kas/Bank - Name: '.$kasbank->BankName,'-','Success');
			$this->persistence->SaveState("info", sprintf("Data Kas/Bank: %s (%s) sudah berhasil dihapus", $kasbank->BankName, $kasbank->Branch));
		} else {
			$log = $log->UserActivityWriter($this->userCabangId,'master.kasbank','Delete Kas/Bank - Name: '.$kasbank->BankName,'-','Failed');
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data kas/bank: %s (%s). Error: %s", $kasbank->BankName, $kasbank->Branch, $this->connector->GetErrorMessage()));
		}

		redirect_url("master.kasbank");
	}
}

// End of file: bank_controller.php
