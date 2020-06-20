<?php
class SaldoAwal extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CompanyId;
	public $CustomerId;
	public $OpDate;
	public $OpNo;
	public $OpAmount = 0;
    public $PaidAmount = 0;
    public $OpStatus = 0;
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
        $this->IsDeleted = $row["is_deleted"] == 1;
		$this->CompanyId = $row["company_id"];
		$this->CustomerId = $row["customer_id"];
		$this->OpDate = $row["op_date"];
        $this->OpNo = $row["op_no"];
        $this->OpAmount = $row["op_amount"];
        $this->PaidAmount = $row["paid_amount"];
        $this->OpStatus = $row["op_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.op_no", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM t_ar_saldoawal AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new SaldoAwal();
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
		$this->connector->CommandText = "SELECT a.* FROM t_ar_saldoawal AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
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
		$this->connector->CommandText = 'INSERT INTO t_ar_saldoawal(company_id,customer_id,op_date,op_no,op_amount,op_status,createby_id,create_time) VALUES(?company_id,?customer_id,?op_date,?op_no,?op_amount,?op_status,?createby_id,now())';
		$this->connector->AddParameter("?company_id", $this->CompanyId,"varchar");
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?op_date", $this->OpDate);
        $this->connector->AddParameter("?op_no", $this->OpNo);
        $this->connector->AddParameter("?op_amount", $this->OpAmount);
        $this->connector->AddParameter("?op_status", $this->OpStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
            $rs = $this->Id;
        }
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE t_ar_saldoawal SET customer_id = ?customer_id, op_date = ?op_date, op_amount = ?op_amount, op_status = ?op_status, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?op_date", $this->OpDate);
        $this->connector->AddParameter("?op_amount", $this->OpAmount);
        $this->connector->AddParameter("?op_status", $this->OpStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE t_ar_saldoawal SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From t_ar_saldoawal Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetOpNo(){
        $sql = 'Select fc_sys_getdocno(?cpi,?txc,?txd) As valout;';
        $txc = 'OP';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cpi", $this->CompanyId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->OpDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }
}
