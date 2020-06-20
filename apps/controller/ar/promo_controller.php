<?php
class PromoController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "ar/promo.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.jenis_promo", "display" => "Type Promo", "width" => 100);
		$settings["columns"][] = array("name" => "a.promo_descs", "display" => "Nama Promo", "width" => 250);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 60);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 200);
        $settings["columns"][] = array("name" => "a.min_qty", "display" => "Min Qty", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.min_amount", "display" => "Min Rp", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.disc_pct", "display" => "Disc %", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.poin", "display" => "Poin", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.qty_bonus", "display" => "Bonus", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.start_date", "display" => "Mulai", "width" => 60);
        $settings["columns"][] = array("name" => "a.end_date", "display" => "Sampai", "width" => 60);
        $settings["columns"][] = array("name" => "if(a.is_aktif = 0,'Non-Aktif','Aktif')", "display" => "Status", "width" => 50);

		$settings["filters"][] = array("name" => "a.jenis_promo", "display" => "Type Promo");
		$settings["filters"][] = array("name" => "a.item_code", "display" => "Kode Barang");
        $settings["filters"][] = array("name" => "a.item_name", "display" => "Nama Barang");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Promo dan Program Penjualan";

			if ($acl->CheckUserAccess("ar.promo", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ar.promo/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ar.promo", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ar.promo/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih promo terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu promo.",
					"Confirm" => "");
			}
            if ($acl->CheckUserAccess("ar.promo", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ar.promo/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Mohon memilih promo terlebih dahulu sebelum proses view.\nPERHATIAN: Mohon memilih tepat satu promo.",
                    "Confirm" => "");
            }
			if ($acl->CheckUserAccess("ar.promo", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ar.promo/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih promo terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu promo.",
					"Confirm" => "Apakah anda mau menghapus data promo yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
		    $settings["from"] = "vw_ar_promo AS a";
            $settings["where"] = " a.cabang_id = ".$this->userCabangId." And a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Promo $promo) {

		return true;
	}

	public function add() {
        require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/salesarea.php");
        require_once(MODEL . "inventory/items.php");
        $promo = new Promo();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $promo->CabangId = $this->userCabangId;
            $promo->PromoType = $this->GetPostValue("PromoType");
            $promo->PromoDescs = $this->GetPostValue("PromoDescs");
            $promo->ItemId = $this->GetPostValue("ItemId");
            $promo->CustypeId = $this->GetPostValue("CustypeId");
            $promo->ZoneId = $this->GetPostValue("ZoneId");
            $promo->AreaId = $this->GetPostValue("AreaId");
            $promo->StartDate = $this->GetPostValue("StartDate");
            $promo->EndDate = $this->GetPostValue("EndDate");
            $promo->MinQty = $this->GetPostValue("MinQty");
            $promo->MaxQty = $this->GetPostValue("MaxQty");
            $promo->MinAmount = $this->GetPostValue("MinAmount");
            $promo->MaxAmount = $this->GetPostValue("MaxAmount");
            $promo->DiscPct = $this->GetPostValue("DiscPct");
            $promo->Poin = $this->GetPostValue("Poin");
            $promo->QtyBonus = $this->GetPostValue("QtyBonus");
            $promo->ItemIdBonus = $this->GetPostValue("ItemIdBonus");
            $promo->IsKelipatan = $this->GetPostValue("IsKelipatan");
            $promo->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($promo)) {
                $promo->CreatebyId = $this->userUid;
                $rs = $promo->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.promo','Add New Promo -> Type: '.$promo->PromoType.' - '.$promo->PromoDescs,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Type: %s (%s) sudah berhasil disimpan", $promo->PromoDescs, $promo->PromoType));
                    redirect_url("ar.promo");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.promo','Add New Promo -> Type: '.$promo->PromoType.' - '.$promo->PromoDescs,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new SalesArea();
        $arealists = $loader->LoadByCompanyId($this->userCompanyId);
        $zonelists = $loader->getZoneList();
        $this->Set("arealists", $arealists);
        $this->Set("zonelists", $zonelists);
        $loader = new Items();
        $itemlists = $loader->LoadAll($this->userCompanyId,$this->userCabangId);
        $this->Set("itemlists", $itemlists);
        $loader = new Promo();
        $typelists = $loader->getPromoTypeList();
        $this->Set("typelists", $typelists);
        $loader = new CusType();
        $custypes = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("custypes", $custypes);
        $this->Set("promo", $promo);
	}

	public function edit($id = null) {
        require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/salesarea.php");
        require_once(MODEL . "inventory/items.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ar.promo");
        }
        $log = new UserAdmin();
        $promo = new Promo();
        if (count($this->postData) > 0) {
            $promo->Id = $id;
            $promo->CabangId = $this->userCabangId;
            $promo->PromoType = $this->GetPostValue("PromoType");
            $promo->PromoDescs = $this->GetPostValue("PromoDescs");
            $promo->ItemId = $this->GetPostValue("ItemId");
            $promo->CustypeId = $this->GetPostValue("CustypeId");
            $promo->ZoneId = $this->GetPostValue("ZoneId");
            $promo->AreaId = $this->GetPostValue("AreaId");
            $promo->StartDate = $this->GetPostValue("StartDate");
            $promo->EndDate = $this->GetPostValue("EndDate");
            $promo->MinQty = $this->GetPostValue("MinQty");
            $promo->MaxQty = $this->GetPostValue("MaxQty");
            $promo->MinAmount = $this->GetPostValue("MinAmount");
            $promo->MaxAmount = $this->GetPostValue("MaxAmount");
            $promo->DiscPct = $this->GetPostValue("DiscPct");
            $promo->Poin = $this->GetPostValue("Poin");
            $promo->QtyBonus = $this->GetPostValue("QtyBonus");
            $promo->ItemIdBonus = $this->GetPostValue("ItemIdBonus");
            $promo->IsKelipatan = $this->GetPostValue("IsKelipatan");
            $promo->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($promo)) {
                $promo->UpdatebyId = $this->userUid;
                $rs = $promo->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.promo','Update Promo -> Type: '.$promo->PromoType.' - '.$promo->PromoDescs,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data promo: %s (%s) sudah berhasil disimpan", $promo->PromoDescs, $promo->PromoType));
                    redirect_url("ar.promo");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.promo','Update Promo -> Type: '.$promo->PromoType.' - '.$promo->PromoDescs,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data promo. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $promo = $promo->LoadById($id);
            if ($promo == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("ar.promo");
            }
        }
        $loader = new SalesArea();
        $arealists = $loader->LoadByCompanyId($this->userCompanyId);
        $zonelists = $loader->getZoneList();
        $this->Set("arealists", $arealists);
        $this->Set("zonelists", $zonelists);
        $loader = new Items();
        $itemlists = $loader->LoadAll($this->userCompanyId,$this->userCabangId);
        $this->Set("itemlists", $itemlists);
        $loader = new Promo();
        $typelists = $loader->getPromoTypeList();
        $this->Set("typelists", $typelists);
        $loader = new CusType();
        $custypes = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("custypes", $custypes);
        $this->Set("promo", $promo);
	}

    public function view($id = null) {
        require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/salesarea.php");
        require_once(MODEL . "inventory/items.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ar.promo");
        }
        $log = new UserAdmin();
        $promo = new Promo();
        $promo = $promo->LoadById($id);
        if ($promo == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ar.promo");
        }
        $loader = new SalesArea();
        $arealists = $loader->LoadByCompanyId($this->userCompanyId);
        $zonelists = $loader->getZoneList();
        $this->Set("arealists", $arealists);
        $this->Set("zonelists", $zonelists);
        $loader = new Items();
        $itemlists = $loader->LoadAll($this->userCompanyId,$this->userCabangId);
        $this->Set("itemlists", $itemlists);
        $loader = new Promo();
        $typelists = $loader->getPromoTypeList();
        $this->Set("typelists", $typelists);
        $loader = new CusType();
        $custypes = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("custypes", $custypes);
        $this->Set("promo", $promo);
    }

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("ar.promo");
        }
        $log = new UserAdmin();
        $promo = new Promo();
        $promo = $promo->LoadById($id);
        if ($promo == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ar.promo");
        }
        $rs = $promo->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.promo','Delete Promo -> Type: '.$promo->PromoType.' - '.$promo->PromoDescs,'-','Success');
            $this->persistence->SaveState("info", sprintf("Type Promo: %s (%s) sudah dihapus", $promo->PromoDescs, $promo->PromoType));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.promo','Delete Promo -> Type: '.$promo->PromoType.' - '.$promo->PromoDescs,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus promo: %s (%s). Error: %s", $promo->PromoDescs, $promo->PromoType, $this->connector->GetErrorMessage()));
        }
		redirect_url("ar.promo");
	}
}

// End of file: promo_controller.php
