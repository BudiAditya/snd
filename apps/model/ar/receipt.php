<?php

require_once("receipt_detail.php");

class Receipt extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $ReceiptStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
        2 => "APPROVED",
		3 => "VOID"
	);

	public $Id;
    public $IsDeleted = false;
	public $CabangId;
    public $CabangCode;
	public $ReceiptNo;
	public $ReceiptDate;
    public $DebtorId;
    public $KasbankId = 0;
    public $ReceiptDescs;
    public $ReceiptAmount;
    public $AllocateAmount;
    public $BalanceAmount;
	public $ReceiptStatus;
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

	/** @var ReceiptDetail[] */
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
		$this->ReceiptNo = $row["receipt_no"];
		$this->ReceiptDate = strtotime($row["receipt_date"]);
		$this->DebtorId = $row["debtor_id"];
		$this->KasbankId = $row["kasbank_id"];
		$this->ReceiptDescs = $row["receipt_descs"];
		$this->PaymentTypeId = $row["receipt_mode"];
		$this->ReceiptAmount = $row["receipt_amount"];
		$this->AllocateAmount = $row["allocate_amount"];
        $this->BalanceAmount = $row["receipt_amount"] - $row["allocate_amount"];
        $this->ReceiptStatus = $row["receipt_status"];
		$this->CreatebyId = $row["createby_id"];
		$this->CreateTime = strtotime($row["create_time"]);
		$this->UpdatebyId = $row["updateby_id"];
		$this->UpdateTime = strtotime($row["update_time"]);
        $this->NoVoucher = $row["no_voucher"];
        $this->WarkatNo = $row["warkat_no"];
        $this->WarkatDate = strtotime($row["warkat_date"]);
        $this->WarkatBankId = $row["warkat_bank_id"];
        $this->WarkatDescs = $row["warkat_descs"];
        $this->ReturnNo = $row["return_no"];
	}

	public function FormatReceiptDate($format = HUMAN_DATE) {
		return is_int($this->ReceiptDate) ? date($format, $this->ReceiptDate) : date($format, strtotime(date('Y-m-d')));
	}

    public function FormatWarkatDate($format = HUMAN_DATE) {
        return is_int($this->WarkatDate) ? date($format, $this->WarkatDate) : null;
    }

	/**
	 * @return ReceiptDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new ReceiptDetail();
		$this->Details = $detail->LoadByReceiptId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Receipt
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ar_receipt_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_receipt_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByReceiptNo($cabangId,$receiptNo) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ar_receipt_master AS a WHERE a.cabang_id = ?cabangId And a.receipt_no = ?receiptNo";
		$this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?receiptNo", $receiptNo);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByEntityId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_receipt_master AS a WHERE b.company_id = ?companyId";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Receipt();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function Load4RekapReports($companyId,$cabangId = 0,$bankId = 0, $debtorId = 0, $receiptMode = -1, $receiptStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ar_receipt_master AS a WHERE a.is_deleted = 0 and a.receipt_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($receiptStatus > -1){
            $sql.= " and a.receipt_status = ".$receiptStatus;
        }else{
            $sql.= " and a.receipt_status <> 3";
        }
        if ($debtorId > 0){
            $sql.= " and a.debtor_id = ".$debtorId;
        }
        if ($bankId > 0){
            $sql.= " and a.kasbank_id = ".$bankId;
        }
        if ($receiptMode > 0){
            $sql.= " and a.receipt_mode = ".$receiptMode;
        }
        $sql.= " Order By a.receipt_date, a.receipt_no, a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4DetailReports($companyId,$cabangId = 0,$bankId = 0, $debtorId = 0, $receiptMode = -1, $receiptStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,coalesce(c.invoice_no,'Opening') as invoice_no,c.invoice_date,b.allocate_amount FROM vw_ar_receipt_master AS a Join t_ar_receipt_detail b On b.receipt_id = a.id Left Join t_ar_invoice_master c ON b.invoice_id = c.id";
        $sql.= " WHERE a.is_deleted = 0 and a.receipt_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($receiptStatus > -1){
            $sql.= " and a.receipt_status = ".$receiptStatus;
        }else{
            $sql.= " and a.receipt_status <> 3";
        }
        if ($debtorId > 0){
            $sql.= " and a.debtor_id = ".$debtorId;
        }
        if ($bankId > 0){
            $sql.= " and a.kasbank_id = ".$bankId;
        }
        if ($receiptMode > 0){
            $sql.= " and a.receipt_mode = ".$receiptMode;
        }
        $sql.= " Order By a.receipt_date, a.receipt_no, a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadByCabangId($cabangId = 0) {
        if($cabangId > 0){
            $this->connector->CommandText = "SELECT a.* FROM vw_ar_receipt_master AS a WHERE a.cabang_id = ?cabangId";
        }else{
            $this->connector->CommandText = "SELECT a.* FROM vw_ar_receipt_master AS a";
        }
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Receipt();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ar_receipt_master(kasbank_id,cabang_id, receipt_no, receipt_date, debtor_id, receipt_descs, payment_type_id, receipt_amount, allocate_amount, receipt_status, createby_id, create_time, no_voucher, warkat_no, warkat_date, warkat_bank_id, warkat_descs, return_no)
VALUES(?kasbank_id,?cabang_id, ?receipt_no, ?receipt_date, ?debtor_id, ?receipt_descs, ?payment_type_id, ?receipt_amount, ?allocate_amount, ?receipt_status, ?createby_id, NOW(), ?no_voucher, ?warkat_no, ?warkat_date, ?warkat_bank_id, ?warkat_descs, ?return_no)";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?receipt_no", $this->ReceiptNo);
        $this->connector->AddParameter("?receipt_date", $this->ReceiptDate);
		$this->connector->AddParameter("?debtor_id", $this->DebtorId);
        $this->connector->AddParameter("?receipt_descs", $this->ReceiptDescs);
        $this->connector->AddParameter("?receipt_amount", $this->ReceiptAmount);
        $this->connector->AddParameter("?allocate_amount", $this->AllocateAmount == null ? 0 : $this->AllocateAmount);
        $this->connector->AddParameter("?receipt_status", $this->ReceiptStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $this->connector->AddParameter("?no_voucher", $this->NoVoucher);
        $this->connector->AddParameter("?payment_type_id", $this->PaymentTypeId);
        $this->connector->AddParameter("?kasbank_id", $this->KasbankId);
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
"UPDATE t_ar_receipt_master SET
	cabang_id = ?cabang_id
	, receipt_no = ?receipt_no
	, receipt_date = ?receipt_date
	, warkat_bank_id = ?warkat_bank_id
	, debtor_id = ?debtor_id
	, kasbank_id = ?kasbank_id
	, receipt_descs = ?receipt_descs	
	, receipt_amount = ?receipt_amount
	, allocate_amount = ?allocate_amount
	, receipt_status = ?receipt_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
	, no_voucher = ?no_voucher
	, payment_type_id = ?payment_type_id
	, warkat_no = ?warkat_no
	, warkat_date = ?warkat_date
	, warkat_descs = ?warkat_descs
	, return_no = ?return_no
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?receipt_no", $this->ReceiptNo);
        $this->connector->AddParameter("?receipt_date", $this->ReceiptDate);
        $this->connector->AddParameter("?debtor_id", $this->DebtorId);
        $this->connector->AddParameter("?receipt_descs", $this->ReceiptDescs);
        $this->connector->AddParameter("?payment_type_id", $this->PaymentTypeId);
        $this->connector->AddParameter("?kasbank_id", $this->KasbankId);
        $this->connector->AddParameter("?receipt_amount", $this->ReceiptAmount);
        $this->connector->AddParameter("?allocate_amount", $this->AllocateAmount == null ? 0 : $this->AllocateAmount);
        $this->connector->AddParameter("?receipt_status", $this->ReceiptStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?no_voucher", $this->NoVoucher);
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
        $this->connector->CommandText = "SELECT fc_ar_receiptmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText = "Delete From t_ar_receipt_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs =  $this->connector->ExecuteNonQuery();
        if ($rs){
            $this->UpdateInvoiceMasterPaidAmount($id);
        }
        return $rs;
	}

    public function Void($id) {
        //unpost dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ar_receiptmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText = "Update t_ar_receipt_master a Set a.receipt_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs =  $this->connector->ExecuteNonQuery();
        if ($rs){
            $this->UpdateInvoiceMasterPaidAmount($id);
        }
        return $rs;
    }

    public function GetReceiptDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'OR';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->ReceiptDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public  function GetReceiptDetailRow($id = 0){
        $sql = 'Select count(*) as valout From t_ar_receipt_detail Where receipt_id = ?receipt_id';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?receipt_id", $id);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function UpdateInvoiceMasterPaidAmount($receiptId){
        $sql = 'UPDATE t_ar_invoice_master a JOIN ( SELECT c.receipt_id,c.invoice_id,COALESCE (sum(c.allocate_amount),0) AS sumAlloc	FROM t_ar_receipt_detail c';
	    $sql.= ' Join t_ar_receipt_master d on c.receipt_id = d.id Where d.is_deleted = 0 And d.receipt_status <> 3 GROUP BY c.receipt_id,c.invoice_id) b On a.id = b.invoice_id';
        $sql.= ' Set a.paid_amount = a.paid_amount - b.sumAlloc Where b.receipt_id = ?receiptId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?receiptId", $receiptId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetJSonUnpaidInvoices($cabangId = 0,$customerId = 0 ,$sort = 'a.invoice_no',$order = 'ASC') {
        $sql = "SELECT a.id,a.invoice_no,a.invoice_date,a.due_date,a.balance_amount FROM vw_ar_invoice_master AS a";
        $sql.= " Where a.invoice_status <> 3 and a.is_deleted = 0 and a.balance_amount > 0";
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ?cabangId";
        }
        if ($customerId > 0){
            $sql.= " And a.customer_id = ?customerId";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?customerId", $customerId);
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

    public function Approve($id = 0, $uid = 0){
        $this->connector->CommandText = "SELECT fc_ar_receipt_approve($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unapprove($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ar_receipt_unapprove($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function LoadReceipt4Approval ($cabId = 0,$stDate, $enDate, $rStatus = 0){
        $sql = "SELECT a.* FROM vw_ar_receipt_master a WHERE a.is_deleted = 0 And (a.receipt_date BETWEEN ?stDate And ?enDate)";
        if ($cabId > 0){
            $sql.= " And a.cabang_id = ".$cabId;
        }
        if ($rStatus > -1){
            $sql.= " And a.receipt_status = ".$rStatus;
        }
        $sql.= " ORDER BY a.receipt_date,a.receipt_no";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?stDate", date('Y-m-d', $stDate));
        $this->connector->AddParameter("?enDate", date('Y-m-d', $enDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
