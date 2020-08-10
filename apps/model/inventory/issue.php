<?php
class Issue extends EntityBase {
	public $Id;
	public $CabangId;
	public $WarehouseId;
    public $IssueDate;
    public $IssueNo;
    public $DebetAccId = 0;
    public $Keterangan;
	public $ItemId;
    public $ItemCode;
    public $ItemUom;
    public $Qty = 0;
    public $Price = 0;
    public $IsStatus;
    public $CreatebyId;

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
        $this->IssueDate = strtotime($row["issue_date"]);
        $this->IssueNo = $row["issue_no"];
        $this->DebetAccId = $row["debet_acc_id"];
        $this->Keterangan = $row["keterangan"];
		$this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
        $this->ItemUom = $row["satuan"];
        $this->Qty = $row["qty"];
        $this->Price = $row["price"];
        $this->IsStatus = $row["is_status"];
        $this->CreatebyId = $row["createby_id"];
	}

    public function FormatIssueDate($format = HUMAN_DATE) {
        return is_int($this->IssueDate) ? date($format, $this->IssueDate) : date($format, strtotime(date('Y-m-d')));
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($cabangId = 0,$orderBy = "a.warehouse_id, a.item_code") {
        if ($cabangId == 0){
            $this->connector->CommandText = "SELECT a.* FROM vw_ic_issue AS a ORDER BY $orderBy";
        }else{
            $this->connector->CommandText = "SELECT a.* FROM vw_ic_issue AS a Where a.cabang_id = $cabangId ORDER BY $orderBy";
        }
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Issue();
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
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_issue AS a WHERE a.id = ?id";
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
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_issue AS a WHERE a.cabang_id = ?cabangId And a.item_code = ?itemCode";
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
        $sql = "INSERT INTO t_ic_issue (warehouse_id, issue_no, issue_date, item_id, keterangan, price, debet_acc_id, qty, is_status, createby_id, create_time)";
        $sql.= " VALUES(?warehouse_id, ?issue_no, ?issue_date, ?item_id, ?keterangan, ?price, ?debet_acc_id, ?qty, ?is_status, ?createby_id,now())";
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
        $this->connector->AddParameter("?issue_no", $this->IssueNo,"char");
        $this->connector->AddParameter("?issue_date", date('Y-m-d', $this->IssueDate));
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?keterangan", $this->Keterangan);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?debet_acc_id", $this->DebetAccId);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?is_status", $this->IsStatus);
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
		$this->connector->CommandText = 'Delete From t_ic_issue Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function UpdatePrice() {
        $this->connector->CommandText = 'Update t_ic_issue a Set a.price = ?price, a.is_status = 1 Where id = ?id';
        $this->connector->AddParameter("?id", $this->Id);
        $this->connector->AddParameter("?price", $this->Price);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetIssueDocNo($cabangId){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'IS';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $cabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", date('Y-m-d', $this->IssueDate));
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function Load4Reports($cabangId = 0,$gudangId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ic_issue AS a WHERE a.is_status <> 3 and a.issue_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($gudangId > 0){
            $sql.= " and a.warehouse_id = ".$gudangId;
        }
        $sql.= " Order By a.issue_date,a.issue_no,a.item_code,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadRekap4Reports($cabangId = 0,$gudangId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.cabang_code,a.wh_code,a.item_code,a.bnama,a.bsatkecil,sum(a.qty) as qty FROM vw_ic_issue AS a";
        $sql.= " WHERE a.is_status <> 3 and a.issue_date BETWEEN ?startdate and ?enddate";
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
}
