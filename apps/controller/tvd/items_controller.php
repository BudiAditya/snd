<?php

class ItemsController extends AppController {
	private $userUid;
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;

	protected function Initialize() {
		require_once(MODEL . "inventory/items.php");
        require_once(MODEL . "master/user_admin.php");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();
		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        //$settings["columns"][] = array("name" => "a.entity_code", "display" => "Entitas", "width" => 50);
        //$settings["columns"][] = array("name" => "a.brand_name", "display" => "Brand", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 80);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 250);
        //$settings["columns"][] = array("name" => "format(a.l_uom_qty,0)", "display" => "Isi", "width" => 20,"align" => "right");
        $settings["columns"][] = array("name" => "a.l_uom_code", "display" => "Sat Besar", "width" => 50);
        //$settings["columns"][] = array("name" => "format(a.m_uom_qty,0)", "display" => "Isi", "width" => 20,"align" => "right");
        //$settings["columns"][] = array("name" => "a.m_uom_code", "display" => "Sedang", "width" => 50);
        $settings["columns"][] = array("name" => "format(a.s_uom_qty,0)", "display" => "Isi", "width" => 20,"align" => "right");
        $settings["columns"][] = array("name" => "a.s_uom_code", "display" => "Sat Kecil", "width" => 50);
        $settings["columns"][] = array("name" => "format(a.qty_convert,1)", "display" => "Isi", "width" => 30,"align" => "right");
        $settings["columns"][] = array("name" => "a.c_uom_code", "display" => "Volume", "width" => 50);
        $settings["columns"][] = array("name" => "if(a.is_aktif = 1,'Aktif','Tidak')", "display" => "Is Aktif", "width" => 50);
        $settings["columns"][] = array("name" => "a.subcategory_name", "display" => "Category Produk", "width" => 150);
        //$settings["columns"][] = array("name" => "a.principal_name", "display" => "Principal", "width" => 200);

		$settings["filters"][] = array("name" => "a.item_name", "display" => "Nama Barang");
        $settings["filters"][] = array("name" => "a.item_code", "display" => "Kode Barang");
        $settings["filters"][] = array("name" => "if(a.is_aktif = 1,'Aktif','Tidak')", "display" => "Status Aktif");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Barang Castrol";
            /*
			if ($acl->CheckUserAccess("inventory.items", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.items/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.items", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.items/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih items terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu items.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.items", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.items/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih items terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu items.",
					"Confirm" => "Apakah anda mau menghapus data items yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}
            */
			$settings["def_order"] = 3;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_ic_items AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 AND a.is_aktif = 1 And a.brand_id = 1";
            } else {
                $settings["where"] = "a.is_deleted = 0 And a.brand_id = 1";
            }
            $settings["order by"] = "a.item_code";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}
}

// End of file: items_controller.php
