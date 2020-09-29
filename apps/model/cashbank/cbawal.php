<?php
class CbAwal extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CompanyId;
	public $CabangId;
	public $OpDate;
    public $BankId;
    public $OpAmount;
    public $OpStatus = 1;
    public $CreateById;
    public $CreateTime;
	public $UpdateById;
	public $UpdateTime;

    public function __construct($id = null) {
        parent::__construct();
        if (is_numeric($id)) {
            $this->LoadById($id);
        }
    }

    public function FormatOpDate($format = HUMAN_DATE) {
        return is_int($this->OpDate) ? date($format, $this->OpDate) : date($format, strtotime(date('Y-m-d')));
    }

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
        $this->CompanyId = $row["company_id"];
        $this->CabangId = $row["cabang_id"];
        $this->OpDate = strtotime($row["op_date"]);
        $this->BankId = $row["bank_id"];
        $this->OpAmount = $row["op_amount"];
        $this->OpStatus = $row["op_status"];
        $this->CreateById = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdateById = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
	}

	public function LoadAll($orderBy = "a.trx_no") {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_saldoawal AS a WHERE a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CbAwal();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return TrxType
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_saldoawal AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_saldoawal AS a WHERE a.is_deleted = 0 and a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new CbAwal();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_cb_saldoawal(cabang_id,op_date,bank_id,op_amount,op_status,createby_id,create_time) VALUES(?cabang_id,?op_date,?bank_id,?op_amount,?op_status,?createby_id,NOW())";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?op_date", $this->OpDate);
        $this->connector->AddParameter("?bank_id", $this->BankId);
        $this->connector->AddParameter("?op_amount", $this->OpAmount);
        $this->connector->AddParameter("?op_status", $this->OpStatus);
        $this->connector->AddParameter("?createby_id", $this->CreateById);
        $rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText = "UPDATE t_cb_saldoawal SET cabang_id = ?cabang_id, op_date = ?op_date, bank_id = ?bank_id, op_amount = ?op_amount, op_status = ?op_status, updateby_id = ?updateby_id, update_time = NOW() WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?op_date", date('Y-m-d', $this->OpDate));
        $this->connector->AddParameter("?bank_id", $this->BankId);
        $this->connector->AddParameter("?op_amount", $this->OpAmount);
        $this->connector->AddParameter("?op_status", $this->OpStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdateById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "Delete From t_cb_saldoawal WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        $this->connector->CommandText = "Update t_cb_saldoawal a Set a.is_deleted = 1, a.op_status = 3 WHERE a.id = ?id";
       $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function Approve($id = null){
        $this->connector->CommandText = "SELECT fc_cb_saldoawal_approve(?id,?uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $this->UpdateById);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unapprove($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_cb_saldoawal_unapprove(?id,?uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $this->UpdateById);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function LoadTrx4Approval ($cabId = 0,$stDate, $enDate, $tStatus = 0){
        $sql = "SELECT a.* FROM vw_cb_saldoawal a WHERE a.is_deleted = 0 And (a.op_date BETWEEN ?stDate And ?enDate)";
        if ($cabId > 0){
            $sql.= " And a.cabang_id = ".$cabId;
        }
        if ($tStatus > -1){
            $sql.= " And a.op_status = ".$tStatus;
        }
        $sql.= " ORDER BY a.op_date,a.trx_no";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?stDate", date('Y-m-d', $stDate));
        $this->connector->AddParameter("?enDate", date('Y-m-d', $enDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}

// End of file: bank.php
