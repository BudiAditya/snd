<?php
class ItemSubCategory extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $DivisionId = 0;
	public $CategoryId = 0;
	public $CategoryCode;
	public $CategoryName;
    public $SubCategoryCode;
    public $SubCategoryName;
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
        $this->CategoryId = $row["category_id"];
		$this->CategoryCode = $row["category_code"];
		$this->CategoryName = $row["category_name"];
        $this->SubCategoryCode = $row["subcategory_code"];
        $this->SubCategoryName = $row["subcategory_name"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a1.subcategory_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id WHERE a1.is_deleted = 0 ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemSubCategory();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByDivisionId($divisionId = 0,$orderBy = "a1.subcategory_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id Where a.division_id = $divisionId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id WHERE a1.is_deleted = 0 And a.division_id = $divisionId ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemSubCategory();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByEntityId($entityId = 0,$orderBy = "a1.subcategory_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id JOIN m_item_division b ON a.division_id = b.id Where b.entity_id = $entityId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id JOIN m_item_division b ON a.division_id = b.id Where b.entity_id = $entityId And a1.is_deleted = 0 ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemSubCategory();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCompanyId($companyId = 0,$orderBy = "a1.subcategory_code", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id JOIN m_item_division b ON a.division_id = b.id JOIN m_item_entity c ON b.entity_id = c.id Where c.company_id = $companyId ORDER BY $orderBy";
        } else {
            $this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id JOIN m_item_division b ON a.division_id = b.id JOIN m_item_entity c ON b.entity_id = c.id Where c.company_id = $companyId And a1.is_deleted = 0 ORDER BY $orderBy";
        }
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemSubCategory();
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
		$this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id WHERE a1.id = ?id";
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
		$this->connector->CommandText = "SELECT a1.*,a.category_code,a.category_name,a.division_id FROM m_item_subcategory AS a1 JOIN m_item_category AS a ON a1.category_id = a.id WHERE a1.subcategory_code = ?iCode";
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
		$this->connector->CommandText = 'INSERT INTO m_item_subcategory(category_id,subcategory_code,subcategory_name,createby_id,create_time) VALUES(?category_id,?category_code,?category_name,?createby_id,now())';
		$this->connector->AddParameter("?category_id", $this->CategoryId);
        $this->connector->AddParameter("?category_code", $this->SubCategoryCode);
        $this->connector->AddParameter("?category_name", $this->SubCategoryName);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_item_subcategory SET category_id = ?category_id, subcategory_code = ?category_code, subcategory_name = ?category_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?category_id", $this->CategoryId);
        $this->connector->AddParameter("?category_code", $this->SubCategoryCode);
        $this->connector->AddParameter("?category_name", $this->SubCategoryName);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_item_subcategory SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_item_subcategory WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
