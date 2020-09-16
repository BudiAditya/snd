<?php
class TransferController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "inventory/transfer.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        //$settings["columns"][] = array("name" => "a.cabang_code", "display" => "Dari Cabang", "width" => 100);
        $settings["columns"][] = array("name" => "a.npb_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.npb_no", "display" => "No. NPB", "width" => 100);
        $settings["columns"][] = array("name" => "a.fr_wh_code", "display" => "Dari Gudang", "width" => 100);
        $settings["columns"][] = array("name" => "a.to_wh_code", "display" => "Ke Cabang", "width" => 100);
        $settings["columns"][] = array("name" => "a.npb_descs", "display" => "Keterangan", "width" => 300);
        $settings["columns"][] = array("name" => "if(a.npb_status = 0,'Draft',if(a.npb_status = 1,'Posted',if(a.npb_status = 2,'Approved','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.npb_no", "display" => "No. NPB");
        $settings["filters"][] = array("name" => "a.npb_descs", "display" => "Keterangan");
        $settings["filters"][] = array("name" => "a.npb_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "if(a.npb_status = 0,'Draft','Posted')", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 2;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = true;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Transfer Stock";

            if ($acl->CheckUserAccess("inventory.transfer", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "inventory.transfer/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.transfer", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.transfer/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Transfer terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("inventory.transfer", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.transfer/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("inventory.transfer", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.transfer/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Transfer terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("inventory.transfer", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "inventory.transfer/report", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("inventory.transfer", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approval", "Url" => "inventory.transfer/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Transaksi terlebih dahulu sebelum proses approval.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda menyetujui data transaksi yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approval", "Url" => "inventory.transfer/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Transaksi terlebih dahulu sebelum proses pembatalan.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda mau membatalkan approval data transaksi yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ic_transfer_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And year(a.npb_date) = ".$this->trxYear." And month(a.npb_date) = ".$this->trxMonth." And (a.cabang_id = " . $this->userCabangId . " or a.to_cabang_id = ". $this->userCabangId . ")";
            } else {
                $settings["where"] = "a.is_deleted = 0 And (a.cabang_id = " . $this->userCabangId . " or a.to_cabang_id = ". $this->userCabangId . ")";
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	/* Untuk entry data estimasi perbaikan dan penggantian spare part */
	public function add()
    {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $loader = null;
        $log = new UserAdmin();
        $transfer = new Transfer();
        $transfer->CabangId = $this->userCabangId;
        if (count($this->postData) > 0) {
            $transfer->CabangId = $this->userCabangId;
            $transfer->NpbDate = $this->GetPostValue("NpbDate");
            $transfer->NpbNo = $this->GetPostValue("NpbNo");
            $transfer->NpbDescs = $this->GetPostValue("NpbDescs");
            $transfer->ToWhId = $this->GetPostValue("ToWhId");
            $transfer->FrWhId = $this->GetPostValue("FrWhId");
            if ($this->GetPostValue("NpbStatus") == null || $this->GetPostValue("NpbStatus") == '') {
                $transfer->NpbStatus = 0;
            }else {
                $transfer->NpbStatus = $this->GetPostValue("NpbStatus");
            }
            $transfer->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            //$whs = new Warehouse($transfer->FrWhId);
            //$transfer->CabangId = $whs->CabangId;
            //$whs = new Warehouse($transfer->ToWhId);
            //$transfer->ToCabangId = $whs->CabangId;
            if ($this->ValidateMaster($transfer)) {
                if ($transfer->NpbNo == null || $transfer->NpbNo == "-" || $transfer->NpbNo == "") {
                    $transfer->NpbNo = $transfer->GetNpbDocNo();
                }
                $rs = $transfer->Insert();
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->Set("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                    $log = $log->UserActivityWriter($this->userCabangId, 'inventory.transfer', 'Add New Stock Transfer', $transfer->NpbNo, 'Failed');
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId, 'inventory.transfer', 'Add New Stock Transfer', $transfer->NpbNo, 'Success');
                    redirect_url("inventory.transfer/edit/" . $transfer->Id);
                }
            }
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("transfer", $transfer);
        //load data gudang asal
        $loader = new Warehouse();
        $whfrom = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("whfrom", $whfrom);
        //load data gudang tujuan
        $loader = new Warehouse();
        $whdest = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("whdest", $whdest);
	}

	private function ValidateMaster(Transfer $transfer) {
        if ($transfer->FrWhId == 0 || $transfer->FrWhId == null || $transfer->FrWhId == ''){
            $this->Set("error", "Gudang asal barang belum dipilih..");
            return false;
        }
        if ($transfer->ToWhId == 0 || $transfer->ToWhId == null || $transfer->ToWhId == ''){
            $this->Set("error", "Gudang tujuan barang belum dipilih..");
            return false;
        }
        if ($transfer->FrWhId == $transfer->ToWhId){
            $this->Set("error", "Gudang asal & tujuan tidak boleh sama..");
            return false;
        }
		return true;
	}

    public function edit($transferId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $loader = null;
        $log = new UserAdmin();
        $transfer = new Transfer();
        if (count($this->postData) > 0) {
            $transfer->Id = $transferId;
            $transfer->CabangId = $this->CabangId;
            $transfer->FrWhId = $this->GetPostValue("FrWhId");
            $transfer->ToWhId = $this->GetPostValue("ToWhId");
            $transfer->NpbDate = $this->GetPostValue("NpbDate");
            $transfer->NpbNo = $this->GetPostValue("NpbNo");
            $transfer->NpbDescs = $this->GetPostValue("NpbDescs");
            if ($this->GetPostValue("NpbStatus") == null || $this->GetPostValue("NpbStatus") == ''){
                $transfer->NpbStatus = 0;
            }else{
                $transfer->NpbStatus = $this->GetPostValue("NpbStatus");
            }
            $transfer->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($this->ValidateMaster($transfer)) {
                //$whs = new Warehouse($transfer->WarehouseId);
                //$transfer->CabangId = $whs->CabangId;
                //$whs = new Warehouse($transfer->ToWarehouseId);
                //$transfer->ToCabangId = $whs->CabangId;
                $rs = $transfer->Update($transfer->Id);
                if ($rs != 1) {
                    if ($this->connector->IsDuplicateError()) {
                        $this->Set("error", "Maaf Nomor Dokumen sudah ada pada database.");
                    } else {
                        $this->persistence->SaveState("error", "Maaf error saat simpan master dokumen. Message: " . $this->connector->GetErrorMessage());
                    }
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Update Stock Transfer',$transfer->NpbNo,'Failed');
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Update Stock Transfer',$transfer->NpbNo,'Success');
                    $this->persistence->SaveState("info", sprintf("Data Transfer/Nota No.: '%s' Tanggal: %s telah berhasil diubah..", $transfer->NpbNo, $transfer->NpbDate));
                    redirect_url("inventory.transfer/edit/".$transfer->Id);
                }
            }
        }else{
            $transfer = $transfer->LoadById($transferId);
            if($transfer == null){
               $this->persistence->SaveState("error", "Maaf, Data Stock Transfer dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("inventory.transfer");
            }
            if($transfer->NpbStatus == 2){
                $this->persistence->SaveState("error", sprintf("Maaf, Data Stock Transfer No. %s sudah berstatus -APPROVED-",$transfer->NpbNo));
                redirect_url("inventory.transfer");
            }
            if($transfer->CabangId != $this->userCabangId){
                $this->persistence->SaveState("error", sprintf("Data Stock Transfer No. %s tidak boleh diedit oleh user ini!",$transfer->NpbNo));
                redirect_url("inventory.transfer");
            }
        }
        // load details
        $transfer->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("transfer", $transfer);
        //load data gudang asal
        $loader = new Warehouse();
        $whfrom = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("whfrom", $whfrom);
        //load data gudang tujuan
        $loader = new Warehouse();
        $whdest = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("whdest", $whdest);
    }

	public function view($transferId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $loader = null;
        $transfer = new Transfer();
        $transfer = $transfer->LoadById($transferId);
        if($transfer == null){
            $this->persistence->SaveState("error", "Maaf, Data Stock Transfer dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("inventory.transfer");
        }
        // load details
        $transfer->LoadDetails();
        ///load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("transfer", $transfer);
        //load data gudang asal
        $loader = new Warehouse();
        $whfrom = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("whfrom", $whfrom);
        //load data gudang tujuan
        $loader = new Warehouse();
        $whdest = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("whdest", $whdest);
        $acl = AclManager::GetInstance();
        $this->Set("acl", $acl);
	}

    public function delete($transferId) {
        // Cek datanya
        $log = new UserAdmin();
        $transfer = new Transfer();
        $transfer = $transfer->FindById($transferId);
        if($transfer == null){
            $this->Set("error", "Maaf, Data Stock Transfer dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("inventory.transfer");
        }
        if($transfer->CabangId != $this->userCabangId){
            $this->persistence->SaveState("error", sprintf("Data Stock Transfer No. %s tidak boleh dihapus oleh user ini!",$transfer->NpbNo));
            redirect_url("inventory.transfer");
        }
        // periksa status po
        if($transfer->NpbStatus < 2){
            $transfer->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
            if ($transfer->Void($transferId) == 1) {
                $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Delete Stock Transfer',$transfer->NpbNo,'Success');
                $this->persistence->SaveState("info", sprintf("Data Transfer No: %s sudah berhasil dihapus", $transfer->NpbNo));
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Delete Stock Transfer',$transfer->NpbNo,'Failed');
                $this->persistence->SaveState("error", sprintf("Maaf, Data Transfer No: %s gagal dihapus", $transfer->NpbNo));
            }
        }else{
            $this->persistence->SaveState("error", sprintf("Maaf, Data Transfer No: %s sudah berstatus -APPROVED-", $transfer->NpbNo));
        }
        redirect_url("inventory.transfer");
    }

	public function add_detail($transferId = null) {
        $log = new UserAdmin();
        $transfer = new Transfer($transferId);
        $transferdetail = new TransferDetail();
        $transferdetail->NpbId = $transferId;
        $items = null;
        if (count($this->postData) > 0) {
            $transferdetail->ItemId = $this->GetPostValue("aItemId");
            $transferdetail->Qty = $this->GetPostValue("aQty");
            $rs = $transferdetail->Insert() == 1;
            if ($rs > 0) {
                $transfer->UpdateNpbStatus($transferId);
                $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Add Stock Transfer detail -> Item Code: '.$transferdetail->ItemCode.' = '.$transferdetail->Qty,$transfer->NpbNo,'Success');
                echo json_encode(array());
            }else{
                $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Add Stock Transfer detail -> Item Code: '.$transferdetail->ItemCode.' = '.$transferdetail->Qty,$transfer->NpbNo,'Failed');
                echo json_encode(array('errorMsg'=>'Some errors occured.'));
            }
        }
	}

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $transferdetail = new TransferDetail();
        $transferdetail = $transferdetail->FindById($id);
        if ($transferdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }else{
            $npbId = $transferdetail->NpbId;
        }
        if ($transferdetail->Delete($id) == 1) {
            $trxmaster = new Transfer();
            $trxmaster->UpdateNpbStatus($npbId);
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Delete Stock Transfer detail -> Item Code: '.$transferdetail->ItemCode.' = '.$transferdetail->Qty,$transferdetail->Id,'Success');
            printf("Data Detail Transfer ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.transfer','Delete Stock Transfer detail -> Item Code: '.$transferdetail->ItemCode.' = '.$transferdetail->Qty,$transferdetail->Id,'Failed');
            printf("Maaf, Data Detail Transfer ID: %d gagal dihapus!",$id);
        }
    }

    //proses cetak bukti stock transfer
    public function transfer_print() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Harap pilih data yang akan dicetak !");
            redirect_url("inventory.transfer");
            return;
        }
        $report = array();
        foreach ($ids as $id) {
            $trx = new Transfer();
            $trx = $trx->LoadById($id);
            $trx->LoadDetails();
            $report[] = $trx;
        }
        $this->Set("report", $report);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("inventory.transfer");
            return;
        }
        require_once(MODEL . "inventory/items.php");
        require_once(MODEL . "inventory/stock.php");
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $transfer = new Transfer();
            $transfer = $transfer->FindById($id);
            /** @var $transfer Transfer */
            // process npb
            if ($transfer->ToCabangId == $this->userCabangId) {
                if ($transfer->NpbStatus == 1) {
                    $txdetail = $transfer->LoadDetails();
                    if ($txdetail == null) {
                        continue;
                    }
                    /** @var $txdetail TransferDetail[] */
                    $flagSuccess = false;
                    $this->connector->BeginTransaction();
                    foreach ($txdetail as $detail) {
                        $items = new Items($detail->ItemId);
                        $stock = new Stock();
                        $stocks = $stock->LoadStocksFifo($this->trxYear, $detail->ItemId, $items->SuomCode, $transfer->FrWhId);
                        // Set variable-variable pendukung
                        $remainingQty = $detail->Qty;
                        $detail->Hpp = 0;
                        /** @var $stocks Stock[] */
                        foreach ($stocks as $stock) {
                            // Buat object stock keluarnya
                            $issue = new Stock();
                            $issue->TrxYear = $this->trxYear;
                            $issue->CreatedById = $this->userUid;
                            $issue->StockTypeCode = 102;                // Item Issue dari IS
                            $issue->ReffId = $detail->Id;
                            $issue->TrxDate = $transfer->NpbDate;
                            $issue->WarehouseId = $transfer->FrWhId;    // Gudang asal!
                            $issue->ItemId = $detail->ItemId;
                            //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                            $issue->UomCode = $items->SuomCode;
                            $issue->Price = $stock->Price;                // Ya pastilah pake angka ini...
                            $issue->UseStockId = $stock->Id;            // Kasi tau kalau issue ini based on stock id mana
                            $issue->QtyBalance = null;                    // Klo issue harus NULL

                            $stock->UpdatedById = $this->userUid;

                            if ($remainingQty > $stock->QtyBalance) {
                                // Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
                                $issue->Qty = $stock->QtyBalance;            // Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya
                                $remainingQty -= $stock->QtyBalance;        // Kita masih perlu...
                                $stock->QtyBalance = 0;                        // Habis...
                            } else {
                                // Barang di gudang mencukupi atau PAS
                                $issue->Qty = $remainingQty;
                                $stock->QtyBalance -= $remainingQty;
                                $remainingQty = 0;
                            }
                            // Apapun yang terjadi masukkan data issue stock
                            if ($issue->Insert() > 0) {
                                $flagSuccess = true;
                            } else {
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $transfer->NpbNo, $items->ItemCode, $items->ItemName);
                                break;        // Break loop stocks
                            }
                            // Update Qty Balance
                            if ($stock->Update($stock->Id) > 0) {
                                $flagSuccess = true;
                            } else {
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $transfer->NpbNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                                break;        // Break loop stocks
                            }
                            // OK jangan lupa update data cost
                            $detail->Hpp += $issue->Qty * $issue->Price;
                            if ($remainingQty <= 0) {
                                $flagSuccess = true;
                                // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                                break;
                            }
                        }

                        if ($flagSuccess) {
                            // Kalau tidak ada error isi stock masuknya !
                            // Buat object stock keluarnya
                            $instock = new Stock();
                            $instock->TrxYear = $this->trxYear;
                            $instock->CreatedById = $this->userUid;
                            $instock->StockTypeCode = 3;                // Stock masuk transfer
                            $instock->ReffId = $detail->Id;
                            $instock->TrxDate = $transfer->NpbDate;
                            $instock->WarehouseId = $transfer->ToWhId;    // Gudang tujuan !
                            $instock->ItemId = $detail->ItemId;
                            $instock->Qty = $detail->Qty;            // Depend on case...
                            $instock->UomCode = $items->SuomCode;
                            $instock->Price = round($detail->Hpp / $detail->Qty, 2);                // Ya pastilah pake angka ini...
                            $instock->UseStockId = null;                    // Kasi tau kalau issue ini based on stock id mana
                            $instock->QtyBalance = $detail->Qty;    /// Klo issue harus NULL
                            /// // Apapun yang terjadi masukkan data issue stock
                            if ($instock->Insert() > 0) {
                                $flagSuccess = true;
                            } else {
                                $flagSuccess = false;
                                $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $transfer->NpbNo, $items->ItemCode, $items->ItemName);
                            }
                        }
                        // Nah sekarang saatnya checking barang cukup atau tidak
                        if ($remainingQty > 0) {
                            // WTF... barang tidak cukup !!!
                            $flagSuccess = false;
                        }
                    }
                    if ($flagSuccess) {
                        $transfer->UpdateNpbApproveStatus($id, 2);
                        $this->connector->CommitTransaction();
                        $infos[] = sprintf("Data Transfer No.: '%s' (%s) telah berhasil di-approve.", $transfer->NpbNo, $transfer->NpbDescs);
                    } else {
                        $this->connector->RollbackTransaction();
                        $errors[] = sprintf("Maaf, Gagal proses approve Data Transfer: '%s'. Message: %s", $transfer->NpbNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    if ($transfer->NpbStatus == 0) {
                        $errors[] = sprintf("Data Transfer No.%s masih berstatus -Draft- !", $transfer->NpbNo);
                    } elseif ($transfer->NpbStatus == 2) {
                        $errors[] = sprintf("Data Transfer No.%s sudah berstatus -Approved- !", $transfer->NpbNo);
                    } else {
                        $errors[] = sprintf("Data Transfer No.%s berstatus -Void- !", $transfer->NpbNo);
                    }
                }
            }else{
                $errors[] = sprintf("NPB No. %s, User tidak berhak melakukan proses approval!", $transfer->NpbNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("inventory.transfer");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di batalkan !");
            redirect_url("inventory.transfer");
            return;
        }
        require_once(MODEL . "inventory/stock.php");
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $transfer = new Transfer();
            $transfer = $transfer->FindById($id);
            /** @var $transfer Transfer */
            // process npb
            if ($transfer->ToCabangId == $this->userCabangId) {
                if ($transfer->NpbStatus == 2) {
                    $txdetail = $transfer->LoadDetails();
                    if ($txdetail == null) {
                        continue;
                    }
                    /** @var $txdetail TransferDetail[] */
                    $flagSuccess = true;
                    $erno = 0;
                    $this->connector->BeginTransaction();
                    foreach ($txdetail as $detail) {
                        $did = $detail->Id;
                        $stock = new Stock();
                        $stock = $stock->FindByTypeReffId($this->trxYear, 102, $did);
                        if ($stock == null) {
                            $erno = 1;
                            $flagSuccess = false;
                        } else {
                            /** @var $stock Stock[] */
                            foreach ($stock as $dstock) {
                                $cstock = new Stock($dstock->UseStockId);
                                if ($cstock == null) {
                                    $erno = 2;
                                    $flagSuccess = false;
                                } else {
                                    $cstock->QtyBalance += $dstock->Qty;
                                    $rs = $cstock->Update($dstock->UseStockId);
                                    if (!$rs) {
                                        $erno = 3;
                                        $flagSuccess = false;
                                    }
                                }
                            }
                            if ($flagSuccess) {
                                $stock = new Stock();
                                $stock->UpdatedById = $this->userUid;
                                $stock->VoidByTypeReffId($this->trxYear, 3, $did);
                                $stock->VoidByTypeReffId($this->trxYear, 102, $did);
                            }
                        }
                    }
                    if ($flagSuccess) {
                        $transfer->UpdateNpbApproveStatus($id, 1);
                        $this->connector->CommitTransaction();
                        $infos[] = sprintf("Data Transfer No.: '%s' (%s) telah berhasil di-batalkan.", $transfer->NpbNo, $transfer->NpbDescs);
                    } else {
                        $this->connector->RollbackTransaction();
                        $errors[] = sprintf("[ER-%d] Maaf, Gagal proses unapprove Data Transfer: '%s'. Message: %s", $erno, $transfer->NpbNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    if ($transfer->NpbStatus == 0) {
                        $errors[] = sprintf("Data Transfer No.%s masih berstatus -Draft- !", $transfer->NpbNo);
                    } elseif ($transfer->NpbStatus == 1) {
                        $errors[] = sprintf("Data Transfer No.%s masih berstatus -Posted- !", $transfer->NpbNo);
                    } else {
                        $errors[] = sprintf("Data Transfer No.%s berstatus -Void- !", $transfer->NpbNo);
                    }
                }
            }else{
                $errors[] = sprintf("NPB No. %s, User tidak berhak melakukan proses pembatalan approval!", $transfer->NpbNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("inventory.transfer");
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sGudangId = $this->GetPostValue("GudangId");
            $sStatus = $this->GetPostValue("NpbStatus");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $transfer = new Transfer();
            if ($sJnsLaporan == 1) {
                $reports = $transfer->Load4Reports($this->userCabangId, $sGudangId, $sStartDate, $sEndDate, $sStatus);
            }else{
                $reports = $transfer->LoadRekap4Reports($this->userCabangId, $sGudangId, $sStartDate, $sEndDate, $sStatus);
            }
        }else{
            $sCabangId = 0;
            $sGudangId = 0;
            $sStatus = -1;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sJnsLaporan = 1;
            $sOutput = 0;
            $reports = null;
        }
        $company = new Company($this->userCompanyId);
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        if ($this->userLevel > 3){
            $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        }else{
            $cabang = $loader->LoadById($this->userCabangId);
            $cabCode = $cabang->Kode;
            $cabName = $cabang->Cabang;
        }
        $loader = new Warehouse();
        $gudang = $loader->LoadByCompanyId($this->userCompanyId);
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("CabangId",$sCabangId);
        $this->Set("GudangId",$sGudangId);
        $this->Set("NpbStatus",$sStatus);
        $this->Set("cabangs", $cabang);
        $this->Set("gudangs", $gudang);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("JnsLaporan",$sJnsLaporan);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("company_name", $company->CompanyName);
    }
}


// End of File: estimasi_controller.php
