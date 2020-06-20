<?php
class OrderController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ap/order.php");
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
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 80);
        $settings["columns"][] = array("name" => "a.po_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.po_no", "display" => "No. Order", "width" => 80);
        $settings["columns"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier", "width" => 200);
        $settings["columns"][] = array("name" => "a.po_descs", "display" => "Keterangan", "width" => 160);
        $settings["columns"][] = array("name" => "a.sales_name", "display" => "Salesman", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.payment_type = 0,'Cash','Credit')", "display" => "Cara Bayar", "width" => 80);
        $settings["columns"][] = array("name" => "format(a.total_amount,0)", "display" => "Nilai Order", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 100);
        $settings["columns"][] = array("name" => "if(a.po_status = 0,'Draft',if(a.po_status = 1,'Open','Closed'))", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");
        $settings["filters"][] = array("name" => "a.po_no", "display" => "No. Order");
        $settings["filters"][] = array("name" => "a.po_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier");
        $settings["filters"][] = array("name" => "if(a.po_status = 0,'Draft','Posted')", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Purchase Order (PO)";

            if ($acl->CheckUserAccess("ap.order", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.order/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.order", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.order/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Order terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.order", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "ap.order/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.order", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.order/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Order terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.order", "print")) {
                $settings["actions"][] = array("Text" => "Print PO", "Url" => "ap.order/order_print","Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "Cetak P/O yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.order", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.order/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_ap_po_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.po_date) = ".$this->trxYear." And month(a.po_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add() {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "ap/supplier.php");
        $loader = null;
		$order = new Order();
        $order->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
			$order->CabangId = $this->GetPostValue("CabangId");
			$order->PoDate = $this->GetPostValue("PoDate");
            $order->RequestDate = $this->GetPostValue("RequestDate");
            if ($order->RequestDate == '' || $order->RequestDate == null){
                $order->RequestDate = $order->PoDate;
            }
            $order->PoNo = $this->GetPostValue("PoNo");
            $order->PoDescs = $this->GetPostValue("PoDescs");
            $order->SupplierId = $this->GetPostValue("SupplierId");
            $order->SalesName = $this->GetPostValue("SalesName");
            if ($this->GetPostValue("PoStatus") == null || $this->GetPostValue("PoStatus") == 0){
                $order->PoStatus = 1;
            }else{
                $order->PoStatus = $this->GetPostValue("PoStatus");
            }
            $order->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->GetPostValue("PaymentType") == null){
                $order->PaymentType = 0;
            }else{
                $order->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $order->CreditTerms = 0;
            }else{
                $order->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            $order->BaseAmount = 0;
            $order->Disc1Pct = 0;
            $order->Disc1Amount = 0;
            $order->Disc2Pct = 0;
            $order->Disc2Amount = 0;
            $order->TaxPct = 0;
            $order->TaxAmount = 0;
            $order->OtherCosts = '-';
            $order->OtherCostsAmount = 0;
            $order->PaidAmount = 0;
            if ($this->ValidateMaster($order)) {
                if ($order->PoNo == null || $order->PoNo == "-" || $order->PoNo == ""){
                    $order->PoNo = $order->GetPoDocNo();
                }
                $rs = $order->Insert();
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                }else{
                    redirect_url("ap.order/edit/".$order->Id);
                }
			}
		}
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
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
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
	}

	private function ValidateMaster(Order $order) {
        if ($order->SupplierId == 0 || $order->SupplierId == null || $order->SupplierId == ''){
            $this->Set("error", "Supplier tidak boleh kosong!");
            return false;
        }
        return true;
	}

    public function edit($orderId = null) {
        require_once(MODEL . "master/cabang.php");
        $loader = null;
        $order = new Order();
        if (count($this->postData) > 0) {
            $order->Id = $orderId;
            $order->CabangId = $this->GetPostValue("CabangId");
            $order->PoDate = $this->GetPostValue("PoDate");
            $order->RequestDate = $this->GetPostValue("RequestDate");
            if ($order->RequestDate == '' || $order->RequestDate == null){
                $order->RequestDate = $order->PoDate;
            }
            $order->PoNo = $this->GetPostValue("PoNo");
            $order->PoDescs = $this->GetPostValue("PoDescs");
            $order->SupplierId = $this->GetPostValue("SupplierId");
            $order->SalesName = $this->GetPostValue("SalesName");
            if ($this->GetPostValue("PoStatus") == null || $this->GetPostValue("PoStatus") == 0){
                $order->PoStatus = 1;
            }else{
                $order->PoStatus = $this->GetPostValue("PoStatus");
            }
            $order->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if($this->GetPostValue("PaymentType") == null){
                $order->PaymentType = 0;
            }else{
                $order->PaymentType = $this->GetPostValue("PaymentType");
            }
            if($this->GetPostValue("CreditTerms") == null){
                $order->CreditTerms = 0;
            }else{
                $order->CreditTerms = $this->GetPostValue("CreditTerms");
            }
            $order->Disc1Pct = $this->GetPostValue("Disc1Pct");
            $order->TaxPct = $this->GetPostValue("TaxPct");
            $order->OtherCosts = $this->GetPostValue("OtherCosts");
            $order->OtherCostsAmount = str_replace(",","",$this->GetPostValue("OtherCostsAmount"));
            if ($this->ValidateMaster($order)) {
                $rs = $order->Update($order->Id);
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->persistence->SaveState("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                }else{
                    $this->persistence->SaveState("info", sprintf("Data Order/Nota No.: '%s' Tanggal: %s telah berhasil diubah..", $order->PoNo, $order->PoDate));
                    redirect_url("ap.order/edit/".$order->Id);
                }
            }
        }else{
            $order = $order->LoadById($orderId);
            if($order == null){
               $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ap.order");
            }
            if($order->PoStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Order No. %s sudah berstatus -CLOSED-",$order->PoNo));
                redirect_url("ap.order");
            }
            // load details
            $order->LoadDetails();
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
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
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
    }

	public function view($orderId = null) {
        require_once(MODEL . "master/cabang.php");
        $loader = null;
        $order = new Order();
        $order = $order->LoadById($orderId);
        if($order == null){
            $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.order");
        }
        // load details
        $order->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
	}

    public function delete($orderId) {
        // Cek datanya
        $order = new Order();
        $order = $order->FindById($orderId);
        if($order == null){
            $this->Set("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.order");
        }
        // periksa status po
        if($order->PoStatus < 2){
            $order->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($order->Delete($orderId) == 1) {
                $this->persistence->SaveState("info", sprintf("Data Order No: %s sudah berhasil dihapus", $order->PoNo));
            }else{
                $this->persistence->SaveState("error", sprintf("Maaf, Data Order No: %s gagal dihapus", $order->PoNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Order No: %s sudah berstatus -CLOSED-", $order->PoNo));
        }
        redirect_url("ap.order");
    }

	public function add_detail($orderId = null) {
        $order = new Order($orderId);
        $orderdetail = new OrderDetail();
        $orderdetail->PoId = $orderId;
        $items = 1;
        if (count($this->postData) > 0) {
            $orderdetail->ItemId = $this->GetPostValue("aItemId");
            $orderdetail->OrderQty = $this->GetPostValue("aQty");
            $orderdetail->ItemDescs = '-';
            $orderdetail->ReceiptQty = 0;
            $orderdetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $orderdetail->DiscFormula = 0;
            }else{
                $orderdetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $orderdetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $orderdetail->SubTotal = $this->GetPostValue("aSubTotal");
            if ($items != null){
                $orderdetail->Lqty = 0;
                $orderdetail->Sqty = 0;
                // insert ke table
                $rs = $orderdetail->Insert()== 1;
                if ($rs > 0) {
                    echo json_encode(array());
                } else {
                    echo json_encode(array('errorMsg'=>'Some errors occured.'));
                }
            }else{
                echo json_encode(array('errorMsg'=>'Data barang tidak ditemukan!'));
            }
        }
	}

    public function edit_detail($orderId = null) {
        $order = new Order($orderId);
        $orderdetail = new OrderDetail();
        $orderdetail->PoId = $orderId;
        $items = 1;
        if (count($this->postData) > 0) {
            $orderdetail->Id = $this->GetPostValue("aId");
            $orderdetail->ItemId = $this->GetPostValue("aItemId");
            $orderdetail->OrderQty = $this->GetPostValue("aQty");
            $orderdetail->ReceiptQty = $this->GetPostValue("rQty");
            $orderdetail->Price = $this->GetPostValue("aPrice");
            if ($this->GetPostValue("aDiscFormula") == ''){
                $orderdetail->DiscFormula = 0;
            }else{
                $orderdetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            }
            $orderdetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $orderdetail->SubTotal = $this->GetPostValue("aSubTotal");
            if ($items != null){
                // insert ke table
                $rs = $orderdetail->Update($orderdetail->Id);
                if ($rs > 0) {
                    echo json_encode(array());
                } else {
                    echo json_encode(array('errorMsg'=>'Some errors occured.'));
                }
            }else{
                echo json_encode(array('errorMsg'=>'Data barang tidak ditemukan!'));
            }
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $orderdetail = new OrderDetail();
        $orderdetail = $orderdetail->FindById($id);
        if ($orderdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($orderdetail->Delete($id) == 1) {
            printf("Data Detail Order ID: %d berhasil dihapus!",$id);
        }else{
            printf("Maaf, Data Detail Order ID: %d gagal dihapus!",$id);
        }
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.order");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $order = new Order();
            $order = $order->FindById($id);
            /** @var $order Order */
            // process po
            if($order->PoStatus == 0){
                $rs = $order->Approve($order->Id,$uid);
                if ($rs) {
                    $infos[] = sprintf("Data Order No.: '%s' (%s) telah berhasil di-approve.", $order->PoNo, $order->PoDescs);
                } else {
                    $errors[] = sprintf("Maaf, Gagal proses approve Data Order: '%s'. Message: %s", $order->PoNo, $this->connector->GetErrorMessage());
                }
            }else{
                $errors[] = sprintf("Data Order No.%s sudah berstatus -Posted- !",$order->PoNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.order");
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
            $sSuppliersId = $this->GetPostValue("SuppliersId");
            $sSoStatus = $this->GetPostValue("SoStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $order = new Order();
            if ($sJnsLaporan == 1){
                $reports = $order->LoadPo4Reports($this->userCompanyId,$sCabangId,$sSuppliersId,$sSoStatus,$sStartDate,$sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $order->LoadPo4ReportsDetail($this->userCompanyId,$sCabangId,$sSuppliersId,$sSoStatus,$sStartDate,$sEndDate);
            }else{
                $reports = $order->LoadPo4ReportsRekapItem($this->userCompanyId,$sCabangId,$sSuppliersId,$sSoStatus,$sStartDate,$sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sSuppliersId = 0;
            $sSoStatus = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $sJnsLaporan = 1;
            $reports = null;
        }
        $supplier = new Supplier();
        $supplier = $supplier->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
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
        // kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("suppliers",$supplier);
        $this->Set("CabangId",$sCabangId);
        $this->Set("SuppliersId",$sSuppliersId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Status",$sSoStatus);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("JnsLaporan",$sJnsLaporan);
    }

    public function getitems_json($principalId = 0,$order = "a.item_code"){
        require_once(MODEL . "inventory/items.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $items = new Items();
        $itemlists = $items->GetJSonItems($this->userCompanyId,$this->userCabangId,$principalId,$filter,$order);
        echo json_encode($itemlists);
    }

    public function getjson_polists($cabangId,$supplierId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $order = new Order();
        $polists = $order->GetActivePoList($cabangId,$supplierId,$filter);
        echo json_encode($polists);
    }

    //proses cetak purchase order (PO)
    public function order_print($doctype = 'order') {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Harap pilih data yang akan dicetak !");
            redirect_url("ap.order");
            return;
        }
        $report = array();
        foreach ($ids as $id) {
            $order = new Order();
            $order = $order->LoadById($id);
            $order->LoadDetails();
            $report[] = $order;
        }

        $this->Set("doctype", $doctype);
        $this->Set("report", $report);
    }
}


// End of File: estimasi_controller.php
