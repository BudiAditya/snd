<?php
class CorrectionController extends AppController {
    private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userAccYear;
    private $userAccMonth;

    protected function Initialize() {
        require_once(MODEL . "inventory/correction.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userAccYear = $this->persistence->LoadState("acc_year");
        $this->userAccMonth = $this->persistence->LoadState("acc_month");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        //$settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.wh_code", "display" => "Gudang", "width" => 80);
        $settings["columns"][] = array("name" => "a.corr_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.corr_no", "display" => "Trx. No.", "width" => 100);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 250);
        $settings["columns"][] = array("name" => "a.satuan", "display" => "Satuan", "width" => 50);
        $settings["columns"][] = array("name" => "a.sys_qty", "display" => "System", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.whs_qty", "display" => "Gudang", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "a.corr_qty", "display" => "Koreksi", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.corr_amt,2)", "display" => "Nilai", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.corr_reason", "display" => "Keterangan", "width" => 300);

        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Cabang");
        $settings["filters"][] = array("name" => "a.wh_code", "display" => "Gudang");

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Stock Opname & Koreksi";

            if ($acl->CheckUserAccess("inventory.correction", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "inventory.correction/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.correction", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.correction/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
                    "Error" => "Mohon memilih correction terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu correction.",
                    "Confirm" => "Apakah anda mau menghapus data correction yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }

            $settings["def_order"] = 2;
            $settings["def_filter"] = 0;
            $settings["singleSelect"] = true;

        } else {
            $settings["from"] = "vw_ic_stock_correction AS a ";
            $settings["where"] = "Year(a.corr_date) = ".$this->userAccYear; //"a.cabang_id = ".$this->userCabangId." And Year(a.corr_date) = ".$this->userAccYear;
            $settings["order_by"] = "a.wh_code, a.item_code";
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    private function ValidateData(Correction $correction) {
        if ($correction->CorrQty == 0 || $correction->CorrQty == null || $correction->CorrQty == ''){
            $this->Set("error", "Koreksi Stock tidak valid (Koreksi = 0)!");
            return false;
        }else {
            return true;
        }
    }

    public function add() {
        require_once (MODEL . "master/warehouse.php");
        require_once(MODEL . "inventory/stock.php");
        $correction = new Correction();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $correction->WarehouseId = $this->GetPostValue("WarehouseId");
            $correction->CorrDate = $this->GetPostValue("CorrDate");
            $correction->ItemId = $this->GetPostValue("ItemId");
            $correction->ItemUom = $this->GetPostValue("ItemUom");
            $correction->ItemCode = $this->GetPostValue("ItemCode");
            $correction->SysQty = $this->GetPostValue("SysQty");
            $correction->WhsQty = $this->GetPostValue("WhsQty");
            $correction->CorrQty = $this->GetPostValue("CorrQty");
            $correction->CorrReason = $this->GetPostValue("CorrReason");
            $correction->CorrStatus = 0;
            $correction->CorrAmt = 0;
            if ($this->ValidateData($correction)) {
                $correction->CreatebyId = $this->userUid;
                $correction->CorrNo = $correction->GetCorrectionDocNo($this->userCabangId);
                $flagSuccess = false;
                $this->connector->BeginTransaction();
                if ($correction->Insert()) {
                    if ($correction->CorrQty < 0){
                        $stock = new Stock();
                        $stocks = $stock->LoadStocksFifo($this->userAccYear, $correction->ItemId, $correction->ItemUom, $correction->WarehouseId);
                        // Set variable-variable pendukung
                        $remainingQty = $correction->CorrQty * -1;
                        $hpp = 0;
                        /** @var $stocks Stock[] */
                        foreach ($stocks as $stock) {
                            // Buat object stock keluarnya
                            $istock = new Stock();
                            $istock->TrxYear = $this->userAccYear;
                            $istock->CreatedById = $this->userUid;
                            $istock->StockTypeCode = 104;                // Item Issue dari IS
                            $istock->ReffId = $correction->Id;
                            $istock->TrxDate = strtotime($correction->CorrDate);
                            $istock->WarehouseId = $correction->WarehouseId;    // Gudang asal!
                            $istock->ItemId = $correction->ItemId;
                            //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                            $istock->UomCode = $correction->ItemUom;
                            $istock->Price = $stock->Price;                // Ya pastilah pake angka ini...
                            $istock->UseStockId = $stock->Id;            // Kasi tau kalau issue ini based on stock id mana
                            $istock->QtyBalance = null;                    // Klo issue harus NULL
                            $istock->UpdatedById = $this->userUid;
                            if ($remainingQty > $stock->QtyBalance) {
                                // Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
                                $istock->Qty = $stock->QtyBalance;            // Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya
                                $remainingQty -= $stock->QtyBalance;        // Kita masih perlu...
                                $stock->QtyBalance = 0;                        // Habis...
                            } else {
                                // Barang di gudang mencukupi atau PAS
                                $istock->Qty = $remainingQty;
                                $stock->QtyBalance -= $remainingQty;
                                $remainingQty = 0;
                            }
                            $hpp += $istock->Qty * $istock->Price;
                            // Apapun yang terjadi masukkan data issue stock
                            if ($istock->Insert() > 0) {
                                $flagSuccess = true;
                            } else {
                                $errno = 1;
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item Code:  %s Message: Stock tidak cukup!", $correction->CorrNo, $correction->ItemCode);
                                break;        // Break loop stocks
                            }
                            // update hpp detail
                            if ($hpp > 0) {
                                $flagSuccess = true;
                                $correction->CorrAmt = round($hpp / $correction->Qty, 2);
                                $correction->CorrStatus = 1;
                                if ($correction->UpdateAmount() > 0) {
                                    $flagSuccess = true;
                                } else {
                                    $errno = 2;
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $correction->CorrNo, $correction->ItemCode, $this->connector->GetErrorMessage());
                                    break;
                                }
                            } else {
                                $lhpp = new Stock();
                                $lhpp = $lhpp->GetLastHpp($this->userAccYear, $correction->WarehouseId, $correction->ItemId);
                                if ($lhpp > 0) {
                                    $flagSuccess = true;
                                    $correction->CorrAmt = $lhpp;
                                    $correction->CorrStatus = 1;
                                    if ($correction->UpdateAmount() > 0) {
                                        $flagSuccess = true;
                                    } else {
                                        $errno = 3;
                                        $flagSuccess = false;
                                        $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $correction->CorrNo, $correction->ItemCode, $this->connector->GetErrorMessage());
                                        break;
                                    }
                                } else {
                                    $flagSuccess = true;
                                    $dhpp = new Stock();
                                    $dhpp = $dhpp->GetDefaultHpp($correction->ItemId);
                                    if ($dhpp > 0) {
                                        $correction->CorrAmt = round($dhpp / $correction->Qty, 2);
                                        $correction->CorrStatus = 1;
                                        if ($correction->UpdateAmount() > 0) {
                                            $flagSuccess = true;
                                        } else {
                                            $errno = 4;
                                            $flagSuccess = false;
                                            $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $correction->CorrNo, $correction->ItemCode, $this->connector->GetErrorMessage());
                                            break;
                                        }
                                    } else {
                                        $correction->CorrAmt = 0;
                                        $correction->CorrStatus = 1;
                                        if ($correction->UpdateAmount() > 0) {
                                            $flagSuccess = true;
                                        } else {
                                            $errno = 5;
                                            $flagSuccess = false;
                                            $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $correction->CorrNo, $correction->ItemCode, $this->connector->GetErrorMessage());
                                            break;
                                        }
                                    }
                                }
                            }
                            // Update Qty Balance
                            if ($stock->Update($stock->Id) > 0) {
                                $flagSuccess = true;
                            } else {
                                $errno = 6;
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: %s Message: Gagal update data stock ! Message: %s", $correction->CorrNo, $correction->ItemCode, $this->connector->GetErrorMessage());
                                break;        // Break loop stocks
                            }
                            // OK jangan lupa update data cost
                            if ($remainingQty <= 0) {
                                $flagSuccess = true;
                                // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                                break;
                            }
                        }
                    }elseif ($correction->CorrQty > 0){
                        $lhpp = new Stock();
                        $lhpp = $lhpp->GetLastHpp($this->userAccYear, $correction->WarehouseId, $correction->ItemId);
                        if ($lhpp > 0) {
                            $flagSuccess = true;
                            $hpp = $lhpp;
                        } else {
                            $flagSuccess = true;
                            $dhpp = new Stock();
                            $dhpp = $dhpp->GetDefaultHpp($correction->ItemId);
                            if ($dhpp > 0) {
                                $hpp = $dhpp;
                            } else {
                                $errno = 5;
                            }
                        }
                        if ($hpp == null){
                            $hpp = 0;
                        }
                        $correction->CorrAmt = $hpp;
                        $correction->UpdateAmount();
                        //create fifo data
                        $sql = "Insert Into t_ic_stock_fifo (trx_year,stock_type_code,reff_id,trx_date,warehouse_id,item_id,qty,uom_code,price,qty_balance)";
                        $sql.= " Values (?trx_year,5,?id,?trx_date,?wh_id,?item_id,?qty,?uom_code,?hpp,?qbl)";
                        $this->connector->CommandText = $sql;
                        $this->connector->AddParameter("?trx_year", $this->userAccYear);
                        $this->connector->AddParameter("?id", $correction->Id);
                        $this->connector->AddParameter("?trx_date", $correction->CorrDate);
                        $this->connector->AddParameter("?wh_id", $correction->WarehouseId);
                        $this->connector->AddParameter("?item_id", $correction->ItemId);
                        $this->connector->AddParameter("?qty", $correction->CorrQty);
                        $this->connector->AddParameter("?uom_code", $correction->ItemUom);
                        $this->connector->AddParameter("?hpp", $correction->CorrAmt);
                        $this->connector->AddParameter("?qbl", $correction->CorrQty);
                        $rs = $this->connector->ExecuteNonQuery();
                    }else{
                        $flagSuccess = false;
                    }
                }
                if ($flagSuccess){
                    $this->connector->CommitTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Add New Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Stock Correction: %s (%s) sudah berhasil disimpan", $correction->ItemId, $correction->SysQty));
                    redirect_url("inventory.correction");
                } else {
                    $this->connector->RollbackTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Add New Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Failed');
                    $this->Set("error", sprintf("[ER-%d] Gagal pada saat menyimpan data.. Message: ",$errno, $this->connector->GetErrorMessage()));
                }
            }
        }
        $loader = new Warehouse();
        $whs  = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("whs", $whs);
        $this->Set("correction", $correction);
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.correction");
        }
        $log = new UserAdmin();
        $correction = new Correction();
        $correction = $correction->LoadById($id);
        if ($correction == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.correction");
        }
        if ($correction->CorrQty > 0){
            $fcode = 5;
        }else{
            $fcode = 104;
        }
        $erno = 0;
        $flagSuccess = true;
        $this->connector->BeginTransaction();
        if ($correction->Delete($id)){
            require_once(MODEL . "inventory/stock.php");
            $stock = new  Stock();
            $stock = $stock->FindByTypeReffId($this->userAccYear, $fcode, $id);
            if ($stock == null) {
                $erno = 2;
                $flagSuccess = false;
            } else {
                /** @var $stock Stock[] */
                if ($fcode == 104) {
                    foreach ($stock as $dstock) {
                        $cstock = new Stock($dstock->UseStockId);
                        if ($cstock == null) {
                            $erno = 3;
                            $flagSuccess = false;
                        } else {
                            $cstock->QtyBalance += $dstock->Qty;
                            $rs = $cstock->Update($dstock->UseStockId);
                            if (!$rs) {
                                $erno = 4;
                                $flagSuccess = false;
                            }
                        }
                    }
                }
                if ($flagSuccess) {
                    $stock = new Stock();
                    $stock->UpdatedById = $this->userUid;
                    $stock->VoidByTypeReffId($this->userAccYear, $fcode, $id);
                }
            }
        }else{
            $erno = 1;
        }
        if ($flagSuccess) {
            $this->connector->CommitTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Delete Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Success');
            $this->persistence->SaveState("info", sprintf("Stock Correction Barang: %s (%s) sudah dihapus", $correction->CorrNo, $correction->ItemCode));
        } else {
            $this->connector->RollbackTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.correction','Delete Item Stock Correction -> Stock Correction: '.$correction->ItemId.' - '.$correction->SysQty,'-','Failed');
            $this->persistence->SaveState("error", sprintf("[ER-%d] Gagal menghapus Stock Correction: %s (%s). Error: %s",$erno, $correction->CorrNo, $correction->ItemCode, $this->connector->GetErrorMessage()));
        }
        redirect_url("inventory.correction");
    }
}

// End of file: correction_controller.php
