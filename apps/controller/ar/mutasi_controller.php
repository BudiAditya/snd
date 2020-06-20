<?php
class MutasiController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userCabIds;

    protected function Initialize() {
        require_once(MODEL . "ar/mutasi.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
    }

    public function Index(){
        require_once(MODEL . "ar/customer.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sCustomersId = $this->GetPostValue("CustomersId");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sOutput = $this->GetPostValue("Output");
            $mutasi = new Mutasi();
            $reports = $mutasi->Load4Reports($sCabangId,$sCustomersId,$sStartDate,$sEndDate,$this->userCabIds);
        }else{
            $sCabangId = $this->userCabangId;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            $sEndDate = time();
            $sCustomersId = 0;
            $sOutput = 0;
            $reports = null;
        }
        $customer = new Customer();
        $customer = $customer->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadAllowedCabId($this->userCabIds);
        $cabCode = null;
        $cabName = null;
        // kirim ke view
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("cabangs", $cabang);
        $this->Set("customers",$customer);
        $this->Set("CabangId",$sCabangId);
        $this->Set("CustomersId",$sCustomersId);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
    }
}


// End of File: invoice_controller.php
