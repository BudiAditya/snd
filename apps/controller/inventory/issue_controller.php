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
        $settings["columns"][] = array("name" => "c.wh_code", "display" => "Gudang", "width" => 70);
        $settings["columns"][] = array("name" => "a.issue_date", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.issue_no", "display" => "No.Issue", "width" => 100);
        $settings["columns"][] = array("name" => "b.item_code", "display" => "Kode Barang", "width" => 70);
        $settings["columns"][] = array("name" => "b.item_name", "display" => "Nama Barang", "width" => 200);
        $settings["columns"][] = array("name" => "b.s_uom_code", "display" => "Satuan", "width" => 40);
        $settings["columns"][] = array("name" => "format(a.qty,0)", "display" => "QTY", "width" => 40, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.price,0)", "display" => "Harga", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.qty * a.price,0)", "display" => "Biaya", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.keterangan", "display" => "Keterangan", "width" => 350);
        $settings["columns"][] = array("name" => "if(a.is_status = 0,'Draft','Posted')", "display" => "Status", "width" => 40);

        $settings["filters"][] = array("name" => "b.item_code", "display" => "Kode Barang");
        $settings["filters"][] = array("name" => "c.wh_code", "display" => "Gudang");
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
            $settings["from"] = "t_ic_issue AS a Join m_items AS b ON a.item_id = b.id Join m_warehouse AS c ON a.warehouse_id = c.id";
            $settings["where"] = "a.cabang_id = ".$this->userCabangId." And Year(a.issue_date) = ".$this->userAccYear;
            $settings["order_by"] = "a.issue_date,c.wh_code, b.item_code";
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }

    private function ValidateData(Issue $issue) {
        if ($issue->CorrQty == 0 || $issue->CorrQty == null || $issue->CorrQty == ''){
            $this->Set("error", "Koreksi Stock tidak valid (Koreksi = 0)!");
            return false;
        }else {
            return true;
        }
    }

    public function add() {
        require_once (MODEL . "master/warehouse.php");
        $issue = new Issue();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $issue->WarehouseId = $this->GetPostValue("WarehouseId");
            $issue->CorrDate = $this->GetPostValue("CorrDate");
            $issue->ItemId = $this->GetPostValue("ItemId");
            $issue->SysQty = $this->GetPostValue("SysQty");
            $issue->WhsQty = $this->GetPostValue("WhsQty");
            $issue->CorrQty = $this->GetPostValue("CorrQty");
            $issue->CorrReason = 'Selisih Stock';
            $issue->CorrStatus = 0;
            $issue->CorrAmt = 0;
            if ($this->ValidateData($issue)) {
                $issue->CreatebyId = $this->userUid;
                $issue->CorrNo = $issue->GetIssueDocNo($this->userCabangId);
                $rs = $issue->Insert();
                if ($rs > 0) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Add New Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->SysQty,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Stock Issue: %s (%s) sudah berhasil disimpan", $issue->ItemId, $issue->SysQty));
                    redirect_url("inventory.issue");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Add New Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->SysQty,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new Warehouse();
        $whs  = $loader->LoadByCabangId($this->userCabangId);
        $this->Set("whs", $whs);
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
        $rs = $issue->Delete($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Delete Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->SysQty,'-','Success');
            $this->persistence->SaveState("info", sprintf("Stock Issue Barang: %s (%s) sudah dihapus", $issue->ItemId, $issue->SysQty));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.issue','Delete Item Stock Issue -> Stock Issue: '.$issue->ItemId.' - '.$issue->SysQty,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $issue->ItemId, $issue->SysQty, $this->connector->GetErrorMessage()));
        }
        redirect_url("inventory.issue");
    }
}

// End of file: issue_controller.php
