<?php
class StatisticController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userAllowCabangIds;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ar/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userAllowCabangIds = $this->persistence->LoadState("user_allow_cabids");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function index(){
        if (count($this->postData) > 0) {
            $month = $this->GetPostValue("month");
            $year  = $this->GetPostValue("year");
            $type  = $this->GetPostValue("type");
        }else {
            $month = $this->trxMonth;
            $year = $this->trxYear;
            $type = 1;
        }
        //get invoice summary by month data
        $invoice = new Invoice();
        $dataInvoices = $invoice->GetInvoiceSumByYear($year);
        $this->Set("dataInvoices",$dataInvoices);
        $dataInvMonthly = $invoice->GetDataInvoiceSumByMonth($year);
        $this->Set("dataInvMonthly",$dataInvMonthly);
        $dataReceipts = $invoice->GetReceiptSumByYear($year);
        $this->Set("dataReceipts",$dataReceipts);
        $this->Set("dataTahun",$year);
        //get omset by entitas
        $dataOmset = $invoice->LoadEntityOmset($type,$year,$month);
        $this->Set("dataEntityOmset",$dataOmset);
        //get omset by principal
        $dataOmset = $invoice->LoadPrincipalOmset($type,$year,$month);
        $this->Set("dataPrincipalOmset",$dataOmset);
        //get omset by sales
        $dataOmset = $invoice->LoadSalesOmset($type,$year,$month);
        $this->Set("dataSalesOmset",$dataOmset);
        //get omset by area
        $dataOmset = $invoice->LoadAreaOmset($type,$year,$month);
        $this->Set("dataAreaOmset",$dataOmset);
        //get top 10 customer omset
        $dataTop10Customer = $invoice->LoadTop10Customer($type,$year,$month);
        $this->Set("dataCustomer",$dataTop10Customer);
        //get top 10 item omset
        $dataTop10Item = $invoice->LoadTop10Item($type,$year,$month);
        $this->Set("dataProduct",$dataTop10Item);
        $this->Set("type",$type);
        $this->Set("month",$month);
        $this->Set("year",$year);
        $monthName = strtoupper(date('F', mktime(0, 0, 0, $month, 1,$year)));
        $this->Set("monthName",$monthName);
        $speriod = null;
        if ($type == 2){
            $speriod = 'BULAN '.$monthName.' '.$year;
        }elseif ($type == 3){
            $speriod = 'S/D BULAN '.$monthName.' '.$year;
        }else{
            $speriod = 'TAHUN '.$year;
        }
        $this->Set("statPeriod",$speriod);
    }

    public function salesByEntityData($type,$year,$month){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanByEntity($type,$year,$month);
        echo json_encode($data);
    }

    public function salesByPrincipalData($type,$year,$month){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanByPrincipal($type,$year,$month);
        echo json_encode($data);
    }

    public function omsetSalesData($type,$year,$month){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanBySalesman($type,$year,$month);
        echo json_encode($data);
    }

    public function omsetByAreaData($type,$year,$month){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanByArea($type,$year,$month);
        echo json_encode($data);
    }

    public function top10CustomerData($type,$year,$month){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonTop10Customer($type,$year,$month);
        echo json_encode($data);
    }

    public function top10ItemData($type,$year,$month){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonTop10Item($type,$year,$month);
        echo json_encode($data);
    }
}


// End of File: ar.dashboard_controller.php
