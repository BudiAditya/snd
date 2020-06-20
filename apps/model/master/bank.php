<?php
class Bank extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $BankCode;
	public $BankName;
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
		$this->BankCode = $row["bank_code"];
		$this->BankName = $row["bank_name"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.bank_code", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM m_bank AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Bank();
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
		$this->connector->CommandText = "SELECT a.* FROM m_bank AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO m_bank(bank_code,bank_name,createby_id,create_time) VALUES(?bank_code,?bank_name,?createby_id,now())';
		$this->connector->AddParameter("?bank_code", $this->BankCode,"varchar");
        $this->connector->AddParameter("?bank_name", $this->BankName);
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
		$this->connector->CommandText = 'UPDATE m_bank SET bank_code = ?bank_code, bank_name = ?bank_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?bank_code", $this->BankCode,"varchar");
        $this->connector->AddParameter("?bank_name", $this->BankName);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_bank SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_bank Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetData($offset,$limit,$field,$search='',$sort = 'a.bank_code',$order = 'ASC') {
        $sql = "SELECT a.* FROM m_bank as a Where a.is_deleted = 0 ";
        if ($search !='' && $field !=''){
            $sql.= "And $field Like '%{$search}%' ";
        }
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= "Order By $sort $order Limit {$offset},{$limit}";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $rows = array();
        if ($rs != null) {
            $i = 0;
            while ($row = $rs->FetchAssoc()) {
                $rows[$i]['id'] = $row['id'];
                $rows[$i]['bank_code'] = $row['bank_code'];
                $rows[$i]['bank_name'] = $row['bank_name'];
                $i++;
            }
        }
        //data hasil query yang dikirim kembali dalam format json
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

}
