<?php
class SerialNo extends EntityBase {
	public $Id;
	public $CompanyId = 1;
	public $TaxYear;
	public $SnPrefix;
	public $SnStart;
	public $SnEnd;
	public $SnNextCounter;
	public $IsAktif;
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
        $this->CompanyId = $row["company_id"];
		$this->TaxYear = $row["tax_year"];
		$this->SnPrefix = $row["sn_prefix"];
        $this->SnStart = $row["sn_start"];
        $this->SnEnd = $row["sn_end"];
        $this->SnNextCounter = $row["sn_next_counter"];
        $this->IsAktif = $row["is_aktif"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($companyId,$orderBy = "a.tax_year,a.sn_start", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM m_fp_serialno AS a Where a.company_id = $companyId ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new SerialNo();
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
		$this->connector->CommandText = "SELECT a.* FROM m_fp_serialno AS a WHERE a.id = ?id";
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
		$this->connector->CommandText = 'INSERT INTO m_fp_serialno(company_id,tax_year,sn_prefix,sn_start,sn_end,sn_next_counter,is_aktif,createby_id,create_time) VALUES(?company_id,?tax_year,?sn_prefix,?sn_start,?sn_end,?sn_next_counter,?is_aktif,?createby_id,now())';
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?tax_year", $this->TaxYear);
        $this->connector->AddParameter("?sn_prefix", $this->SnPrefix,"varchar");
        $this->connector->AddParameter("?sn_start", $this->SnStart,"varchar");
        $this->connector->AddParameter("?sn_end", $this->SnEnd,"varchar");
        $this->connector->AddParameter("?sn_next_counter", $this->SnNextCounter,"varchar");
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
		$this->connector->CommandText = 'UPDATE m_fp_serialno SET company_id = ?company_id, tax_year = ?tax_year, sn_prefix = ?sn_prefix, sn_start = ?sn_start, sn_end = ?sn_end, sn_next_counter = ?sn_next_counter, is_aktif = ?is_aktif, updateby_id = ?updateby_id, update_time = now() WHERE id = ?id';
        $this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?tax_year", $this->TaxYear);
        $this->connector->AddParameter("?sn_prefix", $this->SnPrefix,"varchar");
        $this->connector->AddParameter("?sn_start", $this->SnStart,"varchar");
        $this->connector->AddParameter("?sn_end", $this->SnEnd,"varchar");
        $this->connector->AddParameter("?sn_next_counter", $this->SnNextCounter,"varchar");
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_fp_serialno Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetSnFaktur($companyId,$tglFaktur){
	    //fc_sys_getnsfaktur
        $sql = 'Select fc_sys_getnsfaktur(?cid,?tgf) As valout;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cid", $companyId);
        $this->connector->AddParameter("?tgf", $tglFaktur);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }
}
