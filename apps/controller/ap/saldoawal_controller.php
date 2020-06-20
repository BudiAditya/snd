<?php
class SaldoAwalController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $trxYear;

	protected function Initialize() {
		require_once(MODEL . "ap/saldoawal.php");
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
        $settings["columns"][] = array("name" => "a.op_no", "display" => "No. Bukti", "width" => 100);
		$settings["columns"][] = array("name" => "concat(b.sup_code,' - ',b.sup_name)", "display" => "Nama Supplier", "width" => 300);
        $settings["columns"][] = array("name" => "format(a.op_amount,2)", "display" => "Hutang", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.paid_amount,2)", "display" => "Terbayar", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.op_amount - a.paid_amount,2)", "display" => "Outstanding", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "if (a.op_status = 0,'Draft','Posted')", "display" => "Status", "width" => 100);

		$settings["filters"][] = array("name" => "concat(b.sup_code,' - ',b.sup_name)", "display" => "Nama Supplier");
		$settings["filters"][] = array("name" => "a.op_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "if (a.op_status = 0,'Draft','Posted')", "display" => "Status");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Saldo Awal Hutang Supplier";

			if ($acl->CheckUserAccess("ap.saldoawal", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ap.saldoawal/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ap.saldoawal", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ap.saldoawal/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih saldoawal terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu saldoawal.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ap.saldoawal", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ap.saldoawal/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih saldoawal terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu data.",
					"Confirm" => "Apakah anda mau menghapus data saldoawal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "t_ap_saldoawal AS a JOIN m_supplier AS b ON a.supplier_id = b.id";
            $settings["where"] = "a.is_deleted = 0 And Year(a.op_date) = ".$this->trxYear." And a.company_id = ".$this->userCompanyId;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(SaldoAwal $saldoawal) {
        if ($saldoawal->OpAmount == 0 || $saldoawal->OpAmount == null || $saldoawal->OpAmount == ''){
            $this->persistence->SaveState("error", "Saldo Hutang belum diisi!");
            return false;
        }
        if ($saldoawal->SupplierId == 0 || $saldoawal->SupplierId == null || $saldoawal->SupplierId == ''){
            $this->persistence->SaveState("error", "Supplier belum diisi!");
            return false;
        }
		return true;
	}

	public function add() {
	    require_once (MODEL . "ap/supplier.php");
	    $saldoawal = new SaldoAwal();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $saldoawal->CompanyId = $this->userCompanyId;
            $saldoawal->SupplierId = $this->GetPostValue("SupplierId");
            $saldoawal->OpDate = $this->GetPostValue("OpDate");
            $saldoawal->OpAmount = $this->GetPostValue("OpAmount");
            $saldoawal->OpStatus = 0;
            if ($this->ValidateData($saldoawal)) {
                $saldoawal->CreatebyId = $this->userUid;
                $saldoawal->OpNo = $saldoawal->GetOpNo();
                $rs = $saldoawal->Insert();
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.saldoawal','Add Saldo Awal Hutang -> Saldo Awal Hutang: '.$saldoawal->SupplierId.' - '.$saldoawal->OpDate,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Saldo Awal Hutang: %s (%s) sudah berhasil disimpan", $saldoawal->OpDate, $saldoawal->SupplierId));
                    redirect_url("ap.saldoawal");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.saldoawal','Add Saldo Awal Hutang -> Saldo Awal Hutang: '.$saldoawal->SupplierId.' - '.$saldoawal->OpDate,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $saldoawal->OpDate = $this->trxYear.'-01-01';
        }
        //get data supplier
        $loader = new Supplier();
        $sups = $loader->LoadAll();
        $this->Set("suppliers", $sups);
        $this->Set("saldoawal", $saldoawal);
	}

	public function edit($id = null) {
        require_once (MODEL . "ap/supplier.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ap.saldoawal");
        }
        $log = new UserAdmin();
        $saldoawal = new SaldoAwal();
        if (count($this->postData) > 0) {
            $saldoawal->Id = $id;
            $saldoawal->CompanyId = $this->userCompanyId;
            $saldoawal->SupplierId = $this->GetPostValue("SupplierId");
            $saldoawal->OpDate = $this->GetPostValue("OpDate");
            $saldoawal->OpAmount = $this->GetPostValue("OpAmount");
            if ($this->ValidateData($saldoawal)) {
                $saldoawal->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $saldoawal->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.saldoawal','Update Item Saldo Awal Hutang -> Saldo Awal Hutang: '.$saldoawal->SupplierId.' - '.$saldoawal->OpDate,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Saldo Awal Hutang: %s (%s) sudah berhasil disimpan", $saldoawal->OpDate, $saldoawal->SupplierId));
                    redirect_url("ap.saldoawal");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.saldoawal','Update Item Saldo Awal Hutang -> Saldo Awal Hutang: '.$saldoawal->SupplierId.' - '.$saldoawal->OpDate,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Saldo Awal Hutang. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $saldoawal = $saldoawal->LoadById($id);
            if ($saldoawal == null || $saldoawal->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("ap.saldoawal");
            }
        }
        //get data supplier
        $loader = new Supplier();
        $sups = $loader->LoadAll();
        $this->Set("suppliers", $sups);
        $this->Set("saldoawal", $saldoawal);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("ap.saldoawal");
        }
        $log = new UserAdmin();
        $saldoawal = new SaldoAwal();
        $saldoawal = $saldoawal->LoadById($id);
        if ($saldoawal == null || $saldoawal->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ap.saldoawal");
        }
        $rs = $saldoawal->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.saldoawal','Delete Item Saldo Awal Hutang -> Saldo Awal Hutang: '.$saldoawal->SupplierId.' - '.$saldoawal->OpDate,'-','Success');
            $this->persistence->SaveState("info", sprintf("Saldo Awal Hutang : %s (%s) sudah dihapus", $saldoawal->OpDate, $saldoawal->SupplierId));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.saldoawal','Delete Item Saldo Awal Hutang -> Saldo Awal Hutang: '.$saldoawal->SupplierId.' - '.$saldoawal->OpDate,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $saldoawal->OpDate, $saldoawal->SupplierId, $this->connector->GetErrorMessage()));
        }
		redirect_url("ap.saldoawal");
	}
}

// End of file: saldoawal_controller.php
