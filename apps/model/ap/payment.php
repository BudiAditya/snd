<?php

require_once("payment_detail.php");

class Payment extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $PaymentStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
		2 => "VOID"
	);

	public $Id;
    public $IsDeleted = false;
	public $CabangId;
    public $CabangCode;
	public $PaymentNo;
	public $PaymentDate;
    public $CreditorId;
    public $PaymentDescs;
    public $PaymentAmount = 0;
    public $AllocateAmount = 0;
    public $BalanceAmount = 0;
    public $PaymentMode = 0;
	public $KasbankId = 0;
    public $PaymentStatus = 0;
    public $NoVoucher;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;
    public $PaymentTypeId = 0;
    public $WarkatNo;
    public $WarkatDate;
    public $WarkatBankId = 0;
    public $WarkatDescs;
    public $ReturnNo;
    public $ErrorMsg;

	/** @var PaymentDetail[] */
	public $Details = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
        $this->IsDeleted = $row["is_deleted"] == 1;
		$this->Id = $row["id"];
		$this->CabangId = $row["cabang_id"];
        $this->CabangCode = $row["cabang_code"];
		$this->PaymentNo = $row["payment_no"];
		$this->PaymentDate = strtotime($row["payment_date"]);
		$this->KasbankId = $row["kasbank_id"];
		$this->CreditorId = $row["creditor_id"];
		$this->PaymentDescs = $row["payment_descs"];
		$this->PaymentMode = $row["payment_mode"];
		$this->PaymentAmount = $row["payment_amount"];
		$this->AllocateAmount = $row["allocate_amount"];
        $this->BalanceAmount = $row["payment_amount"] - $row["allocate_amount"];
        $this->PaymentStatus = $row["payment_status"];
		$this->CreatebyId = $row["createby_id"];
		$this->CreateTime = strtotime($row["create_time"]);
		$this->UpdatebyId = $row["updateby_id"];
		$this->UpdateTime = strtotime($row["update_time"]);
        $this->NoVoucher = $row["no_voucher"];
        $this->PaymentTypeId = $row["payment_mode"];
        $this->WarkatNo = $row["warkat_no"];
        $this->WarkatDate = strtotime($row["warkat_date"]);
        $this->WarkatBankId = $row["warkat_bank_id"];
        $this->WarkatDescs = $row["warkat_descs"];
        $this->ReturnNo = $row["return_no"];
	}

	public function FormatPaymentDate($format = HUMAN_DATE) {
		return is_int($this->PaymentDate) ? date($format, $this->PaymentDate) : date($format, strtotime(date('Y-m-d')));
	}

    public function FormatWarkatDate($format = HUMAN_DATE) {
        return is_int($this->WarkatDate) ? date($format, $this->WarkatDate) : null;
    }

	/**
	 * @return PaymentDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new PaymentDetail();
		$this->Details = $detail->LoadByPaymentId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Payment
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ap_payment_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_payment_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByPaymentNo($cabangId,$paymentNo) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ap_payment_master AS a WHERE a.cabang_id = ?cabangId And a.payment_no = ?paymentNo";
		$this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?paymentNo", $paymentNo);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByEntityId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_payment_master AS a WHERE a.company_id = ?companyId";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Payment();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function Load4Reports($companyId,$cabangId = 0,$bankId = 0, $creditorId = 0, $paymentMode = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ap_payment_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.payment_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($paymentStatus > -1){
            $sql.= " and a.payment_status = ".$paymentStatus;
        }else{
            $sql.= " and a.payment_status <> 3";
        }
        if ($creditorId > 0){
            $sql.= " and a.creditor_id = ".$creditorId;
        }
        if ($bankId > 0){
            $sql.= " and a.kasbank_id = ".$bankId;
        }
        if ($paymentMode > 0){
            $sql.= " and a.payment_mode = ".$paymentMode;
        }
        $sql.= " Order By a.payment_date, a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4DetailReports($companyId,$cabangId = 0,$bankId = 0, $creditorId = 0, $paymentMode = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,coalesce(c.grn_no,'Opening') as grn_no,c.grn_date,b.allocate_amount FROM vw_ap_payment_master AS a Join t_ap_payment_detail b On b.payment_id = a.id Left Join t_ap_purchase_master c On b.grn_id = c.id";
        $sql.= " WHERE a.is_deleted = 0 and a.payment_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($paymentStatus > -1){
            $sql.= " and a.payment_status = ".$paymentStatus;
        }else{
            $sql.= " and a.payment_status <> 3";
        }
        if ($creditorId > 0){
            $sql.= " and a.creditor_id = ".$creditorId;
        }
        if ($bankId > 0){
            $sql.= " and a.kasbank_id = ".$bankId;
        }
        if ($paymentMode > 0){
            $sql.= " and a.payment_mode = ".$paymentMode;
        }
        $sql.= " Order By a.payment_date, a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadByCabangId($cabangId = 0) {
        if($cabangId > 0){
            $this->connector->CommandText = "SELECT a.* FROM vw_ap_payment_master AS a WHERE a.cabang_id = ?cabangId";
        }else{
            $this->connector->CommandText = "SELECT a.* FROM vw_ap_payment_master AS a";
        }
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Payment();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ap_payment_master(payment_type_id, warkat_no, warkat_date, warkat_bank_id, warkat_descs, return_no,cabang_id, payment_no, payment_date, kasbank_id, creditor_id, payment_descs, payment_mode, payment_amount, allocate_amount, payment_status, createby_id, create_time, no_voucher)
VALUES(?payment_type_id, ?warkat_no, ?warkat_date, ?warkat_bank_id, ?warkat_descs, ?return_no, ?cabang_id, ?payment_no, ?payment_date, ?kasbank_id, ?creditor_id, ?payment_descs, ?payment_mode, ?payment_amount, ?allocate_amount, ?payment_status, ?createby_id, NOW(), ?no_voucher)";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?payment_no", $this->PaymentNo);
		$this->connector->AddParameter("?payment_date", $this->PaymentDate);
		$this->connector->AddParameter("?kasbank_id", $this->KasbankId);
		$this->connector->AddParameter("?creditor_id", $this->CreditorId);
		$this->connector->AddParameter("?payment_descs", $this->PaymentDescs);
        $this->connector->AddParameter("?payment_mode", $this->PaymentMode);
        $this->connector->AddParameter("?payment_amount", $this->PaymentAmount);
        $this->connector->AddParameter("?allocate_amount", $this->AllocateAmount == null ? 0 : $this->AllocateAmount);
        $this->connector->AddParameter("?payment_status", $this->PaymentStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $this->connector->AddParameter("?no_voucher", $this->NoVoucher);
        $this->connector->AddParameter("?payment_type_id", $this->PaymentTypeId);
        $this->connector->AddParameter("?warkat_no", $this->WarkatNo);
        $this->connector->AddParameter("?warkat_date", is_int($this->WarkatDate) ? date('Y-m-d', $this->WarkatDate) : null);
        $this->connector->AddParameter("?warkat_bank_id", $this->WarkatBankId);
        $this->connector->AddParameter("?warkat_descs", $this->WarkatDescs);
        $this->connector->AddParameter("?return_no", $this->ReturnNo);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ap_payment_master SET
	cabang_id = ?cabang_id
	, payment_no = ?payment_no
	, payment_date = ?payment_date
	, kasbank_id = ?kasbank_id
	, creditor_id = ?creditor_id
	, payment_descs = ?payment_descs
	, payment_mode = ?payment_mode
	, payment_amount = ?payment_amount
	, allocate_amount = ?allocate_amount
	, payment_status = ?payment_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
	, no_voucher = ?no_voucher
	, payment_type_id = ?payment_type_id
	, warkat_no = ?warkat_no
	, warkat_date = ?warkat_date
	, warkat_bank_id = ?warkat_bank_id
	, warkat_descs = ?warkat_descs
	, return_no = ?return_no
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?payment_no", $this->PaymentNo);
        $this->connector->AddParameter("?payment_date", $this->PaymentDate);
        $this->connector->AddParameter("?kasbank_id", $this->KasbankId);
        $this->connector->AddParameter("?creditor_id", $this->CreditorId);
        $this->connector->AddParameter("?payment_descs", $this->PaymentDescs);
        $this->connector->AddParameter("?payment_mode", $this->PaymentMode);
        $this->connector->AddParameter("?payment_amount", $this->PaymentAmount);
        $this->connector->AddParameter("?allocate_amount", $this->AllocateAmount);
        $this->connector->AddParameter("?payment_status", $this->PaymentStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?no_voucher", $this->NoVoucher);
        $this->connector->AddParameter("?payment_type_id", $this->PaymentTypeId);
        $this->connector->AddParameter("?warkat_no", $this->WarkatNo);
        $this->connector->AddParameter("?warkat_date", is_int($this->WarkatDate) ? date('Y-m-d', $this->WarkatDate) : null);
        $this->connector->AddParameter("?warkat_bank_id", $this->WarkatBankId);
        $this->connector->AddParameter("?warkat_descs", $this->WarkatDescs);
        $this->connector->AddParameter("?return_no", $this->ReturnNo);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
        //unpost dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_paymentmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText = "Delete From t_ap_payment_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs =  $this->connector->ExecuteNonQuery();
        if ($rs){
            $this->UpdatePurchaseMasterPaidAmount($id);
        }
        return $rs;
	}

    public function Void($id) {
        //unpost dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_paymentmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText = "Update t_ap_payment_master a Set a.payment_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs =  $this->connector->ExecuteNonQuery();
        if ($rs){
            $this->UpdatePurchaseMasterPaidAmount($id);
        }
        return $rs;
    }

    public function GetPaymentDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'PY';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->PaymentDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public  function GetPaymentDetailRow($id = 0){
        $sql = 'Select count(*) as valout From t_ap_payment_detail Where payment_id = ?payment_id';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?payment_id", $id);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function UpdatePurchaseMasterPaidAmount($paymentId){
        $sql = 'UPDATE t_ap_payment_master a JOIN ( SELECT c.payment_id,c.grn_id,COALESCE (sum(c.allocate_amount)) AS sumAlloc	FROM t_ap_payment_detail c';
	    $sql.= ' Join t_ap_payment_master d on c.payment_id = d.id Where d.is_deleted = 0 And d.payment_status <> 3 GROUP BY c.payment_id,c.grn_id) b On a.id = b.grn_id';
        $sql.= ' Set a.payment_amount = a.payment_amount - b.sumAlloc Where b.payment_id = ?paymentId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?paymentId", $paymentId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetJSonUnpaidGrns($cabangId = 0,$supplierId = 0 ,$sort = 'a.grn_no',$order = 'ASC') {
        $sql = "SELECT a.id,a.grn_no,a.grn_date,a.due_date,a.balance_amount,a.sup_inv_no FROM vw_ap_purchase_master AS a";
        $sql.= " Where a.grn_status > 0 and a.is_deleted = 0 and a.balance_amount > 0";
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ?cabangId";
        }
        if ($supplierId > 0){
            $sql.= " And a.supplier_id = ?supplierId";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?supplierId", $supplierId);
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function Approve($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ap_payment_approve($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unapprove($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ap_payment_unapprove($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function UpdateCaraBayar($id,$wti,$wbi){
        $this->connector->CommandText = "Update t_ap_payment_master a Set a.payment_type_id = $wti,a.warkat_bank_id = $wbi WHERE a.id = $id";
        return $this->connector->ExecuteNonQuery();
    }

    public function LoadPayment4Approval ($cabId = 0,$stDate, $enDate, $pStatus = 0){
        $sql = "SELECT a.* FROM vw_ap_payment_master a WHERE a.is_deleted = 0 AND (a.payment_date BETWEEN ?stDate And ?enDate)";
        if ($cabId > 0){
            $sql.= " And a.cabang_id = ".$cabId;
        }
        if ($pStatus > -1){
            $sql.= " And a.payment_status = ".$pStatus;
        }
        $sql.= " ORDER BY a.payment_date,a.payment_no";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?stDate", date('Y-m-d', $stDate));
        $this->connector->AddParameter("?enDate", date('Y-m-d', $enDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
