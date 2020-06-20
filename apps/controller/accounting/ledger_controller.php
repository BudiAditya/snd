<?php

/**
 * Untuk membuat beberapa laporan accounting yang diminta.
 * Ini berisi laporan bantuan dari buku besar. Detail untuk buku besar. Beberapa caranya mirip dengan cashflow
 *
 * @see CashFlowController
 */
class LedgerController extends AppController {
	private $userCompanyId;
    private $trxYear;

	protected function Initialize() {
		$this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->trxYear = $this->persistence->LoadState("acc_year");
	}

	public function recap() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coagroup.php");
        require_once(MODEL . "master/cabang.php");
        $sql = null;
		if (count($this->getData) > 0) {
			$noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
            $kodeInduk = $this->GetGetValue("kodeInduk");
            $cabangId = $this->GetGetValue("idCabang", 0);
            $month = $this->GetGetValue("Month");
			$year = $this->GetGetValue("Year");
			$status = $this->GetGetValue("DocStatus", 2);
			$noOfDay = $noOfDays[$month];
			if ($month == 2 && $year % 4 == 0) {
				$noOfDay = 29;	// Leap Year
			}
			$output = $this->GetGetValue("Output", "web");
			$firstJanuary = mktime(0,0, 0, 1, 1, $year);
			$startDate = mktime(0, 0, 0, $month, 1, $year);
			$endDate = mktime(0,0, 0, $month, $noOfDay, $year);

			// Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
			// Setting global parameter (Jgn panggil ClearParameters() OK !)
			$this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
			$this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
			$this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
            $this->connector->AddParameter("?cabang", $cabangId);
			if ($month > 1) {
				$this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
			}
			if ($status == -1) {
				$this->connector->AddParameter("?status", "a.journal_status", "int");	// Gw mau paksa agar querynya menjadi a.journal_status = a.journal_status (selalu true) bukan a.journal_status = 'a.journal_status'
			} else {
				if ($status > 0 && $status < 3) {
					$this->connector->AddParameter("?status", $status);
				} else {
					$this->connector->AddParameter("?status", 2);
				}
			}
			if ($status == 2) {
				$this->connector->AddParameter("?obStatus", 2);
			} else {
				$this->connector->AddParameter("?obStatus", "a.journal_status", "int");	// Gw mau paksa agar querynya menjadi a.journal_status = a.journal_status (selalu true) bukan a.journal_status = 'a.journal_status'
				//$this->Set("info", "Saldo awal akan menggunakan semua voucher karena anda tidak memilih status POSTED");
			}

			// OK dafuq ini... mari kita query multi step
			// #01: Filter account yang akan digunakan pada report
			$this->connector->CommandText =
            "CREATE TEMPORARY TABLE acc_id AS
            SELECT a.id, a.kode as acc_no, a.kd_induk, a.perkiraan as acc_name, b.psaldo FROM m_account a INNER JOIN m_lk_rekap_detail b ON a.kd_induk = b.kd_induk And a.company_id = ".$this->userCompanyId."
            WHERE a.is_deleted = 0 AND a.kd_induk = ?kodeInduk";
			$this->connector->AddParameter("?kodeInduk", $kodeInduk);
			$this->connector->ExecuteNonQuery();

			// #02: Ambil sum semua debit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_debit AS
            SELECT b.acc_code, SUM(b.db_amount) AS total_debit
            FROM vw_ac_journal_master AS a
                JOIN vw_ac_journal_detail AS b ON a.id = b.journal_id
            WHERE a.journal_status = ?status AND a.company_id = ".$this->userCompanyId." AND a.journal_date BETWEEN ?start AND ?end
                AND b.acc_code IN (SELECT acc_no FROM acc_id) ";
            if ($cabangId > 0){
                $sql.= " And a.cabang_id = ?cabang";
            }
            $sql.= " GROUP BY b.acc_code;";
			$this->connector->CommandText = $sql;
			$this->connector->ExecuteNonQuery();

			// #03: Ambil sum semua credit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_credit AS
            SELECT b.acc_code, SUM(b.cr_amount) AS total_credit
            FROM vw_ac_journal_master AS a
                JOIN vw_ac_journal_detail AS b ON a.id = b.journal_id
            WHERE a.journal_status = ?status AND a.company_id = ".$this->userCompanyId." AND a.journal_date BETWEEN ?start AND ?end
                AND b.acc_code IN (SELECT acc_no FROM acc_id)";
            if ($cabangId > 0){
                $sql.= " And a.cabang_id = ?cabang";
            }
            $sql.= " GROUP BY b.acc_code;";
			$this->connector->CommandText = $sql;
			$this->connector->ExecuteNonQuery();

			if ($month > 1) {
				// kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
				// #04: Ambil data bulan-bulan sebelumnya (debet)
                $sql = "CREATE TEMPORARY TABLE sum_debit_prev AS
                SELECT b.acc_code, SUM(b.db_amount) AS total_debit_prev
                FROM vw_ac_journal_master AS a
                    JOIN vw_ac_journal_detail AS b ON a.id = b.journal_id
                WHERE a.journal_status = ?obStatus AND a.company_id = ".$this->userCompanyId." AND a.journal_date BETWEEN ?firstJan AND ?prev
                    AND b.acc_code IN (SELECT acc_no FROM acc_id)";
                if ($cabangId > 0){
                    $sql.= " And a.cabang_id = ?cabang";
                }
                $sql.= " GROUP BY b.acc_code;";
				$this->connector->CommandText = $sql;
				$this->connector->ExecuteNonQuery();

				// #05: Ambil data bulan-bulan sebelumnya (kredit)
                $sql = "CREATE TEMPORARY TABLE sum_credit_prev AS
                SELECT b.acc_code, SUM(b.cr_amount) AS total_credit_prev
                FROM vw_ac_journal_master AS a
                    JOIN vw_ac_journal_detail AS b ON a.id = b.journal_id
                WHERE a.journal_status = ?obStatus AND a.company_id = ".$this->userCompanyId." AND a.journal_date BETWEEN ?firstJan AND ?prev
                    AND b.acc_code IN (SELECT acc_no FROM acc_id)";
                if ($cabangId > 0){
                    $sql.= " And a.cabang_id = ?cabang";
                }
                $sql.= " GROUP BY b.acc_code;";
				$this->connector->CommandText = $sql;
				$this->connector->ExecuteNonQuery();

				// #06: OK final query...
				$this->connector->CommandText =
                "SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev, f.db_amount as bal_debit_amt, f.cr_amount as bal_credit_amt
                FROM acc_id AS a
                    LEFT JOIN sum_debit AS b ON a.acc_no = b.acc_code
                    LEFT JOIN sum_credit AS c ON a.acc_no = c.acc_code
                    LEFT JOIN sum_debit_prev AS d ON a.acc_no = d.acc_code
                    LEFT JOIN sum_credit_prev AS e ON a.acc_no = e.acc_code
                    LEFT JOIN vw_ac_saldoawal AS f ON a.acc_no = f.acc_no AND op_date = ?firstJan
                ORDER BY a.acc_no";
			} else {
				// Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
				// Untuk data bulan-bulan sebelumnya selalu 0
				$this->connector->CommandText =
                "SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev, f.db_amount as bal_debit_amt, f.cr_amount as bal_credit_amt
                FROM acc_id AS a
                    LEFT JOIN sum_debit AS b ON a.acc_no = b.acc_code
                    LEFT JOIN sum_credit AS c ON a.acc_no = c.acc_code
                    LEFT JOIN vw_ac_saldoawal AS f ON a.acc_no = f.acc_no AND op_date = ?firstJan
                ORDER BY a.acc_no";
			}

			$report = $this->connector->ExecuteQuery();
		} else {
			$kodeInduk = null;
            $cabangId = null;
			$month = (int)date("n");
			$year = (int)date("Y");
			$status = 2;
			$report = null;
			$output = "web";
		}
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		$account = new CoaGroup();
        $cabang = new Cabang();
        $cabang = $cabang->LoadByCompanyId($this->userCompanyId);
        $this->Set("cabangList", $cabang);
        $this->Set("idCabang", $cabangId);
		$this->Set("parentAccounts", $account->LoadAll());
		$this->Set("kodeInduk", $kodeInduk);
        $this->Set("month", $month);
		$this->Set("year", $year);
		$this->Set("status", $status);
		switch ($status) {
			case -1:
				$this->Set("statusName", "SEMUA DOKUMEN");
				break;
			case 0:
				$this->Set("statusName", "DRAFT");
				break;
			case 1:
				$this->Set("statusName", "APPROVED");
				break;
			case 2:
				$this->Set("statusName", "VERIFIED");
				break;
			default:
				$this->Set("statusName", "N.A.");
				break;
		}
		$this->Set("report", $report);
		$this->Set("output", $output);

		$this->Set("company", $company);
		$this->Set("monthNames", array(1 => "Januari", "Febuari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
	}

	public function detail() {
		require_once(MODEL . "master/company.php");
		require_once(MODEL . "master/coadetail.php");
        require_once(MODEL . "master/coagroup.php");
		require_once(MODEL . "accounting/opening_balance.php");
        require_once(MODEL . "master/cabang.php");
        $openingBalance = null;
        $transaction = null;
        $report = null;
		if (count($this->getData) > 0) {
			$accountNo = $this->GetGetValue("account");
            $cabangId = $this->GetGetValue("idCabang");
			$start = strtotime($this->GetGetValue("start"));
			$end = strtotime($this->GetGetValue("end"));
			$status = $this->GetGetValue("status", 2);
			$output = $this->GetGetValue("output", "web");

			if ($accountNo == "") {
				// Ga pilih akun
				$this->Set("error", "Mohon pilih akun buku tambahan terlebih dahulu.");
			} else {
				// OK Data utama ada mari kita proses....
/* skip dulu
				$openingBalance = new OpeningBalance();
                if ($cabangId > 0){
                    $openingBalance->LoadByAccount($this->userCompanyId,$cabangId,$accountNo, date("Y", $start));
                }else {
                    $openingBalance->LoadByAccount($this->userCompanyId,0,$accountNo, date("Y", $start));
                }
				if ($openingBalance->Id == null && $openingBalance->GetCoa()->IsOpeningBalanceRequired()) {
					$this->Set("info", "Akun yang dipilih diharuskan memiliki Opening Balance tetapi data Tidak ditemukan !");
				}
				$temp = $start - 86400;
				if ($status == 2) {
					$transaction = $openingBalance->CalculateTransaction($temp, 2,$cabangId);
				} else {
					// Status dokumen bukan POSTED jadi kita FORCE menghitung semua transaksi
					$transaction = $openingBalance->CalculateTransaction($temp, -1,$cabangId);
					//$this->Set("info", "Saldo awal akan menggunakan semua voucher karena anda tidak memilih status POSTED");
				}
*/
				$query =
                "SELECT a.id, a.journal_date, a.journal_no, b.keterangan, b.acc_code, b.acc_name, b.db_amount,b.cr_amount, e.company_code, c.kode as kd_cabang, c.cabang
                FROM vw_ac_journal_master AS a
                    JOIN vw_ac_journal_detail AS b ON a.id = b.journal_id
                    JOIN m_cabang AS c ON a.cabang_id = c.id
                    JOIN sys_company AS e ON a.company_id = e.id
                WHERE a.journal_status = ?status AND a.journal_date BETWEEN ?start AND ?end AND (b.acc_code = ?accNo) And a.company_id = ".$this->userCompanyId;
                if ($cabangId > 0){
                    $query.= " And a.cabang_id = ?cabangId";
                }
                $query.= " ORDER BY a.journal_date, left(a.journal_no,2) DESC, a.journal_no";

				$this->connector->CommandText = $query;
				$this->connector->AddParameter("?start", date(SQL_DATETIME, $start));
				$this->connector->AddParameter("?end", date(SQL_DATETIME, $end));
				$this->connector->AddParameter("?accNo", $accountNo);
                $this->connector->AddParameter("?cabangId", $cabangId);
				if ($status == -1) {
					$this->connector->AddParameter("?status", "a.journal_status", "int");	// Gw mau paksa agar querynya menjadi a.journal_status = a.journal_status (selalu true) bukan a.journal_status = 'a.journal_status'
				} else {
					if ($status > 0 && $status < 5) {
						$this->connector->AddParameter("?status", $status);
					} else {
						$this->connector->AddParameter("?status", 4);
					}
				}

				$report = $this->connector->ExecuteQuery();
			}
		} else {
			$accountNo = null;
			$end = time();
			$start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$status = 4;
			$openingBalance = null;
			$transaction = null;
			$report = null;
            $cabangId  = null;
			$output = "web";
		}

		// Cari data login companynya
		$company = new Company();
		$company = $company->LoadById($this->userCompanyId);
		// OK cari data CoA
		$coagroup = new CoaGroup();
		$kodeInduks = array();
		foreach($coagroup->LoadAll() as $coagroups) {
			$kodeInduks[] = $coagroups->KdInduk;
		}
		$account = new CoaDetail();
        $accounts = $account->LoadAll($this->userCompanyId,0);
		$cabang = new Cabang();
        $this->Set("cabangList", $cabang->LoadByCompanyId($this->userCompanyId));
        $this->Set("idCabang", $cabangId);
		$this->Set("accountNo", $accountNo);
		$this->Set("accounts", $accounts);
		$this->Set("start", $start);
		$this->Set("end", $end);
		$this->Set("status", $status);
        switch ($status) {
            case -1:
                $this->Set("statusName", "SEMUA DOKUMEN");
                break;
            case 0:
                $this->Set("statusName", "DRAFT");
                break;
            case 1:
                $this->Set("statusName", "APPROVED");
                break;
            case 2:
                $this->Set("statusName", "VERIFIED");
                break;
            default:
                $this->Set("statusName", "N.A.");
                break;
        }
		$this->Set("openingBalance", $openingBalance);
		$this->Set("transaction", $transaction);
		$this->Set("report", $report);
		$this->Set("output", $output);
		$this->Set("company", $company);
	}

    public function costrevenue() {
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/coagroup.php");
        require_once(MODEL . "master/cabang.php");

        $sql = null;
        if (count($this->getData) > 0) {
            $noOfDays = array(-1, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

            $kodeInduk = $this->GetGetValue("kodeInduk");
            $cabangId = $this->GetGetValue("idCabang");
            $month = $this->GetGetValue("month");
            $year = $this->GetGetValue("year");
            $status = $this->GetGetValue("status", 4);
            $noOfDay = $noOfDays[$month];
            if ($month == 2 && $year % 4 == 0) {
                $noOfDay = 29;	// Leap Year
            }
            $output = $this->GetGetValue("output", "web");
            $firstJanuary = mktime(0,0, 0, 1, 1, $year);
            $startDate = mktime(0, 0, 0, $month, 1, $year);
            $endDate = mktime(0,0, 0, $month, $noOfDay, $year);

            // Hmm gw tau klo ini bisa dalam bentuk string secara langsung tapi gw prefer cara ini agar 'strong type'
            // Setting global parameter (Jgn panggil ClearParameters() OK !)
            $this->connector->AddParameter("?start", date(SQL_DATETIME, $startDate));
            $this->connector->AddParameter("?end", date(SQL_DATETIME, $endDate));
            $this->connector->AddParameter("?firstJan", date(SQL_DATETIME, $firstJanuary));
            $this->connector->AddParameter("?cabang", $cabangId);
            if ($month > 1) {
                $this->connector->AddParameter("?prev", date(SQL_DATETIME, $startDate - 1));
            }
            if ($status == -1) {
                $this->connector->AddParameter("?status", "a.journal_status", "int");	// Gw mau paksa agar querynya menjadi a.journal_status = a.journal_status (selalu true) bukan a.journal_status = 'a.journal_status'
            } else {
                if ($status > 0 && $status < 5) {
                    $this->connector->AddParameter("?status", $status);
                } else {
                    $this->connector->AddParameter("?status", 4);
                }
            }
            if ($status == 4) {
                $this->connector->AddParameter("?obStatus", 4);
            } else {
                $this->connector->AddParameter("?obStatus", "a.journal_status", "int");	// Gw mau paksa agar querynya menjadi a.journal_status = a.journal_status (selalu true) bukan a.journal_status = 'a.journal_status'
                //$this->Set("info", "Saldo awal akan menggunakan semua voucher karena anda tidak memilih status POSTED");
            }

            // OK dafuq ini... mari kita query multi step
            // #01: Filter account yang akan digunakan pada report
            if ($kodeInduk == '4'){
                $sql = "CREATE TEMPORARY TABLE acc_id AS
                SELECT a.id, a.kode as acc_no, a.kd_induk, a.perkiraan as acc_name, b.psaldo FROM m_account a INNER JOIN m_lk_rekap_detail b ON a.kd_induk = b.kd_induk
                WHERE a.is_deleted = 0 AND left(a.kode,1) = '4'";
            }elseif ($kodeInduk == '5'){
                $sql = "CREATE TEMPORARY TABLE acc_id AS
                SELECT a.id, a.kode as acc_no, a.kd_induk, a.perkiraan as acc_name, b.psaldo FROM m_account a INNER JOIN m_lk_rekap_detail b ON a.kd_induk = b.kd_induk
                WHERE a.is_deleted = 0 AND (left(a.kode,1) = '5' or left(a.kode,1) = '6')";
            }
            $this->connector->CommandText = $sql;
            //$this->connector->AddParameter("?kodeInduk", $kodeInduk);
            $this->connector->ExecuteNonQuery();

            // #02: Ambil sum semua debit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_debit AS
            SELECT b.acdebet_no, SUM(b.jumlah)AS total_debit
            FROM vw_ac_journal_master AS a
                JOIN vw_ac_journal_detail AS b ON a.journal_no = b.journal_no
            WHERE a.journal_status = ?status AND a.is_deleted = 0 AND a.journal_date BETWEEN ?start AND ?end
                AND b.acdebet_no IN (SELECT id FROM acc_id) ";
            if ($cabangId > 0){
                $sql.= " And b.cabang_id = ?cabang";
            }
            $sql.= " GROUP BY b.acdebet_no;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            // #03: Ambil sum semua credit pada periode yang diminta
            $sql = "CREATE TEMPORARY TABLE sum_credit AS
            SELECT b.ackredit_no, SUM(b.jumlah)AS total_credit
            FROM vw_ac_journal_master AS a
                JOIN vw_ac_journal_detail AS b ON a.journal_no = b.journal_no
            WHERE a.journal_status = ?status AND a.is_deleted = 0 AND a.journal_date BETWEEN ?start AND ?end
                AND b.ackredit_no IN (SELECT id FROM acc_id)";
            if ($cabangId > 0){
                $sql.= " And b.cabang_id = ?cabang";
            }
            $sql.= " GROUP BY b.ackredit_no;";
            $this->connector->CommandText = $sql;
            $this->connector->ExecuteNonQuery();

            if ($month > 1) {
                // kalau periode yang diminta bukan januari kita perlu data tambahan.... >_<
                // #04: Ambil data bulan-bulan sebelumnya (debet)
                $sql = "CREATE TEMPORARY TABLE sum_debit_prev AS
                SELECT b.acdebet_no, SUM(b.jumlah)AS total_debit_prev
                FROM vw_ac_journal_master AS a
                    JOIN vw_ac_journal_detail AS b ON a.journal_no = b.journal_no
                WHERE a.journal_status = ?obStatus AND a.is_deleted = 0 AND a.journal_date BETWEEN ?firstJan AND ?prev
                    AND b.acdebet_no IN (SELECT id FROM acc_id)";
                if ($cabangId > 0){
                    $sql.= " And b.cabang_id = ?cabang";
                }
                $sql.= " GROUP BY b.acdebet_no;";
                $this->connector->CommandText = $sql;
                $this->connector->ExecuteNonQuery();

                // #05: Ambil data bulan-bulan sebelumnya (kredit)
                $sql = "CREATE TEMPORARY TABLE sum_credit_prev AS
                SELECT b.ackredit_no, SUM(b.jumlah)AS total_credit_prev
                FROM vw_ac_journal_master AS a
                    JOIN vw_ac_journal_detail AS b ON a.journal_no = b.journal_no
                WHERE a.journal_status = ?obStatus AND a.is_deleted = 0 AND a.journal_date BETWEEN ?firstJan AND ?prev
                    AND b.ackredit_no IN (SELECT id FROM acc_id)";
                if ($cabangId > 0){
                    $sql.= " And b.cabang_id = ?cabang";
                }
                $sql.= " GROUP BY b.ackredit_no;";
                $this->connector->CommandText = $sql;
                $this->connector->ExecuteNonQuery();

                // #06: OK final query...
                $this->connector->CommandText =
                    "SELECT a.*, b.total_debit, c.total_credit, d.total_debit_prev, e.total_credit_prev, f.debet as bal_debit_amt, f.kredit as bal_credit_amt
                    FROM acc_id AS a
                        LEFT JOIN sum_debit AS b ON a.id = b.acdebet_no
                        LEFT JOIN sum_credit AS c ON a.id = c.ackredit_no
                        LEFT JOIN sum_debit_prev AS d ON a.id = d.acdebet_no
                        LEFT JOIN sum_credit_prev AS e ON a.id = e.ackredit_no
                        LEFT JOIN t_ac_saldoawal AS f ON a.id = acc_id AND op_date = ?firstJan
                    ORDER BY a.acc_no";
            } else {
                // Bulan periode yang diminta adalah januari jadi bisa langsung query total debet dan kredit
                // Untuk data bulan-bulan sebelumnya selalu 0
                $this->connector->CommandText =
                    "SELECT a.*, b.total_debit, c.total_credit, 0 AS total_debit_prev, 0 AS total_credit_prev, f.debet as bal_debit_amt, f.kredit as bal_credit_amt
                    FROM acc_id AS a
                        LEFT JOIN sum_debit AS b ON a.id = b.acdebet_no
                        LEFT JOIN sum_credit AS c ON a.id = c.ackredit_no
                        LEFT JOIN t_ac_saldoawal AS f ON a.id = acc_id AND op_date = ?firstJan
                    ORDER BY a.acc_no";
            }

            $report = $this->connector->ExecuteQuery();
        } else {
            $kodeInduk = null;
            $cabangId = null;
            $month = (int)date("n");
            $year = (int)date("Y");
            $status = 4;
            $report = null;
            $output = "web";
        }

        $company = new Company();
        $company = $company->LoadById($this->userCompanyId);
        $account = new CoaGroup();
        $cabang = new Cabang();
        $this->Set("cabangList", $cabang->LoadByEntityId($this->userCompanyId));
        $this->Set("idCabang", $cabangId);
        $this->Set("parentAccounts", $account->LoadAll());
        $this->Set("kodeInduk", $kodeInduk);
        $this->Set("month", $month);
        $this->Set("year", $year);
        $this->Set("status", $status);
        switch ($status) {
            case -1:
                $this->Set("statusName", "SEMUA DOKUMEN");
                break;
            case 0:
                $this->Set("statusName", "DRAFT");
                break;
            case 1:
                $this->Set("statusName", "APPROVED");
                break;
            case 2:
                $this->Set("statusName", "VERIFIED");
                break;
            default:
                $this->Set("statusName", "N.A.");
                break;
        }
        $this->Set("report", $report);
        $this->Set("output", $output);

        $this->Set("company", $company);
        $this->Set("monthNames", array(1 => "Januari", "Febuari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"));
    }

    public function revcostat() {
        //get revenue and cost summary by month
        require_once (MODEL . "accounting/journal.php");
        $journal = new Journal();
        $dataRevenues = $journal->GetRevenueSumByYear($this->trxYear);
        $this->Set("dataRevenues",$dataRevenues);
        $dataCosts = $journal->GetCostSumByYear($this->trxYear);
        $this->Set("dataCosts",$dataCosts);
        $this->Set("dataTahun",$this->trxYear);
    }
}

// End of file: bukutambahan_controller.php
