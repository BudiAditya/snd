<?php
/**
 * Bertugas mengatur dan meng-query data mengenai stock barang yang ada di gudang.
 * Pada table stock akan berisi keluar masuk barang. Jika Qty bernilai (+) artinya barang masuk. Jika (-) maka barang keluar
 *
 * PENTING: Source dokumen dibedakan bedasarkan stock_type_code !
 * KODE YANG DIGUNAKAN:
 *  - Jika xxx / Dibawah 100 bearti barang masuk
 *  - Jika 1xx / Diatas 100 bearti barang keluar (TIDAK ADA KODE 100)
 *
 * PENTING: Reference Id akan berbeda-beda berdasarkan stock_type_code. Misal jika stock type GN maka reference ID adalah ID dari detail GN ! Jika type IS maka reference ID adalah ID detail IS dan stock ID akan berisi nilai
 *
 * PENTING: UseStockId hanya digunakan untuk barang keluar (tracking stock yang dikeluarkan) dan berisi NULL untuk stock masuk
 *
 * PENTING: QtyBalance hanya digunakan untuk barang masuk yang sudah dikeluarkan. Jika QtyBalance sudah 0 bearti semya barang sudah dikeluarkan !
 */
class Stock extends EntityBase {
	public $Id;
	public $IsDeleted = false;
	public $CreatedById;
	public $CreatedDate;
	public $UpdatedById;
	public $UpdatedDate;

	public $TrxYear = 0;
	public $StockTypeCode;
	public $ReffId = 0;
	public $TrxDate;
	public $WarehouseId = 0;
	public $CabangId = 0;
	public $ItemId = 0;
	public $Qty = 0;
	public $UomCode;
	public $Price = 0;
	public $UseStockId = null;
	public $QtyBalance = null;

	// Variable-variable dokumen referensi
	public $DocumentId = null;
	public $DocumentType = null;
	public $DocumentNo = null;
	public $DocumentDate = null;	// Harusnya untuk yang ini akan sama dengan Stock Date....

    public function __construct($id = null) {
        parent::__construct();
        if (is_numeric($id)) {
            $this->FindById($id);
        }
    }

	// Special query
	const QUERY_STOCK_BY_CABANG = "SELECT a.item_id, SUM(CASE WHEN a.stock_type_code < 100 THEN a.qty ELSE a.qty * -1 END) AS qty_stock, a.uom_code FROM t_ic_stock_fifo AS a JOIN m_warehouse b ON a.warehouse_id = b.id WHERE a.is_deleted = 0 AND a.trx_year = ?tahun And a.trx_date < ?date AND b.cabang_id IN (SELECT id FROM m_cabang WHERE company_id = ?sbu) GROUP BY a.item_id, a.uom_code";

	const QUERY_STOCK_BY_WAREHOUSE = "SELECT a.item_id, SUM(CASE WHEN a.stock_type_code < 100 THEN a.qty ELSE a.qty * -1 END) AS qty_stock, a.uom_code FROM t_ic_stock_fifo AS a WHERE a.is_deleted = 0 AND a.trx_year = ?tahun AND a.trx_date < ?date AND a.warehouse_id = ?warehouseId GROUP BY a.item_id, a.uom_code";

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CreatedById = $row["createby_id"];
		$this->CreatedDate = strtotime($row["create_time"]);
		$this->UpdatedById = $row["updateby_id"];
		$this->UpdatedDate = strtotime($row["update_time"]);

		$this->TrxYear = $row["trx_year"];
		$this->StockTypeCode = $row["stock_type_code"];
		$this->ReffId = $row["reff_id"];
		$this->TrxDate = strtotime($row["trx_date"]);
		$this->WarehouseId = $row["warehouse_id"];
		$this->ItemId = $row["item_id"];
		$this->Qty = $row["qty"];
		$this->UomCode = $row["uom_code"];
		$this->Price = $row["price"];
		$this->UseStockId = $row["use_stock_id"];
		$this->QtyBalance = $row["qty_balance"];
	}

	public function LoadReferencedDocument() {
		if ($this->Id == null || $this->StockTypeCode == null || $this->ReffId == null) {
			throw new Exception("DataNotComplete Exception ! Loading a referenced document must be loading required data first !");
		}

		// Disini kita akan mengambil data dokumen referensi.
		switch ($this->StockTypeCode) {
			case 1:
			    //saldo awal stock
				$this->DocumentType = "OB";
				$this->connector->CommandText = "SELECT a.id, trx_no AS doc_no, a.op_date AS doc_date FROM t_ic_saldoawal AS a WHERE a.id = ?refId";
				break;
			case 2:
			    //pembelian barang
                $this->DocumentType = "GN";
                $this->connector->CommandText = "SELECT a.id, a.grn_no as doc_no, a.grn_date AS doc_date FROM t_ap_purchase_master AS a WHERE a.id = (SELECT grn_id FROM t_ap_purchase_detail WHERE id = ?refId)";
                break;
            case 3:
                //stock transfer - masuk
                $this->DocumentType = "ST";
                $this->connector->CommandText = "SELECT a.id, a.npb_no as doc_no, a.npb_date AS doc_date FROM t_ic_transfer_master AS a WHERE a.id = (SELECT npb_id FROM t_ic_transfer_detail WHERE id = ?refId)";
                break;
            case 4:
                //retur penjualan
                $this->DocumentType = "RJ";
                $this->connector->CommandText = "SELECT a.id, a.rj_no as doc_no, a.rj_date AS doc_date FROM t_ar_return_master AS a WHERE a.id = (SELECT rj_id FROM t_ar_return_detail WHERE id = ?refId)";
                break;
			case 101:
			    //penjualan - invoice
				$this->DocumentType = "IV";
				$this->connector->CommandText = "SELECT a.id, a.invoice_no as doc_no, a.invoice_date AS doc_date FROM t_ar_invoice_master AS a WHERE a.id = (SELECT invoice_id FROM t_ar_invoice_detail WHERE id = ?refId)";
			    break;
            case 102:
                //stock transfer - keluar
                $this->DocumentType = "ST";
                $this->connector->CommandText = "SELECT a.id, a.npb_no as doc_no, a.npb_date AS doc_date FROM t_ic_transfer_master AS a WHERE a.id = (SELECT npb_id FROM t_ic_transfer_detail WHERE id = ?refId)";
                break;
            case 103:
                //retur pembelian
                $this->DocumentType = "RB";
                $this->connector->CommandText = "SELECT a.id, a.rb_no as doc_no, a.rb_date AS doc_date FROM t_ap_return_master AS a WHERE a.id = (SELECT rj_id FROM t_ap_return_detail WHERE id = ?refId)";
                break;
			case 104:
			    //koreksi stock
				$this->DocumentType = "CR";
				$this->connector->CommandText = "SELECT a.id, a.corr_no as doc_no, a.corr_date AS doc_date FROM t_ic_stock_correction AS a WHERE a.id = ?refId";
				break;
            case 105:
                //Pemakaian sendiri (Issue)
                $this->DocumentType = "IS";
                $this->connector->CommandText = "SELECT a.id, a.issue_no as doc_no, a.issue_date AS doc_date FROM t_ic_issue AS a WHERE a.id = ?refId";
                break;
			default:
				throw new Exception("NotImplemented Exception ! StockTypeCode: " . $this->StockTypeCode . " is not yet implemented for acquiring referenced document ! Please contact system admin !");
		}

		$this->connector->AddParameter("?refId", $this->ReffId);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null) {
			throw new Exception("Failed to retrieve referenced document data ! Stock Id: " . $this->Id);
		}
		$row = $rs->FetchAssoc();
		$this->DocumentId = $row["id"];
		$this->DocumentNo = $row["doc_no"];
		$this->DocumentDate = strtotime($row["doc_date"]);
	}

	public function GenerateReferenceLink() {
		if ($this->DocumentId == null) {
			throw new Exception("DataNotComplete Exception ! Generating Link require DocumentId to be loaded first !");
		}

		switch ($this->StockTypeCode) {
			case 1:
				return "inventory.correction/view/" . $this->DocumentId;
			case 2:
                return "ap.purchase/view/" . $this->DocumentId;
            case 3:
                return "inventory.transfer/view/" . $this->DocumentId;
            case 4:
                return "ar.return/view/" . $this->DocumentId;
			case 101:
				return "ar.invoice/view/" . $this->DocumentId;
			case 102:
                return "inventory.transfer/view/" . $this->DocumentId;
            case 103:
                return "ap.return/view/" . $this->DocumentId;
			case 104:
				return "inventory.correction/view/" . $this->DocumentId;
            case 105:
                return "inventory.issue/view/" . $this->DocumentId;
			default:
				throw new Exception("NotImplemented Exception ! StockTypeCode: " . $this->StockTypeCode . " is not yet implemented for generate reference link ! Please contact system admin !");
		}
	}

	public function FormatTrxDate($format = HUMAN_DATE) {
		return is_int($this->TrxDate) ? date($format, $this->TrxDate) : null;
	}

	public function FormatDocumentDate($format = HUMAN_DATE) {
		return is_int($this->DocumentDate) ? date($format, $this->DocumentDate) : null;
	}

	/**
	 * Berfungsi untuk mencari stock barang secara FIFO berdasarkan gudang yang dipilih.
	 * Jika filter gudang bernilai null artinya semua gudang berdasarkan SBU
	 *
	 * @param $itemId			=> ID barang yang akan dicari... (INGAT Barang sudah specific per SBU)
	 * @param $uomCode			=> Satuan Barang
	 * @param null $warehouseId	=> Jika perlu filter gudang
	 * @return array
	 */
	public function LoadStocksFifo($tahun, $itemId, $uomCode = null, $warehouseId = 0) {
		if ($uomCode == null) {
            $query = "SELECT a.* FROM t_ic_stock_fifo AS a WHERE a.is_deleted = 0 AND a.trx_year = ?tahun AND a.warehouse_id = ?warehouseId AND a.item_id = ?itemId AND a.qty_balance > 0 ORDER BY a.trx_date,a.id ASC";
		} else {
            $query = "SELECT a.* FROM t_ic_stock_fifo AS a WHERE a.is_deleted = 0 AND a.trx_year = ?tahun AND a.warehouse_id = ?warehouseId AND a.item_id = ?itemId AND a.uom_code = ?uomCd AND a.qty_balance > 0 ORDER BY a.trx_date,a.id ASC";
		}
		$this->connector->CommandText = $query;
		$this->connector->AddParameter("?tahun", $tahun);
        $this->connector->AddParameter("?warehouseId", $warehouseId);
        $this->connector->AddParameter("?itemId", $itemId);
		$this->connector->AddParameter("?uomCd", $uomCode);

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Stock();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function FindById($id) {
        $query = "SELECT a.* FROM t_ic_stock_fifo AS a WHERE a.is_deleted = 0 AND a.id = ?id";
        $this->connector->CommandText = $query;
        $this->connector->AddParameter("?id", $id);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $this->FillProperties($rs->FetchAssoc());
        return $this;
    }

	/**
	 * Berfungsi untuk mengload history barang yang diminta.
	 * Jika filter gudang bernilai null artinya semua gudang berdasarkan SBU
	 *
	 * @param $itemId				=> ID barang yang akan dicari... (INGAT Barang sudah specific per SBU)
	 * @param $uomCode				=> Satuan Barang
	 * @param $startDate
	 * @param $endDate
	 * @param null $warehouseId		=> Jika perlu filter gudang
	 * @return array
	 */
	public function LoadHistoriesBetween($tahun, $itemId, $startDate, $endDate, $warehouseId = 0) {
		if ($warehouseId == null) {
            $query = "SELECT a.* FROM t_ic_stock_fifo AS a WHERE a.is_deleted = 0 AND a.trx_year = ?tahun AND a.warehouse_id = ?warehouseId AND a.item_id = ?itemId AND a.trx_date BETWEEN ?start AND ?end ORDER BY a.trx_date ASC";
            $this->connector->AddParameter("?warehouseId", $warehouseId);
        } else {
            $query = "SELECT a.* FROM t_ic_stock_fifo AS a WHERE a.is_deleted = 0 AND a.trx_year = ?tahun AND a.item_id = ?itemId AND a.trx_date BETWEEN ?start AND ?end ORDER BY a.trx_date ASC";
		}

		$this->connector->CommandText = $query;
        $this->connector->AddParameter("?tahun", $tahun);
		$this->connector->AddParameter("?itemId", $itemId);
		$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
		$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));

		$result = array();
		$rs = $this->connector->ExecuteQuery();
		if ($rs) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Stock();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}

		return $result;
	}

	public function Insert() {
		$this->connector->CommandText = "INSERT INTO t_ic_stock_fifo(trx_year,warehouse_id,stock_type_code, reff_id, trx_date, item_id, qty, uom_code, price, use_stock_id, qty_balance, createby_id, create_time) VALUES(?trx_year,?warehouse_id, ?stock_type, ?ref, ?trx_date, ?item, ?qty, ?uom, ?price, ?useStockId, ?balance, ?user, NOW())";
		$this->connector->AddParameter("?trx_year", $this->TrxYear);
        $this->connector->AddParameter("?stock_type", $this->StockTypeCode);
		$this->connector->AddParameter("?ref", $this->ReffId);
		$this->connector->AddParameter("?trx_date", date('Y-m-d', $this->TrxDate));
		$this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
		$this->connector->AddParameter("?item", $this->ItemId);
		$this->connector->AddParameter("?qty", $this->Qty);
		$this->connector->AddParameter("?uom", $this->UomCode);
		$this->connector->AddParameter("?price", $this->Price);
		$this->connector->AddParameter("?useStockId", $this->UseStockId);
		$this->connector->AddParameter("?balance", $this->QtyBalance);
		$this->connector->AddParameter("?user", $this->CreatedById);
		$rs = $this->connector->ExecuteNonQuery();
		if ($rs == 1) {
			$this->connector->CommandText = "SELECT LAST_INSERT_ID();";
			$this->Id = (int)$this->connector->ExecuteScalar();
		}

		return $rs;
	}

	public function Update($id) {
		$this->connector->CommandText =
"UPDATE t_ic_stock_fifo SET
    trx_year = ?trx_year
	,stock_type_code = ?stock_type
	, reff_id = ?ref
	, trx_date = ?trx_date
	, warehouse_id = ?warehouse_id
	, item_id = ?item
	, qty = ?qty
	, uom_code = ?uom
	, price = ?price
	, use_stock_id = ?useStockId
	, qty_balance = ?balance
	, updateby_id = ?user
	, update_time = NOW()
	, warehouse_id = ?warehouse_id
WHERE id = ?id";
        $this->connector->AddParameter("?trx_year", $this->TrxYear);
        $this->connector->AddParameter("?stock_type", $this->StockTypeCode);
        $this->connector->AddParameter("?ref", $this->ReffId);
        $this->connector->AddParameter("?trx_date", date('Y-m-d', $this->TrxDate));
        $this->connector->AddParameter("?warehouse_id", $this->WarehouseId);
        $this->connector->AddParameter("?item", $this->ItemId);
        $this->connector->AddParameter("?qty", $this->Qty);
        $this->connector->AddParameter("?uom", $this->UomCode);
        $this->connector->AddParameter("?price", $this->Price);
        $this->connector->AddParameter("?useStockId", $this->UseStockId);
        $this->connector->AddParameter("?balance", $this->QtyBalance);
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);

		return $this->connector->ExecuteNonQuery();
	}

	public function Void($id) {
		$this->connector->CommandText = "UPDATE t_ic_stock_fifo SET is_deleted = 1, updateby_id = ?user, update_time = NOW() WHERE id = ?id";
		$this->connector->AddParameter("?user", $this->UpdatedById);
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

    public function VoidByTypeReffId($tahun,$typeId,$reffId) {
        $this->connector->CommandText = "UPDATE t_ic_stock_fifo a SET a.is_deleted = 1, a.updateby_id = ?user, a.update_time = NOW() WHERE a.trx_year = $tahun And a.stock_type_code = ?typeId And a.reff_id = ?reffId";
        $this->connector->AddParameter("?user", $this->UpdatedById);
        $this->connector->AddParameter("?typeId", $typeId);
        $this->connector->AddParameter("?reffId", $reffId);
        return $this->connector->ExecuteNonQuery();
    }

    public function Delete($id) {
        $this->connector->CommandText = "Delete From t_ic_stock_fifo WHERE id = ?id";
        $this->connector->AddParameter("?id", $id);
        return $this->connector->ExecuteNonQuery();
    }

    public function DeleteByTypeReffId($tahun,$typeId,$reffId) {
        $this->connector->CommandText = "Delete a From t_ic_stock_fifo a WHERE a.trx_year = $tahun And a.stock_type_code = ?typeId And a.reff_id = ?reffId";
        $this->connector->AddParameter("?typeId", $typeId);
        $this->connector->AddParameter("?reffId", $reffId);
        return $this->connector->ExecuteNonQuery();
    }

    public function CheckStock($tahun, $whId = 0,$itemId) {
        $sqx = null;
        $sqty = 0;
        $sqx = "SELECT coalesce(sum(a.qty_stock),0) as qtystock FROM vw_ic_stock_center AS a WHERE a.trx_year = ?tahun And a.item_id = ?itemId";
        if ($whId > 0){
            $sqx.= " And a.warehouse_id = ?whId";
        }
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?tahun",$tahun);
        $this->connector->AddParameter("?whId",$whId);
        $this->connector->AddParameter("?itemId", $itemId);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return $sqty;
        }
        $row = $rs->FetchAssoc();
        $sqty = $row["qtystock"];
        return $sqty;
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
                `awal`  decimal(11,2) NOT NULL DEFAULT 0,
                `masuk`  decimal(11,2) NOT NULL DEFAULT 0,
                `keluar`  decimal(11,2) NOT NULL DEFAULT 0,
                `issue`  decimal(11,2) NOT NULL DEFAULT 0,
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
                `awal`  decimal(11,2) NOT NULL DEFAULT 0,
                `masuk`  decimal(11,2) NOT NULL DEFAULT 0,
                `keluar`  decimal(11,2) NOT NULL DEFAULT 0,
                `issue`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0,
                `saldo`  decimal(11,2) NOT NULL DEFAULT 0,
                `notes` varchar(250))';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // get saldo awal
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,awal,relasi,price) Select a.op_date,'Saldo Awal','inventory.awal',a.op_qty,'-',0 From t_ic_saldoawal as a";
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
        $sqx.= " Select a.npb_date,concat('Pengiriman - ',a.npb_no),concat('inventory.transfer/view/',a.id),0,b.qty,concat('Dari Gudang - ',a.fr_wh_code)";
        $sqx.= " From vw_ic_transfer_master a Join t_ic_transfer_detail b On a.id = b.npb_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.npb_date) = ?year and a.to_wh_id = ?gudang_id and a.is_deleted = 0 and a.npb_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

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

        // get penjualan
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,keluar,relasi,notes)";
        $sqx.= " Select a.invoice_date,concat('Penjualan - ',a.invoice_no),concat('ar.invoice/view/',a.id),b.price,b.sales_qty,concat(a.customer_name,' (',a.customer_code,')'),b.item_descs";
        $sqx.= " From vw_ar_invoice_master as a Join t_ar_invoice_detail as b On a.id = b.invoice_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.invoice_date) = ?year and a.gudang_id = ?gudang_id and a.is_deleted = 0 and a.invoice_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

        // get transfer keluar
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,price,keluar,relasi)";
        $sqx.= " Select a.npb_date,concat('Pengiriman - ',a.npb_no),concat('inventory.transfer/view/',a.id),0,b.qty,concat('Ke Gudang - ',a.to_wh_code)";
        $sqx.= " From vw_ic_transfer_master a Join t_ic_transfer_detail b On a.id = b.npb_id";
        $sqx.= " Where b.item_id = ?item_id and Year(a.npb_date) = ?year and a.fr_wh_id = ?gudang_id and a.is_deleted = 0 and a.npb_status <> 3";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?item_id", $this->ItemId);
        $this->connector->AddParameter("?year", $trxYear);
        $this->connector->AddParameter("?gudang_id", $this->WarehouseId);
        $rs = $this->connector->ExecuteNonQuery();

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

        // get issue
        $sqx = "Insert Into `tmp_card` (trx_date,trx_type,trx_url,issue,relasi)";
        $sqx.= " Select a.issue_date,concat('Issue - ',a.corr_no),'inventory.issue',a.qty,a.keterangan";
        $sqx.= " From t_ic_issue as a";
        $sqx.= " Where a.item_id = ?item_id and Year(a.issue_date) = ?year and a.warehouse_id = ?gudang_id and a.is_status = 1";
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

        //filter data
        $sqx = "Insert Into tmp_card1 (seq_no,trx_date,trx_type,saldo)";
        $sqx.= " Select 0,'".date('Y-m-d',$startDate)."','Saldo lalu...',coalesce(sum((a.awal+a.masuk+a.koreksi)-a.keluar),0) From tmp_card a Where a.trx_date < '".date('Y-m-d',$startDate)."'";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        $sqx = "Insert Into tmp_card1 (seq_no,trx_date,trx_type,trx_url,relasi,price,awal,masuk,keluar,issue,koreksi,saldo,notes)";
        $sqx.= " Select 1,trx_date,trx_type,trx_url,relasi,price,awal,masuk,keluar,issue,koreksi,saldo,notes From tmp_card a Where a.trx_date >= '".date('Y-m-d',$startDate)."' And a.trx_date <= '".date('Y-m-d',$endDate)."'";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // try get all tmp card data
        $sqx = "Select * From tmp_card1 Order By trx_date,seq_no";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetJSonItemStock($tahun,$whId = 0,$filter,$sort = 'a.item_name',$order = 'ASC') {
        $sql = "SELECT a.* From vw_ic_stock_list AS a Where a.trx_year = $tahun And a.warehouse_id = $whId And a.qty_stock > 0";
        if ($filter != null){
            $sql.= " And (a.item_code Like '%$filter%' Or a.item_name Like '%$filter%')";
        }
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By $sort $order";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetMutasiStock($trxYear, $whId = 0, $startDate = null, $endDate = null, $entityId = 0){
        $sqx = null;
        // create previous mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_prev` (
                `item_id`  int(5) NOT NULL DEFAULT 0,
                `awal`  decimal(11,2) NOT NULL DEFAULT 0,
                `beli`  decimal(11,2) NOT NULL DEFAULT 0,
                `xin`  decimal(11,2) NOT NULL DEFAULT 0,
                `rjual`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyin`  decimal(11,2) NOT NULL DEFAULT 0,
                `jual`  decimal(11,2) NOT NULL DEFAULT 0,
                `xout`  decimal(11,2) NOT NULL DEFAULT 0,
                `rbeli`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyout`  decimal(11,2) NOT NULL DEFAULT 0,
                `issue`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // create request mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_mutasi` (
                `item_id`  int(5) NOT NULL DEFAULT 0,
                `awal`  decimal(11,2) NOT NULL DEFAULT 0,
                `beli`  decimal(11,2) NOT NULL DEFAULT 0,
                `xin`  decimal(11,2) NOT NULL DEFAULT 0,
                `rjual`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyin`  decimal(11,2) NOT NULL DEFAULT 0,
                `jual`  decimal(11,2) NOT NULL DEFAULT 0,
                `xout`  decimal(11,2) NOT NULL DEFAULT 0,
                `rbeli`  decimal(11,2) NOT NULL DEFAULT 0,
                `asyout`  decimal(11,2) NOT NULL DEFAULT 0,
                `issue`  decimal(11,2) NOT NULL DEFAULT 0,
                `koreksi`  decimal(11,2) NOT NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // get saldo awal
        $sqx = "Insert Into `tmp_prev` (item_id,awal) Select a.item_id,sum(a.op_qty) From t_ic_saldoawal as a";
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

        // get issue lalu
        $sqx = "Insert Into `tmp_prev` (item_id,issue)";
        $sqx.= " Select a.item_id,sum(a.qty) From t_ic_issue as a";
        $sqx.= " Where Year(a.issue_date) = ?year And a.issue_date < ?startDate and a.warehouse_id = ?whId and a.is_status = 1 Group By a.item_id";
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

        // get saldo awal dari transaksi sebelumnya
        $sqx = "Insert Into `tmp_mutasi` (item_id,awal) Select a.item_id,sum((a.awal+a.beli+a.xin+a.rjual+a.asyin)-(a.jual+a.xout+a.rbeli+a.asyout)+a.koreksi) From tmp_prev as a Group By a.item_id";
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

        // get issue
        $sqx = "Insert Into `tmp_mutasi` (item_id,issue)";
        $sqx.= " Select a.item_id,sum(a.qty) From t_ic_issue as a";
        $sqx.= " Where Year(a.issue_date) = ?year And a.issue_date BETWEEN ?startDate and ?endDate and a.warehouse_id = ?whId and a.is_status = 1 Group By a.item_id";
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
        $sqx = "Select c.entity_id,a.item_id, b.item_code, b.item_name, b.s_uom_code as satuan, b.s_uom_qty, b.c_uom_code, b.qty_convert, sum(a.awal) as sAwal, sum(a.beli) as sBeli, sum(a.asyin) as sAsyin, sum(a.xin) as sXin, sum(a.rjual) as sRjual, sum(a.asyout) as sAsyout, sum(a.jual) as sJual, sum(a.xout) as sXout, sum(a.rbeli) as sRbeli, sum(a.koreksi) as sKoreksi ";
        $sqx.= " From tmp_mutasi as a Join m_items as b On a.item_id = b.id Join m_item_brand c ON b.brand_id = c.id";
        if ($entityId > 0){
            $sqx.= " Where c.entity_id = ".$entityId;
        }
        $sqx.= " Group By c.entity_id,a.item_id, b.item_code, b.item_name, b.s_uom_code, b.s_uom_qty Order By c.entity_id,b.item_code, b.item_name";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function FindByTypeReffId($tahun,$typeId,$reffId) {
        $this->connector->CommandText = "Select a.* From t_ic_stock_fifo a WHERE a.is_deleted = 0 And a.trx_year = ?tahun And a.stock_type_code = ?typeId And a.reff_id = ?reffId";
        $this->connector->AddParameter("?tahun", $tahun);
        $this->connector->AddParameter("?typeId", $typeId);
        $this->connector->AddParameter("?reffId", $reffId);
        $result = array();
        $rs = $this->connector->ExecuteQuery();
        if ($rs) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Stock();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function Load4Reports($cabangId = 0, $whId = 0, $entityId = 0){
        $sql = "Select a.*,0 as hrg_beli,0 as hrg_jual,sum(a.qty_stock) as qty_stock,coalesce(sum(b.po_qty),0) as po_qty, coalesce(sum(c.so_qty),0) as so_qty";
        $sql.= " From vw_ic_stock_list as a Left Join vw_ap_po_outstanding_qty as b On a.item_id = b.item_id";
        $sql.= " Left Join vw_ar_so_outstanding_qty as c On a.item_id = c.item_id";
        $sql.= " Where a.item_id > 0";
        if ($cabangId > 0){
            $sql.= " And a.cabang_id = ".$cabangId;
        }
        if ($whId > 0){
            $sql.= " And a.warehouse_id = ".$whId;
        }
        if ($entityId != "-"){
            $sql.= " And a.entity_id = '".$entityId."'";
        }
        $sql.= " Group By a.wh_code,a.item_code,a.item_name,a.l_uom_code";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetItemStocks($tahun,$gudangId,$filter) {
        $sql = "SELECT a.item_id,a.item_code,a.item_name,a.s_uom_code,a.l_uom_code,a.qty_stock,coalesce(b.zone_1,0) as hrg_jual,a.s_uom_qty,b.uom_code as p_uom_code";
        $sql.= " From vw_ic_stock_list AS a Left Join m_item_prices b ON a.item_id = b.item_id";
        $sql.= " Where a.trx_year = $tahun And a.warehouse_id = $gudangId And a.qty_stock > 0";
        if ($filter != null){
            $sql.= " And (a.item_name Like '%$filter%')";
        }
        $this->connector->CommandText = $sql;
        $data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
        $sql.= " Order By a.item_name Asc";
        $this->connector->CommandText = $sql;
        $rows = array();
        $rs = $this->connector->ExecuteQuery();
        while ($row = $rs->FetchAssoc()){
            $rows[] = $row;
        }
        $result = array('total'=>$data['count'],'rows'=>$rows);
        return $result;
    }

    public function GetLastHpp($tahun, $whId = 0,$itemId) {
        $lhpp = 0;
        $this->connector->CommandText = "Select coalesce(max(a.price),0) as lhpp From t_ic_stock_fifo a Where a.trx_year = $tahun and a.item_id = $itemId and a.warehouse_id = $whId";
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return $lhpp;
        }
        $row = $rs->FetchAssoc();
        $lhpp = $row["lhpp"];
        return $lhpp;
    }

    public function GetDefaultHpp($itemId) {
        $dhpp = 0;
        $this->connector->CommandText = "Select coalesce(a.hpp,0) as dhpp From m_item_prices a Where a.item_id = $itemId";
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return $dhpp;
        }
        $row = $rs->FetchAssoc();
        $dhpp = $row["dhpp"];
        return $dhpp;
    }
}


// End of File: stock.php
