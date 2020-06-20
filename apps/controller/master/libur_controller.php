<?php
class LiburController extends AppController {
	private $userUid;
    private $userCabangId;
    private $trxYear;

	protected function Initialize() {
		require_once(MODEL . "master/libur.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->trxYear = $this->persistence->LoadState("acc_year");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.tgl_libur", "display" => "Tanggal", "width" => 80);
		$settings["columns"][] = array("name" => "if(a.jns_libur = 1,'Nasional','Perusahaan')", "display" => "Jenis", "width" => 100);
        $settings["columns"][] = array("name" => "a.keterangan", "display" => "Keterangan", "width" => 250);

		$settings["filters"][] = array("name" => "a.tgl_libur", "display" => "Tanggal");
		$settings["filters"][] = array("name" => "if(a.jns_libur = 1,'Nasional','Perusahaan')", "display" => "Jenis");
        $settings["filters"][] = array("name" => "a.keterangan", "display" => "Keterangan");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Data Hari Libur";

			if ($acl->CheckUserAccess("master.libur", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.libur/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.libur", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.libur/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih data terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu data.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.libur", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.libur/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih data terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu libur.",
					"Confirm" => "Apakah anda mau menghapus data gudang yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 2;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "m_libur AS a";
            $settings["where"] = "Year(a.tgl_libur) = ".$this->trxYear;
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Libur $libur) {

		return true;
	}

	public function add() {
        $libur = new Libur();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $libur->TglLibur = $this->GetPostValue("TglLibur");
            $libur->JnsLibur = $this->GetPostValue("JnsLibur");
            $libur->Keterangan = $this->GetPostValue("Keterangan");
            if ($this->ValidateData($libur)) {
                $libur->CreatebyId = $this->userUid;
                $rs = $libur->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.libur','Add New Item TglLibur -> TglLibur: '.$libur->TglLibur.' - '.$libur->JnsLibur,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data TglLibur: %s (%s) sudah berhasil disimpan", $libur->JnsLibur, $libur->TglLibur));
                    redirect_url("master.libur");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.libur','Add New Item TglLibur -> TglLibur: '.$libur->TglLibur.' - '.$libur->JnsLibur,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $this->Set("libur", $libur);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.libur");
        }
        $log = new UserAdmin();
        $libur = new Libur();
        if (count($this->postData) > 0) {
            $libur->Id = $id;
            $libur->TglLibur = $this->GetPostValue("TglLibur");
            $libur->JnsLibur = $this->GetPostValue("JnsLibur");
            $libur->Keterangan = $this->GetPostValue("Keterangan");
            if ($this->ValidateData($libur)) {
                $libur->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $libur->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.libur','Update Item TglLibur -> TglLibur: '.$libur->TglLibur.' - '.$libur->JnsLibur,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $libur->JnsLibur, $libur->TglLibur));
                    redirect_url("master.libur");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.libur','Update Item TglLibur -> TglLibur: '.$libur->TglLibur.' - '.$libur->JnsLibur,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $libur = $libur->LoadById($id);
            if ($libur == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.libur");
            }
        }
        $this->Set("libur", $libur);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.libur");
        }
        $log = new UserAdmin();
        $libur = new Libur();
        $libur = $libur->LoadById($id);
        if ($libur == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.libur");
        }
        $rs = $libur->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.libur','Delete Item TglLibur -> TglLibur: '.$libur->TglLibur.' - '.$libur->JnsLibur,'-','Success');
            $this->persistence->SaveState("info", sprintf("TglLibur Barang: %s (%s) sudah dihapus", $libur->JnsLibur, $libur->TglLibur));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.libur','Delete Item TglLibur -> TglLibur: '.$libur->TglLibur.' - '.$libur->JnsLibur,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $libur->JnsLibur, $libur->TglLibur, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.libur");
	}
}

// End of file: libur_controller.php
