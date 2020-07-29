<?php
class CusType extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CompanyId = 1;
	public $TypeCode;
	public $TypeName;
	public $TrxId = 0;
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
        $this->IsDeleted = $row["is_deleted"];
        $this->CompanyId = $row["company_id"];
		$this->TypeCode = $row["type_code"];
		$this->TypeName = $row["type_name"];
		$this->TrxId = $row["trx_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.type_code") {
		$this->connector->CommandText = "SELECT a.* FROM m_customer_type AS a Where  a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CusType();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCompanyId($companyId = 0,$orderBy = "a.type_code") {
        $this->connector->CommandText = "SELECT a.* FROM m_customer_type AS a Where a.company_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new CusType();
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
		$this->connector->CommandText = "SELECT a.* FROM m_customer_type AS a WHERE a.id = ?id And a.is_deleted = 0";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function FindByCode($eCode) {
		$this->connector->CommandText = "SELECT a.* FROM m_customer_type AS a WHERE a.type_code = ?eCode And a.is_deleted = 0";
		$this->connector->AddParameter("?eCode", $eCode);
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
		$this->connector->CommandText = 'INSERT INTO m_customer_type(company_id, type_code, type_name, trx_id, createby_id, create_time) VALUES(?company_id, ?type_code, ?type_name, ?trx_id, ?createby_id, now())';
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?type_code", $this->TypeCode, "varchar");
        $this->connector->AddParameter("?type_name", $this->TypeName);
		$this->connector->AddParameter("?trx_id", $this->TrxId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_customer_type SET company_id = ?company_id,type_code = ?type_code,type_name = ?type_name,trx_id = ?trx_id,updateby_id = ?updateby_id,update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?type_code", $this->TypeCode,"varchar");
        $this->connector->AddParameter("?type_name", $this->TypeName);
        $this->connector->AddParameter("?trx_id", $this->TrxId);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'Update m_customer_type a Set a.is_deleted = 1 WHERE a.id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_customer_type WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
