<?php

class SoDetail extends EntityBase {
	public $Id;
	public $SoId;
    public $SoNo;
	public $ItemDescs;
    public $ItemCode;
    public $ItemId;
    public $Lqty;
    public $Sqty;
	public $OrderQty;
    public $SendQty;
	public $Price;
    public $DiscFormula;
    public $DiscAmount;
    public $SubTotal;
    public $SatBesar;
    public $SatKecil;

	// Helper Variable;
	public $MarkedForDeletion = false;


	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->SoId = $row["so_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_name"];
        $this->Lqty = $row["l_qty"];
        $this->Sqty = $row["s_qty"];
		$this->OrderQty = $row["order_qty"];
        $this->SendQty = $row["send_qty"];
		$this->Price = $row["price"];
        $this->DiscFormula = $row["disc_formula"];
        $this->DiscAmount = $row["disc_amount"];
        $this->SubTotal = $row["sub_total"];
        $this->SatBesar = $row["bsatbesar"];
        $this->SatKecil = $row["bsatkecil"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_so_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_so_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadBySoId($soId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_so_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.so_id = ?soId ORDER BY $orderBy";
		$this->connector->AddParameter("?soId", $soId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new SoDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadBySoNo($soNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_so_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.so_no = ?soNo ORDER BY $orderBy";
        $this->connector->AddParameter("?soNo", $soNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new SoDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ar_so_detail(so_id, item_id, item_descs, l_qty, s_qty, order_qty, send_qty, price, disc_formula, disc_amount, sub_total)
VALUES(?so_id, ?item_id, ?item_descs, ?l_qty, ?s_qty, ?order_qty, ?send_qty, ?price, ?disc_formula, ?disc_amount, ?sub_total)";
		$this->connector->AddParameter("?so_id", $this->SoId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_descs", $this->ItemDescs == null ? '-' : $this->ItemDescs);
        $this->connector->AddParameter("?l_qty", $this->Lqty == null ? 0 : $this->Lqty);
        $this->connector->AddParameter("?s_qty", $this->Sqty == null ? 0 : $this->Sqty);
        $this->connector->AddParameter("?order_qty", $this->OrderQty);
        $this->connector->AddParameter("?send_qty", $this->SendQty == null ? 0 : $this->SendQty);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?sub_total", $this->SubTotal);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            //update po
            $this->UpdateSoMaster($this->SoId);
		}
		return $rs;
	}

	public function Update($id) {
        $this->connector->CommandText =
"UPDATE t_ar_so_detail SET
	  so_id = ?so_id
	, item_descs = ?item_descs
	, order_qty = ?order_qty
	, send_qty = ?send_qty
	, price = ?price
	, sub_total = ?sub_total
	, item_id = ?item_id
	, l_qty = ?l_qty
	, s_qty = ?s_qty
	, disc_formula = ?disc_formula
	, disc_amount = ?disc_amount
WHERE id = ?id";
        $this->connector->AddParameter("?so_id", $this->SoId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_descs", $this->ItemDescs == null ? '-' : $this->ItemDescs);
        $this->connector->AddParameter("?l_qty", $this->Lqty == null ? 0 : $this->Lqty);
        $this->connector->AddParameter("?s_qty", $this->Sqty == null ? 0 : $this->Sqty);
        $this->connector->AddParameter("?order_qty", $this->OrderQty);
        $this->connector->AddParameter("?send_qty", $this->SendQty == null ? 0 : $this->SendQty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?sub_total", $this->SubTotal);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //update po master
            $this->UpdateSoMaster($this->SoId);
        }
        return $rs;
	}

	public function Delete($id) {
        //hapus detail
		$this->connector->CommandText = "DELETE FROM t_ar_so_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            $this->UpdateSoMaster($this->SoId);
        }
        return $rs;
	}

    public function UpdateSoMaster($soId){
        $sql = 'Update t_ar_so_master a Set a.base_amount = 0, a.tax_amount = 0, a.disc1_amount = 0 Where a.id = ?soId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?soId", $soId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_so_master a
Join (Select c.so_id, sum(c.sub_total) As sumPrice From t_ar_so_detail c Group By c.so_id) b
On a.id = b.so_id Set a.base_amount = b.sumPrice, a.disc1_amount = if(a.disc1_pct > 0,round(b.sumPrice * (a.disc1_pct/100),0),0) Where a.id = ?soId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?soId", $soId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_so_master a Set a.tax_amount = if(a.tax_pct > 0 And (a.base_amount - a.disc1_amount) > 0,round((a.base_amount - a.disc1_amount)  * (a.tax_pct/100),0),0) Where a.id = ?soId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?soId", $soId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }
}
// End of File: estimasi_detail.php
