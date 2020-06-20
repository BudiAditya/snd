<?php

class KasBank extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CompanyId = 1;
	public $CabangId = 0;
	public $AtsNama;
	public $BankName;
	public $Branch;
	public $Address;
	public $NoRekening;
	public $CurrencyCode = "IDR";
	public $TrxAccId = 0;
	public $CostAccId = 0;
	public $RevAccId = 0;
	public $CreateById;
	public $CreateTime;
	public $UpdateById;
	public $UpdateTime;
	public $IsAktif = 0;
	public $BankId = 0;
	public $TrxAccNo;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CompanyId = $row["company_id"];
		$this->CabangId = $row["cabang_id"];
		$this->BankName = $row["bank_name"];
        $this->AtsNama = $row["ats_nama"];
        $this->BankId = $row["bank_id"];
		$this->Branch = $row["branch"];
		$this->Address = $row["address"];
		$this->NoRekening = $row["rek_no"];
		$this->CurrencyCode = $row["currency_cd"];
		$this->TrxAccId = $row["trx_acc_id"];
		$this->CostAccId = $row["cost_acc_id"];
		$this->RevAccId = $row["rev_acc_id"];
		$this->CreateById = $row["createby_id"];
		$this->CreateTime = $row["create_time"];
		$this->UpdateById = $row["updateby_id"];
		$this->UpdateTime = $row["update_time"];
		$this->TrxAccNo = $row["trx_acc_code"];
	}

	/**
	 * @param string $orderBy
	 * @return KasBank[]
	 */
	public function LoadAll($orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.* FROM vw_m_kasbank AS a WHERE a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new KasBank();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function LoadAllNonCash($sbu,$orderBy = "a.bank_name") {
		$this->connector->CommandText = "SELECT a.* FROM vw_m_kasbank AS a WHERE a.cabang_id = $sbu And a.bank_name <> 'TUNAI' and a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new KasBank();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}
	/**
	 * @param int $sbu
	 * @param string $orderBy
	 * @return KasBank[]
	 */
	public function LoadByCompanyId($sbu, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.* FROM vw_m_kasbank AS a WHERE a.is_deleted = 0 AND a.company_id = ?Entity ORDER BY $orderBy";
		$this->connector->AddParameter("?Entity", $sbu);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new KasBank();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function LoadByCabangId($cbi, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.* FROM vw_m_kasbank AS a WHERE a.is_deleted = 0 AND a.cabang_id = ?cabang_id ORDER BY $orderBy";
		$this->connector->AddParameter("?cabang_id", $cbi);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new KasBank();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return KasBank
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_m_kasbank AS a WHERE a.id = ?id";
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
	 * @return KasBank
	 */
	public function LoadByAccId($sbu, $accId) {
		$this->connector->CommandText = "SELECT a.* FROM vw_m_kasbank AS a WHERE a.company_id = ?Entity AND a.trx_acc_id = ?id And a.is_deleted = 0";
		$this->connector->AddParameter("?Entity", $sbu);
		$this->connector->AddParameter("?id", $accId);

		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO m_kasbank(ats_nama,bank_id,is_aktif,company_id, cabang_id, bank_name, branch, address, rek_no, currency_cd, trx_acc_id, cost_acc_id, rev_acc_id, createby_id, create_time)
VALUES(?ats_nama,?bank_id,?is_aktif,?Entity, ?cabang_id, ?bank_name, ?branch, ?address, ?noRek, ?currency, ?accId, ?costAccId, ?revAccId, ?user, NOW())";

		$this->connector->AddParameter("?Entity", $this->CompanyId);
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?bank_name", $this->BankName);
		$this->connector->AddParameter("?branch", $this->Branch);
        $this->connector->AddParameter("?ats_nama", $this->AtsNama);
		$this->connector->AddParameter("?address", $this->Address);
		$this->connector->AddParameter("?noRek", $this->NoRekening, "varchar");
		$this->connector->AddParameter("?currency", $this->CurrencyCode, "varchar");
		$this->connector->AddParameter("?accId", $this->TrxAccId);
        $this->connector->AddParameter("?bank_id", $this->BankId);
		$this->connector->AddParameter("?costAccId", $this->CostAccId);
		$this->connector->AddParameter("?revAccId", $this->RevAccId);
		$this->connector->AddParameter("?user", $this->CreateById);
		$this->connector->AddParameter("?is_aktif", $this->IsAktif);

		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE m_kasbank SET
	bank_name = ?bank_name
	, branch = ?branch
	, address = ?address
	, rek_no = ?noRek
	, currency_cd = ?currency
	, trx_acc_id = ?accId
	, cost_acc_id = ?costAccId
	, rev_acc_id = ?revAccId
	, updateby_id = ?user
	, update_time = NOW()
	, company_id = ?company_id
	, cabang_id = ?cabang_id
	, bank_id = ?bank_id
	, ats_nama = ?ats_nama
WHERE id = ?id";
		$this->connector->AddParameter("?company_id", $this->CompanyId);
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
		$this->connector->AddParameter("?bank_name", $this->BankName);
		$this->connector->AddParameter("?branch", $this->Branch);
        $this->connector->AddParameter("?ats_nama", $this->AtsNama);
		$this->connector->AddParameter("?address", $this->Address);
		$this->connector->AddParameter("?noRek", $this->NoRekening, "varchar");
		$this->connector->AddParameter("?currency", $this->CurrencyCode, "varchar");
		$this->connector->AddParameter("?accId", $this->TrxAccId);
        $this->connector->AddParameter("?bank_id", $this->BankId);
		$this->connector->AddParameter("?costAccId", $this->CostAccId);
		$this->connector->AddParameter("?revAccId", $this->RevAccId);
		$this->connector->AddParameter("?user", $this->UpdateById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE m_kasbank SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdateById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}
}

// End of file: bank.php
