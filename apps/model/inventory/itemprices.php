<?php
class ItemPrices extends EntityBase {
	public $Id;
	public $CompanyId;
	public $CabangId;
    public $CabangCode;
    public $EntityCode;
	public $ItemId = 0;
    public $ItemCode;
    public $ItemName;
    public $UomCode;
    public $LuomCode;
    public $SuomCode;
    public $SuomQty;
    public $PriceDate;
    public $PurchasePrice = 0;
    public $Hpp = 0;
    public $pZone1 = 0;
    public $pZone2 = 0;
    public $pZone3 = 0;
    public $pZone4 = 0;
    public $pZone5 = 0;
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
		$this->CompanyId = $row["company_id"];
        $this->CabangId = $row["cabang_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->EntityCode = $row["entity_code"];
		$this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
        $this->ItemName = $row["item_name"];
        $this->UomCode = $row["uom_code"];
        $this->LuomCode = $row["l_uom_code"];
        $this->SuomCode = $row["s_uom_code"];
        $this->SuomQty = $row["s_uom_qty"];
        $this->PriceDate = strtotime($row["price_date"]);
        $this->PurchasePrice = $row["purchase_price"];
        $this->Hpp = $row["hpp"];
        $this->pZone1 = $row["zone_1"];
        $this->pZone2 = $row["zone_2"];
        $this->pZone3 = $row["zone_3"];
        $this->pZone4 = $row["zone_4"];
        $this->pZone5 = $row["zone_5"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

    public function FormatPriceDate($format = HUMAN_DATE) {
        return is_int($this->PriceDate) ? date($format, $this->PriceDate) : date($format, strtotime(date('Y-m-d')));
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($cabangId = 0,$orderBy = "a.cabang_id, a.item_code") {
        $sqx = "SELECT a.* FROM vw_m_item_prices AS a";
        if ($cabangId > 0) {
            $sqx .= " Where a.cabang_id = $cabangId";
        }
        $sqx.= " ORDER BY $orderBy;";
        $this->connector->CommandText = $sqx;
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ItemPrices();
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
		$this->connector->CommandText = "SELECT a.* FROM vw_m_item_prices AS a WHERE a.id = ?id Limit 1";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByItemId($cabangId,$itemId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_m_item_prices AS a WHERE a.cabang_id = ?cabangId And a.item_id = ?itemId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?itemId", $itemId);
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

    public function  FindPriceByUnitId($cabangId,$itemId,$uomCode){
        $sql = "Select coalesce(a.id,0) as ValResult From m_item_prices a Where a.cabang_id = $cabangId And a.item_id = $itemId And a.uom_code = '".$uomCode."'";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return strval($row["ValResult"]);
    }

	public function Insert() {
        $sqx = "Insert Into m_item_prices (cabang_id, item_id, uom_code, purchase_price, zone_1, zone_2, zone_3, zone_4, zone_5, hpp, price_date, createby_id, create_time)";
        $sqx.= "Values (?cabang_id, ?item_id, ?uom_code, ?purchase_price, ?zone_1, ?zone_2, ?zone_3, ?zone_4, ?zone_5, ?hpp, ?price_date, ?createby_id, now())";
        $this->connector->CommandText = $sqx;
		$this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?uom_code", $this->UomCode);
        $this->connector->AddParameter("?purchase_price", $this->PurchasePrice);
        $this->connector->AddParameter("?zone_1", $this->pZone1);
        $this->connector->AddParameter("?zone_2", $this->pZone2);
        $this->connector->AddParameter("?zone_3", $this->pZone3);
        $this->connector->AddParameter("?zone_4", $this->pZone4);
        $this->connector->AddParameter("?zone_5", $this->pZone5);
        $this->connector->AddParameter("?hpp", $this->Hpp);
        $this->connector->AddParameter("?price_date", $this->FormatPriceDate(SQL_DATEONLY));
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
        }
		return $rs;
	}

	public function Update($id) {
        $this->connector->CommandText = 'Insert Into m_item_prices_history Select a.* From m_item_prices as a Where a.id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'UPDATE m_item_prices 
SET uom_code = ?uom_code,
cabang_id = ?cabang_id,
item_id = ?item_id,
uom_code = ?uom_code,
price_date = ?price_date,
purchase_price = ?purchase_price,
hpp = ?hpp,
zone_1 = ?zone_1,
zone_2 = ?zone_2,
zone_3 = ?zone_3,
zone_4 = ?zone_4,
zone_5 = ?zone_5,
updateby_id = ?updateby_id,
update_time = now() 
WHERE id = ?id';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?uom_code", $this->UomCode);
        $this->connector->AddParameter("?purchase_price", $this->PurchasePrice);
        $this->connector->AddParameter("?zone_1", $this->pZone1);
        $this->connector->AddParameter("?zone_2", $this->pZone2);
        $this->connector->AddParameter("?zone_3", $this->pZone3);
        $this->connector->AddParameter("?zone_4", $this->pZone4);
        $this->connector->AddParameter("?zone_5", $this->pZone5);
        $this->connector->AddParameter("?hpp", $this->Hpp);
        $this->connector->AddParameter("?price_date", $this->FormatPriceDate(SQL_DATEONLY));
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}

	public function Delete($id) {
        $this->connector->CommandText = 'Insert Into m_item_prices_history Select a.* From m_item_prices as a Where a.id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
		$this->connector->CommandText = 'Delete From m_item_prices Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }
}
