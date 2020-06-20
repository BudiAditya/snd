<?php
class Libur extends EntityBase {
	public $Id;
	public $TglLibur;
	public $JnsLibur = 0;
	public $Keterangan;
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
		$this->TglLibur = strtotime($row["tgl_libur"]);
		$this->JnsLibur = $row["jns_libur"];
        $this->Keterangan = $row["keterangan"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

    public function FormatTglLibur($format = JS_DATE) {
        return is_int($this->TglLibur) ? date($format, $this->TglLibur) : date($format, strtotime(date('Y-m-d')));
    }

	public function LoadAll($orderBy = "a.tgl_libur") {
		$this->connector->CommandText = "SELECT a.* FROM m_libur AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Libur();
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
		$this->connector->CommandText = "SELECT a.* FROM m_libur AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByTglLibur($code) {
        $this->connector->CommandText = "SELECT a.* FROM m_libur AS a WHERE a.tgl_libur = ?code";
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
		$this->connector->CommandText = 'INSERT INTO m_libur(tgl_libur,jns_libur,keterangan,createby_id,create_time) VALUES(?tgl_libur,?jns_libur,?keterangan,?createby_id,now())';
		$this->connector->AddParameter("?tgl_libur", $this->TglLibur);
        $this->connector->AddParameter("?jns_libur", $this->JnsLibur);
        $this->connector->AddParameter("?keterangan", $this->Keterangan);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_libur SET tgl_libur = ?tgl_libur, jns_libur = ?jns_libur, keterangan = ?keterangan, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?tgl_libur", $this->TglLibur);
        $this->connector->AddParameter("?jns_libur", $this->JnsLibur);
        $this->connector->AddParameter("?keterangan", $this->Keterangan);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'Delete From m_libur WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
	}

}
