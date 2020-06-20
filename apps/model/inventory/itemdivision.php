<?php
class ItemDivision extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $EntityCode;
	public $DivisionCode;
	public $DivisionName;
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
		$this->EntityId = $row["entity_id"];
        $this->EntityCode = $row["entity_code"];
		$this->DivisionCode = $row["division_code"];
		$this->DivisionName = $row["division_name"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.division_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.*,b.entity_code FROM m_item_division AS a JOIN m_item_entity b ON a.entity_id = b.id ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.*,b.entity_code FROM m_item_division AS a JOIN m_item_entity b ON a.entity_id = b.id WHERE a.is_deleted = 0 ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemDivision();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCompanyId($companyId,$orderBy = "a.division_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a.*,b.entity_code FROM m_item_division AS a JOIN m_item_entity b ON a.entity_id = b.id Where b.company_id = $companyId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a.*,b.entity_code FROM m_item_division AS a JOIN m_item_entity b ON a.entity_id = b.id WHERE b.company_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemDivision();
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
		$this->connector->CommandText = "SELECT a.*,b.entity_code FROM m_item_division AS a JOIN m_item_entity b ON a.entity_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function FindByDivision($iDivision) {
		$this->connector->CommandText = "SELECT a.*,b.entity_code FROM m_item_division AS a JOIN m_item_entity b ON a.entity_id = b.id WHERE a.division_code = ?iDivision";
		$this->connector->AddParameter("?iDivision", $iDivision);
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
		$this->connector->CommandText = 'INSERT INTO m_item_division(entity_id,division_code,division_name,createby_id,create_time) VALUES(?entity_id,?division_code,?division_name,?createby_id,now())';
		$this->connector->AddParameter("?entity_id", $this->EntityId);
        $this->connector->AddParameter("?division_code", $this->DivisionCode);
        $this->connector->AddParameter("?division_name", $this->DivisionName);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_item_division SET entity_id = ?entity_id, division_code = ?division_code, division_name = ?division_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?entity_id", $this->EntityId);
		$this->connector->AddParameter("?division_code", $this->DivisionCode);
        $this->connector->AddParameter("?division_name", $this->DivisionName);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_item_division SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_item_division WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
