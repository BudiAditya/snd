<?php
class IssueController extends AppController {
    private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userAccYear;
    private $userAccMonth;

    protected function Initialize() {
        require_once(MODEL . "inventory/issue.php");
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
        $settings["columns"][] = array("name" => "a.wh_code", "display" => "Gudang", "width" => 70);
        $settings["columns"][] = array("name" => "a.issue_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.issue_no", "display" => "No.Issue", "width" => 100);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 70);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 200);
        $settings["columns"][] = array("name" => "a.satuan", "display" => "Satuan", "width" => 40);
        $settings["columns"][] = array("name" => "format(a.qty,0)", "display" => "QTY", "width" => 40, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.price,0)", "display" => "Harga", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.qty * a.price,0)", "display" => "Biaya", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.keterangan", "display" => "Keterangan", "width" => 350);
        $settings["columns"][] = array("name" => "if(a.is_status = 0,'Draft','Posted')", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "a.item_code", "display" => "Kode Barang");
        $settings["filters"][] = array("name" => "a.wh_code", "display" => "Gudang");
        $settings["filters"][] = array("name" => "a.issue_date", "display" => "Tanggal");

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Pemakaian Barang";

            if ($acl->CheckUserAccess("inventory.issue", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "inventory.issue/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.issue", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.issue/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
                    "Error" => "Mohon memilih issue terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu issue.",
                    "Confirm" => "Apakah anda mau menghapus data issue yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }

            $settings["def_order"] = 2;
            $settings["def_filter"] = 0;
            $settings["singleSelect"] = true;

        } else {
            $settings["from"] = "vw_ic_issue AS a";
            $settings["where"] = "Year(a.issue_date) = ".$this->userAccYear;
            $settings["order_by"] = "a.issue_date,a.wh_code, a.item_code";
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    private function ValidateData(Issue $issue) {
        if ($issue->ItemId == 0 || $issue->ItemId == null || $issue->ItemId == ''){
            $this->Set("error", "Item Barang belum dipilih!");
            return false;
        }
        if ($issue->Qty == 0 || $issue->Qty == null || $issue->Qty == ''){
            $this->Set("error", "Data tidak valid QTY = 0!");
            return false;
        }
        if ($issue->DebetAccId == 0 || $issue->DebetAccId == null || $issue->DebetAccId == ''){
            $this->Set("error", "Akun Beban belum dipilih!");
            return false;
        }
        if ($issue->Keterangan == null || $issue->Keterangan == ''){
            $this->Set("error", "Keterangan belum diisi!");
            return false;
        }
        return true;
    }

    public function add() {
        require_once (MODEL . "master/warehouse.php");
        require_once (MODEL . "master/coadetail.php");
        require_once(MODEL . "inventory/stock.php");
        $issue = new Issue();
        $log = new UserAdmin();
        $errno = 0;
        if (count($this->postData) > 0) {
            $issue->WarehouseId = $this->GetPostValue("WarehouseId");
            $issue->IssueDate = strtotime($this->GetPostValue("IssueDate"));
            $issue->ItemId = $this->GetPostValue("ItemId");
            $issue->DebetAccId = $this->GetPostValue("DebetAccId");
            $issue->Qty = $this->GetPostValue("Qty");
            $issue->ItemCode = $this->GetPostValue("ItemCode");
            $issue->ItemUom = $this->GetPostValue("ItemUom");
            $issue->Keterangan = $this->GetPostValue("Keterangan");
            $issue->IsStatus = 0;
            $issue->Price = 0;
            if ($this->ValidateData($issue)) {
                $issue->CreatebyId = $this->userUid;
                $issue->IssueNo = $issue->GetIssueDocNo($this->userCabangId);
                $flagSuccess = false;
                $this->connector->BeginTransaction();
                if ($issue->Insert() > 0) {
                    $stock = new Stock();
                    $stocks = $stock->LoadStocksFifo($this->userAccYear, $issue->ItemId, $issue->ItemUom, $issue->WarehouseId);
                    // Set variable-variable pendukung
                    $remainingQty = $issue->Qty;
                    $issue->Price = 0;
                    $hpp = 0;
                    /** @var $stocks Stock[] */
                    foreach ($stocks as $stock) {
                        // Buat object stock keluarnya
                        $istock = new Stock();
                        $istock->TrxYear = $this->userAccYear;
                        $istock->CreatedById = $this->userUid;
                        $istock->StockTypeCode = 105;                // Item Issue dari IS
                        $istock->ReffId = $issue->Id;
                        $istock->TrxDate = $issue->IssueDate;
                        $istock->WarehouseId = $issue->WarehouseId;    // Gudang asal!
                        $istock->ItemId = $issue->ItemId;
                        //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                        $istock->UomCode = $issue->ItemUom;
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
                            $errors[] = sprintf("%s -> Item Code:  %s Message: Stock tidak cukup!", $issue->IssueNo, $issue->ItemCode);
                            break;        // Break loop stocks
                        }
                        // update hpp detail
                        if ($hpp > 0) {
                            $flagSuccess = true;
                            $issue->Price = round($hpp / $issue->Qty, 2);
                            $issue->IsStatus = 1;
                            if ($issue->UpdatePrice() > 0) {
                                $flagSuccess = true;
                            } else {
                                $errno = 2;
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $issue->IssueNo, $issue->ItemCode, $this->connector->GetErrorMessage());
                                break;
                            }
                        } else {
                            $lhpp = new Stock();
                            $lhpp = $lhpp->GetLastHpp($this->userAccYear, $issue->WarehouseId, $issue->ItemId);
                            if ($lhpp > 0) {
                                $flagSuccess = true;
                                $issue->Price = $lhpp;
                                $issue->IsStatus = 1;
                                if ($issue->UpdatePrice() > 0) {
                                    $flagSuccess = true;
                                } else {
                                    $errno = 3;
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $issue->IssueNo, $issue->ItemCode, $this->connector->GetErrorMessage());
                                    break;
                                }
                            } else {
                                $flagSuccess = true;
                                $dhpp = new Stock();
                                $dhpp = $dhpp->GetDefaultHpp($issue->ItemId);
                                if ($dhpp > 0) {
                                    $issue->Price = round($dhpp / $issue->Qty, 2);
                                    $issue->IsStatus = 1;
                                    if ($issue->UpdatePrice() > 0) {
                                        $flagSuccess = true;
                                    } else {
                                        $errno = 4;
                                        $flagSuccess = false;
                                        $errors[] = sprintf("%s -> Item: %s Message: Gagal update hpp item invoice ! Message: %s", $issue->IssueNo, $issue->ItemCode, $this->connector->GetErrorMessage());
                                        break;
                                    }
                                } else {
                                    $errno = 5;
                                    $flagSuccess = false;
                                    $errors[] = sprintf("%s -> Item: %s Message: HPP Tidak dihitung (Kosong) ! Message: %s", $issue->IssueNo, $issue->ItemCode, $this->connector->GetErrorMessage());
                                    break;
                                }
                            }
                        }
                        // Update Qty Balance
                        if ($stock->Update($stock->Id) > 0) {
                            $flagSuccess = true;
                        } else {
                            $errno = 6;
                            $flagSuccess = false;
                            $errors[] = sprintf("%s -> Item: %s Message: Gagal update data stock ! Message: %s", $issue->IssueNo, $issue->ItemCode, $this->connector->GetErrorMessage());
                            break;        // Break loop stocks
                        }
                        // OK jangan lupa update data cost
                        if ($remainingQty <= 0) {
                            $flagSuccess = true;
                            // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                            break;
                        }
                    }
                }else{
                    $errno = -1;
                }
                if ($flagSuccess) {
                    $this->connector->CommitTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Add New Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->DebetAccId,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Stock Issue: %s (%s) sudah berhasil disimpan", $issue->ItemId, $issue->DebetAccId));
                    redirect_url("inventory.issue");
                } else {
                    $this->connector->RollbackTransaction();
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Add New Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->DebetAccId,'-','Failed');
                    $this->Set("error", "[$errno] Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        //load warehouse
        $loader = new Warehouse();
        $whs  = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("whs", $whs);
        //load cost account
        $loader = new CoaDetail();
        $coas   = $loader->LoadCostAccount($this->userCompanyId);
        $this->Set("coas", $coas);
        $this->Set("issue", $issue);
    }

    public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.issue");
        }
        $log = new UserAdmin();
        $issue = new Issue();
        $issue = $issue->LoadById($id);
        if ($issue == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.issue");
        }
        $flagSuccess = true;
        $this->connector->BeginTransaction();
        if ($issue->Delete($id) == 1) {
            require_once(MODEL . "inventory/stock.php");
            $stock = new  Stock();
            $stock = $stock->FindByTypeReffId($this->userAccYear, 105, $id);
            if ($stock == null) {
                $flagSuccess = false;
            } else {
                /** @var $stock Stock[] */
                foreach ($stock as $dstock) {
                    $cstock = new Stock($dstock->UseStockId);
                    if ($cstock == null) {
                        $flagSuccess = false;
                    } else {
                        $cstock->QtyBalance += $dstock->Qty;
                        $rs = $cstock->Update($dstock->UseStockId);
                        if (!$rs) {
                            $flagSuccess = false;
                        }
                    }
                }
                if ($flagSuccess) {
                    $stock = new Stock();
                    $stock->UpdatedById = $this->userUid;
                    $stock->VoidByTypeReffId($this->userAccYear, 105, $id);
                }
            }
        }
        if($flagSuccess){
            $this->connector->CommitTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Delete Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->DebetAccId,'-','Success');
            $this->persistence->SaveState("info", sprintf("Stock Issue Barang: %s (%s) sudah dihapus", $issue->ItemId, $issue->DebetAccId));
        } else {
            $this->connector->RollbackTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Delete Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->DebetAccId,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus Stock Issue Barang: %s (%s). Error: %s", $issue->ItemId, $issue->DebetAccId, $this->connector->GetErrorMessage()));
        }
        redirect_url("inventory.issue");
    }
}

// End of file: issue_controller.php
