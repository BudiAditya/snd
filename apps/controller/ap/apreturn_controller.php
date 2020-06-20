<?php
class ApReturnController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "ap/apreturn.php");
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
        //$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Entity", "width" => 30);
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 80);
        $settings["columns"][] = array("name" => "a.rb_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.rb_no", "display" => "No. Bukti", "width" => 80);
        $settings["columns"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier", "width" => 150);
        $settings["columns"][] = array("name" => "a.rb_descs", "display" => "Keterangan", "width" => 160);
        $settings["columns"][] = array("name" => "format(a.rb_amount,0)", "display" => "Nilai Retur", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.rb_allocate,0)", "display" => "Alokasi", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.rb_amount - a.rb_allocate,0)", "display" => "Sisa", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 80);
        $settings["columns"][] = array("name" => "if(a.rb_status = 0,'Draft',if(a.rb_status = 1,'Posted',if(a.rb_status = 2,'Approved','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.rb_no", "display" => "No. Bukti");
        $settings["filters"][] = array("name" => "a.rb_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.supplier_name", "display" => "Nama Supplier");
        $settings["filters"][] = array("name" => "if(a.rb_status = 0,'Draft',if(a.rb_status = 1,'Posted',if(a.rb_status = 2,'Approved','Void')))", "display" => "Status");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Retur Ex. Pembelian";

            if ($acl->CheckUserAccess("ap.apreturn", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ap.apreturn/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.apreturn", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ap.apreturn/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data ApReturn terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ap.apreturn", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ap.apreturn/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ap.apreturn", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ap.apreturn/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data ApReturn terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data apreturn","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.apreturn", "print")) {
                $settings["actions"][] = array("Text" => "Print Bukti", "Url" => "ap.apreturn/print_pdf/%s", "Class" => "bt_pdf", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data ApReturn terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data apreturn","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ap.apreturn", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ap.apreturn/report", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ap.apreturn", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve Retur", "Url" => "ap.apreturn/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Retur terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data retur yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approve", "Url" => "ap.apreturn/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Retur terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data retur yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ap_return_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.rb_date) = ".$this->trxYear." And month(a.rb_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	private function ValidateMaster(ApReturn $apreturn) {
        // validation here
        if ($apreturn->SupplierId > 0){
            return true;
        }else{
            $this->Set("error", "Nama Supplier masih kosong..");
            return false;
        }
	}

    public function add($apreturnId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $apreturn = new ApReturn();
        $log = new UserAdmin();
        if ($apreturnId > 0) {
            $apreturn = $apreturn->LoadById($apreturnId);
            if($apreturn == null){
               $this->persistence->SaveState("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ap.apreturn");
            }
            if ($apreturn->RbStatus == 2){
                $this->Set("error", "Maaf Data Return ini berstatus -APPROVED-!");
                redirect_url("ap.apreturn");
            }
            if ($apreturn->RbStatus == 3){
                $this->Set("error", "Maaf Data Return ini berstatus -VOID-!");
                redirect_url("ap.apreturn");
            }
            if ($apreturn->RbAllocate > 0){
                $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh diubah!");
                redirect_url("ap.apreturn");
            }
        }
        // load details
        $apreturn->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Warehouse();
        $gudangs = $loader->LoadByCabangId($this->userCabangId,1);
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("apreturn", $apreturn);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->ApReturnItemsCount($apreturnId));
    }

    public function proses_master($apreturnId = 0){
        $apreturn = new ApReturn();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $apreturn->Id = $apreturnId;
            $apreturn->CabangId = $this->userCabangId;
            $apreturn->GudangId = $this->GetPostValue("GudangId");
            $apreturn->RbDate = date('Y-m-d',strtotime($this->GetPostValue("RbDate")));
            $apreturn->RbNo = $this->GetPostValue("RbNo");
            $apreturn->RbDescs = $this->GetPostValue("RbDescs");
            $apreturn->SupplierId = $this->GetPostValue("SupplierId");
            if ($this->GetPostValue("RbStatus") == null || $this->GetPostValue("RbStatus") == 0){
                $apreturn->RbStatus = 1;
            }else{
                $apreturn->RbStatus = $this->GetPostValue("RbStatus");
            }
            if ($apreturn->Id == 0) {
                $apreturn->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $apreturn->RbNo = $apreturn->GetApReturnDocNo();
                $rs = $apreturn->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.grn','Add New Return',$apreturn->RbNo,'Success');
                    printf("OK|A|%d|%s",$apreturn->Id,$apreturn->RbNo);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.grn','Add New Return',$apreturn->RbNo,'Failed');
                    printf("ER|A|%d",$apreturn->Id);
                }
            }else{
                $apreturn->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $apreturn->Update($apreturn->Id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.grn','Update Return',$apreturn->RbNo,'Success');
                    printf("OK|U|%d|%s",$apreturn->Id,$apreturn->RbNo);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.grn','Update Grn',$apreturn->RbNo,'Failed');
                    printf("ER|U|%d",$apreturn->Id);
                }
            }
        }else{
            printf("ER|X|%d",$apreturnId);
        }
    }

	public function view($apreturnId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $apreturn = new ApReturn();
        $apreturn = $apreturn->LoadById($apreturnId);
        if($apreturn == null){
            $this->persistence->SaveState("error", "Maaf Data ApReturn dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.apreturn");
        }
        // load details
        $apreturn->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Warehouse();
        $gudangs = $loader->LoadByCabangId($this->userCabangId);
        //kirim ke view
        $this->Set("gudangs", $gudangs);
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("apreturn", $apreturn);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->ApReturnItemsCount($apreturnId));
	}

    public function delete($apreturnId) {
        // Cek datanya
        $log = new UserAdmin();
        $apreturn = new ApReturn();
        $apreturn = $apreturn->FindById($apreturnId);
        if($apreturn == null){
            $this->Set("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.apreturn");
        }
        /** @var $apreturn ApReturn */
        if ($apreturn->RbAllocate > 0){
            $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh dihapus!");
            redirect_url("ap.apreturn");
        }
        if ($apreturn->Delete($apreturnId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Delete Return',$apreturn->RbNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Return No: %s sudah berhasil dihapus", $apreturn->RbNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Delete Return',$apreturn->RbNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Return No: %s gagal dihapus", $apreturn->RbNo));
        }
        redirect_url("ap.apreturn");
    }

    public function void($apreturnId) {
        // Cek datanya
        $log = new UserAdmin();
        $apreturn = new ApReturn();
        $apreturn = $apreturn->FindById($apreturnId);
        if($apreturn == null){
            $this->Set("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.apreturn");
        }
        /** @var $apreturn ApReturn */
        if ($apreturn->RbAllocate > 0){
            $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh dihapus!");
            redirect_url("ap.apreturn");
        }
        if ($apreturn->RbStatus == 3){
            $this->Set("error", "Maaf Data Return sudah berstatus -VOID-!");
            redirect_url("ap.apreturn");
        }
        if ($apreturn->Void($apreturnId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Delete Return',$apreturn->RbNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Return No: %s sudah berhasil dibatalkan", $apreturn->RbNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Delete Return',$apreturn->RbNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Return No: %s gagal dibatalkan", $apreturn->RbNo));
        }
        redirect_url("ap.apreturn");
    }

	public function add_detail($apreturnId = null) {
        require_once(MODEL . "inventory/items.php");
        require_once(MODEL . "inventory/stock.php");
        $apreturn = new ApReturn($apreturnId);
        $retdetail = new ApReturnDetail();
        $log = new UserAdmin();
        $retdetail->RbId = $apreturnId;
        $items = null;
        if (count($this->postData) > 0) {
            $retdetail->ExGrnId = $this->GetPostValue("aExGrnId");
            $retdetail->ItemId = $this->GetPostValue("aItemId");
            $retdetail->ExGrnDetailId = $this->GetPostValue("aExGrnDetailId");
            $retdetail->QtyBeli = $this->GetPostValue("aQtyBeli");
            $retdetail->QtyRetur = $this->GetPostValue("aQtyRetur");
            $retdetail->Price = $this->GetPostValue("aPrice");
            $retdetail->DiscFormula = $this->GetPostValue("aDiscFormula");
            $retdetail->DiscAmount = $this->GetPostValue("aDiscAmount");
            $retdetail->PpnPct = $this->GetPostValue("aPpnPct");
            $retdetail->PphPct = $this->GetPostValue("aPphPct");
            $retdetail->Kondisi = $this->GetPostValue("aKondisi");
            $retdetail->IsFree = $this->GetPostValue("aIsFree");
            $retdetail->ItemHpp = $this->GetPostValue("aItemHpp");
            // insert ke table
            $flagSuccess = false;
            $this->connector->BeginTransaction();
            $rs = $retdetail->Insert()== 1;
            if ($rs > 0) {
                //pasti adalah item ini
                $items = new Items($retdetail->ItemId);
                $stock = new Stock();
                $stocks = $stock->LoadStocksFifo($this->trxYear,$retdetail->ItemId,$items->SuomCode,$apreturn->GudangId);
                // Set variable-variable pendukung
                $remainingQty = $retdetail->QtyRetur;
                //$retdetail->ItemHpp = 0;
                /** @var $stocks Stock[] */
                foreach ($stocks as $stock) {
                    // Buat object stock keluarnya
                    $issue = new Stock();
                    $issue->TrxYear = $this->trxYear;
                    $issue->CreatedById = $this->userUid;
                    $issue->StockTypeCode = 103;				// Item Issue dari IS
                    $issue->ReffId = $retdetail->Id;
                    $issue->TrxDate = $apreturn->RbDate;
                    $issue->WarehouseId = $apreturn->GudangId;	// Gudang asal!
                    $issue->ItemId = $retdetail->ItemId;
                    //$issue->Qty = $stock->QtyBalance;			// Depend on case...
                    $issue->UomCode = $items->SuomCode;
                    $issue->Price = $stock->Price;				// Ya pastilah pake angka ini...
                    $issue->UseStockId = $stock->Id;			// Kasi tau kalau issue ini based on stock id mana
                    $issue->QtyBalance = null;					// Klo issue harus NULL

                    $stock->UpdatedById = $this->userUid;

                    if ($remainingQty > $stock->QtyBalance) {
                        // Waduh stock pertama ga cukup... gpp kita coba habiskan dulu...
                        $issue->Qty = $stock->QtyBalance;			// Berhubung barang yang dikeluarkan tidak cukup ambil dari sisanya
                        $remainingQty -= $stock->QtyBalance;		// Kita masih perlu...
                        $stock->QtyBalance = 0;						// Habis...
                    } else {
                        // Barang di gudang mencukupi atau PAS
                        $issue->Qty = $remainingQty;
                        $stock->QtyBalance -= $remainingQty;
                        $remainingQty = 0;
                    }
                    // Apapun yang terjadi masukkan data issue stock
                    if ($issue->Insert() > 0) {
                        $flagSuccess = true;
                    }else{
                        $flagSuccess = false;
                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Stock tidak cukup!", $apreturn->RbNo, $items->ItemCode, $items->ItemName);
                        break;		// Break loop stocks
                    }
                    // Update Qty Balance
                    if ($stock->Update($stock->Id) > 0) {
                        $flagSuccess = true;
                    }else{
                        $flagSuccess = false;
                        $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal update data stock ! Message: %s", $apreturn->RbNo, $items->ItemCode, $items->ItemName, $this->connector->GetErrorMessage());
                        break;		// Break loop stocks
                    }
                    // OK jangan lupa update data cost
                    //$retdetail->Hpp += $issue->Qty * $issue->Price;
                    if ($remainingQty <= 0) {
                        $flagSuccess = true;
                        // Barang yang di issue sudah mencukupi... (TIDAK ERROR !)
                        break;
                    }
                }	// End Loop: foreach ($stocks as $stock) {
                // Nah sekarang saatnya checking barang cukup atau tidak
                if ($remainingQty > 0) {
                    // WTF... barang tidak cukup !!!
                    $flagSuccess = false;
                }
            }
            if ($flagSuccess) {
                $this->connector->CommitTransaction();
                $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Add Return detail -> Ex.Grn No: '.$retdetail->ExGrnNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$apreturn->RbNo,'Success');
                echo json_encode(array());
            } else {
                $this->connector->RollbackTransaction();
                $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Add Return detail -> Ex.Grn No: '.$retdetail->ExGrnNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$apreturn->RbNo,'Failed');
                echo json_encode(array('errorMsg'=>$errors));
            }
        }
	}

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $retdetail = new ApReturnDetail();
        $retdetail = $retdetail->FindById($id);
        if ($retdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $flagSuccess = true;
        $this->connector->BeginTransaction();
        if ($retdetail->Delete($id) == 1) {
            require_once(MODEL . "inventory/stock.php");
            $stock = new  Stock();
            $stock = $stock->FindByTypeReffId($this->trxYear,103,$id);
            if ($stock == null){
                $flagSuccess = false;
            }else {
                /** @var $stock Stock[]*/
                foreach ($stock as $dstock){
                    $cstock = new Stock($dstock->UseStockId);
                    if ($cstock == null){
                        $flagSuccess = false;
                    }else{
                        $cstock->QtyBalance += $dstock->Qty;
                        $rs = $cstock->Update($dstock->UseStockId);
                        if (!$rs){
                            $flagSuccess = false;
                        }
                    }
                }
                if ($flagSuccess) {
                    $stock = new Stock();
                    $stock->UpdatedById = $this->userUid;
                    $stock->VoidByTypeReffId($this->trxYear,103, $id);
                }
            }
        }
        if ($flagSuccess){
            $this->connector->CommitTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Delete Return detail -> Ex.Grn No: '.$retdetail->ExGrnNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$retdetail->RbId,'Success');
            printf("Data Detail ID: %d berhasil dihapus!",$id);
        }else{
            $this->connector->RollbackTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'ap.apreturn','Delete Return detail -> Ex.Grn No: '.$retdetail->ExGrnNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$retdetail->RbId,'Success');
            printf("Maaf, Data Detail ID: %d gagal dihapus!",$id);
        }
    }

    public function print_pdf($apreturnId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/karyawan.php");
        $loader = null;
        $apreturn = new ApReturn();
        $apreturn = $apreturn->LoadById($apreturnId);
        if($apreturn == null){
            $this->persistence->SaveState("error", "Maaf Data ApReturn dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ap.apreturn");
        }
        // load details
        $apreturn->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByEntityId($this->userCompanyId);
        $loader = new Karyawan();
        $sales = $loader->LoadAll();
        $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
        //kirim ke view
        $this->Set("sales", $sales);
        $this->Set("cabangs", $cabang);
        $this->Set("apreturn", $apreturn);
        $this->Set("userName", $userName);
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "ap/supplier.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sSuppliersId = $this->GetPostValue("SuppliersId");
            $sKondisi = $this->GetPostValue("Kondisi");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $apreturn = new ApReturn();
            if ($sJnsLaporan == 1){
                $reports = $apreturn->Load4Reports($this->userCompanyId,$sCabangId,$sSuppliersId,$sKondisi,$sStartDate,$sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $apreturn->Load4ReportsDetail($this->userCompanyId,$sCabangId,$sSuppliersId,$sKondisi,$sStartDate,$sEndDate);
            }else{
                $reports = $apreturn->Load4ReportsRekapItem($this->userCompanyId,$sCabangId,$sSuppliersId,$sKondisi,$sStartDate,$sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sSuppliersId = 0;
            $sKondisi = 0;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $sJnsLaporan = 1;
            $reports = null;
        }
        $supplier = new Supplier();
        $supplier = $supplier->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
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
        // kirim ke view
        $this->Set("cabangs", $cabang);
        $this->Set("suppliers",$supplier);
        $this->Set("CabangId",$sCabangId);
        $this->Set("SuppliersId",$sSuppliersId);
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("Kondisi",$sKondisi);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
        $this->Set("JnsLaporan",$sJnsLaporan);
    }

    public function getApReturnItemRows($id){
        $apreturn = new ApReturn();
        $rows = $apreturn->GetApReturnItemRow($id);
        print($rows);
    }

    public function createTextApReturn($id){
        $apreturn = new ApReturn($id);
        if ($apreturn <> null){
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $apreturn->CompanyName);
            fwrite($myfile, "\n".'FAKTUR PENJUALAN');

            fclose($myfile);
        }
    }

    public function getjson_returnlists($cabangId,$supplierId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $apreturn = new ApReturn();
        $retlists = $apreturn->GetJSonApReturns($cabangId,$supplierId,$filter);
        echo json_encode($retlists);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ap.apreturn");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $return = new ApReturn();
            $log = new UserAdmin();
            $return = $return->FindById($id);
            /** @var $return ApReturn */
            // process retur
            if($return->RbStatus == 1){
                if ($return->RbAmount > 0) {
                    $rs = $return->Approve($return->Id);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ap.apreturn', 'Approve Return', $return->RbNo, 'Success');
                        $infos[] = sprintf("Data Retur No.: '%s' (%s) telah berhasil di-approve.", $return->RbNo, $return->RbDescs);
                    }
                }else{
                    $errors[] = sprintf("Detail Data Retur No.%s belum diisi !",$return->RbNo);
                }
            }else{
                $errors[] = sprintf("Data Retur No.%s sudah berstatus -Approved- !",$return->RbNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.apreturn");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di batalkan !");
            redirect_url("ap.apreturn");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $return = new ApReturn();
            $log = new UserAdmin();
            $return = $return->FindById($id);
            /** @var $return ApReturn */
            // process retur
            if($return->RbStatus == 2){
                $rs = $return->Unapprove($return->Id);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId, 'ap.apreturn', 'Unapprove Return', $return->RbNo, 'Success');
                    $infos[] = sprintf("Data Retur No.: '%s' (%s) telah berhasil di-batalkan.", $return->RbNo, $return->RbDescs);
                }
            }else{
                $errors[] = sprintf("Data Retur No.%s masih berstatus -Posted- !",$return->RbNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ap.apreturn");
    }

    public function ApReturnItemsCount($id){
        $apreturn = new ApReturn();
        $rows = $apreturn->GetApReturnItemRow($id);
        return $rows;
    }

    public function getjson_grnlists($gudangId,$supplierId){
        require_once (MODEL . "ap/purchase.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $grns = new Purchase();
        $invlists = $grns->GetJSonGrnsByGudang($gudangId,$supplierId,$filter);
        echo json_encode($invlists);
    }

    public function getjson_grnitems($grnId = 0){
        require_once (MODEL . "ap/purchase.php");
        $grns = new Purchase();
        $itemlists = $grns->GetJSonGrnItems($grnId);
        echo json_encode($itemlists);
    }
}


// End of File: estimasi_controller.php
