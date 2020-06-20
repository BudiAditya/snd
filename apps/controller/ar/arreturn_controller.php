<?php
class ArReturnController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "ar/arreturn.php");
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
        $settings["columns"][] = array("name" => "a.rj_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.rj_no", "display" => "No. Bukti", "width" => 80);
        $settings["columns"][] = array("name" => "a.customer_name", "display" => "Nama Customer", "width" => 150);
        $settings["columns"][] = array("name" => "a.rj_descs", "display" => "Keterangan", "width" => 160);
        $settings["columns"][] = array("name" => "format(a.rj_amount,0)", "display" => "Nilai Retur", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.rj_allocate,0)", "display" => "Alokasi", "width" => 90, "align" => "right");
        //$settings["columns"][] = array("name" => "format(a.rj_amount - a.rj_allocate,0)", "display" => "Sisa", "width" => 90, "align" => "right");
        $settings["columns"][] = array("name" => "a.admin_name", "display" => "Admin", "width" => 80);
        $settings["columns"][] = array("name" => "if(a.rj_status = 0,'Draft',if(a.rj_status = 1,'Posted',if(a.rj_status = 2,'Approved','Void')))", "display" => "Status", "width" => 50);

        $settings["filters"][] = array("name" => "a.rj_no", "display" => "No. Bukti");
        $settings["filters"][] = array("name" => "a.rj_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.customer_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "if(a.rj_status = 0,'Draft',if(a.rj_status = 1,'Posted',if(a.rj_status = 2,'Approved','Void')))", "display" => "Status");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Retur Ex. Penjualan";

            if ($acl->CheckUserAccess("ar.arreturn", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "ar.arreturn/add/0", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.arreturn", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "ar.arreturn/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data ArReturn terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("ar.arreturn", "delete")) {
                $settings["actions"][] = array("Text" => "Void", "Url" => "ar.arreturn/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("ar.arreturn", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "ar.arreturn/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data ArReturn terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data arreturn","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.arreturn", "print")) {
                $settings["actions"][] = array("Text" => "Print Bukti", "Url" => "ar.arreturn/print_pdf/%s", "Class" => "bt_pdf", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data ArReturn terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data arreturn","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("ar.arreturn", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "ar.arreturn/report", "Class" => "bt_report", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("ar.arreturn", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve Retur", "Url" => "ar.arreturn/approve", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Retur terlebih dahulu sebelum proses approval.",
                    "Confirm" => "Apakah anda menyetujui data retur yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approve", "Url" => "ar.arreturn/unapprove", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Retur terlebih dahulu sebelum proses pembatalan.",
                    "Confirm" => "Apakah anda mau membatalkan approval data retur yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
            }
        } else {
            $settings["from"] = "vw_ar_return_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.rj_date) = ".$this->trxYear." And month(a.rj_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	private function ValidateMaster(ArReturn $arreturn) {
        // validation here
        if ($arreturn->CustomerId > 0){
            return true;
        }else{
            $this->Set("error", "Nama Customer masih kosong..");
            return false;
        }
	}

    public function add($arreturnId = 0) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $arreturn = new ArReturn();
        if ($arreturnId > 0) {
            $arreturn = $arreturn->LoadById($arreturnId);
            if($arreturn == null){
               $this->persistence->SaveState("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
               redirect_url("ar.arreturn");
            }
            if ($arreturn->RjStatus == 2){
                $this->Set("error", "Maaf Data Return ini berstatus -APPROVED-!");
                redirect_url("ar.arreturn");
            }
            if ($arreturn->RjStatus == 3){
                $this->Set("error", "Maaf Data Return ini berstatus -VOID-!");
                redirect_url("ar.arreturn");
            }
            if ($arreturn->RjAllocate > 0){
                $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh diubah!");
                redirect_url("ar.arreturn");
            }
        }
        // load details
        $arreturn->LoadDetails();
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
        $this->Set("arreturn", $arreturn);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->ArReturnItemsCount($arreturnId));
    }

    public function edit($arreturnId = 0) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $arreturn = new ArReturn();
        if ($arreturnId > 0) {
            $arreturn = $arreturn->LoadById($arreturnId);
            if($arreturn == null){
                $this->persistence->SaveState("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("ar.arreturn");
            }
            if ($arreturn->RjStatus == 2){
                $this->Set("error", "Maaf Data Return ini berstatus -APPROVED-!");
                redirect_url("ar.arreturn");
            }
            if ($arreturn->RjStatus == 3){
                $this->Set("error", "Maaf Data Return ini berstatus -VOID-!");
                redirect_url("ar.arreturn");
            }
            if ($arreturn->RjAllocate > 0){
                $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh diubah!");
                redirect_url("ar.arreturn");
            }
        }
        // load details
        $arreturn->LoadDetails();
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
        $this->Set("arreturn", $arreturn);
        $this->Set("acl", $acl);
        $this->Set("itemsCount", $this->ArReturnItemsCount($arreturnId));
    }

    public function proses_master($arreturnId = 0){
        $arreturn = new ArReturn();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $arreturn->Id = $arreturnId;
            $arreturn->CabangId = $this->userCabangId;
            $arreturn->GudangId = $this->GetPostValue("GudangId");
            $arreturn->RjDate = date('Y-m-d',strtotime($this->GetPostValue("RjDate")));
            $arreturn->RjNo = $this->GetPostValue("RjNo");
            $arreturn->RjDescs = $this->GetPostValue("RjDescs");
            $arreturn->CustomerId = $this->GetPostValue("CustomerId");
            if ($this->GetPostValue("RjStatus") == null || $this->GetPostValue("RjStatus") == 0){
                $arreturn->RjStatus = 1;
            }else{
                $arreturn->RjStatus = $this->GetPostValue("RjStatus");
            }
            if ($arreturn->Id == 0) {
                $arreturn->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $arreturn->RjNo = $arreturn->GetArReturnDocNo();
                $rs = $arreturn->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Add New Return',$arreturn->RjNo,'Success');
                    printf("OK|A|%d|%s",$arreturn->Id,$arreturn->RjNo);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Add New Return',$arreturn->RjNo,'Failed');
                    printf("ER|A|%d",$arreturn->Id);
                }
            }else{
                $arreturn->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $arreturn->Update($arreturn->Id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Update Return',$arreturn->RjNo,'Success');
                    printf("OK|U|%d|%s",$arreturn->Id,$arreturn->RjNo);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'ar.invoice','Update Invoice',$arreturn->RjNo,'Failed');
                    printf("ER|U|%d",$arreturn->Id);
                }
            }
        }else{
            printf("ER|X|%d",$arreturnId);
        }
    }

	public function view($arreturnId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/karyawan.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $arreturn = new ArReturn();
        $arreturn = $arreturn->LoadById($arreturnId);
        if($arreturn == null){
            $this->persistence->SaveState("error", "Maaf Data ArReturn dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.arreturn");
        }
        // load details
        $arreturn->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Karyawan();
        $sales = $loader->LoadAll();
        //kirim ke view
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("sales", $sales);
        $this->Set("cabangs", $cabang);
        $this->Set("arreturn", $arreturn);
        $this->Set("acl", $acl);
	}

    public function delete($arreturnId) {
        // Cek datanya
        $log = new UserAdmin();
        $arreturn = new ArReturn();
        $arreturn = $arreturn->FindById($arreturnId);
        if($arreturn == null){
            $this->Set("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.arreturn");
        }
        /** @var $arreturn ArReturn */
        if ($arreturn->RjAllocate > 0){
            $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh dihapus!");
            redirect_url("ar.arreturn");
        }
        if ($arreturn->Delete($arreturnId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Delete Return',$arreturn->RjNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Return No: %s sudah berhasil dihapus", $arreturn->RjNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Delete Return',$arreturn->RjNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Return No: %s gagal dihapus", $arreturn->RjNo));
        }
        redirect_url("ar.arreturn");
    }

    public function void($arreturnId) {
        // Cek datanya
        $log = new UserAdmin();
        $arreturn = new ArReturn();
        $arreturn = $arreturn->FindById($arreturnId);
        if($arreturn == null){
            $this->Set("error", "Maaf Data Return dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.arreturn");
        }
        /** @var $arreturn ArReturn */
        if ($arreturn->RjAllocate > 0){
            $this->Set("error", "Maaf Data Return sudah dialokasikan. Tidak boleh dihapus!");
            redirect_url("ar.arreturn");
        }
        if ($arreturn->RjStatus == 3){
            $this->Set("error", "Maaf Data Return sudah berstatus -VOID-!");
            redirect_url("ar.arreturn");
        }
        if ($arreturn->Void($arreturnId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Delete Return',$arreturn->RjNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Return No: %s sudah berhasil dibatalkan", $arreturn->RjNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Delete Return',$arreturn->RjNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Return No: %s gagal dibatalkan", $arreturn->RjNo));
        }
        redirect_url("ar.arreturn");
    }

	public function add_detail($arreturnId = null) {
        $arreturn = new ArReturn($arreturnId);
        $retdetail = new ArReturnDetail();
        $log = new UserAdmin();
        $retdetail->RjId = $arreturnId;
        $items = null;
        $did = 0;
        if (count($this->postData) > 0) {
            $retdetail->ExInvoiceId = $this->GetPostValue("aExInvoiceId");
            $retdetail->ItemId = $this->GetPostValue("aItemId");
            $retdetail->ExInvDetailId = $this->GetPostValue("aExInvDetailId");
            $retdetail->QtyJual = $this->GetPostValue("aQtyJual");
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
                require_once(MODEL . "inventory/stock.php");
                require_once(MODEL . "inventory/items.php");
                $items = new Items($retdetail->ItemId);
                $did = $retdetail->Id;
                // Buat object stock masuknya
                $issue = new Stock();
                $issue->TrxYear = $this->trxYear;
                $issue->CreatedById = $this->userUid;
                $issue->StockTypeCode = 4;                // Item Issue dari IS
                $issue->ReffId = $retdetail->Id;
                $issue->TrxDate = $arreturn->RjDate;
                $issue->WarehouseId = $arreturn->GudangId;    // Gudang asal!
                $issue->ItemId = $retdetail->ItemId;
                $issue->Qty = $retdetail->QtyRetur;            // Depend on case...
                $issue->UomCode = $items->SuomCode;
                $issue->Price = $retdetail->ItemHpp;                // Ya pastilah pake angka ini...
                $issue->UseStockId = null;            // Kasi tau kalau issue ini based on stock id mana
                $issue->QtyBalance = $retdetail->QtyRetur;                    // Klo issue harus NULL
                // Apapun yang terjadi masukkan data issue stock
                if ($issue->Insert() > 0) {
                    $flagSuccess = true;
                } else {
                    $flagSuccess = false;
                    $errors[] = sprintf("%s -> Item: [%s] %s Message: Gagal create data stock!", $arreturn->RjNo, $items->ItemCode, $items->ItemName);
                }
            }
            if ($flagSuccess) {
                $this->connector->CommitTransaction();
                $rs = $retdetail->Posting($did);
                $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Add Return detail -> Ex.Inv No: '.$retdetail->ExInvoiceNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$arreturn->RjNo,'Success');
                echo json_encode(array());
            } else {
                $this->connector->RollbackTransaction();
                $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Add Return detail -> Ex.Inv No: '.$retdetail->ExInvoiceNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$arreturn->RjNo,'Failed');
                echo json_encode(array('errorMsg'=>$errors));
            }
        }
	}

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $retdetail = new ArReturnDetail();
        $retdetail = $retdetail->FindById($id);
        if ($retdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        $flagSuccess = false;
        $this->connector->BeginTransaction();
        $rs = $retdetail->Unposting($id);
        if ($rs) {
            if ($retdetail->Delete($id) == 1) {
                require_once(MODEL . "inventory/stock.php");
                $stock = new Stock();
                $rs = $stock->VoidByTypeReffId($this->trxYear, 4, $id);
                if ($rs) {
                    $flagSuccess = true;
                }
            }
        }
        //lanjut proses
        if ($flagSuccess){
            $this->connector->CommitTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Delete Return detail -> Ex.Inv No: '.$retdetail->ExInvoiceNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$retdetail->RjId,'Success');
            printf("Data Detail ID: %d berhasil dihapus!",$id);
        }else{
            $this->connector->RollbackTransaction();
            $log = $log->UserActivityWriter($this->userCabangId,'ar.arreturn','Delete Return detail -> Ex.Inv No: '.$retdetail->ExInvoiceNo.' -> Item Code: '.$retdetail->ItemCode.' = '.$retdetail->QtyRetur,$retdetail->RjId,'Success');
            printf("Maaf, Data Detail ID: %d gagal dihapus!",$id);
        }
    }

    public function print_pdf($arreturnId = null) {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/karyawan.php");
        $loader = null;
        $arreturn = new ArReturn();
        $arreturn = $arreturn->LoadById($arreturnId);
        if($arreturn == null){
            $this->persistence->SaveState("error", "Maaf Data ArReturn dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("ar.arreturn");
        }
        // load details
        $arreturn->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new Karyawan();
        $sales = $loader->LoadAll();
        $userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
        //kirim ke view
        $this->Set("sales", $sales);
        $this->Set("cabangs", $cabang);
        $this->Set("arreturn", $arreturn);
        $this->Set("userName", $userName);
    }

    public function report(){
        // report rekonsil process
        require_once(MODEL . "ar/customer.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sCustomersId = $this->GetPostValue("CustomersId");
            $sKondisi = $this->GetPostValue("Kondisi");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $arreturn = new ArReturn();
            if ($sJnsLaporan == 1){
                $reports = $arreturn->Load4Reports($this->userCompanyId,$sCabangId,$sCustomersId,$sKondisi,$sStartDate,$sEndDate);
            }elseif ($sJnsLaporan == 2){
                $reports = $arreturn->Load4ReportsDetail($this->userCompanyId,$sCabangId,$sCustomersId,$sKondisi,$sStartDate,$sEndDate);
            }else{
                $reports = $arreturn->Load4ReportsRekapItem($this->userCompanyId,$sCabangId,$sCustomersId,$sKondisi,$sStartDate,$sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sCustomersId = 0;
            $sKondisi = 0;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            //$sStartDate = date('d-m-Y',$sStartDate);
            $sEndDate = time();
            //$sEndDate = date('d-m-Y',$sEndDate);
            $sOutput = 0;
            $sJnsLaporan = 1;
            $reports = null;
        }
        $customer = new Customer();
        $customer = $customer->LoadAll();
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
        $this->Set("customers",$customer);
        $this->Set("CabangId",$sCabangId);
        $this->Set("CustomersId",$sCustomersId);
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

    public function getArReturnItemRows($id){
        $arreturn = new ArReturn();
        $rows = $arreturn->GetArReturnItemRow($id);
        print($rows);
    }

    public function createTextArReturn($id){
        $arreturn = new ArReturn($id);
        if ($arreturn <> null){
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $arreturn->CompanyName);
            fwrite($myfile, "\n".'FAKTUR PENJUALAN');

            fclose($myfile);
        }
    }

    public function getjson_returnlists($cabangId,$customerId){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $arreturn = new ArReturn();
        $retlists = $arreturn->GetJSonArReturns($cabangId,$customerId,$filter);
        echo json_encode($retlists);
    }

    public function approve() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di approve !");
            redirect_url("ar.arreturn");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $return = new ArReturn();
            $log = new UserAdmin();
            $return = $return->FindById($id);
            /** @var $return ArReturn */
            // process retur
            if($return->RjStatus == 1){
                if ($return->RjAmount > 0) {
                    $rs = $return->Approve($return->Id,$uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'ar.arreturn', 'Approve Return', $return->RjNo, 'Success');
                        $infos[] = sprintf("Data Retur No.: '%s' (%s) telah berhasil di-approve.", $return->RjNo, $return->RjDescs);
                    }
                }else{
                    $errors[] = sprintf("Detail Data Retur No.%s belum diisi !",$return->RjNo);
                }
            }else{
                $errors[] = sprintf("Data Retur No.%s sudah berstatus -Approved- !",$return->RjNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.arreturn");
    }

    public function unapprove() {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di batalkan !");
            redirect_url("ar.arreturn");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $return = new ArReturn();
            $log = new UserAdmin();
            $return = $return->FindById($id);
            /** @var $return ArReturn */
            // process retur
            if($return->RjStatus == 2){
                $rs = $return->Unapprove($return->Id,$uid);
                if ($rs) {
                    $log = $log->UserActivityWriter($this->userCabangId, 'ar.arreturn', 'Unapprove Return', $return->RjNo, 'Success');
                    $infos[] = sprintf("Data Retur No.: '%s' (%s) telah berhasil di-batalkan.", $return->RjNo, $return->RjDescs);
                }
            }else{
                $errors[] = sprintf("Data Retur No.%s masih berstatus -Posted- !",$return->RjNo);
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("ar.arreturn");
    }

    public function ArReturnItemsCount($id){
        $arreturn = new ArReturn();
        $rows = $arreturn->GetArReturnItemRow($id);
        return $rows;
    }

    public function getjson_invoicelists($gudangId,$customerId){
        require_once (MODEL . "ar/invoice.php");
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $invoices = new Invoice();
        $invlists = $invoices->GetJSonInvoicesByGudang($gudangId,$customerId,$filter);
        echo json_encode($invlists);
    }

    public function getjson_invoiceitems($invoiceId = 0){
        require_once (MODEL . "ar/invoice.php");
        $invoices = new Invoice();
        $itemlists = $invoices->GetJSonInvoiceItems($invoiceId);
        echo json_encode($itemlists);
    }
}


// End of File: estimasi_controller.php
