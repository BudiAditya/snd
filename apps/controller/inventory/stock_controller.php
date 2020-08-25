<?php

/**
 * Dipake untuk membuat laporan stock pergudang atau overview
 * By Default ini hanya bisa melihat stock per Company dan tidak bisa semua Company. Jadi Corporate user harus impersonate
 *
 * NOTE:
 *  - Berhubung query yang dipake disini aneh-aneh maka saya prefer tidak pakai model tetapi langsung query.
 */
class StockController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $trxYear;
    private $trxMonth;
    private $userLevel;
    private $userUid;
    private $userCabIds;

	protected function Initialize() {
		require_once(MODEL . "inventory/stock.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
	}

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "concat(a.warehouse_id,'|',a.item_id)", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.wh_code", "display" => "Gudang", "width" => 50);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Produk", "width" =>250);
        $settings["columns"][] = array("name" => "a.uom_code", "display" => "Satuan", "width" =>50);
        $settings["columns"][] = array("name" => "format(a.qty_op,0)", "display" => "+Awal", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.qty_in,0)", "display" => "+Masuk", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.qty_ot,0)", "display" => "-Keluar", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.qty_stock,0)", "display" => "Stock", "width" => 50, "align" => "right");

        $settings["filters"][] = array("name" => "a.item_code", "display" => "Item Code");
        $settings["filters"][] = array("name" => "a.item_name", "display" => "Item Name");
        $settings["filters"][] = array("name" => "a.wh_code", "display" => "Gudang");

        if (!$router->IsAjaxRequest) {
            // UI Settings
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Stock Produk";
            if ($acl->CheckUserAccess("inventory.stock", "view")) {
                $settings["actions"][] = array("Text" => "Kartu Stock", "Url" => "inventory.stock/card/%s", "Class" => "bt_view", "ReqId" => 1,"Confirm" => "");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan Stock Per Periode", "Url" => "inventory.stock/stkdetail", "Class" => "bt_report", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan Stock Terakhir", "Url" => "inventory.stock/report", "Class" => "bt_report", "ReqId" => 0);
            }

            $settings["def_filter"] = 1;
            $settings["def_order"] = 2;
            $settings["singleSelect"] = true;
        } else {
            $settings["from"] = "vw_ic_stock_list AS a";
            $settings["where"] = "a.trx_year = $this->trxYear And a.cabang_id IN (" . $this->userCabIds . ")";
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }


    public function getStockQty($whId = 0,$itemId = 0){
        $sqty = 0;
        $stock = new Stock();
        $sqty = $stock->CheckStock($this->userAccYear,$whId,$itemId);
        print(number_format($sqty,0));
    }

    public function card($data = '0|0'){
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/items.php");
        // Intelligent time detection...
        //$month = (int)date("n");
        $month = 1;
        //$year = (int)date("Y");
        $year = $this->trxYear;
        $data = explode('|',$data);
        $whId = $data[0];
        $itemId = $data[1];
        if (count($this->postData) > 0) {
            $startDate =  strtotime($this->GetPostValue("startDate"));
            $endDate = strtotime($this->GetPostValue("endDate"));
            $outPut = $this->GetPostValue("outPut");
        }else{
            $startDate = mktime(0, 0, 0, $month, 1, $year);
            $endDate = time();
            $outPut = 0;
        }
        $stock = new Stock();
        $stock->WarehouseId = $whId;
        $stock->ItemId = $itemId;
        $stkcard = $stock->GetStockHistory($this->trxYear,$startDate,$endDate);
        $this->Set("startDate",$startDate);
        $this->Set("endDate",$endDate);
        $this->Set("outPut",$outPut);
        $this->Set("stock",$stock);
        $this->Set("stkcard",$stkcard);
        $company = new Company($this->userCompanyId);
        $this->Set("company_name", $company->CompanyName);
        $whs = new Warehouse($whId);
        $this->Set("whs",$whs);
        $its = new Items($itemId);
        $this->Set("its",$its);
    }

    public function stkdetail(){
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/itementity.php");
        // proses pembuatan mutasi stock
        // Intelligent time detection...
        //$month = (int)date("n");
        //$year = (int)date("Y");
        $month = $this->trxMonth;
        $year = $this->trxYear;
        $mstock = null;
        if (count($this->postData) > 0) {
            $cabangId =  $this->userCabangId;
            $entityId = $this->GetPostValue("entityId");
            $whId = $this->GetPostValue("whId");
            $startDate =  strtotime($this->GetPostValue("startDate"));
            $endDate = strtotime($this->GetPostValue("endDate"));
            $outPut = $this->GetPostValue("outPut");
        }else{
            $cabangId = $this->userCabangId;
            $entityId = 0;
            $whId = 0;
            $startDate = mktime(0, 0, 0, $month, 1, $year);
            $endDate = mktime(0, 0, 0, $month+1, 0, $year);//time();
            $outPut = 0;
        }
        //load data
        $mstock = new Stock();
        $mstock = $mstock->GetMutasiStock($this->trxYear,$whId,$startDate,$endDate,$entityId);
        //load data cabang
        $company = new Company($this->userCompanyId);
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        if ($this->userLevel > 3){
            $cabang = $loader->LoadByCompanyId($this->userCompanyId);
            $cab = new Cabang();
            $cab = $cab->LoadById($cabangId);
            $cabCode = $cab->Kode;
            $cabName = $cab->Cabang;
        }else{
            $cabang = $loader->LoadById($this->userCabangId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        //load data gudang
        $loader = new Warehouse();
        $gudangs = $loader->LoadByAllowedCabangId($this->userCabIds);
        //load data entity barang
        $loader = new ItemEntity();
        $entities = $loader->LoadByCompanyId($this->userCompanyId,"a.id");
        $this->Set("whId",$whId);
        $this->Set("etId",$entityId);
        $this->Set("cabangId",$cabangId);
        $this->Set("startDate",$startDate);
        $this->Set("endDate",$endDate);
        $this->Set("outPut",$outPut);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("cabangs",$cabang);
        $this->Set("gudangs",$gudangs);
        $this->Set("entities",$entities);
        $this->Set("mstock",$mstock);
        $this->Set("company_name", $company->CompanyName);
    }
    
    public function getitemstock_plain($whId,$itemCode){
        $ret = 'ER|0';
        if($itemCode != null || $itemCode != ''){
            /** @var $stock Stock */
            $stock = new Stock();
            $stock = $stock->FindByKodeGudang($this->userAccYear,$whId,$itemCode);
            if ($stock != null){
                $ret = "OK|".$stock->ItemId.'|'.$stock->ItemName.'|'.$stock->SuomCode.'|'.$stock->QtyStock;
            }
        }
        print $ret;
    }

    public function getitemstock_json($whId = 0,$order="a.item_code"){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $stock = new Stock();
        $itemlists = $stock->GetJSonItemStock($this->trxYear,$whId,$filter,$order);
        echo json_encode($itemlists);
    }

    public function stock_list($output){
        require_once(MODEL . "master/company.php");
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $compname = $company->CompanyName;
        $items = new Stock();
        $items = $items->Load4Excel($this->userAccYear,$this->userCabangId);
        $this->Set("items", $items);
        $this->Set("output", $output);
        $this->Set("company_name", $compname);
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/itementity.php");
        require_once(MODEL . "ap/supplier.php");
        $loader = null;
        $sCabangId = $this->userCabangId;
        $sReportType = 0;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sGudangId = $this->GetPostValue("GudangId");
            $sEntityId = $this->GetPostValue("EntityId");
            $sTypeHarga = $this->GetPostValue("TypeHarga");
            $sReportType = $this->GetPostValue("ReportType");
            $sOutput = $this->GetPostValue("Output");
            if ($sCabangId <> $this->userCabangId){
                $this->persistence->SaveState("error", "Maaf Anda tidak boleh mengakses Laporan Stock cabang ini!");
                redirect_url("inventory.stock");
            }
        }else{
            $sGudangId = 0;
            $sEntityId = 0;
            $sSupplierId = null;
            $sTypeHarga = 0;
            $sOutput = 0;
            $sReportType = 0;
        }
        // ambil data yang diperlukan
        $stock = new Stock();
        $reports = $stock->Load4Reports($sCabangId,$sGudangId,$sEntityId);
        //get data header
        $company = new Company($this->userCompanyId);
        $cabCode = null;
        $cabName = null;
        $scabCode = null;
        $gudang = new Warehouse();
        $gudangs = $gudang->LoadByCabangId($sCabangId);

        $jenis = new ItemEntity();
        $jenis = $jenis->LoadByCompanyId($this->userCompanyId);
        // kirim ke view
        $this->Set("company_name", $company->CompanyName);
        $this->Set("gudangs", $gudangs);
        $this->Set("jenis", $jenis);
        $this->Set("output",$sOutput);
        $this->Set("reports",$reports);
        $this->Set("userEntityId",$sEntityId);
        $this->Set("userTypeHarga",$sTypeHarga);
        $this->Set("userReportType",$sReportType);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("gudangId",$sGudangId);
    }


    public function checkStock($whId,$itemId){
        $stock = new Stock();
        $stock = $stock->CheckStock($this->userAccYear,$whId,$itemId);
        print $stock;
    }
}


// End of File: stockcas_controller.php
