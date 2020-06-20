<?php

class Warkat extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $CabangId;
	public $WarkatDate;
    public $WarkatNo;
    public $DueDate;
    public $WarkatBankId;
    public $WarkatTypeId;
    public $WarkatDescs;
    public $WarkatAmount;
    public $ReffNo;
    public $WarkatMode;
    public $WarkatStatus;
    public $CustomerId;
    public $SupplierId;
    public $InVoucherNo;
    public $ProcessVoucherNo;
    public $ReffAccId;
    public $ProcessDate;
    public $CreateById;
    public $CreateTime;
	public $UpdateById;
	public $UpdateTime;
    public $ReasonId;
    public $CreateMode;
    public $CustCode;
    public $CustName;
    public $SupCode;
    public $SupName;

    public function __construct($id = null) {
        parent::__construct();
        if (is_numeric($id)) {
            $this->LoadById($id);
        }
    }

    public function FormatWarkatDate($format = HUMAN_DATE) {
        return is_int($this->WarkatDate) ? date($format, $this->WarkatDate) : date($format, strtotime(date('Y-m-d')));
    }

    public function FormatDueDate($format = HUMAN_DATE) {
        return is_int($this->DueDate) ? date($format, $this->DueDate) : null;
    }

    public function FormatProcessDate($format = HUMAN_DATE) {
        return is_int($this->ProcessDate) ? date($format, $this->ProcessDate) : date($format, strtotime(date('Y-m-d')));
    }

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
        $this->EntityId = $row["company_id"];
        $this->CabangId = $row["cabang_id"];
        $this->WarkatDate = strtotime($row["warkat_date"]);
        $this->WarkatNo = $row["warkat_no"];
        $this->DueDate = strtotime($row["due_date"]);
        $this->WarkatDescs = $row["warkat_descs"];
        $this->WarkatAmount = $row["warkat_amount"];
        $this->ReffNo = $row["reff_no"];
        $this->WarkatMode = $row["warkat_mode"];
        $this->WarkatStatus = $row["warkat_status"];
        $this->CustomerId = $row["customer_id"];
        $this->SupplierId = $row["supplier_id"];
        $this->InVoucherNo = $row["in_voucher_no"];
        $this->ProcessVoucherNo = $row["process_voucher_no"];
        $this->WarkatBankId = $row["warkat_bank_id"];
        $this->WarkatTypeId = $row["warkat_type_id"];
        $this->ReasonId = $row["reason_id"];
        $this->ReffAccId = $row["kontra_akun"];
        $this->ProcessDate = strtotime($row["process_date"]);
        $this->CreateById = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdateById = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
        $this->CreateMode = $row["create_mode"];
        $this->CustCode = $row["cust_code"];
        $this->CustName = $row["cust_name"];
        $this->SupCode = $row["supp_code"];
        $this->SupName = $row["supp_name"];
	}

	/**
	 * @param string $orderBy
	 * @return TrxType[]
	 */
	public function LoadAll($orderBy = "a.warkat_no") {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_warkat_list AS a ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Warkat();
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
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_warkat_list AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByWarkatNo($WarkatNo) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_warkat_list AS a WHERE a.warkat_no = ?warkatNo";
        $this->connector->AddParameter("?warkatNo", $WarkatNo);
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
	public function LoadByEntityId($companyId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_cb_warkat_list AS a WHERE a.company_id = ?companyId";
		$this->connector->AddParameter("?companyId", $companyId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warkat();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
	}

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_warkat_list AS a WHERE a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warkat();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadByCabangWarkatTypeId($cabangId,$WarkatTypeId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cb_warkat_list AS a WHERE a.cabang_id = ?cabangId and a.warkat_type_id = ?WarkatTypeId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?WarkatTypeId", $WarkatTypeId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Warkat();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_cb_warkat(cabang_id,warkat_no,warkat_mode,warkat_bank_id,warkat_descs,warkat_date,warkat_type_id,due_date,warkat_amount,customer_id,supplier_id,reff_no,warkat_status,reason_id,in_voucher_no,reff_acc_id,process_date,createby_id,create_time)
		VALUES(?cabang_id,?warkat_no,?warkat_mode,?warkat_bank_id,?warkat_descs,?warkat_date,?warkat_type_id,?due_date,?warkat_amount,?customer_id,?supplier_id,?reff_no,?warkat_status,?reason_id,?voucher_no,?reff_acc_id,?process_date,?createby_id,NOW())";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?warkat_no", $this->WarkatNo);
        $this->connector->AddParameter("?warkat_mode", $this->WarkatMode);
        $this->connector->AddParameter("?warkat_bank_id", $this->WarkatBankId);
        $this->connector->AddParameter("?warkat_descs", $this->WarkatDescs);
        $this->connector->AddParameter("?warkat_date", $this->WarkatDate);
        $this->connector->AddParameter("?warkat_type_id", $this->WarkatTypeId);
        $this->connector->AddParameter("?due_date", $this->DueDate);
        $this->connector->AddParameter("?warkat_amount", $this->WarkatAmount);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?warkat_status", $this->WarkatStatus);
        $this->connector->AddParameter("?reason_id", $this->ReasonId);
        $this->connector->AddParameter("?voucher_no", $this->InVoucherNo);
        $this->connector->AddParameter("?reff_acc_id", $this->ReffAccId);
        $this->connector->AddParameter("?process_date", $this->ProcessDate);
        $this->connector->AddParameter("?createby_id", $this->CreateById);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}
		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_cb_warkat SET
    cabang_id = ?cabang_id
    , warkat_no = ?warkat_no
    , warkat_mode = ?warkat_mode
    , warkat_bank_id = ?warkat_bank_id
    , warkat_descs = ?warkat_descs
    , warkat_date = ?warkat_date
	, warkat_type_id = ?warkat_type_id
	, due_date = ?due_date
	, warkat_amount = ?warkat_amount
	, customer_id = ?customer_id
	, supplier_id = ?supplier_id
	, reff_no = ?reff_no
	, warkat_status = ?warkat_status
	, reason_id = ?reason_id
	, in_voucher_no = ?voucher_no
	, reff_acc_id = ?reff_acc_id
	, process_date = ?process_date
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?warkat_no", $this->WarkatNo);
        $this->connector->AddParameter("?warkat_mode", $this->WarkatMode);
        $this->connector->AddParameter("?warkat_bank_id", $this->WarkatBankId);
        $this->connector->AddParameter("?warkat_descs", $this->WarkatDescs);
        $this->connector->AddParameter("?warkat_date", $this->WarkatDate);
        $this->connector->AddParameter("?warkat_type_id", $this->WarkatTypeId);
        $this->connector->AddParameter("?due_date", $this->DueDate);
        $this->connector->AddParameter("?warkat_amount", $this->WarkatAmount);
        $this->connector->AddParameter("?customer_id", $this->CustomerId);
        $this->connector->AddParameter("?supplier_id", $this->SupplierId);
        $this->connector->AddParameter("?reff_no", $this->ReffNo);
        $this->connector->AddParameter("?warkat_status", $this->WarkatStatus);
        $this->connector->AddParameter("?reason_id", $this->ReasonId);
        $this->connector->AddParameter("?voucher_no", $this->InVoucherNo);
        $this->connector->AddParameter("?reff_acc_id", $this->ReffAccId);
        $this->connector->AddParameter("?process_date", $this->ProcessDate);
        $this->connector->AddParameter("?updateby_id", $this->UpdateById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Process($id = 0,$processType = 0) {
        $this->connector->CommandText =
            "UPDATE t_cb_Warkat SET
    warkat_descs = ?warkat_descs
    , reff_acc_id = ?reff_acc_id
	, process_date = ?process_date
	, reason_id = ?reason_id
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
        $this->connector->AddParameter("?warkat_descs", $this->WarkatDescs);
        $this->connector->AddParameter("?reff_acc_id", $this->ReffAccId);
        $this->connector->AddParameter("?reason_id", $this->ReasonId);
        $this->connector->AddParameter("?process_date", $this->ProcessDate);
        $this->connector->AddParameter("?updateby_id", $this->UpdateById);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs){
            if ($processType == 1) {
                $this->connector->CommandText = "SELECT fc_cb_warkat_approve(?id,?uid) As valresult;";
                $this->connector->AddParameter("?id", $id);
                $this->connector->AddParameter("?uid", $this->UpdateById);
                $rs = $this->connector->ExecuteQuery();
                $row = $rs->FetchAssoc();
                return strval($row["valresult"]);
            }elseif($processType == 2) {
                $this->connector->CommandText = "Update t_cb_warkat a Set a.warkat_status = 2 Where a.id = ?id";
                $this->connector->AddParameter("?id", $id);
                $rs = $this->connector->ExecuteNonQuery();
            }else{
                return $rs;
            }
        }
    }

	public function Delete($id) {
		$this->connector->CommandText = "Delete From t_cb_warkat WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Load4Reports($companyId,$cabangId = 0, $trxMode = 0, $kasbankId = 0, $bankId = 0, $trxStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_cb_warkat_list AS a";
        $sql.= " WHERE a.warkat_date BETWEEN ?startdate and ?enddate";
        if ($trxStatus > -1){
            $sql.= " and a.warkat_status = ".$trxStatus;
        }
        if ($trxMode > 0){
            $sql.= " and a.warkat_mode = ".$trxMode;
        }
        if ($kasbankId > 0){
            $sql.= " and a.reff_acc_id = ".$kasbankId;
        }
        if ($bankId > 0){
            $sql.= " and a.warkat_bank_id = ".$bankId;
        }
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        $sql.= " Order By a.warkat_date, a.warkat_no";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetWarkatToDate($id,$companyId = 0,$cabangId = 0){
        $sql = "Select coalesce(sum(a.warkat_amount),0) as valresult From vw_cb_warkat_list as a Where a.warkat_mode = 1 And a.customer_id = ?id";
        if ($companyId > 0){
            $sql.= " And a.company_id = ".$companyId;
        }
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ".$cabangId;
        }
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["valresult"]);
    }
}

// End of file: bank.php
