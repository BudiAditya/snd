<?php
class Items extends EntityBase {
	public $Id;
	public $IsDeleted = false;
    public $PrincipalId = 0;
	public $BrandId = 0;
	public $SubCategoryId = 0;
    public $ItemCode;
    public $OldCode = '-';
    public $ItemName;
    public $BarCode;
    public $LuomCode;
    public $LuomQty = 0;
    public $MuomCode;
    public $MuomQty = 0;
    public $SuomCode;
    public $SuomQty = 0;
    public $IsConvert = 0;
    public $QtyConvert = 0;
    public $CuomCode;
    public $MaxStock = 0;
    public $MinStock = 0;
    public $IsPurchase = 1;
    public $IsSale = 1;
    public $IsStock = 1;
    public $IsAssembly = 0;
    public $IsAllowMinus = 0;
    public $IsAktif = 1;
    public $ItemLevel = 0;
    public $CabangId = 0;
    public $CreatebyId = 0;
    public $UpdatebyId = 0;

	public function __construct($bid = null) {
		parent::__construct();
		if (is_numeric($bid)) {
			$this->FindById($bid);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
        $this->PrincipalId = $row["principal_id"];
		$this->BrandId = $row["brand_id"];
		$this->SubCategoryId = $row["subcategory_id"];
        $this->ItemCode = $row["item_code"];
        $this->OldCode = $row["old_code"];
        $this->ItemName = $row["item_name"];
        $this->BarCode = $row["bar_code"];
        $this->LuomCode = $row["l_uom_code"];
        $this->LuomQty = $row["l_uom_qty"];
        $this->MuomCode = $row["m_uom_code"];
        $this->MuomQty = $row["m_uom_qty"];
        $this->SuomCode = $row["s_uom_code"];
        $this->SuomQty = $row["s_uom_qty"];
        $this->IsConvert = $row["is_convert"];
        $this->QtyConvert = $row["qty_convert"];
        $this->CuomCode = $row["c_uom_code"];
        $this->MaxStock = $row["max_stock"];
        $this->MinStock = $row["min_stock"];
        $this->IsPurchase = $row["is_purchase"];
        $this->IsSale = $row["is_sale"];
        $this->IsStock = $row["is_stock"];
        $this->IsAssembly = $row["is_assembly"];
        $this->IsAllowMinus = $row["is_allow_minus"];
        $this->IsAktif = $row["is_aktif"];
        $this->ItemLevel = $row["item_level"];
        $this->CabangId = $row["cabang_id"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($entityId,$cabangId,$orderBy = "a.item_code", $includeDeleted = false) {
        $sqx = "SELECT a.* FROM vw_ic_items AS a Where a.is_aktif = 1";
		if (!$includeDeleted) {
			$sqx.= " And a.is_deleted = 0";
		}
        $sqx.= " And Not (a.item_level = 1 And a.entity_id <> $entityId) And Not (a.item_level = 2 And a.cabang_id <> $cabangId)";
        //$sqx.= " And a.entity_id = ".$entityId;
        $sqx.= " ORDER BY $orderBy;";
        $this->connector->CommandText = $sqx;
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Items();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadItemList($entityId,$cabangId,$itemStatus = 1,$orderBy = "a.item_code", $includeDeleted = false) {
        $sqx = "SELECT a.* FROM vw_ic_items AS a ";
        if ($itemStatus == -1){
            $sqx.= "Where a.is_aktif > -1";
        }else{
            $sqx.= "Where a.is_aktif = $itemStatus";
        }
        if ($includeDeleted) {
            $sqx.= " And a.is_deleted = 0";
        }
        $sqx.= " And Not (a.item_level = 1 And a.entity_id <> $entityId) And Not (a.item_level = 2 And a.cabang_id <> $cabangId)";
        $sqx.= " ORDER BY $orderBy;";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Items();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	/**
	 * @param int $bid
	 * @return Location
	 */
	public function FindById($bid) {
		$this->connector->CommandText = "SELECT a.* FROM vw_ic_items AS a WHERE a.id = ?bid";
		$this->connector->AddParameter("?bid", $bid);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByKode($itemCode) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ic_items AS a WHERE a.item_code = ?item_code";
        $this->connector->AddParameter("?item_code", $itemCode);
        $rs = $this->connector->ExecuteQuery();

        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

	/**
	 * @param int $bid
	 * @return Location
	 */
	public function LoadById($bid) {
		return $this->FindById($bid);
	}

    public function LoadByKode($itemCode) {
        return $this->FindByKode($itemCode);
    }

	public function Insert() {
        $sql = 'INSERT INTO m_items(old_code,principal_id,brand_id,subcategory_id,item_code,item_name,bar_code,l_uom_code,l_uom_qty,s_uom_code,s_uom_qty,is_convert,qty_convert,c_uom_code,max_stock,min_stock,is_purchase,is_sale,is_stock,is_allow_minus,is_aktif,item_level,cabang_id,createby_id,create_time)';
        $sql.= ' VALUES(?old_code,?principal_id,?brand_id,?subcategory_id,?item_code,?item_name,?bar_code,?l_uom_code,?l_uom_qty,?s_uom_code,?s_uom_qty,?is_convert,?qty_convert,?c_uom_code,?max_stock,?min_stock,?is_purchase,?is_sale,?is_stock,?is_allow_minus,?is_aktif,?item_level,?cabang_id,?createby_id,now())';
		$this->connector->CommandText = $sql;
		$this->connector->AddParameter("?principal_id", $this->PrincipalId);
        $this->connector->AddParameter("?brand_id", $this->BrandId);
        $this->connector->AddParameter("?subcategory_id", $this->SubCategoryId);
        $this->connector->AddParameter("?item_code", $this->ItemCode, "varchar");
        $this->connector->AddParameter("?old_code", $this->OldCode, "varchar");
        $this->connector->AddParameter("?item_name", $this->ItemName);
        $this->connector->AddParameter("?bar_code", $this->BarCode, "varchar");
        $this->connector->AddParameter("?l_uom_code", $this->LuomCode);
        $this->connector->AddParameter("?l_uom_qty", $this->LuomQty);
        $this->connector->AddParameter("?s_uom_code", $this->SuomCode);
        $this->connector->AddParameter("?s_uom_qty", $this->SuomQty);
        $this->connector->AddParameter("?is_convert", $this->IsConvert);
        $this->connector->AddParameter("?qty_convert", $this->QtyConvert);
        $this->connector->AddParameter("?c_uom_code", $this->CuomCode);
        $this->connector->AddParameter("?max_stock", $this->MaxStock);
        $this->connector->AddParameter("?min_stock", $this->MinStock);
        $this->connector->AddParameter("?is_purchase", $this->IsPurchase);
        $this->connector->AddParameter("?is_sale", $this->IsSale);
        $this->connector->AddParameter("?is_stock", $this->IsStock);
        $this->connector->AddParameter("?is_allow_minus", $this->IsAllowMinus);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?item_level", $this->ItemLevel);
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
		$rs = $this->connector->ExecuteNonQuery();
        $rcn = 0;
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
        }
        return $rs;
	}

	public function Update($bid) {
		$this->connector->CommandText = 'UPDATE m_items 
SET principal_id = ?principal_id, 
brand_id = ?brand_id, 
subcategory_id = ?subcategory_id,
item_code = ?item_code,
old_code = ?old_code,
item_name = ?item_name,
bar_code = ?bar_code,
l_uom_code = ?l_uom_code,
l_uom_qty = ?l_uom_qty, 
s_uom_code = ?s_uom_code,
s_uom_qty = ?s_uom_qty,
is_convert = ?is_convert,
qty_convert = ?qty_convert, 
c_uom_code = ?c_uom_code,
max_stock = ?max_stock,
min_stock = ?min_stock,
is_purchase = ?is_purchase,
is_sale = ?is_sale, 
is_stock = ?is_stock,
is_allow_minus = ?is_allow_minus, 
is_aktif = ?is_aktif,
item_level = ?item_level,  
cabang_id = ?cabang_id, 
updateby_id = ?updateby_id, 
update_time = now()
WHERE id = ?bid';
        $this->connector->AddParameter("?principal_id", $this->PrincipalId);
        $this->connector->AddParameter("?brand_id", $this->BrandId);
        $this->connector->AddParameter("?subcategory_id", $this->SubCategoryId);
        $this->connector->AddParameter("?item_code", $this->ItemCode, "varchar");
        $this->connector->AddParameter("?old_code", $this->OldCode, "varchar");
        $this->connector->AddParameter("?item_name", $this->ItemName);
        $this->connector->AddParameter("?bar_code", $this->BarCode, "varchar");
        $this->connector->AddParameter("?l_uom_code", $this->LuomCode);
        $this->connector->AddParameter("?l_uom_qty", $this->LuomQty);
        $this->connector->AddParameter("?s_uom_code", $this->SuomCode);
        $this->connector->AddParameter("?s_uom_qty", $this->SuomQty);
        $this->connector->AddParameter("?is_convert", $this->IsConvert);
        $this->connector->AddParameter("?qty_convert", $this->QtyConvert);
        $this->connector->AddParameter("?c_uom_code", $this->CuomCode);
        $this->connector->AddParameter("?max_stock", $this->MaxStock);
        $this->connector->AddParameter("?min_stock", $this->MinStock);
        $this->connector->AddParameter("?is_purchase", $this->IsPurchase);
        $this->connector->AddParameter("?is_sale", $this->IsSale);
        $this->connector->AddParameter("?is_stock", $this->IsStock);
        $this->connector->AddParameter("?is_allow_minus", $this->IsAllowMinus);
        $this->connector->AddParameter("?is_aktif", $this->IsAktif);
        $this->connector->AddParameter("?item_level", $this->ItemLevel);
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		$this->connector->AddParameter("?bid", $bid);
		return $this->connector->ExecuteNonQuery();
	}

	public function Void($bid) {
		$this->connector->CommandText = 'Update m_items SET is_deleted = 1, is_aktif = 0, updateby_id = ?updateby_id, update_time = now() WHERE id = ?bid';
		$this->connector->AddParameter("?bid", $bid);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
		return $this->connector->ExecuteNonQuery();
	}

    public function Delete($bid) {
        $this->connector->CommandText = 'Delete From m_items WHERE id = ?bid';
        $this->connector->AddParameter("?bid", $bid);
        return $this->connector->ExecuteNonQuery();
    }

    public function GetDataGrid($entityId = 0,$cabangId = 0,$offset,$limit,$field,$search='',$sort = 'a.item_code',$order = 'ASC') {
        $sql = "SELECT a.* FROM vw_ic_items as a Where a.is_deleted = 0 and a.is_aktif = 1";
        if ($search !='' && $field !=''){
            $sql.= "And $field Like '%{$search}%' ";
        }
        $sql.= " And Not (a.item_level = 1 And a.entity_id <> $entityId) And Not (a.item_level = 2 And a.is_aktif <>$cabangId)";
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= "Order By $sort $order Limit {$offset},{$limit}";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $rows = array();
        if ($rs != null) {
            $i = 0;
            while ($row = $rs->FetchAssoc()) {
                $rows[$i]['id'] = $row['id'];
                $rows[$i]['item_code'] = $row['item_code'];
                $rows[$i]['item_name'] = $row['item_name'];
                $i++;
            }
        }
        //data hasil query yang dikirim kembali dalam format json
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetJSonItems($entityId,$cabangId,$principalId = 0,$filter = null,$sort = 'a.item_code',$order = 'ASC') {
        $sql = "SELECT a.id as item_id,a.item_code,a.item_name,a.l_uom_code,a.s_uom_code,a.s_uom_qty,0 as hrg_beli,0 as hrg_jual,0 as qty_order FROM vw_ic_items as a Where a.is_deleted = 0 And a.is_aktif = 1";
        if ($principalId > 0){
            $sql.= " And a.principal_id = $principalId";
        }
        if ($filter != null){
            $sql.= " And (a.item_name Like '%$filter%' or a.item_code Like '%$filter%')";
        }
        //$sql.= " And Not (a.item_level = 1 And a.entity_id <> $entityId) And Not (a.item_level = 2 And a.cabang_id <>$cabangId)";
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function DeleteProcess() {
        $this->connector->CommandText = "Delete From m_items WHERE item_code = ?item_code";
        $this->connector->AddParameter("?item_code",$this->ItemCode);
        return $this->connector->ExecuteNonQuery();
    }

    public function getLastCode($itemCode){
	    $sql = "Select max(a.item_code) as itemCode From m_items a Where left(a.item_code,5) = '".$itemCode."'";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs != null) {
            $row = $rs->FetchAssoc();
            $itemCode = right($row["itemCode"],3);
        }else{
            $itemCode = "000";
        }
        return $itemCode;
    }
}
