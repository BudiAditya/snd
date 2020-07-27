<?php
class InvocasController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;
    private $userCabIds;

    protected function Initialize() {
        require_once(MODEL . "tvd/invocas.php");
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
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.invoice_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.invoice_no", "display" => "No. Invoice", "width" => 100);
        $settings["columns"][] = array("name" => "a.customer_code", "display" => "Kode", "width" => 50);
        $settings["columns"][] = array("name" => "a.customer_name", "display" => "Nama Customer", "width" => 200);
        $settings["columns"][] = array("name" => "format(a.base_amount,0)", "display" => "Jumlah", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.disc_amount,0)", "display" => "Diskon", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.base_amount - a.disc_amount,0)", "display" => "DPP", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.ppn_amount,0)", "display" => "PPN", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.total_amount,0)", "display" => "Total", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "a.sales_name", "display" => "Salesman", "width" => 100);

        $settings["filters"][] = array("name" => "a.invoice_no", "display" => "No. Invoice");
        $settings["filters"][] = array("name" => "a.invoice_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.customer_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_invoice"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Invoice Penjualan Castrol";

            if ($acl->CheckUserAccess("tvd.invocas", "add")) {
                $settings["actions"][] = array("Text" => "<b>Add</b>", "Url" => "tvd.invocas/add/0", "Class" => "bt_add", "ReqId" => 0);
            }

            if ($acl->CheckUserAccess("tvd.invocas", "edit")) {
                $settings["actions"][] = array("Text" => "<b>Edit</b>", "Url" => "tvd.invocas/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("tvd.invocas", "delete")) {
                $settings["actions"][] = array("Text" => "<b>Delete</b>", "Url" => "tvd.invocas/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("tvd.invocas", "view")) {
                $settings["actions"][] = array("Text" => "<b>View</b>", "Url" => "tvd.invocas/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("tvd.invocas", "print")) {
                $settings["actions"][] = array("Text" => "Print Invoice", "Url" => "tvd.invocas/printout/invoice","Class" => "bt_print", "Target" => "_blank", "ReqId" => 2, "Confirm" => "Pastikan Data sudah di-approve\nCetak Invoice yang dipilih?");
                //$settings["actions"][] = array("Text" => "Print Surat Jalan", "Url" => "tvd.invocas/printout/suratjalan","Class" => "bt_print", "Target" => "_blank", "ReqId" => 2, "Confirm" => "Pastikan Data sudah di-approve\nCetak Surat Jalan yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("tvd.invocas", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "tvd.invocas/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_cas_invoice_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.invoice_date) = ".$this->trxYear;//." And month(a.invoice_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    public function add($id = 0){
        //proses rekap dll
        if (count($this->postData) > 0) {
            $tahun = $this->GetPostValue("Tahun");
            $bulan = $this->GetPostValue("Bulan");
            $output = $this->GetPostValue("Output");
        }else{
            $tahun = $this->trxYear;
            $bulan = $this->trxMonth;
            $output = 0;
        }
        $loader = new Invocas();
        $invoice= $loader->LoadInvoiceSourceByMonth($tahun,$bulan);
        $this->Set("tahun", $tahun);
        $this->Set("bulan", $bulan);
        $this->Set("output", $output);
        $this->Set("invoices", $invoice);
    }

    public function create(){
        $invocas = new Invocas();
        $ids = $this->GetPostValue("ids", array());
        foreach ($ids as $detailId){
            $result  = $invocas->CreateDetail($detailId);
        }
        redirect_url("tvd.invocas");
    }

    public function proses_master($invoiceId = 0){
        require_once (MODEL . "master/cabang.php");
        $log = new UserAdmin();
        $invoice = new Invocas();
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
                        $log = $log->UserActivityWriter($this->userCabangId, 'tvd.invocas', 'Add New Invoice', $invoice->InvoiceNo, 'Success');
                        printf("OK|A|%d|%s|%s",$invoice->Id,$invoice->InvoiceNo,'Success!');
                    }else{
                        if ($this->connector->IsDuplicateError()) {
                            $err ="Maaf Nomor Invoice sudah ada pada database";
                        } else {
                            $err = "Maaf error saat simpan data. Message: " . $this->connector->GetErrorMessage();
                        }
                        $log = $log->UserActivityWriter($this->userCabangId, 'tvd.invocas', 'Add New Invoice', $invoice->InvoiceNo, 'Failed');
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
                        $log = $log->UserActivityWriter($this->userCabangId, 'tvd.invocas', 'Update Invoice', $invoice->InvoiceNo, 'Success');
                        printf("OK|U|%d|%s|%s",$invoice->Id,$invoice->InvoiceNo,'Success!');
                    }else{
                        if ($this->connector->IsDuplicateError()) {
                            $err ="Maaf Nomor Invoice sudah ada pada database";
                        } else {
                            $err = "Maaf error saat update data. Message: " . $this->connector->GetErrorMessage();
                        }
                        $log = $log->UserActivityWriter($this->userCabangId, 'tvd.invocas', 'Update Invoice', $invoice->InvoiceNo, 'Failed');
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
        $invoice = new Invocas();
        if ($invoiceId > 0){
            $invoice = $invoice->LoadById($invoiceId);
            if($invoice == null){
                $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("tvd.invocas");
            }
            /*
            if($invoice->InvoiceStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -TERBAYAR-",$invoice->InvoiceNo));
                redirect_url("tvd.invocas");
            }
            */
            if($invoice->InvoiceStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -VOID-",$invoice->InvoiceNo));
                redirect_url("tvd.invocas/view/".$invoiceId);
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
            redirect_url("tvd.invocas");
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
        $invoice = new Invocas();
        $invoice = $invoice->LoadById($invoiceId);
        if($invoice == null){
            $this->persistence->SaveState("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("tvd.invocas");
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
        $gudangs = $loader->LoadByCabangId($this->userCabangId);
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

    public function InvoiceItemsCount($id){
        $invoice = new Invocas();
        $rows = $invoice->GetInvoiceItemRow($id);
        return $rows;
    }

    public function delete($invoiceId = 0) {
        // Cek datanya
        $ExSoId = null;
        $log = new UserAdmin();
        $invoice = new Invocas();
        $invoice = $invoice->FindById($invoiceId);
        if($invoice == null){
            $this->Set("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("tvd.invocas");
        }
        if($invoice->InvoiceStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -VOID-",$invoice->InvoiceNo));
            redirect_url("tvd.invocas");
        }
        /*
        if($invoice->InvoiceStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -APPROVED-",$invoice->InvoiceNo));
            redirect_url("tvd.invocas");
        }

        if($invoice->BaseAmount > 0){
            $this->persistence->SaveState("error", sprintf("Maaf hapus dulu detail Invoice No. %s sebelum diproses!",$invoice->InvoiceNo));
            redirect_url("tvd.invocas");
        }
        */
        $ExSoId = $invoice->ExSoId;
        // periksa status po
        if($invoice->InvoiceStatus < 3){
            $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($invoice->Delete($invoiceId,$ExSoId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'tvd.invocas','Delete Invoice',$invoice->InvoiceNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Invoice No: %s sudah berhasil batalkan", $invoice->InvoiceNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'tvd.invocas','Delete Invoice',$invoice->InvoiceNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s gagal dibatalkan", $invoice->InvoiceNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s sudah berstatus -TERBAYAR-", $invoice->InvoiceNo));
        }
        redirect_url("tvd.invocas");
    }

    public function void($invoiceId = 0) {
        // Cek datanya
        $ExSoId = null;
        $log = new UserAdmin();
        $invoice = new Invocas();
        $invoice = $invoice->FindById($invoiceId);
        if($invoice == null){
            $this->Set("error", "Maaf Data Invoice dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("tvd.invocas");
        }
        if($invoice->InvoiceStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -VOID-",$invoice->InvoiceNo));
            redirect_url("tvd.invocas");
        }
        /*
        if($invoice->InvoiceStatus == 2){
            $this->persistence->SaveState("error", sprintf("Maaf Data Invoice No. %s sudah berstatus -APPROVED-",$invoice->InvoiceNo));
            redirect_url("tvd.invocas");
        }

        if($invoice->BaseAmount > 0){
            $this->persistence->SaveState("error", sprintf("Maaf hapus dulu detail Invoice No. %s sebelum diproses!",$invoice->InvoiceNo));
            redirect_url("tvd.invocas");
        }
        */
        $ExSoId = $invoice->ExSoId;
        // periksa status po
        if($invoice->InvoiceStatus < 3){
            $invoice->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($invoice->Void($invoiceId,$ExSoId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'tvd.invocas','Delete Invoice',$invoice->InvoiceNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Invoice No: %s sudah berhasil batalkan", $invoice->InvoiceNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'tvd.invocas','Delete Invoice',$invoice->InvoiceNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s gagal dibatalkan", $invoice->InvoiceNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Invoice No: %s sudah berstatus -TERBAYAR-", $invoice->InvoiceNo));
        }
        redirect_url("tvd.invocas");
    }

    public function add_detail($invoiceId = 0) {
        if ($invoiceId > 0) {
            $invoice = new Invocas($invoiceId);
            $invoicedetail = new InvocasDetail();
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
                $invoicedetail->PpnPct = $this->GetPostValue("aPpnPct");
                $invoicedetail->PpnAmount = $this->GetPostValue("aPpnAmount");
                // insert ke table
                $rs = $invoicedetail->Insert() == 1;
                if ($rs == 0){
                    echo json_encode(array('errorMsg' => 'Data Detail gagal disimpan!'));
                }
            }
        }else{
            echo json_encode(array('errorMsg' => 'Data Master belum ada!'));
        }
    }

    public function edit_detail($invoiceId = 0,$detailId = 0) {
        if ($invoiceId > 0 && $detailId > 0) {
            $invoice = new Invocas($invoiceId);
            $invoicedetail = new InvocasDetail();
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
                $rs = $invoicedetail->Update($detailId) == 1;
                if ($rs == 0){
                    echo json_encode(array('errorMsg' => 'Data Detail gagal diupdate!'));
                }

            }
        }else{
            echo json_encode(array('errorMsg' => 'Data detail tidak ditemukan!'));
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $invoicedetail = new InvocasDetail();
        $invoicedetail = $invoicedetail->FindById($id);
        if ($invoicedetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($invoicedetail->Delete($id) == 1) {
            printf("Data Detail Invoice ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail Invoice ID: %d gagal dihapus!",$id);
        }
    }
}


// End of File: estimasi_controller.php
