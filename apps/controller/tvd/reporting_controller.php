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
        $this->endDate = mktime(0, 0, 0, $month+1, 0, $year);
        $this->cEmployee = 0;
        $this->cCustomer = 0;
        $this->cInvoice = 0;
        $this->cWarehouse = 0;
        $this->cStock = 0;
        $this->Set("startDate",$this->startDate);
        $this->Set("endDate",$this->endDate);
        $this->Set("cbEmployee",$this->cEmployee);
        $this->Set("cbCustomer",$this->cCustomer);
        $this->Set("cbInvoice",$this->cInvoice);
        $this->Set("cbWarehouse",$this->cWarehouse);
        $this->Set("cbStock",$this->cStock);
    }

    public function create(){
        if (count($this->postData) > 0) {
            require_once (MODEL . "master/company.php");
            require_once (MODEL . "master/salesarea.php");
            $this->startDate =  strtotime($this->GetPostValue("startDate"));
            $this->endDate = strtotime($this->GetPostValue("endDate"));
            if ($this->GetPostValue("cbEmployee") != null) {
                $this->cEmployee = 1;
            } else {
                $this->cEmployee = 0;
            }
            if ($this->GetPostValue("cbCustomer") != null) {
                $this->cCustomer = 1;
            } else {
                $this->cCustomer = 0;
            }
            if ($this->GetPostValue("cbInvoice") != null) {
                $this->cInvoice = 1;
            } else {
                $this->cInvoice = 0;
            }
            if ($this->GetPostValue("cbWarehouse") != null) {
                $this->cWarehouse = 1;
            } else {
                $this->cWarehouse = 0;
            }
            if ($this->GetPostValue("cbStock") != null) {
                $this->cStock = 1;
            } else {
                $this->cStock = 0;
            }
            $company = new Company($this->userCompanyId);
            $namafile = $company->CasDistCode.'_'.date("Ymd").'.txt';
            $content = null;
            $textcon = null;
            //list salesarea
            $sarea = new SalesArea();
            $sarea = $sarea->LoadByCompanyId($this->userCompanyId,"a.area_name");
            if ($sarea != null){
                /** @var $sarea SalesArea[] */
                foreach ($sarea as $area){
                    $textcon.= "100|".$company->CasDistCode."|".$area->AreaCode."|".$area->AreaName."|".$company->CasDistArea."|".PHP_EOL;
                }
            }
            //salesman
            if ($this->cEmployee == 1){
                require_once (MODEL . "master/salesman.php");
                $salesman = new Salesman();
                $salesman = $salesman->LoadAll();
                if ($salesman != null){
                    /** @var $salesman Salesman[] */
                    foreach ($salesman as $sales){
                        $textcon.= "200|".$company->CasDistCode."|".$sales->FirstName."|".$sales->LastName."|".$company->CasDistArea."|MGR|".PHP_EOL;
                    }
                }
            }
            //customer
            if ($this->cCustomer == 1){
                require_once (MODEL . "ar/customer.php");
                require_once (MODEL . "ar/custype.php");
                $customer = new Customer();
                $customer = $customer->LoadAll();
                if ($customer != null){
                    /** @var $customer Customer[] */
                    foreach ($customer as $cus){
                        $custype = new CusType($cus->CusTypeId);
                        if ($custype == null){
                            $ctype = 'GEN-957';
                        }else{
                            $ctype = $custype->TypeCode;
                        }
                        if ($cus->StartDate == null){
                            $stdate = '01/01/2000';
                        }else{
                            $stdate = $cus->FormatStartDate('d/m/Y');
                        }
                        if ($cus->Phone == null){
                            $phone = '1';
                        }else{
                            $phone = $cus->Phone;
                        }
                        $area = new SalesArea($cus->AreaId);
                        if ($area == null){
                            $sarea = "MANADO";
                        }else{
                            $sarea = $area->AreaName;
                        }
                        $textcon.= "300|".$company->CasDistCode."|".$cus->CusCode."|".$cus->CusName."|".$ctype."||".$stdate."||".$cus->Address."|".$cus->City."|00000|".$sarea."|".$phone."|VAN ROUTE|".PHP_EOL;
                    }
                }
            }

            //save file
            $content.= $textcon;
            $file = fopen($namafile, "w") or die("Unable to open file!");
            fwrite($file, $content);
            fclose($file);
            //header download
            header("Content-Disposition: attachment; filename=\"" . $namafile . "\"");
            header("Content-Type: application/force-download");
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header("Content-Type: text/plain");
            echo $content;
        }else{
            redirect("tvd.reporting");
        }

    }
}


// End of File: stockcas_controller.php
