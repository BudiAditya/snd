<?php

require_once("invoice_detail.php");

class Invoice extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $InvoiceStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
        2 => "APPROVED",
		3 => "VOID"
	);
   
	public $Id = 0;
    public $IsDeleted = false;
    public $CompanyId;
    public $AreaId;
    public $CompanyCode;
    public $CompanyName;
    public $CabangId;
    public $CabangCode;
	public $InvoiceNo;
	public $InvoiceDate;
    public $CustomerId;
    public $CustomerCode;
    public $CustomerName;
    public $CustomerAddress;
    public $SalesName;
    public $SalesId;
	public $InvoiceDescs;
	public $ExSoId = 0;
	public $ExSoNo;
    public $ReceiptDate;
	public $BaseAmount = 0;
    public $DiscAmount = 0;
	public $PpnAmount = 0;
    public $OtherCosts = 0;
    public $OtherCostsAmount = 0;
    public $TotalAmount = 0;
	public $PaidAmount = 0;
    public $CreditTerms = 0;
    public $InvoiceStatus = 0;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;
    public $PaymentType = 1;
    public $GudangId;
    public $GudangCode;
    public $AdminName;
    public $FpDate;
    public $DueDate;
    public $ExpeditionId;
    public $PphAmount = 0;
    public $NsfPajak;
    public $ExpName;
    public $DbAccId = 0;
    public $Npwp;

    //helper
    public $ErrorMsg;

	/** @var InvoiceDetail[] */
	public $Details = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
        $this->Id = $row["id"];
        $this->IsDeleted = $row["is_deleted"] == 1;
        $this->CompanyCode = $row["company_code"];
        $this->CompanyId = $row["company_id"];
        $this->CompanyName = $row["company_name"];
        $this->CabangId = $row["cabang_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->InvoiceNo = $row["invoice_no"];
        $this->InvoiceDate = strtotime($row["invoice_date"]);
        $this->CustomerId = $row["customer_id"];
        $this->CustomerCode = $row["customer_code"];
        $this->CustomerName = $row["customer_name"];
        $this->CustomerAddress = $row["customer_address"];
        $this->Npwp = $row["customer_npwp"];
        $this->SalesName = $row["sales_name"];
        $this->SalesId = $row["sales_id"];
        $this->InvoiceDescs = $row["invoice_descs"];
        $this->ExSoId = $row["ex_so_id"];
        //$this->ExSoNo = $row["ex_so_no"];
        $this->BaseAmount = $row["base_amount"];
        $this->DiscAmount = $row["disc_amount"];
        $this->PpnAmount = $row["ppn_amount"];
        $this->TotalAmount = ($row["base_amount"] - $row["disc_amount"]) + $row["ppn_amount"] + $row["pph_amount"];
        $this->OtherCosts = $row["other_costs"];
        $this->OtherCostsAmount = $row["other_costs_amount"];
        $this->PaidAmount = $row["paid_amount"];
        $this->CreditTerms = $row["credit_terms"];
        $this->InvoiceStatus = $row["invoice_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
        $this->PaymentType = $row["payment_type"];
        $this->GudangId = $row["gudang_id"];
        $this->GudangCode = $row["gudang_code"];
        $this->DueDate = strtotime($row["due_date"]);
        $this->AdminName = $row["admin_name"];
        $this->FpDate = strtotime($row["fp_date"]);
        $this->ExpeditionId = $row["expedition_id"];
        //$this->ExpName = $row["exp_name"];
        $this->PphAmount = $row["pph_amount"];
        $this->NsfPajak = $row["nsf_pajak"];
        $this->AreaId = $row["area_id"];
        $this->DbAccId = $row["db_acc_id"];
	}

	public function FormatInvoiceDate($format = HUMAN_DATE) {
		return is_int($this->InvoiceDate) ? date($format, $this->InvoiceDate) : date($format);
	}

    public function FormatFpDate($format = HUMAN_DATE) {
        return is_int($this->FpDate) ? date($format, $this->FpDate) : null;
    }

    public function FormatDueDate($format = HUMAN_DATE) {
        return is_int($this->DueDate) ? date($format, $this->DueDate) : null;
    }

	/**
	 * @return InvoiceDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new InvoiceDetail();
		$this->Details = $detail->LoadByInvoiceId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Invoice
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ar_invoice_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_invoice_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByInvoiceNo($invoiceNo,$cabangId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ar_invoice_master AS a WHERE a.invoice_no = ?invoiceNo And a.cabang_id = ?cabangId";
		$this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $this->connector->AddParameter("?cabangId", $cabangId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCompanyId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_invoice_master AS a WHERE a.company_id = ?companyId";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Invoice();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByMonth($companyId,$year,$bulan) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_invoice_master AS a WHERE a.company_id = ?companyId And Year(a.invoice_date) = ?tahun And Month(a.invoice_date) = ?bulan And a.is_deleted = 0 And a.invoice_status = 2";
        $this->connector->AddParameter("?companyId", $companyId);
        $this->connector->AddParameter("?tahun", $year);
        $this->connector->AddParameter("?bulan", $bulan);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Invoice();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_invoice_master AS a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Invoice();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function GetUnpaidInvoices($cabangId = 0,$customerId = 0,$invoiceNo = null) {
        $sql = "SELECT a.* FROM vw_ar_invoice_master AS a";
        $sql.= " Where a.invoice_status > 0 and a.is_deleted = 0 and a.balance_amount > 0 And a.invoice_no = ?invoiceNo";
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ?cabangId";
        }
        if ($customerId > 0){
            $sql.= " And a.customer_id = ?customerId";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?customerId", $customerId);
        $this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ar_invoice_master (db_acc_id,expedition_id,fp_date,nsf_pajak,gudang_id,cabang_id, invoice_no, invoice_date, customer_id, sales_id, invoice_descs, ex_so_id, base_amount, disc_amount, ppn_amount, pph_amount, other_costs, other_costs_amount, payment_type, credit_terms, invoice_status, createby_id, create_time)";
        $sql.= "VALUES(?db_acc_id,?expedition_id, ?fp_date, ?nsf_pajak, ?gudang_id, ?cabang_id, ?invoice_no, ?invoice_date, ?customer_id, ?sales_id, ?invoice_descs, ?ex_so_id, ?base_amount, ?disc_amount, ?ppn_amount, ?pph_amount, ?other_costs, ?other_costs_amount, ?payment_type, ?credit_terms, ?invoice_status, ?createby_id, now())";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
        $this->connector->AddParameter("?invoice_no", $this->InvoiceNo, "char");
        $this->connector->AddParameter("?invoice_date", $this->InvoiceDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?sales_id", $this->SalesId);
        $this->connector->AddParameter("?db_acc_id", $this->DbAccId);
        $this->connector->AddParameter("?invoice_descs", $this->InvoiceDescs);
        $this->connector->AddParameter("?ex_so_id", $this->ExSoId == null || $this->ExSoId == '' ? 0 : $this->ExSoId);
        $this->connector->AddParameter("?base_amount", str_replace(",","",$this->BaseAmount));
        $this->connector->AddParameter("?disc_amount", str_replace(",","",$this->DiscAmount));
        $this->connector->AddParameter("?ppn_amount", str_replace(",","",$this->PpnAmount));
        $this->connector->AddParameter("?pph_amount", str_replace(",","",$this->PphAmount));
        $this->connector->AddParameter("?other_costs", str_replace(",","",$this->OtherCosts));
        $this->connector->AddParameter("?other_costs_amount", str_replace(",","",$this->OtherCostsAmount));
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?invoice_status", $this->InvoiceStatus);
        $this->connector->AddParameter("?expedition_id", $this->ExpeditionId);
        $this->connector->AddParameter("?fp_date", $this->FpDate);
        $this->connector->AddParameter("?nsf_pajak", $this->NsfPajak);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            if (strlen($this->ExSoId) > 2) {
                $this->PostPoDetail2Invoice($this->Id, $this->InvoiceNo, $this->ExSoId);
            }
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ar_invoice_master SET
	cabang_id = ?cabang_id
	, gudang_id = ?gudang_id
	, invoice_no = ?invoice_no
	, invoice_date = ?invoice_date
	, customer_id = ?customer_id
	, sales_id = ?sales_id
	, invoice_descs = ?invoice_descs
	, ex_so_id = ?ex_so_id
	, db_acc_id = ?db_acc_id
	, base_amount = ?base_amount
	, disc_amount = ?disc_amount
	, ppn_amount = ?ppn_amount
	, pph_amount = ?pph_amount
	, other_costs = ?other_costs
	, other_costs_amount = ?other_costs_amount
	, payment_type = ?payment_type
	, credit_terms = ?credit_terms
	, invoice_status = ?invoice_status
	, updateby_id = ?updateby_id
	, expedition_id = ?expedition_id
	, fp_date = ?fp_date
	, nsf_pajak = ?nsf_pajak
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
        $this->connector->AddParameter("?invoice_no", $this->InvoiceNo, "char");
        $this->connector->AddParameter("?invoice_date", $this->InvoiceDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?sales_id", $this->SalesId);
        $this->connector->AddParameter("?db_acc_id", $this->DbAccId);
        $this->connector->AddParameter("?invoice_descs", $this->InvoiceDescs);
        $this->connector->AddParameter("?ex_so_id", $this->ExSoId == null || $this->ExSoId == '' ? 0 : $this->ExSoId);
        $this->connector->AddParameter("?base_amount", str_replace(",","",$this->BaseAmount));
        $this->connector->AddParameter("?disc_amount", str_replace(",","",$this->DiscAmount));
        $this->connector->AddParameter("?ppn_amount", str_replace(",","",$this->PpnAmount));
        $this->connector->AddParameter("?pph_amount", str_replace(",","",$this->PphAmount));
        $this->connector->AddParameter("?other_costs", str_replace(",","",$this->OtherCosts));
        $this->connector->AddParameter("?other_costs_amount", str_replace(",","",$this->OtherCostsAmount));
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?invoice_status", $this->InvoiceStatus);
        $this->connector->AddParameter("?expedition_id", $this->ExpeditionId);
        $this->connector->AddParameter("?fp_date", $this->FpDate);
        $this->connector->AddParameter("?nsf_pajak", $this->NsfPajak);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1){
            $this->RecalculateInvoiceMaster($id);
        }
        return $rs;
	}

	public function Delete($id,$exSoNo = null) {
        //unpost stock dulu
        $rsx = null;

        $this->connector->CommandText = "SELECT fc_ar_invoicemaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        if ($exSoNo != null){
            #Update PO Status
            $this->connector->CommandText = "Update t_ar_so_master AS a Set a.po_status = 1 Where a.so_no = '".$exSoNo."'";
            $this->connector->ExecuteNonQuery();
        }

        //hapus data invoice_
        $this->connector->CommandText = "Delete From t_ar_invoice_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id,$exSoNo) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ar_invoicemaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        if ($exSoNo != null){
            #Update PO Status
            $this->connector->CommandText = "Update t_ar_so_master AS a Set a.so_status = 1 Where a.so_no = '".$exSoNo."'";
            $this->connector->ExecuteNonQuery();
        }
        //mark as void data invoice_
        $this->connector->CommandText = "Update t_ar_invoice_master a Set a.invoice_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rsz =  $this->connector->ExecuteNonQuery();
        //update so status
        //$this->connector->CommandText = "SELECT fc_ar_so_checkstatus_by_invoice('".$this->InvoiceNo."') As valresult;";
        //$rsx = $this->connector->ExecuteQuery();
        return $rsz;
    }

    public function GetInvoiceDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'IV';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->InvoiceDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }
    
    public function RecalculateInvoiceMaster($invoiceId){
        $sql = 'Update t_ar_invoice_master a Set a.base_amount = 0, a.ppn_amount = 0, a.pph_amount = 0, a.disc_amount = 0,a.other_costs_amount = 0 Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_invoice_master a
Join (Select c.invoice_id, coalesce(sum(c.sub_total),0) As subTotal, coalesce(sum(c.disc_amount),0) as sumDiscount, coalesce(sum(c.ppn_amount),0) as sumPpn, coalesce(sum(c.pph_amount),0) as sumPph, coalesce(sum(c.by_angkut),0) as sumByAngkut From t_ar_invoice_detail c Group By c.invoice_id) b
On a.id = b.invoice_id Set a.base_amount = b.subTotal, a.disc_amount = b.sumDiscount, a.ppn_amount = b.sumPpn, a.pph_amount = b.sumPph, a.other_costs_amount = b.sumByangkut Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_invoice_master a Set a.paid_amount = (a.base_amount - a.disc_amount) + a.ppn_amount + a.pph_amount + a.other_costs_amount Where a.id = ?invoiceId And a.payment_type = 0;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function Load4Reports($companyId, $cabangId = 0, $gudangId = 0, $customerId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null,$entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 1 & 4
        $sql = "SELECT a.* FROM vw_ar_invoice_master AS a WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        $sql.= " Order By a.invoice_date,a.invoice_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($companyId, $cabangId = 0, $gudangId = 0,$customerId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null,$entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 2
        $sql = "SELECT a.*,c.item_code,d.brand_name,c.item_name,b.sales_qty,b.price,b.disc_formula,b.disc_amount,b.sub_total,b.ppn_amount FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail b On a.id = b.invoice_id JOIN m_items c ON b.item_id = c.id LEFT JOIN m_item_brand d ON c.brand_id = d.id";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($entityId > 0){
            $sql.= " and d.entity_id = ".$entityId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        if ($principalId > 0){
            $sql.= " and c.principal_id = ".$principalId;
        }
        if ($brandId > 0){
            $sql.= " and c.brand_id = ".$brandId;
        }
        $sql.= " Order By a.invoice_date,a.invoice_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($companyId, $cabangId = 0, $gudangId = 0,$customerId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null,$entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 3
        $sql = "SELECT d.entity_id,d.brand_name,c.item_code,c.item_name,c.s_uom_qty,c.qty_convert,b.price, coalesce(sum(b.sales_qty),0) as sum_qty,coalesce(sum(b.sub_total-b.disc_amount),0) as sum_dpp, sum(b.ppn_amount) as sum_ppn";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.id = b.invoice_id Join m_items AS c On b.item_id = c.id LEFT JOIN m_item_brand d ON c.brand_id = d.id";
        $sql.= " WHERE a.is_deleted = 0 And a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($entityId > 0){
            $sql.= " and d.entity_id = ".$entityId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        if ($principalId > 0){
            $sql.= " and c.principal_id = ".$principalId;
        }
        if ($brandId > 0){
            $sql.= " and c.brand_id = ".$brandId;
        }
        $sql.= " Group By d.entity_id,d.brand_name,c.item_code,c.item_name,c.s_uom_qty,c.qty_convert,b.price Order By c.item_name,c.item_code,b.price";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem1($companyId, $cabangId = 0, $gudangId = 0,$customerId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null, $entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 5
        $sql = "SELECT d.entity_id,d.brand_name,c.item_code, c.item_name, c.s_uom_code as satuan, c.s_uom_qty, c.qty_convert, coalesce(sum(b.sales_qty),0) as sum_qty";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.id = b.invoice_id Join m_items AS c On b.item_id = c.id LEFT JOIN m_item_brand d ON c.brand_id = d.id";
        $sql.= " WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($entityId > 0){
            $sql.= " and d.entity_id = ".$entityId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        if ($principalId > 0){
            $sql.= " and c.principal_id = ".$principalId;
        }
        if ($brandId > 0){
            $sql.= " and c.brand_id = ".$brandId;
        }
        $sql.= " Group By d.entity_id,d.brand_name,c.item_code,c.item_name,c.s_uom_code,c.s_uom_qty,c.qty_convert Order By c.item_name,c.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetJSonInvoices($cabangId,$customerId) {
        $sql = "SELECT a.id,a.invoice_no,a.invoice_date,a.ppn_pct FROM t_ar_invoice_master as a Where a.invoice_status <> 3 And a.is_deleted = 0 And a.cabang_id = ".$cabangId." And a.customer_id = ".$customerId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.invoice_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetJSonInvoiceItems($invoiceId = 0) {
        $sql = "SELECT a.id,a.item_id,b.item_code,b.item_name as item_descs,a.sales_qty - a.return_qty as qty_jual,b.s_uom_code as satuan,round(a.sub_total/a.sales_qty,2) as price,a.disc_formula,a.disc_amount,a.ppn_pct,a.pph_pct,c.gudang_id,a.item_hpp,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil,b.s_uom_qty as bisisatkecil,a.is_free FROM t_ar_invoice_detail AS a";
        $sql.= " JOIN m_items AS b ON a.item_id = b.id Join t_ar_invoice_master c On a.invoice_id = c.id Where c.invoice_status <> 3 And (a.sales_qty - coalesce(a.return_qty,0)) > 0 And a.invoice_id = ".$invoiceId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By b.item_code Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetInvoiceItemCount($invoiceId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ar_invoice_detail as a Where a.invoice_id = ?invoiceId;";
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Approve($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ar_invoice_approve($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unapprove($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ar_invoice_unapprove($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    //function post po detail into invoice detail
    public function PostPoDetail2Invoice($id,$invoiceo,$pono){
        $sql = "Update t_ar_invoice_master a Join t_ar_so_master b On a.ex_so_id = b.so_no";
        $sql.= " Set a.payment_type = b.payment_type, a.credit_terms = b.credit_terms, a.ppn_pct = b.ppn_pct, a.ppn_amount = b.ppn_amount, a.disc_pct = b.disc_pct, a.disc_amount = b.disc_amount, a.disc2_pct = b.disc2_pct, a.disc2_amount = b.disc2_amount, a.other_costs = b.other_costs, a.other_costs_amount = b.other_costs_amount";
        $sql.= " Where a.id = $id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        $sql = "Insert Into t_ar_invoice_detail (invoice_id,cabang_id,invoice_no,item_id,item_code,item_descs,invoice_qty,price,disc_formula,disc_amount,sub_total)";
        $sql.= " Select $id,a.cabang_id,'".$invoiceo."',a.item_id,a.item_code,a.item_descs,a.order_qty,a.price,a.disc_formula,a.disc_amount,a.sub_total From t_ar_so_detail AS a Where a.so_no = '".$pono."' Order By a.id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs){
            #Post detailnya
            $this->connector->CommandText = "SELECT fc_ar_invoicedetail_all_post($id) As valresult;";
            $this->connector->ExecuteQuery();
            #Update PO Qty received
            $this->connector->CommandText = "Update t_ar_so_detail AS a Set a.receipt_qty = a.order_qty Where a.so_no = '".$pono."'";
            $this->connector->ExecuteQuery();
            #Update PO status
            $sql = "Update t_ar_so_master AS a Set a.po_status = 2 Where a.so_no = '".$pono."'";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();
        }
        return $rs;
    }

    public function GetInvoiceItemRow($invoiceId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ar_invoice_detail as a Where a.invoice_id = ?invoiceId;";
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function GetJSonInvoicesByGudang($gudangId,$customerId) {
        $sql = "SELECT a.id,a.invoice_no,a.invoice_date,a.base_amount,a.disc_amount,a.ppn_amount,a.pph_amount FROM t_ar_invoice_master as a Where a.invoice_status <> 3 And a.is_deleted = 0 And a.gudang_id = ".$gudangId." And a.customer_id = ".$customerId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.invoice_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetInvoiceSumByYear($year){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.invoice_date) = 1 THEN a.total_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 2 THEN a.total_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 3 THEN a.total_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 4 THEN a.total_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 5 THEN a.total_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 6 THEN a.total_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 7 THEN a.total_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 8 THEN a.total_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 9 THEN a.total_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 10 THEN a.total_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 11 THEN a.total_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.invoice_date) = 12 THEN a.total_amount ELSE 0 END), 0) December
			    FROM vw_ar_invoice_master a Where year(a.invoice_date) = $year And a.invoice_status <> 3 And a.is_deleted = 0";
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $data = $row["January"];
        $data.= ",".$row["February"];
        $data.= ",".$row["March"];
        $data.= ",".$row["April"];
        $data.= ",".$row["May"];
        $data.= ",".$row["June"];
        $data.= ",".$row["July"];
        $data.= ",".$row["August"];
        $data.= ",".$row["September"];
        $data.= ",".$row["October"];
        $data.= ",".$row["November"];
        $data.= ",".$row["December"];
        return $data;
    }

    public function GetReceiptSumByYear($year){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.receipt_date) = 1 THEN a.receipt_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 2 THEN a.receipt_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 3 THEN a.receipt_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 4 THEN a.receipt_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 5 THEN a.receipt_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 6 THEN a.receipt_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 7 THEN a.receipt_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 8 THEN a.receipt_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 9 THEN a.receipt_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 10 THEN a.receipt_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 11 THEN a.receipt_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.receipt_date) = 12 THEN a.receipt_amount ELSE 0 END), 0) December
			    FROM vw_ar_receipt_master a Where year(a.receipt_date) = $year And a.receipt_status <> 3 And a.is_deleted = 0";
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $data = $row["January"];
        $data.= ",".$row["February"];
        $data.= ",".$row["March"];
        $data.= ",".$row["April"];
        $data.= ",".$row["May"];
        $data.= ",".$row["June"];
        $data.= ",".$row["July"];
        $data.= ",".$row["August"];
        $data.= ",".$row["September"];
        $data.= ",".$row["October"];
        $data.= ",".$row["November"];
        $data.= ",".$row["December"];
        return $data;
    }

    public function LoadSalesOmsetReports($companyId, $cabangId = 0, $gudangId = 0, $customerId = 0, $salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null, $entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 6
        $sql = "Select a.sales_id,a.sales_name,coalesce(sum(a.base_amount-a.disc_amount),0) as sum_dpp,coalesce(sum(a.ppn_amount),0) as sum_ppn,coalesce(sum(a.paid_amount),0) as paid_amount,coalesce(sum(a.return_amount),0) as return_amount";
        $sql.= " FROM vw_ar_invoice_master AS a WHERE a.is_deleted = 0 and a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        $sql.= " Group By a.sales_id,a.sales_name";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadOmsetByEntityReports($companyId, $cabangId = 0, $gudangId = 0,$salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null, $entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 7
        $sql = "SELECT d.entity_id,e.entity_code,e.entity_name,coalesce(sum(b.sales_qty),0) as sum_qty,coalesce(sum(b.sales_qty * c.qty_convert),0) as sum_liter,coalesce(sum(b.sub_total-b.disc_amount),0) as sum_dpp, sum(b.ppn_amount) as sum_ppn";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.id = b.invoice_id Join m_items AS c On b.item_id = c.id LEFT JOIN m_item_brand d ON c.brand_id = d.id LEFT JOIN m_item_entity e ON d.entity_id = e.id";
        $sql.= " WHERE a.is_deleted = 0 And a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($entityId > 0){
            $sql.= " and d.entity_id = ".$entityId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        if ($principalId > 0){
            $sql.= " and c.principal_id = ".$principalId;
        }
        if ($brandId > 0){
            $sql.= " and c.brand_id = ".$brandId;
        }
        $sql.= " Group By d.entity_id,e.entity_code,e.entity_name Order By e.entity_code,e.entity_name";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadOmsetBySalesDetailReports($companyId, $cabangId = 0, $gudangId = 0,$salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null, $entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 8
        $sql = "SELECT a.sales_id,a.sales_name,d.entity_id,e.entity_code,e.entity_name,coalesce(sum(b.sales_qty),0) as sum_qty,coalesce(sum(b.sales_qty * c.qty_convert),0) as sum_liter,coalesce(sum(b.sub_total-b.disc_amount),0) as sum_dpp, sum(b.ppn_amount) as sum_ppn";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.id = b.invoice_id Join m_items AS c On b.item_id = c.id LEFT JOIN m_item_brand d ON c.brand_id = d.id LEFT JOIN m_item_entity e ON d.entity_id = e.id";
        $sql.= " WHERE a.is_deleted = 0 And a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($entityId > 0){
            $sql.= " and d.entity_id = ".$entityId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        if ($principalId > 0){
            $sql.= " and c.principal_id = ".$principalId;
        }
        if ($brandId > 0){
            $sql.= " and c.brand_id = ".$brandId;
        }
        $sql.= " Group By a.sales_id,a.sales_name,d.entity_id,e.entity_code,e.entity_name Order By a.sales_name,e.entity_code,e.entity_name";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadOmsetByPrincipleReports($companyId, $cabangId = 0, $gudangId = 0,$salesId = 0, $invoiceStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null, $entityId = 0,$principalId = 0,$propId = 0,$salesAreaId = 0,$brandId = 0,$cabIds = null) {
        //laptype = 9
        $sql = "SELECT c.principal_id,c.principal_code,c.principal_name,coalesce(sum(b.sales_qty),0) as sum_qty,coalesce(sum(b.sales_qty * c.qty_convert),0) as sum_liter,coalesce(sum(b.sub_total-b.disc_amount),0) as sum_dpp, sum(b.ppn_amount) as sum_ppn";
        $sql.= " FROM vw_ar_invoice_master AS a Join t_ar_invoice_detail AS b On a.id = b.invoice_id Join vw_ic_items AS c On b.item_id = c.id LEFT JOIN m_item_brand d ON c.brand_id = d.id LEFT JOIN m_item_entity e ON d.entity_id = e.id";
        $sql.= " WHERE a.is_deleted = 0 And a.invoice_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.cabang_id IN (".$cabIds.")";
        }
        if ($companyId > 0){
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($gudangId > 0){
            $sql.= " and a.gudang_id = ".$gudangId;
        }
        if ($invoiceStatus > -1){
            $sql.= " and a.invoice_status = ".$invoiceStatus;
        }else{
            $sql.= " and a.invoice_status <> 3 ";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > 0";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) = 0";
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        if ($entityId > 0){
            $sql.= " and d.entity_id = ".$entityId;
        }
        if ($salesAreaId > 0){
            $sql.= " and a.area_id = ".$salesAreaId;
        }
        if ($propId > 0){
            $sql.= " and a.prop_id = ".$propId;
        }
        if ($principalId > 0){
            $sql.= " and c.principal_id = ".$principalId;
        }
        if ($brandId > 0){
            $sql.= " and c.brand_id = ".$brandId;
        }
        $sql.= " Group By c.principal_id,c.principal_code,c.principal_name Order By c.principal_name";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetJSonPenjualanByEntity($type,$year,$month){
        if ($type == 1) {
            $query = "Select a.entity_code as kode,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_entity_by_year a Where a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2){
            $query = "Select a.entity_code as kode,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_entity_by_month a Where a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $query = "Select a.entity_code as kode,sum(a.sumPenjualan) as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_entity_by_month a Where a.tahun = $year And a.bulan <= $month GROUP BY a.entity_code Order By a.sumPenjualan";
        }
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function GetJSonPenjualanByPrincipal($type,$year,$month){
        if ($type == 1) {
            $query = "Select a.principal_name as kode,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_principle_by_year a Where a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2){
            $query = "Select a.principal_name as kode,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_principle_by_month a Where a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $query = "Select a.principal_name as kode,sum(a.sumPenjualan) as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_principle_by_month a Where a.tahun = $year And a.bulan <= $month GROUP BY a.principal_name Order By a.sumPenjualan";
        }
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function GetJSonPenjualanByArea($type,$year,$month){
        if ($type == 1) {
            $query = "Select a.area_name as kode,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_salesarea_by_year a Where a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2){
            $query = "Select a.area_name as kode,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_salesarea_by_month a Where a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $query = "Select a.area_name as kode,sum(a.sumPenjualan) as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_salesarea_by_month a Where a.tahun = $year And a.bulan <= $month GROUP BY a.area_name Order By a.sumPenjualan";
        }
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function GetJSonPenjualanBySalesman($type,$year,$month){
        if ($type == 1) {
            $query = "Select a.sales_name as nama,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_salesman_by_year a Where a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2){
            $query = "Select a.sales_name as nama,a.sumPenjualan as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_salesman_by_month a Where a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $query = "Select a.sales_name as nama,sum(a.sumPenjualan) as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_salesman_by_month a Where a.tahun = $year And a.bulan <= $month GROUP BY a.sales_name Order By sum(a.sumPenjualan)";
        }
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function LoadEntityOmset($type,$year,$month) {
        if ($type == 1) {
            $sql = "Select a.entity_code,a.entity_name,a.sumPenjualan as omset FROM vw_ar_invoice_sum_by_entity_by_year AS a WHERE a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2){
            $sql = "Select a.entity_code,a.entity_name,a.sumPenjualan as omset FROM vw_ar_invoice_sum_by_entity_by_month AS a WHERE a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $sql = "Select a.entity_code,a.entity_name,sum(a.sumPenjualan) as omset FROM vw_ar_invoice_sum_by_entity_by_month AS a WHERE a.tahun = $year And a.bulan <= $month Group By a.entity_code,a.entity_name Order By sum(a.sumPenjualan)";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadPrincipalOmset($type,$year,$month) {
        if ($type == 1) {
            $sql = "Select a.principal_code, a.principal_name,a.sumPenjualan as omset FROM vw_ar_invoice_sum_by_principle_by_year AS a WHERE a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2) {
            $sql = "Select a.principal_code, a.principal_name,a.sumPenjualan as omset FROM vw_ar_invoice_sum_by_principle_by_month AS a WHERE a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $sql = "Select a.principal_code, a.principal_name,sum(a.sumPenjualan) as omset FROM vw_ar_invoice_sum_by_principle_by_month AS a WHERE a.tahun = $year And a.bulan <= $month Group By a.principal_code, a.principal_name Order By sum(a.sumPenjualan)";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadSalesOmset($type,$year,$month) {
        $sql = "Select a.sales_id,a.sales_name,coalesce(sum(a.total_amount),0) as omset FROM vw_ar_invoice_master AS a WHERE a.is_deleted = 0 And a.invoice_status <> 3 And year(a.invoice_date) = $year";
        if ($type == 2){
            $sql.= " And month(a.invoice_date) = ".$month;
        }elseif ($type == 3){
            $sql.= " And month(a.invoice_date) <= ".$month;
        }
        $sql.= " Group By a.sales_id,a.sales_name Order By sum(a.total_amount)";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadAreaOmset($type,$year,$month) {
        if ($type == 1) {
            $sql = "Select a.area_code,a.area_name,a.sumPenjualan as omset FROM vw_ar_invoice_sum_by_salesarea_by_year AS a WHERE a.tahun = $year Order By a.sumPenjualan";
        }elseif ($type == 2){
            $sql = "Select a.area_code,a.area_name,a.sumPenjualan as omset FROM vw_ar_invoice_sum_by_salesarea_by_month AS a WHERE a.tahun = $year And a.bulan = $month Order By a.sumPenjualan";
        }else{
            $sql = "Select a.area_code,a.area_name,sum(a.sumPenjualan) as omset FROM vw_ar_invoice_sum_by_salesarea_by_month AS a WHERE a.tahun = $year And a.bulan <= $month Group By a.area_code,a.area_name Order By sum(a.sumPenjualan)";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }


    public function LoadTop10Customer($type,$year,$month) {
        if ($type == 1) {
            $sql = "Select a.customer_code,a.customer_name, a.sum_trx as omset From vw_ar_invoice_sum_by_customer_trx_by_year a Where a.trx_year = $year Order By a.sum_trx Desc Limit 0,10;";
        }elseif ($type == 2){
            $sql = "Select a.customer_code,a.customer_name, a.sum_trx as omset From vw_ar_invoice_sum_by_customer_trx_by_month a Where a.trx_year = $year And a.trx_month = $month Order By a.sum_trx Desc Limit 0,10;";
        }else{
            $sql = "Select a.customer_code,a.customer_name, sum(a.sum_trx) as omset From vw_ar_invoice_sum_by_customer_trx_by_month a Where a.trx_year = $year And a.trx_month <= $month Group By a.customer_code,a.customer_name Order By sum(a.sum_trx) Desc Limit 0,10;";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }


    public function GetJSonTop10Customer($type,$year,$month){
        if ($type == 1) {
            $query = "Select a.customer_code as kode, a.sum_trx as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_customer_trx_by_year a Where a.trx_year = $year Order By a.sum_trx Desc Limit 0,10;";
        }elseif ($type == 2){
            $query = "Select a.customer_code as kode, a.sum_trx as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_customer_trx_by_month a Where a.trx_year = $year And a.trx_month = $month Order By a.sum_trx Desc Limit 0,10;";
        }else{
            $query = "Select a.customer_code as kode, sum(a.sum_trx) as nilai,zfc_random_color() as warna From vw_ar_invoice_sum_by_customer_trx_by_month a Where a.trx_year = $year And a.trx_month <= $month Group By a.customer_code Order By sum(a.sum_trx) Desc Limit 0,10;";
        }
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function LoadTop10Item($type,$year,$month) {
        if ($type == 1) {
            $sql = "Select a.item_code,a.item_name,a.nilai From vw_ar_omset_by_item_by_year a Where a.tahun = $year Order By a.nilai Desc Limit 0,10;";
        }elseif ($type == 2){
            $sql = "Select a.item_code,a.item_name,a.nilai From vw_ar_omset_by_item_by_month a Where a.tahun = $year And a.bulan = $month Order By a.nilai Desc Limit 0,10;";
        }else{
            $sql = "Select a.item_code,a.item_name,sum(a.nilai) as nilai From vw_ar_omset_by_item_by_month a Where a.tahun = $year And a.bulan <= $month Group By a.item_code,a.item_name Order By sum(a.nilai) Desc Limit 0,10;";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }


    public function GetJSonTop10Item($type,$year,$month){
        if ($type == 1) {
            $query = "Select a.item_code as kode, a.nilai,zfc_random_color() as warna From vw_ar_omset_by_item_by_year a Where a.tahun = $year Order By a.nilai Desc Limit 0,10;";
        }elseif ($type == 2){
            $query = "Select a.item_code as kode, a.nilai,zfc_random_color() as warna From vw_ar_omset_by_item_by_month a Where a.tahun = $year And a.bulan = $month Order By a.nilai Desc Limit 0,10;";
        }else{
            $query = "Select a.item_code as kode, sum(a.nilai) as nilai,zfc_random_color() as warna From vw_ar_omset_by_item_by_month a Where a.tahun = $year And a.bulan <= $month Group By a.item_code Order By sum(a.nilai) Desc Limit 0,10;";
        }
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function LoadInvoiceDelivery ($areaId = 0, $whId = 0, $stDate, $enDate, $dStatus = 0){
        $sql = "Select a.* From vw_ar_invoice_delivery_detail a Where a.invoice_date BETWEEN ?stDate and ?enDate";
        if ($areaId > 0){
            $sql.= " And a.area_id = ".$areaId;
        }
        if ($whId > 0){
            $sql.= " And a.gudang_id = ".$whId;
        }
        $sql.= " And a.delivery_status = ".$dStatus;
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?stDate", date('Y-m-d', $stDate));
        $this->connector->AddParameter("?enDate", date('Y-m-d', $enDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function ProcessDelivery($id){
        $sql = "Update t_ar_invoice_master a Set a.delivery_status = 1,a.delivery_date = now() Where a.id = $id";
        $this->connector->CommandText = $sql;
        return $this->connector->ExecuteNonQuery();
    }
}


// End of File: invoice.php
