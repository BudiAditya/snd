<?php
class InvoiceController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;
    private $userCabIds;

    protected function Initialize() {
        require_once(MODEL . "ar/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();
        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        //$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Entity", "width" => 30);
        //$settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.invoice_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.invoice_no", "display" => "No. Invoice", "width" => 100);
        $settings["columns"][] = array("name" => "concat(b.cus_name,'(',b.cus_code,')')", "display" => "Nama Customer", "width" => 200);
        //$settings["columns"][] = array("name" => "a.invoice_descs", "display" => "Keterangan", "width" => 150);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Cara Bayar", "width" => 55);
        $settings["columns"][] = array("name" => "a.invoice_date + INTERVAL a.credit_terms DAY", "display" => "JTP", "width" => 60);
        $settings["columns"][] = array("name" => "format(a.base_amount - a.disc_amount + a.ppn_amount + a.other_costs_amount,0)", "display" => "Nilai Penjualan", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.return_amount,0)", "display" => "Retur", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.paid_amount,0)", "display" => "Terbayar", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.base_amount - a.disc_amount + a.ppn_amount + a.other_costs_amount - (a.paid_amount + a.return_amount),0)", "display" => "OutStanding", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "c.sales_name", "display" => "Salesman", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.invoice_status = 0,'Draft',if(a.invoice_status = 1,'Posted',if(a.invoice_status = 2,'Approved','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.invoice_no", "display" => "No. Invoice");
        $settings["filters"][] = array("name" => "a.invoice_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "b.cus_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Cara Bayar");
        $settings["filters"][] = array("name" => "if(a.invoice_status = 0,'Draft',if(a.invoice_status = 1,'Posted',if(a.invoice_status = 2,'Approved','Void')))", "display" => "Status");

        $settings["def_filter"] = 1;
        $settings["def_order"] = 2;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Invoice Penjualan";

            if ($acl->CheckUserAccess("ar.invoice", "add")) {
                $settings["actions"][] = array("Text" => "<b>Add</b>", "Url" => "ar.invoice/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.invoice", "add")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "<b>Create From S/O</b>", "Url" => "ar.invoice/create", "Class" => "bt_create_new", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
            }
            if ($acl->CheckUserAccess("ar.invoice", "edit")) {
                $settings["actions"][] = array("Text" => "<b>Edit</b>", "Url" => "ar.invoice/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ar.invoice", "delete")) {
                $settings["actions"][] = array("Text" => "<b>Void</b>", "Url" => "ar.invoice/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ar.invoice", "view")) {
                $settings["actions"][] = array("Text" => "<b>View</b>", "Url" => "ar.invoice/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.invoice", "print")) {
                $settings["actions"][] = array("Text" => "Print Invoice", "Url" => "ar.invoice/ivcprint","Class" => "bt_print", "Target" => "_blank", "ReqId" => 0, "Confirm" => "");
                //$settings["actions"][] = array("Text" => "Print Surat Jalan", "Url" => "ar.invoice/printout/suratjalan","Class" => "bt_print", "Target" => "_blank", "ReqId" => 2, "Confirm" => "Pastikan Data sudah di-approve\nCetak Surat Jalan yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.invoice", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ar.invoice/report", "Class" => "bt_report", "ReqId" => 0);
            }

            if ($acl->CheckUserAccess("ar.invoice", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "<b>Approval Invoice</b>", "Url" => "ar.invoice/approve/1", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Penjualan terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data pembelian yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "<b>Batal Approval</b>", "Url" => "ar.invoice/approve/0", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Penjualan terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data pembelian yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "<b>Proses Approval</b>", "Url" => "ar.invoice/approval", "Class" => "bt_approve", "ReqId" => 0);
            }

        } else {
            //$settings["from"] = "vw_ar_invoice_master AS a";
            $settings["from"] = "t_ar_invoice_master AS a JOIN m_customer AS b ON a.customer_id = b.id JOIN m_salesman AS c ON a.sales_id = c.id";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.invoice_date) = ".$this->trxYear." And month(a.invoice_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.invoice_date) = ".$this->trxYear;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    /* Untuk entry data estimasi perbaikan dan penggantian spare part */
    public function add($invoiceId = 0) {
        require_once (MODEL . "master/cabang.php");
        require_once (MODEL . "master/warehouse.php");
        require_once (MODEL . "master/salesman.php");
        require_once (MODEL . "inventory/expedition.php");
        require_once (MODEL . "master/kasbank.php");
        require_once (MODEL . "master/user_privileges.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $log = new UserAdmin();
        $invoice = new Invoice();
        if ($invoiceId > 0){
            $invoice = $invoice->LoadById($invoiceId);
            if($invoice == null){
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.invoice");
            }
            if($invoice->InvoiceStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -APPROVED-",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if($invoice->PaidAmount > 0 && $invoice->PaymentType == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -TERBAYAR-",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if($invoice->InvoiceStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -VOID-",$invoice->InvoiceNo));
                redirect_url("ar.invoice/view/".$invoiceId);
            }
            if ($invoice->CreatebyId <> AclManager::GetInstance()->GetCurrentUser()->Id && $this->userLevel == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Anda tidak boleh mengubah data ini!",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        if ($cabang->CabType == 2){
            $this->persistence->SaveState("error", "Maaf Cabang %s dalam mode Gudang, tidak boleh digunakan untuk transaksi!",$cabang->Kode);
            redirect_url("ar.invoice");
        }
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Warehouse();
        $gudangs = $loader->LoadByCabangId($this->userCabangId,1);
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("invoice", $invoice);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->InvoiceItemsCount($invoiceId));
        //load expedisi
        $loader = new Expedition();
        $expedisi = $loader->LoadAll();
        $this->Set("expedition", $expedisi);
        //load salesman
        $loader = new Salesman();
        $sales = $loader->LoadByStatus(1);
        $this->Set("sales", $sales);
        //load coa kas/bank
        $loader = new KasBank();
        $coakas = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("coakas", $coakas);
        //load user discount priviledges
        $loader = new UserPrivileges();
        $discprev = $loader->LoadDiscPrivileges($this->userUid,8);
        $this->Set("discPrev", $discprev);
    }

    public function proses_master($invoiceId = 0){
        require_once (MODEL . "master/cabang.php");
        $log = new UserAdmin();
        $invoice = new Invoice();
        $invoice->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
            $invoice->GudangId = $this->GetPostValue("GudangId");
            $invoice->InvoiceDate = $this->GetPostValue("InvoiceDate");
            $invoice->InvoiceNo = $this->GetPostValue("InvoiceNo");
            $invoice->InvoiceDescs = $this->GetPostValue("InvoiceDescs");
            $invoice->CustomerId = $this->GetPostValue("CustomerId");
            $invoice->SalesId = $this->GetPostValue("SalesId");
            $invoice->ExpeditionId = $this->GetPostValue("ExpeditionId");
            if ($this->GetPostValue("InvoiceStatus") == null || $this->GetPostValue("InvoiceStatus") == 0){
                $invoice->InvoiceStatus = 1;
            }else{
                $invoice->InvoiceStatus = $this->GetPostValue("InvoiceStatus");
            }
            if($this->GetPostValue("PaymentType") == null){
                $invoice->PaymentType = 0;
            }else{
                $invoice->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $invoice->CreditTerms = 0;
            }else{
                $invoice->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            if ($invoice->PaymentType == 0 && $invoice->DbAccId == 0){
                $cabang = new Cabang($this->userCabangId);
                $invoice->DbAccId = $cabang->KasAccId;
            }
            if ($this->ValidateMaster($invoice)) {
                if ($invoiceId == 0) {
                    if ($invoice->InvoiceNo == null || $invoice->InvoiceNo == "-" || $invoice->InvoiceNo == "" || $invoice->InvoiceNo == "0") {
                        $invoice->InvoiceNo = $invoice->GetInvoiceDocNo();
                    }
                    $invoice->BaseAmount = 0;
                    $invoice->DiscAmount = 0;
                    $invoice->PpnAmount = 0;
                    $invoice->PphAmount = 0;
                    $invoice->OtherCosts = '-';
                    $invoice->OtherCostsAmount = 0;
                    $invoice->PaidAmount = 0;
                    $invoice->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                    $rs = $invoice->Insert();
                    if ($rs == 1) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add New Invoice', $invoice->InvoiceNo, 'Success');
                        printf("OK|A|%d|%s|%s",$invoice->Id,$invoice->InvoiceNo,'Success!');
                    }else{
                        if ($this->connector->IsDuplicateError()) {
                            $err ="Maaf Nomor Invoice sudah ada pada database";
                        } else {
                            $err = "Maaf error saat simpan data. Message: " . $this->connector->GetErrorMessage();
                        }
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add New Invoice', $invoice->InvoiceNo, 'Failed');
                        printf("ER|A|%d|%s|%s",$invoice->Id,'0',$err);
                    }
                }else{
                    if ($invoice->PaymentType == 0){
                        $invoice->PaidAmount = 0;
                    }
                    $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                    $rs = $invoice->Update($invoiceId);
                    if ($rs == 1) {
                        //$rs = $invoice->RecalculateInvoiceMaster($invoiceId);
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Update Invoice', $invoice->InvoiceNo, 'Success');
                        printf("OK|U|%d|%s|%s",$invoice->Id,$invoice->InvoiceNo,'Success!');
                    }else{
                        if ($this->connector->IsDuplicateError()) {
                            $err ="Maaf Nomor Invoice sudah ada pada database";
                        } else {
                            $err = "Maaf error saat update data. Message: " . $this->connector->GetErrorMessage();
                        }
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Update Invoice', $invoice->InvoiceNo, 'Failed');
                        printf("ER|U|%d|%s|%s",$invoice->Id,'0',$err);
                    }
                }
            }else{
                printf("ER|X|%d|%s|%s",$invoiceId,'0',$invoice->ErrorMsg);
            }
        }else{
            printf("ER|X|%d|%s|%s",$invoiceId,'0','No Data posted!');
        }
    }

    private function ValidateMaster(Invoice $invoice) {
        $invoice->ErrorMsg = null;
        if ($invoice->CustomerId == 0 || $invoice->CustomerId == null || $invoice->CustomerId == ''){
            $invoice->ErrorMsg = "Customer tidak boleh kosong!";
            $this->Set("error", $invoice->ErrorMsg);
            return false;
        }
        if ($invoice->PaymentType == 1 && $invoice->CreditTerms == 0){
            $invoice->ErrorMsg = "Lama kredit belum diisi!";
            $this->Set("error", $invoice->ErrorMsg);
            return false;
        }
        if ($invoice->PaymentType == 0 && $invoice->DbAccId == 0){
            $invoice->ErrorMsg = "Akun Kas belum dipilih!";
            $this->Set("error", $invoice->ErrorMsg);
            return false;
        }
        if ($invoice->GudangId == 0 || $invoice->GudangId == ""){
            $invoice->ErrorMsg = "Gudang tujuan belum dipilih!";
            $this->Set("error", $invoice->ErrorMsg);
            return false;
        }
        if ($invoice->SalesId == 0 || $invoice->SalesId == ""){
            $invoice->ErrorMsg = "Salesman belum dipilih!";
            $this->Set("error", $invoice->ErrorMsg);
            return false;
        }
        if (date('Y',strtotime($invoice->InvoiceDate)) != $this->trxYear){
            $invoice->ErrorMsg = "Tahun Transaksi salah! harusnya tahun = ".$this->trxYear;
            $this->Set("error", $invoice->ErrorMsg);
            return false;
        }
        return true;
    }

    public function edit($invoiceId = 0) {
        require_once (MODEL . "master/cabang.php");
        require_once (MODEL . "master/warehouse.php");
        require_once (MODEL . "master/salesman.php");
        require_once (MODEL . "inventory/expedition.php");
        require_once (MODEL . "master/kasbank.php");
        require_once (MODEL . "master/user_privileges.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $log = new UserAdmin();
        $invoice = new Invoice();
        if ($invoiceId > 0){
            $invoice = $invoice->LoadById($invoiceId);
            if($invoice == null){
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.invoice");
            }
            if($invoice->InvoiceStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -APPROVED-",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if($invoice->PaidAmount > 0 && $invoice->PaymentType == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -TERBAYAR-",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if($invoice->InvoiceStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -VOID-",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if ($invoice->CreatebyId <> AclManager::GetInstance()->GetCurrentUser()->Id && $this->userLevel == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Anda tidak boleh mengubah data ini!",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        if ($cabang->CabType == 2){
            $this->persistence->SaveState("error", "Maaf Cabang %s dalam mode Gudang, tidak boleh digunakan untuk transaksi!",$cabang->Kode);
            redirect_url("ar.invoice");
        }
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Warehouse();
        $gudangs = $loader->LoadByCabangId($this->userCabangId,1);
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("invoice", $invoice);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->InvoiceItemsCount($invoiceId));
        //load expedisi
        $loader = new Expedition();
        $expedisi = $loader->LoadAll();
        $this->Set("expedition", $expedisi);
        //load salesman
        $loader = new Salesman();
        $sales = $loader->LoadByStatus(1);
        $this->Set("sales", $sales);
        $loader = new KasBank();
        $coakas = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("coakas", $coakas);
        //load user discount priviledges
        $loader = new UserPrivileges();
        $discprev = $loader->LoadDiscPrivileges($this->userUid,8);
        $this->Set("discPrev", $discprev);
    }

    public function view($invoiceId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "master/salesman.php");
        require_once(MODEL . "inventory/expedition.php");
        require_once(MODEL . "master/kasbank.php");
        require_once(MODEL . "ar/customer.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $invoice = new Invoice();
        $invoice = $invoice->LoadById($invoiceId);
        if($invoice == null){
            $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.invoice");
        }
        // load details
        $invoice->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Warehouse();
        $gudangs = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("invoice", $invoice);
        $this->Set("acl", $acl);
        //load expedisi
        $loader = new Expedition();
        $expedisi = $loader->LoadAll();
        $this->Set("expedition", $expedisi);
        //load salesman
        $loader = new Salesman();
        $karyawan = $loader->LoadAll();
        $this->Set("sales", $karyawan);
        //load coa kas/bank
        $loader = new KasBank();
        $coakas = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("coakas", $coakas);
        //load customer
        $loader = new Customer($invoice->CustomerId);
        $this->Set("custdata", $loader);
    }

    public function delete($invoiceId) {
        // Cek datanya
        $ExSoId = null;
        $log = new UserAdmin();
        $invoice = new Invoice();
        $invoice = $invoice->FindById($invoiceId);
        if($invoice == null){
            $this->Set("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.invoice");
        }
        // periksa status po
        $ExSoId = $invoice->ExSoId;
        if($invoice->InvoiceStatus < 2){
            $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($invoice->Delete($invoiceId,$ExSoId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Delete Invoice',$invoice->InvoiceNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Invoice No: %s sudah berhasil dihapus", $invoice->InvoiceNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Delete Invoice',$invoice->InvoiceNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s gagal dihapus", $invoice->InvoiceNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s sudah berstatus -APPROVED-", $invoice->InvoiceNo));
        }
        redirect_url("ar.invoice");
    }

    public function void($invoiceId) {
        // Cek datanya
        $ExSoId = null;
        $log = new UserAdmin();
        $invoice = new Invoice();
        $invoice = $invoice->FindById($invoiceId);
        if($invoice == null){
            $this->Set("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.invoice");
        }
        if($invoice->InvoiceStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -VOID-",$invoice->InvoiceNo));
            redirect_url("ar.invoice");
        }
        if($invoice->InvoiceStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -APPROVED-",$invoice->InvoiceNo));
            redirect_url("ar.invoice");
        }
        if($invoice->PaidAmount > 0 && $invoice->PaymentType == 1){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -TERBAYAR-",$invoice->InvoiceNo));
            redirect_url("ar.invoice");
        }
        $ExSoId = $invoice->ExSoId;
        // periksa status po
        if($invoice->InvoiceStatus < 2){
            $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->InvoiceItemsCount($invoiceId) > 0 && $this->void_detail($invoiceId) == 0){
                $this->persistence->SaveState("error", sprintf("Maaf hapus dulu detail Invoice No. %s sebelum diproses!",$invoice->InvoiceNo));
                redirect_url("ar.invoice");
            }
            if ($invoice->Void($invoiceId,$ExSoId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Delete Invoice',$invoice->InvoiceNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Invoice No: %s sudah berhasil batalkan", $invoice->InvoiceNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Delete Invoice',$invoice->InvoiceNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s gagal dibatalkan", $invoice->InvoiceNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s sudah berstatus -APPROVED-", $invoice->InvoiceNo));
        }
        redirect_url("ar.invoice");
    }

    public function add_detail($invoiceId = 0) {
        require_once(MODEL . "inventory/items.php");
        require_once(MODEL . "inventory/stock.php");
        if ($invoiceId > 0) {
            $log = new UserAdmin();
            $invoice = new Invoice($invoiceId);
            $invoicedetail = new InvoiceDetail();
            $invoicedetail->InvoiceId = $invoiceId;
            if (count($this->postData) > 0) {
                $invoicedetail->ItemId = $this->GetPostValue("aItemId");
                $invoicedetail->ExSoId = $this->GetPostValue("aExSoId");
                $invoicedetail->SalesQty = $this->GetPostValue("aQty");
                $invoicedetail->ReturnQty = 0;
                $invoicedetail->Price = $this->GetPostValue("aPrice");
                if ($this->GetPostValue("aDiscFormula") == '') {
                    $invoicedetail->DiscFormula = 0;
                } else {
                    $invoicedetail->DiscFormula = $this->GetPostValue("aDiscFormula");
                }
                $invoicedetail->DiscAmount = $this->GetPostValue("aDiscAmount");
                $invoicedetail->SubTotal = $this->GetPostValue("aSubTotal");
                $invoicedetail->IsFree = $this->GetPostValue("aIsFree");
                //$invoicedetail->PphPct = $this->GetPostValue("aPphPct");
                //$invoicedetail->PphAmount = $this->GetPostValue("aPphAmount");
                $invoicedetail->PpnPct = $this->GetPostValue("aPpnPct");
                $invoicedetail->PpnAmount = $this->GetPostValue("aPpnAmount");
                // insert ke table
                $flagSuccess = false;
                $this->connector->BeginTransaction();
                $rs = $invoicedetail->Insert() == 1;
                if ($rs > 0){
                    //pasti adalah item ini
                    $items = new Items($invoicedetail->ItemId);
                    $stock = new Stock();
                    $stocks = $stock->LoadStocksFifo($this->trxYear,$invoicedetail->ItemId,$items->SuomCode,$invoice->GudangId);
                    // Set variable-variable pendukung
                    $remainingQty = $invoicedetail->SalesQty;
                    $invoicedetail->ItemHpp = 0;
                    $hpp = 0;
                    /** @var $stocks Stock[] */
                    foreach ($stocks as $stock) {
                        // Buat object stock keluarnya
                        $issue = new Stock();
                        $issue->TrxYear = $this->trxYear;
                        $issue->CreatedById = $this->userUid;
                        $issue->StockTypeCode = 101;                // Item Issue dari IS
                        $issue->ReffId = $invoicedetail->Id;
                        $issue->TrxDate = $invoice->InvoiceDate;
                        $issue->WarehouseId = $invoice->GudangId;    // Gudang asal!
                        $issue->ItemId = $invoicedetail->ItemId;
                        //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                        $issue->UomCode = $items->SuomCode;
                        $issue->Price = $stock->Price;                // Ya pastilah pake angka ini...
                        $issue->UseStockId = $stock->Id;            // Kasi tau kalau issue ini based on stock id mana
                        $issue->QtyBalance = null;                    // Klo issue harus NULL

                        $stock->UpdatedById = $this->userUid;

                        if ($remainingQty > $stock->QtyBalance) {
                            // Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
                            $issue->Qty = $stock->QtyBalance;            // Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya
                            $remainingQty -= $stock->QtyBalance;        // Kita masih perlu...
                            $stock->QtyBalance = 0;                        // Habis...
                        } else {
                            // Barang di gudang mencukupi atau PAS
                            $issue->Qty = $remainingQty;
                            $stock->QtyBalance -= $remainingQty;
                            $remainingQty = 0;
                        }
                        $hpp+= $issue->Qty * $issue->Price;
                        // Apapun yang terjadi masukkan data issue stock
                        if ($issue->Insert() > 0) {
                            $flagSuccess = true;
                        } else {
                            $flagSuccess = false;
                            $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName);
                            break;        // Break loop stocks
                        }
                        // update hpp detail

                        if ($hpp > 0) {
                            $invoicedetail->ItemHpp = round($hpp / $invoicedetail->SalesQty, 2);
                            $invoicedetail->IsPost = 1;
                            if ($invoicedetail->UpdateHpp() > 0) {
                                $flagSuccess = true;
                            } else {
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                break;
                            }
                        }else{
                            $lhpp = new Stock();
                            $lhpp = $lhpp->GetLastHpp($this->trxYear,$invoice->GudangId,$invoicedetail->ItemId);
                            if ($lhpp > 0){
                                $invoicedetail->ItemHpp = $lhpp;
                                $invoicedetail->IsPost = 1;
                                if ($invoicedetail->UpdateHpp() > 0) {
                                    $flagSuccess = true;
                                } else {
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                    break;
                                }
                            }else{
                                $dhpp = new Stock();
                                $dhpp = $dhpp->GetDefaultHpp($invoicedetail->ItemId);
                                if ($dhpp > 0) {
                                    $invoicedetail->ItemHpp = round($dhpp / $invoicedetail->SalesQty, 2);
                                    $invoicedetail->IsPost = 1;
                                    if ($invoicedetail->UpdateHpp() > 0) {
                                        $flagSuccess = true;
                                    } else {
                                        $flagSuccess = false;
                                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                        break;
                                    }
                                }else{
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: [%s] %s Message: HPP Tidak dihitung (Kosong) ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                    break;
                                }
                            }
                        }
                        // Update Qty Balance
                        if ($stock->Update($stock->Id) > 0) {
                            $flagSuccess = true;
                        } else {
                            $flagSuccess = false;
                            $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                            break;        // Break loop stocks
                        }
                        // OK jangan lupa update data cost
                        //$invoicedetail->Hpp += $issue->Qty * $issue->Price;
                        if ($remainingQty <= 0) {
                            $flagSuccess = true;
                            // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                            break;
                        }
                    }
                }
                if ($flagSuccess) {
                    $qts = $invoicedetail->SalesQty;
                    if ($invoicedetail->ExSoId > 0 && $qts > 0){
                        //update sales order send qty
                        $this->connector->CommandText = "Update t_ar_order a Set a.send_qty = a.send_qty + $qts Where a.id = ".$invoicedetail->ExSoId;
                        $rz = $this->connector->ExecuteNonQuery();
                    }
                    $this->connector->CommitTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add Invoice detail -> Item Code: ' . $invoicedetail->ItemCode . ' = ' . $invoicedetail->SalesQty, $invoice->InvoiceNo, 'Success');
                    echo json_encode(array());
                } else {
                    $this->connector->RollbackTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add Invoice detail -> Item Code: ' . $invoicedetail->ItemCode . ' = ' . $invoicedetail->SalesQty, $invoice->InvoiceNo, 'Failed');
                    echo json_encode(array('errorMsg' =>$errors));
                }
            }
        }else{
            echo json_encode(array('errorMsg' => 'Data Master belum ada!'));
        }
    }

    public function edit_detail($invoiceId = 0,$detailId = 0) {
        require_once(MODEL . "inventory/items.php");
        require_once(MODEL . "inventory/stock.php");
        $errors = array();
        if ($invoiceId > 0 && $detailId > 0) {
            $log = new UserAdmin();
            $invoice = new Invoice($invoiceId);
            $invoicedetail = new InvoiceDetail();
            $invoicedetail = $invoicedetail->LoadById($detailId);
            if (count($this->postData) > 0) {
                $invoicedetail->ItemId = $this->GetPostValue("aItemId");
                $invoicedetail->ExSoId = $this->GetPostValue("aExSoId");
                $invoicedetail->SalesQty = $this->GetPostValue("aQty");
                $invoicedetail->Price = $this->GetPostValue("aPrice");
                if ($this->GetPostValue("aDiscFormula") == '') {
                    $invoicedetail->DiscFormula = 0;
                } else {
                    $invoicedetail->DiscFormula = $this->GetPostValue("aDiscFormula");
                }
                $invoicedetail->DiscAmount = $this->GetPostValue("aDiscAmount");
                $invoicedetail->SubTotal = $this->GetPostValue("aSubTotal");
                $invoicedetail->IsFree = $this->GetPostValue("aIsFree");
                $invoicedetail->PpnPct = $this->GetPostValue("aPpnPct");
                $invoicedetail->PpnAmount = $this->GetPostValue("aPpnAmount");
                // update ke table
                $flagSuccess = false;
                $this->connector->BeginTransaction();
                // #1. update stock
                $rs = $invoicedetail->Update($detailId) == 1;
                if ($rs > 0){
                    $flagSuccess = true;
                    // update stock
                    $stock = new  Stock();
                    $stock = $stock->FindByTypeReffId($this->trxYear,101,$detailId);
                    if ($stock == null){
                        $flagSuccess = false;
                    }else {
                        $oQty = 0;
                        /** @var $stock Stock[]*/
                        foreach ($stock as $dstock){
                            $cstock = new Stock($dstock->UseStockId);
                            if ($cstock == null){
                                $flagSuccess = false;
                            }else{
                                $oQty += $dstock->Qty;
                                $cstock->QtyBalance += $dstock->Qty;
                                $rs = $cstock->Update($dstock->UseStockId);
                                if (!$rs){
                                    $flagSuccess = false;
                                }
                            }
                        }
                        if ($flagSuccess) {
                            $stock = new Stock();
                            $stock->UpdatedById = $this->userUid;
                            $stock->DeleteByTypeReffId($this->trxYear,101, $detailId);
                            if ($invoicedetail->ExSoId > 0 && $oQty > 0) {
                                $this->connector->CommandText = "Update t_ar_order a Set a.send_qty = a.send_qty - $oQty Where a.id = $invoicedetail->ExSoId";
                                $this->connector->ExecuteNonQuery();
                            }
                        }
                    }
                    if ($flagSuccess) {
                        // pasti adalah item ini
                        $items = new Items($invoicedetail->ItemId);
                        $stock = new Stock();
                        $stocks = $stock->LoadStocksFifo($this->trxYear, $invoicedetail->ItemId, $items->SuomCode, $invoice->GudangId);
                        // Set variable-variable pendukung
                        $remainingQty = $invoicedetail->SalesQty;
                        $invoicedetail->ItemHpp = 0;
                        $hpp = 0;
                        /** @var $stocks Stock[] */
                        foreach ($stocks as $stock) {
                            // Buat object stock keluarnya
                            $issue = new Stock();
                            $issue->TrxYear = $this->trxYear;
                            $issue->CreatedById = $this->userUid;
                            $issue->StockTypeCode = 101;                // Item Issue dari IS
                            $issue->ReffId = $detailId;
                            $issue->TrxDate = $invoice->InvoiceDate;
                            $issue->WarehouseId = $invoice->GudangId;    // Gudang asal!
                            $issue->ItemId = $invoicedetail->ItemId;
                            //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                            $issue->UomCode = $items->SuomCode;
                            $issue->Price = $stock->Price;                // Ya pastilah pake angka ini...
                            $issue->UseStockId = $stock->Id;            // Kasi tau kalau issue ini based on stock id mana
                            $issue->QtyBalance = null;                    // Klo issue harus NULL

                            $stock->UpdatedById = $this->userUid;

                            if ($remainingQty > $stock->QtyBalance) {
                                // Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
                                $issue->Qty = $stock->QtyBalance;            // Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya
                                $remainingQty -= $stock->QtyBalance;        // Kita masih perlu...
                                $stock->QtyBalance = 0;                        // Habis...
                            } else {
                                // Barang di gudang mencukupi atau PAS
                                $issue->Qty = $remainingQty;
                                $stock->QtyBalance -= $remainingQty;
                                $remainingQty = 0;
                            }
                            $hpp += $issue->Qty * $issue->Price;
                            // Apapun yang terjadi masukkan data issue stock
                            if ($issue->Insert() > 0) {
                                $flagSuccess = true;
                            } else {
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName);
                                break;        // Break loop stocks
                            }
                            // update hpp detail
                            /*
                            if ($hpp > 0) {
                                $invoicedetail->ItemHpp = round($hpp / $invoicedetail->SalesQty, 2);
                                $invoicedetail->IsPost = 1;
                                if ($invoicedetail->UpdateHpp() > 0) {
                                    $flagSuccess = true;
                                } else {
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                    break;
                                }
                            } else {
                                $lhpp = new Stock();
                                $lhpp = $lhpp->GetLastHpp($this->trxYear, $invoice->GudangId, $invoicedetail->ItemId);
                                if ($lhpp > 0) {
                                    $invoicedetail->ItemHpp = $lhpp;
                                    $invoicedetail->IsPost = 1;
                                    if ($invoicedetail->UpdateHpp() > 0) {
                                        $flagSuccess = true;
                                    } else {
                                        $flagSuccess = false;
                                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                        break;
                                    }
                                } else {
                                    $dhpp = new Stock();
                                    $dhpp = $dhpp->GetDefaultHpp($invoicedetail->ItemId);
                                    if ($dhpp > 0) {
                                        $invoicedetail->ItemHpp = round($dhpp / $invoicedetail->SalesQty, 2);
                                        $invoicedetail->IsPost = 1;
                                        if ($invoicedetail->UpdateHpp() > 0) {
                                            $flagSuccess = true;
                                        } else {
                                            $flagSuccess = false;
                                            $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                            break;
                                        }
                                    } else {
                                        $flagSuccess = false;
                                        $errors[] = sprintf("%s -> Item: [%s] %s Message: HPP Tidak dihitung (Kosong) ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                        break;
                                    }
                                }
                            }
                            */
                            // Update Qty Balance
                            if ($stock->Update($stock->Id) > 0) {
                                $flagSuccess = true;
                            } else {
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                break;        // Break loop stocks
                            }
                            // OK jangan lupa update data cost
                            //$invoicedetail->Hpp += $issue->Qty * $issue->Price;
                            if ($remainingQty <= 0) {
                                $flagSuccess = true;
                                // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                                break;
                            }
                        }
                    }
                }
                if ($flagSuccess) {
                    $qts = $invoicedetail->SalesQty;
                    if ($invoicedetail->ExSoId > 0 && $qts > 0){
                        //update sales order send qty
                        $this->connector->CommandText = "Update t_ar_order a Set a.send_qty = a.send_qty + $qts Where a.id = ".$invoicedetail->ExSoId;
                        $rz = $this->connector->ExecuteNonQuery();
                    }
                    $this->connector->CommitTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add Invoice detail -> Item Code: ' . $invoicedetail->ItemCode . ' = ' . $invoicedetail->SalesQty, $invoice->InvoiceNo, 'Success');
                    echo json_encode(array());
                } else {
                    $this->connector->RollbackTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add Invoice detail -> Item Code: ' . $invoicedetail->ItemCode . ' = ' . $invoicedetail->SalesQty, $invoice->InvoiceNo, 'Failed');
                    echo json_encode(array('errorMsg' =>$errors));
                }
            }
        }else{
            echo json_encode(array('errorMsg' => 'Data detail tidak ditemukan!'));
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $invoicedetail = new InvoiceDetail();
        $invoicedetail = $invoicedetail->FindById($id);
        if ($invoicedetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $flagSuccess = true;
        $this->connector->BeginTransaction();
        if ($invoicedetail->Delete($id) == 1) {
            require_once(MODEL . "inventory/stock.php");
            $stock = new  Stock();
            $stock = $stock->FindByTypeReffId($this->trxYear,101,$id);
            if ($stock == null){
                $flagSuccess = false;
            }else {
                /** @var $stock Stock[]*/
                foreach ($stock as $dstock){
                    $cstock = new Stock($dstock->UseStockId);
                    if ($cstock == null){
                        $flagSuccess = false;
                    }else{
                        $cstock->QtyBalance += $dstock->Qty;
                        $rs = $cstock->Update($dstock->UseStockId);
                        if (!$rs){
                            $flagSuccess = false;
                        }
                    }
                }
                if ($flagSuccess) {
                    $stock = new Stock();
                    $stock->UpdatedById = $this->userUid;
                    $stock->VoidByTypeReffId($this->trxYear,101, $id);
                }
            }
        }
        if($flagSuccess){
            $this->connector->CommitTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Delete Invoice detail -> Item Code: '.$invoicedetail->ItemCode.' = '.$invoicedetail->SalesQty,$invoicedetail->InvoiceId,'Success');
            printf("Data Detail Invoice ID: %d berhasil dihapus!",$id);
        }else{
            $this->connector->RollbackTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Delete Invoice detail -> Item Code: '.$invoicedetail->ItemCode.' = '.$invoicedetail->SalesQty,$invoicedetail->InvoiceId,'Failed');
            printf("Maaf, Data Detail Invoice ID: %d gagal dihapus!",$id);
        }
    }

    public function void_detail($invId = 0) {
        // Cek datanya
        //$log = new UserAdmin();
        $invoicedetail = new InvoiceDetail();
        $invoicedetail = $invoicedetail->LoadByInvoiceId($invId);
        if ($invoicedetail == null) {
            //print("Data tidak ditemukan..");
            return 0;
        }
        require_once(MODEL . "inventory/stock.php");
        $flagSuccess = true;
        $this->connector->BeginTransaction();
        /** @var $invoicedetail InvoiceDetail[] */
        foreach ($invoicedetail as $detail) {
           $id = $detail->Id;
           $stock = new  Stock();
           $stock = $stock->FindByTypeReffId($this->trxYear, 101, $id);
           if ($stock == null) {
               $flagSuccess = false;
           } else {
               /** @var $stock Stock[] */
               foreach ($stock as $dstock) {
                   $cstock = new Stock($dstock->UseStockId);
                   if ($cstock == null) {
                       $flagSuccess = false;
                   } else {
                       $cstock->QtyBalance += $dstock->Qty;
                       $rs = $cstock->Update($dstock->UseStockId);
                       if (!$rs) {
                           $flagSuccess = false;
                       }
                   }
               }
               if ($flagSuccess) {
                   //update stock
                   $stock = new Stock();
                   $stock->UpdatedById = $this->userUid;
                   $stock->VoidByTypeReffId($this->trxYear, 101, $id);
                   //update invoice detail post status
                   $detail->ItemHpp = 0;
                   $detail->IsPost = 0;
                   $detail->UpdateHpp();
               }
           }
           if ($flagSuccess) {
               $this->connector->CommitTransaction();
               return 1;
           } else {
               $this->connector->RollbackTransaction();
               return 0;
           }
        }
    }

    public function getitems_json($principalId = 0,$order = "a.item_code"){
        require_once(MODEL . "inventory/items.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $items = new Items();
        $itemlists = $items->GetJSonItems($this->userCompanyId,$this->userCabangId,$principalId,$filter,$order);
        echo json_encode($itemlists);
    }

    public function getjson_solists($customerId){
        require_once (MODEL . "ar/so.php");
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new So();
        $solists = $order->GetActiveSoList($this->userCabangId,$customerId);
        echo json_encode($solists);
    }

    public function getjson_soitems($soId = 0){
        require_once (MODEL . "ar/so.php");
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new So();
        $soitems = $order->GetItemSoItems($soId);
        echo json_encode($soitems);
    }

    public function getjson_orderitems($customerId = 0,$salesId = 0){
        require_once (MODEL . "ar/order.php");
        $order = new Order();
        $soitems = $order->GetItemSoItems($customerId,$salesId);
        echo json_encode($soitems);
    }

    public function getjson_stockitems($gudangId = 0){
        require_once (MODEL . "inventory/stock.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $stock = new Stock();
        $stock = $stock->GetItemStocks($this->trxYear,$gudangId,$filter);
        echo json_encode($stock);
    }

    public function prosesSalesOrder($invId = 0,$invNo = 0,$soNo = 0){
        //proses transfer data dari sales order
        //print('Test OK! '.$invId.' - '.$invNo.' - '.$soNo);
        $inv = new Invoice();
        $hsl = $inv->PostSoDetail2Invoice($invId,$invNo,$soNo);
        if ($hsl > 0){
            print("OK");
        }else{
            print("ER");
        }
    }

    public function getSumOutstanding($customerId = 0){
        $sumOut = 0;
        $invoice = new Invoice();
        $sumOut = $invoice->GetSumOutstandingInvoices($this->userCabangId,$customerId);
        print($sumOut);
    }

    public function getQtyOutstanding($customerId = 0){
        $qtyOut = 0;
        $invoice = new Invoice();
        $qtyOut = $invoice->GetQtyOutstandingInvoices($this->userCabangId,$customerId);
        print($qtyOut);
    }

    public function getInvoiceItemRows($id){
        $invoice = new Invoice();
        $rows = $invoice->GetInvoiceItemRow($id);
        print($rows);
    }

    public function InvoiceItemsCount($id){
        $invoice = new Invoice();
        $rows = $invoice->GetInvoiceItemRow($id);
        return $rows;
    }

    //direct printing
    public function printhtml($invId,$paperType = 0,$prtName = null){
        require_once (MODEL . "master/cabang.php");
        require_once (MODEL . "ar/customer.php");
        $invoice = new Invoice($invId);
        if ($invoice->InvoiceStatus > 0) {
            $invoice->LoadDetails();
            $cabang = new Cabang($invoice->CabangId);
            $customer = new Customer($invoice->CustomerId);
            $this->Set("invoice", $invoice);
            $this->Set("cabang", $cabang);
            $this->Set("customer", $customer);
        }else{
            redirect_url("ar.invoice");
        }
    }

    public function ivcprint(){
        require_once (MODEL . "master/salesarea.php");
        require_once (MODEL . "master/warehouse.php");
        if (count($this->postData) > 0) {
            $areid = $this->GetPostValue("areaId");
            $whsid = $this->GetPostValue("whsId");
            $sdate = strtotime($this->GetPostValue("stDate"));
            $edate = strtotime($this->GetPostValue("enDate"));
            $psize = $this->GetPostValue("pSize");
        }else{
            $areid = 0;
            $whsid = 0;
            $sdate = time();
            $edate = $sdate;
            $psize = 0;
        }
        $loader = new Invoice();
        $invoice= $loader->LoadInvoicePrint($this->userCabangId,$areid,$whsid,$sdate,$edate,$psize);
        $this->Set("areaId", $areid);
        $this->Set("whsId", $whsid);
        $this->Set("stDate", $sdate);
        $this->Set("enDate", $edate);
        $this->Set("pSize", $psize);
        $this->Set("invoices", $invoice);
        //load cabang
        $loader = new SalesArea();
        $areas = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("areas", $areas);
        //load warehouse
        $loader = new Warehouse();
        $warehouses = $loader->LoadByCabangId($this->userCabangId,1);
        $this->Set("warehouses", $warehouses);
    }

    public function approval(){
        if (count($this->postData) > 0) {
            $sdate = strtotime($this->GetPostValue("stDate"));
            $edate = strtotime($this->GetPostValue("enDate"));
            $ivsts = $this->GetPostValue("iStatus");
        }else{
            $sdate = time();
            $edate = $sdate;
            $ivsts = 1;
        }
        $loader = new Invoice();
        $invoice = $loader->LoadInvoice4Approval($this->userCabangId,$sdate,$edate,$ivsts);
        $this->Set("stDate", $sdate);
        $this->Set("enDate", $edate);
        $this->Set("iStatus", $ivsts);
        $this->Set("invoices", $invoice);
    }

    //proses cetak form invoice
    public function printout($doctype = 'invoice') {
        require_once (MODEL . "master/cabang.php");
        require_once (MODEL . "ar/customer.php");
        $ids = $this->GetPostValue("ids", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Harap pilih data yang akan dicetak !");
            redirect_url("ar.invoice");
            return;
        }
        $jdt = 0;
        $errors = array();
        $report = array();
        foreach ($ids as $id) {
            $inv = new Invoice();
            $inv = $inv->LoadById($id);
            /** @var $inv Invoice */
            if ($inv != null) {
                if ($inv->InvoiceStatus == 2) {
                    $jdt++;
                    $inv->LoadDetails();
                    $report[] = $inv;
                }
            }
        }
        if ($jdt == 0){
            //$errors[] = sprintf("Data Invoice yg dipilih tidak memenuhi syarat!");
            $this->persistence->SaveState("error", "Data Invoice yg dipilih tidak memenuhi syarat!");
            redirect_url("ar.invoice");
        }
        $cabang = new Cabang($this->userCabangId);
        $this->Set("cabang", $cabang);
        $this->Set("doctype", $doctype);
        $this->Set("report", $report);
    }

    public function getItemSalePriceBySalesArea($customerId = 0,$itemId){
        $dPrice = 'ERR|0';
        $sZone = 0;
        $iPrices = null;
        $areaId = 0;
        if ($customerId > 0) {
            require_once(MODEL . "master/salesarea.php");
            require_once(MODEL . "ar/customer.php");
            $customer = new Customer($customerId);
            $areaId = $customer->AreaId;
            $sarea = new SalesArea($areaId);
            if ($sarea != null){
                $sZone = $sarea->ZoneId;
                if ($sZone > 0){
                    require_once (MODEL . "inventory/itemprices.php");
                    $iPrices = new ItemPrices();
                    $iPrices = $iPrices->FindByItemId($this->userCabangId,$itemId);
                    if ($iPrices != null){
                        switch ($sZone){
                            case 1:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone1;
                                break;
                            case 2:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone2;
                                break;
                            case 3:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone3;
                                break;
                            case 4:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone4;
                                break;
                            case 5:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone5;
                                break;
                            default:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone1;
                        }
                        $dPrice .= "|".$iPrices->LuomCode."|".$iPrices->SuomCode."|".$iPrices->SuomQty;
                    }
                }
            }
        }
        print $dPrice;
    }

    public function retItemSalePrice($customerId = 0,$itemId){
        $dPrice = 'ERR|0';
        $sZone = 0;
        $iPrices = null;
        $areaId = 0;
        if ($customerId > 0) {
            require_once(MODEL . "master/salesarea.php");
            require_once(MODEL . "ar/customer.php");
            $customer = new Customer($customerId);
            $areaId = $customer->AreaId;
            $sarea = new SalesArea($areaId);
            if ($sarea != null){
                $sZone = $sarea->ZoneId;
                if ($sZone > 0){
                    require_once (MODEL . "inventory/itemprices.php");
                    $iPrices = new ItemPrices();
                    $iPrices = $iPrices->FindByItemId($this->userCabangId,$itemId);
                    if ($iPrices != null){
                        switch ($sZone){
                            case 1:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone1;
                                break;
                            case 2:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone2;
                                break;
                            case 3:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone3;
                                break;
                            case 4:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone4;
                                break;
                            case 5:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone5;
                                break;
                            default:
                                $dPrice = $iPrices->UomCode.'|'.$iPrices->pZone1;
                        }
                    }
                }
            }
        }
        return $dPrice;
    }

    public function checkStock($whId,$itemId){
        require_once (MODEL . "inventory/stock.php");
        $stock = new Stock();
        $stock = $stock->CheckStock($this->trxYear,$whId,$itemId);
        print $stock;
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "ar/customer.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "master/salesman.php");
        require_once(MODEL . "inventory/itementity.php");
        require_once(MODEL . "inventory/itembrand.php");
        require_once(MODEL . "inventory/itemprincipal.php");
        require_once(MODEL . "master/salesarea.php");
        // Intelligent time detection...
        //$month = (int)date("n");
        //$year = (int)date("Y");
        $month = $this->trxMonth;
        $year = $this->trxYear;
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sGudangId = 0;//$this->GetPostValue("GudangId");
            $sEntityId = $this->GetPostValue("EntityId");
            $sContactsId = $this->GetPostValue("ContactsId");
            $sSalesId = $this->GetPostValue("SalesId");
            $sStatus = $this->GetPostValue("Status");
            $sPaymentStatus = $this->GetPostValue("PaymentStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            $sBrandId = $this->GetPostValue("BrandId");
            $sPrincipalId = $this->GetPostValue("PrincipalId");
            $sSalesAreaId = $this->GetPostValue("SalesAreaId");
            $sPropId = $this->GetPostValue("PropId");
            $invoice = new Invoice();
            if ($sJnsLaporan == 1){
                $reports = $invoice->Load4Reports($this->userCompanyId,$sCabangId,$sGudangId,$sContactsId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate,$sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 2) {
                $reports = $invoice->Load4ReportsDetail($this->userCompanyId,$sCabangId,$sGudangId,$sContactsId, $sSalesId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate, $sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 3) {
                $reports = $invoice->Load4ReportsRekapItem($this->userCompanyId,$sCabangId,$sGudangId,$sContactsId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate, $sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 4){
                $reports = $invoice->Load4Reports($this->userCompanyId,$sCabangId,$sGudangId,$sContactsId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate,$sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 5){
                $reports = $invoice->Load4ReportsRekapItem1($this->userCompanyId,$sCabangId,$sGudangId,$sContactsId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate, $sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 6){
                $reports = $invoice->LoadSalesOmsetReports($this->userCompanyId,$sCabangId,$sGudangId,$sContactsId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate,$sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 7){
                $reports = $invoice->LoadOmsetByEntityReports($this->userCompanyId,$sCabangId,$sGudangId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate, $sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 8){
                $reports = $invoice->LoadOmsetBySalesDetailReports($this->userCompanyId,$sCabangId,$sGudangId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate, $sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }elseif ($sJnsLaporan == 9){
                $reports = $invoice->LoadOmsetByPrincipleReports($this->userCompanyId,$sCabangId,$sGudangId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate, $sEntityId,$sPrincipalId,$sPropId,$sSalesAreaId,$sBrandId,$this->userCabIds);
            }
        }else{
            $sCabangId = 0;
            $sGudangId = 0;
            $sEntityId = 0;
            $sContactsId = 0;
            $sSalesId = 0;
            $sStatus = -1;
            $sPaymentStatus = -1;
            $sOutput = 0;
            $sBrandId = 0;
            $sPrincipalId = 0;
            $sSalesAreaId = 0;
            $sPropId = 0;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            if ($month == 12) {
                $sEndDate = mktime(0, 0, 0, $month, 31, $year);
            }else{
                $sEndDate = mktime(0, 0, 0, $month + 1, 0, $year);
            }
            $sJnsLaporan = 1;
            $reports = null;
        }
        // ambil data yang diperlukan
        $customer = new Customer();
        $customer = $customer->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
        $loader = new Salesman();
        $sales = $loader->LoadAll();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadAllowedCabId($this->userCabIds);
        $loader = new Warehouse();
        $gudang = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new ItemEntity();
        $entities = $loader->LoadByCompanyId($this->userCompanyId);
        // kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("gudangs", $gudang);
        $this->Set("customers",$customer);
        $this->Set("sales",$sales);
        $this->Set("entities",$entities);
        $this->Set("CabangId",$sCabangId);
        $this->Set("EntityId",$sEntityId);
        $this->Set("GudangId",$sGudangId);
        $this->Set("ContactsId",$sContactsId);
        $this->Set("SalesId",$sSalesId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Status",$sStatus);
        $this->Set("PaymentStatus",$sPaymentStatus);
        $this->Set("Output",$sOutput);
        $this->Set("JnsLaporan",$sJnsLaporan);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        //get salesarea
        $loader = new SalesArea();
        $areas = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("areaList",$areas);
        //get brand
        $loader = new ItemBrand();
        $brands = $loader->LoadByCompanyId($this->userCompanyId,"a.brand_name");
        $this->Set("brandList",$brands);
        //get principal
        $loader = new ItemPrincipal();
        $principals = $loader->LoadByCompanyId($this->userCompanyId,"a.principal_name");
        $this->Set("principaList",$principals);
        //send to view
        $this->Set("BrandId",$sBrandId);
        $this->Set("PrincipalId",$sPrincipalId);
        $this->Set("SalesAreaId",$sSalesAreaId);
        $this->Set("PropId",$sPropId);
    }

    public function approve($token) {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.invoice");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        set_time_limit(600);
        ini_set("memory_limit", "256M");
        foreach ($ids as $id) {
            $invoice = new Invoice();
            $log = new UserAdmin();
            $invoice = $invoice->FindById($id);
            /** @var $invoice Invoice */
            // process invoice
            if ($token == 1) {
                if ($invoice->InvoiceStatus == 1) {
                    $rs = $invoice->Approve($invoice->Id, $uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Approve Invoice', $invoice->InvoiceNo, 'Success');
                        $infos[] = sprintf("Data Invoice No.: '%s' (%s) telah berhasil di-approve.", $invoice->InvoiceNo, $invoice->InvoiceDescs);
                    } else {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Approve Invoice', $invoice->InvoiceNo, 'Failed');
                        $errors[] = sprintf("Maaf, Gagal proses approve Data Invoice: '%s'. Message: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    $errors[] = sprintf("Data Invoice No.%s sudah berstatus -Approved- !", $invoice->InvoiceNo);
                }
            }else{
                if($invoice->InvoiceStatus == 2){
                    if ($invoice->PaidAmount > 0 && $invoice->PaymentType == 1) {
                        $errors[] = sprintf("Data Invoice No.%s sudah terbayar !", $invoice->InvoiceNo);
                        //}elseif($invoice->QtyReturn($invoice->Id) > 0){
                        //    $errors[] = sprintf("Data Invoice No.%s ada item yg diretur !", $invoice->InvoiceNo);
                    }else {
                        $rs = $invoice->Unapprove($invoice->Id, $uid);
                        if ($rs) {
                            $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Un-approve Invoice', $invoice->InvoiceNo, 'Success');
                            $infos[] = sprintf("Data Invoice No.: '%s' (%s) telah berhasil di-batalkan.", $invoice->InvoiceNo, $invoice->InvoiceDescs);
                        } else {
                            $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Un-approve Invoice', $invoice->InvoiceNo, 'Failed');
                            $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Invoice: '%s'. Message: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
                        }
                    }
                }else{
                    if ($invoice->InvoiceStatus == 1) {
                        $errors[] = sprintf("Data Invoice No.%s masih berstatus -POSTED- !", $invoice->InvoiceNo);
                    }elseif ($invoice->InvoiceStatus == 3){
                        $errors[] = sprintf("Data Invoice No.%s sudah berstatus -VOID- !",$invoice->InvoiceNo);
                    }else{
                        $errors[] = sprintf("Data Invoice No.%s masih berstatus -DRAFT- !",$invoice->InvoiceNo);
                    }
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.invoice");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.invoice");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $invoice = new Invoice();
            $log = new UserAdmin();
            $invoice = $invoice->FindById($id);
            /** @var $invoice Invoice */
            // process invoice
            if($invoice->InvoiceStatus == 2){
                if ($invoice->PaidAmount > 0 && $invoice->PaymentType == 1) {
                    $errors[] = sprintf("Data Invoice No.%s sudah terbayar !", $invoice->InvoiceNo);
                //}elseif($invoice->QtyReturn($invoice->Id) > 0){
                //    $errors[] = sprintf("Data Invoice No.%s ada item yg diretur !", $invoice->InvoiceNo);
                }else {
                    $rs = $invoice->Unapprove($invoice->Id, $uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Un-approve Invoice', $invoice->InvoiceNo, 'Success');
                        $infos[] = sprintf("Data Invoice No.: '%s' (%s) telah berhasil di-batalkan.", $invoice->InvoiceNo, $invoice->InvoiceDescs);
                    } else {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Un-approve Invoice', $invoice->InvoiceNo, 'Failed');
                        $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Invoice: '%s'. Message: %s", $invoice->InvoiceNo, $this->connector->GetErrorMessage());
                    }
                }
            }else{
                if ($invoice->InvoiceStatus == 1) {
                    $errors[] = sprintf("Data Invoice No.%s masih berstatus -POSTED- !", $invoice->InvoiceNo);
                }elseif ($invoice->InvoiceStatus == 3){
                    $errors[] = sprintf("Data Invoice No.%s sudah berstatus -VOID- !",$invoice->InvoiceNo);
                }else{
                    $errors[] = sprintf("Data Invoice No.%s masih berstatus -DRAFT- !",$invoice->InvoiceNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.invoice");
    }

    public function create(){
        // report sales order
        require_once(MODEL . "ar/order.php");
        require_once(MODEL . "master/warehouse.php");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCustomersId = $this->GetPostValue("CustomersId");
            $sSalesId = $this->GetPostValue("SalesId");
            $sGudangId = $this->GetPostValue("GudangId");
            $sOrderStatus = $this->GetPostValue("OrderStatus");
        }else{
            $sCustomersId = 0;
            $sSalesId = 0;
            $sGudangId = 0;
            $sOrderStatus = -1;
        }
        // ambil data yang diperlukan
        $order = new Order();
        $datas = $order->LoadOrder4Process($this->userCabangId,$sCustomersId,$sSalesId);
        // load data kirim ke view
        $loader = new Order();
        $customer = $loader->GetCustomerList();
        $this->Set("customers",$customer);
        $this->Set("CustomersId",$sCustomersId);
        $this->Set("SalesId",$sSalesId);
        $this->Set("GudangId",$sGudangId);
        $this->Set("Status",$sOrderStatus);
        $this->Set("Datas",$datas);
        //load salesman
        $loader = new Order();
        $sales = $loader->GetSalesList();
        $this->Set("sales", $sales);
        //load warehouse
        $loader = new Warehouse();
        $gudang = $loader->LoadByCabangId($this->userCabangId,1,"a.id");
        $this->Set("gudangs", $gudang);
    }

    public function generate(){
        require_once (MODEL . "inventory/stock.php");
        require_once (MODEL . "inventory/items.php");
        $log = new UserAdmin();
        $ids = $this->GetPostValue("ids", array());
        $gdi = $this->GetPostValue("GudangId");
        //sudah divalidasi harus ada yg di post
        $qry = "Select a.* From vw_ar_order_list a Where a.id IN ?ids Order By a.sales_name,a.cus_name,a.order_date,a.item_code";
        $this->connector->CommandText = $qry;
        $this->connector->AddParameter("?ids", $ids);
        $rs = $this->connector->ExecuteQuery();
        if ($rs != null){
            $ivn = null;
            $iti = 0;
            $its = 0;
            $qti = 0;
            $qto = 0;
            $sli = null;
            $csi = null;
            $ivi = 0;
            $ipr = 0;
            $pra = array();
            $prc = null;
            $invoice = null;
            $invdetail = null;
            while ($row = $rs->FetchAssoc()) {
                $iti = $row["item_id"];
                //cek stock
                $stock = new Stock();
                $its = $stock->CheckStock($this->trxYear,$gdi,$iti);
                //check harga
                $prc = $this->retItemSalePrice($row["customer_id"],$row["item_id"]);
                $pra = explode("|",$prc);
                $ipr = $pra[1];
                $qto = $row["order_qty"] - $row["send_qty"];
                if ($its > 0 && $ipr > 0){
                    if ($its >= $qto){
                        $qti = $qto;
                    }else{
                        $qti = $its;
                    }
                    if ($csi != $row["customer_id"] && $sli != $row["sales_id"]){
                        $invoice = new Invoice();
                        $invoice->CabangId = $this->userCabangId;
                        $invoice->CustomerId = $row["customer_id"];
                        $invoice->SalesId = $row["sales_id"];
                        $invoice->GudangId = $gdi;
                        $invoice->InvoiceDate = $row["request_date"];
                        $invoice->InvoiceNo = $invoice->GetInvoiceDocNo();
                        $invoice->InvoiceDescs = 'Ex. Order Tgl. '.$row["order_date"];
                        if ($row["credit_terms"] > 0){
                            $invoice->PaymentType = 1;
                            $invoice->CreditTerms = $row["credit_terms"];
                        }else{
                            $invoice->PaymentType = 1;
                            $invoice->CreditTerms = 30;
                        }
                        $invoice->InvoiceStatus = 1;
                        if ($invoice->Insert() > 0){
                            $ivi = $invoice->Id;
                        }else{
                            $ivi = 0;
                        }
                    }
                    //jika master invoice sudah ada, dan harga barang sudah disetting, lanjut...
                    if ($ivi > 0){
                        $detail = new InvoiceDetail();
                        $detail->InvoiceId = $ivi;
                        $detail->ItemId = $row["item_id"];
                        $detail->SalesQty = $qti;
                        $detail->Price = $ipr;
                        if ($detail->Price > 0 && $row["s_uom_qty"] > 0){
                            $ipr = round($detail->Price/$row["s_uom_qty"],2);
                        }else{
                            $ipr = 0;
                        }
                        $detail->SubTotal = round($detail->SalesQty * $ipr,0);
                        $detail->DiscAmount = 0;
                        $detail->DiscFormula = '0';
                        $detail->PpnPct = 10;
                        $detail->PpnAmount = round($detail->SubTotal/10,0);
                        $detail->ExSoId = $row["id"];
                        // insert ke table
                        $flagSuccess = false;
                        $this->connector->BeginTransaction();
                        $rx = $detail->Insert() == 1;
                        if ($rx > 0){
                            //pasti adalah item ini
                            $items = new Items($detail->ItemId);
                            $stock = new Stock();
                            $stocks = $stock->LoadStocksFifo($this->trxYear,$detail->ItemId,$items->SuomCode,$invoice->GudangId);
                            // Set variable-variable pendukung
                            $remainingQty = $detail->SalesQty;
                            $detail->ItemHpp = 0;
                            $hpp = 0;
                            /** @var $stocks Stock[] */
                            foreach ($stocks as $stock) {
                                // Buat object stock keluarnya
                                $issue = new Stock();
                                $issue->TrxYear = $this->trxYear;
                                $issue->CreatedById = $this->userUid;
                                $issue->StockTypeCode = 101;                // Item Issue dari IS
                                $issue->ReffId = $detail->Id;
                                $issue->TrxDate = strtotime($invoice->InvoiceDate);
                                $issue->WarehouseId = $invoice->GudangId;    // Gudang asal!
                                $issue->ItemId = $detail->ItemId;
                                //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                                $issue->UomCode = $items->SuomCode;
                                $issue->Price = $stock->Price;                // Ya pastilah pake angka ini...
                                $issue->UseStockId = $stock->Id;            // Kasi tau kalau issue ini based on stock id mana
                                $issue->QtyBalance = null;                    // Klo issue harus NULL

                                $stock->UpdatedById = $this->userUid;

                                if ($remainingQty > $stock->QtyBalance) {
                                    // Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
                                    $issue->Qty = $stock->QtyBalance;            // Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya
                                    $remainingQty -= $stock->QtyBalance;        // Kita masih perlu...
                                    $stock->QtyBalance = 0;                        // Habis...
                                } else {
                                    // Barang di gudang mencukupi atau PAS
                                    $issue->Qty = $remainingQty;
                                    $stock->QtyBalance -= $remainingQty;
                                    $remainingQty = 0;
                                }
                                $hpp+= $issue->Qty * $issue->Price;
                                // Apapun yang terjadi masukkan data issue stock
                                if ($issue->Insert() > 0) {
                                    $flagSuccess = true;
                                } else {
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName);
                                    break;        // Break loop stocks
                                }
                                // update hpp detail
                                //if ($hpp > 0) {
                                    if ($hpp > 0) {
                                        $detail->ItemHpp = round($hpp / $detail->SalesQty, 2);
                                    }else{
                                        $detail->ItemHpp = 0;
                                    }
                                    $detail->IsPost = 1;
                                    if ($detail->UpdateHpp() > 0) {
                                        $flagSuccess = true;
                                    } else {
                                        $flagSuccess = false;
                                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update hpp item invoice ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                        break;
                                    }
                                //}else{
                                //    $flagSuccess = false;
                                //    $errors[] = sprintf("%s -> Item: [%s] %s Message: HPP Tidak dihitung (Kosong) ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                //    break;
                                //}
                                // Update Qty Balance
                                if ($stock->Update($stock->Id) > 0) {
                                    $flagSuccess = true;
                                } else {
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $invoice->InvoiceNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                    break;        // Break loop stocks
                                }
                                // OK jangan lupa update data cost
                                //$invoicedetail->Hpp += $issue->Qty * $issue->Price;
                                if ($remainingQty <= 0) {
                                    $flagSuccess = true;
                                    // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                                    break;
                                }
                            }
                        }
                        if ($flagSuccess) {
                            //update sales order send qty
                            $this->connector->CommandText = "Update t_ar_order a Set a.send_qty = a.send_qty + $qti Where a.id = ".$row["id"];
                            $rz = $this->connector->ExecuteNonQuery();
                            $this->connector->CommitTransaction();
                            //$log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add Invoice detail -> Item Code: ' . $detail->ItemCode . ' = ' . $detail->SalesQty, $invoice->InvoiceNo, 'Success');
                            //echo json_encode(array());
                        } else {
                            $this->connector->RollbackTransaction();
                            //$log = $log->UserActivityWriter($this->userCabangId, 'ar.invoice', 'Add Invoice detail -> Item Code: ' . $detail->ItemCode . ' = ' . $detail->SalesQty, $invoice->InvoiceNo, 'Failed');
                            //echo json_encode(array('errorMsg' =>$errors));
                        }
                    }
                    $csi = $row["customer_id"];
                    $sli = $row["sales_id"];
                }else{
                    $ket = null;
                    if ($its == 0){
                        $ket.= "S0";
                    }
                    if ($ipr == 0){
                        if (strlen($ket) > 0) {
                            $ket .= ":P0";
                        }else{
                            $ket .= "P0";
                        }
                    }
                    $this->connector->CommandText = "Update t_ar_order a Set a.keterangan = '".$ket."' Where a.id = ".$row["id"];
                    $rz = $this->connector->ExecuteNonQuery();
                }
            }
        }
        redirect_url("ar.invoice/create");
    }
}


// End of File: estimasi_controller.php
