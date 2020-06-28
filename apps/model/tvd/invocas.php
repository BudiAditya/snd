<?php

require_once("invocas_detail.php");

class Invocas extends EntityBase {
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
		$this->connector->CommandText = "SELECT a.* FROM vw_cas_invoice_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cas_invoice_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByInvoiceNo($invoiceNo,$cabangId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_cas_invoice_master AS a WHERE a.invoice_no = ?invoiceNo And a.cabang_id = ?cabangId";
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
        $this->connector->CommandText = "SELECT a.* FROM vw_cas_invoice_master AS a WHERE a.company_id = ?companyId";
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
        $this->connector->CommandText = "SELECT a.* FROM vw_cas_invoice_master AS a WHERE a.company_id = ?companyId And Year(a.invoice_date) = ?tahun And Month(a.invoice_date) = ?bulan And a.is_deleted = 0 And a.invoice_status = 2";
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
        $this->connector->CommandText = "SELECT a.* FROM vw_cas_invoice_master AS a.cabang_id = ?cabangId";
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

	public function Insert() {
        $sql = "INSERT INTO t_cas_ar_invoice_master (db_acc_id,expedition_id,fp_date,nsf_pajak,gudang_id,cabang_id, invoice_no, invoice_date, customer_id, sales_id, invoice_descs, ex_so_id, base_amount, disc_amount, ppn_amount, pph_amount, other_costs, other_costs_amount, payment_type, credit_terms, invoice_status, createby_id, create_time)";
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
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_cas_ar_invoice_master SET
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
        return $rs;
	}

	public function LoadInvoiceSourceByMonth ($year,$month){
	    $sql = "Select a.*,b.cus_code,b.cus_name From vw_cas_source_invoice_detail a Join m_customer b ON a.customer_id = b.id";
	    $sql.= " Where Year(a.invoice_date) = $year And Month(a.invoice_date) = $month";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function CreateDetail($id){
        $this->connector->CommandText = "SELECT fc_cas_create_invoice($id) As valresult;";
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }
}


// End of File: invoice.php
