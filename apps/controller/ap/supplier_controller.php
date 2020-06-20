<?php
class SupplierController extends AppController {
	private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "ap/supplier.php");
        require_once(MODEL . "master/user_admin.php");
		$this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
		$settings["columns"][] = array("name" => "b.type_name", "display" => "Type", "width" => 80);
        $settings["columns"][] = array("name" => "a.sup_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.sup_name", "display" => "Nama Supplier", "width" => 250);
		$settings["columns"][] = array("name" => "a.addr1", "display" => "Alamat 1", "width" => 350);
        $settings["columns"][] = array("name" => "a.addr2", "display" => "Alamat 2", "width" => 350);
        $settings["columns"][] = array("name" => "a.city", "display" => "Kota", "width" => 100);
        $settings["columns"][] = array("name" => "a.contact", "display" => "P I C", "width" => 100);
        $settings["columns"][] = array("name" => "a.hp", "display" => "No. HP", "width" => 150);

		$settings["filters"][] = array("name" => "a.sup_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.sup_name", "display" => "Nama Contact");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Master Data Supplier";

			if ($acl->CheckUserAccess("ap.supplier", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ap.supplier/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ap.supplier", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ap.supplier/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
						"Error" => "Maaf anda harus memilih data sup terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data sup",
						"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ap.supplier", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "ap.supplier/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih data sup terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data sup",
					"Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("ap.supplier", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ap.supplier/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih data sup terlebih dahulu sebelum proses hapus data.\nPERHATIAN: Pilih tepat 1 data sup",
					"Confirm" => "Apakah anda yakin mau menghapus data sup yang dipilih ? \n\n** Penghapusan Data akan mempengaruhi data transaksi yang berkaitan ** \n\nKlik 'OK' untuk melanjutkan prosedur");
			}
			$settings["def_order"] = 3;
			$settings["def_filter"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "m_supplier AS a JOIN m_supplier_type b ON a.suptype_id = b.id";
            $settings["where"] = "a.is_deleted = 0 and b.company_id = ".$this->userCompanyId;
		}
		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

    private function ValidateData(Supplier $supplier) {

        return true;
    }

    public function add() {
        require_once(MODEL . "ap/suptype.php");
        $supplier = new Supplier();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $supplier->CabangId = $this->userCabangId;
            $supplier->SupTypeId = $this->GetPostValue("SupTypeId");
            $supplier->SupCode = $this->GetPostValue("SupCode");
            $supplier->SupName = $this->GetPostValue("SupName");
            $supplier->Addr1 = $this->GetPostValue("Addr1");
            $supplier->Addr2 = $this->GetPostValue("Addr2");
            $supplier->PostCode  = $this->GetPostValue("PostCode");
            $supplier->City  = $this->GetPostValue("City");
            $supplier->Phone = $this->GetPostValue("Phone");
            $supplier->Fax = $this->GetPostValue("Fax");
            $supplier->Hp = $this->GetPostValue("Hp");
            $supplier->Contact = $this->GetPostValue("Contact");
            $supplier->Manager = $this->GetPostValue("Manager");
            $supplier->Npwp = $this->GetPostValue("Npwp");
            $supplier->Bank = $this->GetPostValue("Bank");
            $supplier->AccountNo = $this->GetPostValue("AccountNo");
            $supplier->IsPkp = $this->GetPostValue("IsPkp");
            $supplier->IsAktif = $this->GetPostValue("IsAktif");
            $supplier->IsPrincipal = $this->GetPostValue("IsPrincipal");
            $supplier->CreditLimit = $this->GetPostValue("CreditLimit");
            $supplier->Term = $this->GetPostValue("Term");
            if ($this->ValidateData($supplier)) {
                $supplier->CreatebyId = $this->userUid;
                $rs = $supplier->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.supplier','Add New Supplier -> Nama: '.$supplier->SupCode.' - '.$supplier->SupName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Supplier %s (%s) sudah berhasil disimpan", $supplier->SupName, $supplier->SupCode));
                    redirect_url("ap.supplier");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.supplier','Add New Supplier -> Nama: '.$supplier->SupName.' - '.$supplier->SupCode,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new SupType();
        $stypes = $loader->LoadAll();
        $this->Set("stypes", $stypes);
        $this->Set("supplier", $supplier);
    }

    public function edit($id = null) {
        require_once(MODEL . "ap/suptype.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ap.supplier");
        }
        $log = new UserAdmin();
        $supplier = new Supplier();
        if (count($this->postData) > 0) {
            $supplier->Id = $id;
            $supplier->CabangId = $this->userCabangId;
            $supplier->SupTypeId = $this->GetPostValue("SupTypeId");
            $supplier->SupCode = $this->GetPostValue("SupCode");
            $supplier->SupName = $this->GetPostValue("SupName");
            $supplier->Addr1 = $this->GetPostValue("Addr1");
            $supplier->Addr2 = $this->GetPostValue("Addr2");
            $supplier->City  = $this->GetPostValue("City");
            $supplier->PostCode  = $this->GetPostValue("PostCode");
            $supplier->Phone = $this->GetPostValue("Phone");
            $supplier->Fax = $this->GetPostValue("Fax");
            $supplier->Hp = $this->GetPostValue("Hp");
            $supplier->Contact = $this->GetPostValue("Contact");
            $supplier->Manager = $this->GetPostValue("Manager");
            $supplier->Npwp = $this->GetPostValue("Npwp");
            $supplier->Bank = $this->GetPostValue("Bank");
            $supplier->AccountNo = $this->GetPostValue("AccountNo");
            $supplier->IsPkp = $this->GetPostValue("IsPkp");
            $supplier->IsAktif = $this->GetPostValue("IsAktif");
            $supplier->IsPrincipal = $this->GetPostValue("IsPrincipal");
            $supplier->CreditLimit = $this->GetPostValue("CreditLimit");
            $supplier->Term = $this->GetPostValue("Term");
            if ($this->ValidateData($supplier)) {
                $supplier->UpdatebyId = $this->userUid;
                $rs = $supplier->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.supplier','Update Supplier -> Nama: '.$supplier->SupName.' - '.$supplier->SupCode,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data supplier: %s (%s) sudah berhasil disimpan", $supplier->SupCode, $supplier->SupName));
                    redirect_url("ap.supplier");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.supplier','Update Supplier -> Nama: '.$supplier->SupName.' - '.$supplier->SupCode,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data supplier. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $supplier = $supplier->LoadById($id);
            if ($supplier == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("ap.supplier");
            }
        }
        $loader = new SupType();
        $stypes = $loader->LoadAll();
        $this->Set("stypes", $stypes);
        $this->Set("supplier", $supplier);
    }

    public function view($id = null) {
        require_once(MODEL . "ap/suptype.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ap.supplier");
        }
        $supplier = new Supplier();
        $supplier = $supplier->LoadById($id);
        if ($supplier == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ap.supplier");
        }
        $loader = new SupType();
        $stypes = $loader->LoadAll();
        $this->Set("stypes", $stypes);
        $this->Set("supplier", $supplier);
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("ap.supplier");
        }
        $log = new UserAdmin();
        $supplier = new Supplier();
        $supplier = $supplier->LoadById($id);
        if ($supplier == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ap.supplier");
        }
        $rs = $supplier->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.supplier','Delete Supplier -> Nama: '.$supplier->SupName.' - '.$supplier->SupCode,'-','Success');
            $this->persistence->SaveState("info", sprintf("Data Supplier: %s (%s) sudah dihapus", $supplier->SupCode, $supplier->SupName));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.supplier','Delete Supplier -> Nama: '.$supplier->SupName.' - '.$supplier->SupCode,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus Data Supplier: %s (%s). Error: %s", $supplier->SupCode, $supplier->SupName, $this->connector->GetErrorMessage()));
        }
        redirect_url("ap.supplier");
    }

    public function getJsonSupplier($cbi = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $sups = new Supplier();
        $sups = $sups->GetJSonSupplier(0,$filter);
        echo json_encode($sups);
    }
}
