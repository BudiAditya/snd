<?php
class ItemEntity extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CompanyId = 1;
	public $EntityCode;
	public $EntityName;
	public $RevAccId = 0;
	public $IvtAccId = 0;
	public $SlsDiscAccId = 0;
	public $RetSlsAccId = 0;
	public $HppAccId = 0;
	public $ArAccId = 0;
	public $ApAccId = 0;
    public $RetPrcAccId = 0;
	public $PrcDiscAccId = 0;
	public $ChartColor;
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
        $this->IsDeleted = $row["is_deleted"];
        $this->CompanyId = $row["company_id"];
		$this->EntityCode = $row["entity_code"];
		$this->EntityName = $row["entity_name"];
		$this->RevAccId = $row["rev_acc_id"];
        $this->IvtAccId = $row["ivt_acc_id"];
        $this->SlsDiscAccId = $row["sls_disc_acc_id"];
        $this->RetSlsAccId = $row["ret_sls_acc_id"];
        $this->HppAccId = $row["hpp_acc_id"];
        $this->ArAccId = $row["ar_acc_id"];
        $this->ApAccId = $row["ap_acc_id"];
        $this->PrcDiscAccId = $row["prc_disc_acc_id"];
        $this->RetPrcAccId = $row["ret_prc_acc_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.entity_code") {
		$this->connector->CommandText = "SELECT a.* FROM m_item_entity AS a Where  a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemEntity();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCompanyId($companyId = 0,$orderBy = "a.entity_code",$idFilter = 0) {
	    $sql = "SELECT a.* FROM m_item_entity AS a Where a.company_id = $companyId And a.is_deleted = 0 ";
	    if ($idFilter > 0){
	        $sql.= " And id = $idFilter";
        }
	    $sql.= " ORDER BY $orderBy";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ItemEntity();
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
		$this->connector->CommandText = "SELECT a.* FROM m_item_entity AS a WHERE a.id = ?id And a.is_deleted = 0";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	public function FindByCode($eCode) {
		$this->connector->CommandText = "SELECT a.* FROM m_item_entity AS a WHERE a.entity_code = ?eCode And a.is_deleted = 0";
		$this->connector->AddParameter("?eCode", $eCode);
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
		$this->connector->CommandText = 'INSERT INTO m_item_entity(ret_prc_acc_id,company_id, entity_code, entity_name, rev_acc_id, ivt_acc_id, sls_disc_acc_id, ret_sls_acc_id, hpp_acc_id, ar_acc_id, ap_acc_id, prc_disc_acc_id, createby_id, create_time) VALUES(?ret_prc_acc_id,?company_id, ?entity_code, ?entity_name, ?rev_acc_id, ?ivt_acc_id, ?sls_disc_acc_id, ?ret_sls_acc_id, ?hpp_acc_id, ?ar_acc_id, ?ap_acc_id, ?prc_disc_acc_id,?createby_id,now())';
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?entity_code", $this->EntityCode);
        $this->connector->AddParameter("?entity_name", $this->EntityName);
		$this->connector->AddParameter("?rev_acc_id", $this->RevAccId);
        $this->connector->AddParameter("?ivt_acc_id", $this->IvtAccId);
        $this->connector->AddParameter("?sls_disc_acc_id", $this->SlsDiscAccId);
        $this->connector->AddParameter("?ret_sls_acc_id", $this->RetSlsAccId);
        $this->connector->AddParameter("?ret_prc_acc_id", $this->RetPrcAccId);
        $this->connector->AddParameter("?hpp_acc_id", $this->HppAccId);
        $this->connector->AddParameter("?ar_acc_id", $this->ArAccId);
        $this->connector->AddParameter("?ap_acc_id", $this->ApAccId);
        $this->connector->AddParameter("?prc_disc_acc_id", $this->PrcDiscAccId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE m_item_entity 
SET company_id = ?company_id,
entity_code = ?entity_code, 
entity_name = ?entity_name, 
rev_acc_id = ?rev_acc_id, 
ivt_acc_id = ?ivt_acc_id,
sls_disc_acc_id = ?sls_disc_acc_id,
ret_sls_acc_id = ?ret_sls_acc_id,
ret_prc_acc_id = ?ret_prc_acc_id,
hpp_acc_id = ?hpp_acc_id,
ar_acc_id = ?ar_acc_id,
ap_acc_id = ?ap_acc_id,
prc_disc_acc_id = ?prc_disc_acc_id,
updateby_id = ?updateby_id,
update_time = now() 

WHERE id = ?id';
        $this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?entity_code", $this->EntityCode);
        $this->connector->AddParameter("?entity_name", $this->EntityName);
        $this->connector->AddParameter("?rev_acc_id", $this->RevAccId);
        $this->connector->AddParameter("?ivt_acc_id", $this->IvtAccId);
        $this->connector->AddParameter("?sls_disc_acc_id", $this->SlsDiscAccId);
        $this->connector->AddParameter("?ret_sls_acc_id", $this->RetSlsAccId);
        $this->connector->AddParameter("?ret_prc_acc_id", $this->RetPrcAccId);
        $this->connector->AddParameter("?hpp_acc_id", $this->HppAccId);
        $this->connector->AddParameter("?ar_acc_id", $this->ArAccId);
        $this->connector->AddParameter("?ap_acc_id", $this->ApAccId);
        $this->connector->AddParameter("?prc_disc_acc_id", $this->PrcDiscAccId);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'Update m_item_entity a Set a.is_deleted = 1 WHERE a.id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From m_item_entity WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

}
