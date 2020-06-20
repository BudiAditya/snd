<?php
class ItemCategory extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $DivisionId;
	public $DivisionCode;
	public $DivisionName;
	public $CategoryCode;
	public $CategoryName;
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
		$this->DivisionId = $row["division_id"];
        $this->DivisionCode = $row["division_code"];
        $this->DivisionName = $row["division_name"];
		$this->CategoryCode = $row["category_code"];
		$this->CategoryName = $row["category_name"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.category_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id WHERE a.is_deleted = 0 ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemCategory();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByDivisionId($divisionId = 0,$orderBy = "a.category_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id Where a.division_id = $divisionId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id WHERE a.is_deleted = 0 And a.division_id = $divisionId ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemCategory();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByEntityId($entityId = 0,$orderBy = "a.category_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name  FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id Where b.entity_id = $entityId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name  FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id WHERE a.is_deleted = 0 And b.entity_id = $entityId ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemCategory();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCompanyId($companyId = 0,$orderBy = "a.category_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name  FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id JOIN m_item_entity c ON b.entity_id = c.id Where c.company_id = $companyId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name  FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id JOIN m_item_entity c ON b.entity_id = c.id WHERE a.is_deleted = 0 And c.company_id = $companyId ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemCategory();
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
		$this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function FindByCode($iCode) {
		$this->connector->CommandText = "SELECT a.*,b.division_code,b.division_name FROM m_item_category AS a JOIN m_item_division b ON a.division_id = b.id WHERE a.category_code = ?iCode";
		$this->connector->AddParameter("?iCode", $iCode);
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
		$this->connector->CommandText = 'INSERT INTO m_item_category(division_id,category_code,category_name,createby_id,create_time) VALUES(?division_id,?category_code,?category_name,?createby_id,now())';
		$this->connector->AddParameter("?division_id", $this->DivisionId);
        $this->connector->AddParameter("?category_code", $this->CategoryCode);
        $this->connector->AddParameter("?category_name", $this->CategoryName);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_item_category SET division_id = ?division_id, category_code = ?category_code, category_name = ?category_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?division_id", $this->DivisionId);
		$this->connector->AddParameter("?category_code", $this->CategoryCode);
        $this->connector->AddParameter("?category_name", $this->CategoryName);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_item_category SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_item_category WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
