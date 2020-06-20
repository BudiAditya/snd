<?php

class CbTrx extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CompanyId;
	public $CabangId;
	public $TrxDate;
    public $TrxMode;
    public $TrxTypeId;
    public $CoaBankId;
    public $TrxNo;
    public $TrxDescs;
    public $DbAccId;
    public $CrAccId;
    public $TrxAmount;
    public $RelasiName;
    public $ReffNo;
    public $TrxStatus = 0;
    public $CreateById;
    public $CreateTime;
	public $UpdatedById;
	public $UpdatedTime;
    public $CreateMode;

    public function __construct($id = null) {
        parent::__construct();
        if (is_numeric($id)) {
            $this->LoadById($id);
        }
    }

    public function FormatTrxDate($format = HUMAN_DATE) {
        return is_int($this->TrxDate) ? date($format, $this->TrxDate) : date($format, strtotime(date('Y-m-d')));
    }

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
        $this->CompanyId = $row["company_id"];
        $this->CabangId = $row["cabang_id"];
        $this->TrxDate = strtotime($row["trx_date"]);
        $this->TrxMode = $row["trx_mode"];
        $this->TrxTypeId = $row["trxtype_id"];
        $this->CoaBankId = $row["bank_id"];
        $this->TrxNo = $row["trx_no"];
        $this->TrxDescs = $row["trx_descs"];
        $this->DbAccId = $row["db_acc_id"];
        $this->CrAccId = $row["cr_acc_id"];
        $this->TrxAmount = $row["trx_amount"];
        $this->RelasiName = $row["relasi_name"];
        $this->ReffNo = $row["reff_no"];
        $this->TrxStatus = $row["trx_status"];
        $this->CreateById = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatedById = $row["updateby_id"];
        $this->UpdatedTime = $row["update_time"];
        $this->CreateMode = $row["create_mode"];
	}

	/**
	 * @param string $orderBy
	 * @return TrxType[]
	 */
	public function LoadAll($orderBy = "a.trx_no") {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_transaction AS a WHERE a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new CbTrx();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return TrxType
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_transaction AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByTrxNo($docNo) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_transaction AS a WHERE a.trx_no = ?docNo";
        $this->connector->AddParameter("?docNo", $docNo);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	/**
	 * Mencari data bank berdasarkan akun CoA nya
	 *
	 * @param int $sbu
	 * @param int $accId
	 * @return TrxType
	 */
	public function LoadByTrxMode($trxMode) {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_transaction AS a WHERE a.is_deleted = 0 and a.trx_mode = ?trxMode";
		$this->connector->AddParameter("?trxMode", $trxMode);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new CbTrx();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
	}

    public function LoadByTrxTypeId($trxTypeId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_transaction AS a WHERE a.is_deleted = 0 and a.trxtype_id = ?trxTypeId";
        $this->connector->AddParameter("?trxTypeId", $trxTypeId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new CbTrx();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_transaction AS a WHERE a.is_deleted = 0 and a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new CbTrx();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function Load4Reports($companyId,$cabangId = 0, $trxTypeId = 0, $trxMode = 0, $bankId = 0, $trxStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_cb_transaction AS a";
        $sql.= " WHERE a.is_deleted = 0 and a.trx_date BETWEEN ?startdate and ?enddate";
        if ($trxStatus > -1){
            $sql.= " and a.trx_status = ".$trxStatus;
        }
        if ($trxMode > 0){
            $sql.= " and a.trx_mode = ".$trxMode;
        }
        if ($trxTypeId > 0){
            $sql.= " and a.trxtype_id = ".$trxTypeId;
        }
        if ($bankId > 0){
            $sql.= " and (a.db_acc_id = ".$bankId." or a.cr_acc_id = ".$bankId.")";
        }
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        $sql.= " Order By a.trx_date, a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadRekoran($cabangId = 0, $bankId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.id, a.kode_cabang, a.trx_date,a.trx_no,a.trx_descs,a.customer_name,a.reff_no,a.user_id,if(a.db_acc_id = ?bank_id,a.trx_amount,0) as db_amount, if(a.cr_acc_id = ?bank_id,a.trx_amount,0) as cr_amount";
        $sql.= " From vw_cb_transaction a";
        $sql.= " Where a.refftype_id <> 5 and (a.db_acc_id = ?bank_id or a.cr_acc_id = ?bank_id)";
        if($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        $sql.= " and a.is_deleted = 0 and a.trx_date BETWEEN ?startdate and ?enddate Order By a.trx_date, a.xmode Desc;";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?bank_id", $bankId);
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_cb_transaction(cabang_id,trx_date,trx_mode,trxtype_id,bank_id,trx_no,trx_descs,db_acc_id,cr_acc_id,trx_amount,relasi_name,reff_no,trx_status,createby_id,create_time,create_mode)
		VALUES(?cabang_id,?trx_date,?trx_mode,?trxtype_id,?bank_id,?trx_no,?trx_descs,?db_acc_id,?cr_acc_id,?trx_amount,?relasi_name,?reff_no,?trx_status,?createby_id,NOW(),?create_mode)";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?trx_date", $this->TrxDate);
        $this->connector->AddParameter("?trx_mode", $this->TrxMode);
        $this->connector->AddParameter("?trxtype_id", $this->TrxTypeId);
        $this->connector->AddParameter("?bank_id", $this->CoaBankId);
        $this->connector->AddParameter("?trx_no", $this->TrxNo);
        $this->connector->AddParameter("?trx_descs", $this->TrxDescs);
        $this->connector->AddParameter("?db_acc_id", $this->DbAccId);
        $this->connector->AddParameter("?cr_acc_id", $this->CrAccId);
        $this->connector->AddParameter("?trx_amount", $this->TrxAmount);
        $this->connector->AddParameter("?relasi_name", $this->RelasiName);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?trx_status", $this->TrxStatus);
        $this->connector->AddParameter("?createby_id", $this->CreateById);
        $this->connector->AddParameter("?create_mode", $this->CreateMode);
        $rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_cb_transaction SET
    cabang_id = ?cabang_id
    , trx_date = ?trx_date
	, trx_mode = ?trx_mode
	, trxtype_id = ?trxtype_id
	, bank_id = ?bank_id
	, trx_no = ?trx_no
	, trx_descs = ?trx_descs
	, db_acc_id = ?db_acc_id
	, cr_acc_id = ?cr_acc_id
	, trx_amount = ?trx_amount
	, relasi_name = ?relasi_name
	, reff_no = ?reff_no
	, trx_status = ?trx_status
	, updateby_id = ?updateby_id
	, update_time = NOW()
	, create_mode = ?create_mode
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?trx_date", $this->TrxDate);
        $this->connector->AddParameter("?trx_mode", $this->TrxMode);
        $this->connector->AddParameter("?trxtype_id", $this->TrxTypeId);
        $this->connector->AddParameter("?bank_id", $this->CoaBankId);
        $this->connector->AddParameter("?trx_no", $this->TrxNo);
        $this->connector->AddParameter("?trx_descs", $this->TrxDescs);
        $this->connector->AddParameter("?db_acc_id", $this->DbAccId);
        $this->connector->AddParameter("?cr_acc_id", $this->CrAccId);
        $this->connector->AddParameter("?trx_amount", $this->TrxAmount);
        $this->connector->AddParameter("?relasi_name", $this->RelasiName);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?trx_status", $this->TrxStatus);
        $this->connector->AddParameter("?updateby_id", $this->UpdatedById);
        $this->connector->AddParameter("?create_mode", $this->CreateMode);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		//$this->connector->CommandText = "UPDATE vw_cb_transaction SET is_deleted = 1 , update_by = ?user , update_date = NOW() WHERE id = ?id";
        $this->connector->CommandText = "Delete From t_cb_transaction WHERE id = ?id";
		//$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Void($id) {
        //$this->connector->CommandText = "UPDATE vw_cb_transaction SET is_deleted = 1 , update_by = ?user , update_date = NOW() WHERE id = ?id";
        $this->connector->CommandText = "Update vw_t_transaction a Set a.trx_status = 3 WHERE a.id = ?id";
        //$this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetCbTrxNo($entityId){
        $sql = 'Select fc_sys_getdocno(?eti,?txc,?txd) As valout;';
        $txc = null;
        if($this->TrxMode == 1){
           $txc = 'BM';
        }elseif($this->TrxMode == 2){
            $txc = 'BK';
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?eti", $entityId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->TrxDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
           $row = $rs->FetchAssoc();
           $val = $row["valout"];
        }
        return $val;
    }

    public function Approve($id = null){
        $this->connector->CommandText = "SELECT fc_cb_trx_approve(?id,?uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $this->UpdatedById);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function Unapprove($id = null, $uid = null){
        $this->connector->CommandText = "SELECT fc_cb_trx_unapprove(?id,?uid) As valresult;";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $this->UpdatedById);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }

    public function GetSaldoAwal($cabangId = 0, $bankId = 0, $startDate = null){
        $sql = "Select fc_cb_getsaldoawal(?cabang_id,?bank_id,?start_date) As valout;";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $cabangId);
        $this->connector->AddParameter("?bank_id", $bankId);
        $this->connector->AddParameter("?start_date", date('Y-m-d', $startDate));
        $rs = $this->connector->ExecuteQuery();
        $val = 0;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }
}

// End of file: bank.php
