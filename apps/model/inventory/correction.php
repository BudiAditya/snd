<?php
class Correction extends EntityBase {
	public $Id;
	public $CabangId;
	public $WarehouseId;
    public $CorrNo;
	public $ItemId;
    public $ItemCode;
    public $ItemName;
    public $ItemUom;
    public $CorrDate;   
    public $CorrQty = 0;
    public $CorrReason;
    public $CorrStatus;
    public $SysQty = 0;
    public $WhsQty = 0;
    public $CorrAmt = 0;
    public $CreatebyId;
    public $UpdatebyId;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->CabangId = $row["cabang_id"];
        $this->WarehouseId = $row["warehouse_id"];
        $this->CorrNo = $row["corr_no"];
        $this->CorrDate = strtotime($row["corr_date"]);
		$this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
        $this->ItemName = $row["item_name"];
        $this->ItemUom = $row["satuan"];
        $this->CorrReason = $row["corr_reason"];
        $this->SysQty = $row["sys_qty"];
        $this->WhsQty = $row["whs_qty"];
        $this->CorrQty = $row["corr_qty"];
        $this->CorrAmt = $row["corr_amt"];
        $this->CorrStatus = $row["corr_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

    public function FormatCorrDate($format = HUMAN_DATE) {
        return is_int($this->CorrDate) ? date($format, $this->CorrDate) : date($format, strtotime(date('Y-m-d')));
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($cabangId = 0,$orderBy = "a.warehouse_id, a.item_code") {
        if ($cabangId == 0){
            $this->connector->CommandText = "SELECT a.* FROM vw_ic_stock_correction AS a ORDER BY $orderBy";
        }else{
            $this->connector->CommandText = "SELECT a.* FROM vw_ic_stock_correction AS a Where a.cabang_id = $cabangId ORDER BY $orderBy";
        }
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Correction();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return Location
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_stock_correction AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByKode($cabangId,$itemCode) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_stock_correction AS a WHERE a.cabang_id = ?cabangId And a.item_code = ?itemCode";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?itemCode", $itemCode);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

	/**
	 * @param int $id
	 * @return Location
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	public function Insert() {
        $sql = 'INSERT INTO t_ic_stock_correction (warehouse_id, corr_no, corr_date, item_id, corr_reason, sys_qty, whs_qty, corr_qty, corr_status, corr_amt, createby_id, create_time) ';
        $sql.= ' VALUES(?warehouse_id, ?corr_no, ?corr_date, ?item_id, ?corr_reason, ?sys_qty, ?whs_qty, ?corr_qty, ?corr_status, ?corr_amt, ?createby_id,now())';
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
        $this->connector->AddParameter("?corr_no", $this->CorrNo,"char");
        $this->connector->AddParameter("?corr_date", $this->CorrDate);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?corr_reason", $this->CorrReason);
        $this->connector->AddParameter("?sys_qty", $this->SysQty);
        $this->connector->AddParameter("?whs_qty", $this->WhsQty);
        $this->connector->AddParameter("?corr_qty", $this->CorrQty);
        $this->connector->AddParameter("?corr_amt", $this->CorrAmt);
        $this->connector->AddParameter("?corr_status", $this->CorrStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        $ret = 0;
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
            $ret = $this->Id;
        }
		return $ret;
	}

	public function Delete($id) {
        //$this->connector->CommandText = "SELECT fc_ic_correction_unpost($id) As valresult;";
        //$rs = $this->connector->ExecuteQuery();
        //$row = $rs->FetchAssoc();
		$this->connector->CommandText = 'Delete From t_ic_stock_correction Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function Void($id) {
        $this->connector->CommandText = "SELECT fc_ic_correction_unpost($id) As valresult;";
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $this->connector->CommandText = 'Update t_ic_stock_correction a Set a.corr_status = 3 Where a.id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetCorrectionDocNo($cabangId){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'CR';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $cabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->CorrDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function Load4Reports($cabangId = 0,$gudangId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ic_stockcorrection AS a WHERE a.corr_status <> 3 and a.corr_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($gudangId > 0){
            $sql.= " and a.warehouse_id = ".$gudangId;
        }
        $sql.= " Order By a.corr_date,a.corr_no,a.item_code,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadRekap4Reports($cabangId = 0,$gudangId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.cabang_code,a.wh_code,a.item_code,a.bnama,a.bsatkecil,sum(a.corr_qty) as qty FROM vw_ic_stockcorrection AS a";
        $sql.= " WHERE a.corr_status <> 3 and a.corr_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($gudangId > 0){
            $sql.= " and a.warehouse_id = ".$gudangId;
        }
        $sql.= " Group By a.cabang_code,a.wh_code,a.item_code,a.bnama,a.bsatkecil";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function UpdateAmount() {
        $this->connector->CommandText = 'Update t_ic_stock_correction a Set a.corr_amt = ?price, a.corr_status = 1 Where id = ?id';
        $this->connector->AddParameter("?id", $this->Id);
        $this->connector->AddParameter("?price", $this->CorrAmt);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }
}
