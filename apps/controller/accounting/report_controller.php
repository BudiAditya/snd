<?php

namespace Accounting;

/**
 * Untuk membuat laporan-laporan accounting
 *
 * Class ReportController
 * @package Accounting
 */
class ReportController extends \AppController {
	private $userCompanyId;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("company_id");
	}

	/**
	 * Akan membuat laporan jurnal voucher berdasarkan jenis-jenis dokumen yang dipilih.
	 */
	public function journal() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "accounting/voucher_type.php");
        require_once(MODEL . "master/cabang.php");

		if (count($this->getData) > 0) {
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
            $cabangId = $this->GetGetValue("idCabang");
			$docIds = $this->GetGetValue("docType", array());
			$status = $this->GetGetValue("status");
			$output = $this->GetGetValue("output", "web");
			$showNo = $this->GetGetValue("showNo", "0") == "1";
			//$showAdditionalColumn = $this->GetGetValue("showCol", "0") == "1";
			$orientation = strtoupper($this->GetGetValue("orientation", "p"));
			if (!in_array($orientation, array("P", "L"))) {
				$orientation = "P";
			}
			if (count($docIds) == 0) {
				$this->persistence->SaveState("error", "Mohon pilih jenis dokumen terlebih dahulu. Sekurang-kurangnya pilih 1.");
				redirect_url("accounting.report/journal");
				return;
			}
			// OK bikin querynya... (Untuk Bank masuk dilihat dari jenis dokumen BM dan OR)
			$query =
                "SELECT a.id, b.journal_date, b.journal_no, a.keterangan, b.company_id, a.acc_id,a.acc_code,a.acc_name,a.db_amount,a.cr_amount,b.journal_descs,b.reff_no,a.cabang_code
                FROM vw_ac_journal_detail AS a JOIN vw_ac_journal_master AS b ON a.journal_id = b.id JOIN sys_doc_type c ON b.trx_code = c.trx_code
                WHERE b.is_deleted = 0 AND b.journal_date BETWEEN ?start AND ?end AND c.id IN ?docTypes %s";
                if ($cabangId > 0){
                    $query.= " And b.cabang_id = ?cabangId";
                }
                $query.= " ORDER BY b.journal_date, b.journal_no";

			$extendedWhere = "";
			if ($this->userCompanyId != 1 && $this->userCompanyId != null) {
				$extendedWhere .= "  AND b.company_id = ?sbu";
				$this->connector->AddParameter("?sbu", $this->userCompanyId);
			}
			if ($status > -1) {
				$extendedWhere .= " AND b.journal_status = ?status";
				$this->connector->AddParameter("?status", $status);
			}
			$this->connector->CommandText = sprintf($query, $extendedWhere);
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
			$this->connector->AddParameter("?cabangId", $cabangId);
            $this->connector->AddParameter("?docTypes", $docIds);
			$report = $this->connector->ExecuteQuery();
		} else {
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
            $cabangId = null;
			$docIds = array();
			$status = -1;
			$showNo = true;
			//$showAdditionalColumn = false;
			$report = null;
			$output = "web";
			$orientation = "P";

			if ($this->persistence->StateExists("error")) {
				$this->Set("error", $this->persistence->LoadState("error"));
				$this->persistence->DestroyState("error");
			}
		}

		$company = new \Company();
		$company = $company->LoadById($this->userCompanyId);
		$docType = new \DocType();
		$vocType = new \VoucherType();
        $cabang = new \Cabang();
        $this->Set("cabangList", $cabang->LoadByCompanyId($this->userCompanyId));
        $this->Set("idCabang", $cabangId);
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("docTypes", $docType->LoadHaveVoucher());
		$this->Set("vocTypes", $vocType->LoadAll());
		$this->Set("docIds", $docIds);
		$this->Set("status", $status);
		$this->Set("showNo", $showNo);
		//$this->Set("showCol", $showAdditionalColumn);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("orientation", $orientation);
		$this->Set("company", $company);
	}

	public function recap() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "common/doc_type.php");
		require_once(MODEL . "accounting/voucher_type.php");
        require_once(MODEL . "master/cabang.php");

		if (count($this->getData) > 0) {
			$month = $this->GetGetValue("month");
			$year = $this->GetGetValue("year");
			$docIds = $this->GetGetValue("docType", array());
            $cabangId = $this->GetGetValue("idCabang");
			$status = $this->GetGetValue("status");
			$output = $this->GetGetValue("output", "web");
			$orientation = strtoupper($this->GetGetValue("orientation", "p"));
			if (!in_array($orientation, array("P", "L"))) {
				$orientation = "P";
			}
			if (count($docIds) == 0) {
				$this->persistence->SaveState("error", "Mohon pilih jenis dokumen terlebih dahulu. Sekurang-kurangnya pilih 1.");
				redirect_url("accounting.report/recap");
				return;
			}

			$firstJanuary = mktime(0,0, 0, 1, 1, $year);
			$startDate = mktime(0, 0, 0, $month, 1, $year);
			$endDate = mktime(0,0, 0, $month + 1, 0, $year);	// Bulan berikutnya kurangin 1 (pake tehnik tanggal 0 == hari sebelumnya)

			// Setting global parameter (Jgn panggil ClearParameters() OK !)
			$this->connector->AddParameter("?status", $status);
			$this->connector->AddParameter("?sbu", $this->userCompanyId);
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			$this->connector->AddParameter("?docIds", $docIds);
            $this->connector->AddParameter("?cabangId", $cabangId);
			if ($month > 1) {
				// Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
				$this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
				$this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
			}

			// OK dafuq ini... mari kita query multi step
			// Disini kita akan mengambil data dari semua dokumen yang diminta user ($docIds)

			// #01: Ambil sum semua debit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_debit AS SELECT a.acc_code, SUM(a.db_amount) AS total_debit FROM vw_ac_journal_detail AS a JOIN vw_ac_journal_master b ON a.journal_id = b.id LEFT JOIN sys_doc_type c ON b.trx_code = c.trx_code
	WHERE [status] [company] [cabang_id] b.is_deleted = 0 AND b.journal_date BETWEEN ?start AND ?end AND c.id IN ?docIds
GROUP BY a.acc_code;";
            if ($cabangId > 0){
               $query = str_replace("[cabang_id]", "b.cabang_id = ?cabangId AND", $query);
            }else{
                $query = str_replace("[cabang_id]", "", $query);
            }
			if ($this->userCompanyId == 1 || $this->userCompanyId == null) {
				$query = str_replace("[company]", "", $query);
			} else {
				$query = str_replace("[company]", "b.company_id = ?sbu AND", $query);
			}
			if ($status == -1) {
				$query = str_replace("[status]", "", $query);
			} else {
				$query = str_replace("[status]", "b.journal_status = ?status AND", $query);
			}
			$this->connector->CommandText = $query;
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua credit pada periode yang diminta
			$query =
"CREATE TEMPORARY TABLE sum_credit AS SELECT a.acc_code, SUM(a.cr_amount) AS total_credit FROM vw_ac_journal_detail AS a JOIN vw_ac_journal_master b ON a.journal_id = b.id LEFT JOIN sys_doc_type c ON b.trx_code = c.trx_code
	WHERE [status] [company] [cabang_id] b.is_deleted = 0 AND b.journal_date BETWEEN ?start AND ?end AND c.id IN ?docIds
GROUP BY a.acc_code;";
            if ($cabangId > 0){
                $query = str_replace("[cabang_id]", "b.cabang_id = ?cabangId AND", $query);
            }else{
                $query = str_replace("[cabang_id]", "", $query);
            }
			if ($this->userCompanyId == 1 || $this->userCompanyId == null) {
				$query = str_replace("[company]", "", $query);
			} else {
				$query = str_replace("[company]", "b.company_id = ?sbu AND", $query);
			}
			if ($status == -1) {
				$query = str_replace("[status]", "", $query);
			} else {
				$query = str_replace("[status]", "b.journal_status = ?status AND", $query);
			}
			$this->connector->CommandText = $query;
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #03: Ambil data bulan-bulan sebelumnya (debet)
				$query =
"CREATE TEMPORARY TABLE sum_debit_prev AS SELECT a.acc_code, SUM(a.db_amount) AS total_debit_prev FROM vw_ac_journal_detail AS a JOIN vw_ac_journal_master b ON a.journal_id = b.id LEFT JOIN sys_doc_type c ON b.trx_code = c.trx_code
	WHERE [status] [company] [cabang_id] b.is_deleted = 0 AND b.journal_date BETWEEN ?firstJan AND ?prev AND c.id IN ?docIds
GROUP BY a.acc_code;";
                if ($cabangId > 0){
                    $query = str_replace("[cabang_id]", "b.cabang_id = ?cabangId AND", $query);
                }else{
                    $query = str_replace("[cabang_id]", "", $query);
                }
				if ($this->userCompanyId == 1 || $this->userCompanyId == null) {
					$query = str_replace("[company]", "", $query);
				} else {
					$query = str_replace("[company]", "b.company_id = ?sbu AND", $query);
				}
				if ($status == -1) {
					$query = str_replace("[status]", "", $query);
				} else {
					$query = str_replace("[status]", "b.journal_status = ?status AND", $query);
				}
				$this->connector->CommandText = $query;
				$this->connector->ExecuteNonQuery();

				// #04: Ambil data bulan-bulan sebelumnya (kredit)
				$query =
"CREATE TEMPORARY TABLE sum_credit_prev AS SELECT a.acc_code, SUM(a.cr_amount) AS total_credit_prev FROM vw_ac_journal_detail AS a JOIN vw_ac_journal_master b ON a.journal_id = b.id LEFT JOIN sys_doc_type c ON b.trx_code = c.trx_code
	WHERE [status] [company] [cabang_id] b.is_deleted = 0 AND b.journal_date BETWEEN ?firstJan AND ?prev AND c.id IN ?docIds
GROUP BY a.acc_code;";
                if ($cabangId > 0){
                    $query = str_replace("[cabang_id]", "b.cabang_id = ?cabangId AND", $query);
                }else{
                    $query = str_replace("[cabang_id]", "", $query);
                }
				if ($this->userCompanyId == 1 || $this->userCompanyId == null) {
					$query = str_replace("[company]", "", $query);
				} else {
					$query = str_replace("[company]", "b.company_id = ?sbu AND", $query);
				}
				if ($status == -1) {
					$query = str_replace("[status]", "", $query);
				} else {
					$query = str_replace("[status]", "b.journal_status = ?status AND", $query);
				}
				$this->connector->CommandText = $query;
				$this->connector->ExecuteNonQuery();

				// #05: OK final query...
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev
FROM m_account AS a
	LEFT JOIN sum_debit AS b ON a.kode = b.acc_code
	LEFT JOIN sum_credit AS c ON a.kode = c.acc_code
	LEFT JOIN sum_debit_prev AS d ON a.kode = d.acc_code
	LEFT JOIN sum_credit_prev AS e ON a.kode = e.acc_code
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) + COALESCE(d.total_debit_prev, 0) + COALESCE(e.total_credit_prev, 0) <> 0 And a.company_id = ".$this->userCompanyId."
ORDER BY a.kode";
			} else {
				// Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
				// Untuk data bulan-bulan sebelumnya selalu 0
				$this->connector->CommandText =
"SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev
FROM m_account AS a
	LEFT JOIN sum_debit AS b ON a.kode = b.acc_code
	LEFT JOIN sum_credit AS c ON a.kode = c.acc_code
WHERE COALESCE(b.total_debit, 0) + COALESCE(c.total_credit, 0) <> 0 And a.company_id = ".$this->userCompanyId."
ORDER BY a.kode";
			}

			$report = $this->connector->ExecuteQuery();
		} else {
			$month = (int)date("n");
			$year = (int)date("Y");
			$docIds = array();
            $cabangId = null;
			$status = -1;
			$report = null;
			$output = "web";
			$orientation = "P";

			if ($this->persistence->StateExists("error")) {
				$this->Set("error", $this->persistence->LoadState("error"));
				$this->persistence->DestroyState("error");
			}
		}

		$company = new \Company();
		$company = $company->LoadById($this->userCompanyId);

		$docType = new \DocType();
		$vocType = new \VoucherType();
        $cabang = new \Cabang();
        $this->Set("cabangList", $cabang->LoadByCompanyId($this->userCompanyId));
        $this->Set("idCabang", $cabangId);
		$this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("docTypes", $docType->LoadHaveVoucher());
		$this->Set("vocTypes", $vocType->LoadAll());
		$this->Set("docIds", $docIds);
		$this->Set("status", $status);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("orientation", $orientation);
		$this->Set("company", $company);

		$this->Set("monthNames", array(1 => "Januari", "Febuari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
	}
}

// EoF: report_controller.php