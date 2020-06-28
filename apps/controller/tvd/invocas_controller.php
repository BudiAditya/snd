<?php
class InvocasController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userUid;
    private $userCabIds;

    protected function Initialize() {
        require_once(MODEL . "tvd/invocas.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
    }

    public function index() {
        $router = Router::GetInstance();
        $settings = array();
        $settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 40);
        //$settings["columns"][] = array("name" => "a.entity_cd", "display" => "Entity", "width" => 30);
        $settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.invoice_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.invoice_no", "display" => "No. Invoice", "width" => 100);
        $settings["columns"][] = array("name" => "a.customer_code", "display" => "Kode", "width" => 50);
        $settings["columns"][] = array("name" => "a.customer_name", "display" => "Nama Customer", "width" => 200);
        $settings["columns"][] = array("name" => "format(a.base_amount,0)", "display" => "Jumlah", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.disc_amount,0)", "display" => "Diskon", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.base_amount - a.disc_amount,0)", "display" => "DPP", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.ppn_amount,0)", "display" => "PPN", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.total_amount,0)", "display" => "Total", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "a.sales_name", "display" => "Salesman", "width" => 100);

        $settings["filters"][] = array("name" => "a.invoice_no", "display" => "No. Invoice");
        $settings["filters"][] = array("name" => "a.invoice_date", "display" => "Tanggal");
        $settings["filters"][] = array("name" => "a.customer_name", "display" => "Nama Customer");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Kode Cabang");

        $settings["def_filter"] = 0;
        $settings["def_invoice"] = 3;
        $settings["def_direction"] = "asc";
        $settings["singleSelect"] = false;

        if (!$router->IsAjaxRequest) {
            $acl = AclManager::GetInstance();
            $settings["title"] = "Daftar Invoice Penjualan Castrol";

            if ($acl->CheckUserAccess("tvd.invocas", "add")) {
                $settings["actions"][] = array("Text" => "<b>Add</b>", "Url" => "tvd.invocas/add/0", "Class" => "bt_add", "ReqId" => 0);
            }

            if ($acl->CheckUserAccess("tvd.invocas", "edit")) {
                $settings["actions"][] = array("Text" => "<b>Edit</b>", "Url" => "tvd.invocas/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu sebelum proses edit.\nPERHATIAN: Pilih tepat 1 data rekonsil",
                    "Confirm" => "");
            }
            if ($acl->CheckUserAccess("tvd.invocas", "delete")) {
                $settings["actions"][] = array("Text" => "<b>Void</b>", "Url" => "tvd.invocas/void/%s", "Class" => "bt_delete", "ReqId" => 1);
            }
            if ($acl->CheckUserAccess("tvd.invocas", "view")) {
                $settings["actions"][] = array("Text" => "<b>View</b>", "Url" => "tvd.invocas/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Maaf anda harus memilih Data Invoice terlebih dahulu.\nPERHATIAN: Pilih tepat 1 data rekonsil","Confirm" => "");
            }
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("tvd.invocas", "print")) {
                $settings["actions"][] = array("Text" => "Print Invoice", "Url" => "tvd.invocas/printout/invoice","Class" => "bt_print", "Target" => "_blank", "ReqId" => 2, "Confirm" => "Pastikan Data sudah di-approve\nCetak Invoice yang dipilih?");
                //$settings["actions"][] = array("Text" => "Print Surat Jalan", "Url" => "tvd.invocas/printout/suratjalan","Class" => "bt_print", "Target" => "_blank", "ReqId" => 2, "Confirm" => "Pastikan Data sudah di-approve\nCetak Surat Jalan yang dipilih?");
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("tvd.invocas", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "tvd.invocas/report", "Class" => "bt_report", "ReqId" => 0);
            }
        } else {
            $settings["from"] = "vw_cas_invoice_master AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId ." And year(a.invoice_date) = ".$this->trxYear." And month(a.invoice_date) = ".$this->trxMonth;
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.cabang_id = " . $this->userCabangId;
            }
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    public function add(){
        require_once (MODEL . "tvd/invocas.php");
        //proses rekap dll
        if (count($this->postData) > 0) {
            $tahun = $this->GetPostValue("Tahun");
            $bulan = $this->GetPostValue("Bulan");
            $output = $this->GetPostValue("Output");
        }else{
            $tahun = $this->trxYear;
            $bulan = $this->trxMonth;
            $output = 0;
        }
        $loader = new Invocas();
        $invoice= $loader->LoadInvoiceSourceByMonth($tahun,$bulan);
        $this->Set("tahun", $tahun);
        $this->Set("bulan", $bulan);
        $this->Set("output", $output);
        $this->Set("invoices", $invoice);
    }

    public function create(){
        $invocas = new Invocas();
        $ids = $this->GetPostValue("ids", array());
        foreach ($ids as $detailId){
            $result  = $invocas->CreateDetail($detailId);
        }
        redirect_url("tvd.invocas");
    }
}


// End of File: estimasi_controller.php
