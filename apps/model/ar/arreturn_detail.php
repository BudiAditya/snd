<?php

class ArReturnDetail extends EntityBase {
	public $Id;
	public $RjId;
    public $ExInvoiceId;
    public $ExInvoiceNo;
    public $ExInvDetailId;
    public $ItemId;
    public $ItemCode;
	public $ItemDescs;
    public $QtyJual = 0;
	public $QtyRetur = 0;
    public $Kondisi = 0;
	public $Price = 0;
    public $DiscFormula;
    public $DiscAmount = 0;
    public $PpnPct = 0;
    public $PphPct = 0;
    public $ItemHpp = 0;
    public $SatBesar;
    public $SatKecil;
    public $IsFree = 0;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->RjId = $row["rj_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_name"];
        $this->ExInvoiceId = $row["ex_invoice_id"];
        $this->ExInvoiceNo = $row["ex_invoice_no"];
        $this->ExInvDetailId = $row["ex_invdetail_id"];
        $this->QtyJual = $row["qty_jual"];
		$this->QtyRetur = $row["qty_retur"];
		$this->Price = $row["price"];
        $this->DiscFormula = $row["disc_formula"];
        $this->DiscAmount = $row["disc_amount"];
        $this->SatBesar = $row["bsatbesar"];
        $this->SatKecil = $row["bsatkecil"];
        $this->Kondisi = $row["kondisi"];
        $this->ItemHpp = $row["item_hpp"];
        $this->IsFree = $row["is_free"];
        $this->PpnPct = $row["ppn_pct"];
        $this->PphPct = $row["pph_pct"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,c.invoice_no as ex_invoice_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ar_invoice_master c ON a.ex_invoice_id = c.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,c.invoice_no as ex_invoice_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ar_invoice_master c ON a.ex_invoice_id = c.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByRjId($rjId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,c.invoice_no as ex_invoice_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ar_invoice_master c ON a.ex_invoice_id = c.id WHERE a.rj_id = ?rjId ORDER BY $orderBy";
		$this->connector->AddParameter("?rjId", $rjId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ArReturnDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByRjNo($invoiceNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,c.invoice_no as ex_invoice_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ar_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ar_invoice_master c ON a.ex_invoice_id = c.id WHERE a.rj_no = ?invoiceNo ORDER BY $orderBy";
        $this->connector->AddParameter("?invoiceNo", $invoiceNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ArReturnDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ar_return_detail(rj_id, ex_invoice_id, ex_invdetail_id, item_id, qty_jual, qty_retur, price, disc_amount, kondisi, disc_formula, is_free, ppn_pct, pph_pct, item_hpp)
VALUES(?rj_id, ?ex_invoice_id, ?ex_invdetail_id, ?item_id, ?qty_jual, ?qty_retur, ?price, ?disc_amount, ?kondisi, ?disc_formula, ?is_free, ?ppn_pct, ?pph_pct, ?item_hpp)";
		$this->connector->AddParameter("?rj_id", $this->RjId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?ex_invoice_id", $this->ExInvoiceId);
        $this->connector->AddParameter("?ex_invdetail_id", $this->ExInvDetailId);
        $this->connector->AddParameter("?qty_jual", $this->QtyJual);
		$this->connector->AddParameter("?qty_retur", $this->QtyRetur);
		$this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?disc_formula", $this->DiscFormula);
        $this->connector->AddParameter("?disc_amount", $this->DiscAmount);
        $this->connector->AddParameter("?pph_pct", $this->PphPct);
        $this->connector->AddParameter("?ppn_pct", $this->PpnPct);
        $this->connector->AddParameter("?kondisi", $this->Kondisi);
        $this->connector->AddParameter("?is_free", $this->IsFree);
        $this->connector->AddParameter("?item_hpp", $this->ItemHpp);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        $did = 0;
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $did = $this->Id;
		}
		return $rs;
	}

	public function Delete($id) {
        //baru hapus detail
		$this->connector->CommandText = "DELETE FROM t_ar_return_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
	}

	public function Posting($id){
        $this->connector->CommandText = "SELECT fc_ar_returndetail_post($id) As valresult;";
        return $this->connector->ExecuteQuery();
    }

    public function Unposting($id){
        $this->connector->CommandText = "SELECT fc_ar_returndetail_unpost($id) As valresult;";
        return $this->connector->ExecuteQuery();
    }

}
// End of File: estimasi_detail.php
