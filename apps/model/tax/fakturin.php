<?php
class FakturIn extends EntityBase {

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function LoadAll($companyId,$orderBy = "a.nomor_faktur", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM vw_fp_in_master AS a Where a.company_id = $companyId ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		return $rs;
	}

    public function LoadAllByMonth($companyId,$tahun,$bulan,$orderBy = "a.nomor_faktur", $includeDeleted = false) {
        $this->connector->CommandText = "SELECT a.* FROM vw_fp_in_master AS a Where a.company_id = $companyId And a.masa_pajak = $bulan And a.tahun_pajak = $tahun ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

	/**
	 * @param int $id
	 * @return Location
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_fp_in_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		return $rs;
	}

    public function LoadDetailByFakturInId($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_fp_in_detail AS a JOIN vw_fp_in_master b ON a.grn_id = b.id WHERE b.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        return $rs;
    }
}
