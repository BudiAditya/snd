<?php
class SalesAreaController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "master/salesarea.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.area_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.area_name", "display" => "Nama Area", "width" => 150);
        $settings["columns"][] = array("name" => "d.name", "display" => "Zone Harga", "width" => 100);
        $settings["columns"][] = array("name" => "b.name", "display" => "Kota", "width" => 150);
        $settings["columns"][] = array("name" => "c.name", "display" => "Propinsi", "width" => 150);

		$settings["filters"][] = array("name" => "a.area_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.area_name", "display" => "Nama Area");
        $settings["filters"][] = array("name" => "d.name", "display" => "Zone Harga");
        $settings["filters"][] = array("name" => "b.name", "display" => "Kota");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Sales Area";

			if ($acl->CheckUserAccess("master.salesarea", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "master.salesarea/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("master.salesarea", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "master.salesarea/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih salesarea terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu salesarea.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("master.salesarea", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "master.salesarea/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih salesarea terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu salesarea.",
					"Confirm" => "Apakah anda mau menghapus data salesarea yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
		    $sql = "m_sales_area AS a Join m_city b On a.city_id = b.id Join m_province c ON b.province_id = c.id Join m_zone d On a.zone_id = d.id";
			$settings["from"] = $sql;
            $settings["where"] = " a.company_id = ".$this->userCompanyId." And a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(SalesArea $salesarea) {

		return true;
	}

	public function add() {
        $salesarea = new SalesArea();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $salesarea->CompanyId = $this->userCompanyId;
            $salesarea->AreaCode = $this->GetPostValue("AreaCode");
            $salesarea->AreaName = $this->GetPostValue("AreaName");
            $salesarea->CityId = $this->GetPostValue("CityId");
            $salesarea->ZoneId = $this->GetPostValue("ZoneId");
            if ($this->ValidateData($salesarea)) {
                $salesarea->CreatebyId = $this->userUid;
                $rs = $salesarea->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesarea','Add New Item Jenis -> Jenis: '.$salesarea->AreaCode.' - '.$salesarea->AreaName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Jenis: %s (%s) sudah berhasil disimpan", $salesarea->AreaName, $salesarea->AreaCode));
                    redirect_url("master.salesarea");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesarea','Add New Item Jenis -> Jenis: '.$salesarea->AreaCode.' - '.$salesarea->AreaName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new SalesArea();
        $citylist = $loader->getCityList();
        $loader = new SalesArea();
        $zonelist = $loader->getZoneList();
        $this->Set("citylist", $citylist);
        $this->Set("zonelist", $zonelist);
        $this->Set("salesarea", $salesarea);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("master.salesarea");
        }
        $log = new UserAdmin();
        $salesarea = new SalesArea();
        if (count($this->postData) > 0) {
            $salesarea->Id = $id;
            $salesarea->CompanyId = $this->userCompanyId;
            $salesarea->AreaCode = $this->GetPostValue("AreaCode");
            $salesarea->AreaName = $this->GetPostValue("AreaName");
            $salesarea->CityId = $this->GetPostValue("CityId");
            $salesarea->ZoneId = $this->GetPostValue("ZoneId");
            if ($this->ValidateData($salesarea)) {
                $salesarea->UpdatebyId = $this->userUid;
                $rs = $salesarea->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesarea','Update Item Jenis -> Jenis: '.$salesarea->AreaCode.' - '.$salesarea->AreaName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $salesarea->AreaName, $salesarea->AreaCode));
                    redirect_url("master.salesarea");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'master.salesarea','Update Item Jenis -> Jenis: '.$salesarea->AreaCode.' - '.$salesarea->AreaName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $salesarea = $salesarea->LoadById($id);
            if ($salesarea == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("master.salesarea");
            }
        }
        $loader = new SalesArea();
        $citylist = $loader->getCityList();
        $loader = new SalesArea();
        $zonelist = $loader->getZoneList();
        $this->Set("citylist", $citylist);
        $this->Set("zonelist", $zonelist);
        $this->Set("salesarea", $salesarea);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("master.salesarea");
        }
        $log = new UserAdmin();
        $salesarea = new SalesArea();
        $salesarea = $salesarea->LoadById($id);
        if ($salesarea == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("master.salesarea");
        }
        $rs = $salesarea->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'master.salesarea','Delete Item Jenis -> Jenis: '.$salesarea->AreaCode.' - '.$salesarea->AreaName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Jenis Barang: %s (%s) sudah dihapus", $salesarea->AreaName, $salesarea->AreaCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'master.salesarea','Delete Item Jenis -> Jenis: '.$salesarea->AreaCode.' - '.$salesarea->AreaName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis barang: %s (%s). Error: %s", $salesarea->AreaName, $salesarea->AreaCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("master.salesarea");
	}
}

// End of file: salesarea_controller.php
