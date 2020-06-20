<?php
class Supplier extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CabangId = 1;
	public $SupTypeId = 0;
	public $SupCode;
	public $SupName;
	public $Addr1;
	public $Addr2;
	public $City;
	public $Phone;
	public $Fax;
	public $Hp;
	public $Contact;
	public $Npwp;
	public $Term;
	public $Comodity;
	public $Manager;
	public $PostCode;
	public $Bank;
	public $AccountNo;
	public $IsPkp = 1;
	public $IsAktif = 1;
	public $DateOfBirth;
	public $CreditLimit = 0;
	public $IsPrincipal = 0;
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
		$this->SupTypeId = $row["suptype_id"];
        $this->SupCode = $row["sup_code"];
		$this->SupName = $row["sup_name"];
        $this->Addr1 = $row["addr1"];
        $this->Addr2 = $row["addr2"];
        $this->City = $row["city"];
        $this->Phone = $row["phone"];
        $this->Fax = $row["fax"];
        $this->Hp = $row["hp"];
        $this->Contact = $row["contact"];
        $this->Npwp = $row["npwp"];
        $this->Term = $row["term"];
        $this->Comodity = $row["comodity"];
        $this->Manager = $row["manager"];
        $this->PostCode = $row["postcode"];
        $this->Bank = $row["bank"];
        $this->AccountNo = $row["account_no"];
        $this->IsPkp = $row["is_pkp"];
        $this->IsAktif = $row["is_aktif"];
        $this->IsPrincipal = $row["is_principal"];
        $this->DateOfBirth = $row["dateofbirth"];
        $this->CreditLimit = $row["credit_limit"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.sup_name") {
		$this->connector->CommandText = "SELECT a.* FROM m_supplier AS a Where  a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Supplier();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadPrincipal($orderBy = "a.sup_name") {
        $this->connector->CommandText = "SELECT a.* FROM m_supplier AS a Where a.is_principal = 1 And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Supplier();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($companyId = 0,$orderBy = "a.sup_name") {
        $this->connector->CommandText = "SELECT a.* FROM m_supplier AS a Where a.cabang_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Supplier();
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
		$this->connector->CommandText = "SELECT a.* FROM m_supplier AS a WHERE a.id = ?id And a.is_deleted = 0";
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
		$this->connector->CommandText = "SELECT a.* FROM m_supplier AS a WHERE a.sup_code = ?eCode And a.is_deleted = 0";
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
		$this->connector->CommandText = 'INSERT INTO m_supplier(is_principal,cabang_id,suptype_id,sup_code,sup_name,addr1,addr2,city,phone,fax,hp,contact,npwp,term,comodity,manager,postcode,bank,account_no,is_pkp,is_aktif,dateofbirth,credit_limit,createby_id,create_time) VALUES(?is_principal,?cabang_id,?suptype_id,?sup_code,?sup_name,?addr1,?addr2,?city,?phone,?fax,?hp,?contact,?npwp,?term,?comodity,?manager,?postcode,?bank,?account_no,?is_pkp,?is_aktif,?dateofbirth,?credit_limit,?createby_id, now())';
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?suptype_id", $this->SupTypeId);
        $this->connector->AddParameter("?sup_code", $this->SupCode, "varchar");
        $this->connector->AddParameter("?sup_name", $this->SupName);
		$this->connector->AddParameter("?addr1", $this->Addr1);
        $this->connector->AddParameter("?addr2", $this->Addr2);
        $this->connector->AddParameter("?city", $this->City);
        $this->connector->AddParameter("?phone", $this->Phone);
        $this->connector->AddParameter("?fax", $this->Fax);
        $this->connector->AddParameter("?hp", $this->Hp);
        $this->connector->AddParameter("?contact", $this->Contact);
        $this->connector->AddParameter("?npwp", $this->Npwp,"varchar");
        $this->connector->AddParameter("?term", $this->Term);
        $this->connector->AddParameter("?comodity", $this->Comodity);
        $this->connector->AddParameter("?manager", $this->Manager);
        $this->connector->AddParameter("?postcode", $this->PostCode);
        $this->connector->AddParameter("?bank", $this->Bank);
        $this->connector->AddParameter("?account_no", $this->AccountNo);
        $this->connector->AddParameter("?is_pkp", $this->IsPkp);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?is_principal", $this->IsPrincipal);
        $this->connector->AddParameter("?dateofbirth", $this->DateOfBirth);
        $this->connector->AddParameter("?credit_limit", $this->CreditLimit);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_supplier 
SET cabang_id = ?cabang_id
,suptype_id = ?suptype_id
,sup_code = ?sup_code
,sup_name = ?sup_name
,addr1 = ?addr1
,addr2 = ?addr2
,city = ?city
,phone = ?phone
,fax = ?fax
,hp = ?hp
,contact = ?contact
,npwp = ?npwp
,term = ?term
,comodity = ?comodity
,manager = ?manager
,postcode = ?postcode
,bank = ?bank
,account_no = ?account_no
,is_pkp = ?is_pkp
,is_aktif = ?is_aktif
,is_principal = ?is_principal
,dateofbirth = ?dateofbirth
,credit_limit = ?credit_limit
,updateby_id = ?updateby_id
,update_time = now() 
WHERE id = ?id';
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?suptype_id", $this->SupTypeId);
        $this->connector->AddParameter("?sup_code", $this->SupCode, "varchar");
        $this->connector->AddParameter("?sup_name", $this->SupName);
        $this->connector->AddParameter("?addr1", $this->Addr1);
        $this->connector->AddParameter("?addr2", $this->Addr2);
        $this->connector->AddParameter("?city", $this->City);
        $this->connector->AddParameter("?phone", $this->Phone);
        $this->connector->AddParameter("?fax", $this->Fax);
        $this->connector->AddParameter("?hp", $this->Hp);
        $this->connector->AddParameter("?contact", $this->Contact);
        $this->connector->AddParameter("?npwp", $this->Npwp,"varchar");
        $this->connector->AddParameter("?term", $this->Term);
        $this->connector->AddParameter("?comodity", $this->Comodity);
        $this->connector->AddParameter("?manager", $this->Manager);
        $this->connector->AddParameter("?postcode", $this->PostCode);
        $this->connector->AddParameter("?bank", $this->Bank);
        $this->connector->AddParameter("?account_no", $this->AccountNo);
        $this->connector->AddParameter("?is_pkp", $this->IsPkp);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?is_principal", $this->IsPrincipal);
        $this->connector->AddParameter("?dateofbirth", $this->DateOfBirth);
        $this->connector->AddParameter("?credit_limit", $this->CreditLimit);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'Update m_supplier a Set a.is_deleted = 1 WHERE a.id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_supplier WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetJSonSupplier($cabangId = 0, $filter = null,$sort = 'a.sup_code',$order = 'ASC') {
        $sql = "SELECT a.id,a.sup_code,a.sup_name,a.addr1,a.city,a.term,a.credit_limit,a.is_principal,a.is_pkp FROM m_supplier as a Where a.is_deleted = 0";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = $cabangId";
        }
        if ($filter != null){
            $sql.= " And (a.sup_code Like '%$filter%' Or a.sup_name Like '%$filter%')";
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
