<?php

require_once("transcas_detail.php");

class Transcas extends EntityBase {
	private $editableDocId = array(1, 2, 3, 4);

	public static $NpbStatusCodes = array(
		0 => "DRAFT",
		1 => "POSTED",
        2 => "CLOSED",
		3 => "VOID"
	);
   
	public $Id;
    public $IsDeleted = false;
    public $CabangId;
    public $CabangCode;
	public $NpbNo;
	public $NpbDate;
    public $NpbDescs;
    public $FrWhId;
    public $FrWhCode;
    public $FrWhName;
    public $ToWhId;
    public $ToWhCode;
    public $ToWhName;
    public $NpbStatus;
	public $CreatebyId;
	public $CreateTime;
	public $UpdatebyId;
	public $UpdateTime;
    public $UserName;


	/** @var TranscasDetail[] */
	public $Details = array();

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->LoadById($id);
		}
	}

	public function FillProperties(array $row) {
        $this->Id = $row["id"];
        $this->IsDeleted = $row["is_deleted"] == 1;
        $this->CabangId = $row["cabang_id"];
        $this->CabangCode = $row["cabang_code"];
        $this->NpbNo = $row["npb_no"];
        $this->NpbDate = strtotime($row["npb_date"]);
        $this->FrWhId = $row["fr_wh_id"];
        $this->FrWhCode = $row["fr_wh_code"];
        $this->FrWhName = $row["fr_wh_name"];
        $this->ToWhId = $row["to_wh_id"];
        $this->ToWhCode = $row["to_wh_code"];
        $this->ToWhName = $row["to_wh_name"];
        $this->NpbDescs = $row["npb_descs"];
        $this->NpbStatus = $row["npb_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->CreateTime = $row["create_time"];
        $this->UpdatebyId = $row["updateby_id"];
        $this->UpdateTime = $row["update_time"];
        //$this->UserName = $row["admin_name"];
	}

	public function FormatNpbDate($format = HUMAN_DATE) {
		return is_int($this->NpbDate) ? date($format, $this->NpbDate) : date($format, strtotime(date('Y-m-d')));
	}

	/**
	 * @return TranscasDetail[]
	 */
	public function LoadDetails() {
		if ($this->Id == null) {
			return $this->Details;
		}
		$detail = new TranscasDetail();
		$this->Details = $detail->LoadByNpbId($this->Id);
		return $this->Details;
	}

	/**
	 * @param int $id
	 * @return Npb
	 */
	public function LoadById($id) {
		$this->FindById($id);
		return $this;
	}

    public function FindById($id) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cas_ic_transfer_master AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	public function LoadByNpbNo($npbNo) {
		$this->connector->CommandText = "SELECT a.* FROM vw_cas_ic_transfer_master AS a WHERE a.npb_no = ?npbNo";
		$this->connector->AddParameter("?npbNo", $npbNo);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$this->FillProperties($rs->FetchAssoc());
		return $this;
	}

    public function LoadByCabangId($cabangId) {
        $this->connector->CommandText = "SELECT a.* FROM vw_cas_ic_transfer_master AS a WHERE a.cabang_id = ?cabangId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Transcas();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function Load4Reports($cabangId = 0,$gudangId = 0, $startDate = null, $endDate = null) {
        $sql = "SELECT a.*,c.item_code,c.item_name,c.s_uom_code as satuan,b.qty";
        $sql.= " FROM vw_cas_ic_transfer_master AS a Join t_ic_transfer_detail AS b On a.id = b.npb_id Left Join m_items AS c On b.item_id = c.id";
        $sql.= " WHERE a.is_deleted = 0 and a.npb_status <> 3 and a.npb_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($gudangId > 0){
            $sql.= " and a.fr_wh_id = ".$gudangId;
        }
        $sql.= " Order By a.npb_date,a.npb_no,c.item_name,a.id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function LoadRekap4Reports($cabangId = 0,$gudangId = 0,$startDate = null, $endDate = null) {
        $sql = "SELECT a.cabang_code,a.to_wh_code,a.fr_wh_code,c.item_code,c.item_name,c.s_uom_code as satuan,sum(b.qty) as sum_qty";
        $sql.= " FROM vw_cas_ic_transfer_master AS a Join t_ic_transfer_detail AS b On a.id = b.npb_id Join m_items AS c On b.item_id = c.id";
        $sql.= " WHERE a.is_deleted = 0 and a.npb_status <> 3 and a.npb_date BETWEEN ?startdate and ?enddate";
        if ($cabangId > 0){
            $sql.= " and a.cabang_id = ".$cabangId;
        }
        if ($gudangId > 0){
            $sql.= " and a.fr_wh_id = ".$gudangId;
        }
        $sql.= " Group By a.fr_wh_code,a.to_wh_code,c.item_code,c.item_name,c.s_uom_code Order By a.cabang_code,c.item_name";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?startdate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?enddate", date('Y-m-d', $endDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
