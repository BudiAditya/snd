<?php

class InvocasDetail extends EntityBase {
	public $Id;
    public $CabangId;
	public $InvoiceId;
	public $ItemDescs;
    public $ItemCode;
    public $ItemId;
    public $Lqty = 0;
    public $Sqty = 0;
	public $SalesQty = 0;
	public $SendQty = 0;
    public $ReturnQty = 0;
	public $Price = 0;
    public $DiscFormula;
    public $DiscAmount = 0;
    public $SubTotal = 0;
    public $SatBesar;
    public $SatKecil;
    public $IsFree = 0;
    public $ExSoId = 0;
    public $EntityCode;
    public $PpnPct = 10;
    public $PpnAmount = 0;
    public $PphPct = 0;
    public $PphAmount = 0;
    public $BatchNo;
    public $ExpDate;
    public $ByAngkut = 0;
    public $IsiSatKecil = 0;
    public $ItemHpp = 0;
    public $IsPost = 0;

	// Helper Variable;
	public $MarkedForDeletion = false;


	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->InvoiceId = $row["invoice_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_name"];
		$this->SalesQty = $row["sales_qty"];
        $this->ReturnQty = $row["return_qty"];
		$this->Price = $row["price"];
        $this->DiscFormula = $row["disc_formula"];
        $this->DiscAmount = $row["disc_amount"];
        $this->SubTotal = $row["sub_total"];
        $this->SatBesar = $row["bsatbesar"];
        $this->SatKecil = $row["bsatkecil"];
        $this->IsFree = $row["is_free"];
        $this->ExSoId = $row["ex_so_id"];
        $this->EntityCode = $row["entity_code"];
        $this->PpnPct = $row["ppn_pct"];
        $this->PpnAmount = $row["ppn_amount"];
        $this->PphPct = $row["pph_pct"];
        $this->PphAmount = $row["pph_amount"];
        $this->ItemHpp = $row["item_hpp"];
        $this->ExpDate = strtotime($row["exp_date"]);
        $this->ByAngkut = $row["by_angkut"];
        $this->IsiSatKecil = $row["bisisatkecil"];
        if ($this->SalesQty >= $this->IsiSatKecil && $this->IsiSatKecil > 0){
            $aqty = array();
            $sqty = round($this->SalesQty/$this->IsiSatKecil,2);
            $aqty = explode('.',$sqty);
            $lqty = $aqty[0];
            $this->Lqty = $lqty;
            $this->Sqty = $this->SalesQty - ($lqty * $this->IsiSatKecil);
        }else {
            $this->Lqty = 0;
            $this->Sqty = $this->SalesQty;
        }
        $this->IsPost = $row["is_post"];
	}

    public function FormatExpDate($format = HUMAN_DATE) {
        return is_int($this->ExpDate) ? date($format, $this->ExpDate) : null;
    }

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,b.entity_code,b.s_uom_qty as bisisatkecil FROM t_cas_ar_invoice_detail AS a Join vw_ic_items AS b On a.item_id = b.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,b.entity_code,b.s_uom_qty as bisisatkecil FROM t_cas_ar_invoice_detail AS a Join vw_ic_items AS b On a.item_id = b.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByInvoiceId($invoiceId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,b.entity_code,b.s_uom_qty as bisisatkecil FROM t_cas_ar_invoice_detail AS a Join vw_ic_items AS b On a.item_id = b.id WHERE a.invoice_id = ?invoiceId ORDER BY $orderBy";
		$this->connector->AddParameter("?invoiceId", $invoiceId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new InvocasDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByInvoiceNo($invoiceNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code AS bsatbesar,b.s_uom_code AS bsatkecil,b.entity_code,b.s_uom_qty as bisisatkecil FROM t_cas_ar_invoice_detail AS a Join vw_ic_items AS b On a.item_id = b.id WHERE a.invoice_no = ?invoiceNo ORDER BY $orderBy";
        $this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new InvocasDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_cas_ar_invoice_detail(is_post,ex_so_id,by_angkut,is_free,invoice_id, item_id, item_descs, sales_qty, return_qty, price, disc_formula, disc_amount, sub_total, pph_pct, pph_amount, ppn_pct, ppn_amount, exp_date, item_hpp)
VALUES(?is_post,?ex_so_id,?by_angkut,?is_free,?invoice_id, ?item_id, ?item_descs, ?sales_qty, ?return_qty, ?price, ?disc_formula, ?disc_amount, ?sub_total, ?pph_pct, ?pph_amount, ?ppn_pct, ?ppn_amount, ?exp_date, ?item_hpp)";
		$this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_descs", $this->ItemDescs == null ? '-' : $this->ItemDescs);
		$this->connector->AddParameter("?sales_qty", $this->SalesQty);
        $this->connector->AddParameter("?return_qty", $this->ReturnQty);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?sub_total", $this->SubTotal);
        $this->connector->AddParameter("?is_free", $this->IsFree == null ? 0 : $this->IsFree);
        $this->connector->AddParameter("?ex_so_id", $this->ExSoId == null || $this->ExSoId == '' ? 0 : $this->ExSoId);
        $this->connector->AddParameter("?ppn_pct", $this->PpnPct);
        $this->connector->AddParameter("?ppn_amount", $this->PpnAmount);
        $this->connector->AddParameter("?pph_pct", $this->PphPct);
        $this->connector->AddParameter("?pph_amount", $this->PphAmount);
        $this->connector->AddParameter("?item_hpp", $this->ItemHpp == null ? 0 : $this->ItemHpp);
        $this->connector->AddParameter("?exp_date", null);
        $this->connector->AddParameter("?by_angkut", $this->ByAngkut);
        $this->connector->AddParameter("?is_post", $this->IsPost);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        $did = 0;
        if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $did = $this->Id;
            //tambah stock
            $this->connector->CommandText = "SELECT fc_ar_invoicedetail_post($did) As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update po status (jika ada)
            $this->connector->CommandText = "SELECT fc_ar_so_checkstatus('".$this->ExSoId."') As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update invoice
            $this->UpdateInvoiceMaster($this->InvoiceId);
		}
		return $rs;
	}

	public function Update($id) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ar_invoicedetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText =
"UPDATE t_cas_ar_invoice_detail SET
	  invoice_id = ?invoice_id
	, item_descs = ?item_descs
	, sales_qty = ?sales_qty
	, return_qty = ?return_qty
	, price = ?price
	, sub_total = ?sub_total
	, item_id = ?item_id
	, disc_formula = ?disc_formula
	, disc_amount = ?disc_amount
	, is_free = ?is_free
	, ex_so_id = ?ex_so_id
	, pph_pct = ?ppn_pct
	, pph_amount = ?pph_amount
	, ppn_pct = ?ppn_pct
	, ppn_amount = ?ppn_amount
	, exp_date = ?exp_date
	, by_angkut = ?by_angkut
	, item_hpp = ?item_hpp
	, is_post = ?is_post
WHERE id = ?id";
        $this->connector->AddParameter("?invoice_id", $this->InvoiceId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_descs", $this->ItemDescs == null ? '-' : $this->ItemDescs);
        $this->connector->AddParameter("?sales_qty", $this->SalesQty);
        $this->connector->AddParameter("?return_qty", $this->ReturnQty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?sub_total", $this->SubTotal);
        $this->connector->AddParameter("?is_free", $this->IsFree == null ? 0 : $this->IsFree);
        $this->connector->AddParameter("?ex_so_id", $this->ExSoId == null || $this->ExSoId == '' ? 0 : $this->ExSoId);
        $this->connector->AddParameter("?pph_pct", $this->PphPct);
        $this->connector->AddParameter("?pph_amount", $this->PphAmount);
        $this->connector->AddParameter("?ppn_pct", $this->PpnPct);
        $this->connector->AddParameter("?ppn_amount", $this->PpnAmount);
        $this->connector->AddParameter("?item_hpp", $this->ItemHpp == null ? 0 : $this->ItemHpp);
        $this->connector->AddParameter("?exp_date", null);
        $this->connector->AddParameter("?by_angkut", $this->ByAngkut);
        $this->connector->AddParameter("?is_post", $this->IsPost);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //potong stock lagi
            $this->connector->CommandText = "SELECT fc_ar_invoicedetail_post($id) As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update po status (jika ada)
            $this->connector->CommandText = "SELECT fc_ar_so_checkstatus('".$this->ExSoId."') As valresult;";
            $rsx = $this->connector->ExecuteQuery();
            //update invoice master
            $this->UpdateInvoiceMaster($this->InvoiceId);
        }
        return $rs;
	}

	public function UpdateHpp(){
        $this->connector->CommandText = "Update t_cas_ar_invoice_detail a Set a.item_hpp = ?hpp, a.is_post = ?isp Where a.id = ?id";
        $this->connector->AddParameter("?hpp", $this->ItemHpp);
        $this->connector->AddParameter("?isp", $this->IsPost);
        $this->connector->AddParameter("?id", $this->Id);
        return $this->connector->ExecuteNonQuery();
    }

	public function Delete($id) {
        //unpost stock dulu
        $rsx = null;
        $pid = $this->ExSoId;
        $this->connector->CommandText = "SELECT fc_ar_invoicedetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //hapus detail
		$this->connector->CommandText = "DELETE FROM t_cas_ar_invoice_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //update so status (jika ada)
            //$this->connector->CommandText = "SELECT fc_ar_so_checkstatus('".$pid."') As valresult;";
            $this->connector->CommandText = "Update t_ar_order a Set a.send_qty = a.send_qty - ".$this->SalesQty.", a.keterangan = null Where a.id = $pid";
            $this->connector->ExecuteNonQuery();
            //update GRN master
            $this->UpdateInvoiceMaster($this->InvoiceId);
        }
        return $rs;
	}

    public function UpdateInvoiceMaster($invoiceId){
        $sql = 'Update t_ar_invoice_master a Set a.base_amount = 0, a.ppn_amount = 0, a.pph_amount = 0, a.disc_amount = 0, a.other_costs_amount = 0 Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_invoice_master a
Join (Select c.invoice_id, coalesce(sum(c.sub_total),0) As subTotal, coalesce(sum(c.disc_amount),0) as sumDiscount, coalesce(sum(c.ppn_amount),0) as sumPpn, coalesce(sum(c.pph_amount),0) as sumPph, coalesce(sum(c.by_angkut),0) as sumByAngkut From t_cas_ar_invoice_detail c Group By c.invoice_id) b
On a.id = b.invoice_id Set a.base_amount = b.subTotal, a.disc_amount = b.sumDiscount, a.ppn_amount = b.sumPpn, a.pph_amount = b.sumPph, a.other_costs_amount = b.sumByAngkut Where a.id = ?invoiceId;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        $sql = 'Update t_ar_invoice_master a Set a.paid_amount = (a.base_amount - a.disc_amount) + a.ppn_amount + a.pph_amount + a.other_costs_amount Where a.id = ?invoiceId And a.payment_type = 0;';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?invoiceId", $invoiceId);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }
}
// End of File: estimasi_detail.php
