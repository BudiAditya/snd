<?php

class TranscasDetail extends EntityBase {
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
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code,b.s_uom_code,b.s_uom_qty FROM vw_cas_ic_transfer_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByNpbId($npbId, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.item_code,b.item_name,b.l_uom_code,b.s_uom_code,b.s_uom_qty FROM vw_cas_ic_transfer_detail AS a Join m_items AS b On a.item_id = b.id WHERE a.npb_id = ?npbId Order By $orderBy";
		$this->connector->AddParameter("?npbId", $npbId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new TranscasDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}
}
// End of File: estimasi_detail.php
