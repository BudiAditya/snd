<?php
class Zone extends EntityBase {
	public $Id;
	public $ZoneCode;
	public $ZoneName;
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
		$this->ZoneCode = $row["zone_code"];
		$this->ZoneName = $row["zone_name"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.zone_code") {
		$this->connector->CommandText = "SELECT a.* FROM m_zone AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Zone();
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
		$this->connector->CommandText = "SELECT a.* FROM m_zone AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByZoneCode($code) {
        $this->connector->CommandText = "SELECT a.* FROM m_zone AS a WHERE a.zone_code = ?code";
        $this->connector->AddParameter("?code", $code);
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
		$this->connector->CommandText = 'INSERT INTO m_zone(`code`,`name`,createby_id,create_time) VALUES(?zone_code,?zone_name,?createby_id,now())';
		$this->connector->AddParameter("?zone_code", $this->ZoneCode);
        $this->connector->AddParameter("?zone_name", $this->ZoneName);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_zone SET `code` = ?zone_code, `name` = ?zone_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?zone_code", $this->ZoneCode);
        $this->connector->AddParameter("?zone_name", $this->ZoneName);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From m_zone WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
	}

}
