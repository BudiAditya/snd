<?php
class Cabang extends EntityBase {
	public $Id;
	public $IsDeleted = false;
    public $CompanyId;
	public $CompanyCode;
    public $AreaId;
    public $AreaName;
	public $Kode;
	public $Cabang;
    public $Alamat;
    public $Pic;
    public $FLogo;
    public $NamaCabang;
	public $CompanyName;
	public $RawPrintMode;
	public $RawPrinterName;
	public $CreatebyId;
	public $UpdatebyId;
	public $CabType;
	public $AllowMinus = 0;
	public $Npwp;
	public $Kota;
	public $Norek;
	public $Notel;
    public $PriceIncPpn = 0;
    public $Jk1Mulai = '08:00';
    public $Jk1Akhir = '17:00';
    public $Jk2Mulai = '08:00';
    public $Jk2Akhir = '15:00';
    public $KasAccId = 0;
    public $WktAccId = 0;
    public $PtyAccId = 0;
    public $WorkMode = 0;

	public function __construct($id = null) {
		parent::__construct();
		if (is_numeric($id)) {
			$this->FindById($id);
		}
	}

	public function FillProperties(array $row) {
		$this->Id = $row["id"];
		$this->IsDeleted = $row["is_deleted"] == 1;
		$this->CompanyId = $row["company_id"];
		$this->CompanyCode = $row["company_code"];
		$this->Kode = $row["kode"];
		$this->Cabang = $row["cabang"];
        $this->Alamat = $row["alamat"];
        $this->Pic = $row["pic"];
        $this->AreaId = $row["area_id"];
        $this->AreaName = $row["area_name"];
        $this->FLogo = $row["flogo"];
        $this->NamaCabang = $row["nama_outlet"];
		$this->CompanyName = $row["company_name"];
		$this->RawPrintMode = $row["raw_print_mode"];
		$this->RawPrinterName = $row["raw_printer_name"];
		$this->CreatebyId = $row["createby_id"];
		$this->UpdatebyId = $row["updateby_id"];
		$this->CabType = $row["cab_type"];
		$this->AllowMinus = $row["allow_minus"];
        $this->Kota = $row["kota"];
        $this->Npwp = $row["npwp"];
        $this->Notel = $row["notel"];
        $this->Norek = $row["norek"];
        $this->PriceIncPpn = $row["price_inc_ppn"];
        $this->Jk1Mulai = $row["jk1_mulai"];
        $this->Jk1Akhir = $row["jk1_akhir"];
        $this->Jk2Mulai = $row["jk2_mulai"];
        $this->Jk2Akhir = $row["jk2_akhir"];
        $this->KasAccId = $row["kas_acc_id"];
        $this->WktAccId = $row["wkt_acc_id"];
        $this->PtyAccId = $row["pty_acc_id"];
        $this->WorkMode = $row["work_mode"];
	}

	/**
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Cabang[]
	 */
	public function LoadAll($orderBy = "a.kode", $includeDeleted = false) {
		if ($includeDeleted) {
			$this->connector->CommandText =
"SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name
FROM m_cabang AS a
	JOIN sys_company AS b ON a.company_id = b.id
	JOIN m_area As c On a.area_id = c.id
ORDER BY $orderBy";
		} else {
			$this->connector->CommandText =
"SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name
FROM m_cabang AS a
	JOIN sys_company AS b ON a.company_id = b.id
	JOIN m_area As c On a.area_id = c.id
WHERE a.is_deleted = 0
ORDER BY $orderBy";
		}
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Cabang();
				$temp->FillProperties($row);

				$result[] = $temp;
			}
		}
		return $result;
	}

	public function LoadByType($companyId = 0, $cabType = 0, $operator = "=",$orderBy = "a.kode") {
		$sql = "SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name FROM m_cabang AS a";
		$sql.= " JOIN sys_company AS b ON a.company_id = b.id JOIN m_area As c On a.area_id = c.id";
		if ($companyId > 0){
			$sql.= " WHERE a.is_deleted = 0 And a.cab_type $operator $cabType And a.company_id = $companyId ORDER BY $orderBy";
		}else{
			$sql.= " WHERE a.is_deleted = 0 And a.cab_type $operator $cabType ORDER BY $orderBy";
		}
		$this->connector->CommandText = $sql;
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Cabang();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 * @return Cabang
	 */
	public function FindById($id) {
		$this->connector->CommandText =
"SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name
FROM m_cabang AS a
	JOIN sys_company AS b ON a.company_id = b.id
	JOIN m_area As c On a.area_id = c.id
WHERE a.id = ?id";
		$this->connector->AddParameter("?id", $id);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

	/**
	 * @param int $id
	 * @return Cabang
	 */
	public function LoadById($id) {
		return $this->FindById($id);
	}

	/**
	 * @param int $eti
	 * @param string $orderBy
	 * @param bool $includeDeleted
	 * @return Cabang[]
	 */
	public function LoadByCompanyId($eti, $orderBy = "a.kode") {
		$this->connector->CommandText = "SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name FROM m_cabang AS a JOIN sys_company AS b ON a.company_id = b.id JOIN m_area As c On a.area_id = c.id WHERE a.is_deleted = 0 AND a.company_id = ?eti ORDER BY $orderBy";
		$this->connector->AddParameter("?eti", $eti);
		$rs = $this->connector->ExecuteQuery();
        $result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new Cabang();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
	}

    public function LoadAllowedCabId($cabIds, $orderBy = "a.kode") {
        $this->connector->CommandText = "SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name FROM m_cabang AS a JOIN sys_company AS b ON a.company_id = b.id JOIN m_area As c On a.area_id = c.id WHERE a.is_deleted = 0 AND a.id IN (".$cabIds.") ORDER BY $orderBy";
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Cabang();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function LoadByCompanyId1($eti) {
		$this->connector->CommandText = "SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name FROM m_cabang AS a
	JOIN sys_company AS b ON a.company_id = b.id JOIN m_area As c On a.area_id = c.id WHERE a.company_id = ?eti Order By a.id Limit 1";
		$this->connector->AddParameter("?eti", $eti);
		$rs = $this->connector->ExecuteQuery();
		if ($rs == null || $rs->GetNumRows() == 0) {
			return null;
		}

		$row = $rs->FetchAssoc();
		$this->FillProperties($row);
		return $this;
	}

    public function LoadByAreaId($ari, $orderBy = "a.kode", $includeDeleted = false) {
        if ($includeDeleted) {
            $this->connector->CommandText =
                "SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name
FROM m_cabang AS a
	JOIN sys_company AS b ON a.company_id = b.id
	JOIN m_area As c On a.area_id = c.id
WHERE a.area_id = ?ari
ORDER BY $orderBy";
        } else {
            $this->connector->CommandText =
                "SELECT a.*, b.company_code, c.id as area_id, c.area_name,b.company_name
FROM m_cabang AS a
	JOIN sys_company AS b ON a.company_id = b.id
	JOIN m_area As c On a.area_id = c.id
WHERE a.is_deleted = 0 AND a.area_id = ?ari
ORDER BY $orderBy";
        }

        $this->connector->AddParameter("?ari", $ari);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new Cabang();
                $temp->FillProperties($row);
                $result[] = $temp;
            }
        }
        return $result;
    }

	public function Insert() {
		$this->connector->CommandText = 'INSERT INTO m_cabang(work_mode,pty_acc_id,kas_acc_id,wkt_acc_id,jk1_mulai,jk1_akhir,jk2_mulai,jk2_akhir,price_inc_ppn,kota,npwp,norek,notel,allow_minus,cab_type,company_id,area_id,kode,cabang,alamat,pic,flogo,nama_outlet,raw_print_mode,raw_printer_name,createby_id,create_time) VALUES(?work_mode,?pty_acc_id,?kas_acc_id,?wkt_acc_id,?jk1_mulai,?jk1_akhir,?jk2_mulai,?jk2_akhir,?price_inc_ppn,?kota,?npwp,?norek,?notel,?allow_minus,?cab_type,?company_id,?area_id,?kode,?cabang,?alamat,?pic,?flogo,?nama_outlet,?raw_print_mode,?raw_printer_name,?createby_id,now())';
		$this->connector->AddParameter("?allow_minus", $this->AllowMinus);
		$this->connector->AddParameter("?cab_type", $this->CabType);
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?area_id", $this->AreaId);
        $this->connector->AddParameter("?kode", $this->Kode);
        $this->connector->AddParameter("?cabang", $this->Cabang);
        $this->connector->AddParameter("?alamat", $this->Alamat);
        $this->connector->AddParameter("?pic", $this->Pic);
        $this->connector->AddParameter("?flogo", $this->FLogo);
        $this->connector->AddParameter("?nama_outlet", $this->NamaCabang);
		$this->connector->AddParameter("?raw_print_mode", $this->RawPrintMode);
		$this->connector->AddParameter("?raw_printer_name", $this->RawPrinterName);
		$this->connector->AddParameter("?createby_id", $this->CreatebyId);
        $this->connector->AddParameter("?kota", $this->Kota);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?norek", $this->Norek);
        $this->connector->AddParameter("?notel", $this->Notel, "char");
        $this->connector->AddParameter("?price_inc_ppn", $this->PriceIncPpn);
        $this->connector->AddParameter("?jk1_mulai", $this->Jk1Mulai);
        $this->connector->AddParameter("?jk1_akhir", $this->Jk1Akhir);
        $this->connector->AddParameter("?jk2_mulai", $this->Jk2Mulai);
        $this->connector->AddParameter("?jk2_akhir", $this->Jk2Akhir);
        $this->connector->AddParameter("?kas_acc_id", $this->KasAccId);
        $this->connector->AddParameter("?wkt_acc_id", $this->WktAccId);
        $this->connector->AddParameter("?pty_acc_id", $this->PtyAccId);
        $this->connector->AddParameter("?work_mode", $this->WorkMode);
		return $this->connector->ExecuteNonQuery();
	}

	public function Update($id) {
        if ($this->FLogo == null){
            $sql = 'UPDATE m_cabang SET work_mode = ?work_mode, pty_acc_id = ?pty_acc_id, kas_acc_id = ?kas_acc_id, wkt_acc_id = ?wkt_acc_id, jk1_mulai = ?jk1_mulai, jk1_akhir = ?jk1_akhir, jk2_mulai = ?jk2_mulai, jk2_akhir = ?jk2_akhir, price_inc_ppn = ?price_inc_ppn, kota = ?kota, npwp = ?npwp, norek = ?norek, notel = ?notel, allow_minus = ?allow_minus, cab_type = ?cab_type, company_id = ?company_id, area_id = ?area_id,	kode = ?kode, cabang = ?cabang,	alamat = ?alamat, pic = ?pic, nama_outlet = ?nama_outlet, raw_print_mode = ?raw_print_mode, raw_printer_name = ?raw_printer_name, updateby_id = ?updateby_id WHERE id = ?id';
        }else{
            $sql = 'UPDATE m_cabang SET work_mode = ?work_mode, pty_acc_id = ?pty_acc_id, kas_acc_id = ?kas_acc_id, wkt_acc_id = ?wkt_acc_id, jk1_mulai = ?jk1_mulai, jk1_akhir = ?jk1_akhir, jk2_mulai = ?jk2_mulai, jk2_akhir = ?jk2_akhir, price_inc_ppn = ?price_inc_ppn, kota = ?kota, npwp = ?npwp, norek = ?norek, notel = ?notel, allow_minus = ?allow_minus, cab_type = ?cab_type, company_id = ?company_id,	area_id = ?area_id,	kode = ?kode, cabang = ?cabang,	alamat = ?alamat, pic = ?pic, flogo = ?flogo, nama_outlet = ?nama_outlet, raw_print_mode = ?raw_print_mode, raw_printer_name = ?raw_printer_name, updateby_id = ?updateby_id WHERE id = ?id';
        }
		$this->connector->CommandText = $sql;
		$this->connector->AddParameter("?allow_minus", $this->AllowMinus);
		$this->connector->AddParameter("?cab_type", $this->CabType);
		$this->connector->AddParameter("?company_id", $this->CompanyId);
        $this->connector->AddParameter("?kode", $this->Kode);
        $this->connector->AddParameter("?area_id", $this->AreaId);
        $this->connector->AddParameter("?cabang", $this->Cabang);
        $this->connector->AddParameter("?alamat", $this->Alamat);
        $this->connector->AddParameter("?pic", $this->Pic);
		$this->connector->AddParameter("?id", $id);
        $this->connector->AddParameter("?flogo", $this->FLogo);
        $this->connector->AddParameter("?nama_outlet", $this->NamaCabang);
		$this->connector->AddParameter("?raw_print_mode", $this->RawPrintMode);
		$this->connector->AddParameter("?raw_printer_name", $this->RawPrinterName);
		$this->connector->AddParameter("?updateby_id", $this->UpdatebyId);
        $this->connector->AddParameter("?kota", $this->Kota);
        $this->connector->AddParameter("?npwp", $this->Npwp);
        $this->connector->AddParameter("?norek", $this->Norek);
        $this->connector->AddParameter("?notel", $this->Notel, "char");
        $this->connector->AddParameter("?price_inc_ppn", $this->PriceIncPpn);
        $this->connector->AddParameter("?jk1_mulai", $this->Jk1Mulai);
        $this->connector->AddParameter("?jk1_akhir", $this->Jk1Akhir);
        $this->connector->AddParameter("?jk2_mulai", $this->Jk2Mulai);
        $this->connector->AddParameter("?jk2_akhir", $this->Jk2Akhir);
        $this->connector->AddParameter("?kas_acc_id", $this->KasAccId);
        $this->connector->AddParameter("?wkt_acc_id", $this->WktAccId);
        $this->connector->AddParameter("?pty_acc_id", $this->PtyAccId);
        $this->connector->AddParameter("?work_mode", $this->WorkMode);
		return $this->connector->ExecuteNonQuery();
	}

	public function Delete($id) {
		$this->connector->CommandText = "UPDATE m_cabang SET is_deleted = 1 WHERE id = ?id";
		$this->connector->AddParameter("?id", $id);
		return $this->connector->ExecuteNonQuery();
	}

	public function GetJSonCabangs($companyId = 0) {
		$sql = "SELECT a.id,a.kode,a.cabang,b.company_code,a.cab_type FROM m_cabang as a Join sys_company as b On a.company_id = b.id";
		if ($companyId > 0) {
			$sql.= " Where a.company_id = " . $companyId;
		}
		$this->connector->CommandText = $sql;
		$data['count'] = $this->connector->ExecuteQuery()->GetNumRows();
		$sql.= " Order By a.kode";
		$this->connector->CommandText = $sql;
		$rows = array();
		$rs = $this->connector->ExecuteQuery();
		while ($row = $rs->FetchAssoc()){
			$rows[] = $row;
		}
		$result = array('total'=>$data['count'],'rows'=>$rows);
		return $result;
	}

	public function GetComboJSonCabangs($companyId = 0) {
		$sql = "SELECT a.id,a.kode,a.cabang,b.company_code,a.cab_type FROM m_cabang as a Join sys_company as b On a.company_id = b.id";
		if ($companyId > 0) {
			$sql.= " Where a.company_id = " . $companyId;
		}
		$this->connector->CommandText = $sql;
		$sql.= " Order By a.kode";
		$this->connector->CommandText = $sql;
		$rows = array();
		$rs = $this->connector->ExecuteQuery();
		while ($row = $rs->FetchAssoc()){
			$rows[] = $row;
		}
		$result = $rows;
		return $result;
	}

	public function IsAllowLogin($cabId){
	    //cek aturan caban ini
        $this->connector->CommandText = "SELECT a.jk1_mulai,a.jk1_akhir,a.jk2_mulai,a.jk2_akhir,a.work_mode FROM m_cabang AS a WHERE a.id = ?id";
        $this->connector->AddParameter("?id", $cabId);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return false;
        }
        $row = $rs->FetchAssoc();
        //jika tidak diatur (boleh)
        if ($row["work_mode"] == 0){
            return true;
        }
        $dow = date('w');
        //check hari minggu atau libur
        if ($dow == 0){
            return false;
        }else{
            $sql = "Select a.* From m_libur a Where a.tgl_libur = '".date('Y-m-d')."'";
            $this->connector->CommandText = $sql;
            $rs = $this->connector->ExecuteQuery();
            if ($rs->GetNumRows() > 0) {
                return false;
            }
        }
        $now = new DateTime("now");
        $std = null;
        $end = null;
        if ($dow > 0 && $dow < 6){
            //senin 1 - jumat 5
            $std = new DateTime($row["jk1_mulai"]);
            $end = new DateTime($row["jk1_akhir"]);
        }else{
            //sabtu 6
            $std = new DateTime($row["jk2_mulai"]);
            $end = new DateTime($row["jk2_akhir"]);
        }
        if($now >= $std && $now <= $end){
            return true;
        } else {
            return false;
        }
    }
}
