<?php

class TransferDetail extends EntityBase {
	public $Id;
    public $NpbId;
    public $ItemCode;
    public $ItemId;
    public $ItemName;
    public $Lqty = 0;
    public $Sqty = 0;
	public $Qty = 0;
    public $SatBesar;
    public $IsiSatKecil = 0;
    public $SatKecil;
    public $Hpp = 0;

	// Helper Variable;
	public $MarkedForDeletion = false;


	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->NpbId = $row["npb_id"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
        $this->ItemName = $row["item_name"];
		$this->Qty = $row["qty"];
        $this->Hpp = $row["hpp"];
        $this->SatBesar = $row["l_uom_code"];
        $this->SatKecil = $row["s_uom_code"];
        $this->IsiSatKecil = $row["s_uom_qty"];
	}

	public function LoadById($id) {
		$this->FindById($id);
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code,b.s_uom_code,b.s_uom_qty FROM t_ic_transfer_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByNpbId($npbId, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code,b.s_uom_code,b.s_uom_qty FROM t_ic_transfer_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.npb_id = ?npbId Order By $orderBy";
		$this->connector->AddParameter("?npbId", $npbId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TransferDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ic_transfer_detail(npb_id, item_id, qty, hpp) VALUES(?npb_id, ?item_id, ?qty, ?hpp)";
		$this->connector->AddParameter("?npb_id", $this->NpbId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?hpp", $this->Hpp);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        $did = 0;
        if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $did = $this->Id;
            //posting
            //if ($did > 0) {
            //    $this->connector->CommandText = "SELECT fc_ic_transferdetail_post($did) As valresult;";
            //    $rsx = $this->connector->ExecuteQuery();
            //}
		}
		return $rs;
	}

	public function Update($id) {
        //unpost stock dulu
        $rsx = null;
        //$this->connector->CommandText = "SELECT fc_ic_transferdetail_unpost($id) As valresult;";
        //$rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText = "UPDATE t_ic_transfer_detail SET npb_id = ?npb_id, qty = ?qty, item_id = ?item_id, hpp = ?hpp WHERE id = ?id";
        $this->connector->AddParameter("?npb_id", $this->NpbId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?hpp", $this->Hpp);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //potong stock lagi
            //$this->connector->CommandText = "SELECT fc_ic_transferdetail_post($id) As valresult;";
            //$rsx = $this->connector->ExecuteQuery();
        }
        return $rs;
	}

	public function Delete($id) {
        //unpost stock dulu
        $rsx = null;
        //$this->connector->CommandText = "SELECT fc_ic_transferdetail_unpost($id) As valresult;";
        //$rsx = $this->connector->ExecuteQuery();
        //hapus detail
		$this->connector->CommandText = "DELETE FROM t_ic_transfer_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
	}
}
// End of File: estimasi_detail.php
