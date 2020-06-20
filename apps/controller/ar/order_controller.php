<?php
class OrderController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ar/order.php");
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
        $settings["columns"][] = array("name" => "a.order_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.sales_name", "display" => "Salesman", "width" => 80);
        $settings["columns"][] = array("name" => "a.cus_name", "display" => "Nama Outlet", "width" => 200);
        $settings["columns"][] = array("name" => "a.brand_name", "display" => "Brand", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode", "width" => 60);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 250);
        $settings["columns"][] = array("name" => "format(a.l_qty,0)", "display" => "L-QTY", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.order_qty - (a.l_qty * a.s_uom_qty),0)", "display" => "S-QTY", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.order_qty,0)", "display" => "QTY", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.send_qty,0)", "display" => "Kirim", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.order_qty - a.send_qty,0)", "display" => "Sisa", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "if (a.order_qty - a.send_qty = 0,'Close',if(a.order_status = 0,'New',if(a.order_status = 1,'Open','Close')))", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "a.order_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.sales_name", "display" => "Salesman");
        $settings["filters"][] = array("name" => "a.cus_name", "display" => "Nama Customer");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Sales Order (SO)";

            if ($acl->CheckUserAccess("ar.order", "add")) {
                $settings["actions"][] = array("Text" => "<b>Add</b>", "Url" => "ar.order/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.order", "edit")) {
                $settings["actions"][] = array("Text" => "<b>Edit</b>", "Url" => "ar.order/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Order terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }

            if ($acl->CheckUserAccess("ar.order", "view")) {
                $settings["actions"][] = array("Text" => "<b>View</b>", "Url" => "ar.order/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Order terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }

            if ($acl->CheckUserAccess("ar.order", "delete")) {
                $settings["actions"][] = array("Text" => "<b>Delete</b>", "Url" => "ar.order/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }

            if ($acl->CheckUserAccess("ar.invoice", "add")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "<b>Create Invoice</b>", "Url" => "ar.invoice/create", "Class" => "bt_create_new", "ReqId" => 0);
            }

            //$settings["actions"][] = array("Text" => "separator", "Url" => null);
            //if ($acl->CheckUserAccess("ar.order", "print")) {
            //    $settings["actions"][] = array("Text" => "Print SO", "Url" => "ar.order/order_print","Class" => "bt_pdf", "ReqId" => 2, "Confirm" => "Cetak P/O yang dipilih?");
            //}

            if ($acl->CheckUserAccess("ar.order", "view")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ar.order/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_ar_order_list AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.order_date) = ".$this->trxYear;
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
        require_once(MODEL . "master/salesman.php");
        $loader = null;
		$order = new Order();
        $order->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
			$order->OrderDate = $this->GetPostValue("OrderDate");
            $order->RequestDate = $this->GetPostValue("RequestDate");
            $order->CustomerId = $this->GetPostValue("CustomerId");
            $order->SalesId = $this->GetPostValue("SalesId");
            $order->PriorityId = $this->GetPostValue("PriorityId");
            $order->ItemId = $this->GetPostValue("ItemId");
            $order->OrderQty = $this->GetPostValue("aQty");
            if ($this->GetPostValue("OrderStatus") == null || $this->GetPostValue("OrderStatus") == 0){
                $order->OrderStatus = 1;
            }else{
                $order->OrderStatus = $this->GetPostValue("OrderStatus");
            }
            $order->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateMaster($order)) {
                $rs = $order->Insert();
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                }else{
                    redirect_url("ar.order");
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
        //load salesman
        $loader = new Salesman();
        $karyawan = $loader->LoadAll();
        $this->Set("sales", $karyawan);
	}

	private function ValidateMaster(Order $order) {
        if ($order->CustomerId == 0 || $order->CustomerId == null || $order->CustomerId == ''){
            $this->Set("error", "Customer tidak boleh kosong!");
            return false;
        }
        if ($order->SalesId == 0 || $order->SalesId == null || $order->SalesId == ''){
            $this->Set("error", "Salesman tidak boleh kosong!");
            return false;
        }
        if ($order->OrderQty == 0 || $order->OrderQty == null || $order->OrderQty == ''){
            $this->Set("error", "QTY Order tidak boleh kosong!");
            return false;
        }
        return true;
	}

    public function edit($orderId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/salesman.php");
        $loader = null;
        $order = new Order();
        if (count($this->postData) > 0) {
            $order->Id = $orderId;
            $order->CabangId = $this->userCabangId;
            $order->OrderDate = $this->GetPostValue("OrderDate");
            $order->RequestDate = $this->GetPostValue("RequestDate");
            $order->CustomerId = $this->GetPostValue("CustomerId");
            $order->SalesId = $this->GetPostValue("SalesId");
            $order->PriorityId = $this->GetPostValue("PriorityId");
            $order->ItemId = $this->GetPostValue("ItemId");
            $order->OrderQty = $this->GetPostValue("aQty");
            if ($this->GetPostValue("OrderStatus") == null || $this->GetPostValue("OrderStatus") == 0){
                $order->OrderStatus = 1;
            }else{
                $order->OrderStatus = $this->GetPostValue("OrderStatus");
            }
            if ($this->ValidateMaster($order)) {
                $rs = $order->Update($order->Id);
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->persistence->SaveState("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                }else{
                    $this->persistence->SaveState("info", sprintf("Data Sales Order Tanggal: %s telah berhasil diubah..", $order->OrderDate));
                    redirect_url("ar.order");
                }
            }
        }else{
            $order = $order->LoadById($orderId);
            if($order == null){
               $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ar.order");
            }
            if($order->OrderQty - $order->SendQty == 0){
                $this->persistence->SaveState("error", sprintf("Maaf Data Order Tgl. %s sudah berstatus -CLOSED-",$order->OrderDate));
                redirect_url("ar.order");
            }
            if($order->OrderStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf Data Order Tgl. %s sudah berstatus -CLOSED-",$order->OrderDate));
                redirect_url("ar.order");
            }
            if($order->OrderStatus == 3){
                $this->persistence->SaveState("error", sprintf("Maaf Data Order Tgl. %s sudah berstatus -VOID-",$order->OrderDate));
                redirect_url("ar.order");
            }
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
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
        //load salesman
        $loader = new Salesman();
        $karyawan = $loader->LoadAll();
        $this->Set("sales", $karyawan);
    }

	public function view($orderId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/salesman.php");
        $loader = null;
        $order = new Order();
        $order = $order->LoadById($orderId);
        if($order == null){
            $this->persistence->SaveState("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.order");
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("order", $order);
        //load salesman
        $loader = new Salesman();
        $karyawan = $loader->LoadAll();
        $this->Set("sales", $karyawan);
	}

    public function delete($orderId) {
        // Cek datanya
        $order = new Order();
        $order = $order->FindById($orderId);
        if($order == null){
            $this->Set("error", "Maaf Data Order dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.order");
        }
        // periksa status po
        if($order->OrderStatus < 2 && $order->SendQty == 0){
            $order->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($order->Delete($orderId) == 1) {
                $this->persistence->SaveState("info", sprintf("Data Order Tgl: %s sudah berhasil dihapus", date('Y-m-d',$order->OrderDate)));
            }else{
                $this->persistence->SaveState("error", sprintf("Maaf, Data Order Tgl: %s gagal dihapus", date('Y-m-d',$order->OrderDate)));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Order Tgl: %s sudah diproses!", date('Y-m-d',$order->OrderDate)));
        }
        redirect_url("ar.order");
    }

    public function getitems_json($principalId = 0,$order = "a.item_name"){
        require_once(MODEL . "inventory/items.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $items = new Items();
        $itemlists = $items->GetJSonItems($this->userCompanyId,$this->userCabangId,$principalId,$filter,$order);
        echo json_encode($itemlists);
    }

    public function report(){
        // report sales order
        require_once(MODEL . "ar/customer.php");
        require_once(MODEL . "master/salesman.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sCustomersId = $this->GetPostValue("CustomersId");
            $sSalesId = $this->GetPostValue("SalesId");
            $sOrderStatus = $this->GetPostValue("OrderStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $order = new Order();
            if ($sJnsLaporan == 1){
                $reports = $order->LoadOrder4ReportsDetail($this->userCompanyId,$sCabangId,$sCustomersId,$sSalesId,$sOrderStatus,$sStartDate,$sEndDate);
            }else{
                $reports = $order->LoadOrder4ReportsRekapItem($this->userCompanyId,$sCabangId,$sCustomersId,$sSalesId,$sOrderStatus,$sStartDate,$sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sCustomersId = 0;
            $sSalesId = 0;
            $sOrderStatus = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $sJnsLaporan = 1;
            $reports = null;
        }
        $customer = new Customer();
        $customer = $customer->LoadAll();
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
        $this->Set("customers",$customer);
        $this->Set("CabangId",$sCabangId);
        $this->Set("CustomersId",$sCustomersId);
        $this->Set("SalesId",$sSalesId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Status",$sOrderStatus);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("JnsLaporan",$sJnsLaporan);
        //load salesman
        $loader = new Salesman();
        $karyawan = $loader->LoadAll();
        $this->Set("salesman", $karyawan);
    }
}


// End of File: estimasi_controller.php
