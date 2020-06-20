<?php
class DocType extends EntityBase {
	public $Id;
	public $DocCode;
	public $Description;
	public $ModuleId;
	public $ModuleCd;
	public $AccVoucherId;
	public $VoucherCd;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->DocCode = $row["trx_code"];
		$this->Description = $row["keterangan"];
		$this->ModuleId = $row["module_id"];
		$this->ModuleCd = $row["xmodule"];
		$this->AccVoucherId = $row["vouchertype_id"];
		$this->VoucherCd = $row["voucher_code"];
	}

	/**
	 * @param string $orderBy
	 * @return DocType[]
	 */
	public function LoadAll($orderBy = "a.doc_code") {
		$this->connector->CommandText = "SELECT a.*, b.id as module_id, b.module_cd, c.voucher_cd FROM sys_doc_type AS a JOIN sys_module AS b ON a.xmodule = b.module_cd LEFT JOIN sys_voucher_type AS c ON a.vouchertype_id = c.id ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new DocType();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}

		return $result;
	}

    public function LoadHaveVoucher($orderBy = "a.trx_code") {
        $this->connector->CommandText = "SELECT a.* FROM sys_doc_type AS a Where a.vouchertype_id > 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new DocType();
                $temp->FillProperties($row);

                $result[] = $temp;
            }
        }

        return $result;
    }

	/**
	 * @param int $id
	 * @return DocType
	 */
	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.module_cd, c.voucher_cd
FROM sys_doctype AS a
	JOIN sys_module AS b ON a.module_id = b.id
	LEFT JOIN sys_voucher_type AS c ON a.vouchertype_id = c.id
WHERE a.id = ?id";
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
	 * @return DocType
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param string $code
	 * @return DocType
	 */
	public function FindByCode($code) {
        $this->connector->CommandText =
"SELECT a.*, b.module_cd, c.voucher_cd
FROM sys_doctype AS a
	JOIN sys_module AS b ON a.module_id = b.id
	LEFT JOIN sys_voucher_type AS c ON a.vouchertype_id = c.id
WHERE a.doc_code = ?code";
        $this->connector->AddParameter("?code", $code);
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
'INSERT INTO sys_doctype(doc_code,description,module_id,vouchertype_id) VALUES(?doc_code,?description,?module_id,?vouchertype_id)';
		$this->connector->AddParameter("?doc_code", $this->DocCode);
		$this->connector->AddParameter("?description", $this->Description);
		$this->connector->AddParameter("?module_id", $this->ModuleId);
		$this->connector->AddParameter("?vouchertype_id", $this->AccVoucherId);

		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText =
'UPDATE sys_doctype SET
	doc_code = ?doc_code,
	description = ?description,
	module_id = ?module_id,
	vouchertype_id = ?vouchertype_id
WHERE id = ?id';
		$this->connector->AddParameter("?doc_code", $this->DocCode);
		$this->connector->AddParameter("?description", $this->Description);
		$this->connector->AddParameter("?module_id", $this->ModuleId);
		$this->connector->AddParameter("?vouchertype_id", $this->AccVoucherId);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = 'DELETE FROM sys_doctype WHERE id = ?id';
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

}
