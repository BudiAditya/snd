<?php

class Mutasi extends EntityBase {

    public function Load4Reports($cabangId = 0, $supplierId = 0, $stDate,$enDate) {
        $stDate = date('Y-m-d',$stDate);
        $enDate = date('Y-m-d',$enDate);
        $rs = 0;
        $sqx = null;
        // create previous mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_prev` (
                `idx`  tinyint(1) NULL DEFAULT 0 ,
                `trx_date`  date NULL ,
                `no_bukti`  varchar(50) NULL ,
                `supplier`  varchar(100) NULL ,
                `keterangan`  varchar(100) NULL ,
                `grn`  bigint(11) NULL DEFAULT 0 ,
                `retur`  int(11) NULL DEFAULT 0 ,
                `payment`  bigint(11) NULL DEFAULT 0 ,
                `saldo`  bigint(11) NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        // create request mutasi temp table
        $sqx = 'CREATE TEMPORARY TABLE `tmp_mutasi` (
                `idx`  tinyint(1) NULL DEFAULT 0 ,
                `trx_date`  date NULL ,
                `no_bukti`  varchar(50) NULL ,
                `supplier`  varchar(100) NULL ,
                `keterangan`  varchar(100) NULL ,
                `grn`  bigint(11) NULL DEFAULT 0 ,
                `retur`  int(11) NULL DEFAULT 0 ,
                `payment`  bigint(11) NULL DEFAULT 0 ,
                `saldo`  bigint(11) NULL DEFAULT 0)';
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get grn lalu
        $sqx = "Insert Into tmp_prev (idx,trx_date,grn)";
        $sqx.= " Select 0,'" . $stDate ."', coalesce(sum(a.total_amount),0) as sum_grn From vw_ap_purchase_master a Where a.grn_date < '" . $stDate ."' And a.grn_status < 3";
        if ($supplierId > 0){
            $sqx.= " And a.supplier_id = ".$supplierId;
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get return lalu
        $sqx = "Insert Into tmp_prev (idx,trx_date,retur)";
        $sqx.= " Select 0,'" . $stDate ."', coalesce(sum(a.rb_amount),0) as sum_return From vw_ap_return_master a Where a.rb_date < '" . $stDate ."' And a.rb_status < 3";
        if ($supplierId > 0){
            $sqx.= " And a.supplier_id = ".$supplierId;
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get penerimaan lalu
        $sqx = "Insert Into tmp_prev (idx,trx_date,payment)";
        $sqx.= " Select 0,'" . $stDate ."', coalesce(sum(a.payment_amount),0) as sum_payment From vw_ap_payment_master a Where a.payment_date < '" . $stDate ."' And a.payment_status < 3";
        if ($supplierId > 0){
            $sqx.= " And a.creditor_id = ".$supplierId;
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //jadikan saldo awalcas
        $sqx = "Insert Into tmp_mutasi (idx,trx_date,supplier,saldo)";
        $sqx.= " Select 0,'" . $stDate ."','Saldo sebelumnya..',coalesce(sum(a.grn - (a.retur + a.payment)),0) as sum_saldo From tmp_prev a Group By a.trx_date";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get grn bulan diminta
        $sqx = "Insert Into tmp_mutasi (idx,trx_date,no_bukti,supplier,grn)";
        $sqx.= " Select 1,a.grn_date,a.grn_no,a.supplier_name,a.total_amount From vw_ap_purchase_master a Where (a.grn_date >= '". $stDate . "' And a.grn_date <= '". $enDate ."') And a.grn_status < 3";
        if ($supplierId > 0){
            $sqx.= " And a.supplier_id = ".$supplierId;
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get return bulan diminta
        $sqx = "Insert Into tmp_mutasi (idx,trx_date,no_bukti,supplier,retur)";
        $sqx.= " Select 2,a.rb_date,a.rb_no,a.supplier_name,a.rb_amount From vw_ap_return_master a Where (a.rb_date >= '". $stDate . "' And a.rb_date <= '". $enDate ."') And a.rb_status < 3";
        if ($supplierId > 0){
            $sqx.= " And a.supplier_id = ".$supplierId;
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //get payment bulan diminta
        $sqx = "Insert Into tmp_mutasi (idx,trx_date,no_bukti,supplier,payment)";
        $sqx.= " Select 3,a.payment_date,a.payment_no,a.supplier_name,a.payment_amount From vw_ap_payment_master a Where(a.payment_date >= '". $stDate . "' And a.payment_date <= '". $enDate ."') And a.payment_status < 3";
        if ($supplierId > 0){
            $sqx.= " And a.creditor_id = ".$supplierId;
        }
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteNonQuery();
        //ambil datanya
        $sqx = "Select a.* From tmp_mutasi a Order By trx_date,a.idx,no_bukti";
        $this->connector->CommandText = $sqx;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}


// End of File: estimasi.php
