<?php
class Customer extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CabangId = 1;
	public $CusTypeId = 0;
	public $CusCode;
	public $CusName;
	public $Addr1;
	public $Addr2;
	public $AreaId = 0;
	public $Phone;
	public $Fax;
	public $Contact;
	public $Npwp;
	public $Term = 0;
	public $IsPkp = 1;
	public $IsExternal = 0;
	public $IsAktif = 1;
	public $TaxCustId = 0;
	public $DateOfBirth;
	public $CreditLimit = 0;
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
		$this->CusTypeId = $row["custype_id"];
        $this->CusCode = $row["cus_code"];
		$this->CusName = $row["cus_name"];
        $this->Addr1 = $row["addr1"];
        $this->Addr2 = $row["addr2"];
        $this->AreaId = $row["area_id"];
        $this->Phone = $row["phone"];
        $this->Fax = $row["fax"];
        $this->Contact = $row["contact"];
        $this->Npwp = $row["npwp"];
        $this->Term = $row["term"];
        $this->IsPkp = $row["is_pkp"];
        $this->IsExternal = $row["is_external"];
        $this->DateOfBirth = $row["dateofbirth"];
        $this->CreditLimit = $row["credit_limit"];
        $this->IsAktif = $row["is_aktif"];
        $this->TaxCustId = $row["taxcust_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.cus_name") {
		$this->connector->CommandText = "SELECT a.* FROM m_customer AS a Where  a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Customer();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCabangId($companyId = 0,$orderBy = "a.cus_name") {
        $this->connector->CommandText = "SELECT a.* FROM m_customer AS a Where a.cabang_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Customer();
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
		$this->connector->CommandText = "SELECT a.* FROM m_customer AS a WHERE a.id = ?id And a.is_deleted = 0";
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
		$this->connector->CommandText = "SELECT a.* FROM m_customer AS a WHERE a.cus_code = ?eCode And a.is_deleted = 0";
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
		$this->connector->CommandText = 'INSERT INTO m_customer(cabang_id,custype_id,cus_code,cus_name,addr1,addr2,area_id,phone,fax,contact,npwp,term,is_aktif,taxcust_id,is_pkp,is_external,dateofbirth,credit_limit,createby_id,create_time) VALUES(?cabang_id,?custype_id,?cus_code,?cus_name,?addr1,?addr2,?area_id,?phone,?fax,?contact,?npwp,?term,?is_aktif,?taxcust_id,?is_pkp,?is_external,?dateofbirth,?credit_limit,?createby_id, now())';
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?custype_id", $this->CusTypeId);
        $this->connector->AddParameter("?cus_code", $this->CusCode, "varchar");
        $this->connector->AddParameter("?cus_name", $this->CusName);
		$this->connector->AddParameter("?addr1", $this->Addr1);
        $this->connector->AddParameter("?addr2", $this->Addr2);
        $this->connector->AddParameter("?area_id", $this->AreaId);
        $this->connector->AddParameter("?phone", $this->Phone);
        $this->connector->AddParameter("?fax", $this->Fax);
        $this->connector->AddParameter("?contact", $this->Contact);
        $this->connector->AddParameter("?npwp", $this->Npwp,"varchar");
        $this->connector->AddParameter("?term", $this->Term);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?taxcust_id", $this->TaxCustId);
        $this->connector->AddParameter("?is_pkp", $this->IsPkp);
        $this->connector->AddParameter("?is_external", $this->IsExternal);
        $this->connector->AddParameter("?dateofbirth", $this->DateOfBirth);
        $this->connector->AddParameter("?credit_limit", $this->CreditLimit);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_customer 
SET cabang_id = ?cabang_id
,custype_id = ?custype_id
,cus_code = ?cus_code
,cus_name = ?cus_name
,addr1 = ?addr1
,addr2 = ?addr2
,area_id = ?area_id
,phone = ?phone
,fax = ?fax
,contact = ?contact
,npwp = ?npwp
,term = ?term
,is_aktif = ?is_aktif
,taxcust_id = ?taxcust_id
,is_pkp = ?is_pkp
,is_external = ?is_external
,dateofbirth = ?dateofbirth
,credit_limit = ?credit_limit
,updateby_id = ?updateby_id
,update_time = now() 
WHERE id = ?id';
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?custype_id", $this->CusTypeId);
        $this->connector->AddParameter("?cus_code", $this->CusCode, "varchar");
        $this->connector->AddParameter("?cus_name", $this->CusName);
        $this->connector->AddParameter("?addr1", $this->Addr1);
        $this->connector->AddParameter("?addr2", $this->Addr2);
        $this->connector->AddParameter("?area_id", $this->AreaId);
        $this->connector->AddParameter("?phone", $this->Phone);
        $this->connector->AddParameter("?fax", $this->Fax);
        $this->connector->AddParameter("?contact", $this->Contact);
        $this->connector->AddParameter("?npwp", $this->Npwp,"varchar");
        $this->connector->AddParameter("?term", $this->Term);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?taxcust_id", $this->TaxCustId);
        $this->connector->AddParameter("?is_pkp", $this->IsPkp);
        $this->connector->AddParameter("?is_external", $this->IsExternal);
        $this->connector->AddParameter("?dateofbirth", $this->DateOfBirth);
        $this->connector->AddParameter("?credit_limit", $this->CreditLimit);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'Update m_customer a Set a.is_deleted = 1 WHERE a.id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_customer WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetAutoCustCode($areaId){
        $sql = "Select a.area_code as valout From m_sales_area a Where a.id = $areaId";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $acd = 'ERR';
        $ncd = 0;
        if($rs){
            $row = $rs->FetchAssoc();
            $acd = $row["valout"];
            $sql = "Select coalesce(max(substr(a.cus_code,4,4)),'0000') as valout From m_customer a Where a.area_id = $areaId";
            $this->connector->CommandText = $sql;
            $rs = $this->connector->ExecuteQuery();
            $row = $rs->FetchAssoc();
            $ncd = ((Int)$row["valout"])+1;
            $acd.= str_pad($ncd,4,'0',STR_PAD_LEFT);
        }
        return $acd;
    }

    public function GetJSonCustomer($cabangId = 0, $filter = null,$sort = 'a.cus_name',$order = 'ASC') {
        $sql = "SELECT a.id,a.cus_name,a.cus_code,a.addr1,b.area_name,a.term,a.credit_limit,a.is_pkp,b.zone_id FROM m_customer as a Left Join m_sales_area b ON a.area_id = b.id Where a.is_deleted = 0";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = $cabangId";
        }
        if ($filter != null){
            $sql.= " And (a.cus_name Like '%$filter%')";
        }
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }
}
