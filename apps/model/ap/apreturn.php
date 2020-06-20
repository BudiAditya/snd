<?php

require_once("apreturn_detail.php");

class ApReturn extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $RbStatusCodes = array(
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
	public $RbNo;
	public $RbDate;
    public $SupplierId;
    public $SupplierCode;
    public $SupplierName;
    public $SupplierAddress;
	public $RbDescs;
	public $RbAmount = 0;
    public $RbAllocate = 0;
    public $RbStatus = 0;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;

	/** @var ApReturnDetail[] */
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
        $this->RbNo = $row["rb_no"];
        $this->RbDate = strtotime($row["rb_date"]);
        $this->SupplierId = $row["supplier_id"];
        $this->SupplierCode = $row["supplier_code"];
        $this->SupplierName = $row["supplier_name"];
        $this->SupplierAddress = $row["supplier_address"];
        $this->RbDescs = $row["rb_descs"];
        $this->RbAmount = $row["rb_amount"];
        $this->RbAllocate = $row["rb_allocate"];
        $this->RbStatus = $row["rb_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
	}

	public function FormatRbDate($format = HUMAN_DATE) {
		return is_int($this->RbDate) ? date($format, $this->RbDate) : date($format, strtotime(date('Y-m-d')));
	}

	/**
	 * @return ApReturnDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new ApReturnDetail();
		$this->Details = $detail->LoadByRbId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return ApReturn
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ap_return_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_return_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByRbNo($rbNo,$cabangId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ap_return_master AS a WHERE a.rb_no = ?rbNo And a.cabang_id = ?cabangId";
		$this->connector->AddParameter("?rbNo", $rbNo);
        $this->connector->AddParameter("?cabangId", $cabangId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCompanyId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_return_master AS a WHERE a.company_id = ?companyId";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ApReturn();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ap_return_master AS a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ApReturn();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
        $sql = "INSERT INTO t_ap_return_master (gudang_id,cabang_id, rb_no, rb_date, supplier_id, rb_descs, rb_amount, rb_allocate, rb_status, createby_id, create_time)";
        $sql.= "VALUES(?gudang_id,?cabang_id, ?rb_no, ?rb_date, ?supplier_id, ?rb_descs, ?rb_amount, ?rb_allocate, ?rb_status, ?createby_id, now())";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
		$this->connector->AddParameter("?rb_no", $this->RbNo, "char");
		$this->connector->AddParameter("?rb_date", $this->RbDate);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
		$this->connector->AddParameter("?rb_descs", $this->RbDescs);
        $this->connector->AddParameter("?rb_amount", $this->RbAmount);
        $this->connector->AddParameter("?rb_allocate", $this->RbAllocate);
        $this->connector->AddParameter("?rb_status", $this->RbStatus);
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
"UPDATE t_ap_return_master SET
	cabang_id = ?cabang_id
	, gudang_id = ?gudang_id
	, rb_no = ?rb_no
	, rb_date = ?rb_date
	, supplier_id = ?supplier_id
	, rb_descs = ?rb_descs
	, rb_amount = ?rb_amount
	, rb_allocate = ?rb_allocate
	, rb_status = ?rb_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?gudang_id", $this->GudangId);
        $this->connector->AddParameter("?rb_no", $this->RbNo, "char");
        $this->connector->AddParameter("?rb_date", $this->RbDate);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
        $this->connector->AddParameter("?rb_descs", $this->RbDescs);
        $this->connector->AddParameter("?rb_amount", $this->RbAmount);
        $this->connector->AddParameter("?rb_allocate", $this->RbAllocate);
        $this->connector->AddParameter("?rb_status", $this->RbStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}

	public function Delete($id) {
        //fc_ar_rbmaster_unpost
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_returnmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //baru hapus rbnya
        //if ($rsx > 0){
            $this->connector->CommandText = "Delete From t_ap_return_master WHERE id = ?id";
            $this->connector->AddParameter("?id", $id);
            return $this->connector->ExecuteNonQuery();
        //}else{
        //    return $rsx;
        //}
	}

    public function Void($id) {
        //fc_ar_rbmaster_unpost
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_returnmaster_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //baru hapus rbnya
        //if ($rsx > 0){
        $this->connector->CommandText = "Update t_ap_return_master a Set a.rb_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
        //}else{
        //    return $rsx;
        //}
    }

    public function GetApReturnDocNo(){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'RB';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $this->CabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->RbDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    //$reports = $rb->Load4Reports($sCabangId,$sSupplierId,$sSalesId,$sStatus,$sPaymentStatus,$sStartDate,$sEndDate);
    public function Load4Reports($companyId, $cabangId = 0, $supplierId = 0, $kondisi = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ap_return_master AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.rb_status <> 3 and a.rb_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($supplierId > 0){
            $sql.= " and a.supplier_id = ".$supplierId;
        }
        $sql.= " Order By a.rb_date,a.rb_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsDetail($companyId, $cabangId = 0, $supplierId = 0, $kondisi = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT	a.*, c.item_code,d.grn_no as ex_grn_no,c.item_name as item_descs,b.qty_retur,b.price,b.disc_amount,b.ppn_pct,b.kondisi FROM vw_ap_return_master AS a JOIN t_ap_return_detail b ON a.id = b.rb_id JOIN m_items c ON b.item_id = c.id JOIN t_ap_purchase_master d ON b.ex_grn_id = d.id";
        $sql.= " WHERE a.is_deleted = 0 and a.rb_status <> 3 and a.rb_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($kondisi > 0){
            $sql.= " and a.kondisi = ".$kondisi;
        }
        if ($supplierId > 0){
            $sql.= " and a.supplier_id = ".$supplierId;
        }
        $sql.= " Order By a.rb_date,a.rb_no,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4ReportsRekapItem($companyId, $cabangId = 0, $supplierId = 0, $kondisi = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT c.item_code,c.item_name as item_descs,c.s_uom_code as satuan,coalesce(sum(if(b.kondisi = 1,b.qty_retur,0)),0) as qty_bagus,coalesce(sum(if(b.kondisi = 2,b.qty_retur,0)),0) as qty_rusak,coalesce(sum(if(b.kondisi = 3,b.qty_retur,0)),0) as qty_expire,COALESCE (sum(round((round(b.qty_retur * b.price, 0) - b.disc_amount) * 1.1,0)),0) AS sum_total";
        $sql.= " FROM vw_ap_return_master AS a Join t_ap_return_detail AS b On a.id = b.rb_id Left Join m_items AS c On b.item_id = c.id";
        $sql.= " WHERE  a.rb_status <> 3 and a.is_deleted = 0 and a.rb_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($kondisi > 0){
            $sql.= " and b.kondisi = ".$kondisi;
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

    public function GetJSonApReturns($cabangId,$supplierId) {
        $sql = "SELECT a.id,a.rb_no,a.rb_date,a.rb_amount - a.rb_allocate as rb_balance FROM t_ap_return_master as a Where a.rb_amount > a.rb_allocate And a.is_deleted = 0 And a.cabang_id = ".$cabangId." And a.supplier_id = ".$supplierId;
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.rb_no Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function Approve($id = 0){
        $sql = "Update t_ap_return_master a Set a.rb_status = 2 Where a.id = $id And a.rb_status = 1";
        $this->connector->CommandText = $sql;
        if ($this->connector->ExecuteNonQuery()){
            return 1;
        }else {
            return 0;
        }
    }

    public function Unapprove($id = 0){
        $sql = "Update t_ap_return_master a Set a.rb_status = 1 Where a.id = $id And a.rb_status = 2";
        $this->connector->CommandText = $sql;
        if ($this->connector->ExecuteNonQuery()){
            return 1;
        }else {
            return 0;
        }
    }

    public function GetApReturnItemRow($rbId){
        $this->connector->CommandText = "Select count(*) As valresult From t_ap_return_detail as a Where a.rb_id = ?rbId;";
        $this->connector->AddParameter("?rbId", $rbId);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }
}


// End of File: estimasi.php
