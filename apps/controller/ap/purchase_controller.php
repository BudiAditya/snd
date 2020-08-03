<?php
class PurchaseController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ap/purchase.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();
        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        //$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Entity", "width" => 30);
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.grn_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.grn_no", "display" => "No. Purchase", "width" => 110);
        $settings["columns"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier", "width" => 200);
        //$settings["columns"][] = array("name" => "a.grn_descs", "display" => "Keterangan", "width" => 150);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Cara Bayar", "width" => 80);
        $settings["columns"][] = array("name" => "a.due_date", "display" => "JTP", "width" => 60);
        $settings["columns"][] = array("name" => "format(a.total_amount,0)", "display" => "Nilai Pembelian", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.return_amount,0)", "display" => "Retur", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.paid_amount,0)", "display" => "Terbayar", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.balance_amount,0)", "display" => "OutStanding", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 80);
        $settings["columns"][] = array("name" => "if(a.grn_status = 0,'Draft',if(a.grn_status = 1,'Posted',if(a.grn_status = 2,'Approved','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.grn_no", "display" => "No. Purchase");
        $settings["filters"][] = array("name" => "a.grn_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier");
        $settings["filters"][] = array("name" => "if(a.grn_status = 0,'Draft',if(a.grn_status = 1,'Posted',if(a.grn_status = 2,'Approved','Void')))", "display" => "Status");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_purchase"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Pembelian Barang";

            if ($acl->CheckUserAccess("ap.purchase", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.purchase/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.purchase", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.purchase/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Purchase terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.purchase", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "ap.purchase/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.purchase", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.purchase/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Purchase terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.purchase", "print")) {
                $settings["actions"][] = array("Text" => "Print Bukti", "Url" => "ap.purchase/grn_print","Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "Cetak Bukti Pembelian yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.purchase", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.purchase/report", "Class" => "bt_report", "ReqId" => 0);
            }

            if ($acl->CheckUserAccess("ap.purchase", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve Pembelian", "Url" => "ap.purchase/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Pembelian terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data pembelian yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approve", "Url" => "ap.purchase/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Pembelian terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data pembelian yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }

        } else {
            $settings["from"] = "vw_ap_purchase_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.grn_date) = ".$this->trxYear." And month(a.grn_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.grn_date) = ".$this->trxYear;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add($purchaseId = 0) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/expedition.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        if ($purchaseId > 0) {
            $purchase = $purchase->LoadById($purchaseId);
            if($purchase == null){
                $this->persistence->SaveState("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ap.purchase");
            }
            if($purchase->GrnStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -TERBAYAR-",$purchase->GrnNo));
                redirect_url("ap.purchase");
            }
            if($purchase->GrnStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -VOID-",$purchase->GrnNo));
                redirect_url("ap.purchase/view/".$purchaseId);
            }
            if ($purchase->CreatebyId <> AclManager::GetInstance()->GetCurrentUser()->Id && $this->userLevel == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Anda tidak boleh mengubah data ini!",$purchase->GrnNo));
                redirect_url("ap.purchase");
            }
        }else{
            $purchase->CabangId = $this->userCabangId;
        }
        // load details
        $purchase->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        if ($cabang->CabType == 2){
            $this->persistence->SaveState("error", "Maaf Cabang %s dalam mode Gudang, tidak boleh digunakan untuk transaksi!",$cabang->Kode);
            redirect_url("ap.purchase");
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
        $this->Set("purchase", $purchase);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->GrnItemsCount($purchaseId));
        //load expedisi
        $loader = new Expedition();
        $expedisi = $loader->LoadAll();
        $this->Set("expedition", $expedisi);
	}

    public function proses_master($purchaseId = 0){
        require_once (MODEL . "master/cabang.php");
        $log = new UserAdmin();
        $purchase = new Purchase();
        if (count($this->postData) > 0) {
            $purchase->CabangId = $this->userCabangId;
            $purchase->GudangId = $this->GetPostValue("GudangId");
            $purchase->GrnDate = $this->GetPostValue("GrnDate");
            $purchase->ReceiptDate = $this->GetPostValue("ReceiptDate");
            $purchase->GrnNo = $this->GetPostValue("GrnNo");
            $purchase->GrnDescs = $this->GetPostValue("GrnDescs");
            $purchase->SupplierId = $this->GetPostValue("SupplierId");
            $purchase->SalesName = $this->GetPostValue("SalesName");
            $purchase->ExPoId = $this->GetPostValue("ExPoId");
            $purchase->ExpeditionId = $this->GetPostValue("ExpeditionId");
            $purchase->SupInvDate = strtotime($this->GetPostValue("SupInvDate"));
            $purchase->SupInvNo = $this->GetPostValue("SupInvNo");
            $purchase->JtpDate = strtotime($this->GetPostValue("JtpDate"));
            $purchase->NsfPajak = $this->GetPostValue("NsfPajak");
            if ($this->GetPostValue("GrnStatus") == null || $this->GetPostValue("GrnStatus") == 0){
                $purchase->GrnStatus = 1;
            }else{
                $purchase->GrnStatus = $this->GetPostValue("GrnStatus");
            }
            $purchase->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            $purchase->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->GetPostValue("PaymentType") == null){
                $purchase->PaymentType = 0;
            }else{
                $purchase->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $purchase->CreditTerms = 0;
            }else{
                $purchase->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            if ($this->ValidateMaster($purchase)) {
                if ($purchaseId == 0) {
                    $purchase->BaseAmount = 0;
                    $purchase->DiscAmount = 0;
                    $purchase->PpnAmount = 0;
                    $purchase->PphAmount = 0;
                    $purchase->OtherCosts = '-';
                    $purchase->OtherCostsAmount = 0;
                    $purchase->PaidAmount = 0;
                    if ($purchase->GrnNo == null || $purchase->GrnNo == "-" || $purchase->GrnNo == ""){
                        $purchase->GrnNo = $purchase->GetGrnDocNo();
                    }
                    $rs = $purchase->Insert();
                    if ($rs == 1) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ap.purchase', 'Add New Purchase', $purchase->GrnNo, 'Success');
                        printf("OK|A|%d|%s|%s",$purchase->Id,$purchase->GrnNo,'Success!');
                    }else{
                        if ($this->connector->IsDuplicateError()) {
                            $err ="Maaf Nomor Purchase sudah ada pada database";
                        } else {
                            $err = "Maaf error saat simpan data. Message: " . $this->connector->GetErrorMessage();
                        }
                        $log = $log->UserActivityWriter($this->userCabangId, 'ap.purchase', 'Add New Purchase', $purchase->GrnNo, 'Failed');
                        printf("ER|A|%d|%s|%s",$purchase->Id,'0',$err);
                    }
                }else{
                    if ($purchase->PaymentType == 0){
                        $purchase->PaidAmount = 0;
                    }
                    $purchase->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                    $rs = $purchase->Update($purchaseId);
                    if ($rs == 1) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ap.purchase', 'Update Purchase', $purchase->GrnNo, 'Success');
                        printf("OK|U|%d|%s|%s",$purchaseId,$purchase->GrnNo,'Success!');
                    }else{
                        if ($this->connector->IsDuplicateError()) {
                            $err ="Maaf Nomor Purchase sudah ada pada database";
                        } else {
                            $err = "Maaf error saat update data. Message: " . $this->connector->GetErrorMessage();
                        }
                        $log = $log->UserActivityWriter($this->userCabangId, 'ap.purchase', 'Update Purchase', $purchase->GrnNo, 'Failed');
                        printf("ER|U|%d|%s|%s",$purchaseId,'0',$err);
                    }
                }
            }else{
                printf("ER|X|%d|%s|%s",$purchaseId,'0',$purchase->ErrorMsg);
            }
        }else{
            printf("ER|X|%d|%s|%s",$purchaseId,'0','No Data posted!');
        }
    }

	private function ValidateMaster(Purchase $purchase) {
        if ($purchase->SupplierId == 0 || $purchase->SupplierId == null || $purchase->SupplierId == ''){
            $this->Set("error", "Supplier tidak boleh kosong!");
            return false;
        }
        if ($purchase->PaymentType == 1 && $purchase->CreditTerms == 0){
            $this->Set("error", "Lama kredit belum diisi!");
            return false;
        }
        if ($purchase->GudangId == 0 || $purchase->GudangId == ""){
            $this->Set("error", "Gudang tujuan belum dipilih!");
            return false;
        }
        //if ($purchase->ExpeditionId == 0 || $purchase->ExpeditionId == ""){
        //    $this->Set("error", "Expedisi belum dipilih!");
        //    return false;
        //}
        if (date('Y',strtotime($purchase->GrnDate)) != $this->trxYear){
            $this->Set("error", "Tahun Transaksi salah! harusnya tahun = ".$this->trxYear);
            return false;
        }
		return true;
	}

    public function edit($purchaseId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/expedition.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        if ($purchaseId > 0) {
            $purchase = $purchase->LoadById($purchaseId);
            if($purchase == null){
               $this->persistence->SaveState("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ap.purchase");
            }
            if($purchase->GrnStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -TERBAYAR-",$purchase->GrnNo));
                redirect_url("ap.purchase");
            }
            if($purchase->GrnStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -VOID-",$purchase->GrnNo));
                redirect_url("ap.purchase/view/".$purchaseId);
            }
            if ($purchase->CreatebyId <> AclManager::GetInstance()->GetCurrentUser()->Id && $this->userLevel == 1){
                $this->persistence->SaveState("error", sprintf("Maaf Anda tidak boleh mengubah data ini!",$purchase->GrnNo));
                redirect_url("ap.purchase");
            }
        }else{
            $purchase->CabangId = $this->userCabangId;
        }
        // load details
        $purchase->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        if ($cabang->CabType == 2){
            $this->persistence->SaveState("error", "Maaf Cabang %s dalam mode Gudang, tidak boleh digunakan untuk transaksi!",$cabang->Kode);
            redirect_url("ap.purchase");
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
        $this->Set("purchase", $purchase);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->GrnItemsCount($purchaseId));
        //load expedisi
        $loader = new Expedition();
        $expedisi = $loader->LoadAll();
        $this->Set("expedition", $expedisi);
    }

	public function view($purchaseId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/expedition.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $purchase = new Purchase();
        $purchase = $purchase->LoadById($purchaseId);
        if($purchase == null){
            $this->persistence->SaveState("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.purchase");
        }
        // load details
        $purchase->LoadDetails();
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
        $this->Set("purchase", $purchase);
        $this->Set("acl", $acl);
        //load expedisi
        $loader = new Expedition();
        $expedisi = $loader->LoadAll();
        $this->Set("expedition", $expedisi);
        $this->Set("itemsCount", $this->GrnItemsCount($purchaseId));
	}

    public function delete($purchaseId) {
        // Cek datanya
        $ExPoId = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        $purchase = $purchase->FindById($purchaseId);
        if($purchase == null){
            $this->Set("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.purchase");
        }
        // periksa status po
        $ExPoId = $purchase->ExPoId;
        if($purchase->GrnStatus < 2){
            $purchase->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($purchase->Delete($purchaseId,$ExPoId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Purchase No: %s sudah berhasil dihapus", $purchase->GrnNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s gagal dihapus", $purchase->GrnNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s sudah berstatus -TERBAYAR-", $purchase->GrnNo));
        }
        redirect_url("ap.purchase");
    }

    public function void($purchaseId) {
        // Cek datanya
        $ExPoId = null;
        $log = new UserAdmin();
        $purchase = new Purchase();
        $purchase = $purchase->FindById($purchaseId);
        if($purchase == null){
            $this->Set("error", "Maaf Data Purchase dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.purchase");
        }
        if($purchase->GrnStatus == 3){
            $this->persistence->SaveState("error", sprintf("Maaf Data Purchase No. %s sudah berstatus -VOID-",$purchase->GrnNo));
            redirect_url("ap.purchase");
        }
        $ExPoId = $purchase->ExPoId;
        // periksa status po
        if($purchase->GrnStatus < 2){
            $purchase->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($purchase->Void($purchaseId,$ExPoId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Purchase No: %s sudah berhasil batalkan", $purchase->GrnNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase',$purchase->GrnNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s gagal dibatalkan", $purchase->GrnNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Purchase No: %s sudah berstatus -TERBAYAR-", $purchase->GrnNo));
        }
        redirect_url("ap.purchase");
    }

	public function add_detail($purchaseId = null) {
        $log = new UserAdmin();
        $purchase = new Purchase($purchaseId);
        $purchasedetail = new PurchaseDetail();
        $purchasedetail->GrnId = $purchaseId;
        $items = 1;
        if (count($this->postData) > 0) {
            $purchasedetail->ItemId = $this->GetPostValue("aItemId");
            $purchasedetail->ExPoId = $this->GetPostValue("aExPoId");
            $purchasedetail->PurchaseQty = $this->GetPostValue("aQty");
            $purchasedetail->ReturnQty = 0;
            $purchasedetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $purchasedetail->DiscFormula = 0;
            }else{
                $purchasedetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $purchasedetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $purchasedetail->SubTotal = $this->GetPostValue("aSubTotal");
            $purchasedetail->IsFree = $this->GetPostValue("aIsFree");
            $purchasedetail->PphPct = $this->GetPostValue("aPphPct");
            $purchasedetail->PphAmount = $this->GetPostValue("aPphAmount");
            $purchasedetail->PpnPct = $this->GetPostValue("aPpnPct");
            $purchasedetail->PpnAmount = $this->GetPostValue("aPpnAmount");
            $purchasedetail->ByAngkut = $this->GetPostValue("aByAngkut");
            $purchasedetail->BatchNo = $this->GetPostValue("aBatchNo");
            if ($items != null){
                // insert ke table
                $rs = $purchasedetail->Insert()== 1;
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Add Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Success');
                    echo json_encode(array());
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Add Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Failed');
                    echo json_encode(array('errorMsg'=>'Some errors occured.'));
                }
            }else{
                echo json_encode(array('errorMsg'=>'Data barang tidak ditemukan!'));
            }
        }
	}

    public function edit_detail($purchaseId = null) {
        $log = new UserAdmin();
        $purchase = new Purchase($purchaseId);
        $purchasedetail = new PurchaseDetail();
        $purchasedetail->GrnId = $purchaseId;
        $items = 1;
        if (count($this->postData) > 0) {
            $purchasedetail->Id = $this->GetPostValue("aId");
            $purchasedetail->ExPoId = $this->GetPostValue("aExPoId");
            $purchasedetail->ItemId = $this->GetPostValue("aItemId");
            $purchasedetail->PurchaseQty = $this->GetPostValue("aQty");
            $purchasedetail->ReturnQty = $this->GetPostValue("rQty");
            $purchasedetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $purchasedetail->DiscFormula = 0;
            }else{
                $purchasedetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $purchasedetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $purchasedetail->SubTotal = $this->GetPostValue("aSubTotal");
            $purchasedetail->IsFree = $this->GetPostValue("aIsFree");
            $purchasedetail->PphPct = $this->GetPostValue("aPphPct");
            $purchasedetail->PphAmount = $this->GetPostValue("aPphAmount");
            $purchasedetail->PpnPct = $this->GetPostValue("aPpnPct");
            $purchasedetail->PpnAmount = $this->GetPostValue("aPpnAmount");
            $purchasedetail->ByAngkut = $this->GetPostValue("aByAngkut");
            $purchasedetail->BatchNo = $this->GetPostValue("aBatchNo");
            if ($items != null){
                // insert ke table
                $rs = $purchasedetail->Update($purchasedetail->Id);
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Update Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Success');
                    echo json_encode(array());
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Update Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchase->GrnNo,'Failed');
                    echo json_encode(array('errorMsg'=>'Some errors occured.'));
                }
            }else{
                echo json_encode(array('errorMsg'=>'Data barang tidak ditemukan!'));
            }
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $purchasedetail = new PurchaseDetail();
        $purchasedetail = $purchasedetail->FindById($id);
        if ($purchasedetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($purchasedetail->Delete($id) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchasedetail->GrnId,'Success');
            printf("Data Detail Purchase ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Delete Purchase detail -> Item Code: '.$purchasedetail->ItemCode.' = '.$purchasedetail->PurchaseQty,$purchasedetail->GrnId,'Failed');
            printf("Maaf, Data Detail Purchase ID: %d gagal dihapus!",$id);
        }
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "ap/supplier.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sContactsId = $this->GetPostValue("ContactsId");
            $sStatus = $this->GetPostValue("Status");
            $sPaymentStatus = $this->GetPostValue("PaymentStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $purchase = new Purchase();
            if ($sJnsLaporan == 1) {
                $reports = $purchase->Load4Reports($this->userCompanyId, $sCabangId, $sContactsId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $purchase->Load4ReportsDetail($this->userCompanyId, $sCabangId, $sContactsId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate);
            }else{
                $reports = $purchase->Load4ReportsRekapItem($this->userCompanyId, $sCabangId, $sContactsId, $sStatus, $sPaymentStatus, $sStartDate, $sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sContactsId = 0;
            $sStatus = -1;
            $sPaymentStatus = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sJnsLaporan = 1;
            $sOutput = 0;
            $reports = null;
        }
        $supplier = new Supplier();
        $supplier = $supplier->LoadAll();
        $company = new Company($this->userCompanyId);
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        if ($this->userLevel > 3){
            $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        }else{
            $cabang = $loader->LoadById($this->userCabangId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("suppliers",$supplier);
        $this->Set("CabangId",$sCabangId);
        $this->Set("ContactsId",$sContactsId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Status",$sStatus);
        $this->Set("PaymentStatus",$sPaymentStatus);
        $this->Set("JnsLaporan",$sJnsLaporan);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("company_name", $company->CompanyName);
    }

    public function getjson_grnlists($cabangId,$supplierId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $purchases = new Purchase();
        $grnlists = $purchases->GetJSonGrns($cabangId,$supplierId,$filter);
        echo json_encode($grnlists);
    }

    public function getjson_grnitems($grnId = 0){
        $purchases = new Purchase();
        $itemlists = $purchases->GetJSonGrnItems($grnId);
        echo json_encode($itemlists);
    }

    public function GrnItemsCount($grnId){
        $purchases = new Purchase();
        $rows = $purchases->GetGrnItemCount($grnId);
        return $rows;
    }

    //proses cetak bukti pembelian
    public function grn_print($doctype = 'grn') {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Harap pilih data yang akan dicetak !");
            redirect_url("ap.purchase");
            return;
        }
        $report = array();
        foreach ($ids as $id) {
            $grn = new Purchase();
            $grn = $grn->LoadById($id);
            $grn->LoadDetails();
            $report[] = $grn;
        }

        $this->Set("doctype", $doctype);
        $this->Set("report", $report);
    }

    public function getitems_json($principalId = 0,$order = "a.item_code"){
        require_once(MODEL . "inventory/items.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $items = new Items();
        $itemlists = $items->GetJSonItems($this->userCompanyId,$this->userCabangId,$principalId,$filter,$order);
        echo json_encode($itemlists);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di Approve !");
            redirect_url("ap.purchase");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $purchase = new Purchase();
            $log = new UserAdmin();
            $purchase = $purchase->FindById($id);
            /** @var $purchase Purchase */
            // process purchase
            if($purchase->GrnStatus == 1){
                $rs = $purchase->Approve($purchase->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Approve Pembelian',$purchase->GrnNo,'Success');
                    $infos[] = sprintf("Data Pembelian No: '%s' (%s) telah berhasil di-approve.", $purchase->GrnNo, $purchase->GrnDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Approve Pembelian',$purchase->GrnNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Pembelian: '%s'. Message: %s", $purchase->GrnNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Pembelian No.%s sudah berstatus -Approved- !",$purchase->GrnNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.purchase");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.purchase");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $purchase = new Purchase();
            $log = new UserAdmin();
            $purchase = $purchase->FindById($id);
            /** @var $purchase Purchase */
            // process invoice
            if($purchase->GrnStatus == 2){
                $rs = $purchase->Unapprove($purchase->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Un-approve Pembelian',$purchase->GrnNo,'Success');
                    $infos[] = sprintf("Approval Data Pembelian No: '%s' (%s) telah berhasil di-batalkan.", $purchase->GrnNo, $purchase->GrnDescs);
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'ap.purchase','Un-approve Pembelian',$purchase->GrnNo,'Failed');
                    $errors[] = sprintf("Maaf, Gagal proses pembatalan Data Pembelian: '%s'. Message: %s", $purchase->GrnNo, $this->connector->GetErrorMessage());
                }
            }else{
                if ($purchase->GrnStatus == 1){
                    $errors[] = sprintf("Data Pembelian No.%s masih berstatus -POSTED- !",$purchase->GrnNo);
                }else{
                    $errors[] = sprintf("Data Pembelian No.%s masih berstatus -DRAFT- !",$purchase->GrnNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.purchase");
    }

    public function getjson_polists($supplierId){
        require_once (MODEL . "ap/order.php");
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new Order();
        $polists = $order->GetActivePoList($this->userCabangId,$supplierId);
        echo json_encode($polists);
    }

    public function getjson_poitems($poId){
        require_once (MODEL . "ap/order.php");
        //$filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new Order();
        $poitems = $order->GetItemPoItems($poId);
        echo json_encode($poitems);
    }
}


// End of File: estimasi_controller.php
