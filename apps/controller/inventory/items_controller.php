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
        $settings["columns"][] = array("name" => "a.entity_code", "display" => "Entitas", "width" => 50);
        $settings["columns"][] = array("name" => "a.brand_name", "display" => "Brand", "width" => 80);
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
        $settings["columns"][] = array("name" => "a.subcategory_name", "display" => "Category", "width" => 150);
        $settings["columns"][] = array("name" => "a.principal_name", "display" => "Principal", "width" => 200);
        $settings["columns"][] = array("name" => "a.old_code", "display" => "P I C", "width" => 100);

		$settings["filters"][] = array("name" => "a.item_name", "display" => "Nama Barang");
        $settings["filters"][] = array("name" => "a.item_code", "display" => "Kode Barang");
        $settings["filters"][] = array("name" => "a.old_code", "display" => "Item Code");
        $settings["filters"][] = array("name" => "a.subcategory_name", "display" => "Category");
        $settings["filters"][] = array("name" => "a.brand_name", "display" => "Brand");
        $settings["filters"][] = array("name" => "if(a.is_aktif = 1,'Aktif','Tidak')", "display" => "Status Aktif");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Barang";

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
			/*
            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("inventory.items", "add")) {
                $settings["actions"][] = array("Text" => "Upload Daftar Barang", "Url" => "inventory.items/upload", "Class" => "bt_excel", "ReqId" => 0);
            }

            $settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("inventory.items", "view")) {
                $settings["actions"][] = array("Text" => "Daftar Barang (Aktif)", "Url" => "inventory.items/items_list/xls/1", "Class" => "bt_excel", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Daftar Barang (Non-Aktif)", "Url" => "inventory.items/items_list/xls/0", "Class" => "bt_excel", "ReqId" => 0);
                $settings["actions"][] = array("Text" => "separator", "Url" => null);
                $settings["actions"][] = array("Text" => "Daftar Barang (All)", "Url" => "inventory.items/items_list/xls/-1", "Class" => "bt_excel", "ReqId" => 0);
            }
            */
			$settings["def_order"] = 3;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_ic_items AS a";
            if ($_GET["query"] == "") {
                $_GET["query"] = null;
                $settings["where"] = "a.is_deleted = 0 AND a.is_aktif = 1";
            } else {
                $settings["where"] = "a.is_deleted = 0";
            }
            $settings["order by"] = "a.item_code";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(Items $items) {
		return true;
	}

	public function add() {
        require_once(MODEL . "ap/supplier.php");
        require_once(MODEL . "inventory/itembrand.php");
        require_once(MODEL . "inventory/itemsubcategory.php");
        require_once(MODEL . "inventory/itemuom.php");
        require_once(MODEL . "master/cabang.php");
        $log = new UserAdmin();
        $items = new Items();
        $loader = null;
        if (count($this->postData) > 0) {
            $items->ItemCode = $this->GetPostValue("ItemCode");
            $items->OldCode = $this->GetPostValue("OldCode");
            $items->ItemName = $this->GetPostValue("ItemName");
            $items->SubCategoryId = $this->GetPostValue("SubCategoryId");
            //$items->BarCode = $this->GetPostValue("BarCode");
            $items->PrincipalId = $this->GetPostValue("PrincipalId");
            $items->BrandId = $this->GetPostValue("BrandId");
            $items->LuomCode = $this->GetPostValue("LuomCode");
            $items->LuomQty = $this->GetPostValue("LuomQty");
            $items->SuomCode = $this->GetPostValue("SuomCode");
            $items->SuomQty = $this->GetPostValue("SuomQty");
            //$items->IsConvert = $this->GetPostValue("IsConvert");
            $items->QtyConvert = $this->GetPostValue("QtyConvert");
            $items->CuomCode = $this->GetPostValue("CuomCode");
            //$items->MaxStock = $this->GetPostValue("MaxStock");
            $items->MinStock = $this->GetPostValue("MinStock");
            //$items->IsPurchase = $this->GetPostValue("IsPurchase");
            //$items->IsSale = $this->GetPostValue("IsSale");
            //$items->IsStock = $this->GetPostValue("IsStock");
            $items->IsAllowMinus = $this->GetPostValue("IsAllowMinus");
            $items->IsAktif = $this->GetPostValue("IsAktif");
            $items->CabangId = $this->GetPostValue("CabangId");
            $items->ItemLevel = $this->GetPostValue("ItemLevel");
            if ($this->ValidateData($items)) {
                $items->CreatebyId = $this->userUid;
                $rs = $items->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.items','Add New Item -> Kode: '.$items->ItemCode.' - '.$items->ItemName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Barang: %s (%s) sudah berhasil disimpan", $items->ItemName, $items->ItemCode));
                    redirect_url("inventory.items");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.items','Add New Item -> Kode: '.$items->ItemCode.' - '.$items->ItemName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Cabang();
        $cabangs = $loader->LoadByType($this->userCompanyId,-1,">");
        $loader = new Supplier();
        $principals = $loader->LoadPrincipal();
        $loader = new ItemBrand();
        $brands = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new ItemSubCategory();
        $subcategories = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new ItemUom();
        $itemuoms = $loader->LoadAll();
        //send to form
        $this->Set("principals", $principals);
        $this->Set("brands", $brands);
        $this->Set("subcategories", $subcategories);
        $this->Set("itemuoms", $itemuoms);
        $this->Set("items", $items);
        $this->Set("cabId", $this->userCabangId);
        $this->Set("cabCode", $cabCode);
        $this->Set("cabName", $cabName);
        $this->Set("cabangs", $cabangs);
	}

	public function edit($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data barang terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.items");
        }
        require_once(MODEL . "ap/supplier.php");
        require_once(MODEL . "inventory/itembrand.php");
        require_once(MODEL . "inventory/itemsubcategory.php");
        require_once(MODEL . "inventory/itemuom.php");
        require_once(MODEL . "master/cabang.php");
        $items = new Items();
        $log = new UserAdmin();
        $loader = null;
        if (count($this->postData) > 0) {
            $items->ItemCode = $this->GetPostValue("ItemCode");
            $items->OldCode = $this->GetPostValue("OldCode");
            $items->ItemName = $this->GetPostValue("ItemName");
            $items->SubCategoryId = $this->GetPostValue("SubCategoryId");
            //$items->BarCode = $this->GetPostValue("BarCode");
            $items->PrincipalId = $this->GetPostValue("PrincipalId");
            $items->BrandId = $this->GetPostValue("BrandId");
            $items->LuomCode = $this->GetPostValue("LuomCode");
            $items->LuomQty = $this->GetPostValue("LuomQty");
            $items->SuomCode = $this->GetPostValue("SuomCode");
            $items->SuomQty = $this->GetPostValue("SuomQty");
            //$items->IsConvert = $this->GetPostValue("IsConvert");
            $items->QtyConvert = $this->GetPostValue("QtyConvert");
            $items->CuomCode = $this->GetPostValue("CuomCode");
            //$items->MaxStock = $this->GetPostValue("MaxStock");
            $items->MinStock = $this->GetPostValue("MinStock");
            //$items->IsPurchase = $this->GetPostValue("IsPurchase");
            //$items->IsSale = $this->GetPostValue("IsSale");
            //$items->IsStock = $this->GetPostValue("IsStock");
            $items->IsAllowMinus = $this->GetPostValue("IsAllowMinus");
            $items->IsAktif = $this->GetPostValue("IsAktif");
            $items->CabangId = $this->GetPostValue("CabangId");
            $items->ItemLevel = $this->GetPostValue("ItemLevel");
            if ($this->ValidateData($items)) {
                $items->UpdatebyId = AclManager::GetInstance()->GetCurrentUser()->Id;
                $rs = $items->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.items','Update Item -> Kode: '.$items->ItemCode.' - '.$items->ItemName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data barang: %s (%s) sudah berhasil disimpan", $items->ItemName, $items->ItemCode));
                    redirect_url("inventory.items");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.items','Update Item -> Kode: '.$items->ItemCode.' - '.$items->ItemName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data barang. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $items = $items->LoadById($id);
            if ($items == null || $items->IsDeleted) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.items");
            }
        }
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        $loader = new Cabang();
        $cabangs = $loader->LoadByType($this->userCompanyId,-1,">");
        $loader = new Supplier();
        $principals = $loader->LoadPrincipal();
        $loader = new ItemBrand();
        $brands = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new ItemSubCategory();
        $subcategories = $loader->LoadByCompanyId($this->userCompanyId);
        $loader = new ItemUom();
        $itemuoms = $loader->LoadAll();
        //send to form
        $this->Set("principals", $principals);
        $this->Set("brands", $brands);
        $this->Set("subcategories", $subcategories);
        $this->Set("itemuoms", $itemuoms);
        $this->Set("items", $items);
        $this->Set("cabId", $this->userCabangId);
        $this->Set("cabCode", $cabCode);
        $this->Set("cabName", $cabName);
        $this->Set("cabangs", $cabangs);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.items");
        }
        $log = new UserAdmin();
        $items = new Items();
        $items = $items->LoadById($id);
        if ($items == null || $items->IsDeleted) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.items");
        }
        $rs = $items->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.items','Delete Item -> Kode: '.$items->ItemCode.' - '.$items->ItemName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Barang Barang: %s (%s) sudah dihapus", $items->ItemName, $items->ItemCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.items','Delete Item -> Kode: '.$items->ItemCode.' - '.$items->ItemName,'-','Success');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis kontak: %s (%s). Error: %s", $items->ItemName, $items->ItemCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.items");
	}

    public function getAutoCode($subcatid = 0){
	    require_once (MODEL . "inventory/itemsubcategory.php");
        require_once (MODEL . "inventory/itemcategory.php");
        require_once (MODEL . "inventory/itemdivision.php");
        $itemCode = null;
        $etiCode = "00";
        $tSeq = null;
        $nSeq = 0;
        $subcats = new ItemSubCategory($subcatid);
        if ($subcats != null){
            $cats = new ItemCategory($subcats->CategoryId);
            if ($cats != null){
                $divs = new ItemDivision($cats->DivisionId);
                if ($divs != null){
                    $etiCode = str_pad($divs->EntityId,2, '0',STR_PAD_LEFT);
                    $itemCode = $etiCode.right($subcats->SubCategoryCode,3);
                    $itemx = new Items();
                    $tSeq = $itemx->getLastCode($itemCode);
                    $nSeq = (Int)$tSeq;
                    $nSeq++;
                    $itemCode = $itemCode.str_pad($nSeq,3,'0',STR_PAD_LEFT);
                }
            }
        }
        print $itemCode;
    }

    public function checkcode($item_code = null){
        $items = new Items();
        $items = $items->FindByKode($item_code);
        $ret = 0;
        if ($items != null){
            $ret = $items->ItemName;
        }
        print $ret;
    }

    public function getitems_json($principalId = 0,$order = "a.item_code"){
        $filter = isset($_POST['q']) ? strval($_POST['q']) : '';
        $items = new Items();
        $itemlists = $items->GetJSonItems($this->userCompanyId,$this->userCabangId,$principalId,$filter,$order);
        echo json_encode($itemlists);
    }

    public function getplain_items($item_code){
        $ret = 'ER|0';
        if($item_code != null || $item_code != ''){
            /** @var $items Items */
            $items = new Items();
            $items = $items->FindByKode($item_code);
            if ($items != null){
                $ret = "OK|".$items->Bid.'|'.$items->ItemName.'|'.$items->LuomCode.'|'.$items->Bsatsedang.'|'.$items->SuomCode.'|'.$items->IsPurchase.'|'.$items->Bqtystock.'|'.$items->Bhargabeli.'|'.$items->Bhargajual;
            }
        }
        print $ret;
    }

    public function items_list($output,$status){
        require_once(MODEL . "master/company.php");
        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $compname = $company->CompanyName;
        $items = new Items();
        $items = $items->LoadItemList($this->userCompanyId,$this->userCabangId,$status);
        $this->Set("items", $items);
        $this->Set("output", $output);
        $this->Set("company_name", $compname);
    }

    public function upload(){
        // untuk melakukan upload dan update data sparepart
        if (count($this->postData) > 0) {
            // Ada data yang di upload...
            $this->doUpload();
            redirect_url("inventory.items");
        }
    }

    public function doUpload(){
        $log = new UserAdmin();
        $items = new Items();
        $uploadedFile = $this->GetPostValue("fileUpload");
        $processedData = 0;
        $infoMessages = array();	// Menyimpan info message yang akan di print
        $errorMessages = array();	// Menyimpan error message yang akan di print

        if ($uploadedFile["error"] !== 0) {
            $this->persistence->SaveState("error", "Gagal Upload file ke server !");
            return;
        }

        $tokens = explode(".", $uploadedFile["name"]);
        $ext = end($tokens);

        if ($ext != "xls" && $ext != "xlsx") {
            $this->persistence->SaveState("error", "File yang diupload bukan berupa file excel !");
            return;
        }

        // Load libs Excel
        require_once(LIBRARY . "PHPExcel.php");
        if ($ext == "xls") {
            $reader = new PHPExcel_Reader_Excel5();
        } else {
            $reader = new PHPExcel_Reader_Excel2007();
        }
        $phpExcel = $reader->load($uploadedFile["tmp_name"]);

        // OK baca file excelnya sekarang....
        require_once(MODEL . "master/itemjenis.php");
        require_once(MODEL . "master/itemdivisi.php");
        require_once(MODEL . "master/itemkelompok.php");
        require_once(MODEL . "master/itemuom.php");
        require_once(MODEL . "master/contacts.php");
        require_once(MODEL . "master/setprice.php");
        require_once(MODEL . "inventory/stock.php");

        // Step #01: Baca mapping kode shift
        $sheet = $phpExcel->getSheetByName("Data Barang");
        $maxRow = $sheet->getHighestRow();
        $startFrom = 4;
        $sql = null;
        $nmr = 0;
        for ($i = $startFrom; $i <= $maxRow; $i++) {
            $nmr++;
            // OK kita lihat apakah User berbaik hati menggunakan ID atau tidak
            $iJenis = trim($sheet->getCellByColumnAndRow(1, $i)->getCalculatedValue());
            $iDivisi = trim($sheet->getCellByColumnAndRow(2, $i)->getCalculatedValue());
            $iKelompok = trim($sheet->getCellByColumnAndRow(3, $i)->getCalculatedValue());
            $iKdBarang = trim($sheet->getCellByColumnAndRow(4, $i)->getCalculatedValue());
            $iNmBarang = trim($sheet->getCellByColumnAndRow(5, $i)->getCalculatedValue());
            $iSatBesar = trim($sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue());
            $iIsiSatKecil = $sheet->getCellByColumnAndRow(7, $i)->getCalculatedValue();
            $iSatKecil = trim($sheet->getCellByColumnAndRow(8, $i)->getCalculatedValue());
            $iKdSupplier = trim($sheet->getCellByColumnAndRow(9, $i)->getCalculatedValue());
            $iKeterangan = trim($sheet->getCellByColumnAndRow(10, $i)->getCalculatedValue());
            $iHrgBeli = $sheet->getCellByColumnAndRow(11, $i)->getCalculatedValue();
            $iHrgJual = $sheet->getCellByColumnAndRow(12, $i)->getCalculatedValue();

            if ($iJenis == "" || $iJenis == null || $iJenis == '-'){
                $infoMessages[] = sprintf("[%d] Jenis Barang: -%s- tidak valid! Pastikan Jenis Barang pada template sudah benar!",$nmr,$iJenis);
                continue;
            }
            if ($iDivisi == "" || $iDivisi == null || $iDivisi == '-'){
                $infoMessages[] = sprintf("[%d] Divisi Barang: -%s- tidak valid! Pastikan Divisi Barang pada template sudah benar!",$nmr,$iDivisi);
                continue;
            }
            if ($iKelompok == "" || $iKelompok == null || $iKelompok == '-'){
                $infoMessages[] = sprintf("[%d] Kelompok Barang: -%s- tidak valid! Pastikan Kelompok Barang pada template sudah benar!",$nmr,$iKelompok);
                continue;
            }
            if ($iKdBarang == "" || $iKdBarang == null || $iKdBarang == '-'){
                $infoMessages[] = sprintf("[%d] Kode Barang: -%s- tidak valid! Pastikan Kode Barang pada template sudah benar ",$nmr,$iKdBarang);
                continue;
            }
            if ($iNmBarang == "" || $iNmBarang == null || $iNmBarang == '-'){
                $infoMessages[] = sprintf("[%d] Nama Barang: -%s- tidak valid! Pastikan Nama Barang pada template sudah benar!",$nmr,$iNmBarang);
                continue;
            }
            if ($iSatBesar == "" || $iSatBesar == null || $iSatBesar == '-'){
                $infoMessages[] = sprintf("[%d] Satuan Barang: -%s- tidak valid! Pastikan Satuan Barang pada template sudah benar!",$nmr,$iNmBarang);
                continue;
            }
            //periksa kode supplier
            //if (($iKdSupplier != "" && $iKdSupplier != null && $iKdSupplier != '-') || (strlen(trim($iKdSupplier))>3)){
            if ((strlen(trim($iKdSupplier))>3)){
                $bsupplier = new Contacts();
                $bsupplier = $bsupplier->FindBySupplierCode($iKdSupplier);
                if ($bsupplier == null) {
                    $infoMessages[] = sprintf("[%d] Kode Supplier: -%s- tidak valid! Pastikan Kode Supplier pada template sudah benar!", $nmr, $iKdSupplier);
                    continue;
                }
            }
            //periksa jenis barang jika tidak ada tambahkan
            $bjenis = new ItemJenis();
            $bjenis = $bjenis->FindByJenis($iJenis);
            if($bjenis == null){
                $bjenis = new ItemJenis();
                $bjenis->JnsBarang = $iJenis;
                $bjenis->Keterangan = $iJenis;
                $rs = $bjenis->Insert();
                if ($rs == 0){
                    $infoMessages[] = sprintf("[%d] Jenis Barang: -%s- tidak valid! Pastikan Jenis Barang pada template sudah benar!",$nmr,$iJenis);
                    continue;
                }
            }
            //periksa divisi barang jika tidak ada tambahkan
            $principal_id = new ItemDivisi();
            $principal_id = $principal_id->FindByDivisi($iDivisi);
            if($principal_id == null){
                $principal_id = new ItemDivisi();
                $principal_id->Divisi = $iDivisi;
                $principal_id->Keterangan = $iDivisi;
                $rs = $principal_id->Insert();
                if ($rs == 0){
                    $infoMessages[] = sprintf("[%d] Divisi Barang: -%s- tidak valid! Pastikan Divisi Barang pada template sudah benar!",$nmr,$iDivisi);
                    continue;
                }
            }
            //periksa kelompok barang jika tidak ada tambahkan
            $brand_id = new ItemKelompok();
            $brand_id = $brand_id->FindByKelompok($iKelompok);
            if($brand_id == null){
                $brand_id = new ItemKelompok();
                $brand_id->Kelompok = $iKelompok;
                $brand_id->Keterangan = $iKelompok;
                $rs = $brand_id->Insert();
                if ($rs == 0){
                    $infoMessages[] = sprintf("[%d] Kelompok Barang: -%s- tidak valid! Pastikan Kelompok Barang pada template sudah benar!",$nmr,$iKelompok);
                    continue;
                }
            }
            $xitems = null;
            $isnew = true;
            $isoke = true;
            $iBid = 0;
            $items->PrincipalId = $iJenis;
            $items->BrandId = $iDivisi;
            $items->Bkelompok = $iKelompok;
            $items->ItemCode = $iKdBarang;
            $items->ItemName = $iNmBarang;
            $items->LuomCode = $iSatBesar;
            $items->SuomCode = $iSatKecil;
            $items->SuomQty = $iIsiSatKecil == null ? 1 : $iIsiSatKecil;
            $items->Bsupplier = $iKdSupplier;
            $items->Bhargabeli = $iHrgBeli == null ? 0 : $iHrgBeli;
            $items->Bhargajual = $iHrgJual == null ? 0 : $iHrgJual;
            $items->Bbarcode = $iKdBarang;
            $items->IsStock = 1;
            $items->BarCode = $iKeterangan;
            $xitems = new Items();
            $xitems = $xitems->LoadByKode($iKdBarang);
            if ($xitems != null){
                $isnew = false;
            }
            // mulai proses update
            $this->connector->BeginTransaction();
            $hasError = false;
            //$rs = $items->DeleteProcess();
            if ($isnew) {
                $items->CreatebyId = $this->userUid;
                $items->ItemLevel = 2;
                $items->IsAktif = $this->userCabangId;
                $rs = $items->Insert();
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal simpan Data Barang-> Kode: %s - Nama: %s Message: %s",$nmr,$iKdBarang,$iNmBarang,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $iBid = $items->Bid;
                }
            }else{
                $items->ItemLevel = 2;
                $items->IsAktif = $this->userCabangId;
                $items->UpdatebyId = $this->userUid;
                $rs = $items->Update($xitems->Bid);
                if ($rs != 1) {
                    // Hmm error apa lagi ini ?? DBase related harusnya
                    $errorMessages[] = sprintf("[%d] Gagal Update Data Barang-> Kode: %s - Nama: %s Message: %s",$nmr,$iKdBarang,$iNmBarang,$this->connector->GetErrorMessage());
                    $hasError = true;
                    $isoke = false;
                    break;
                }else{
                    $iBid = $xitems->Bid;
                }
            }
            //update daftar harga sekalian
            if ($isoke){
                $bprice = new SetPrice();
                $bprice = $bprice->FindByKode($this->userCabangId,$iKdBarang);
                if ($bprice == null){
                    //harga barang baru
                    $bprice = new SetPrice();
                    $bprice->CabangId = $this->userCabangId;
                    $bprice->ItemId = $iBid;
                    $bprice->ItemCode = $iKdBarang;
                    $bprice->Satuan = $iSatBesar;
                    $bprice->HrgBeli = $iHrgBeli;
                    $bprice->HrgJual1 = $iHrgJual;
                    $bprice->PriceDate = date('Y-m-d',time());
                    $bprice->HrgJual2 = $iHrgJual;
                    $bprice->HrgJual3 = $iHrgJual;
                    $bprice->HrgJual4 = $iHrgJual;
                    $bprice->HrgJual5 = $iHrgJual;
                    $bprice->HrgJual6 = $iHrgJual;
                    $bprice->CreatebyId = $this->userUid;
                    $bprice->UpdatebyId = $this->userUid;
                    $bprice->Insert();
                }else{
                    //harga barang baru
                    $bprice = new SetPrice();
                    $bprice->CabangId = $this->userCabangId;
                    $bprice->ItemId = $iBid;
                    $bprice->ItemCode = $iKdBarang;
                    $bprice->Satuan = $iSatBesar;
                    $bprice->HrgBeli = $iHrgBeli;
                    $bprice->HrgJual1 = $iHrgJual;
                    $bprice->PriceDate = date('Y-m-d',time());
                    $bprice->HrgJual2 = $iHrgJual;
                    $bprice->HrgJual3 = $iHrgJual;
                    $bprice->HrgJual4 = $iHrgJual;
                    $bprice->HrgJual5 = $iHrgJual;
                    $bprice->HrgJual6 = $iHrgJual;
                    $bprice->CreatebyId = $this->userUid;
                    $bprice->UpdatebyId = $this->userUid;
                    $bprice->Update($iBid);
                }
                // revised 20170614
                // isi stockcenter dengan 0
                $bstock = new Stock();
                $bstock = $bstock->FindByKode($this->userCabangId,$iKdBarang);
                if ($bstock == null){
                    $bstock = new Stock();
                    $bstock->CabangId = $this->userCabangId;
                    $bstock->ItemId = $iBid;
                    $bstock->ItemCode = $iKdBarang;
                    $bstock->QtyStock = 0;
                    $bstock->CreatebyId = $this->userUid;
                    $rs = $bstock->Insert();
                }
            }
            // Step #06: Commit/Rollback transcation per karyawan...
            if ($hasError) {
                $this->connector->RollbackTransaction();
            } else {
                $this->connector->CommitTransaction();
                $processedData++;
            }
        }

        // Step #07: Sudah selesai.... semua karyawan sudah diproses
        if (count($errorMessages) > 0) {
            $this->persistence->SaveState("error", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $errorMessages)));
            $infoMessages[] = "Data Barang yang ERROR tidak di-entry ke system sedangkan yang lainnya tetap dimasukkan.";
        }
        if ($processedData > 0) {
            $log = $log->UserActivityWriter($this->userCabangId, 'inventory.items', 'Upload Data Items from excel file = '.$processedData.' item(s)', '-', 'Success');
        }
        $infoMessages[] = "Proses Upload Data Barang selesai. Jumlah data yang diproses: " . $processedData;
        $this->persistence->SaveState("info", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $infoMessages)));

        // Completed...
    }

    public function template(){
        // untuk melakukan download template
        require_once(MODEL . "master/itemjenis.php");
        require_once(MODEL . "master/itemdivisi.php");
        require_once(MODEL . "master/itemkelompok.php");
        require_once(MODEL . "master/itemuom.php");
        require_once(MODEL . "master/contacts.php");
        $ijenis = new ItemJenis();
        $ijenis = $ijenis->LoadAll();
        $this->Set("ijenis",$ijenis);
        $idivisi = new ItemDivisi();
        $idivisi = $idivisi->LoadAll();
        $this->Set("idivisi",$idivisi);
        $ikelompok = new ItemKelompok();
        $ikelompok = $ikelompok->LoadAll();
        $this->Set("ikelompok",$ikelompok);
        $isatuan = new ItemUom();
        $isatuan = $isatuan->LoadAll();
        $this->Set("isatuan",$isatuan);
        $isupplier = new Contacts();
        $isupplier = $isupplier->LoadByType(2);
        $this->Set("isupplier",$isupplier);
    }
}

// End of file: items_controller.php
