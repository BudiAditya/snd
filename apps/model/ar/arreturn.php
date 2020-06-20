<?php

require_once("arreturn_detail.php");

class ArReturn extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $RjStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
        2 => "APPROVED",
		3 => "VOID"
	);

    public static $CollectStatusCodes = array(
        0 => "ON HOLD",
        1 => "ON PROCESS",
        2 => "PAID",
        3 => "VOID"
    );

	public $Id;
    public $IsDeleted = false;
    public $CompanyId;
    public $AreaId;
    public $CompanyCode;
    public $CompanyName;
    public $CabangId = 0;
    public $GudangId = 0;
    public $CabangCode;
    public $GudangCode;
	public $RjNo;
	public $RjDate;
    public $CustomerId;
    public $CustomerCode;
    public $CustomerName;
    public $CustomerAddress;
	public $RjDescs;
	public $RjAmount = 0;
    public $RjAllocate = 0;
    public $RjStatus = 0;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;

	/** @var ArReturnDetail[] */
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
        $this->AreaId = $row["area_id"];
        $this->CompanyName = $row["company_name"];
        $this->CabangId = $row["cabang_id"];
        $this->GudangId = $row["gudang_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->GudangCode = $row["wh_code"];
        $this->RjNo = $row["rj_no"];
        $this->RjDate = strtotime($row["rj_date"]);
        $this->CustomerId = $row["customer_id"];
        $this->CustomerCode = $row["customer_code"];
        $this->CustomerName = $row["customer_name"];
        $this->CustomerAddress = $row["customer_address"];
        $this->RjDescs = $row["rj_descs"];
        $this->RjAmount = $row["rj_amount"];
        $this->RjAllocate = $row["rj_allocate"];
        $this->RjStatus = $row["rj_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
	}

	public function FormatRjDate($format = HUMAN_DATE) {
		return is_int($this->RjDate) ? date($format, $this->RjDate) : date($format, strtotime(date('Y-m-d')));
	}

	/**
	 * @return ArReturnDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new ArReturnDetail();
		$this->Details = $detail->LoadByRjId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return ArReturn
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ar_return_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_return_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByRjNo($rjNo,$cabangId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ar_return_master AS a WHERE a.rj_no = ?rjNo And a.cabang_id = ?cabangId";
		$this->connector->AddParameter("?rjNo", $rjNo);
        $this->connector->AddParameter("?cabangId", $cabangId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCompanyId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_return_master AS a WHERE a.company_id = ?companyId";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ArReturn();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_return_master AS a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ArReturn();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ar_return_master (gudang_id,cabang_id, rj_no, rj_date, customer_id, rj_descs, rj_amount, rj_allocate, rj_status, createby_id, create_time)";
        $sql.= "VALUES(?gudang_id,?cabang_id, ?rj_no, ?rj_date, ?customer_id, ?rj_descs, ?rj_amount, ?rj_allocate, ?rj_status, ?createby_id, now())";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
		$this->connector->AddParameter("?rj_no", $this->RjNo, "char");
		$this->connector->AddParameter("?rj_date", $this->RjDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
		$this->connector->AddParameter("?rj_descs", $this->RjDescs);
        $this->connector->AddParameter("?rj_amount", $this->RjAmount);
        $this->connector->AddParameter("?rj_allocate", $this->RjAllocate);
        $this->connector->AddParameter("?rj_status", $this->RjStatus);
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
"UPDATE t_ar_return_master SET
	cabang_id = ?cabang_id
	, gudang_id = ?gudang_id
	, rj_no = ?rj_no
	, rj_date = ?rj_date
	, customer_id = ?customer_id
	, rj_descs = ?rj_descs
	, rj_amount = ?rj_amount
	, rj_allocate = ?rj_allocate
	, rj_status = ?rj_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
        $this->connector->AddParameter("?rj_no", $this->RjNo, "char");
        $this->connector->AddParameter("?rj_date", $this->RjDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?rj_descs", $this->RjDescs);
        $this->connector->AddParameter("?rj_amount", $this->RjAmount);
        $this->connector->AddParameter("?rj_allocate", $this->RjAllocate);
        $this->connector->AddParameter("?rj_status", $this->RjStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}

	public function Delete($id) {
        //fc_ar_rjmaster_unpost
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ar_returnmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //baru hapus rjnya
        //if ($rsx > 0){
            $this->connector->CommandText = "Delete From t_ar_return_master WHERE id = ?id";
            $this->connector->AddParameter("?id", $id);
            return $this->connector->ExecuteNonQuery();
        //}else{
        //    return $rsx;
        //}
	}

    public function Void($id) {
        //fc_ar_rjmaster_unpost
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ar_returnmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //baru hapus rjnya
        //if ($rsx > 0){
        $this->connector->CommandText = "Update t_ar_return_master a Set a.rj_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
        //}else{
        //    return $rsx;
        //}
    }

    public function GetArReturnDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'RJ';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->RjDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    //$reports = $rj->Load4Reports($sCabangId,$sCustomerId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate);
    public function Load4Reports($companyId, $cabangId = 0, $customerId = 0, $kondisi = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ar_return_master AS a WHERE a.is_deleted = 0 and a.rj_status <> 3 and a.rj_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Order By a.rj_date,a.rj_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($companyId, $cabangId = 0, $customerId = 0, $kondisi = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT	a.*, c.item_code,d.invoice_no as ex_invoice_no,c.item_name as item_descs,b.qty_retur,b.price,b.disc_amount,b.ppn_pct,b.kondisi FROM vw_ar_return_master AS a JOIN t_ar_return_detail b ON a.id = b.rj_id Join m_items c ON b.item_id = c.id Join t_ar_invoice_master d On b.ex_invoice_id = d.id";
        $sql.= " WHERE a.is_deleted = 0 and a.rj_status <> 3 and a.rj_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($kondisi > 0){
            $sql.= " and a.kondisi = ".$kondisi;
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Order By a.rj_date,a.rj_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($companyId, $cabangId = 0, $customerId = 0, $kondisi = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT c.item_code,c.item_name as item_descs,c.s_uom_code as satuan,coalesce(sum(if(b.kondisi = 1,b.qty_retur,0)),0) as qty_bagus,coalesce(sum(if(b.kondisi = 2,b.qty_retur,0)),0) as qty_rusak,coalesce(sum(if(b.kondisi = 3,b.qty_retur,0)),0) as qty_expire,COALESCE (sum(round((round(b.qty_retur * b.price, 0) - b.disc_amount) * 1.1,0)),0) AS sum_total";
        $sql.= " FROM vw_ar_return_master AS a Join t_ar_return_detail AS b On a.id = b.rj_id Left Join m_items AS c On b.item_id = c.id";
        $sql.= " WHERE  a.rj_status <> 3 and a.is_deleted = 0 and a.rj_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($kondisi > 0){
            $sql.= " and b.kondisi = ".$kondisi;
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        $sql.= " Group By c.item_code,c.item_name,c.s_uom_code Order By c.item_name,c.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetJSonArReturns($cabangId,$customerId) {
        $sql = "SELECT a.id,a.rj_no,a.rj_date,a.rj_amount - a.rj_allocate as rj_balance FROM t_ar_return_master as a Where a.rj_amount > a.rj_allocate And a.is_deleted = 0 And a.cabang_id = ".$cabangId." And a.customer_id = ".$customerId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.rj_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function Approve($id = 0,$uid){
        $this->connector->CommandText = "SELECT fc_ar_return_approve($id,$uid) As valresult;";
        return $this->connector->ExecuteQuery();
    }

    public function Unapprove($id = 0,$uid){
        $this->connector->CommandText = "SELECT fc_ar_return_unapprove($id,$uid) As valresult;";
        return $this->connector->ExecuteQuery();
    }

    public function GetArReturnItemRow($rjId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ar_return_detail as a Where a.rj_id = ?rjId;";
        $this->connector->AddParameter("?rjId", $rjId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }
}


// End of File: estimasi.php
