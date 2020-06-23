<?php
class SalesArea extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CabangId = 1;
	public $AreaCode;
	public $AreaName;
	public $ZoneId = 0;
	public $PropId = 0;
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
        $this->CabangId = $row["cabang_id"];
		$this->AreaCode = $row["area_code"];
		$this->AreaName = $row["area_name"];
		$this->ZoneId = $row["zone_id"];
        $this->PropId = $row["prop_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.area_code") {
		$this->connector->CommandText = "SELECT a.* FROM m_sales_area AS a Where  a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new SalesArea();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCompanyId($companyId = 0,$orderBy = "a.area_code") {
        $this->connector->CommandText = "SELECT a.* FROM m_sales_area AS a Join m_cabang b ON a.cabang_id = b.id Where b.company_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new SalesArea();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId = 0,$orderBy = "a.area_code") {
        $this->connector->CommandText = "SELECT a.* FROM m_sales_area AS a Where a.cabang_id = $cabangId And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new SalesArea();
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
		$this->connector->CommandText = "SELECT a.* FROM m_sales_area AS a WHERE a.id = ?id And a.is_deleted = 0";
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
		$this->connector->CommandText = "SELECT a.* FROM m_sales_area AS a WHERE a.area_code = ?eCode And a.is_deleted = 0";
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
		$this->connector->CommandText = 'INSERT INTO m_sales_area(cabang_id, area_code, area_name, zone_id, prop_id, createby_id, create_time) VALUES(?cabang_id, ?area_code, ?area_name, ?zone_id, ?prop_id, ?createby_id, now())';
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?area_code", $this->AreaCode, "varchar");
        $this->connector->AddParameter("?area_name", $this->AreaName);
		$this->connector->AddParameter("?zone_id", $this->ZoneId);
        $this->connector->AddParameter("?prop_id", $this->PropId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_sales_area SET cabang_id = ?cabang_id,area_code = ?area_code,area_name = ?area_name,zone_id = ?zone_id,prop_id = ?prop_id,updateby_id = ?updateby_id,update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?area_code", $this->AreaCode,"varchar");
        $this->connector->AddParameter("?area_name", $this->AreaName);
        $this->connector->AddParameter("?zone_id", $this->ZoneId);
        $this->connector->AddParameter("?prop_id", $this->PropId);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'Update m_sales_area a Set a.is_deleted = 1 WHERE a.id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_sales_area WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function getZoneList(){
        $this->connector->CommandText = 'Select a.id,a.code,a.name From m_zone a Order By a.code';
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function getPropList(){
        $this->connector->CommandText = 'Select a.id,a.code as prop_code,a.name as prop_name From m_province a Order By a.code';
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

}
