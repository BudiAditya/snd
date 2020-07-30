<?php
class ReportingController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $trxYear;
    private $trxMonth;
    private $userLevel;
    private $userUid;
    private $userCabIds;
    private $cEmployee;
    private $cCustomer;
    private $cInvoice;
    private $cWarehouse;
    private $cStock;
    private $startDate;
    private $endDate;
    private $fileName;
    private $rContent;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
	}

    public function index() {
        // Intelligent time detection...
        $month = $this->trxMonth;
        $year = $this->trxYear;
        $mstock = null;
        $this->startDate = mktime(0, 0, 0, $month, 1, $year);
        $this->endDate = mktime(0, 0, 0, $month + 1, 0, $year);
        $this->cEmployee = 0;
        $this->cCustomer = 0;
        $this->cInvoice = 0;
        $this->cWarehouse = 0;
        $this->cStock = 0;
        $this->rContent = null;
        $this->Set("startDate",$this->startDate);
        $this->Set("endDate",$this->endDate);
        $this->Set("cbEmployee",$this->cEmployee);
        $this->Set("cbCustomer",$this->cCustomer);
        $this->Set("cbInvoice",$this->cInvoice);
        $this->Set("cbWarehouse",$this->cWarehouse);
        $this->Set("cbStock",$this->cStock);
        $this->Set("rpContent",$this->rContent);
    }

    public function create(){
        if (count($this->postData) > 0) {
            $this->startDate =  strtotime($this->GetPostValue("startDate"));
            $this->endDate = strtotime($this->GetPostValue("endDate"));
            $this->cEmployee = $this->GetPostValue("cbEmployee");
            $this->cCustomer = $this->GetPostValue("cbCustomer");
            $this->cInvoice = $this->GetPostValue("cbInvoice");
            $this->cWarehouse = $this->GetPostValue("cbWarehouse");
            $this->cStock = $this->GetPostValue("cbStock");
            if ($this->cEmployee + $this->cCustomer + $this->cInvoice + $this->cWarehouse + $this->cStock > 0) {
                //init filename
                require_once (MODEL . "master/company.php");
                require_once (MODEL . "master/salesarea.php");
                $company = new Company($this->userCompanyId);
                $namafile = $company->CasDistCode . '_' . date("Ymd") . '.txt';
                $this->fileName = $namafile;
                $content = null;
                $textcon = null;
                //list salesarea
                $sarea = new SalesArea();
                $sarea = $sarea->LoadByCompanyId($this->userCompanyId, "a.area_name");
                if ($sarea != null) {
                    /** @var $sarea SalesArea[] */
                    foreach ($sarea as $area) {
                        $textcon .= "100|" . $company->CasDistCode . "|" . $area->AreaCode . "|" . $area->AreaName . "|" . $company->CasDistArea . "|" . PHP_EOL;
                    }
                }
                //salesman
                if ($this->cEmployee == 1) {
                    require_once(MODEL . "master/salesman.php");
                    $salesman = new Salesman();
                    $salesman = $salesman->LoadAll();
                    if ($salesman != null) {
                        /** @var $salesman Salesman[] */
                        foreach ($salesman as $sales) {
                            $fname = $sales->FirstName == null ? $sales->SalesName : $sales->FirstName;
                            $lname = $sales->LastName == null ? 'CASTROL' : $sales->LastName;
                            $textcon .= "200|" . $company->CasDistCode . "|" . $fname . "|" . $lname . "|" . $company->CasDistArea . "|MGR|" . PHP_EOL;
                        }
                    }
                }
                //customer
                if ($this->cCustomer == 1) {
                    require_once(MODEL . "ar/customer.php");
                    require_once(MODEL . "ar/custype.php");
                    $customer = new Customer();
                    $customer = $customer->LoadAll();
                    if ($customer != null) {
                        /** @var $customer Customer[] */
                        foreach ($customer as $cus) {
                            $custype = new CusType($cus->CusTypeId);
                            $ctype = $custype == null ? 'GEN-957' : $custype->TypeCode;
                            $stdate = $cus->StartDate == null ? '01/01/2000' : $cus->FormatStartDate('d/m/Y');
                            $phone = $cus->Phone == null ? '1' : $cus->Phone;
                            $area = new SalesArea($cus->AreaId);
                            $sarea = $area == null ? 'MANADO' : $area->AreaName;
                            $textcon .= "300|" . $company->CasDistCode . "|" . $cus->CusCode . "|" . $cus->CusName . "|" . $ctype . "||" . $stdate . "||" . $cus->Address . "|" . $cus->City . "|12345|" . $sarea . "|" . $phone . "|VAN ROUTE|" . PHP_EOL;
                        }
                    }
                }
                //invoice
                if ($this->cInvoice == 1) {
                    require_once (MODEL . "tvd/invocas.php");
                    $invoice = new Invocas();
                    $invoice = $invoice->GetCasInvReport($this->startDate,$this->endDate);
                    if ($invoice != null){
                        while ($rs = $invoice->FetchAssoc()){
                            if ($rs["sub_total"] == 0) {
                                $ivamount = '14Q4SCHM01';
                            }else{
                                $ivamount = '';
                            }
                            if ($rs["price"] > 0 && $rs["s_uom_qty"] > 0) {
                                $price = round($rs["price"]/$rs["s_uom_qty"],0);
                            }else{
                                $price = '0';
                            }
                            if ($rs["cas_code"] == null || $rs["cas_code"] == '-'){
                                $itemcode = $rs["item_code"];
                            }else{
                                $itemcode = $rs["cas_code"];
                            }
                            $textcon .= "510|".$company->CasDistCode."|".$rs["customer_code"]."|".$itemcode."|LUB|".$rs["sales_id"]."|1|".$rs["invoice_no"]."|".date("d/m/Y",strtotime($rs["invoice_date"]))."|".$rs["gudang_id"]."|";
                            $textcon .= $ivamount."|".(int)$rs["sales_qty"]."|P|".$rs["sub_total"]."|".$price."|I||||".PHP_EOL;
                        }
                    }
                }
                //warehouse
                if ($this->cWarehouse == 1){
                    require_once (MODEL . "master/warehouse.php");
                    $whs = new Warehouse();
                    $whs = $whs->LoadByCompanyId($this->userCompanyId,1,"a.id");
                    if ($whs != null){
                        /** @var $whs Warehouse[] */
                        foreach ($whs as $gudang){
                            $textcon .= "700|".$company->CasDistCode."|".$gudang->Id."|".$gudang->WhName."|".$company->CasDistArea . "|" . PHP_EOL;
                        }
                    }
                }
                //stock
                if ($this->cStock == 1){
                    require_once (MODEL . "tvd/stockcas.php");
                    $stock = new StockCas();
                    $stock = $stock->Load4Reports($this->trxYear);
                    if ($stock != null){

                    }
                }
                //save file
                $content .= $textcon;
                $this->rContent = trim($content);
                $file = fopen($namafile, "w") or die("Unable to open file!");
                fwrite($file, $content);
                fclose($file);
                print('OK');
            }else{
                print('ER');
            }
        }else{
            redirect("tvd.reporting");
        }
    }

    public function getreport()
    {
        require_once (MODEL . "master/company.php");
        $company = new Company($this->userCompanyId);
        $namafile = $company->CasDistCode . '_' . date("Ymd") . '.txt';
        $myfile = fopen($namafile, "r") or die("Unable to open file!");
        $this->rContent = fread($myfile,filesize($namafile));
        fclose($myfile);
        print($this->rContent);
    }

    public function export()
    {
        require_once (MODEL . "master/company.php");
        $company = new Company($this->userCompanyId);
        $namafile = $company->CasDistCode . '_' . date("Ymd") . '.txt';
        $myfile = fopen($namafile, "r") or die("Unable to open file!");
        $this->rContent = fread($myfile,filesize($namafile));
        fclose($myfile);
        //header download
        header("Content-Disposition: attachment; filename=\"" . $namafile . "\"");
        header("Content-Type: application/force-download");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Type: text/plain");
        print($this->rContent);
    }
}


// End of File: stockcas_controller.php
