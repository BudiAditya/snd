<?php
class ItemPrincipal extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CompanyId;
	public $CompanyCode;
	public $PrincipalCode;
	public $PrincipalName;
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
        $this->CompanyCode = $row["company_code"];
		$this->PrincipalCode = $row["principal_code"];
		$this->PrincipalName = $row["principal_name"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.principal_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.*,b.company_code FROM m_item_principal AS a JOIN sys_company b ON a.company_id = b.id ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.*,b.company_code FROM m_item_principal AS a JOIN sys_company b ON a.company_id = b.id WHERE a.is_deleted = 0 ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemPrincipal();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCompanyId($companyId,$orderBy = "a.principal_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a.*,b.company_code FROM m_item_principal AS a JOIN sys_company b ON a.company_id = b.id Where a.company_id = $companyId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a.*,b.company_code FROM m_item_principal AS a JOIN sys_company b ON a.company_id = b.id WHERE a.company_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemPrincipal();
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
		$this->connector->CommandText = "SELECT a.*,b.company_code FROM m_item_principal AS a JOIN sys_company b ON a.company_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function FindByPrincipal($iPrincipal) {
		$this->connector->CommandText = "SELECT a.*,b.company_code FROM m_item_principal AS a JOIN sys_company b ON a.company_id = b.id WHERE a.principal_code = ?iPrincipal";
		$this->connector->AddParameter("?iPrincipal", $iPrincipal);
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
		$this->connector->CommandText = 'INSERT INTO m_item_principal(company_id,principal_code,principal_name,createby_id,create_time) VALUES(?company_id,?principal_code,?principal_name,?createby_id,now())';
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?principal_code", $this->PrincipalCode);
        $this->connector->AddParameter("?principal_name", $this->PrincipalName);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_item_principal SET company_id = ?company_id, principal_code = ?principal_code, principal_name = ?principal_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?company_id", $this->CompanyId);
		$this->connector->AddParameter("?principal_code", $this->PrincipalCode);
        $this->connector->AddParameter("?principal_name", $this->PrincipalName);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_item_principal SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_item_principal WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
