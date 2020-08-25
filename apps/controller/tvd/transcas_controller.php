<?php
class TranscasController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;

    protected function Initialize() {
        require_once(MODEL . "tvd/transcas.php");
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
        $settings["columns"][] = array("name" => "if(a.npb_status = 0,'Draft','Posted')", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "a.npb_no", "display" => "No. NPB");
        $settings["filters"][] = array("name" => "a.npb_descs", "display" => "Keterangan");
        $settings["filters"][] = array("name" => "a.npb_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "if(a.npb_status = 0,'Draft','Posted')", "display" => "Status");

        $settings["def_filter"] = 0;
        $settings["def_order"] = 1;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = true;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Transfer Stock Castrol";

            if ($acl->CheckUserAccess("tvd.transcas", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "tvd.transcas/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Transfer terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("tvd.transcas", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "tvd.transcas/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_cas_ic_transfer_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And year(a.npb_date) = ".$this->trxYear;//." And month(a.npb_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0";
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

	public function view($transferId = null) {
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        $loader = null;
        $transfer = new Transcas();
        $transfer = $transfer->LoadById($transferId);
        if($transfer == null){
            $this->persistence->SaveState("error", "Maaf Data Transfer dimaksud tidak ada pada database. Mungkin sudah dihapus!");
            redirect_url("tvd.transcas");
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
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sJnsLaporan = $this->GetPostValue("JnsLaporan");
            $sOutput = $this->GetPostValue("Output");
            // ambil data yang diperlukan
            $transfer = new Transcas();
            if ($sJnsLaporan == 1) {
                $reports = $transfer->Load4Reports($this->userCabangId, $sGudangId, $sStartDate, $sEndDate);
            }else{
                $reports = $transfer->LoadRekap4Reports($this->userCabangId, $sGudangId, $sStartDate, $sEndDate);
            }
        }else{
            $sCabangId = 0;
            $sGudangId = 0;
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
