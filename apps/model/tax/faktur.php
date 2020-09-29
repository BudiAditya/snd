<?php
class Faktur extends EntityBase {

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function LoadAll($companyId,$orderBy = "a.nomor_faktur", $includeDeleted = false) {
		$this->connector->CommandText = "SELECT a.* FROM vw_fp_master AS a Where a.company_id = $companyId ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		return $rs;
	}

    public function LoadAllByMonth($companyId,$tahun,$bulan,$ptype = -1,$orderBy = "a.nomor_faktur") {
	    if ($ptype > -1){
	        $sql = "SELECT a.* FROM vw_fp_master AS a Where a.company_id = $companyId And a.masa_pajak = $bulan And a.tahun_pajak = $tahun And a.payment_type = $ptype ORDER BY $orderBy";
        }else{
            $sql = "SELECT a.* FROM vw_fp_master AS a Where a.company_id = $companyId And a.masa_pajak = $bulan And a.tahun_pajak = $tahun ORDER BY $orderBy";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

	/**
	 * @param int $id
	 * @return Location
	 */
	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.* FROM vw_fp_master AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		return $rs;
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Update t_ar_invoice_master a Set a.fp_date = null, a.nsf_pajak = null Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function LoadDetailByFakturId($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_fp_detail AS a JOIN vw_fp_master b ON a.invoice_id = b.id WHERE b.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        return $rs;
    }

    public function CreateFakturPPN($id,$tgl,$nsf) {
        $this->connector->CommandText = 'Update t_ar_invoice_master a Set a.fp_date = ?tgl, a.nsf_pajak = ?nsf Where a.id = ?id';
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?tgl", $tgl);
        $this->connector->AddParameter("?nsf", $nsf,"varchar");
        return $this->connector->ExecuteNonQuery();
    }
}
