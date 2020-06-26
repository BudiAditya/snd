<?php

/**
 * Dipake untuk membuat laporan stock pergudang atau overview
 * By Default ini hanya bisa melihat stock per Company dan tidak bisa semua Company. Jadi Corporate user harus impersonate
 *
 * NOTE:
 *  - Berhubung query yang dipake disini aneh-aneh maka saya prefer tidak pakai model tetapi langsung query.
 */
class StockController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $trxYear;
    private $trxMonth;
    private $userLevel;
    private $userUid;
    private $userCabIds;

	protected function Initialize() {
		//require_once(MODEL . "tvd/stock.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
	}

    public function index() {
        $router = Router::GetInstance();
        $settings = array();

        $settings["columns"][] = array("name" => "concat(a.warehouse_id,'|',a.item_id)", "display" => "ID", "width" => 50);
        $settings["columns"][] = array("name" => "a.wh_code", "display" => "Gudang", "width" => 50);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Produk", "width" =>250);
        $settings["columns"][] = array("name" => "a.s_uom_code", "display" => "Satuan", "width" =>50);
        $settings["columns"][] = array("name" => "format(a.op_qty,0)", "display" => "Awal", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.in_qty,0)", "display" => "Masuk", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.ot_qty,0)", "display" => "Keluar", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.aj_qty,0)", "display" => "Koreksi", "width" => 50, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.cl_qty,0)", "display" => "Stock", "width" => 50, "align" => "right");

        $settings["filters"][] = array("name" => "a.item_code", "display" => "Item Code");
        $settings["filters"][] = array("name" => "a.item_name", "display" => "Item Name");
        $settings["filters"][] = array("name" => "a.wh_code", "display" => "Gudang");

        if (!$router->IsAjaxRequest) {
            // UI Settings
            $acl = AclManager::GetInstance();
            $settings["title"] = "Stock Produk Castrol";
            if ($acl->CheckUserAccess("inventory.stock", "view")) {
                $settings["actions"][] = array("Text" => "Kartu Stock", "Url" => "inventory.stock/card/%s", "Class" => "bt_view", "ReqId" => 1,"Confirm" => "");
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan Stock Per Periode", "Url" => "inventory.stock/stkdetail", "Class" => "bt_report", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Laporan Stock Terakhir", "Url" => "inventory.stock/report", "Class" => "bt_report", "ReqId" => 0);
            }

            $settings["def_filter"] = 1;
            $settings["def_order"] = 2;
            $settings["singleSelect"] = true;
        } else {
            $settings["from"] = "vw_cas_item_stock AS a";
            $settings["where"] = "a.trx_year = ".$this->trxYear;
        }

        $dispatcher = Dispatcher::CreateInstance();
        $dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
    }
}


// End of File: stock_controller.php
