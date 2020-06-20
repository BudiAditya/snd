<?php

class AssemblyDetail extends EntityBase {
	public $Id;
    public $CabangId;
	public $AssemblyId;
    public $AssemblyNo;
	public $ItemDescs;
    public $ItemCode;
    public $ItemId;
	public $Qty;
    public $Price;
    public $SatBesar;
    public $SatKecil;
    public $ItemNote;

	public function FillProperties(array $row) {
		$this->Id = $row["id"];        
		$this->AssemblyId = $row["assembly_id"];
        $this->CabangId = $row["cabang_id"];
        $this->AssemblyNo = $row["assembly_no"];
        $this->ItemId = $row["item_id"];
        $this->ItemCode = $row["item_code"];
		$this->ItemDescs = $row["bnama"];
        $this->Qty = $row["qty"];
        $this->Price = $row["price"];
        $this->SatBesar = $row["bsatbesar"];
        $this->SatKecil = $row["bsatkecil"];
        $this->ItemNote = $row["item_note"];
	}

	public function LoadById($id) {
		$this->connector->CommandText = "SELECT a.*,b.bnama,b.bsatbesar,b.bsatkecil FROM t_ic_assembly_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.*,b.bnama,b.bsatbesar,b.bsatkecil FROM t_ic_assembly_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByAssemblyId($assemblyId, $orderBy = "a.id") {
		$this->connector->CommandText = "SELECT a.*,b.bnama,b.bsatbesar,b.bsatkecil FROM t_ic_assembly_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.assembly_id = ?assemblyId ORDER BY $orderBy";
		$this->connector->AddParameter("?assemblyId", $assemblyId);
		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new AssemblyDetail();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadByAssemblyNo($assemblyNo, $orderBy = "a.id") {
        $this->connector->CommandText = "SELECT a.*,b.bnama,b.bsatbesar,b.bsatkecil FROM t_ic_assembly_detail AS a Join m_barang AS b On a.item_code = b.bkode WHERE a.assembly_no = ?assemblyNo ORDER BY $orderBy";
        $this->connector->AddParameter("?assemblyNo", $assemblyNo);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new AssemblyDetail();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ic_assembly_detail(item_note,assembly_id, cabang_id, assembly_no, item_id, item_code, qty, price) VALUES(?item_note,?assembly_id, ?cabang_id, ?assembly_no, ?item_id, ?item_code, ?qty, ?price)";
		$this->connector->AddParameter("?assembly_id", $this->AssemblyId);
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?assembly_no", $this->AssemblyNo);
        $this->connector->AddParameter("?item_id", $this->ItemId);
		$this->connector->AddParameter("?item_code", $this->ItemCode, "char");
		$this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?item_note", $this->ItemNote);
		$rs = $this->connector->ExecuteNonQuery();
        $rsx = null;
        $did = 0;
        if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
            $did = $this->Id;
            //kurangi stock
            $this->connector->CommandText = "SELECT fc_ic_assemblydetail_post($did) As valresult;";
            $rsx = $this->connector->ExecuteQuery();
		}
		return $rs;
	}

    public function Update($id) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ic_assemblydetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        $this->connector->CommandText =
            "UPDATE t_ic_assembly_detail SET
	  assembly_id = ?assembly_id
	, cabang_id = ?cabang_id
	, assembly_no = ?assembly_no
	, qty = ?qty
	, price = ?price
	, item_code = ?item_code
	, item_id = ?item_id
	, item_note = ?item_note
WHERE id = ?id";
        $this->connector->AddParameter("?assembly_id", $this->InvoiceId);
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        $this->connector->AddParameter("?assembly_no", $this->InvoiceNo);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?item_code", $this->ItemCode, "char");
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?item_note", $this->ItemNote);
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        if ($rs == 1) {
            //potong stock lagi
            $this->connector->CommandText = "SELECT fc_ic_assemblydetail_post($id) As valresult;";
            $rsx = $this->connector->ExecuteQuery();
        }
        return $rs;
    }

	public function Delete($id) {
        //unpost stock dulu
        $rsx = null;
        $this->connector->CommandText = "SELECT fc_ic_assemblydetail_unpost($id) As valresult;";
        $rsx = $this->connector->ExecuteQuery();
        //hapus detail
		$this->connector->CommandText = "DELETE FROM t_ic_assembly_detail WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;

	}

    public function FindDuplicate($cabId,$assId,$itemCd,$itemPrice) {
        $sql = "SELECT a.*,b.bnama,b.bsatbesar,b.bsatkecil FROM t_ic_assembly_detail AS a Join m_barang AS b On a.item_code = b.bkode";
        $sql.= " Where a.cabang_id = $cabId And a.assembly_id = $assId And a.item_code = '$itemCd' And a.price = $itemPrice";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }
}
// End of File: estimasi_detail.php
