<?php
class AwalController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $trxYear;

	protected function Initialize() {
		require_once(MODEL . "cashbank/cbawal.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->trxYear = $this->persistence->LoadState("acc_year");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();
		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.op_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.bank_name", "display" => "Kas/Bank", "width" => 200);
		$settings["columns"][] = array("name" => "a.kode", "display" => "Kode", "width" => 70);
        $settings["columns"][] = array("name" => "a.perkiraan", "display" => "Perkiraan", "width" => 200);
        $settings["columns"][] = array("name" => "format(a.op_amount,2)", "display" => "Saldo", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "if (a.op_status = 1,'Posted',if (a.op_status = 2,'Approved','Void'))", "display" => "Status", "width" => 100);

		$settings["filters"][] = array("name" => "a.bank_name", "display" => "Kas/Bank");
		$settings["filters"][] = array("name" => "a.op_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "if (a.op_status = 1,'Posted',if (a.op_status = 2,'Approved','Void'))", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Saldo Awal Kas/Bank";

			if ($acl->CheckUserAccess("cashbank.awal", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "cashbank.awal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("cashbank.awal", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "cashbank.awal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih saldoawal terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu saldoawal.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("cashbank.awal", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "cashbank.awal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih saldoawal terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu data.",
					"Confirm" => "Apakah anda mau menghapus data saldoawal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_cb_saldoawal AS a";
            $settings["where"] = "a.is_deleted = 0 And Year(a.op_date) = ".$this->trxYear." And a.company_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(CbAwal $saldoawal) {
        if ($saldoawal->OpAmount == 0 || $saldoawal->OpAmount == null || $saldoawal->OpAmount == ''){
            $this->persistence->SaveState("error", "Saldo Kas/Bank belum diisi!");
            return false;
        }
        if ($saldoawal->BankId == 0 || $saldoawal->BankId == null || $saldoawal->BankId == ''){
            $this->persistence->SaveState("error", "Kas/Bank belum diisi!");
            return false;
        }
		return true;
	}

	public function add() {
	    require_once (MODEL . "master/kasbank.php");
	    $saldoawal = new CbAwal();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $saldoawal->CabangId = $this->userCabangId;
            $saldoawal->BankId = $this->GetPostValue("BankId");
            $saldoawal->OpDate = $this->GetPostValue("OpDate");
            $saldoawal->OpAmount = str_replace(",","", $this->GetPostValue("OpAmount"));
            $saldoawal->OpStatus = 1;
            if ($this->ValidateData($saldoawal)) {
                $saldoawal->CreateById = $this->userUid;
                $rs = $saldoawal->Insert();
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'cashbank.awal','Add Saldo Awal Kas/Bank -> Saldo Awal Kas/Bank: '.$saldoawal->BankId.' - '.$saldoawal->OpDate,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Saldo Awal Kas/Bank: %s (%s) sudah berhasil disimpan", $saldoawal->OpDate, $saldoawal->BankId));
                    redirect_url("cashbank.awal");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'cashbank.awal','Add Saldo Awal Kas/Bank -> Saldo Awal Kas/Bank: '.$saldoawal->BankId.' - '.$saldoawal->OpDate,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $saldoawal->OpDate = $this->trxYear.'-01-01';
        }
        //get data kas/bank
        $loader = new KasBank();
        $banks = $loader->LoadByCabangId($this->userCabangId,"a.trx_acc_code");
        $this->Set("banks", $banks);
        $this->Set("saldoawal", $saldoawal);
	}

	public function edit($id = null) {
        require_once (MODEL . "master/kasbank.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("cashbank.awal");
        }
        $log = new UserAdmin();
        $saldoawal = new CbAwal();
        if (count($this->postData) > 0) {
            $saldoawal->Id = $id;
            $saldoawal->CabangId = $this->userCabangId;
            $saldoawal->BankId = $this->GetPostValue("BankId");
            $saldoawal->OpDate = $this->GetPostValue("OpDate");
            $saldoawal->OpAmount = str_replace(",","", $this->GetPostValue("OpAmount"));
            if ($this->ValidateData($saldoawal)) {
                $saldoawal->UpdateById = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $saldoawal->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'cashbank.awal','Update Item Saldo Awal Kas/Bank -> Saldo Awal Kas/Bank: '.$saldoawal->BankId.' - '.$saldoawal->OpDate,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Saldo Awal Kas/Bank: %s (%s) sudah berhasil disimpan", $saldoawal->OpDate, $saldoawal->BankId));
                    redirect_url("cashbank.awal");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'cashbank.awal','Update Item Saldo Awal Kas/Bank -> Saldo Awal Kas/Bank: '.$saldoawal->BankId.' - '.$saldoawal->OpDate,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Saldo Awal Kas/Bank. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $saldoawal = $saldoawal->LoadById($id);
            if ($saldoawal == null || $saldoawal->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("cashbank.awal");
            }
        }
        //get data kas/bank
        $loader = new KasBank();
        $banks = $loader->LoadByCabangId($this->userCabangId,"a.trx_acc_code");
        $this->Set("banks", $banks);
        $this->Set("saldoawal", $saldoawal);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("cashbank.awal");
        }
        $log = new UserAdmin();
        $saldoawal = new CbAwal();
        $saldoawal = $saldoawal->LoadById($id);
        if ($saldoawal == null || $saldoawal->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("cashbank.awal");
        }
        $rs = $saldoawal->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'cashbank.awal','Delete Item Saldo Awal Kas/Bank -> Saldo Awal Kas/Bank: '.$saldoawal->BankId.' - '.$saldoawal->OpDate,'-','Success');
            $this->persistence->SaveState("info", sprintf("Saldo Awal Kas/Bank : %s (%s) sudah dihapus", $saldoawal->OpDate, $saldoawal->BankId));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'cashbank.awal','Delete Item Saldo Awal Kas/Bank -> Saldo Awal Kas/Bank: '.$saldoawal->BankId.' - '.$saldoawal->OpDate,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $saldoawal->OpDate, $saldoawal->BankId, $this->connector->GetErrorMessage()));
        }
		redirect_url("cashbank.awal");
	}
}

// End of file: saldoawal_controller.php
