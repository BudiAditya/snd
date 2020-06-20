<?php
class DashboardController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

    protected function Initialize() {
        require_once(MODEL . "ap/purchase.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
    }

    public function index() {
        //get purchase summary by month data
        $purchase = new Purchase();
        $dataPurchases = $purchase->GetPurchaseSumByYear($this->trxYear);
        $this->Set("dataPurchases",$dataPurchases);
        $dataPayments = $purchase->GetPaymentSumByYear($this->trxYear);
        $this->Set("dataPayments",$dataPayments);
        $this->Set("dataTahun",$this->trxYear);
    }
}


// End of File: estimasi_controller.php
