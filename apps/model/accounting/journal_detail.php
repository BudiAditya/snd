<?php

class JournalDetail extends EntityBase {
	public $Id;
	public $JournalId;
    public $AccId = 0;
    public $AccCode;
    public $AccName;
    public $Keterangan;
    public $DbAmount = 0;
    public $CrAmount = 0;
    public $CabangId = 0;
    public $DeptId = 0;
    public $CustomerId = 0;
    public $SupplierId = 0;
    public $EmployeeId = 0;
    public $CabangCode;
    public $DeptName;
    public $CustName;
    public $SuppName;
    public $EmployeeName;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
        $this->JournalId = $row["journal_id"];
		$this->AccId = $row["acc_id"];
        $this->AccCode = $row["acc_code"];
        $this->AccName = $row["acc_name"];
        $this->DbAmount = $row["db_amount"];
        $this->CrAmount = $row["cr_amount"];
        $this->Keterangan = $row["keterangan"];;
        $this->DeptId = $row["dept_id"];
        $this->CabangId = $row["cabang_id"];
        $this->CustomerId = $row["customer_id"];
        $this->SupplierId = $row["supplier_id"];
        $this->EmployeeId = $row["employee_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->DeptName = $row["dept_name"];
        $this->CustName = $row["cus_name"];
        $this->SuppName = $row["sup_name"];
        $this->EmployeeName = $row["nama_karyawan"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_detail AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_detail AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByJournalId($journalId, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_detail AS a WHERE a.journal_id = ?journalId ORDER BY $orderBy";
        $this->connector->AddParameter("?journalId", $journalId);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {

            while ($row = $rs->FetchAssoc()) {
                $temp = new JournalDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ac_journal_detail(journal_id, acc_id, keterangan, db_amount, cr_amount, cabang_id, dept_id, customer_id, supplier_id, employee_id) VALUES(?journal_id, ?acc_id, ?keterangan, ?db_amount, ?cr_amount, ?cabang_id, ?dept_id, ?customer_id, ?supplier_id, ?employee_id)";
		$this->connector->AddParameter("?journal_id", $this->JournalId);
		$this->connector->AddParameter("?acc_id", $this->AccId);
        $this->connector->AddParameter("?keterangan", $this->Keterangan);
		$this->connector->AddParameter("?db_amount", $this->DbAmount == null ? 0 : $this->DbAmount);
        $this->connector->AddParameter("?cr_amount", $this->CrAmount == null ? 0 : $this->CrAmount);
        $this->connector->AddParameter("?cabang_id", $this->CabangId == null ? 0 : $this->CabangId);
        $this->connector->AddParameter("?dept_id", $this->DeptId == null ? 0 : $this->DeptId);
        $this->connector->AddParameter("?customer_id", $this->CustomerId == null ? 0 : $this->CustomerId);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId == null ? 0 : $this->SupplierId);
        $this->connector->AddParameter("?employee_id", $this->EmployeeId == null ? 0 : $this->EmployeeId);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update() {
		$this->connector->CommandText =  "UPDATE t_ac_journal_detail SET journal_id = ?journal_id, acc_id = ?acc_id, db_amount = ?db_amount, cr_amount = ?cr_amount, customer_id = ?customer_id, supplier_id = ?supplier_id, dept_id = ?dept_id, cabang_id = ?cabang_id, keterangan = ?keterangan, employee_id = ?employee_id WHERE id = ?id";
        $this->connector->AddParameter("?journal_id", $this->JournalId);
        $this->connector->AddParameter("?acc_id", $this->AccId);
        $this->connector->AddParameter("?keterangan", $this->Keterangan);
        $this->connector->AddParameter("?db_amount", $this->DbAmount == null ? 0 : $this->DbAmount);
        $this->connector->AddParameter("?cr_amount", $this->CrAmount == null ? 0 : $this->CrAmount);
        $this->connector->AddParameter("?cabang_id", $this->CabangId == null ? 0 : $this->CabangId);
        $this->connector->AddParameter("?dept_id", $this->DeptId == null ? 0 : $this->DeptId);
        $this->connector->AddParameter("?customer_id", $this->CustomerId == null ? 0 : $this->CustomerId);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId == null ? 0 : $this->SupplierId);
        $this->connector->AddParameter("?employee_id", $this->EmployeeId == null ? 0 : $this->EmployeeId);
		$this->connector->AddParameter("?id", $this->Id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}

	public function Delete($id) {
		$this->connector->CommandText = "DELETE FROM t_ac_journal_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}
}
// End of File: estimasi_detail.php
