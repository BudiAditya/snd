<?php
class Warehouse extends EntityBase {
	public $Id;
	public $CabangId;
	public $CabCode;
	public $WhCode;
	public $WhName;
	public $WhPic;
	public $WhStatus = 1;
	public $IsTrx = 0;
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
		$this->CabangId = $row["cabang_id"];
        $this->CabCode = $row["cabang_code"];
		$this->WhCode = $row["wh_code"];
		$this->WhName = $row["wh_name"];
        $this->WhPic = $row["wh_pic"];
        $this->WhStatus = $row["wh_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->IsTrx = $row["is_trx"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($isTrxOnly = 0,$orderBy = "a.wh_code") {
	    $sql = "SELECT a.*,b.kode as cabang_code FROM m_warehouse AS a Join m_cabang b On a.cabang_id = b.id";
        if ($isTrxOnly == 1){
            $sql.= " Where a.is_trx = 1";
        }
	    $sql.= " ORDER BY $orderBy";
		$this->connector->CommandText = $sql;
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Warehouse();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCabangId($cabId = 0, $isTrxOnly = 0, $orderBy = "a.wh_code") {
	    $sql = "SELECT a.*,b.kode as cabang_code FROM m_warehouse AS a Join m_cabang b On a.cabang_id = b.id Where a.cabang_id = $cabId";
	    if ($isTrxOnly == 1){
	        $sql.= " And a.is_trx = 1";
        }
	    $sql.= " ORDER BY $orderBy";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warehouse();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByNotCabangId($cabId = 0, $isTrxOnly = 0, $orderBy = "a.wh_code") {
        $sql = "SELECT a.*,b.kode as cabang_code FROM m_warehouse AS a Join m_cabang b On a.cabang_id = b.id Where a.cabang_id <> $cabId";
        if ($isTrxOnly == 1){
            $sql.= " And a.is_trx = 1";
        }
        $sql.= " ORDER BY $orderBy";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warehouse();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByAllowedCabangId($cabIds = 0, $isTrxOnly = 0, $orderBy = "a.wh_code") {
        $sql = "SELECT a.*,b.kode as cabang_code FROM m_warehouse AS a Join m_cabang b On a.cabang_id = b.id Where a.cabang_id IN (".$cabIds.")";
        if ($isTrxOnly == 1){
            $sql.= " And a.is_trx = 1";
        }
        $sql.= " ORDER BY $orderBy";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warehouse();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByEntityId($entityId = 0, $isTrxOnly = 0, $orderBy = "a.wh_code") {
	    $sql = "SELECT a.*,b.kode as cabang_code FROM m_warehouse AS a Join m_cabang b On a.cabang_id = b.id Where b.company_id = $entityId ";
        if ($isTrxOnly == 1){
            $sql.= " And a.is_trx = 1";
        }
	    $sql.= " ORDER BY $orderBy";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warehouse();
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
		$this->connector->CommandText = "SELECT a.*,b.kode as cabang_code FROM m_warehouse AS a Join m_cabang b On a.cabang_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
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
		$this->connector->CommandText = 'INSERT INTO m_warehouse(is_trx,cabang_id,wh_code,wh_name,wh_pic,wh_status,createby_id,create_time) VALUES(?is_trx,?cabang_id,?wh_code,?wh_name,?wh_pic,?wh_status,?createby_id,now())';
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?wh_code", $this->WhCode);
        $this->connector->AddParameter("?wh_name", $this->WhName);
        $this->connector->AddParameter("?wh_pic", $this->WhPic);
        $this->connector->AddParameter("?wh_status", $this->WhStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $this->connector->AddParameter("?is_trx", $this->IsTrx);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_warehouse SET is_trx = ?is_trx, cabang_id = ?cabang_id, wh_code = ?wh_code, wh_name = ?wh_name, wh_pic = ?wh_pic, wh_status = ?wh_status, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?wh_code", $this->WhCode);
        $this->connector->AddParameter("?wh_name", $this->WhName);
        $this->connector->AddParameter("?wh_pic", $this->WhPic);
        $this->connector->AddParameter("?wh_status", $this->WhStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?is_trx", $this->IsTrx);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		//$this->connector->CommandText = 'UPDATE m_warehouse SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->CommandText = 'Delete From m_warehouse WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        //$this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

}
