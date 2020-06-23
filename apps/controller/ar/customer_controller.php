<?php
class CustomerController extends AppController {
	private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $userUid;

	protected function Initialize() {
		require_once(MODEL . "ar/customer.php");
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
        $settings["columns"][] = array("name" => "a.cus_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "b.type_name", "display" => "Kategori", "width" => 70);
		$settings["columns"][] = array("name" => "a.cus_name", "display" => "Nama Customer", "width" => 250);
		$settings["columns"][] = array("name" => "a.addr1", "display" => "Alamat 1", "width" => 250);
        $settings["columns"][] = array("name" => "a.addr2", "display" => "Alamat 2", "width" => 150);
        $settings["columns"][] = array("name" => "c.area_name", "display" => "Area", "width" => 100);
        $settings["columns"][] = array("name" => "a.contact", "display" => "P I C", "width" => 100);
        $settings["columns"][] = array("name" => "a.phone", "display" => "No. Telepon/HP", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.is_pkp = 0,'Non-PKP','PKP')", "display" => "Pajak", "width" => 50);

		$settings["filters"][] = array("name" => "a.cus_code", "display" => "Kode");
		$settings["filters"][] = array("name" => "a.cus_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "c.area_name", "display" => "Sales Area");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Master Data Customer";

			if ($acl->CheckUserAccess("ar.customer", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "ar.customer/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("ar.customer", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "ar.customer/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
						"Error" => "Maaf anda harus memilih data sup terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data sup",
						"Confirm" => "");
			}
			if ($acl->CheckUserAccess("ar.customer", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "ar.customer/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih data sup terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data sup",
					"Confirm" => "");
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("ar.customer", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "ar.customer/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Maaf anda harus memilih data sup terlebih dahulu sebelum proses hapus data.\nPERHATIAN: Pilih tepat 1 data sup",
					"Confirm" => "Apakah anda yakin mau menghapus data sup yang dipilih ? \n\n** Penghapusan Data akan mempengaruhi data transaksi yang berkaitan ** \n\nKlik 'OK' untuk melanjutkan prosedur");
			}
			$settings["def_order"] = 1;
			$settings["def_filter"] = 1;
			$settings["singleSelect"] = true;
		} else {
			$settings["from"] = "m_customer AS a LEFT JOIN m_customer_type b ON a.custype_id = b.id LEFT JOIN m_sales_area c ON a.area_id = c.id";
            $settings["where"] = "a.is_deleted = 0 and b.company_id = ".$this->userCompanyId;
		}
		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

    private function ValidateData(Customer $customer) {

        return true;
    }

    public function add() {
        require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/salesarea.php");
        $customer = new Customer();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $customer->CabangId = $this->userCabangId;
            $customer->CusTypeId = $this->GetPostValue("CusTypeId");
            $customer->CusCode = $this->GetPostValue("CusCode");
            $customer->CusName = $this->GetPostValue("CusName");
            $customer->Addr1 = $this->GetPostValue("Addr1");
            $customer->Addr2 = $this->GetPostValue("Addr2");
            $customer->AreaId  = $this->GetPostValue("AreaId");
            $customer->Phone = $this->GetPostValue("Phone");
            $customer->Fax = $this->GetPostValue("Fax");
            $customer->Contact = $this->GetPostValue("Contact");
            $customer->CreditLimit = $this->GetPostValue("CreditLimit");
            $customer->Term = $this->GetPostValue("Term");
            $customer->Npwp = $this->GetPostValue("Npwp");
            $customer->IsAktif = $this->GetPostValue("IsAktif");
            $customer->TaxCustId = $this->GetPostValue("TaxCustId");
            $customer->IsPkp = $this->GetPostValue("IsPkp");
            $customer->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($customer)) {
                $customer->CreatebyId = $this->userUid;
                $rs = $customer->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.customer','Add New Customer -> Nama: '.$customer->CusCode.' - '.$customer->CusName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Customer %s (%s) sudah berhasil disimpan", $customer->CusName, $customer->CusCode));
                    redirect_url("ar.customer");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.customer','Add New Customer -> Nama: '.$customer->CusName.' - '.$customer->CusCode,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new CusType();
        $ctypes = $loader->LoadAll();
        $this->Set("ctypes", $ctypes);
        $loader = new SalesArea();
        $sarea = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("sareas", $sarea);
        $this->Set("customer", $customer);
    }

    public function edit($id = null) {
        require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/salesarea.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ar.customer");
        }
        $log = new UserAdmin();
        $customer = new Customer();
        if (count($this->postData) > 0) {
            $customer->Id = $id;
            $customer->CabangId = $this->userCabangId;
            $customer->CusTypeId = $this->GetPostValue("CusTypeId");
            $customer->CusCode = $this->GetPostValue("CusCode");
            $customer->CusName = $this->GetPostValue("CusName");
            $customer->Addr1 = $this->GetPostValue("Addr1");
            $customer->Addr2 = $this->GetPostValue("Addr2");
            $customer->AreaId  = $this->GetPostValue("AreaId");
            $customer->Phone = $this->GetPostValue("Phone");
            $customer->Fax = $this->GetPostValue("Fax");
            $customer->Contact = $this->GetPostValue("Contact");
            $customer->CreditLimit = $this->GetPostValue("CreditLimit");
            $customer->Term = $this->GetPostValue("Term");
            $customer->Npwp = $this->GetPostValue("Npwp");
            $customer->IsAktif = $this->GetPostValue("IsAktif");
            $customer->TaxCustId = $this->GetPostValue("TaxCustId");
            $customer->IsPkp = $this->GetPostValue("IsPkp");
            $customer->IsAktif = $this->GetPostValue("IsAktif");
            if ($this->ValidateData($customer)) {
                $customer->UpdatebyId = $this->userUid;
                $rs = $customer->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.customer','Update Customer -> Nama: '.$customer->CusName.' - '.$customer->CusCode,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data customer: %s (%s) sudah berhasil disimpan", $customer->CusCode, $customer->CusName));
                    redirect_url("ar.customer");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.customer','Update Customer -> Nama: '.$customer->CusName.' - '.$customer->CusCode,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data customer. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $customer = $customer->LoadById($id);
            if ($customer == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("ar.customer");
            }
        }
        $loader = new CusType();
        $ctypes = $loader->LoadAll();
        $this->Set("ctypes", $ctypes);
        $loader = new SalesArea();
        $sarea = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("sareas", $sarea);
        $this->Set("customer", $customer);
    }

    public function view($id = null) {
        require_once(MODEL . "ar/custype.php");
        require_once(MODEL . "master/salesarea.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("ar.customer");
        }
        $log = new UserAdmin();
        $customer = new Customer();
        $customer = $customer->LoadById($id);
        if ($customer == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ar.customer");
        }
        $loader = new CusType();
        $ctypes = $loader->LoadAll();
        $this->Set("ctypes", $ctypes);
        $loader = new SalesArea();
        $sarea = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("sareas", $sarea);
        $this->Set("customer", $customer);
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("ar.customer");
        }
        $log = new UserAdmin();
        $customer = new Customer();
        $customer = $customer->LoadById($id);
        if ($customer == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("ar.customer");
        }
        $rs = $customer->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.customer','Delete Customer -> Nama: '.$customer->CusName.' - '.$customer->CusCode,'-','Success');
            $this->persistence->SaveState("info", sprintf("Data Customer: %s (%s) sudah dihapus", $customer->CusCode, $customer->CusName));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.customer','Delete Customer -> Nama: '.$customer->CusName.' - '.$customer->CusCode,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus Data Customer: %s (%s). Error: %s", $customer->CusCode, $customer->CusName, $this->connector->GetErrorMessage()));
        }
        redirect_url("ar.customer");
    }

    public function getAutoCustCode($areaId = 0){
	    $code = 'ERR';
	    if ($areaId > 0) {
            $cust = new Customer();
            $code = $cust->GetAutoCustCode($areaId);
        }
	    print $code;
    }

    public function getJsonCustomer($cbi = 0){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $cust = new Customer();
        $cust = $cust->GetJSonCustomer($cbi,$filter);
        echo json_encode($cust);
    }
}
