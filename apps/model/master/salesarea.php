<?php
class SalesArea extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CompanyId = 1;
	public $AreaCode;
	public $AreaName;
	public $ZoneId = 0;
	public $CityId = 0;
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
		$this->AreaCode = $row["area_code"];
		$this->AreaName = $row["area_name"];
		$this->ZoneId = $row["zone_id"];
        $this->CityId = $row["city_id"];
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
        $this->connector->CommandText = "SELECT a.* FROM m_sales_area AS a Where a.company_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
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
		$this->connector->CommandText = 'INSERT INTO m_sales_area(company_id, area_code, area_name, zone_id, city_id, createby_id, create_time) VALUES(?company_id, ?area_code, ?area_name, ?zone_id, ?city_id, ?createby_id, now())';
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?area_code", $this->AreaCode, "varchar");
        $this->connector->AddParameter("?area_name", $this->AreaName);
		$this->connector->AddParameter("?zone_id", $this->ZoneId);
        $this->connector->AddParameter("?city_id", $this->CityId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_sales_area SET company_id = ?company_id,area_code = ?area_code,area_name = ?area_name,zone_id = ?zone_id,city_id = ?city_id,updateby_id = ?updateby_id,update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?area_code", $this->AreaCode,"varchar");
        $this->connector->AddParameter("?area_name", $this->AreaName);
        $this->connector->AddParameter("?zone_id", $this->ZoneId);
        $this->connector->AddParameter("?city_id", $this->CityId);
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

    public function getCityList(){
        $this->connector->CommandText = 'Select a.id,a.code as city_code,a.name as city_name,b.name as prop_name From m_city a Join m_province b ON a.province_id = b.id Order By a.name';
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

}
