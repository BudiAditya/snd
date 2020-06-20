<?php

class ApReturnDetail extends EntityBase {
	public $Id;
	public $RbId;
    public $ExGrnId;
    public $ExGrnNo;
    public $ExGrnDetailId;
    public $ItemId;
    public $ItemCode;   
	public $ItemDescs;
    public $QtyBeli = 0;
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
		$this->RbId = $row["rb_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["item_name"];
        $this->ExGrnId = $row["ex_grn_id"];
        $this->ExGrnNo = $row["ex_grn_no"];
        $this->ExGrnDetailId = $row["ex_grndetail_id"];
        $this->QtyBeli = $row["qty_beli"];
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
		$this->connector->CommandText = "SELECT a.*,c.grn_no as ex_grn_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ap_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ap_purchase_master c ON a.ex_grn_id = c.id WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,c.grn_no as ex_grn_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ap_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ap_purchase_master c ON a.ex_grn_id = c.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByRbId($rbId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,c.grn_no as ex_grn_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ap_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ap_purchase_master c ON a.ex_grn_id = c.id WHERE a.rb_id = ?rbId ORDER BY $orderBy";
		$this->connector->AddParameter("?rbId", $rbId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new ApReturnDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByRbNo($grnNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,c.grn_no as ex_grn_no,b.item_code,b.item_name,b.l_uom_code as bsatbesar,b.s_uom_code as bsatkecil FROM t_ap_return_detail AS a Join m_items AS b On a.item_id = b.id Join t_ap_purchase_master c ON a.ex_grn_id = c.id WHERE a.rb_no = ?grnNo ORDER BY $orderBy";
        $this->connector->AddParameter("?grnNo", $grnNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new ApReturnDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText =
"INSERT INTO t_ap_return_detail(rb_id, ex_grn_id, ex_grndetail_id, item_id, qty_beli, qty_retur, price, disc_amount, kondisi, disc_formula, is_free, ppn_pct, pph_pct, item_hpp)
VALUES(?rb_id, ?ex_grn_id, ?ex_grndetail_id, ?item_id, ?qty_beli, ?qty_retur, ?price, ?disc_amount, ?kondisi, ?disc_formula, ?is_free, ?ppn_pct, ?pph_pct, ?item_hpp)";
		$this->connector->AddParameter("?rb_id", $this->RbId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?ex_grn_id", $this->ExGrnId);
        $this->connector->AddParameter("?ex_grndetail_id", $this->ExGrnDetailId);
        $this->connector->AddParameter("?qty_beli", $this->QtyBeli);
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
            //posting
            $this->connector->CommandText = "SELECT fc_ap_returndetail_post($did) As valresult;";
            $this->connector->ExecuteQuery();
		}
		return $rs;
	}

	public function Delete($id) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ap_returndetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //baru hapus detail
		$this->connector->CommandText = "DELETE FROM t_ap_return_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}

}
// End of File: estimasi_detail.php
