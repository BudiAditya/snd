<?php
class DashboardController extends AppController {
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

    public function index() {
        //get invoice summary by month data
        $invoice = new Invoice();
        $dataInvoices = $invoice->GetInvoiceSumByYear($this->trxYear);
        $this->Set("dataInvoices",$dataInvoices);
        $dataReceipts = $invoice->GetReceiptSumByYear($this->trxYear);
        $this->Set("dataReceipts",$dataReceipts);
        $this->Set("dataTahun",$this->trxYear);
        //get omset sales
        $dataOmset = $invoice->LoadSalesOmsetByYear($this->userCompanyId,$this->trxYear);
        $this->Set("dataOmset",$dataOmset);
        //get top 10 customer omset
        $dataTop10Customer = $invoice->LoadTop10Customer($this->userCompanyId,$this->trxYear);
        $this->Set("dataCustomer",$dataTop10Customer);
        //get top 10 item omset
        $dataTop10Item = $invoice->LoadTop10Item($this->userCompanyId,$this->trxYear);
        $this->Set("dataProduct",$dataTop10Item);
    }

    public function salesdata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanByEntity($this->trxYear);
        echo json_encode($data);
    }

    public function principaldata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanByPrincipal($this->trxYear);
        echo json_encode($data);
    }

    public function omsetdata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanBySalesman($this->trxYear);
        echo json_encode($data);
    }

    public function citydata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonPenjualanByCity($this->trxYear);
        echo json_encode($data);
    }

    public function top10customerdata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonTop10Customer($this->trxYear);
        echo json_encode($data);
    }

    public function top10itemdata(){
        //get sales data by entity
        $data = new Invoice();
        $data = $data->GetJSonTop10Item($this->trxYear);
        echo json_encode($data);
    }
}


// End of File: ar.dashboard_controller.php
