<?php
class DeliveryController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userAccYear;
    private $userAccMonth;

	protected function Initialize() {
		require_once(MODEL . "ar/invoice.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userAccYear = $this->persistence->LoadState("acc_year");
        $this->userAccMonth = $this->persistence->LoadState("acc_month");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

    public function index(){
	    require_once (MODEL . "master/cabang.php");
        require_once (MODEL . "master/warehouse.php");
        //proses rekap dll
        if (count($this->postData) > 0) {
            $cabid = $this->GetPostValue("cabId");
            $whsid = $this->GetPostValue("whsId");
            $sdate = strtotime($this->GetPostValue("stDate"));
            $edate = strtotime($this->GetPostValue("enDate"));
            $dstts = $this->GetPostValue("dStatus");
        }else{
            $cabid = $this->userCabangId;
            $whsid = 0;
            $dstts = 0;
            $sdate = time();
            $edate = $sdate;
        }
        $loader = new Invoice();
        $invoice= $loader->LoadInvoiceDelivery($cabid,$whsid,$sdate,$edate,$dstts);
        $this->Set("cabId", $cabid);
        $this->Set("whsId", $whsid);
        $this->Set("dStatus", $dstts);
        $this->Set("stDate", $sdate);
        $this->Set("enDate", $edate);
        $this->Set("invoices", $invoice);
        //load cabang
        $loader = new Cabang();
        $cabangs = $loader->LoadById($this->userCabangId);
        $this->Set("cabangs", $cabangs);
        //load cabang
        $loader = new Warehouse();
        $warehouses = $loader->LoadByCabangId($this->userCabangId,1);
        $this->Set("warehouses", $warehouses);
    }

    public function process(){
        $invoice = new Invoice();
        $ids = $this->GetPostValue("ids", array());
        foreach ($ids as $detailId){
            $result  = $invoice->ProcessDelivery($detailId);
        }
        redirect_url("inventory.delivery");
    }
}

// End of file: awal_controller.php
