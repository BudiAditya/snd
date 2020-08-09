<?php

class Report extends EntityBase {

    public function Load4Reports($cabangId = 0, $customerId = 0, $stDate,$enDate, $cabIds = null) {
        $stDate = date('Y-m-d',$stDate);
        $enDate = date('Y-m-d',$enDate);
        $rs = 0;
        $sqx = null;
        // create previous mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_prev` (
                `idx`  tinyint(1) NULL DEFAULT 0 ,
                `trx_date`  date NULL ,
                `no_bukti`  varchar(50) NULL ,
                `customer`  varchar(100) NULL ,
                `keterangan`  varchar(100) NULL ,
                `invoice`  bigint(20) NULL DEFAULT 0 ,
                `retur`  bigint(20) NULL DEFAULT 0 ,
                `receipt`  bigint(20) NULL DEFAULT 0 ,
                `saldo`  bigint(20) NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // create request mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_mutasi` (
                `idx`  tinyint(1) NULL DEFAULT 0 ,
                `id`  bigint(20) NULL DEFAULT 0,
                `trx_date`  date NULL ,
                `no_bukti`  varchar(50) NULL ,
                `customer`  varchar(100) NULL ,
                `keterangan`  varchar(100) NULL ,
                `invoice`  bigint(20) NULL DEFAULT 0 ,
                `retur`  bigint(20) NULL DEFAULT 0 ,
                `receipt`  bigint(20) NULL DEFAULT 0 ,
                `saldo`  bigint(20) NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get invoice lalu
        $sqx = "Insert Into tmp_prev (idx,trx_date,invoice)";
        $sqx.= " Select 0,'" . $stDate ."', coalesce(sum(a.total_amount),0) as sum_invoice From vw_ar_invoice_master a Where a.invoice_date < '" . $stDate ."' And a.invoice_status < 3";
        if ($customerId > 0){
            $sqx.= " And a.customer_id = ".$customerId;
        }
        if ($cabangId > 0){
            $sqx.= " And a.cabang_id = ".$cabangId;
        }else{
            $sqx.= " And a.cabang_id IN (".$cabIds.")";
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get return lalu
        $sqx = "Insert Into tmp_prev (idx,trx_date,retur)";
        $sqx.= " Select 0,'" . $stDate ."', coalesce(sum(a.rj_amount),0) as sum_return From vw_ar_return_master a Where a.rj_date < '" . $stDate ."' And a.rj_status < 3";
        if ($customerId > 0){
            $sqx.= " And a.customer_id = ".$customerId;
        }
        if ($cabangId > 0){
            $sqx.= " And a.cabang_id = ".$cabangId;
        }else{
            $sqx.= " And a.cabang_id IN (".$cabIds.")";
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get penerimaan lalu
        $sqx = "Insert Into tmp_prev (idx,trx_date,receipt)";
        $sqx.= " Select 0,'" . $stDate ."', coalesce(sum(a.receipt_amount),0) as sum_receipt From vw_ar_receipt_master a Where a.receipt_date < '" . $stDate ."' And a.receipt_status < 3";
        if ($customerId > 0){
            $sqx.= " And a.debtor_id = ".$customerId;
        }
        if ($cabangId > 0){
            $sqx.= " And a.cabang_id = ".$cabangId;
        }else{
            $sqx.= " And a.cabang_id IN (".$cabIds.")";
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //jadikan saldo awalcas
        $sqx = "Insert Into tmp_mutasi (idx,trx_date,customer,saldo)";
        $sqx.= " Select 0,'" . $stDate ."','Saldo sebelumnya..',coalesce(sum(a.invoice - (a.retur + a.receipt)),0) as sum_saldo From tmp_prev a Group By a.trx_date";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get invoice bulan diminta
        $sqx = "Insert Into tmp_mutasi (idx,id,trx_date,no_bukti,customer,invoice)";
        $sqx.= " Select 1,a.id,a.invoice_date,a.invoice_no,a.customer_name,a.total_amount From vw_ar_invoice_master a Where (a.invoice_date >= '". $stDate . "' And a.invoice_date <= '". $enDate ."') And a.invoice_status < 3";
        if ($customerId > 0){
            $sqx.= " And a.customer_id = ".$customerId;
        }
        if ($cabangId > 0){
            $sqx.= " And a.cabang_id = ".$cabangId;
        }else{
            $sqx.= " And a.cabang_id IN (".$cabIds.")";
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get return bulan diminta
        $sqx = "Insert Into tmp_mutasi (idx,id,trx_date,no_bukti,customer,retur)";
        $sqx.= " Select 2,a.id,a.rj_date,a.rj_no,a.customer_name,a.rj_amount From vw_ar_return_master a Where (a.rj_date >= '". $stDate . "' And a.rj_date <= '". $enDate ."') And a.rj_status < 3";
        if ($customerId > 0){
            $sqx.= " And a.customer_id = ".$customerId;
        }
        if ($cabangId > 0){
            $sqx.= " And a.cabang_id = ".$cabangId;
        }else{
            $sqx.= " And a.cabang_id IN (".$cabIds.")";
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get receipt bulan diminta
        $sqx = "Insert Into tmp_mutasi (idx,id,trx_date,no_bukti,customer,receipt)";
        $sqx.= " Select 3,a.id,a.receipt_date,a.receipt_no,a.debtor_name,a.receipt_amount From vw_ar_receipt_master a Where(a.receipt_date >= '". $stDate . "' And a.receipt_date <= '". $enDate ."') And a.receipt_status < 3";
        if ($customerId > 0){
            $sqx.= " And a.debtor_id = ".$customerId;
        }
        if ($cabangId > 0){
            $sqx.= " And a.cabang_id = ".$cabangId;
        }else{
            $sqx.= " And a.cabang_id IN (".$cabIds.")";
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //ambil datanya
        $sqx = "Select a.* From tmp_mutasi a Order By trx_date,a.idx,no_bukti";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function getIvReceiptList ($orId){
        $sqx = "Select b.invoice_date as tanggal,b.due_date as jtp,b.invoice_no as no_bukti,a.allocate_amount as nilai";
        $sqx.= " From t_ar_receipt_detail a Join vw_ar_invoice_master b On a.invoice_id = b.id Where a.receipt_id = $orId Order By b.invoice_date,b.invoice_no";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function getOrInvoiceList ($ivId){
        $sqx = "Select b.receipt_date as tanggal,b.receipt_no as no_bukti,a.allocate_amount as nilai";
        $sqx.= " From t_ar_receipt_detail a Join vw_ar_receipt_master b On a.receipt_id = b.id Where a.invoice_id = $ivId Order By b.receipt_date,b.receipt_no";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function getIvoiceList ($cabId = 0,$csId = 0,$salesId = 0,$stLunas = -1,$stDate,$enDate){
        $sqx = "Select a.* From vw_ar_invoice_master a Where a.is_deleted = 0 And a.invoice_status <> 3 And a.payment_type = 1 And a.invoice_date Between ?stDate And ?enDate";
        if ($cabId > 0){
            $sqx.= " And a.cabang_id = $cabId";
        }
        if ($csId > 0){
            $sqx.= " And a.customer_id = $csId";
        }
        if ($salesId > 0){
            $sqx.= " And a.sales_id = $salesId";
        }
        if ($stLunas == 0){
            $sqx.= " And a.balance_amount > 5000";
        }elseif ($stLunas == 1){
            $sqx.= " And a.balance_amount < 5000";
        }
        $sqx.= " Order By a.invoice_date,a.invoice_no";
        $this->connector->CommandText = $sqx;
        $this->connector->AddParameter("?stDate", date('Y-m-d', $stDate));
        $this->connector->AddParameter("?enDate", date('Y-m-d', $enDate));
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
