<?php
class ReportController extends AppController {
    private $userCompanyId;
    private $userCabangId;
    private $userLevel;
    private $trxMonth;
    private $trxYear;
    private $userCabIds;

    protected function Initialize() {
        require_once(MODEL . "ar/report.php");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userLevel = $this->persistence->LoadState("user_lvl");
        $this->trxMonth = $this->persistence->LoadState("acc_month");
        $this->trxYear = $this->persistence->LoadState("acc_year");
        $this->userCabIds = $this->persistence->LoadState("user_allow_cabids");
    }

    public function rekap(){
        require_once(MODEL . "ar/customer.php");
        require_once(MODEL . "master/company.php");
        require_once(MODEL . "master/cabang.php");
        require_once(MODEL . "master/warehouse.php");
        // Intelligent time detection...
        $month = (int)date("n");
        $year = (int)date("Y");
        $loader = null;
        if (count($this->postData) > 0) {
            // proses rekap disini
            $sCabangId = $this->GetPostValue("CabangId");
            $sCustomersId = $this->GetPostValue("CustomersId");
            $sStartDate = strtotime($this->GetPostValue("StartDate"));
            $sEndDate = strtotime($this->GetPostValue("EndDate"));
            $sOutput = $this->GetPostValue("Output");
            $report = new Report();
            $reports = $report->Load4Reports($sCabangId,$sCustomersId,$sStartDate,$sEndDate,$this->userCabIds);
        }else{
            $sCabangId = $this->userCabangId;
            $sStartDate = mktime(0, 0, 0, $month, 1, $year);
            $sEndDate = time();
            $sCustomersId = 0;
            $sOutput = 0;
            $reports = null;
        }
        $customer = new Customer();
        $customer = $customer->LoadAll();
        $loader = new Company($this->userCompanyId);
        $this->Set("company_name", $loader->CompanyName);
        //load data cabang
        $loader = new Cabang();
        $cabang = $loader->LoadAllowedCabId($this->userCabIds);
        $cabCode = null;
        $cabName = null;
        // kirim ke view
        $this->Set("StartDate",$sStartDate);
        $this->Set("EndDate",$sEndDate);
        $this->Set("cabangs", $cabang);
        $this->Set("customers",$customer);
        $this->Set("CabangId",$sCabangId);
        $this->Set("CustomersId",$sCustomersId);
        $this->Set("Output",$sOutput);
        $this->Set("Reports",$reports);
        $this->Set("userCabId",$this->userCabangId);
        $this->Set("userCabCode",$cabCode);
        $this->Set("userCabName",$cabName);
        $this->Set("userLevel",$this->userLevel);
    }

    public function rekap_aging() {
        if (count($this->getData) > 0) {
            $date = strtotime($this->GetGetValue("date") . " 23:59:59");
            $output = $this->GetGetValue("output");

            // Cara mencari aging = Cari Invoice berserta pembayarannya Jika belum lunas bearti piutang
            // NOTE: Query ini mirip dengan yang ada pada Invoice::LoadUnPaidInvoice()
            // Tetapi query di model tidak digunakan karena yang ini akan lebih ajaib hehehe
            // ToDo: Jika perubahan data pada model Invoice::LoadUnPaidInvoice() harus cek ini juga.

            // Step #01: Buat table temporary untuk menyimpan data Invoice belum lunasnya
            $this->connector->CommandText =
                "CREATE TEMPORARY TABLE unpaid_invoices AS
SELECT a.*, a.total_amount - COALESCE(c.sum_allocated, 0) AS sum_piutang, c.sum_allocated AS sum_paid, DATEDIFF(?date, a.invoice_date) AS age
FROM vw_ar_invoice_master AS a
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui OR baik yang sudah posting ! (dan juga ada batas max tanggal OR)
		-- ToDo: Jika status posting pada OR berubah yang ini juga harus ikut dirubah
		SELECT bb.invoice_id, SUM(bb.allocate_amount) AS sum_allocated
		FROM t_ar_receipt_master AS aa
			JOIN t_ar_receipt_detail AS bb ON aa.id = bb.receipt_id
		WHERE aa.is_deleted = 0 AND aa.receipt_status = 2 AND aa.receipt_date <= ?date
		GROUP BY bb.invoice_id
	) AS c ON a.id = c.invoice_id
-- Untuk mencari invoice yang belum lunas hanya yang sudah berstatus posting
-- ToDo: Jika status posted pada invoice berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.invoice_status = 2 AND (a.total_amount) - COALESCE(c.sum_allocated, 0) > 0 AND a.company_id = ?sbu;";
            $this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
            $this->connector->AddParameter("?sbu", $this->userCompanyId);

            $rs = $this->connector->ExecuteNonQuery();
            if ($rs == -1) {
                throw new \Exception("Error rekap_aging ! Step #01: temp table invoice belum lunas. Message: " . $this->connector->GetErrorMessage());
            }


            $this->connector->CommandText =
                "SELECT a.*, b.sum_piutang_1, b.sum_piutang_2, b.sum_piutang_3, b.sum_piutang_4, b.sum_piutang_5, b.sum_piutang_6
FROM m_customer AS a JOIN m_cabang a1 ON a.cabang_id = a1.id
	LEFT JOIN (
		SELECT aa.customer_id, SUM(IF(aa.age BETWEEN 0 AND 30, aa.sum_piutang, 0)) AS sum_piutang_1, SUM(IF(aa.age BETWEEN 31 AND 60, aa.sum_piutang, 0)) AS sum_piutang_2, SUM(IF(aa.age BETWEEN 61 AND 90, aa.sum_piutang, 0)) AS sum_piutang_3, SUM(IF(aa.age BETWEEN 91 AND 120, aa.sum_piutang, 0)) AS sum_piutang_4, SUM(IF(aa.age BETWEEN 121 AND 150, aa.sum_piutang, 0)) AS sum_piutang_5, SUM(IF(aa.age > 150, aa.sum_piutang, 0)) AS sum_piutang_6
		FROM unpaid_invoices AS aa
		GROUP BY aa.customer_id
	) AS b ON a.id = b.customer_id
WHERE a1.company_id = ?sbu
ORDER BY a.cus_name ASC;";

            $report = $this->connector->ExecuteQuery();
        } else {
            $date = time();
            $output = "web";
            $report = null;
        }

        require_once(MODEL . "master/company.php");
        $company = new Company();

        $this->Set("date", $date);
        $this->Set("output", $output);
        $this->Set("company", $company->LoadById($this->userCompanyId));
        $this->Set("report", $report);
    }

    public function detail_aging() {
        require_once(MODEL . "ar/customer.php");

        $customer =  new Customer();

        if (count($this->getData) > 0) {
            $customerId = $this->GetGetValue("debtorId");
            $date = strtotime($this->GetGetValue("date") . " 23:59:59");
            $output = $this->GetGetValue("output");

            // Ini query detail sih mirip dengan yang rekap bedanya tidak ada proses grouping

            $query =
                "SELECT a.*, c.sum_allocated AS sum_paid, DATEDIFF(?date, a.invoice_date) AS age
FROM vw_ar_invoice_master AS a
	LEFT JOIN (
		-- Cari jumlah pembayaran melalui OR baik yang sudah posting ! (dan juga ada batas max tanggal OR)
		-- ToDo: Jika status posting pada OR berubah yang ini juga harus ikut dirubah
		SELECT bb.invoice_id, SUM(bb.allocate_amount) AS sum_allocated
		FROM t_ar_receipt_master AS aa
			JOIN t_ar_receipt_detail AS bb ON aa.id = bb.receipt_id
		WHERE aa.is_deleted = 0 AND aa.receipt_status = 2 AND aa.receipt_date <= ?date
		GROUP BY bb.invoice_id
	) AS c ON a.id = c.invoice_id
	-- JOIN dengan customer untuk cari nama dll
-- Untuk mencari invoice yang belum lunas hanya yang sudah berstatus posting
-- ToDo: Jika status posted pada invoice berubah yang ini juga harus dirubah
WHERE a.is_deleted = 0 AND a.invoice_status = 2 AND a.total_amount - COALESCE(c.sum_allocated, 0) > 0 AND a.company_id = ?sbu %s
ORDER BY a.customer_name ASC, a.invoice_date ASC;";
            if ($customerId != null) {
                // Mari Kita Filter per customer juga
                $this->connector->CommandText = sprintf($query, " AND a.customer_id = ?customerId");
                $this->connector->AddParameter("?customerId", $customerId);
            } else {
                $this->connector->CommandText = sprintf($query, "");
            }
            $this->connector->AddParameter("?date", date(SQL_DATETIME, $date));
            $this->connector->AddParameter("?sbu", $this->userCompanyId);

            $report = $this->connector->ExecuteQuery();
        } else {
            $customerId = null;
            $date = time();
            $output = "web";
            $report = null;

            if ($this->userCompanyId == 7 | $this->userCompanyId == null) {
                $this->Set("info", "Laporan ini hanya untuk login Company. Anda login CORP harap melakukan inpersonate terlebih dahulu.");
            }
        }

        require_once(MODEL . "master/company.php");
        $company = new \Company();

        $this->Set("customerId", $customerId);
        $this->Set("debtors", $customer->LoadAll());
        $this->Set("date", $date);
        $this->Set("output", $output);
        $this->Set("company", $company->LoadById($this->userCompanyId));
        $this->Set("report", $report);
    }
}


// End of File: invoice_controller.php
