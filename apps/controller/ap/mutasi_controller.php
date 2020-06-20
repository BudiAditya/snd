<?php
class MutasiController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ap/mutasi.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function Index(){
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
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sOutput = $this->GetPostValue("Output");
            $mutasi = new Mutasi();
            $reports = $mutasi->Load4Reports($sCabangId,$sSuppliersId,$sStartDate,$sEndDate);
        }else{
            $sCabangId = $this->userCabangId;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            $sEndDate = time();
            $sSuppliersId = 0;
            $sOutput = 0;
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
            $cab = new Cabang($this->userCabangId);
            $cabCode = $cab->Kode;
            $cabName = $cab->Cabang;
        }else{
            $cabang = $loader->LoadById($this->userCabangId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        // kirim ke view
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("cabangs", $cabang);
        $this->Set("suppliers",$supplier);
        $this->Set("CabangId",$sCabangId);
        $this->Set("SuppliersId",$sSuppliersId);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
    }
}


// End of File: invoice_controller.php
