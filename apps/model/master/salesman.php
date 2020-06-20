<?php
class Salesman extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $SalesCode;
	public $SalesName;
	public $KaryawanId = 0;
	public $IsAktif = 1;
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
		$this->SalesCode = $row["sales_code"];
		$this->SalesName = $row["sales_name"];
        $this->KaryawanId = $row["karyawan_id"];
        $this->IsAktif = $row["is_aktif"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}
    
	public function LoadAll($orderBy = "a.sales_code", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM m_salesman AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Salesman();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByStatus($status = 0, $orderBy = "a.sales_code", $includeDeleted = false) {
        $this->connector->CommandText = "SELECT a.* FROM m_salesman AS a Where a.is_aktif = $status ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Salesman();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $id
	 * @return Salesman
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM m_salesman AS a WHERE a.id = ?id";
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
	 * @return Salesman
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO m_salesman(is_aktif,karyawan_id,sales_code,sales_name,createby_id,create_time) VALUES(?is_aktif,?karyawan_id,?sales_code,?sales_name,?createby_id,now())';
		$this->connector->AddParameter("?sales_code", $this->SalesCode,"varchar");
        $this->connector->AddParameter("?sales_name", $this->SalesName);
        $this->connector->AddParameter("?karyawan_id", $this->KaryawanId);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
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
		$this->connector->CommandText = 'UPDATE m_salesman SET is_aktif = ?is_aktif, karyawan_id = ?karyawan_id, sales_code = ?sales_code, sales_name = ?sales_name, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?sales_code", $this->SalesCode,"varchar");
        $this->connector->AddParameter("?sales_name", $this->SalesName);
        $this->connector->AddParameter("?karyawan_id", $this->KaryawanId);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'UPDATE m_salesman SET is_deleted = 1,updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_salesman Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetData($offset,$limit,$field,$search='',$sort = 'a.sales_code',$order = 'ASC') {
        $sql = "SELECT a.* FROM m_salesman as a Where a.is_aktif = 1 And a.is_deleted = 0 ";
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
                $rows[$i]['sales_code'] = $row['sales_code'];
                $rows[$i]['sales_name'] = $row['sales_name'];
                $i++;
            }
        }
        //data hasil query yang dikirim kembali dalam format json
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

}
