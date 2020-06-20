<?php
class SalesmanController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/salesman.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.sales_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.sales_name", "display" => "Nama Salesman", "width" => 150);
        $settings["columns"][] = array("name" => "if(a.is_aktif = 1,'Aktif','Non-Aktif')", "display" => "Status", "width" => 50);

		$settings["filters"][] = array("name" => "a.sales_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.sales_name", "display" => "Nama Salesman");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Salesman";

			if ($acl->CheckUserAccess("master.salesman", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.salesman/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.salesman", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.salesman/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih salesman terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu salesman.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.salesman", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.salesman/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih salesman terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu salesman.",
					"Confirm" => "Apakah anda mau menghapus data salesman yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_salesman AS a ";
            $settings["where"] = "a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Salesman $salesman) {

		return true;
	}

	public function add() {
	    $salesman = new Salesman();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $salesman->SalesCode = $this->GetPostValue("SalesCode");
            $salesman->SalesName = $this->GetPostValue("SalesName");
            $salesman->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($salesman)) {
                $salesman->CreatebyId = $this->userUid;
                $rs = $salesman->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesman','Add New Item Nama Salesman -> Nama Salesman: '.$salesman->SalesCode.' - '.$salesman->SalesName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Nama Salesman: %s (%s) sudah berhasil disimpan", $salesman->SalesName, $salesman->SalesCode));
                    redirect_url("master.salesman");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesman','Add New Item Nama Salesman -> Nama Salesman: '.$salesman->SalesCode.' - '.$salesman->SalesName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("salesman", $salesman);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.salesman");
        }
        $log = new UserAdmin();
        $salesman = new Salesman();
        if (count($this->postData) > 0) {
            $salesman->Id = $id;
            $salesman->SalesCode = $this->GetPostValue("SalesCode");
            $salesman->SalesName = $this->GetPostValue("SalesName");
            $salesman->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($salesman)) {
                $salesman->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $salesman->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesman','Update Item Nama Salesman -> Nama Salesman: '.$salesman->SalesCode.' - '.$salesman->SalesName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan Data Nama Salesman: %s (%s) sudah berhasil disimpan", $salesman->SalesName, $salesman->SalesCode));
                    redirect_url("master.salesman");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesman','Update Item Nama Salesman -> Nama Salesman: '.$salesman->SalesCode.' - '.$salesman->SalesName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah Data Nama Salesman. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $salesman = $salesman->LoadById($id);
            if ($salesman == null || $salesman->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.salesman");
            }
        }
        $this->Set("salesman", $salesman);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.salesman");
        }
        $log = new UserAdmin();
        $salesman = new Salesman();
        $salesman = $salesman->LoadById($id);
        if ($salesman == null || $salesman->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.salesman");
        }
        $rs = $salesman->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.salesman','Delete Item Nama Salesman -> Nama Salesman: '.$salesman->SalesCode.' - '.$salesman->SalesName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Nama Salesman Barang: %s (%s) sudah dihapus", $salesman->SalesName, $salesman->SalesCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.salesman','Delete Item Nama Salesman -> Nama Salesman: '.$salesman->SalesCode.' - '.$salesman->SalesName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $salesman->SalesName, $salesman->SalesCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.salesman");
	}
}

// End of file: salesman_controller.php
