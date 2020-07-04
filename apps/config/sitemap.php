<?php
/**
 * Later this file will be automatically auto-generated...
 * Menu are stored in database but we create this file for faster menu creation
 */

// Load required library
require_once(LIBRARY . "node.php");

// This act as menu container
$root = new Node("[ROOT]");
$root->AddNode("HOME", "main");
$menu = $root->AddNode("PENJUALAN", null, "menu");
    $menu->AddNode("Relasi/Customer", null, "title");
        $menu->AddNode("Data Customer", "ar.customer");
        $menu->AddNode("Type Customer", "ar.custype");
        $menu->AddNode("Sales Area", "master.salesarea");
        $menu->AddNode("Salesman", "master.salesman");
        $menu->AddNode("Promo & Program", "ar.promo");
        $menu->AddNode("Saldo Awal Piutang", "ar.saldoawal");
    $menu->AddNode("Transaksi", null, "title");
        $menu->AddNode("Sales Order (SO)", "ar.order");
        $menu->AddNode("Invoice Penjualan", "ar.invoice");
        $menu->AddNode("Cetak Invoice", "ar.invoice/ivcprint");
        $menu->AddNode("Penerimaan Piutang", "ar.receipt");
        $menu->AddNode("Retur Penjualan", "ar.arreturn");
        $menu->AddNode("Penagihan Piutang", "ar.collect");
        //$menu->AddNode("Petty Cash", "cb.pettycash");
        //$menu->AddNode("Ganti Cashier", "fo.gantikasir");
    $menu->AddNode("Laporan", null, "title");
        $menu->AddNode("Statistik Penjualan", "ar.statistic");
        $menu->AddNode("Sales Order", "ar.order/report");
        $menu->AddNode("Penjualan", "ar.invoice/report");
        $menu->AddNode("Retur Penjualan", "ar.arreturn/report");
        $subMenu = $menu->AddNode("Kontrol Piutang", null, "submenu");
            $subMenu->AddNode("Penerimaan Piutang", "ar.receipt/report");
            $subMenu->AddNode("Rekapitulasi Piutang", "ar.report/rekap");
            $subMenu->AddNode("Rekap Umur Piutang", "ar.report/rekap_aging");
            $subMenu->AddNode("Detail Umur Piutang", "ar.report/detail_aging");
$menu = $root->AddNode("PEMBELIAN", null, "menu");
    $menu->AddNode("Data Relasi/Supplier", null, "title");
        $menu->AddNode("Data Supplier", "ap.supplier");
        $menu->AddNode("Type Supplier", "ap.suptype");
        $menu->AddNode("Saldo Awal Hutang", "ap.saldoawal");
    $menu->AddNode("Transaksi Pembelian", null, "title");
        $menu->AddNode("Purchase Order (PO)", "ap.order");
        $menu->AddNode("Pembelian Barang", "ap.purchase");
        $menu->AddNode("Pembayaran Hutang", "ap.payment");
        $menu->AddNode("Retur Pembelian", "ap.apreturn");
    $menu->AddNode("Laporan Pembelian", null, "title");
        $menu->AddNode("Statistik Pembelian", "ap.dashboard");
        $menu->AddNode("Laporan Order Pembelian", "ap.order/report");
        $menu->AddNode("Laporan Pembelian & Hutang", "ap.purchase/report");
        $menu->AddNode("Laporan Pembayaran Hutang", "ap.payment/report");
        $menu->AddNode("Laporan Retur Pembelian", "ap.apreturn/report");
        $menu->AddNode("Laporan Rekap Hutang", "ap.mutasi");
$menu = $root->AddNode("INVENTORY", null, "menu");
    $menu->AddNode("Master Data", null, "title");
        $subMenu = $menu->AddNode("Master Data Barang", null, "submenu");
            $subMenu->AddNode("Daftar Barang", "inventory.items");
            $subMenu->AddNode("Entitas Barang", "inventory.itementity");
            $subMenu->AddNode("Divisi Barang", "inventory.itemdivision");
            $subMenu->AddNode("Kategori Barang", "inventory.itemcategory");
            $subMenu->AddNode("Sub-Kategori Barang", "inventory.itemsubcategory");
            $subMenu->AddNode("Merk Barang", "inventory.itembrand");
            $subMenu->AddNode("Satuan Barang", "inventory.itemuom");
        $menu->AddNode("Daftar Harga", "inventory.itemprices");
        $menu->AddNode("Data Principal", "inventory.itemprincipal");
        $menu->AddNode("Data Expedisi", "inventory.expedition");
        $menu->AddNode("Data Gudang", "master.warehouse");
    $menu->AddNode("Transaksi Inventory", null, "title");
        $menu->AddNode("Stock Awal Gudang", "inventory.awal");
        $menu->AddNode("Stock Transfer (NPB)", "inventory.transfer");
        $menu->AddNode("Pengiriman Barang", "inventory.delivery");
        //$menu->AddNode("Penerimaan Barang", "inventory.receive");
        $menu->AddNode("Stock Opname/Koreksi", "inventory.correction");
    $menu->AddNode("Laporan Inventory", null, "title");
        //$menu->AddNode("Statistik Inventory", "inventory.ivtstats");
        $menu->AddNode("Posisi Stock Terakhir", "inventory.stock");
        $menu->AddNode("Stock Per Periode", "inventory.stock/stkdetail");
        //$menu->AddNode("Daftar Produksi", "inventory.assembly/report");
        $menu->AddNode("Laporan Stock Transfer", "inventory.transfer/report");
        //$menu->AddNode("Laporan Stock Opname", "inventory.correction/report");
$menu = $root->AddNode("CASH BOOK", null, "menu");
    $menu->AddNode("Master Data", null, "title");
        $menu->AddNode("Daftar Bank", "master.bank");
        $menu->AddNode("Data Kas/Bank", "master.kasbank");
    $menu->AddNode("Transaksi Kas/Bank", null, "title");
        //$menu->AddNode("Nota Permintaan Kas/Bank", "cashbank.npkb");
        $menu->AddNode("Transaksi Kas/Bank", "cashbank.cbtrx");
        $menu->AddNode("Transaksi Warkat", "cashbank.warkat");
        //$menu->AddNode("Proses Warkat", "cashbank.warkat/proses");
    $menu->AddNode("Laporan Kas/Bank", null, "title");
        $menu->AddNode("Laporan Transaksi", "cashbank.cbtrx/report");
        $menu->AddNode("Laporan Warkat", "cashbank.warkat/report");
        //$menu->AddNode("Laporan Rek. Koran", "cashbank.cbtrx/rekoran");
$menu = $root->AddNode("PERPAJAKAN", null, "menu");
    $menu->AddNode("Master Data", null, "title");
        $menu->AddNode("No. Seri Faktur", "tax.serialno");
    $menu->AddNode("Pajak Keluaran", null, "title");
        $menu->AddNode("Faktur Pajak Keluaran", "tax.faktur");
        $menu->AddNode("Laporan PPN Keluaran", "tax.faktur/report");
    $menu->AddNode("Pajak Masukan", null, "title");
        $menu->AddNode("Faktur Pajak Masukan", "tax.fakturin");
        $menu->AddNode("Laporan PPN Masukan", "tax.fakturin/report");
$menu = $root->AddNode("AKUNTANSI", null, "menu");
    $menu->AddNode("Master Data", null, "title");
        $menu->AddNode("Data Header Akun", "master.coagroup");
        $menu->AddNode("Data Akun Perkiraan", "master.coadetail");
        $menu->AddNode("Jenis Transaksi", "master.trxtype");
    $menu->AddNode("Transaksi Akuntansi", null, "title");
        $menu->AddNode("Saldo Awal Akun", "accounting.obal");
        //$menu->AddNode("Saldo Hutang", "ap.obal");
        //$menu->AddNode("Saldo Piutang", "ar.obal");
        //$menu->AddNode("Set Periode Akuntansi", "main/set_periode");
        $menu->AddNode("Jurnal Akuntansi", "accounting.journal");
        //$menu->AddNode("Print Voucher/Jurnal", "accounting.journal/print_all");
    $menu->AddNode("Laporan Akuntansi", null, "title");
        $subMenu = $menu->AddNode("Laporan Jurnal/Voucher", null, "submenu");
            $subMenu->AddNode("Detail", "accounting.report/journal");
            $subMenu->AddNode("Rekapitulasi", "accounting.report/recap");
        $subMenu = $menu->AddNode("Laporan Ledger", null, "submenu");
            $subMenu->AddNode("Detail", "accounting.ledger/detail");
            $subMenu->AddNode("Rekapitulasi", "accounting.ledger/recap");
            $subMenu->AddNode("Statistik", "accounting.ledger/revcostat");
        //$menu->AddNode("Cost & Revenue", "accounting.ledger/costrevenue");
        $menu->AddNode("Trial Balance", "accounting.trialbalance/recap");
        $menu->AddNode("Worksheet Balance", "accounting.worksheetbalance/recap");
$menu = $root->AddNode("TVD CASTROL", null, "menu");
    $menu->AddNode("Master Data", null, "title");
        $menu->AddNode("Employee/Salesman", "master.salesman");
        $menu->AddNode("Customer/Outlet", "ar.customer");
        $menu->AddNode("Warehouse", "master.warehouse");
        $menu->AddNode("Product Castrol", "tvd.items");
    $menu->AddNode("Transaction", null, "title");
        $menu->AddNode("Penjualan Castrol", "tvd.invocas");
        $menu->AddNode("Pembelian Castrol", "tvd.purcas");
        $menu->AddNode("Product Stock", "tvd.stock");
    $menu->AddNode("Report", null, "title");
        $menu->AddNode("Export Report", "tvd.report");
$menu = $root->AddNode("PENGATURAN", null, "menu");
    $menu->AddNode("Data Perusahaan", null, "title");
        $menu->AddNode("Data Perusahaan", "master.company");
        $menu->AddNode("Data Cabang", "master.cabang");
        $menu->AddNode("Data Bagian", "master.department");
        $menu->AddNode("Data Karyawan", "master.karyawan");
        $menu->AddNode("Data Hari Libur", "master.libur");
$menu->AddNode("Pemakai System", null, "title");
        $menu->AddNode("Pemakai & Hak Akses", "master.useradmin");
    $menu->AddNode("Pengaturan System", null, "title");
        $menu->AddNode("Setting Pengumuman", "master.attention");
        $menu->AddNode("Ganti Periode Transaksi", "main/set_periode");
        $menu->AddNode("Ganti Password Sendiri", "main/change_password/0");
        $menu->AddNode("Daftar Hak Akses", "main/aclview");
// Special access for corporate
$persistence = PersistenceManager::GetInstance();
$isCorporate = $persistence->LoadState("is_corporate");
$forcePeriode = $persistence->LoadState("force_periode");
/*
if ($forcePeriode) {
	$root->AddNode("Ganti Periode", "main/set_periode");
}
$root->AddNode("Ganti Password", "main/change_password");
*/
//$root->AddNode("Notifikasi", "main");
$root->AddNode("LOGOUT", "home/logout");

// End of file: sitemap.php.php
