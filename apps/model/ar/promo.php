<?php
class Promo extends EntityBase {
	public $Id;
	public $IsDeleted = 0;
	public $CabangId = 0;
	public $PromoType;
	public $PromoDescs;
	public $ItemId = 0;
	public $CustypeId = 0;
	public $AreaId = 0;
    public $ZoneId = 0;
	public $StartDate;
	public $EndDate;
	public $MinQty = 0;
	public $MaxQty = 0;
	public $MinAmount = 0;
	public $MaxAmount = 0;
	public $DiscPct = 0;
	public $Poin = 0;
	public $QtyBonus = 0;
	public $ItemIdBonus = 0;
	public $IsKelipatan = 0;
	public $IsAktif = 1;
	public $ApprovebyId = 0;
    public $CreatebyId = 0;
    public $UpdatebyId = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
        $this->IsDeleted = $row["is_deleted"];
        $this->CabangId = $row["cabang_id"];
		$this->PromoType = $row["promo_type"];
		$this->PromoDescs = $row["promo_descs"];
		$this->ItemId = $row["item_id"];
        $this->CustypeId = $row["custype_id"];
        $this->AreaId = $row["area_id"];
        $this->ZoneId = $row["zone_id"];
        $this->StartDate = strtotime($row["start_date"]);
        $this->EndDate = strtotime($row["end_date"]);
        $this->MinQty = $row["min_qty"];
        $this->MaxQty = $row["max_qty"];
        $this->MinAmount = $row["min_amount"];
        $this->MaxAmount = $row["max_amount"];
        $this->DiscPct = $row["disc_pct"];
        $this->Poin = $row["poin"];
        $this->QtyBonus = $row["qty_bonus"];
        $this->ItemIdBonus = $row["item_id_bonus"];
        $this->IsKelipatan = $row["is_kelipatan"];
        $this->IsAktif = $row["is_aktif"];
        $this->ApprovebyId = $row["approveby_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

    public function FormatStartDate($format = HUMAN_DATE) {
        return is_int($this->StartDate) ? date($format, $this->StartDate) : date($format, strtotime(date('Y-m-d')));
    }

    public function FormatEndDate($format = HUMAN_DATE) {
        return is_int($this->EndDate) ? date($format, $this->EndDate) : date($format, strtotime(date('Y-m-d')));
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($orderBy = "a.promo_type") {
		$this->connector->CommandText = "SELECT a.* FROM t_ar_promo AS a Where  a.is_deleted = 0 ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Promo();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByCabangId($cabangId = 0,$orderBy = "a.promo_type") {
        $this->connector->CommandText = "SELECT a.* FROM t_ar_promo AS a Where a.cabang_id = $cabangId And a.is_deleted = 0 ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Promo();
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
		$this->connector->CommandText = "SELECT a.* FROM t_ar_promo AS a WHERE a.id = ?id And a.is_deleted = 0";
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
		$this->connector->CommandText = "SELECT a.* FROM t_ar_promo AS a WHERE a.promo_type = ?eCode And a.is_deleted = 0";
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
		$this->connector->CommandText = 'INSERT INTO t_ar_promo(zone_id,cabang_id, promo_type, promo_descs, item_id, custype_id, area_id, start_date, end_date, min_qty, max_qty, min_amount, max_amount, disc_pct, poin, qty_bonus, item_id_bonus, is_kelipatan, is_aktif, createby_id, create_time) VALUES(?zone_id,?cabang_id, ?promo_type, ?promo_descs, ?item_id, ?custype_id, ?area_id, ?start_date, ?end_date, ?min_qty, ?max_qty, ?min_amount, ?max_amount, ?disc_pct, ?poin, ?qty_bonus, ?item_id_bonus, ?is_kelipatan, ?is_aktif, ?createby_id, now())';
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?promo_type", $this->PromoType, "varchar");
        $this->connector->AddParameter("?promo_descs", $this->PromoDescs);
		$this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?custype_id", $this->CustypeId);
        $this->connector->AddParameter("?area_id", $this->AreaId);
        $this->connector->AddParameter("?zone_id", $this->ZoneId);
        $this->connector->AddParameter("?start_date", $this->StartDate);
        $this->connector->AddParameter("?end_date", $this->EndDate);
        $this->connector->AddParameter("?min_qty", $this->MinQty);
        $this->connector->AddParameter("?max_qty", $this->MaxQty);
        $this->connector->AddParameter("?min_amount", $this->MinAmount);
        $this->connector->AddParameter("?max_amount", $this->MaxAmount);
        $this->connector->AddParameter("?disc_pct", $this->DiscPct);
        $this->connector->AddParameter("?poin", $this->Poin);
        $this->connector->AddParameter("?qty_bonus", $this->QtyBonus);
        $this->connector->AddParameter("?item_id_bonus", $this->ItemIdBonus);
        $this->connector->AddParameter("?is_kelipatan", $this->IsKelipatan);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
		$this->connector->CommandText = 'UPDATE t_ar_promo 
SET cabang_id = ?cabang_id
,promo_type = ?promo_type
,promo_descs = ?promo_descs
,item_id = ?item_id
,custype_id = ?custype_id
,area_id = ?area_id
,zone_id = ?zone_id
,start_date = ?start_date
,end_date = ?end_date
,min_qty = ?min_qty
,max_qty = ?max_qty
,min_amount = ?min_amount
,disc_pct = ?disc_pct
,poin = ?poin
,qty_bonus = ?qty_bonus
,item_id_bonus = ?item_id_bonus
,is_kelipatan = ?is_kelipatan
,is_aktif = ?is_aktif
,updateby_id = ?updateby_id
,update_time = now() 
WHERE id = ?id';
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?promo_type", $this->PromoType, "varchar");
        $this->connector->AddParameter("?promo_descs", $this->PromoDescs);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?custype_id", $this->CustypeId);
        $this->connector->AddParameter("?area_id", $this->AreaId);
        $this->connector->AddParameter("?zone_id", $this->ZoneId);
        $this->connector->AddParameter("?start_date", $this->StartDate);
        $this->connector->AddParameter("?end_date", $this->EndDate);
        $this->connector->AddParameter("?min_qty", $this->MinQty);
        $this->connector->AddParameter("?max_qty", $this->MaxQty);
        $this->connector->AddParameter("?min_amount", $this->MinAmount);
        $this->connector->AddParameter("?max_amount", $this->MaxAmount);
        $this->connector->AddParameter("?disc_pct", $this->DiscPct);
        $this->connector->AddParameter("?poin", $this->Poin);
        $this->connector->AddParameter("?qty_bonus", $this->QtyBonus);
        $this->connector->AddParameter("?item_id_bonus", $this->ItemIdBonus);
        $this->connector->AddParameter("?is_kelipatan", $this->IsKelipatan);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = 'Update t_ar_promo a Set a.is_deleted = 1 WHERE a.id = ?id';
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function Approval($id) {
        $this->connector->CommandText = 'Update t_ar_promo a Set a.approvedby_id = ?approvedby_id, a.approve_time = now() WHERE a.id = ?id';
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?approvedby_id", $this->ApprovebyId);
        return $this->connector->ExecuteNonQuery();
    }

    public function Unapproval($id) {
        $this->connector->CommandText = 'Update t_ar_promo a Set a.approvedby_id = 0 a.approve_time = null WHERE a.id = ?id';
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?approvedby_id", $this->ApprovebyId);
        return $this->connector->ExecuteNonQuery();
    }

    public function Delete($id) {
        $this->connector->CommandText = 'Delete From t_ar_promo WHERE id = ?id';
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function getPromoTypeList(){
        $this->connector->CommandText = "Select a.`code` as promo_type,a.short_desc as promo_descs From sys_status_code a Where a.`key` = 'type_promo' Order By a.`urutan`";
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}
