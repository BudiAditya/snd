<?php

require_once("journal_detail.php");

class Journal extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $DocStatusCodes = array(
		0 => "DRAFT",
		1 => "VERIFIED",
        2 => "APPROVED",
		3 => "VOID"
	);

	public $Id;
    public $IsDeleted = false;
	public $CompanyId;
    public $CabangId;
    public $CabangCode;
	public $TrxCode;
	public $JournalNo;
	public $JournalDate;
	public $JournalDescs;
	public $ReffNo;
	public $JournalStatus = 0;
	public $InputMode = 0;
	public $DbAmount = 0;
	public $CrAmount = 0;
	public $CreatebyId = 0;
	public $CreateTime;
	public $UpdatebyId = 0;
	public $UpdateTime;
	public $VerifybyId = 0;
	public $VerifyTime;
	public $ApprovebyId = 0;
	public $ApproveTime;
	public $SourceCode;

	/** @var JournalDetail[] */
	public $Details = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
        $this->IsDeleted = $row["is_deleted"] == 1;
		$this->Id = $row["id"];
		$this->CompanyId = $row["company_id"];
        $this->CabangId = $row["cabang_id"];
        $this->CabangCode= $row["cabang_code"];
        $this->TrxCode= $row["trx_code"];
        $this->JournalNo= $row["journal_no"];
        $this->JournalDate= strtotime($row["journal_date"]);
        $this->JournalDescs= $row["journal_descs"];
        $this->ReffNo= $row["reff_no"];
        $this->JournalStatus = $row["journal_status"];
        $this->InputMode = $row["input_mode"];
        $this->DbAmount = $row["dbAmount"];
        $this->CrAmount = $row["crAmount"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime= $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime= $row["update_time"];
        $this->VerifybyId = $row["verifyby_id"];
        $this->VerifyTime = $row["verify_time"];
        $this->ApprovebyId = $row["approveby_id"];
        $this->ApproveTime = $row["approve_time"];
        $this->SourceCode = $row["source_code"];
	}

	public function FormatJournalDate($format = HUMAN_DATE) {
		return is_int($this->JournalDate) ? date($format, $this->JournalDate) : null;
	}

	/**
	 * @return JournalDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new JournalDetail();
		$this->Details = $detail->LoadByJournalId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Journal
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_master AS a WHERE a.id = ?id and a.is_deleted = 0";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_master AS a WHERE a.id = ?id and a.is_deleted = 0";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByNoJournal($noJournal) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_master AS a WHERE a.journal_no = ?noJournal and a.is_deleted = 0";
		$this->connector->AddParameter("?noJournal", $noJournal);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCompanyId($companyId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_master AS a WHERE a.company_id = ?companyId and a.is_deleted = 0";
        $this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Journal();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ac_journal_master AS a WHERE a.cabang_id = ?cabangId and a.is_deleted = 0";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Journal();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ac_journal_master(source_code,cabang_id,trx_code,journal_no,journal_date,journal_descs,reff_no,journal_status,input_mode,createby_id,create_time) VALUES(?source_code,?cabang_id,?trx_code,?journal_no,?journal_date,?journal_descs,?reff_no,?journal_status,?input_mode,?createby_id,now())";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?journal_no", $this->JournalNo);
		$this->connector->AddParameter("?journal_date", $this->JournalDate);
		$this->connector->AddParameter("?trx_code", $this->TrxCode);
		$this->connector->AddParameter("?journal_descs", $this->JournalDescs);
		$this->connector->AddParameter("?reff_no", $this->ReffNo);
		$this->connector->AddParameter("?journal_status", $this->JournalStatus);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $this->connector->AddParameter("?input_mode", $this->InputMode);
        $this->connector->AddParameter("?source_code", $this->SourceCode == null || $this->SourceCode == '' ? 'AC' : $this->SourceCode);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText = "UPDATE t_ac_journal_master SET source_code = ?source_code, cabang_id = ?cabang_id, journal_no = ?journal_no, journal_date = ?journal_date, trx_code = ?trx_code, journal_descs = ?journal_descs, journal_status = ?journal_status, updateby_id = ?updateby_id, update_time = NOW(), reff_no = ?reff_no WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?journal_no", $this->JournalNo);
        $this->connector->AddParameter("?journal_date", $this->JournalDate);
        $this->connector->AddParameter("?trx_code", $this->TrxCode);
        $this->connector->AddParameter("?journal_descs", $this->JournalDescs);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?journal_status", $this->JournalStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?source_code", $this->SourceCode == null || $this->SourceCode == '' ? 'AC' : $this->SourceCode);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "Delete a From t_ac_journal_master a WHERE a.id = ?id And a.journal_status = 0";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        $this->connector->CommandText = "Update t_ac_journal_master a Set a.journal_status = 3 WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetJournalDocNo(){
        $sql = 'Select fc_sys_getdocno(?eti,?txc,?txd) As valout;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?eti", $this->CompanyId);
        $this->connector->AddParameter("?txc", $this->TrxCode);
        $this->connector->AddParameter("?txd", $this->JournalDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function Verify($id,$xmode = 1,$uId) {
        if ($xmode == 1) {
            $sql = "Update t_ac_journal_master Set journal_status = 1, verifyby_id = ?updateById, verify_time = NOW() WHERE id = ?id";
        }else{
            $sql = "Update t_ac_journal_master Set journal_status = 0, verifyby_id = 0, verify_time = null WHERE id = ?id";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateById", $uId);
        return $this->connector->ExecuteNonQuery();
    }

    public function Approve($id,$xmode = 1,$uId) {
        if ($xmode == 1) {
            $sql = "Update t_ac_journal_master Set journal_status = 2, approveby_id = ?updateById, approve_time = NOW() WHERE id = ?id";
        }else{
            $sql = "Update t_ac_journal_master Set journal_status = 1, approveby_id = 0, approve_time = null WHERE id = ?id";
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?updateById", $uId);
        return $this->connector->ExecuteNonQuery();
    }

    public function LoadTrxType(){
        $sql = 'Select a.* From sys_doc_type as a Where Not IsNull(a.voucher_code) Order By a.trx_code';
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetRevenueSumByYear($tahun){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.journal_date) = 1 THEN a.cr_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 2 THEN a.cr_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 3 THEN a.cr_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 4 THEN a.cr_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 5 THEN a.cr_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 6 THEN a.cr_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 7 THEN a.cr_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 8 THEN a.cr_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 9 THEN a.cr_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 10 THEN a.cr_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 11 THEN a.cr_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 12 THEN a.cr_amount ELSE 0 END), 0) December
			    FROM vw_ac_journal_detail_4report a Where year(a.journal_date) = $tahun And left(a.acc_code,3) = '410' And a.journal_status = 2 And a.is_deleted = 0";
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $data = $row["January"];
        $data.= ",".$row["February"];
        $data.= ",".$row["March"];
        $data.= ",".$row["April"];
        $data.= ",".$row["May"];
        $data.= ",".$row["June"];
        $data.= ",".$row["July"];
        $data.= ",".$row["August"];
        $data.= ",".$row["September"];
        $data.= ",".$row["October"];
        $data.= ",".$row["November"];
        $data.= ",".$row["December"];
        return $data;
    }

    public function GetRevenueSumByYear1($tahun){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.journal_date) = 1 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 2 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 3 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 4 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 5 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 6 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 7 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 8 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 9 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 10 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 11 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 12 THEN a.cr_amount - a.db_amount ELSE 0 END), 0) December
			    FROM vw_ac_journal_detail_4report a Where year(a.journal_date) = $tahun And left(a.acc_code,3) = '410' And a.journal_status = 2 And a.is_deleted = 0";
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $data = $row["January"];
        $data.= ",".$row["February"];
        $data.= ",".$row["March"];
        $data.= ",".$row["April"];
        $data.= ",".$row["May"];
        $data.= ",".$row["June"];
        $data.= ",".$row["July"];
        $data.= ",".$row["August"];
        $data.= ",".$row["September"];
        $data.= ",".$row["October"];
        $data.= ",".$row["November"];
        $data.= ",".$row["December"];
        return $data;
    }

    public function GetCostSumByYear($tahun){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.journal_date) = 1 THEN a.db_amount  ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 2 THEN a.db_amount  ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 3 THEN a.db_amount  ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 4 THEN a.db_amount  ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 5 THEN a.db_amount  ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 6 THEN a.db_amount  ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 7 THEN a.db_amount  ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 8 THEN a.db_amount  ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 9 THEN a.db_amount  ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 10 THEN a.db_amount  ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 11 THEN a.db_amount  ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 12 THEN a.db_amount  ELSE 0 END), 0) December
			    FROM vw_ac_journal_detail_4report a Where year(a.journal_date) = $tahun And left(a.acc_code,1) = '5' And a.journal_status = 2 And a.is_deleted = 0";
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $data = $row["January"];
        $data.= ",".$row["February"];
        $data.= ",".$row["March"];
        $data.= ",".$row["April"];
        $data.= ",".$row["May"];
        $data.= ",".$row["June"];
        $data.= ",".$row["July"];
        $data.= ",".$row["August"];
        $data.= ",".$row["September"];
        $data.= ",".$row["October"];
        $data.= ",".$row["November"];
        $data.= ",".$row["December"];
        return $data;
    }

    public function GetCostSumByYear1($tahun){
        $query = "SELECT COALESCE(SUM(CASE WHEN month(a.journal_date) = 1 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) January
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 2 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) February
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 3 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) March
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 4 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) April
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 5 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) May
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 6 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) June
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 7 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) July
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 8 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) August
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 9 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) September
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 10 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) October
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 11 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) November
				,COALESCE(SUM(CASE WHEN month(a.journal_date) = 12 THEN a.db_amount - a.cr_amount ELSE 0 END), 0) December
			    FROM vw_ac_journal_detail_4report a Where year(a.journal_date) = $tahun And left(a.acc_code,1) = '5' And a.journal_status = 2 And a.is_deleted = 0";
        $this->connector->CommandText = $query;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        $data = $row["January"];
        $data.= ",".$row["February"];
        $data.= ",".$row["March"];
        $data.= ",".$row["April"];
        $data.= ",".$row["May"];
        $data.= ",".$row["June"];
        $data.= ",".$row["July"];
        $data.= ",".$row["August"];
        $data.= ",".$row["September"];
        $data.= ",".$row["October"];
        $data.= ",".$row["November"];
        $data.= ",".$row["December"];
        return $data;
    }

    public function LoadJournal4Approval ($cabId = 0,$stDate, $enDate, $tStatus = 0){
        $sql = "SELECT a.* FROM vw_ac_journal_master a WHERE a.is_deleted = 0 And (a.journal_date BETWEEN ?stDate And ?enDate)";
        if ($cabId > 0){
            $sql.= " And a.cabang_id = ".$cabId;
        }
        if ($tStatus > -1){
            $sql.= " And a.journal_status = ".$tStatus;
        }
        $sql.= " ORDER BY a.journal_date,a.journal_no";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?stDate", date('Y-m-d', $stDate));
        $this->connector->AddParameter("?enDate", date('Y-m-d', $enDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
