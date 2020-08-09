<?php
class JournalController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userAccMonth;
    private $userAccYear;
    private $userLevel;

    protected function Initialize() {
        require_once(MODEL . "accounting/journal.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userAccMonth = $this->persistence->LoadState("acc_month");
        $this->userAccYear = $this->persistence->LoadState("acc_year");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        $settings["columns"][] = array("name" => "a.journal_no", "display" => "No. Journal", "width" => 80);
        $settings["columns"][] = array("name" => "a.journal_date", "display" => "Tanggal", "width" => 70);
        $settings["columns"][] = array("name" => "a.journal_descs", "display" => "Keterangan", "width" => 300);
        $settings["columns"][] = array("name" => "a.reff_no", "display" => "No. Refferensi", "width" => 100);
        $settings["columns"][] = array("name" => "format(a.dbAmount,0)", "display" => "Debit", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.crAmount,0)", "display" => "Credit", "width" => 100, "align" => "right");
        $settings["columns"][] = array("name" => "if(a.journal_status = 0,'Draft',if(a.journal_status = 1, 'Verified','Approved'))", "display" => "Status", "width" => 50);
        $settings["columns"][] = array("name" => "if(a.input_mode = 0,'Manual','Auto')", "display" => "Mode", "width" => 50);
        $settings["columns"][] = array("name" => "a.source_code", "display" => "Sumber", "width" => 50);

        $settings["filters"][] = array("name" => "a.journal_no", "display" => "No. Journal");
        $settings["filters"][] = array("name" => "a.journal_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.journal_descs", "display" => "Keterangan");
        $settings["filters"][] = array("name" => "a.trx_code", "display" => "Jenis Journal");
        $settings["filters"][] = array("name" => "if(a.journal_status = 0,'Draft',if(a.journal_status = 1, 'Verified','Approved'))", "display" => "Status");
        $settings["filters"][] = array("name" => "if(a.input_mode = 0,'Manual','Auto')", "display" => "Mode");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 2;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Journal Akuntansi";

            if ($acl->CheckUserAccess("accounting.journal", "add")) {
                $settings["actions"][] = array("Text" => "Add", "Url" => "accounting.journal/add", "Class" => "bt_add", "ReqId" => 0);
            }
            if ($acl->CheckUserAccess("accounting.journal", "edit")) {
                $settings["actions"][] = array("Text" => "Edit", "Url" => "accounting.journal/add/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Journal terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("accounting.journal", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "accounting.journal/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Journal terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            if ($acl->CheckUserAccess("accounting.journal", "delete")) {
                $settings["actions"][] = array("Text" => "Delete", "Url" => "accounting.journal/delete/%s", "Class" => "bt_delete", "ReqId" => 1);
            }

            if ($acl->CheckUserAccess("accounting.journal", "verify")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Verifikasi", "Url" => "accounting.journal/verify/1", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Journal terlebih dahulu sebelum proses approval.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda menyetujui data journal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Verifikasi", "Url" => "accounting.journal/verify/0", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Journal terlebih dahulu sebelum proses pembatalan.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda mau membatalkan approval data journal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Proses Verifikasi", "Url" => "accounting.journal/verifikasi", "Class" => "bt_approve", "ReqId" => 0);
            }

            if ($acl->CheckUserAccess("accounting.journal", "approve")) {
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Approve", "Url" => "accounting.journal/approve/1", "Class" => "bt_approve", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Journal terlebih dahulu sebelum proses approval.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda menyetujui data journal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "Batal Approve", "Url" => "accounting.journal/approve/0", "Class" => "bt_reject", "ReqId" => 2,
                    "Error" => "Mohon memilih Data Journal terlebih dahulu sebelum proses pembatalan.\nPERHATIAN: Mohon memilih tepat satu data.",
                    "Confirm" => "Apakah anda mau membatalkan approval data journal yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Proses Approval", "Url" => "accounting.journal/approval", "Class" => "bt_approve", "ReqId" => 0);
            }

        } else {
            $settings["from"] = "vw_ac_journal_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "(month(a.journal_date) = ".$this->userAccMonth." And year(a.journal_date) = ".$this->userAccYear.") And journal_status < 3 And a.company_id = ".$this->userCompanyId;
            } else {
                $settings["where"] = "a.company_id = ".$this->userCompanyId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    public function add($journalId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/department.php");
        require_once(MODEL . "ar/customer.php");
        require_once(MODEL . "ap/supplier.php");
        require_once(MODEL . "master/karyawan.php");
        require_once(MODEL . "master/coadetail.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $journal = new Journal();
        $log = new UserAdmin();
        if ($journalId > 0) {
            $journal = $journal->LoadById($journalId);
            if($journal == null){
                $this->persistence->SaveState("error", "Maaf Data Journal dimaksud tidak ada pada database. Mungkin sudah dihapus!");
                redirect_url("accounting.journal");
            }
            if ($journal->JournalStatus == 1){
                $this->persistence->SaveState("error", "Maaf Data Journal ini berstatus -VERIFIED-!");
                redirect_url("accounting.journal");
            }
            if ($journal->JournalStatus == 2){
                $this->persistence->SaveState("error", "Maaf Data Journal ini berstatus -APPROVED-!");
                redirect_url("accounting.journal");
            }
            if ($journal->JournalStatus == 3){
                $this->persistence->SaveState("error", "Maaf Data Journal ini berstatus -VOID-!");
                redirect_url("accounting.journal");
            }
        }
        // load details
        $journal->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabangs = $loader->LoadByCompanyId($this->userCompanyId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("cabList", $cabangs);
        $this->Set("journal", $journal);
        $this->Set("acl", $acl);
        //load trx code
        $loader = new Journal();
        $trxtypes = $loader->LoadTrxType();
        $this->Set("trxtypes", $trxtypes);
        //load depts
        $loader = new Department();
        $depts = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("depts", $depts);
        //load customer
        $loader = new Customer();
        $custs = $loader->LoadAll();
        $this->Set("custs", $custs);
        //load supplier
        $loader = new Supplier();
        $supps = $loader->LoadAll();
        $this->Set("supps", $supps);
        //load employee
        $loader = new Karyawan();
        $karyas = $loader->LoadByCompanyId($this->userCompanyId);
        $this->Set("karyas", $karyas);
        //load coa
        $loader = new CoaDetail();
        $coas = $loader->LoadAll($this->userCompanyId);
        $this->Set("coas", $coas);
    }

    public function proses_master($journalId = 0){
        $journal = new Journal();
        $log = new UserAdmin();
        $journal->CompanyId = $this->userCompanyId;
        if (count($this->postData) > 0) {
            $journal->Id = $journalId;
            $journal->CabangId = $this->userCabangId;
            $journal->JournalDate = date('Y-m-d',strtotime($this->GetPostValue("JournalDate")));
            $journal->JournalNo = $this->GetPostValue("JournalNo");
            $journal->JournalDescs = $this->GetPostValue("JournalDescs");
            $journal->TrxCode = $this->GetPostValue("TrxCode");
            $journal->ReffNo = $this->GetPostValue("ReffNo");
            if ($this->GetPostValue("JournalStatus") == null){
                $journal->JournalStatus = 0;
            }else{
                $journal->JournalStatus = $this->GetPostValue("JournalStatus");
            }
            if ($journal->Id == 0) {
                $journal->CreatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $journal->JournalNo = $journal->GetJournalDocNo();
                $rs = $journal->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Add New Journal',$journal->JournalNo,'Success');
                    printf("OK|A|%d|%s",$journal->Id,$journal->JournalNo);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Add New Journal',$journal->JournalNo,'Failed');
                    printf("ER|A|%d",$journal->Id);
                }
            }else{
                $journal->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $journal->Update($journal->Id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Update Journal',$journal->JournalNo,'Success');
                    printf("OK|U|%d|%s",$journal->Id,$journal->JournalNo);
                }else{
                    $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Update Invoice',$journal->JournalNo,'Failed');
                    printf("ER|U|%d",$journal->Id);
                }
            }
        }else{
            printf("ER|X|%d",$journalId);
        }
    }

    public function view($journalId = null) {
        require_once(MODEL . "master/cabang.php");
        $acl = AclManager::GetInstance();
        $loader = null;
        $journal = new Journal();
        $journal = $journal->LoadById($journalId);
        if($journal == null){
            $this->persistence->SaveState("error", "Maaf Data Journal dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("accounting.journal");
        }
        // load details
        $journal->LoadDetails();
        //load data cabang
        $loader = new Cabang();
        $cabCode = null;
        $cabName = null;
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("journal", $journal);
        $this->Set("acl", $acl);
        //load trx code
        $loader = new Journal();
        $trxtypes = $loader->LoadTrxType();
        $this->Set("trxtypes", $trxtypes);
    }

    public function delete($journalId) {
        // Cek datanya
        $log = new UserAdmin();
        $journal = new Journal();
        $journal = $journal->FindById($journalId);
        if($journal == null){
            $this->persistence->SaveState("error", "Maaf Data Journal dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("accounting.journal");
        }
        /** @var $journal Journal */
        if ($journal->JournalStatus > 0){
            $this->persistence->SaveState("error", "Maaf Data Journal bukan berstatus -DRAFT- (Tidak boleh dihapus)!");
            redirect_url("accounting.journal");
        }
        if ($journal->Delete($journalId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Delete Journal',$journal->JournalNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Journal No: %s sudah berhasil dihapus", $journal->JournalNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Delete Journal',$journal->JournalNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Journal No: %s gagal dihapus", $journal->JournalNo));
        }
        redirect_url("accounting.journal");
    }

    public function void($journalId) {
        // Cek datanya
        $log = new UserAdmin();
        $journal = new Journal();
        $journal = $journal->FindById($journalId);
        if($journal == null){
            $this->Set("error", "Maaf Data Journal dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("accounting.journal");
        }
        /** @var $journal Journal */
        if ($journal->JournalStatus == 3){
            $this->Set("error", "Maaf Data Journal sudah berstatus -VOID-!");
            redirect_url("accounting.journal");
        }
        if ($journal->Void($journalId) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Delete Journal',$journal->JournalNo,'Success');
            $this->persistence->SaveState("info", sprintf("Data Journal No: %s sudah berhasil dibatalkan", $journal->JournalNo));
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Delete Journal',$journal->JournalNo,'Failed');
            $this->persistence->SaveState("error", sprintf("Maaf, Data Journal No: %s gagal dibatalkan", $journal->JournalNo));
        }
        redirect_url("accounting.journal");
    }

    public function add_detail($journalId = null) {
        $journal = new Journal($journalId);
        $jdetail = new JournalDetail();
        $log = new UserAdmin();
        $jdetail->JournalId = $journalId;
        $items = null;
        if (count($this->postData) > 0) {
            $jdetail->AccId = $this->GetPostValue("aAccId");
            $jdetail->Keterangan = $this->GetPostValue("aKeterangan");
            $jdetail->DbAmount = $this->GetPostValue("aDbAmount");
            $jdetail->CrAmount = $this->GetPostValue("aCrAmount");
            $jdetail->CabangId = $this->GetPostValue("aCabangId");
            $jdetail->DeptId = $this->GetPostValue("aDeptId");
            $jdetail->CustomerId = $this->GetPostValue("aCustomerId");
            $jdetail->SupplierId = $this->GetPostValue("aSupplierId");
            $jdetail->EmployeeId = $this->GetPostValue("aEmployeeId");
            // insert ke table
            $rs = $jdetail->Insert()== 1;
            if ($rs > 0) {
                $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Add Journal detail -> Acc No: '.$jdetail->AccCode,$jdetail->JournalId,'Success');
                echo json_encode(array());
            } else {
                $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Add Journal detail -> Acc No: '.$jdetail->AccCode,$jdetail->JournalId,'Failed');
                echo json_encode(array('errorMsg'=>'Some errors occured.'));
            }
        }
    }

    public function delete_detail($id) {
        // Cek datanya
        $log = new UserAdmin();
        $jdetail = new JournalDetail();
        $jdetail = $jdetail->FindById($id);
        if ($jdetail == null) {
            print("Data tidak ditemukan..");
            return;
        }
        if ($jdetail->Delete($id) == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Delete Journal detail -> Acc No: '.$jdetail->AccCode,$jdetail->JournalId,'Success');
            printf("Data Detail ID: %d berhasil dihapus!",$id);
        }else{
            $log = $log->UserActivityWriter($this->userCabangId,'accounting.journal','Delete Journal detail -> Acc No: '.$jdetail->AccCode,$jdetail->JournalId,'Success');
            printf("Maaf, Data Detail ID: %d gagal dihapus!",$id);
        }
    }

    public function verify($vMode = 1) {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di verifikasi !");
            redirect_url("accounting.journal");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $journal = new Journal();
            $journal = $journal->FindById($id);
            /** @var $journal Journal */
            // process jurnal
            if ($vMode == 1) {
                if ($journal->JournalStatus == 0) {
                    $rs = $journal->Verify($journal->Id, $vMode, $uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Verify Jurnal', $journal->JournalNo, 'Success');
                        $infos[] = sprintf("Data Jurnal No.: '%s' (%s) telah berhasil di-Verifikasi.", $journal->JournalNo, $journal->JournalDescs);
                    } else {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Verify Jurnal', $journal->JournalNo, 'Failed');
                        $errors[] = sprintf("Maaf, Gagal proses verifikasi Data Jurnal: '%s'. Message: %s", $journal->JournalNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    $errors[] = sprintf("Data Jurnal No.%s bukan berstatus -Draft- !", $journal->JournalNo);
                }
            }else{
                if ($journal->JournalStatus == 1) {
                    $rs = $journal->Verify($journal->Id, $vMode, $uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Unverify Jurnal', $journal->JournalNo, 'Success');
                        $infos[] = sprintf("Data Jurnal No.: '%s' (%s) telah berhasil di-Unverifikasi.", $journal->JournalNo, $journal->JournalDescs);
                    } else {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Unverify Jurnal', $journal->JournalNo, 'Failed');
                        $errors[] = sprintf("Maaf, Gagal proses unverifikasi Data Jurnal: '%s'. Message: %s", $journal->JournalNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    $errors[] = sprintf("Data Jurnal No.%s bukan berstatus -Verify- !", $journal->JournalNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("accounting.journal");
    }

    public function approve($vMode = 1) {
        $ids = $this->GetGetValue("id", array());
        if (count($ids) == 0) {
            $this->persistence->SaveState("error", "Maaf anda belum memilih data yang akan di verifikasi !");
            redirect_url("accounting.journal");
            return;
        }
        $uid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $infos = array();
        $errors = array();
        foreach ($ids as $id) {
            $log = new UserAdmin();
            $journal = new Journal();
            $journal = $journal->FindById($id);
            /** @var $journal Journal */
            // process jurnal
            if ($vMode == 1) {
                if ($journal->JournalStatus == 1) {
                    $rs = $journal->Approve($journal->Id, $vMode, $uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Approval Jurnal', $journal->JournalNo, 'Success');
                        $infos[] = sprintf("Data Jurnal No.: '%s' (%s) telah berhasil di-Approve.", $journal->JournalNo, $journal->JournalDescs);
                    } else {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Approval Jurnal', $journal->JournalNo, 'Failed');
                        $errors[] = sprintf("Maaf, Gagal proses verifikasi Data Jurnal: '%s'. Message: %s", $journal->JournalNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    $errors[] = sprintf("Data Jurnal No.%s bukan berstatus -Verify- !", $journal->JournalNo);
                }
            }else{
                if ($journal->JournalStatus == 2) {
                    $rs = $journal->Approve($journal->Id, $vMode, $uid);
                    if ($rs) {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Unapprove Jurnal', $journal->JournalNo, 'Success');
                        $infos[] = sprintf("Data Jurnal No.: '%s' (%s) telah berhasil di-Unapprove.", $journal->JournalNo, $journal->JournalDescs);
                    } else {
                        $log = $log->UserActivityWriter($this->userCabangId, 'accounting.journal', 'Unapprove Jurnal', $journal->JournalNo, 'Failed');
                        $errors[] = sprintf("Maaf, Gagal proses Unapprove Data Jurnal: '%s'. Message: %s", $journal->JournalNo, $this->connector->GetErrorMessage());
                    }
                } else {
                    $errors[] = sprintf("Data Jurnal No.%s bukan berstatus -Approved- !", $journal->JournalNo);
                }
            }
        }
        if (count($infos) > 0) {
            $this->persistence->SaveState("info", "<ul><li>" . implode("</li><li>", $infos) . "</li></ul>");
        }
        if (count($errors) > 0) {
            $this->persistence->SaveState("error", "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
        }
        redirect_url("accounting.journal");
    }

    public function verifikasi(){
        if (count($this->postData) > 0) {
            $sdate = strtotime($this->GetPostValue("stDate"));
            $edate = strtotime($this->GetPostValue("enDate"));
            $tsts = $this->GetPostValue("tStatus");
        }else{
            $sdate = time();
            $edate = $sdate;
            $tsts = 0;
        }
        $loader = new Journal();
        $trxs = $loader->LoadJournal4Approval($this->userCabangId,$sdate,$edate,$tsts);
        $this->Set("stDate", $sdate);
        $this->Set("enDate", $edate);
        $this->Set("tStatus", $tsts);
        $this->Set("trxs", $trxs);
    }

    public function approval(){
        if (count($this->postData) > 0) {
            $sdate = strtotime($this->GetPostValue("stDate"));
            $edate = strtotime($this->GetPostValue("enDate"));
            $tsts = $this->GetPostValue("tStatus");
        }else{
            $sdate = time();
            $edate = $sdate;
            $tsts = 1;
        }
        $loader = new Journal();
        $trxs = $loader->LoadJournal4Approval($this->userCabangId,$sdate,$edate,$tsts);
        $this->Set("stDate", $sdate);
        $this->Set("enDate", $edate);
        $this->Set("tStatus", $tsts);
        $this->Set("trxs", $trxs);
    }
}


// End of File: estimasi_controller.php
