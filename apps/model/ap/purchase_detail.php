<?php

class PurchaseDetail extends EntityBase {
	public $Id;
    public $CabangId;
	public $GrnId;
	public $ItemDescs;
    public $ItemCode;
    public $ItemId;
    public $Lqty = 0;
    public $Sqty = 0;
	public $PurchaseQty = 0;
    public $ReturnQty = 0;
	public $Price = 0;
    public $DiscFormula;
    public $DiscAmount = 0;
    public $SubTotal = 0;
    public $SatBesar;
    public $SatKecil;
    public $IsFree = 0;
    public $ExPoId = 0;
    public $ExPoNo;
    public $PpnPct = 0;
    public $PpnAmount = 0;
    public $PphPct = 0;
    public $PphAmount = 0;
    public $BatchNo;
    public $ExpDate;
    public $ByAngkut = 0;
    public $IsiSatKecil = 0;

	// Helper Variable;
	public $MarkedForDeletion = false;


	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->GrnId = $row["grn_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_name"];
        $this->Lqty = $row["l_qty"];
        $this->Sqty = $row["s_qty"];
		$this->PurchaseQty = $row["purchase_qty"];
        $this->ReturnQty = $row["return_qty"];
		$this->Price = $row["price"];
        $this->DiscFormula = $row["disc_formula"];
        $this->DiscAmount = $row["disc_amount"];
        $this->SubTotal = $row["sub_total"];
        $this->SatBesar = $row["bsatbesar"];
        $this->SatKecil = $row["bsatkecil"];
        $this->IsFree = $row["is_free"];
        $this->ExPoId = $row["ex_po_id"];
        $this->ExPoNo = $row["ex_po_no"];
        $this->PpnPct = $row["ppn_pct"];
        $this->PpnAmount = $row["ppn_amount"];
        $this->PphPct = $row["pph_pct"];
        $this->PphAmount = $row["pph_amount"];
        $this->BatchNo = $row["batch_no"];
        $this->ExpDate = strtotime($row["exp_date"]);
        $this->ByAngkut = $row["by_angkut"];
        $this->IsiSatKecil = $row["bisisatkecil"];
        if ($this->PurchaseQty >= $this->IsiSatKecil){
            $aqty = array();
            $sqty = round($this->PurchaseQty/$this->IsiSatKecil,2);
            $aqty = explode('.',$sqty);
            $lqty = $aqty[0];
            $this->Lqty = $lqty;
            $this->Sqty = $this->PurchaseQty - ($lqty * $this->IsiSatKecil);
        }else {
            $this->Lqty = 0;
            $this->Sqty = $this->PurchaseQty;
        }
	}

    public function FormatExpDate($format = HUMAN_DATE) {
        return is_int($this->ExpDate) ? date($format, $this->ExpDate) : null;
    }

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,c.po_no as ex_po_no,b.s_uom_qty as bisisatkecil FROM t_ap_purchase_detail AS a Join m_items AS b On a.item_id = b.id Left Join t_ap_po_master c ON a.ex_po_id = c.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,c.po_no as ex_po_no,b.s_uom_qty as bisisatkecil FROM t_ap_purchase_detail AS a Join m_items AS b On a.item_id = b.id Left Join t_ap_po_master c ON a.ex_po_id = c.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByGrnId($grnId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,c.po_no as ex_po_no,b.s_uom_qty as bisisatkecil FROM t_ap_purchase_detail AS a Join m_items AS b On a.item_id = b.id Left Join t_ap_po_master c ON a.ex_po_id = c.id WHERE a.grn_id = ?grnId ORDER BY $orderBy";
		$this->connector->AddParameter("?grnId", $grnId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new PurchaseDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByGrnNo($grnNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,c.po_no as ex_po_no,b.s_uom_qty as bisisatkecil FROM t_ap_purchase_detail AS a Join m_items AS b On a.item_id = b.id Left Join t_ap_po_master c ON a.ex_po_id = c.id WHERE a.grn_no = ?grnNo ORDER BY $orderBy";
        $this->connector->AddParameter("?grnNo", $grnNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new PurchaseDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ap_purchase_detail(ex_po_id,by_angkut,is_free,grn_id, item_id, item_descs, l_qty, s_qty, purchase_qty, return_qty, price, disc_formula, disc_amount, sub_total, pph_pct, pph_amount, ppn_pct, ppn_amount, batch_no, exp_date)
VALUES(?ex_po_id,?by_angkut,?is_free,?grn_id, ?item_id, ?item_descs, ?l_qty, ?s_qty, ?purchase_qty, ?return_qty, ?price, ?disc_formula, ?disc_amount, ?sub_total, ?pph_pct, ?pph_amount, ?ppn_pct, ?ppn_amount, ?batch_no, ?exp_date)";
		$this->connector->AddParameter("?grn_id", $this->GrnId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_descs", $this->ItemDescs == null ? '-' : $this->ItemDescs);
        $this->connector->AddParameter("?l_qty", $this->Lqty);
        $this->connector->AddParameter("?s_qty", $this->Sqty);
		$this->connector->AddParameter("?purchase_qty", $this->PurchaseQty);
        $this->connector->AddParameter("?return_qty", $this->ReturnQty);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?sub_total", $this->SubTotal);
        $this->connector->AddParameter("?is_free", $this->IsFree == null ? 0 : $this->IsFree);
        $this->connector->AddParameter("?ex_po_id", $this->ExPoId == null || $this->ExPoId == '' ? 0 : $this->ExPoId);
        $this->connector->AddParameter("?ppn_pct", $this->PpnPct);
        $this->connector->AddParameter("?ppn_amount", $this->PpnAmount);
        $this->connector->AddParameter("?pph_pct", $this->PphPct);
        $this->connector->AddParameter("?pph_amount", $this->PphAmount);
        $this->connector->AddParameter("?batch_no", $this->BatchNo);
        $this->connector->AddParameter("?exp_date", $this->ExpDate);
        $this->connector->AddParameter("?by_angkut", $this->ByAngkut);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        $did = 0;
        if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $did = $this->Id;
            //tambah stock
            $this->connector->CommandText = "SELECT fc_ap_purchasedetail_post($did) As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update po status (jika ada)
            $this->connector->CommandText = "SELECT fc_ap_po_checkstatus('".$this->ExPoId."') As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update grn
            $this->UpdateGrnMaster($this->GrnId);
		}
		return $rs;
	}

	public function Update($id) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_purchasedetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText =
"UPDATE t_ap_purchase_detail SET
	  grn_id = ?grn_id
	, item_descs = ?item_descs
	, purchase_qty = ?purchase_qty
	, return_qty = ?return_qty
	, price = ?price
	, sub_total = ?sub_total
	, item_id = ?item_id
	, l_qty = ?l_qty
	, s_qty = ?s_qty
	, disc_formula = ?disc_formula
	, disc_amount = ?disc_amount
	, is_free = ?is_free
	, ex_po_id = ?ex_po_id
	, pph_pct = ?ppn_pct
	, pph_amount = ?pph_amount
	, ppn_pct = ?ppn_pct
	, ppn_amount = ?ppn_amount
	, batch_no = ?batch_no
	, exp_date = ?exp_date
	, by_angkut = ?by_angkut
WHERE id = ?id";
        $this->connector->AddParameter("?grn_id", $this->GrnId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_descs", $this->ItemDescs == null ? '-' : $this->ItemDescs);
        $this->connector->AddParameter("?l_qty", $this->Lqty);
        $this->connector->AddParameter("?s_qty", $this->Sqty);
        $this->connector->AddParameter("?purchase_qty", $this->PurchaseQty);
        $this->connector->AddParameter("?return_qty", $this->ReturnQty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?sub_total", $this->SubTotal);
        $this->connector->AddParameter("?is_free", $this->IsFree == null ? 0 : $this->IsFree);
        $this->connector->AddParameter("?ex_po_id", $this->ExPoId == null || $this->ExPoId == '' ? 0 : $this->ExPoId);
        $this->connector->AddParameter("?pph_pct", $this->PphPct);
        $this->connector->AddParameter("?pph_amount", $this->PphAmount);
        $this->connector->AddParameter("?ppn_pct", $this->PpnPct);
        $this->connector->AddParameter("?ppn_amount", $this->PpnAmount);
        $this->connector->AddParameter("?batch_no", $this->BatchNo);
        $this->connector->AddParameter("?exp_date", $this->ExpDate);
        $this->connector->AddParameter("?by_angkut", $this->ByAngkut);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //potong stock lagi
            $this->connector->CommandText = "SELECT fc_ap_purchasedetail_post($id) As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update po status (jika ada)
            $this->connector->CommandText = "SELECT fc_ap_po_checkstatus('".$this->ExPoId."') As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update grn master
            $this->UpdateGrnMaster($this->GrnId);
        }
        return $rs;
	}

	public function Delete($id) {
        //unpost stock dulu
        $rsx = null;
        $pid = $this->ExPoId;
        $this->connector->CommandText = "SELECT fc_ap_purchasedetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //hapus detail
		$this->connector->CommandText = "DELETE FROM t_ap_purchase_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //update so status (jika ada)
            $this->connector->CommandText = "SELECT fc_ap_po_checkstatus('".$pid."') As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update GRN master
            $this->UpdateGrnMaster($this->GrnId);
        }
        return $rs;
	}

    public function UpdateGrnMaster($grnId){
        $sql = 'Update t_ap_purchase_master a Set a.base_amount = 0, a.ppn_amount = 0, a.pph_amount = 0, a.disc_amount = 0, a.other_costs_amount = 0 Where a.id = ?grnId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?grnId", $grnId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ap_purchase_master a
Join (Select c.grn_id, coalesce(sum(c.sub_total),0) As subTotal, coalesce(sum(c.disc_amount),0) as sumDiscount, coalesce(sum(c.ppn_amount),0) as sumPpn, coalesce(sum(c.pph_amount),0) as sumPph, coalesce(sum(c.by_angkut),0) as sumByAngkut From t_ap_purchase_detail c Group By c.grn_id) b
On a.id = b.grn_id Set a.base_amount = b.subTotal, a.disc_amount = b.sumDiscount, a.ppn_amount = b.sumPpn, a.pph_amount = b.sumPph, a.other_costs_amount = b.sumByAngkut Where a.id = ?grnId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?grnId", $grnId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ap_purchase_master a Set a.paid_amount = (a.base_amount - a.disc_amount) + a.ppn_amount + a.pph_amount + a.other_costs_amount Where a.id = ?grnId And a.payment_type = 0;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?grnId", $grnId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }
}
// End of File: estimasi_detail.php
