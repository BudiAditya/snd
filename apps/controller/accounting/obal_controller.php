<?php

class ObalController extends AppController {
	private $userCompanyId;
	private $userCabangId;
    private $userTrxYear;
	protected function Initialize() {
		require_once(MODEL . "accounting/opening_balance.php");
		require_once(MODEL . "master/user_admin.php");
		$this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userTrxYear = $this->persistence->LoadState("acc_year");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "b.kode", "display" => "Kode Akun", "width" => 80);
		$settings["columns"][] = array("name" => "b.perkiraan", "display" => "Nama Perkiraan", "width" => 250);
		$settings["columns"][] = array("name" => "a.op_date", "display" => "Tanggal", "width" => 70);
		$settings["columns"][] = array("name" => "FORMAT(a.db_amount, 2)", "display" => "Debet", "width" => 100, "align" => "right");
		$settings["columns"][] = array("name" => "FORMAT(a.cr_amount, 2)", "display" => "Kredit", "width" => 100, "align" => "right");

		$settings["filters"][] = array("name" => "b.kode", "display" => "Kode Akun");
		$settings["filters"][] = array("name" => "b.perkiraan", "display" => "Nama Perkiraan");
		$settings["filters"][] = array("name" => "DATE_FORMAT(a.op_date, '%Y')", "display" => "Tahun", "numeric" => true);
		$settings["filters"][] = array("name" => "DATE_FORMAT(a.op_date, '%m')", "display" => "Bulan", "numeric" => true);

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Saldo Awal Akuntansi";
			if ($acl->CheckUserAccess("obal", "add", "accounting")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "accounting.obal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("obal", "edit", "accounting")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "accounting.obal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
											   "Error" => "Harap memilih opening balance sebelum proses edit !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
											   "Confirm" => "");
			}
			if ($acl->CheckUserAccess("obal", "delete", "accounting")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "accounting.obal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
											   "Error" => "Harap memilih opening balance sebelum proses delete !\nPERHATIAN: Harap memilih tepat 1 data opening balance.",
											   "Confirm" => "Apakah anda yakin mau menghapus data opening balance yang dipilih ?\nKlik 'OK' untuk melanjutkan prosedur delete.");
			}

			$settings["def_filter"] = 0;
			$settings["def_order"] = 1;
			$settings["singleSelect"] = false;
		} else {
			$settings["from"]  = "t_ac_saldoawal AS a JOIN m_account AS b ON a.acc_id = b.id";
			$settings["where"] = "a.company_id = $this->userCompanyId And Year(a.op_date) = $this->userTrxYear";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add() {
		require_once(MODEL . "master/coadetail.php");
		$log = new UserAdmin();
		$openingBalance = new OpeningBalance();
		if (count($this->postData) > 0) {
			$month = $this->GetPostValue("Month", 1);
			$year = $this->userTrxYear;
			$openingBalance->AccId = $this->GetPostValue("AccId");
			$openingBalance->OpDate = mktime(0, 0, 0, $month, 1, $year);
			$openingBalance->DbAmount = str_replace(",","", $this->GetPostValue("DbAmount"));
			$openingBalance->CrAmount = str_replace(",","", $this->GetPostValue("CrAmount"));
			$openingBalance->CompanyId = $this->userCompanyId;
			$openingBalance->CabangId = $this->userCabangId;
			if ($this->ValidateData($openingBalance)) {
				$openingBalance->CreatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($openingBalance->Insert() == 1) {
					$log = $log->UserActivityWriter($this->userCabangId,'accounting.obal','Add New Opening Balance Date: '.date('Y-m-d',$openingBalance->OpDate).' Acc ID: '.$openingBalance->AccId.' Debit: '.$openingBalance->DbAmount.' Credit: '.$openingBalance->CrAmount,'-','Success');
					$this->persistence->SaveState("info", sprintf("Opening balance periode %s sudah disimpan. Debet: %s Kredit: %s", $openingBalance->FormatDate(), number_format($openingBalance->DbAmount, 2), number_format($openingBalance->CrAmount)));
					redirect_url("accounting.obal");
				} else {
					$log = $log->UserActivityWriter($this->userCabangId,'accounting.obal','Add New Opening Balance Date: '.date('Y-m-d',$openingBalance->OpDate).' Acc ID: '.$openingBalance->AccId.' Debit: '.$openingBalance->DbAmount.' Credit: '.$openingBalance->CrAmount,'-','Failed');
					if ($this->connector->GetHasError()) {
						if ($this->connector->IsDuplicateError()) {
							$this->Set("error", "Maaf data opening balance pada periode yang diminta sudah ada.");
						} else {
							$this->Set("error", "Database error: " . $this->connector->GetErrorMessage());
						}
					}
				}
			}
		} else {
			$openingBalance->OpDate = time();
			$openingBalance->DbAmount = 0;
			$openingBalance->CrAmount = 0;
		}

		$account = new CoaDetail();
		//$parentAccounts = $account->LoadByType(2, false, true);
		//$accounts = $account->LoadType3ByFirstCode(array("1", "2", "3"));
        $accounts = $account->LoadAll($this->userCompanyId,$this->userCabangId);

		//$this->Set("parentAccounts", $parentAccounts);
		$this->Set("accounts", $accounts);
		$this->Set("openingBalance", $openingBalance);
	}

	private function ValidateData(OpeningBalance $openingBalance) {
		if ($openingBalance->AccId == null) {
			$this->Set("error", "Maaf anda harus memilih akun terlebih dahulu.");
			return false;
		}
		if (!is_int($openingBalance->OpDate)) {
			$this->Set("error", "Maaf anda harus memilih periode opening balance terlebih dahulu");
			return false;
		}
		if ($openingBalance->DbAmount < 0) {
			$this->Set("error", "Maaf untuk jumlah debet tidak bisa kurang dari 0");
			return false;
		}
		if ($openingBalance->CrAmount < 0) {
			$this->Set("error", "Maaf untuk jumlah kredit tidak bisa kurang dari 0");
			return false;
		}
		if ($openingBalance->DbAmount == 0 && $openingBalance->CrAmount == 0) {
			$this->Set("error", "Maaf Debet dan Kredit bernilai 0. Tidak boleh bernilai 0 untuk kedua field tersebut");
			return false;
		}

		return true;
	}

	public function edit($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih data opening balance terlebih dahulu.");
			redirect_url("accounting.obal");
			return;
		}
		require_once(MODEL . "master/coadetail.php");
		$log = new UserAdmin();
		$openingBalance = new OpeningBalance();
		if (count($this->postData) > 0) {
			$month = $this->GetPostValue("Month", 1);
			$year = $this->userTrxYear;
			$openingBalance->Id = $id;
			$openingBalance->AccId = $this->GetPostValue("AccId");
			$openingBalance->OpDate = mktime(0, 0, 0, $month, 1, $year);
			$openingBalance->DbAmount = str_replace(",","", $this->GetPostValue("DbAmount"));
			$openingBalance->CrAmount = str_replace(",","", $this->GetPostValue("CrAmount"));
			$openingBalance->CompanyId = $this->userCompanyId;
			$openingBalance->CabangId = $this->userCabangId;
			if ($this->ValidateData($openingBalance)) {
				$openingBalance->UpdatedById = AclManager::GetInstance()->GetCurrentUser()->Id;

				if ($openingBalance->Update($openingBalance->Id) == 1) {
					$log = $log->UserActivityWriter($this->userCabangId,'accounting.obal','Update Opening Balance Date: '.date('Y-m-d',$openingBalance->OpDate).' Acc ID: '.$openingBalance->AccId.' Debit: '.$openingBalance->DbAmount.' Credit: '.$openingBalance->CrAmount,'-','Success');
					$this->persistence->SaveState("info", sprintf("Opening balance periode %s sudah disimpan. Debet: %s Kredit: %s", $openingBalance->FormatDate(), number_format($openingBalance->DbAmount, 2), number_format($openingBalance->CrAmount)));
					redirect_url("accounting.obal");
				} else {
					$log = $log->UserActivityWriter($this->userCabangId,'accounting.obal','Update Opening Balance Date: '.date('Y-m-d',$openingBalance->OpDate).' Acc ID: '.$openingBalance->AccId.' Debit: '.$openingBalance->DbAmount.' Credit: '.$openingBalance->CrAmount,'-','Failed');
					if ($this->connector->GetHasError()) {
						if ($this->connector->IsDuplicateError()) {
							$this->Set("error", "Maaf data opening balance pada periode yang diminta sudah ada.");
						} else {
							$this->Set("error", "Database error: " . $this->connector->GetErrorMessage());
						}
					}
				}
			}
		} else {
			$openingBalance = $openingBalance->LoadById($id);
			if ($openingBalance == null) {
				$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
				redirect_url("accounting.obal");
				return;
			}
		}

		$account = new CoaDetail();
		//$parentAccounts = $account->LoadByType(2, false, true);
		//$accounts = $account->LoadType3ByFirstCode(array("1", "2", "3"));
        $accounts = $account->LoadAll($this->userCompanyId,$this->userCabangId);

		//$this->Set("parentAccounts", $parentAccounts);
		$this->Set("accounts", $accounts);
		$this->Set("openingBalance", $openingBalance);
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Maaf anda harus memilih data opening balance terlebih dahulu.");
			redirect_url("accounting.obal");
			return;
		}
		$log = new UserAdmin();
		$openingBalance = new OpeningBalance();
		$openingBalance = $openingBalance->LoadById($id);
		if ($openingBalance == null) {
			$this->persistence->SaveState("error", "Maaf data opening balance yang diminta tidak dapat ditemukan.");
			redirect_url("accounting.obal");
			return;
		}

		$rs = $openingBalance->Delete($openingBalance->Id);
		if ($rs == 1) {
			$log = $log->UserActivityWriter($this->userCabangId,'accounting.obal','Delete Opening Balance Date: '.date('Y-m-d',$openingBalance->OpDate).' Acc ID: '.$openingBalance->AccId.' Debit: '.$openingBalance->DbAmount.' Credit: '.$openingBalance->CrAmount,'-','Success');
			$this->persistence->SaveState("info", sprintf("Opening Balance %s periode %s sudah dihapus", $openingBalance->AccId, $openingBalance->FormatDate("F Y")));
		} else {
			$log = $log->UserActivityWriter($this->userCabangId,'accounting.obal','Delete Opening Balance Date: '.date('Y-m-d',$openingBalance->OpDate).' Acc ID: '.$openingBalance->AccId.' Debit: '.$openingBalance->DbAmount.' Credit: '.$openingBalance->CrAmount,'-','Failed');
			$this->persistence->SaveState("info", sprintf("Gagal hapus opening balance %s periode %s. Message: %s", $openingBalance->AccId, $openingBalance->FormatDate("F Y"), $this->connector->GetErrorMessage()));
		}
        redirect_url("accounting.obal");
	}
}


// End of File: obal_controller.php
