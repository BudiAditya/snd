<?php
class FakturInController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;

	protected function Initialize() {
		require_once(MODEL . "tax/fakturin.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
        $settings["columns"][] = array("name" => "a.nomor_faktur", "display" => "No. Faktur", "width" => 80);
		$settings["columns"][] = array("name" => "a.tanggal_faktur", "display" => "Tanggal", "width" => 60);
        $settings["columns"][] = array("name" => "a.nama", "display" => "Nama PKP", "width" => 150);
        $settings["columns"][] = array("name" => "a.alamat_lengkap", "display" => "Alamat", "width" => 350);
        $settings["columns"][] = array("name" => "a.npwp", "display" => "NPWP", "width" => 110);
        $settings["columns"][] = array("name" => "format(a.jumlah_dpp,0)", "display" => "D P P", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.jumlah_ppn,0)", "display" => "P P N", "width" => 70, "align" => "right");
        $settings["columns"][] = array("name" => "format(a.jumlah_dpp+a.jumlah_ppn,0)", "display" => "Jumlah", "width" => 80, "align" => "right");
        $settings["columns"][] = array("name" => "a.referensi", "display" => "No. Reff", "width" => 100);
        $settings["columns"][] = array("name" => "a.kode_supplier", "display" => "Supplier", "width" => 60);

		$settings["filters"][] = array("name" => "a.nomor_faktur", "display" => "No. Faktur");
        $settings["filters"][] = array("name" => "a.tanggal_faktur", "display" => "Tanggal Faktur");
        $settings["filters"][] = array("name" => "a.nama", "display" => "Nama PKP");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Daftar Faktur Pajak (Masukan)";

			if ($acl->CheckUserAccess("tax.fakturin", "view")) {
				$settings["actions"][] = array("Text" => "View", "Url" => "tax.fakturin/view/%s", "Class" => "bt_view", "ReqId" => 1,
					"Error" => "Mohon memilih faktur terlebih dahulu sebelum proses view.\nPERHATIAN: Mohon memilih tepat satu faktur.",
					"Confirm" => "");
			}

			$settings["actions"][] = array("Text" => "separator", "Url" => null);
            if ($acl->CheckUserAccess("tax.fakturin", "view")) {
                $settings["actions"][] = array("Text" => "Laporan", "Url" => "tax.fakturin/report", "Class" => "bt_report", "ReqId" => 0);
            }

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
			$settings["from"] = "vw_fp_in_master AS a ";
            if ($_GET["query"] == "") {
                $settings["where"] = "Year(a.tanggal_faktur) = $this->trxYear";// And Month(a.tanggal_faktur) = $this->trxMonth And a.company_id = $this->userCompanyId";
            }else{
                $settings["where"] = "a.company_id = $this->userCompanyId";
            }
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	public function view($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("tax.fakturin");
        }
        $log = new UserAdmin();
        $faktur = new FakturIn();
        $faktur = $faktur->LoadById($id);
        if ($faktur == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("tax.fakturin");
        }
        $loader = new FakturIn();
        $dfaktur = $loader->LoadDetailByFakturInId($id);
        $this->Set("mfaktur", $faktur);
        $this->Set("dfaktur", $dfaktur);
	}

    public function report(){
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
        $loader = new FakturIn();
        $dfaktur = $loader->LoadAllByMonth($this->userCompanyId,$tahun,$bulan);
        $this->Set("tahun", $tahun);
        $this->Set("bulan", $bulan);
        $this->Set("output", $output);
        $this->Set("dfaktur", $dfaktur);
    }

    public function gencsv(){
        $ids = $this->GetPostValue("ids", array());
        $thn = $this->GetPostValue("rTahun");
        $bln = $this->GetPostValue("rBulan");
        $qry = "Select a.*,b.grn_id,b.kode as of_kode,b.kode_objek,b.nama as nm_barang,b.harga_satuan,b.jumlah_barang,b.harga_total,b.diskon,b.dpp,b.ppn,b.tarif_ppnbm,b.ppnbm";
        $qry.= " From vw_fp_in_master a JOIN vw_fp_in_detail b ON a.id = b.grn_id Where a.id IN ?ids Order By a.nomor_faktur,a.id,b.id";
        $this->connector->CommandText = $qry;
        $this->connector->AddParameter("?ids", $ids);
        $rs = $this->connector->ExecuteQuery();
        if ($rs != null){
            //config
            $namefile = "clu-fpk-in-".$thn.$bln.".csv";
            $header = '"FK","KD_JENIS_TRANSAKSI","FG_PENGGANTI","NOMOR_FAKTUR","MASA_PAJAK","TAHUN_PAJAK","TANGGAL_FAKTUR","NPWP","NAMA","ALAMAT_LENGKAP","JUMLAH_DPP","JUMLAH_PPN","JUMLAH_PPNBM","ID_KETERANGAN_TAMBAHAN","FG_UANG_MUKA","UANG_MUKA_DPP","UANG_MUKA_PPN","UANG_MUKA_PPNBM","REFERENSI"' . PHP_EOL;
            $header .= '"LT","NPWP","NAMA","JALAN","BLOK","NOMOR","RT","RW","KECAMATAN","KELURAHAN","KABUPATEN","PROPINSI","KODE_POS","NOMOR_TELEPON"' . PHP_EOL;
            $header .= '"OF","KODE_OBJEK","NAMA","HARGA_SATUAN","JUMLAH_BARANG","HARGA_TOTAL","DISKON","DPP","PPN","TARIF_PPNBM","PPNBM"' . PHP_EOL;
            $content = $header;
            $detail = null;
            $invid = 0;
            while ($row = $rs->FetchAssoc()) {
                if ($row['id'] != $invid){
                    if ($row['npwp'] == '' || $row['npwp'] == null){
                        $npwp = '000000000000000';
                    }else {
                        $npwp = str_replace('.', '', $row['npwp']);
                        $npwp = str_replace('-', '', $npwp);
                    }
                    if ($row['alamat_lengkap'] == '' || $row['alamat_lengkap'] == null){
                        $alamat = 'Manado';
                    }else{
                        $alamat = $row['alamat_lengkap'];
                    }
                    $detail.= '"' . $row['kode'] .'","'. $row['kd_jenis_pajak'] .'","'. $row['fg_pengganti'] .'","'. $row['nomor_faktur'] .'","'. $row['masa_pajak'] .'","'. $row['tahun_pajak'] .'","'. date('d/m/Y',strtotime($row['tanggal_faktur'])) .'","'. $npwp.'","'. $row['nama'] .'","'. $alamat .'","'. $row['jumlah_dpp'] .'","'. $row['jumlah_ppn'] .'","'. $row['jumlah_ppnbm'] .'","'. $row['id_keterangan_tambahan'] .'","'. $row['fg_uang_muka'] .'","'. $row['uang_muka_dpp'] .'","'. $row['uang_muka_ppn'] .'","'. $row['uang_muka_ppnbm'] .'","'. $row['referensi'] . '"'. PHP_EOL;
                }
                $detail.= '"' . $row['of_kode'] .'","'. $row['kode_objek'] .'","'. $row['nm_barang'] .'","'. $row['harga_satuan'] .'","'. $row['jumlah_barang'] .'","'. $row['harga_total'] .'","'. $row['diskon'] .'","'. $row['dpp'] .'","'. $row['ppn'] .'","'. $row['tarif_ppnbm'] .'","'. $row['ppnbm'] .'"'. PHP_EOL;
                $invid = $row['grn_id'];
            }
            //save file
            $content.= $detail;

            $file = fopen($namefile, "w") or die("Unable to open file!");
            fwrite($file, $content);
            fclose($file);

            //header download
            header("Content-Disposition: attachment; filename=\"" . $namefile . "\"");
            header("Content-Type: application/force-download");
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header("Content-Type: text/plain");

            echo $content;
        }
    }
}

// End of file: faktur_controller.php
