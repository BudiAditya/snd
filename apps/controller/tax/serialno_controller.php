<?php
class SerialNoController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

	protected function Initialize() {
		require_once(MODEL . "tax/serialno.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.tax_year", "display" => "Tahun", "width" => 50);
		$settings["columns"][] = array("name" => "a.sn_prefix", "display" => "Prefix", "width" => 50);
        $settings["columns"][] = array("name" => "a.sn_start", "display" => "Mulai Nomor", "width" => 100);
        $settings["columns"][] = array("name" => "a.sn_end", "display" => "S/D Nomor", "width" => 100);
        $settings["columns"][] = array("name" => "a.sn_next_counter", "display" => "Berikutnya", "width" => 100);
        $settings["columns"][] = array("name" => "a.sn_end - a.sn_next_counter", "display" => "Sisa", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "if (a.is_aktif = 1,'Aktif','Non-Aktif')", "display" => "Status", "width" => 50);

		$settings["filters"][] = array("name" => "a.tax_year", "display" => "Tahun");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Nomor Seri Faktur Pajak";

			if ($acl->CheckUserAccess("tax.serialno", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "tax.serialno/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("tax.serialno", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "tax.serialno/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih serialno terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu serialno.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("tax.serialno", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "tax.serialno/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih serialno terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu serialno.",
					"Confirm" => "Apakah anda mau menghapus data serialno yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 3;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_fp_serialno AS a ";
            $settings["where"] = "a.tax_year = $this->trxYear And a.company_id = $this->userCompanyId";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(SerialNo $serialno) {
		return true;
	}

	public function add() {
	    $serialno = new SerialNo();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $serialno->CompanyId = $this->userCompanyId;
            $serialno->TaxYear = $this->GetPostValue("TaxYear");
            $serialno->SnPrefix = $this->GetPostValue("SnPrefix");
            $serialno->SnStart = $this->GetPostValue("SnStart");
            $serialno->SnEnd = $this->GetPostValue("SnEnd");
            $serialno->SnNextCounter = $this->GetPostValue("SnNextCounter");
            $serialno->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($serialno)) {
                $serialno->CreatebyId = $this->userUid;
                $rs = $serialno->Insert();
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'tax.serialno','Add New Tax S/N: '.$serialno->TaxYear.' - '.$serialno->SnPrefix,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Tax S/N: %s (%s) sudah berhasil disimpan", $serialno->SnPrefix, $serialno->TaxYear));
                    redirect_url("tax.serialno");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'tax.serialno','Add New Tax S/N: '.$serialno->TaxYear.' - '.$serialno->SnPrefix,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $serialno->TaxYear = $this->trxYear;
        }
        $this->Set("serialno", $serialno);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("tax.serialno");
        }
        $log = new UserAdmin();
        $serialno = new SerialNo();
        if (count($this->postData) > 0) {
            $serialno->Id = $id;
            $serialno->CompanyId = $this->userCompanyId;
            $serialno->TaxYear = $this->GetPostValue("TaxYear");
            $serialno->SnPrefix = $this->GetPostValue("SnPrefix");
            $serialno->SnStart = $this->GetPostValue("SnStart");
            $serialno->SnEnd = $this->GetPostValue("SnEnd");
            $serialno->SnNextCounter = $this->GetPostValue("SnNextCounter");
            $serialno->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($serialno)) {
                $serialno->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $serialno->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'tax.serialno','Update Tax S/N: '.$serialno->TaxYear.' - '.$serialno->SnPrefix,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Tax S/N: %s (%s) sudah berhasil disimpan", $serialno->SnPrefix, $serialno->TaxYear));
                    redirect_url("tax.serialno");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'tax.serialno','Update Tax S/N: '.$serialno->TaxYear.' - '.$serialno->SnPrefix,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Tax S/N. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $serialno = $serialno->LoadById($id);
            if ($serialno == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("tax.serialno");
            }
        }
        $this->Set("serialno", $serialno);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("tax.serialno");
        }
        $log = new UserAdmin();
        $serialno = new SerialNo();
        $serialno = $serialno->LoadById($id);
        if ($serialno == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("tax.serialno");
        }
        $rs = $serialno->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'tax.serialno','Delete Tax S/N: '.$serialno->TaxYear.' - '.$serialno->SnPrefix,'-','Success');
            $this->persistence->SaveState("info", sprintf("Tax S/N: %s (%s) sudah dihapus", $serialno->SnPrefix, $serialno->TaxYear));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'tax.serialno','Delete Tax S/N: '.$serialno->TaxYear.' - '.$serialno->SnPrefix,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $serialno->SnPrefix, $serialno->TaxYear, $this->connector->GetErrorMessage()));
        }
		redirect_url("tax.serialno");
	}
}

// End of file: serialno_controller.php
