<?php
class Expedition extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $ExpCode;
	public $ExpName;
	public $Address;
	public $Phone;
	public $Fax;
	public $Cperson;
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
		$this->ExpCode = $row["exp_code"];
		$this->ExpName = $row["exp_name"];
        $this->Address = $row["address"];
        $this->Phone = $row["phone"];
        $this->Fax = $row["fax"];
        $this->Cperson = $row["cperson"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.exp_code", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM m_expedition AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Expedition();
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
		$this->connector->CommandText = "SELECT a.* FROM m_expedition AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO m_expedition(exp_code,exp_name,address,phone,fax,cperson,createby_id,create_time) VALUES(?exp_code,?exp_name,?address,?phone,?fax,?cperson,?createby_id,now())';
		$this->connector->AddParameter("?exp_code", $this->ExpCode,"varchar");
        $this->connector->AddParameter("?exp_name", $this->ExpName);
        $this->connector->AddParameter("?address", $this->Address);
        $this->connector->AddParameter("?phone", $this->Phone);
        $this->connector->AddParameter("?fax", $this->Fax);
        $this->connector->AddParameter("?cperson", $this->Cperson);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
            $rs = $this->Id;
        }
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_expedition SET address = ?address, phone = ?phone, fax = ?fax, cperson = ?cperson, exp_code = ?exp_code, exp_name = ?exp_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?exp_code", $this->ExpCode,"varchar");
        $this->connector->AddParameter("?exp_name", $this->ExpName);
        $this->connector->AddParameter("?address", $this->Address);
        $this->connector->AddParameter("?phone", $this->Phone);
        $this->connector->AddParameter("?fax", $this->Fax);
        $this->connector->AddParameter("?cperson", $this->Cperson);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_expedition SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_expedition Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
