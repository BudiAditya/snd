<?php
class StockCas extends EntityBase {
	public $Id;
	public $WarehouseId = 0;
	public $ItemId;
    public $TrxYear;   
    public $OpQty = 0;
    public $InQty = 0;
    public $IxQty = 0;
    public $IrQty = 0;
    public $OtQty = 0;
    public $OxQty = 0;
    public $OrQty = 0;
    public $AjQty = 0;
    public $Price = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
        $this->WarehouseId = $row["warehouse_id"];
		$this->ItemId = $row["item_id"];
        $this->TrxYear = $row["trx_year"];
        $this->OpQty = $row["op_qty"];
        $this->InQty = $row["in_qty"];
        $this->IxQty = $row["ix_qty"];
        $this->IrQty = $row["ir_qty"];
        $this->OtQty = $row["ot_qty"];
        $this->OxQty = $row["ox_qty"];
        $this->OrQty = $row["or_qty"];
        $this->AjQty = $row["aj_qty"];
        $this->Price = $row["price"];
	}
	
	public function LoadAll($cabangId = 0,$orderBy = "a.cabang_id, a.item_code") {
        $this->connector->CommandText = "SELECT a.* FROM t_cas_ic_stock AS a JOIN m_warehouse b ON a.warehouse_id = b.id Where b.cabang_id = $cabangId ORDER BY $orderBy";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new StockCas();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	public function FindById($id) {
		$this->connector->CommandText = "SELECT a.* FROM t_cas_ic_stock AS a WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();

		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}
		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function FindByItemId($trxYear,$warehouseId,$itemId) {
        $this->connector->CommandText = "SELECT a.* FROM t_cas_ic_stock AS a WHERE a.trx_year = ?trxYear And a.warehouse_id = ?warehouseId And a.item_id = ?itemId";
        $this->connector->AddParameter("?trxYear", $trxYear);
        $this->connector->AddParameter("?warehouseId", $warehouseId);
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
        $sql = 'INSERT INTO t_cas_ic_stock (in_qty,warehouse_id,item_id,trx_year,op_qty,ir_qty,ot_qty,ox_qty,or_qty,aj_qty,price)';
        $sql.= ' VALUES(?in_qty,?warehouse_id,?item_id,?trx_year,?op_qty,?ir_qty,?ot_qty,?ox_qty,?or_qty,?aj_qty,?price)';
		$this->connector->CommandText = $sql;
        $this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?trx_year", $this->TrxYear);
        $this->connector->AddParameter("?op_qty", $this->OpQty);
        $this->connector->AddParameter("?in_qty", $this->InQty);
        $this->connector->AddParameter("?ir_qty", $this->IrQty);
        $this->connector->AddParameter("?ot_qty", $this->OtQty);
        $this->connector->AddParameter("?ox_qty", $this->OxQty);
        $this->connector->AddParameter("?or_qty", $this->OrQty);
        $this->connector->AddParameter("?aj_qty", $this->AjQty);
        $this->connector->AddParameter("?price", $this->Price);
        $rs = $this->connector->ExecuteNonQuery();
        $ret = 0;
        if ($rs == 1) {
            $this->connector->CommandText = "SELECT LAST_INSERT_ID();";
            $this->Id = (int)$this->connector->ExecuteScalar();
            $ret = $this->Id;
        }
		return $ret;
	}

    public function Update($id) {
        $sql = "Update t_cas_ic_stock a 
        Set in_qty = ?in_qty,
        warehouse_id = ?warehouse_id,
        item_id = ?item_id,
        trx_year = ?trx_year,
        op_qty = ?op_qty,
        ir_qty = ?ir_qty,
        ot_qty = ?ot_qty,
        ox_qty = ?ox_qty,
        or_qty = ?or_qty,
        aj_qty = ?aj_qty,
        price = ?price
        Where a.id = $id";
        $this->connector->CommandText = $sql;
        $this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?trx_year", $this->TrxYear);
        $this->connector->AddParameter("?op_qty", $this->OpQty);
        $this->connector->AddParameter("?in_qty", $this->InQty);
        $this->connector->AddParameter("?ir_qty", $this->IrQty);
        $this->connector->AddParameter("?ot_qty", $this->OtQty);
        $this->connector->AddParameter("?ox_qty", $this->OxQty);
        $this->connector->AddParameter("?or_qty", $this->OrQty);
        $this->connector->AddParameter("?aj_qty", $this->AjQty);
        $this->connector->AddParameter("?price", $this->Price);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

	public function Delete($id) {
        $this->connector->CommandText = 'Delete From t_cas_ic_stock Where id = ?id';
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteNonQuery();
        return $rs;
    }

    public function RecreateStock($trxYear) {
        $this->connector->CommandText = "SELECT fcCasRecreateStockData($trxYear) As valresult;";
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetStockHistory($trxYear,$startDate = null, $endDate = null){
        $sqx   = null;
        $fkali = 1;
        // create card temp table1
        $sqx = 'CREATE TEMPORARY TABLE `tmp_card` (
                `trx_date`  date NOT NULL ,
                `trx_type`  varchar(50),
                `trx_url`  varchar(50),
                `relasi`  varchar(50),
                `price`  int(11) DEFAULT 0,
                `awalcas`  decimal(11,2) NOT NULL DEFAULT 0,
                `masuk`  decimal(11,2) NOT NULL DEFAULT 0,
                `keluar`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0,
                `saldo`  decimal(11,2) NOT NULL DEFAULT 0,
                `notes` varchar(250))';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // create card temp table2
        $sqx = 'CREATE TEMPORARY TABLE `tmp_card1` (
                `seq_no` int(1) NOT NULL DEFAULT 0,
                `trx_date`  date NOT NULL ,
                `trx_type`  varchar(50),
                `trx_url`  varchar(50),
                `relasi`  varchar(50),
                `price`  int(11) DEFAULT 0,
                `awalcas`  decimal(11,2) NOT NULL DEFAULT 0,
                `masuk`  decimal(11,2) NOT NULL DEFAULT 0,
                `keluar`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0,
                `saldo`  decimal(11,2) NOT NULL DEFAULT 0,
                `notes` varchar(250))';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // get saldo awalcas
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,awalcas,relasi,price) Select a.op_date,'Saldo Awal','tvd.awalcas',a.op_qty,'-',0 From t_cas_ic_saldoawal as a";
        $sqx.= " Where a.item_id = ?item_id And Year(a.op_date) = ?year And a.warehouse_id = ?gudang_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

        // get pembelian
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,masuk,relasi)";
        $sqx.= " Select a.grn_date,concat('Pembelian - ',a.grn_no),concat('ap.purchase/view/',a.id),b.price,b.purchase_qty,concat(a.supplier_name,' (',a.supplier_code,')')";
        $sqx.= " From vw_ap_purchase_master as a Join t_ap_purchase_detail as b On a.id = b.grn_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.grn_date) = ?year and a.gudang_id = ?gudang_id and a.is_deleted = 0 and a.grn_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer masuk
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,masuk,relasi)";
        $sqx.= " Select a.npb_date,concat('Pengiriman - ',a.npb_no),concat('tvd.transcas/view/',a.id),0,b.qty,concat('Dari Gudang - ',a.fr_wh_code)";
        $sqx.= " From vw_ic_transfer_master a Join t_ic_transfer_detail b On a.id = b.npb_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.npb_date) = ?year and a.to_wh_id = ?gudang_id and a.is_deleted = 0 and a.npb_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();
        /*
        // get return ex penjualan
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,masuk,relasi)";
        $sqx.= " Select a.rj_date,concat('Return - ',a.rj_no),concat('ar.arreturn/view/',a.id),0,b.qty_retur,concat(a.customer_name,' (',a.customer_code,')')";
        $sqx.= " From vw_ar_return_master a Join t_ar_return_detail b On a.id = b.rj_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.rj_date) = ?year and a.gudang_id = ?gudang_id and a.is_deleted = 0 and a.rj_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();
        */
        // get penjualan
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,keluar,relasi,notes)";
        $sqx.= " Select a.invoice_date,concat('Penjualan - ',a.invoice_no),concat('ar.invoice/view/',a.id),b.price,b.sales_qty,concat(a.customer_name,' (',a.customer_code,')'),b.item_descs";
        $sqx.= " From vw_cas_invoice_master as a Join t_cas_ar_invoice_detail as b On a.id = b.invoice_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.invoice_date) = ?year and a.gudang_id = ?gudang_id and a.is_deleted = 0 and a.invoice_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer keluar
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,keluar,relasi)";
        $sqx.= " Select a.npb_date,concat('Pengiriman - ',a.npb_no),concat('tvd.transcas/view/',a.id),0,b.qty,concat('Ke Gudang - ',a.to_wh_code)";
        $sqx.= " From vw_ic_transfer_master a Join t_ic_transfer_detail b On a.id = b.npb_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.npb_date) = ?year and a.fr_wh_id = ?gudang_id and a.is_deleted = 0 and a.npb_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();
        /*
        // get return ex pembelian
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,keluar,relasi)";
        $sqx.= " Select a.rb_date,concat('Return - ',a.rb_no),concat('ap.apreturn/view/',a.id),0,b.qty_retur,concat(a.supplier_name,' (',a.supplier_code,')')";
        $sqx.= " From vw_ap_return_master a Join t_ap_return_detail b On a.id = b.rb_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.rb_date) = ?year and a.gudang_id = ?gudang_id and a.is_deleted = 0 and a.rb_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

        // get koreksi
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,koreksi,relasi)";
        $sqx.= " Select a.corr_date,concat('Koreksi - ',a.corr_no),'inventory.correction',a.corr_qty,a.corr_reason";
        $sqx.= " From t_ic_stock_correction as a";
        $sqx.= " Where a.item_id = ?item_id and Year(a.corr_date) = ?year and a.warehouse_id = ?gudang_id and a.corr_status = 1";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();
        */
        //filter data
        $sqx = "Insert Into tmp_card1 (seq_no,trx_date,trx_type,saldo)";
        $sqx.= " Select 0,'".date('Y-m-d',$startDate)."','Saldo lalu...',coalesce(sum((a.awalcas+a.masuk+a.koreksi)-a.keluar),0) From tmp_card a Where a.trx_date < '".date('Y-m-d',$startDate)."'";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        $sqx = "Insert Into tmp_card1 (seq_no,trx_date,trx_type,trx_url,relasi,price,awalcas,masuk,keluar,koreksi,saldo,notes)";
        $sqx.= " Select 1,trx_date,trx_type,trx_url,relasi,price,awalcas,masuk,keluar,koreksi,saldo,notes From tmp_card a Where a.trx_date >= '".date('Y-m-d',$startDate)."' And a.trx_date <= '".date('Y-m-d',$endDate)."'";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // try get all tmp card data
        $sqx = "Select * From tmp_card1 Order By trx_date,seq_no";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetMutasiStock($trxYear, $whId = 0, $startDate = null, $endDate = null, $entityId = 0){
        $sqx = null;
        // create previous mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_prev` (
                `item_id`  int(5) NOT NULL DEFAULT 0,
                `awalcas`  decimal(11,2) NOT NULL DEFAULT 0,
                `beli`  decimal(11,2) NOT NULL DEFAULT 0,
                `xin`  decimal(11,2) NOT NULL DEFAULT 0,
                `rjual`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyin`  decimal(11,2) NOT NULL DEFAULT 0,
                `jual`  decimal(11,2) NOT NULL DEFAULT 0,
                `xout`  decimal(11,2) NOT NULL DEFAULT 0,
                `rbeli`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyout`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // create request mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_mutasi` (
                `item_id`  int(5) NOT NULL DEFAULT 0,
                `awalcas`  decimal(11,2) NOT NULL DEFAULT 0,
                `beli`  decimal(11,2) NOT NULL DEFAULT 0,
                `xin`  decimal(11,2) NOT NULL DEFAULT 0,
                `rjual`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyin`  decimal(11,2) NOT NULL DEFAULT 0,
                `jual`  decimal(11,2) NOT NULL DEFAULT 0,
                `xout`  decimal(11,2) NOT NULL DEFAULT 0,
                `rbeli`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyout`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // get saldo awalcas
        $sqx = "Insert Into `tmp_prev` (item_id,awalcas) Select a.item_id,sum(a.op_qty) From t_ic_saldoawal as a";
        $sqx.= " Where year(a.op_date) = ?year And a.op_date <= ?startDate and a.warehouse_id = ?whId Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get pembelian lalu
        $sqx = "Insert Into `tmp_prev` (item_id,beli) Select a.item_id,sum(a.purchase_qty) From t_ap_purchase_detail as a";
        $sqx.= " Join t_ap_purchase_master as b On a.grn_id = b.id";
        $sqx.= " Where Year(b.grn_date) = ?year And b.grn_status <> 3 And b.grn_date < ?startDate and b.gudang_id = ?whId Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer masuk lalu
        $sqx = "Insert Into `tmp_prev` (item_id,xin) Select a.item_id,sum(a.qty) From t_ic_transfer_detail as a";
        $sqx.= " Join t_ic_transfer_master as b On a.npb_id = b.id";
        $sqx.= " Where Year(b.npb_date) = ?year And b.npb_status <> 3 and b.npb_date < ?startDate and b.to_wh_id = ?whId Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get return ex penjualan lalu
        $sqx = "Insert Into `tmp_prev` (item_id,rjual) Select a.item_id,sum(a.qty_retur) From t_ar_return_detail as a";
        $sqx.= " Join t_ar_return_master as b On a.rj_id = b.id";
        $sqx.= " Where Year(b.rj_date) = ?year And b.rj_status <> 3 and b.rj_date < ?startDate and b.gudang_id = ?whId And b.is_deleted = 0 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get penjualan lalu
        $sqx = "Insert Into `tmp_prev` (item_id,jual) Select a.item_id,sum(a.sales_qty) From t_ar_invoice_detail as a";
        $sqx.= " Join t_ar_invoice_master as b On a.invoice_id = b.id";
        $sqx.= " Where Year(b.invoice_date) = ?year And b.invoice_date < ?startDate and b.gudang_id = ?whId And b.is_deleted = 0 and b.invoice_status <>3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer keluar lalu
        $sqx = "Insert Into `tmp_prev` (item_id,xout) Select a.item_id,sum(a.qty) From t_ic_transfer_detail as a";
        $sqx.= " Join t_ic_transfer_master as b On a.npb_id = b.id";
        $sqx.= " Where Year(b.npb_date) = ?year And b.npb_date < ?startDate and b.fr_wh_id = ?whId and b.npb_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get return ex pembelian lalu
        $sqx = "Insert Into `tmp_prev` (item_id,rbeli) Select a.item_id,sum(a.qty_retur) From t_ap_return_detail as a";
        $sqx.= " Join t_ap_return_master as b On a.rb_id = b.id";
        $sqx.= " Where Year(b.rb_date) = ?year And b.rb_date < ?startDate and b.gudang_id = ?whId And b.is_deleted = 0 and b.rb_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get koreksi lalu
        $sqx = "Insert Into `tmp_prev` (item_id,koreksi)";
        $sqx.= " Select a.item_id,sum(a.corr_qty) From t_ic_stock_correction as a";
        $sqx.= " Where Year(a.corr_date) = ?year And a.corr_date < ?startDate and a.warehouse_id = ?whId and a.corr_status = 1 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get saldo awalcas dari transaksi sebelumnya
        $sqx = "Insert Into `tmp_mutasi` (item_id,awalcas) Select a.item_id,sum((a.awalcas+a.beli+a.xin+a.rjual+a.asyin)-(a.jual+a.xout+a.rbeli+a.asyout)+a.koreksi) From tmp_prev as a Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();

        // get pembelian
        $sqx = "Insert Into `tmp_mutasi` (item_id,beli) Select a.item_id,sum(a.purchase_qty) From t_ap_purchase_detail as a";
        $sqx.= " Join t_ap_purchase_master as b On a.grn_id = b.id";
        $sqx.= " Where Year(b.grn_date) = ?year And b.grn_date BETWEEN ?startDate and ?endDate and b.gudang_id = ?whId and b.grn_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer masuk
        $sqx = "Insert Into `tmp_mutasi` (item_id,xin) Select a.item_id,sum(a.qty) From t_ic_transfer_detail as a";
        $sqx.= " Join t_ic_transfer_master as b On a.npb_id = b.id";
        $sqx.= " Where Year(b.npb_date) = ?year And b.npb_date BETWEEN ?startDate and ?endDate and b.to_wh_id = ?whId and b.npb_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get return ex penjualan
        $sqx = "Insert Into `tmp_mutasi` (item_id,rjual) Select a.item_id,sum(a.qty_retur) From t_ar_return_detail as a";
        $sqx.= " Join t_ar_return_master as b On a.rj_id = b.id";
        $sqx.= " Where Year(b.rj_date) = ?year And b.rj_date BETWEEN ?startDate and ?endDate and b.gudang_id = ?whId And b.is_deleted = 0 And b.rj_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get penjualan
        $sqx = "Insert Into `tmp_mutasi` (item_id,jual) Select a.item_id,sum(a.sales_qty) From t_ar_invoice_detail as a";
        $sqx.= " Join t_ar_invoice_master as b On a.invoice_id = b.id";
        $sqx.= " Where Year(b.invoice_date) = ?year And b.invoice_date BETWEEN ?startDate and ?endDate and b.gudang_id = ?whId And b.is_deleted = 0 And b.invoice_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer keluar
        $sqx = "Insert Into `tmp_mutasi` (item_id,xout) Select a.item_id,sum(a.qty) From t_ic_transfer_detail as a";
        $sqx.= " Join t_ic_transfer_master as b On a.npb_id = b.id";
        $sqx.= " Where Year(b.npb_date) = ?year And b.npb_date BETWEEN ?startDate and ?endDate and b.fr_wh_id = ?whId and b.npb_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get return ex pembelian
        $sqx = "Insert Into `tmp_mutasi` (item_id,rbeli) Select a.item_id,sum(a.qty_retur) From t_ap_return_detail as a";
        $sqx.= " Join t_ap_return_master as b On a.rb_id = b.id";
        $sqx.= " Where Year(b.rb_date) = ?year And b.rb_date BETWEEN ?startDate and ?endDate and b.gudang_id = ?whId And b.is_deleted = 0 And b.rb_status <> 3 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // get koreksi
        $sqx = "Insert Into `tmp_mutasi` (item_id,koreksi)";
        $sqx.= " Select a.item_id,sum(a.corr_qty) From t_ic_stock_correction as a";
        $sqx.= " Where Year(a.corr_date) = ?year And a.corr_date BETWEEN ?startDate and ?endDate and a.warehouse_id = ?whId and a.corr_status = 1 Group By a.item_id";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?startDate", date('Y-m-d', $startDate));
        $this->connector->AddParameter("?endDate", date('Y-m-d', $endDate));
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?whId", $whId);
        $rs = $this->connector->ExecuteNonQuery();

        // try get all tmp card data
        $sqx = "Select c.entity_id,a.item_id, b.item_code, b.item_name, b.s_uom_code as satuan, b.s_uom_qty, b.c_uom_code, b.qty_convert, sum(a.awalcas) as sAwal, sum(a.beli) as sBeli, sum(a.asyin) as sAsyin, sum(a.xin) as sXin, sum(a.rjual) as sRjual, sum(a.asyout) as sAsyout, sum(a.jual) as sJual, sum(a.xout) as sXout, sum(a.rbeli) as sRbeli, sum(a.koreksi) as sKoreksi ";
        $sqx.= " From tmp_mutasi as a Join m_items as b On a.item_id = b.id Join m_item_brand c ON b.brand_id = c.id";
        if ($entityId > 0){
            $sqx.= " Where c.entity_id = ".$entityId;
        }
        $sqx.= " Group By c.entity_id,a.item_id, b.item_code, b.item_name, b.s_uom_code, b.s_uom_qty Order By c.entity_id,b.item_code, b.item_name";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function Load4Reports($trxYear, $whId = 0){
        if ($whId > 0) {
            $sql = "Select a.* From vw_cas_item_stock as a Where a.trx_year = $trxYear And a.warehouse_id = $whId Order By a.item_code,a.item_name";
        }else{
            $sql = "Select a.* From vw_cas_item_stock_all_whs as a Where a.trx_year = $trxYear Order By a.item_code,a.item_name";
        }
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}
