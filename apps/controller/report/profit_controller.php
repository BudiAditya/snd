<?php
class ProfitController extends AppController
{
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize()
    {
        require_once(MODEL . "ar/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function index()
    {
        // report rekonsil process
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->userCabangId;
            $sGudangId = $this->GetPostValue("GudangId");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $invoice = new Invoice();
            if ($sJnsLaporan == 1) {
                $reports = $invoice->Load4ProfitTransaksi($this->userCompanyId, $sCabangId, $sGudangId, $sStartDate, $sEndDate);
            } elseif ($sJnsLaporan == 2) {
                $reports = $invoice->Load4ProfitTanggal($this->userCompanyId, $sCabangId, $sGudangId, $sStartDate, $sEndDate);
            } elseif ($sJnsLaporan == 3) {
                $reports = $invoice->Load4ProfitBulan($this->userCompanyId, $sCabangId, $sGudangId, $sStartDate, $sEndDate);
            } else {
                $reports = $invoice->Load4ProfitItem($this->userCompanyId, $sCabangId, $sGudangId, $sStartDate, $sEndDate);
            }
        } else {
            $sCabangId = 0;
            $sGudangId = 0;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sJnsLaporan = 1;
            $sOutput = 0;
            $reports = null;
        }
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        if ($this->userLevel > 3) {
            $cabang = $loader->LoadByEntityId($this->userCompanyId);
        } else {
            $cabang = $loader->LoadById($this->userCabangId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        $loader = new Warehouse();
        $gudangs = $loader->LoadByCabangId($this->userCabangId);
        // kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("CabangId", $sCabangId);
        $this->Set("gudangs", $gudangs);
        $this->Set("GudangId", $sGudangId);
        $this->Set("StartDate", $sStartDate);
        $this->Set("EndDate", $sEndDate);
        $this->Set("Output", $sOutput);
        $this->Set("JnsLaporan", $sJnsLaporan);
        $this->Set("Reports", $reports);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("userLevel", $this->userLevel);
    }
}
// End of File: profit_controller.php
