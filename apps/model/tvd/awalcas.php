<?php
class AwalCas extends EntityBase {
	public $Id;
	public $WarehouseId = 0;
	public $TrxNo;
	public $ItemId;
    public $OpDate;   
    public $OpQty = 0;
    public $Hpp = 0;
    public $DocStatus = 0;
    public $CreatebyId;
    public $UpdatebyId;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
        $this->TrxNo = $row["trx_no"];
		$this->WarehouseId = $row["warehouse_id"];
		$this->ItemId = $row["item_id"];
        $this->OpDate = strtotime($row["op_date"]);
        $this->OpQty = $row["op_qty"];
        $this->Hpp = $row["hpp"];
        $this->DocStatus = $row["doc_status"];
        $this->CreatebyId = $row["createby_id"];
        $this->UpdatebyId = $row["updateby_id"];
	}

    public function FormatOpDate($format = HUMAN_DATE) {
        return is_int($this->OpDate) ? date($format, $this->OpDate) : date($format, strtotime(date('Y-m-d')));
    }

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Location[]
	 */
	public function LoadAll($cabangId = 0,$orderBy = "a.cabang_id, a.item_code") {
        $this->connector->CommandText = "SELECT a.* FROM t_cas_ic_saldoawal AS a JOIN m_warehouse b ON a.warehouse_id = b.id Where b.cabang_id = $cabangId ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new AwalCas();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return Location
	 */
	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM t_cas_ic_saldoawal AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByItemId($cabangId,$itemId) {
        $this->connector->CommandText = "SELECT a.* FROM t_cas_ic_saldoawal AS a WHERE a.cabang_id = ?cabangId And a.item_id = ?itemId";
        $this->connector->AddParameter("?cabangId", $cabangId);
        $this->connector->AddParameter("?itemId", $itemId);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

	/**
	 * @param int $id
	 * @return Location
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	public function Insert() {
        $sql = 'INSERT INTO t_cas_ic_saldoawal (trx_no,hpp,warehouse_id,item_id,op_date,op_qty,createby_id,create_time)';
        $sql.= ' VALUES(?trx_no,?hpp,?warehouse_id,?item_id,?op_date,?op_qty,?createby_id,now())';
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?trx_no", $this->TrxNo);
        $this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?op_date", $this->OpDate);
        $this->connector->AddParameter("?op_qty", $this->OpQty);
        $this->connector->AddParameter("?hpp", $this->Hpp);
        $this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $rs = $this->connector->ExecuteNonQuery();
        $ret = 0;
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
            $ret = $this->Id;
        }
		return $ret;
	}

	public function Delete($id) {
        $this->connector->CommandText = 'Delete From t_cas_ic_saldoawal Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function GetData($entityId = 0,$cabangId = 0,$offset,$limit,$field,$search='',$sort = 'a.item_code',$order = 'ASC') {
        $sql = "SELECT a.* FROM vw_cas_ic_saldoawal as a Where a.item_id > 0 ";
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ".$cabangId;
        }
        if ($entityId > 0){
            $sql.= " And a.entity_id = ".$entityId;
        }
        if ($search !='' && $field !=''){
            $sql.= " And $field Like '%{$search}%' ";
        }
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order Limit {$offset},{$limit}";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $rows = array();
        if ($rs != null) {
            $i = 0;
            while ($row = $rs->FetchAssoc()) {
                $rows[$i]['id'] = $row['id'];
                $rows[$i]['cabang_id'] = $row['cabang_id'];
                $rows[$i]['warehouse_id'] = $row['warehouse_id'];
                $rows[$i]['cabang_code'] = $row['cabang_code'];
                $rows[$i]['whcode'] = $row['wh_code'];
                $rows[$i]['item_id'] = $row['item_id'];
                $rows[$i]['item_code'] = $row['item_code'];
                $rows[$i]['op_date'] = $row['op_date'];
                $rows[$i]['op_qty'] = $row['op_qty'];
                $rows[$i]['bnama'] = $row['bnama'];
                $rows[$i]['bsatbesar'] = $row['bsatbesar'];
                $rows[$i]['bsatsedang'] = $row['bsatsedang'];
                $rows[$i]['bsatkecil'] = $row['bsatkecil'];
                $rows[$i]['bsatstock'] = $row['bsatstock'];
                $i++;
            }
        }
        //data hasil query yang dikirim kembali dalam format json
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetTrxNo($cabangId){
        $sql = 'Select fc_sys_getdocno(?cbi,?txc,?txd) As valout;';
        $txc = 'OC';
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?cbi", $cabangId);
        $this->connector->AddParameter("?txc", $txc);
        $this->connector->AddParameter("?txd", $this->OpDate);
        $rs = $this->connector->ExecuteQuery();
        $val = null;
        if($rs){
            $row = $rs->FetchAssoc();
            $val = $row["valout"];
        }
        return $val;
    }

    public function CreateStockAwalCas($trxYear){
	    $sql = "Delete From t_cas_ic_saldoawal Where Year(op_date) = $trxYear";
        $this->connector->CommandText = $sql;
        $this->connector->ExecuteNonQuery();
	    $sql = "Insert Into t_cas_ic_saldoawal (warehouse_id,op_date,trx_no,item_id,op_qty,hpp)
                Select a.warehouse_id,a.op_date,'-',a.item_id,a.op_qty,a.hpp
                From t_ic_saldoawal a Join m_items b ON a.item_id = b.id And b.principal_id = 1 Join m_warehouse c ON a.warehouse_id = c.id And c.is_trx = 1
                Where Year(a.op_date) = $trxYear Order By a.op_date,a.item_id";
        $this->connector->CommandText = $sql;
        return $this->connector->ExecuteNonQuery();
    }
}
