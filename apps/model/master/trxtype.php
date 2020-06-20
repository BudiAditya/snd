<?php

class TrxType extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $EntityId;
	public $CabangId;
	public $TrxMode;
	public $TrxDescs;
	public $TrxAccId;
    public $DefAccId;
    public $RefftypeId;
	public $CreateById;
	public $CreateTime;
	public $UpdatedById;
	public $UpdateTime;

    public function __construct($id = null) {
        parent::__construct();
        if (is_numeric($id)) {
            $this->LoadById($id);
        }
    }

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->EntityId = $row["company_id"];
		$this->CabangId = $row["cabang_id"];
		$this->TrxMode = $row["trx_mode"];
		$this->TrxDescs = $row["trx_descs"];
		$this->TrxAccId = $row["trx_acc_id"];
        $this->DefAccId = $row["def_acc_id"];
        $this->RefftypeId = $row["refftype_id"];
		$this->CreateById = $row["createby_id"];
		$this->CreateTime = $row["create_time"];
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdateTime = $row["update_time"];
	}

	/**
	 * @param string $orderBy
	 * @return TrxType[]
	 */
	public function LoadAll($companyId,$orderBy = "a.trx_mode, a.id") {
		$this->connector->CommandText = "SELECT a.* FROM sys_trxtype AS a WHERE a.company_id = $companyId And a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TrxType();
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
		$this->connector->CommandText = "SELECT a.* FROM sys_trxtype AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
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
	public function LoadByTrxAccId($cabangId,$accNo) {
		$this->connector->CommandText = "SELECT a.* FROM sys_trxtype AS a WHERE a.trx_acc_id = ?acc_id";
		$this->connector->AddParameter("?acc_id", $accNo);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByTrxMode($companyId,$trxMode) {
        $this->connector->CommandText = "SELECT a.* FROM sys_trxtype AS a WHERE a.company_id = ?company_id And a.trx_mode = ?trx_mode";
        $this->connector->AddParameter("?company_id", $companyId);
		$this->connector->AddParameter("?trx_mode", $trxMode);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new TrxType();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO sys_trxtype(company_id, cabang_id, trx_mode, trx_descs, trx_acc_id, refftype_id, createby_id, create_time, def_acc_id) VALUES(?company_id, ?cabang_id, ?trx_mode, ?trx_descs, ?trx_acc_id, ?refftype_id, ?createby_id, NOW(), ?def_acc_id)";
		$this->connector->AddParameter("?company_id", $this->EntityId);
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?trx_mode", $this->TrxMode);
		$this->connector->AddParameter("?trx_descs", $this->TrxDescs);
		$this->connector->AddParameter("?trx_acc_id", $this->TrxAccId);
        $this->connector->AddParameter("?def_acc_id", $this->DefAccId);
        $this->connector->AddParameter("?refftype_id", $this->RefftypeId);
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
"UPDATE sys_trxtype SET
      company_id = ?company_id
	, cabang_id = ?cabang_id
	, trx_mode = ?trx_mode
	, trx_descs = ?trx_descs
	, trx_acc_id = ?trx_acc_id
	, def_acc_id = ?def_acc_id
	, refftype_id = ?refftype_id
	, updateby_id = ?updateby_id
	, update_time = NOW()
WHERE id = ?id";
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?company_id", $this->EntityId);
        $this->connector->AddParameter("?trx_mode", $this->TrxMode);
        $this->connector->AddParameter("?trx_descs", $this->TrxDescs);
        $this->connector->AddParameter("?trx_acc_id", $this->TrxAccId);
        $this->connector->AddParameter("?def_acc_id", $this->DefAccId);
        $this->connector->AddParameter("?refftype_id", $this->RefftypeId);
        $this->connector->AddParameter("?updateby_id", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "Delete From sys_trxtype WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}

// End of file: bank.php
