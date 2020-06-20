<?php
class ItemPricesController extends AppController {
	private $userUid;
	private $userCompanyId;
	private $userCabangId;
	private $userLevel;

	protected function Initialize() {
		require_once(MODEL . "inventory/itemprices.php");
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
		$settings["columns"][] = array("name" => "a.cabang_code", "display" => "Cabang", "width" => 50);
        $settings["columns"][] = array("name" => "a.entity_code", "display" => "Entitas", "width" => 50);
        $settings["columns"][] = array("name" => "a.item_code", "display" => "Kode Barang", "width" => 100);
        $settings["columns"][] = array("name" => "a.item_name", "display" => "Nama Barang", "width" => 250);
        $settings["columns"][] = array("name" => "a.uom_code", "display" => "Satuan", "width" => 50);
        $settings["columns"][] = array("name" => "format(a.zone_1,2)", "display" => "Harga Zone 1", "width" => 65, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.zone_2,2)", "display" => "Harga Zone 2", "width" => 65, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.zone_3,2)", "display" => "Harga Zone 3", "width" => 65, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.zone_4,2)", "display" => "Harga Zone 4", "width" => 65, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.zone_5,2)", "display" => "Harga Zone 5", "width" => 65, "align" => "right");
        if ($this->userLevel > 1) {
            $settings["columns"][] = array("name" => "format(a.purchase_price,2)", "display" => "Harga Beli", "width" => 65, "align" => "right");
            $settings["columns"][] = array("name" => "format(a.hpp,2)", "display" => "H P P", "width" => 65, "align" => "right");
        }
        $settings["columns"][] = array("name" => "a.price_date", "display" => "Mulai Tgl", "width" => 80);

		$settings["filters"][] = array("name" => "a.item_code", "display" => "Kode Barang");
		$settings["filters"][] = array("name" => "a.item_name", "display" => "Nama Barang");
        $settings["filters"][] = array("name" => "a.cabang_code", "display" => "Cabang");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Harga Barang";

			if ($acl->CheckUserAccess("inventory.itemprices", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itemprices/add/0", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itemprices", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itemprices/add/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih items terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu items.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itemprices", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itemprices/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih items terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu items.",
					"Confirm" => "Apakah anda mau menghapus data items yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}
            if ($acl->CheckUserAccess("inventory.itemprices", "view")) {
                $settings["actions"][] = array("Text" => "View", "Url" => "inventory.itemprices/view/%s", "Class" => "bt_view", "ReqId" => 1,
                    "Error" => "Mohon memilih items terlebih dahulu.\nPERHATIAN: Mohon memilih tepat satu item data.");
            }
            /*
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.itemprices", "edit")) {
				$settings["actions"][] = array("Text" => "Upload Daftar Harga", "Url" => "inventory.itemprices/upload", "Class" => "bt_excel", "ReqId" => 0);
			}
			$settings["actions"][] = array("Text" => "separator", "Url" => null);
			if ($acl->CheckUserAccess("inventory.itemprices", "view")) {
				$settings["actions"][] = array("Text" => "Daftar Harga Barang", "Url" => "inventory.itemprices/prices_list/xls", "Class" => "bt_excel", "ReqId" => 0);
			}
            */
			$settings["def_order"] = 3;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_m_item_prices AS a";
			$settings["where"] = "a.company_id = ".$this->userCompanyId;
            $settings["order by"] = "a.entity_id,a.item_code";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function add($pId = 0) {
		require_once(MODEL . "master/cabang.php");
		$itemprices = new ItemPrices();
		$log = new UserAdmin();
		$loader = null;
		if (count($this->postData) > 0) {
			$itemprices->Id = $this->GetPostValue("Id");
			$itemprices->CabangId = $this->GetPostValue("CabangId");
			$itemprices->PriceDate = strtotime($this->GetPostValue("PriceDate"));
			$itemprices->ItemId = $this->GetPostValue("ItemId");
			$itemprices->UomCode = $this->GetPostValue("UomCode");
			if ($this->userLevel > 1) {
                $itemprices->PurchasePrice = $this->GetPostValue("PurchasePrice");
                $itemprices->Hpp = $this->GetPostValue("Hpp");
            }
			$itemprices->pZone1 = $this->GetPostValue("pZone1");
            $itemprices->pZone2 = $this->GetPostValue("pZone2");
            $itemprices->pZone3 = $this->GetPostValue("pZone3");
            $itemprices->pZone4 = $this->GetPostValue("pZone4");
            $itemprices->pZone5 = $this->GetPostValue("pZone5");
			if ($this->ValidateData($itemprices)) {
				$itemprices->CreatebyId = $this->userUid;
				$itemprices->UpdatebyId = $this->userUid;
				// cek kalo sudah ada data harganya diupdate saja
				$priceId = 0;
				if ($itemprices->Id == 0){
					$pricelist = new ItemPrices();
					$priceId = $pricelist->FindPriceByUnitId($itemprices->CabangId,$itemprices->ItemId,$itemprices->UomCode);
				}else{
					$priceId = $itemprices->Id;
				}
				if ($priceId > 0){
					$rs = $itemprices->Update($priceId);
					if ($rs <> 0) {
						$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Update Price -> Item Code: '.$itemprices->ItemCode.' - Beli: '.$priceId->PurchasePrice.' -> '.$itemprices->PurchasePrice.' - Jual: '.$priceId->pZone1.' -> '.$itemprices->pZone1,'-','Success');
						$this->persistence->SaveState("info", sprintf("Data Harga: %s (%s) sudah berhasil diupdate", $itemprices->ItemName, $itemprices->ItemCode));
						redirect_url("inventory.itemprices");
					} else {
						$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Update Price -> Item Code: '.$itemprices->ItemCode.' - Beli: '.$priceId->PurchasePrice.' -> '.$itemprices->PurchasePrice.' - Jual: '.$priceId->pZone1.' -> '.$itemprices->pZone1,'-','Failed');
						$this->Set("error", "Gagal pada saat update data.. Message: " . $this->connector->GetErrorMessage(). ' ['.$rs.']');
					}
				}else {
					$rs = $itemprices->Insert();
					if ($rs <> 0) {
						$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Add Price -> Item Code: '.$itemprices->ItemCode.' - Beli: '.$itemprices->PurchasePrice.' - Jual: '.$itemprices->pZone1,'-','Success');
						$this->persistence->SaveState("info", sprintf("Data Harga: %s (%s) sudah berhasil disimpan", $itemprices->ItemName, $itemprices->ItemCode));
						redirect_url("inventory.itemprices");
					} else {
						$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Add Price -> Item Code: '.$itemprices->ItemCode.' - Beli: '.$itemprices->PurchasePrice.' - Jual: '.$itemprices->pZone1,'-','Failed');
						$this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage(). ' ['.$rs.']');
					}
				}
			}
		}else{
			if ($pId > 0) {
				$itemprices = $itemprices->LoadById($pId);
			}else{
				$itemprices->Id = 0;
			}
		}
		$loader = new Cabang();
		$cabang = $loader->LoadById($this->userCabangId);
		$cabCode = $cabang->Kode;
		$cabName = $cabang->Cabang;
		//send to form
		$this->Set("userLevel", $this->userLevel);
		$this->Set("userCompId", $this->userCompanyId);
		$this->Set("userCabId", $this->userCabangId);
		$this->Set("userCabCode", $cabCode);
		$this->Set("userCabName", $cabName);
		$this->Set("cabangs", $cabang);
		$this->Set("itemprices", $itemprices);
        $this->Set("ulevel", $this->userLevel);
	}

    public function view($pId = 0) {
        //require_once(MODEL . "master/itemjenis.php");
        require_once(MODEL . "master/cabang.php");
        $itemprices = new ItemPrices();
        $log = new UserAdmin();
        $loader = null;
        if ($pId > 0) {
            $itemprices = $itemprices->LoadById($pId);
        }else{
            $itemprices->Id = 0;
        }
        $loader = new Cabang();
        $cabang = $loader->LoadById($this->userCabangId);
        $cabCode = $cabang->Kode;
        $cabName = $cabang->Cabang;
        //send to form
        $this->Set("userLevel", $this->userLevel);
        $this->Set("userCompId", $this->userCompanyId);
        $this->Set("userCabId", $this->userCabangId);
        $this->Set("userCabCode", $cabCode);
        $this->Set("userCabName", $cabName);
        $this->Set("cabangs", $cabang);
        $this->Set("itemprices", $itemprices);
        $this->Set("ulevel", $this->userLevel);
    }

	private function ValidateData(ItemPrices $itemprices) {
		return true;
	}

	public function delete($id = null) {
		if ($id == null) {
			$this->persistence->SaveState("error", "Anda harus memilih data harga sebelum melakukan hapus data !");
			redirect_url("inventory.itemprices");
		}
		$log = new UserAdmin();
		$prices = new ItemPrices();
		$prices = $prices->FindById($id);
		if ($prices == null) {
			$this->persistence->SaveState("error", "Data harga yang dipilih tidak ditemukan ! Mungkin data sudah dihapus.");
			redirect_url("inventory.itemprices");
		}

		if ($prices->Delete($prices->Id) == 1) {
			$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Delete Price -> Item Code: '.$prices->ItemCode.' - Beli: '.$prices->PurchasePrice.' - Jual: '.$prices->pZone1,'-','Success');
			$this->persistence->SaveState("info", sprintf("Data Harga Barang: '%s' Dengan Kode: %s telah berhasil dihapus.", $prices->ItemName, $prices->ItemCode));
			redirect_url("inventory.itemprices");
		} else {
			$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Delete Price -> Item Code: '.$prices->ItemCode.' - Beli: '.$prices->PurchasePrice.' - Jual: '.$prices->pZone1,'-','Failed');
			$this->persistence->SaveState("error", sprintf("Gagal menghapus data harga: '%s'. Message: %s", $prices->ItemName, $this->connector->GetErrorMessage()));
		}
		redirect_url("inventory.itemprices");
	}

	public function prices_list($output){
		require_once(MODEL . "master/company.php");
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$compname = $company->CompanyName;
		$items = new ItemPrices();
		$items = $items->LoadAll($this->userCabangId);
		$this->Set("items", $items);
		$this->Set("output", $output);
		$this->Set("company_name", $compname);
	}

	public function upload(){
		// untuk melakukan upload dan update data sparepart
		if (count($this->postData) > 0) {
			// Ada data yang di upload...
			$this->doUpload();
			redirect_url("inventory.itemprices");
		}
	}

	public function doUpload(){
		$prices = new ItemPrices();
		$log = new UserAdmin();
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

		// Step #01: Baca mapping kode shift
		$sheet = $phpExcel->getSheetByName("Data Harga");
		$maxRow = $sheet->getHighestRow();
		$startFrom = 4;
		$xprice = null;
		$nmr = 0;
		for ($i = $startFrom; $i <= $maxRow; $i++) {
			$nmr++;
			$pItemId = $sheet->getCellByColumnAndRow(1, $i)->getCalculatedValue();
			$pPriceDate = trim($sheet->getCellByColumnAndRow(2, $i)->getCalculatedValue());
			$pItemCode = trim($sheet->getCellByColumnAndRow(3, $i)->getCalculatedValue());
			$pItemName = trim($sheet->getCellByColumnAndRow(4, $i)->getCalculatedValue());
			$pSatuan = trim($sheet->getCellByColumnAndRow(5, $i)->getCalculatedValue());
			$pPurchasePrice = $sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue();
			$pMaxDisc = $sheet->getCellByColumnAndRow(7, $i)->getCalculatedValue();
			$ppZone1 = $sheet->getCellByColumnAndRow(8, $i)->getCalculatedValue();
			$pHrgJual12 = $sheet->getCellByColumnAndRow(9, $i)->getCalculatedValue();
			$pHrgJual13 = $sheet->getCellByColumnAndRow(10, $i)->getCalculatedValue();
			$pHrgJual14 = $sheet->getCellByColumnAndRow(11, $i)->getCalculatedValue();
			$pHrgJual15 = $sheet->getCellByColumnAndRow(12, $i)->getCalculatedValue();
			$pHrgJual16 = $sheet->getCellByColumnAndRow(13, $i)->getCalculatedValue();
			if ($pItemCode == "" || $pItemCode == null || $pItemCode == '-'){
				$infoMessages[] = sprintf("[%d] Kode Barang: -%s- tidak valid! Pastikan Kode Barang pada template sudah benar!",$nmr,$pItemCode);
				continue;
			}
			$prices->CabangId = $this->userCabangId;
			$prices->PriceDate = date('Y-m-d',time());
			$prices->ItemId = $pItemId;
			$prices->ItemCode = $pItemCode;
			$prices->Satuan = $pSatuan;
			$prices->PurchasePrice = $pPurchasePrice == null || $pPurchasePrice == '' ? 0 : $pPurchasePrice;
			$prices->MaxDisc = $pMaxDisc == null || $pMaxDisc == '' ? 0 : $pMaxDisc;
			$prices->pZone1 = $ppZone1 == null || $ppZone1 == '' ? 0 : $ppZone1;
			$prices->HrgJual12 = $pHrgJual12 == null || $pHrgJual12 == '' ? 0 : $pHrgJual12;
			$prices->HrgJual13 = $pHrgJual13 == null || $pHrgJual13 == '' ? 0 : $pHrgJual13;
			$prices->HrgJual14 = $pHrgJual14 == null || $pHrgJual14 == '' ? 0 : $pHrgJual14;
			$prices->HrgJual15 = $pHrgJual15 == null || $pHrgJual15 == '' ? 0 : $pHrgJual15;
			$prices->HrgJual16 = $pHrgJual16 == null || $pHrgJual16 == '' ? 0 : $pHrgJual16;
			$prices->CreatebyId = $this->userUid;
			$prices->UpdatebyId = $this->userUid;

			//cek apa ada perubahan data
			$xprice = new ItemPrices();
			$xprice = $xprice->FindByKode($this->userCabangId,$pItemCode);
			$isupdate = true;
			if ($xprice != null){
				/** @var $xprice ItemPrices */
				if (floatval($xprice->PurchasePrice) == floatval($pPurchasePrice) && floatval($xprice->MaxDisc) == floatval($pMaxDisc) && floatval($xprice->pZone1) == floatval($ppZone1) && floatval($xprice->HrgJual12) == floatval($pHrgJual12) && floatval($xprice->HrgJual13) == floatval($pHrgJual13) && floatval($xprice->HrgJual14) == floatval($pHrgJual14) && floatval($xprice->HrgJual15) == floatval($pHrgJual15) && floatval($xprice->HrgJual16) == floatval($pHrgJual16)){
					$isupdate = false;
				}
			}
			// mulai proses update
			if ($isupdate) {
				$this->connector->BeginTransaction();
				$hasError = false;
				$rs = $prices->DeleteByKode($this->userCabangId,$pItemCode);
				$rs = $prices->Insert();
				if ($rs != 1) {
					// Hmm error apa lagi ini ?? DBase related harusnya
					$errorMessages[] = sprintf("[%d] Gagal simpan Data Harga Barang-> Kode: %s - Nama: %s Message: %s", $nmr, $pItemCode, $pItemName, $this->connector->GetErrorMessage());
					$hasError = true;
					break;
				}
				// Step #06: Commit/Rollback transcation per karyawan...
				if ($hasError) {
					$this->connector->RollbackTransaction();
				} else {
					$this->connector->CommitTransaction();
					$processedData++;
				}
			}
		}

		// Step #07: Sudah selesai.... semua karyawan sudah diproses
		if (count($errorMessages) > 0) {
			$this->persistence->SaveState("error", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $errorMessages)));
			$infoMessages[] = "Data Harga Barang yang ERROR tidak di-entry ke system sedangkan yang lainnya tetap dimasukkan.";
		}
		if ($processedData > 0){
			$log = $log->UserActivityWriter($this->userCabangId,'inventory.itemprices','Upload Price from excel file -> '.$processedData.' item(s) updated','-','Failed');
		}

		$infoMessages[] = "Proses Upload Data Barang selesai. Jumlah data yang diproses: " . $processedData;
		$this->persistence->SaveState("info", sprintf('<ol style="margin: 0;"><li>%s</li></ol>', implode("</li><li>", $infoMessages)));

		// Completed...
	}

	public function template(){
		// untuk melakukan download template
		$prices = new ItemPrices();
		$prices = $prices->LoadAll($this->userCabangId,'a.item_name,a.item_code');
		$this->Set("prices",$prices);
	}


}

// End of file: items_controller.php
