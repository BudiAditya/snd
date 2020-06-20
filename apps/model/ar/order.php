<?php

class Order extends EntityBase {
    public $Id;
    public $IsDeleted = 0;
    public $CompanyId = 0;
    public $CabangId = 0;
    public $CabangCode;
    public $CabangName;
    public $OrderDate;
    public $CustomerId;
    public $SalesId;
    public $PriorityId = 0;
    public $RequestDate;
    public $OrderStatus = 0;
    public $ItemId = 0;
    public $OrderQty = 0;
    public $SendQty = 0;
    public $Price = 0;
    public $DiscFormula = '0';
    public $DiscAmount = 0;
    public $PromoId = 0;
    public $SubTotal = 0;
    public $CreatebyId = 0;
    public $UpdatebyId = 0;
    public $Lqty = 0;
    public $Sqty = 0;
    
    public function FillProperties(array $row) {
        $this->Id = $row["id"];
        $this->CabangId = $row["cabang_id"];
        $this->OrderDate = strtotime($row["order_date"]);
        $this->PriorityId = $row["priority_id"];
        $this->SalesId = $row["sales_id"];
        $this->CustomerId = $row["customer_id"];
        $this->RequestDate = strtotime($row["request_date"]);
        $this->OrderStatus = $row["order_status"];
        $this->OrderQty = $row["order_qty"];
        $this->SendQty = $row["send_qty"];
        $this->Price = $row["price"];
        $this->DiscFormula = $row["disc_formula"];
        $this->DiscAmount = $row["disc_amount"];
        $this->SubTotal = $row["order_qty"] * $row["price"];
        $this->ItemId = $row["item_id"];
        $this->PromoId = $row["promo_id"];
        $this->Lqty = $row["l_qty"];
        $this->Sqty = $row["order_qty"] - ($row["l_qty"] * $row["s_uom_qty"]);
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
    }

    public function FormatOrderDate($format = JS_DATE) {
        return is_int($this->OrderDate) ? date($format, $this->OrderDate) : date($format, strtotime(date('Y-m-d')));
    }

    public function FormatRequestDate($format = HUMAN_DATE) {
        return is_int($this->RequestDate) ? date($format, $this->RequestDate) : null;
    }

    public function LoadById($id) {
        $this->FindById($id);
        return $this;
    }

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_order_list AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

    public function LoadByCabangId($cabId, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_order_list AS a WHERE a.cabang_id = ?cabId ORDER BY $orderBy";
        $this->connector->AddParameter("?cabId", $cabId);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Order();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function LoadOutstandingOrder($orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_order_list AS a WHERE a.order_qty > a.send_qty ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadClosedOrder($orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.* FROM vw_ar_order_list AS a WHERE a.order_qty <= a.send_qty ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Insert() {
        $sql = "INSERT INTO t_ar_order (cabang_id, order_date, customer_id, sales_id, priority_id, request_date, order_status, createby_id, create_time, item_id, order_qty, send_qty, price, disc_formula, disc_amount, promo_id)";
        $sql.= " Values (?cabang_id, ?order_date, ?customer_id, ?sales_id, ?priority_id, ?request_date, ?order_status, ?createby_id, now(), ?item_id, ?order_qty, ?send_qty, ?price, ?disc_formula, ?disc_amount, ?promo_id)";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?order_date", $this->OrderDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId == null ? 0 : $this->CustomerId);
        $this->connector->AddParameter("?sales_id", $this->SalesId == null ? 0 : $this->SalesId);
        $this->connector->AddParameter("?priority_id", $this->PriorityId == null ? 0 : $this->PriorityId);
        $this->connector->AddParameter("?request_date", $this->RequestDate == null ? $this->OrderDate : $this->RequestDate);
        $this->connector->AddParameter("?order_status", $this->OrderStatus == null ? 0 : $this->OrderStatus);
        $this->connector->AddParameter("?item_id", $this->ItemId == null ? 0 : $this->ItemId);
        $this->connector->AddParameter("?order_qty", $this->OrderQty == null ? 0 : $this->OrderQty);
        $this->connector->AddParameter("?send_qty", $this->SendQty == null ? 0 : $this->SendQty);
        $this->connector->AddParameter("?price", $this->Price == null ? 0 : $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula == null ? 0 : $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount == null ? 0 : $this->DiscAmount);
        $this->connector->AddParameter("?promo_id", $this->PromoId == null ? 0 : $this->PromoId);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
        }
        return $rs;
    }

    public function Update($id) {
        $this->connector->CommandText =
            "UPDATE t_ar_order SET
	  cabang_id = ?cabang_id
	, order_date = ?order_date
	, customer_Id = ?customer_id
	, sales_id = ?sales_id
	, item_id = ?item_id
	, order_qty = ?order_qty
	, send_qty = ?send_qty
	, price = ?price
	, priority_id = ?priority_id
	, request_date = ?request_date
	, order_status = ?order_status
	, disc_formula = ?disc_formula
	, disc_amount = ?disc_amount
	, promo_id = ?promo_id
	, updateby_id = ?updateby_id
WHERE id = ?id";
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?order_date", $this->OrderDate);
        $this->connector->AddParameter("?customer_id", $this->CustomerId == null ? 0 : $this->CustomerId);
        $this->connector->AddParameter("?sales_id", $this->SalesId == null ? 0 : $this->SalesId);
        $this->connector->AddParameter("?priority_id", $this->PriorityId == null ? 0 : $this->PriorityId);
        $this->connector->AddParameter("?request_date", $this->RequestDate == null ? $this->OrderDate : $this->RequestDate);
        $this->connector->AddParameter("?order_status", $this->OrderStatus == null ? 0 : $this->OrderStatus);
        $this->connector->AddParameter("?item_id", $this->ItemId == null ? 0 : $this->ItemId);
        $this->connector->AddParameter("?order_qty", $this->OrderQty == null ? 0 : $this->OrderQty);
        $this->connector->AddParameter("?send_qty", $this->SendQty == null ? 0 : $this->SendQty);
        $this->connector->AddParameter("?price", $this->Price == null ? 0 : $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula == null ? 0 : $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount == null ? 0 : $this->DiscAmount);
        $this->connector->AddParameter("?promo_id", $this->PromoId == null ? 0 : $this->PromoId);
        $this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function Void($id) {
        //hapus detail
        $this->connector->CommandText = "Update t_ar_order a Set a.is_deleted = 1, a.updateby_id = ?uid, a.update_time = now() WHERE id = ?id";
        $this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?uid", $this->UpdatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function Delete($id) {
        //hapus detail
        $this->connector->CommandText = "DELETE FROM t_ar_order WHERE id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function LoadOrder4ReportsDetail($companyId, $cabangId = 0, $customerId = 0, $salesId = 0, $soStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.* FROM vw_ar_order_list AS a WHERE a.is_deleted = 0 and a.order_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($soStatus > -1){
            if ($soStatus == 0) {
                $sql .= " and a.order_status = 0";
            }elseif ($soStatus == 1) {
                $sql .= " and a.order_status = 1 And (a.order_qty - a.send_qty > 0)";
            }elseif ($soStatus == 2){
                $sql .= " and a.order_status = 2 Or (a.order_qty - a.send_qty = 0)";
            }else{
                $sql .= " and a.order_status = 3";
            }
        }else{
            $sql.= " and a.order_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Order By a.order_date,a.sales_name,a.cus_name,a.item_code";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadOrder4ReportsRekapItem($companyId, $cabangId = 0, $customerId = 0, $salesId = 0, $soStatus = -1, $startDate = null, $endDate = null) {
        $sql = "SELECT a.brand_name,a.item_code,a.item_name,a.l_uom_code,a.s_uom_code,a.s_uom_qty,sum(a.order_qty) as sumOrderQty,sum(a.send_qty) as sumSendQty";
        $sql.= " FROM vw_ar_order_list AS a WHERE a.is_deleted = 0 and a.order_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }else{
            $sql.= " and a.company_id = ".$companyId;
        }
        if ($soStatus > -1){
            if ($soStatus == 0) {
                $sql .= " and a.order_status = 0";
            }elseif ($soStatus == 1) {
                $sql .= " and a.order_status = 1 And (a.order_qty - a.send_qty > 0)";
            }elseif ($soStatus == 2){
                $sql .= " and a.order_status = 2 Or (a.order_qty - a.send_qty = 0)";
            }else{
                $sql .= " and a.order_status = 3";
            }
        }else{
            $sql.= " and a.order_status <> 3";
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Group By a.brand_name,a.item_code,a.item_name,a.l_uom_code,a.s_uom_code,a.s_uom_qty;";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadOrder4Process($cabangId = 0, $customerId = 0, $salesId = 0) {
        $sql = "SELECT a.* FROM vw_ar_order_list AS a WHERE a.is_deleted = 0 And a.order_status <> 3 And a.order_qty > a.send_qty";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($customerId > 0){
            $sql.= " and a.customer_id = ".$customerId;
        }
        if ($salesId > 0){
            $sql.= " and a.sales_id = ".$salesId;
        }
        $sql.= " Order By a.sales_name,a.cus_name,a.order_date,a.item_code";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetCustomerList(){
        $sql = "SELECT a.customer_id,a.cus_name,a.cus_code FROM vw_ar_order_list AS a WHERE a.is_deleted = 0 And a.order_status <> 3 And a.order_qty > a.send_qty Group By a.customer_id,a.cus_name,a.cus_code Order By a.cus_name";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetSalesList(){
        $sql = "SELECT a.sales_id,a.sales_name,a.sales_code FROM vw_ar_order_list AS a WHERE a.is_deleted = 0 And a.order_status <> 3 And a.order_qty > a.send_qty Group By a.sales_id,a.sales_name,a.sales_code Order By a.sales_name";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetItemSoItems($customerId,$salesId) {
        $sql = "SELECT a.id,a.item_id,a.item_code,a.item_name,a.s_uom_code,a.l_uom_code,a.order_qty-a.send_qty as qty_order,a.price as hrg_jual,a.s_uom_qty";
        $sql.= " From vw_ar_order_list AS a";
        $sql.= " Where a.customer_id = $customerId And a.sales_id = $salesId And (a.order_qty - a.send_qty > 0 and a.order_status < 2)";
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.item_name Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }
}
// End of File: estimasi_detail.php
