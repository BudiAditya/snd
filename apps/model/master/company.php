<?php
class Company extends EntityBase {
	public $IsDeleted = false;
	public $Id;
	public $CompanyCode;
	public $Urutan;
	public $CompanyName;
	public $Address;
	public $City;
	public $Province;
	public $Telephone;
	public $Facsimile;
    public $Npwp;
    public $PersonInCharge;
    public $PicStatus;
    public $StartDate;
	public $PpnInAccId = 0;
	public $PpnOutAccId = 0;
	public $ArAccId = 0;
	public $ApAccId = 0;
    public $RevAccId = 0;
    public $CasDistCode;
    public $CasDistArea;
    public $IsOtp = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

    public function FormatStartDate($format = HUMAN_DATE) {
        return is_int($this->StartDate) ? date($format, $this->StartDate) : null;
    }

	public function FillProperties(array $row) {
		//$this->IsDeleted = $row["is_deleted"] == 1;
		$this->Id = $row["id"];
		$this->CompanyCode = $row["company_code"];
		$this->Urutan = $row["urutan"];
		$this->CompanyName = $row["company_name"];
		$this->Address = $row["address"];
		$this->City = $row["city"];
		$this->Province = $row["province"];
		$this->Telephone = $row["telephone"];
		$this->Facsimile = $row["facsimile"];
        $this->Npwp = $row["npwp"];
        $this->PersonInCharge = $row["personincharge"];
        $this->PicStatus = $row["pic_status"];
        $this->StartDate = strtotime($row["start_date"]);
		$this->PpnInAccId = $row["ppn_in_acc_id"];
		$this->PpnOutAccId = $row["ppn_out_acc_id"];
        $this->ArAccId = $row["ar_acc_id"];
        $this->ApAccId = $row["ap_acc_id"];
        $this->RevAccId = $row["rev_acc_id"];
        $this->CasDistCode = $row["cas_dist_code"];
        $this->CasDistArea = $row["cas_dist_area"];
        $this->IsOtp = $row["is_otp"];
	}

    public function LoadAll($orderBy = "a.company_code", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText = "SELECT a.* FROM sys_company AS a ORDER BY $orderBy";
		} else {
			$this->connector->CommandText = "SELECT a.* FROM sys_company AS a ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Company();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

    public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM sys_company AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function LoadByCode($code) {
		$this->connector->CommandText = "SELECT a.* FROM sys_company AS a WHERE a.company_code = ?code ORDER BY a.urutan";
		$this->connector->AddParameter("?code", $code);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM sys_company AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
'INSERT INTO sys_company(is_otp,cas_dist_code,cas_dist_area,rev_acc_id,ar_acc_id,ap_acc_id,company_code,company_name,address,city,province,telephone,facsimile,npwp,personincharge,pic_status,start_date,ppn_in_acc_id,ppn_out_acc_id)
VALUES(?is_otp,?cas_dist_code,?cas_dist_area,?rev_acc_id,?ar_acc_id,?ap_acc_id,?company_code,?company_name,?address,?city,?province,?telephone,?facsimile,?npwp,?personincharge,?pic_status,?start_date,?ppn_in_acc_id,?ppn_out_acc_id)';
		$this->connector->AddParameter("?company_code", $this->CompanyCode);
        $this->connector->AddParameter("?company_name", $this->CompanyName);
        $this->connector->AddParameter("?address", $this->Address);
        $this->connector->AddParameter("?city", $this->City);
        $this->connector->AddParameter("?province", $this->Province);
        $this->connector->AddParameter("?telephone", $this->Telephone);
        $this->connector->AddParameter("?facsimile", $this->Facsimile);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?personincharge", $this->PersonInCharge);
        $this->connector->AddParameter("?pic_status", $this->PicStatus);
        $this->connector->AddParameter("?start_date", $this->StartDate);
		$this->connector->AddParameter("?ppn_in_acc_id", $this->PpnInAccId);
		$this->connector->AddParameter("?ppn_out_acc_id", $this->PpnOutAccId);
        $this->connector->AddParameter("?ar_acc_id", $this->ArAccId);
        $this->connector->AddParameter("?ap_acc_id", $this->ApAccId);
        $this->connector->AddParameter("?rev_acc_id", $this->RevAccId);
        $this->connector->AddParameter("?cas_dist_code", $this->CasDistCode,"varchar");
        $this->connector->AddParameter("?cas_dist_area", $this->CasDistArea,"varchar");
        $this->connector->AddParameter("?is_otp", $this->IsOtp);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE sys_company SET
	company_code = ?company_code,
	company_name = ?company_name,
	address = ?address,
	city = ?city,
	province = ?province,
	telephone = ?telephone,
	facsimile = ?facsimile,
	npwp = ?npwp,
	personincharge = ?personincharge,
	pic_status = ?pic_status,
	ppn_in_acc_id = ?ppn_in_acc_id,
	ppn_out_acc_id = ?ppn_out_acc_id,
	ar_acc_id = ?ar_acc_id,
	ap_acc_id = ?ap_acc_id,
	rev_acc_id = ?rev_acc_id,
	start_date = ?start_date,
	cas_dist_code = ?cas_dist_code,
	cas_dist_area = ?cas_dist_area,
	is_otp = ?is_otp
WHERE id = ?id';
		$this->connector->AddParameter("?company_code", $this->CompanyCode);
        $this->connector->AddParameter("?company_name", $this->CompanyName);
        $this->connector->AddParameter("?address", $this->Address);
        $this->connector->AddParameter("?city", $this->City);
        $this->connector->AddParameter("?province", $this->Province);
        $this->connector->AddParameter("?telephone", $this->Telephone);
        $this->connector->AddParameter("?facsimile", $this->Facsimile);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?personincharge", $this->PersonInCharge);
        $this->connector->AddParameter("?pic_status", $this->PicStatus);
        $this->connector->AddParameter("?start_date", $this->StartDate);
		$this->connector->AddParameter("?ppn_in_acc_id", $this->PpnInAccId);
		$this->connector->AddParameter("?ppn_out_acc_id", $this->PpnOutAccId);
        $this->connector->AddParameter("?ar_acc_id", $this->ArAccId);
        $this->connector->AddParameter("?ap_acc_id", $this->ApAccId);
        $this->connector->AddParameter("?rev_acc_id", $this->RevAccId);
        $this->connector->AddParameter("?cas_dist_code", $this->CasDistCode,"varchar");
        $this->connector->AddParameter("?cas_dist_area", $this->CasDistArea,"varchar");
        $this->connector->AddParameter("?is_otp", $this->IsOtp);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
//		$this->connector->CommandText = 'DELETE FROM sys_company WHERE id = ?id';
//		$this->connector->AddParameter("?id", $id);
		$this->connector->CommandText = "UPDATE sys_company SET is_deleted = 1 WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function GetJSonCompanies() {
		$sql = "SELECT a.id,a.company_code,a.company_name FROM sys_company as a";
		$this->connector->CommandText = $sql;
		$data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
		$sql.= " Order By a.urutan";
		$this->connector->CommandText = $sql;
		$rows = array();
		$rs = $this->connector->ExecuteQuery();
		while ($row = $rs->FetchAssoc()){
			$rows[] = $row;
		}
		$result = array('total'=>$data['count'],'rows'=>$rows);
		return $result;
	}

	public function GetComboJSonCompanies() {
		$sql = "SELECT a.id,a.company_code,a.company_name FROM sys_company as a";
		$this->connector->CommandText = $sql;
		$sql.= " Order By a.urutan";
		$this->connector->CommandText = $sql;
		$rows = array();
		$rs = $this->connector->ExecuteQuery();
		while ($row = $rs->FetchAssoc()){
			$rows[] = $row;
		}
		$result = $rows;
		return $result;
	}

}

