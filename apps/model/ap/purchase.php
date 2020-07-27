<?php

require_once("purchase_detail.php");

class Purchase extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $GrnStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
        2 => "APPROVED",
		3 => "VOID"
	);
   
	public $Id;
    public $IsDeleted = false;
    public $CompanyId;
    public $AreaId;
    public $CompanyCode;
    public $CompanyName;
    public $CabangId;
    public $CabangCode;
	public $GrnNo;
	public $GrnDate;
    public $SupplierId;
    public $SupplierCode;
    public $SupplierName;
    public $SalesName;
	public $GrnDescs;
	public $ExPoId = 0;
	public $ExPoNo;
    public $ReceiptDate;
	public $BaseAmount = 0;
    public $DiscAmount = 0;
	public $PpnAmount = 0;
    public $OtherCosts = 0;
    public $OtherCostsAmount = 0;
    public $TotalAmount = 0;
	public $PaidAmount = 0;
    public $CreditTerms = 0;
    public $GrnStatus = 0;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;
    public $PaymentType = 2;
    public $GudangId;
    public $GudangCode;
    public $AdminName;
    public $SupInvNo;
    public $SupInvDate;
    public $JtpDate;
    public $ExpeditionId;
    public $PphAmount = 0;
    public $NsfPajak;
    public $ErrorMsg;

	/** @var PurchaseDetail[] */
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
        $this->GrnNo = $row["grn_no"];
        $this->GrnDate = strtotime($row["grn_date"]);
        $this->SupplierId = $row["supplier_id"];
        $this->SupplierCode = $row["supplier_code"];
        $this->SupplierName = $row["supplier_name"];
        $this->SalesName = $row["sales_name"];
        $this->GrnDescs = $row["grn_descs"];
        $this->ExPoId = $row["ex_po_id"];
        $this->ExPoNo = $row["ex_po_no"];
        $this->BaseAmount = $row["base_amount"];
        $this->DiscAmount = $row["disc_amount"];
        $this->PpnAmount = $row["ppn_amount"];
        $this->OtherCosts = $row["other_costs"];
        $this->OtherCostsAmount = $row["other_costs_amount"];
        $this->TotalAmount = $row["total_amount"];
        $this->PaidAmount = $row["paid_amount"];
        $this->CreditTerms = $row["credit_terms"];
        $this->ReceiptDate = strtotime($row["receipt_date"]);
        $this->GrnStatus = $row["grn_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
        $this->PaymentType = $row["payment_type"];
        $this->GudangId = $row["gudang_id"];
        $this->GudangCode = $row["gudang_code"];
        $this->JtpDate = strtotime($row["jtp_date"]);
        $this->AdminName = $row["admin_name"];
        $this->SupInvDate = strtotime($row["sup_inv_date"]);
        $this->SupInvNo = $row["sup_inv_no"];
        $this->ExpeditionId = $row["expedition_id"];
        $this->PphAmount = $row["pph_amount"];
        $this->NsfPajak = $row["nsf_pajak"];
	}

	public function FormatGrnDate($format = HUMAN_DATE) {
		return is_int($this->GrnDate) ? date($format, $this->GrnDate) : date($format, strtotime(date('Y-m-d')));
	}

    public function FormatReceiptDate($format = HUMAN_DATE) {
        return is_int($this->ReceiptDate) ? date($format, $this->ReceiptDate) : date($format, strtotime(date('Y-m-d')));
    }

    public function FormatSupInvDate($format = HUMAN_DATE) {
        return is_int($this->SupInvDate) ? date($format, $this->SupInvDate) : null;
    }

    public function FormatJtpDate($format = HUMAN_DATE) {
        return is_int($this->JtpDate) ? date($format, $this->JtpDate) : null;
    }

	/**
	 * @return PurchaseDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new PurchaseDetail();
		$this->Details = $detail->LoadByGrnId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Grn
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ap_purchase_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_purchase_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByGrnNo($grnNo,$cabangId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ap_purchase_master AS a WHERE a.grn_no = ?grnNo And a.cabang_id = ?cabangId";
		$this->connector->AddParameter("?grnNo", $grnNo);
        $this->connector->AddParameter("?cabangId", $cabangId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCompanyId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_purchase_master AS a WHERE a.company_id = ?companyId";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Purchase();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_purchase_master AS a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Purchase();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function GetUnpaidGrns($cabangId = 0,$supplierId = 0,$grnNo = null) {
        $sql = "SELECT a.* FROM vw_ap_purchase_master AS a";
        $sql.= " Where a.grn_status > 0 and a.is_deleted = 0 and a.balance_amount > 0 And a.grn_no = ?grnNo";
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ?cabangId";
        }
        if ($supplierId > 0){
            $sql.= " And a.supplier_id = ?supplierId";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?supplierId", $supplierId);
        $this->connector->AddParameter("?grnNo", $grnNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ap_purchase_master (expedition_id,sup_inv_no,sup_inv_date,jtp_date,nsf_pajak,gudang_id,cabang_id, grn_no, grn_date, receipt_date, supplier_id, sales_name, grn_descs, ex_po_id, base_amount, disc_amount, ppn_amount, pph_amount, other_costs, other_costs_amount, payment_type, credit_terms, grn_status, createby_id, create_time)";
        $sql.= "VALUES(?expedition_id, ?sup_inv_no, ?sup_inv_date, ?jtp_date, ?nsf_pajak, ?gudang_id, ?cabang_id, ?grn_no, ?grn_date, ?receipt_date, ?supplier_id, ?sales_name, ?grn_descs, ?ex_po_id, ?base_amount, ?disc_amount, ?ppn_amount, ?pph_amount, ?other_costs, ?other_costs_amount, ?payment_type, ?credit_terms, ?grn_status, ?createby_id, now())";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
        $this->connector->AddParameter("?grn_no", $this->GrnNo, "char");
        $this->connector->AddParameter("?grn_date", $this->GrnDate);
        $this->connector->AddParameter("?receipt_date", $this->ReceiptDate);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
        $this->connector->AddParameter("?sales_name", $this->SalesName);
        $this->connector->AddParameter("?grn_descs", $this->GrnDescs);
        $this->connector->AddParameter("?ex_po_id", $this->ExPoId == null || $this->ExPoId == '' ? 0 : $this->ExPoId);
        $this->connector->AddParameter("?base_amount", str_replace(",","",$this->BaseAmount));
        $this->connector->AddParameter("?disc_amount", str_replace(",","",$this->DiscAmount));
        $this->connector->AddParameter("?ppn_amount", str_replace(",","",$this->PpnAmount));
        $this->connector->AddParameter("?pph_amount", str_replace(",","",$this->PphAmount));
        $this->connector->AddParameter("?other_costs", str_replace(",","",$this->OtherCosts));
        $this->connector->AddParameter("?other_costs_amount", str_replace(",","",$this->OtherCostsAmount));
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?grn_status", $this->GrnStatus);
        $this->connector->AddParameter("?expedition_id", $this->ExpeditionId);
        $this->connector->AddParameter("?sup_inv_no", $this->SupInvNo);
        $this->connector->AddParameter("?sup_inv_date", is_int($this->SupInvDate) ? $this->FormatSupInvDate(SQL_DATEONLY) : null);
        $this->connector->AddParameter("?jtp_date", is_int($this->JtpDate) ? $this->FormatJtpDate(SQL_DATEONLY) : null);
        $this->connector->AddParameter("?nsf_pajak", $this->NsfPajak);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
			if (strlen($this->ExPoId) > 2) {
                $this->PostPoDetail2Purchase($this->Id, $this->GrnNo, $this->ExPoId);
                $this->RecalculateGrnMaster($this->Id);
            }
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ap_purchase_master SET
	cabang_id = ?cabang_id
	, gudang_id = ?gudang_id
	, grn_no = ?grn_no
	, grn_date = ?grn_date
	, receipt_date = ?receipt_date
	, supplier_id = ?supplier_id
	, sales_name = ?sales_name
	, grn_descs = ?grn_descs
	, ex_po_id = ?ex_po_id
	, base_amount = ?base_amount
	, disc_amount = ?disc_amount
	, ppn_amount = ?ppn_amount
	, pph_amount = ?pph_amount
	, other_costs = ?other_costs
	, other_costs_amount = ?other_costs_amount
	, payment_type = ?payment_type
	, credit_terms = ?credit_terms
	, grn_status = ?grn_status
	, updateby_id = ?updateby_id
	, expedition_id = ?expedition_id
	, sup_inv_date = ?sup_inv_date
	, sup_inv_no = ?sup_inv_no
	, jtp_date = ?jtp_date
	, nsf_pajak = ?nsf_pajak
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
        $this->connector->AddParameter("?grn_no", $this->GrnNo, "char");
        $this->connector->AddParameter("?grn_date", $this->GrnDate);
        $this->connector->AddParameter("?receipt_date", $this->ReceiptDate);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
        $this->connector->AddParameter("?sales_name", $this->SalesName);
        $this->connector->AddParameter("?grn_descs", $this->GrnDescs);
        $this->connector->AddParameter("?ex_po_id", $this->ExPoId == null || $this->ExPoId == '' ? 0 : $this->ExPoId);
        $this->connector->AddParameter("?base_amount", str_replace(",","",$this->BaseAmount));
        $this->connector->AddParameter("?disc_amount", str_replace(",","",$this->DiscAmount));
        $this->connector->AddParameter("?ppn_amount", str_replace(",","",$this->PpnAmount));
        $this->connector->AddParameter("?pph_amount", str_replace(",","",$this->PphAmount));
        $this->connector->AddParameter("?other_costs", str_replace(",","",$this->OtherCosts));
        $this->connector->AddParameter("?other_costs_amount", str_replace(",","",$this->OtherCostsAmount));
        $this->connector->AddParameter("?payment_type", $this->PaymentType);
        $this->connector->AddParameter("?credit_terms", $this->CreditTerms);
        $this->connector->AddParameter("?grn_status", $this->GrnStatus);
        $this->connector->AddParameter("?expedition_id", $this->ExpeditionId);
        $this->connector->AddParameter("?sup_inv_no", $this->SupInvNo);
        $this->connector->AddParameter("?sup_inv_date", is_int($this->SupInvDate) ? $this->FormatSupInvDate(SQL_DATEONLY) : null);
        $this->connector->AddParameter("?jtp_date", is_int($this->JtpDate) ? $this->FormatJtpDate(SQL_DATEONLY) : null);
        $this->connector->AddParameter("?nsf_pajak", $this->NsfPajak);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1){
            $this->RecalculateGrnMaster($id);
        }
        return $rs;
	}

	public function Delete($id,$exPoNo = null) {
        //unpost stock dulu
        $rsx = null;

        $this->connector->CommandText = "SELECT fc_ap_purchasemaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        if ($exPoNo != null){
            #Update PO Status
            $this->connector->CommandText = "Update t_ap_po_master AS a Set a.po_status = 1 Where a.po_no = '".$exPoNo."'";
            $this->connector->ExecuteNonQuery();
        }

        //hapus data grn_
        $this->connector->CommandText = "Delete From t_ap_purchase_master WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id,$exPoNo) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_purchasemaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        if ($exPoNo != null){
            #Update PO Status
            $this->connector->CommandText = "Update t_ap_po_master AS a Set a.po_status = 1 Where a.po_no = '".$exPoNo."'";
            $this->connector->ExecuteNonQuery();
        }
        //mark as void data grn_
        $this->connector->CommandText = "Update t_ap_purchase_master a Set a.grn_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rsz =  $this->connector->ExecuteNonQuery();
        //update so status
        $this->connector->CommandText = "SELECT fc_ap_po_checkstatus_by_grn('".$this->GrnNo."') As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        return $rsz;
    }

    public function GetGrnDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'GN';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->GrnDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }
    
    public function RecalculateGrnMaster($grn_Id){
        $sql = 'Update t_ap_purchase_master a Set a.base_amount = 0, a.ppn_amount = 0, a.pph_amount = 0, a.disc_amount = 0,a.other_costs_amount = 0 Where a.id = ?grn_Id;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?grn_Id", $grn_Id);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ap_purchase_master a
Join (Select c.grn_id, coalesce(sum(c.sub_total),0) As subTotal, coalesce(sum(c.disc_amount),0) as sumDiscount, coalesce(sum(c.ppn_amount),0) as sumPpn, coalesce(sum(c.pph_amount),0) as sumPph, coalesce(sum(c.by_angkut),0) as sumByAngkut From t_ap_purchase_detail c Group By c.grn_id) b
On a.id = b.grn_id Set a.base_amount = b.subTotal, a.disc_amount = b.sumDiscount, a.ppn_amount = b.sumPpn, a.pph_amount = b.sumPph, a.other_costs_amount = b.sumByAngkut Where a.id = ?grn_Id;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?grn_Id", $grn_Id);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ap_purchase_master a Set a.paid_amount = (a.base_amount - a.disc_amount) + a.ppn_amount + a.pph_amount + a.other_costs_amount Where a.id = ?grn_Id And a.payment_type = 0;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?grn_Id", $grn_Id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function Load4Reports($companyId,$cabangId = 0, $supplierId = 0, $grnStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ap_purchase_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.grn_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($grnStatus > -1){
            $sql.= " and a.grn_status = ".$grnStatus;
        }else{
            $sql.= " and a.grn_status <> 3";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > a.paid_amount";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) <= a.paid_amount";
        }
        if ($supplierId > 0){
            $sql.= " and a.supplier_id = ".$supplierId;
        }
        $sql.= " Order By a.grn_date,a.grn_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($companyId,$cabangId = 0, $supplierId = 0, $grnStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,c.item_code,c.item_name as item_descs,b.purchase_qty as qty,b.price,b.disc_formula,b.disc_amount,b.sub_total";
        $sql.= " FROM vw_ap_purchase_master AS a Join t_ap_purchase_detail AS b On a.id = b.grn_id Join m_items AS c On b.item_id = c.id";
        $sql.= " WHERE a.is_deleted = 0 and a.grn_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($grnStatus > -1){
            $sql.= " and a.grn_status = ".$grnStatus;
        }else{
            $sql.= " and a.grn_status <> 3";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > a.paid_amount";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) <= a.paid_amount";
        }
        if ($supplierId > 0){
            $sql.= " and a.supplier_id = ".$supplierId;
        }
        $sql.= " Order By a.grn_date,a.grn_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($companyId,$cabangId = 0, $supplierId = 0, $grnStatus = -1, $paymentStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT c.item_code,c.item_name as item_descs,c.s_uom_code as satuan,coalesce(sum(b.purchase_qty),0) as sum_qty,coalesce(sum(b.sub_total),0) as sum_total, sum(Case When b.ppn_pct > 0 Then Round(b.sub_total * (b.ppn_pct/100),0) Else 0 End) as sum_ppn";
        $sql.= " FROM vw_ap_purchase_master AS a Join t_ap_purchase_detail AS b On a.id = b.grn_id Left Join m_items AS c On b.item_id = c.id";
        $sql.= " WHERE a.is_deleted = 0 and a.grn_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($grnStatus > -1){
            $sql.= " and a.grn_status = ".$grnStatus;
        }else{
            $sql.= " and a.grn_status <> 3";
        }
        if ($paymentStatus == 0){
            $sql.= " and (a.balance_amount) > a.paid_amount";
        }elseif ($paymentStatus == 1){
            $sql.= " and (a.balance_amount) <= a.paid_amount";
        }
        if ($supplierId > 0){
            $sql.= " and a.supplier_id = ".$supplierId;
        }
        $sql.= " Group By c.item_code,c.item_name,c.s_uom_code Order By c.item_name,c.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetJSonGrns($cabangId,$supplierId) {
        $sql = "SELECT a.id,a.grn_no,a.grn_date,a.ppn_pct FROM t_ap_purchase_master as a Where a.grn_status <> 3 And a.is_deleted = 0 And a.cabang_id = ".$cabangId." And a.supplier_id = ".$supplierId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.grn_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetJSonGrnItems($grnId = 0) {
        $sql = "SELECT a.id,a.item_id,b.item_code,b.item_name as item_descs,a.purchase_qty - a.return_qty as qty_beli,b.s_uom_code as satuan,round(a.sub_total/a.purchase_qty,2) as price,a.disc_formula,a.disc_amount,a.ppn_pct,a.pph_pct,c.gudang_id,a.hpp_amount as item_hpp,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil,b.s_uom_qty as bisisatkecil,a.is_free FROM t_ap_purchase_detail AS a";
        $sql.= " JOIN m_items AS b ON a.item_id = b.id Join t_ap_purchase_master c On a.grn_id = c.id Where c.grn_status <> 3 And (a.purchase_qty - coalesce(a.return_qty,0)) > 0 And a.grn_id = ".$grnId;
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

    public function GetGrnItemCount($grnId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ap_purchase_detail as a Where a.grn_id = ?grnId;";
        $this->connector->AddParameter("?grnId", $grnId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Approve($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ap_purchase_approve($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unapprove($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_ap_purchase_unapprove($id,$uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $uid);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    //function post po detail into purchase detail
    public function PostPoDetail2Purchase($id,$grno,$pono){
        $sql = "Update t_ap_purchase_master a Join t_ap_po_master b On a.ex_po_id = b.po_no";
        $sql.= " Set a.payment_type = b.payment_type, a.credit_terms = b.credit_terms, a.ppn_pct = b.ppn_pct, a.ppn_amount = b.ppn_amount, a.disc_pct = b.disc_pct, a.disc_amount = b.disc_amount, a.disc2_pct = b.disc2_pct, a.disc2_amount = b.disc2_amount, a.other_costs = b.other_costs, a.other_costs_amount = b.other_costs_amount";
        $sql.= " Where a.id = $id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        $sql = "Insert Into t_ap_purchase_detail (grn_id,cabang_id,grn_no,item_id,item_code,item_descs,purchase_qty,price,disc_formula,disc_amount,sub_total)";
        $sql.= " Select $id,a.cabang_id,'".$grno."',a.item_id,a.item_code,a.item_descs,a.order_qty,a.price,a.disc_formula,a.disc_amount,a.sub_total From t_ap_po_detail AS a Where a.po_no = '".$pono."' Order By a.id";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs){
            #Post detailnya
            $this->connector->CommandText = "SELECT fc_ap_purchasedetail_all_post($id) As valresult;";
            $this->connector->ExecuteQuery();
            #Update PO Qty received
            $this->connector->CommandText = "Update t_ap_po_detail AS a Set a.receipt_qty = a.order_qty Where a.po_no = '".$pono."'";
            $this->connector->ExecuteQuery();
            #Update PO status
            $sql = "Update t_ap_po_master AS a Set a.po_status = 2 Where a.po_no = '".$pono."'";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();
        }
        return $rs;
    }

    public function GetJSonGrnsByGudang($gudangId,$supplierId) {
        $sql = "SELECT a.id,a.grn_no,a.grn_date,a.base_amount,a.disc_amount,a.ppn_amount,a.pph_amount FROM t_ap_purchase_master as a Where a.grn_status <> 3 And a.is_deleted = 0 And a.gudang_id = ".$gudangId." And a.supplier_id = ".$supplierId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.grn_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetPurchaseSumByYear($tahun){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.grn_date) = 1 THEN a.total_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 2 THEN a.total_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 3 THEN a.total_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 4 THEN a.total_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 5 THEN a.total_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 6 THEN a.total_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 7 THEN a.total_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 8 THEN a.total_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 9 THEN a.total_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 10 THEN a.total_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 11 THEN a.total_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.grn_date) = 12 THEN a.total_amount ELSE 0 END), 0) December
			    FROM vw_ap_purchase_master a Where year(a.grn_date) = $tahun And a.grn_status <> 3 And a.is_deleted = 0";
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

    public function GetPaymentSumByYear($tahun){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.payment_date) = 1 THEN a.payment_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 2 THEN a.payment_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 3 THEN a.payment_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 4 THEN a.payment_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 5 THEN a.payment_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 6 THEN a.payment_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 7 THEN a.payment_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 8 THEN a.payment_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 9 THEN a.payment_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 10 THEN a.payment_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 11 THEN a.payment_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.payment_date) = 12 THEN a.payment_amount ELSE 0 END), 0) December
			    FROM vw_ap_payment_master a Where year(a.payment_date) = $tahun And a.payment_status <> 3 And a.is_deleted = 0";
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
}


// End of File: purchase.php
