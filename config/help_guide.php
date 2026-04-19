<?php

/**
 * Panduan kontekstual per route.
 * steps  : maks 5 langkah singkat (HTML diizinkan)
 * tips   : opsional, 1–2 tips penting
 */
return [

    // ═══════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════════════

    'dashboard' => [
        'title' => 'Dashboard',
        'steps' => [
            'Lihat ringkasan stok, PO, dan DO aktif di kartu atas.',
            'Grafik menampilkan barang yang paling cepat dan lambat bergerak.',
            'Klik menu di sidebar untuk masuk ke masing-masing fitur.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // MASTER DATA
    // ═══════════════════════════════════════════════════════════════════════

    'product.index' => [
        'title' => 'Master — Data Barang',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan barang baru.',
            'Urutan wajib sebelum tambah barang: <em>Satuan → Jenis → SKU → Barang</em>.',
            'Pastikan SKU sudah terdaftar sebelum menambah barang.',
            'Klik nama barang untuk melihat detail atau mengedit.',
        ],
        'tips' => [
            'Jika muncul error "Data sudah ada", SKU atau kode barang sudah terdaftar — gunakan yang sudah ada.',
        ],
    ],

    'product.create' => [
        'title' => 'Master — Tambah Barang',
        'steps' => [
            'Pilih <strong>SKU</strong>, <strong>Jenis</strong>, dan <strong>Satuan</strong> yang sudah terdaftar.',
            'Isi nama barang dan kode barang.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'SKU, Jenis, dan Satuan harus sudah ada sebelum bisa menambah barang.',
        ],
    ],

    'product.edit' => [
        'title' => 'Master — Edit Barang',
        'steps' => [
            'Ubah data yang perlu diperbarui.',
            'Klik <strong>Simpan</strong> untuk menyimpan perubahan.',
        ],
        'tips' => [
            'Perubahan nama/kode barang akan berpengaruh ke seluruh transaksi yang merujuk barang ini.',
        ],
    ],

    'product.show' => [
        'title' => 'Master — Detail Barang',
        'steps' => [
            'Halaman ini menampilkan informasi lengkap barang.',
            'Klik <strong>Edit</strong> untuk mengubah data.',
        ],
    ],

    'product_unit.index' => [
        'title' => 'Master — Satuan Barang',
        'steps' => [
            'Satuan adalah langkah <strong>pertama</strong> sebelum mendaftarkan barang baru.',
            'Klik <strong>Tambah</strong>, isi nama satuan (contoh: PCS, KG, DUS).',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'product_unit.create' => [
        'title' => 'Master — Tambah Satuan',
        'steps' => [
            'Isi kode dan nama satuan (contoh: PCS, KG, DUS, LUSIN).',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'product_unit.edit' => [
        'title' => 'Master — Edit Satuan',
        'steps' => [
            'Ubah nama satuan sesuai kebutuhan.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'product_type.index' => [
        'title' => 'Master — Jenis Barang',
        'steps' => [
            'Jenis Barang adalah langkah <strong>kedua</strong> setelah Satuan.',
            'Klik <strong>Tambah</strong>, isi nama jenis barang.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'product_type.create' => [
        'title' => 'Master — Tambah Jenis Barang',
        'steps' => [
            'Isi kode dan nama jenis barang.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'product_type.edit' => [
        'title' => 'Master — Edit Jenis Barang',
        'steps' => [
            'Ubah nama jenis barang sesuai kebutuhan.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'sku.index' => [
        'title' => 'Master — SKU',
        'steps' => [
            'SKU adalah langkah <strong>ketiga</strong> setelah Jenis Barang.',
            'Klik <strong>Tambah</strong> untuk mendaftarkan SKU baru.',
            'Atau gunakan <strong>Import Excel</strong> untuk input SKU massal.',
        ],
        'tips' => [
            'SKU harus unik. Jika sudah ada, sistem akan menolak duplikasi.',
        ],
    ],

    'sku.create' => [
        'title' => 'Master — Tambah SKU',
        'steps' => [
            'Isi kode SKU dan nama SKU.',
            'Pilih <strong>Jenis Barang</strong> yang sesuai.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'sku.edit' => [
        'title' => 'Master — Edit SKU',
        'steps' => [
            'Ubah data SKU yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'customers.index' => [
        'title' => 'Master — Data Pelanggan',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan pelanggan baru.',
            'Isi nama, alamat, dan kontak pelanggan.',
            'Klik <strong>Simpan</strong> — pelanggan siap dipilih saat membuat DO / Invoice.',
        ],
    ],

    'customers.create' => [
        'title' => 'Master — Tambah Pelanggan',
        'steps' => [
            'Isi kode, nama, alamat, dan kontak pelanggan.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'customers.edit' => [
        'title' => 'Master — Edit Pelanggan',
        'steps' => [
            'Ubah data pelanggan yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'suppliers.index' => [
        'title' => 'Master — Data Pemasok',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan pemasok baru.',
            'Isi nama dan kontak pemasok.',
            'Klik <strong>Simpan</strong> — pemasok siap dipilih saat membuat PO.',
        ],
    ],

    'suppliers.create' => [
        'title' => 'Master — Tambah Pemasok',
        'steps' => [
            'Isi kode, nama, dan kontak pemasok.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'suppliers.edit' => [
        'title' => 'Master — Edit Pemasok',
        'steps' => [
            'Ubah data pemasok yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'couriers.index' => [
        'title' => 'Master — Data Kurir',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan kurir baru.',
            'Isi kode dan nama kurir.',
            'Klik <strong>Simpan</strong> — kurir siap dipilih saat membuat Receipt Invoice.',
        ],
        'tips' => [
            'Kurir harus terdaftar sebelum bisa membuat Receipt Invoice.',
        ],
    ],

    'couriers.create' => [
        'title' => 'Master — Tambah Kurir',
        'steps' => [
            'Isi kode dan nama kurir.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'couriers.edit' => [
        'title' => 'Master — Edit Kurir',
        'steps' => [
            'Ubah data kurir yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'bank.index' => [
        'title' => 'Master — Data Bank',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan rekening bank baru.',
            'Isi kode bank, nama bank, dan nomor rekening.',
            'Klik <strong>Simpan</strong> — bank siap dipilih saat membuat Invoice.',
        ],
        'tips' => [
            'Bank harus terdaftar sebelum bisa membuat Invoice.',
        ],
    ],

    'bank.create' => [
        'title' => 'Master — Tambah Bank',
        'steps' => [
            'Isi kode bank, nama bank, dan nomor rekening.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'bank.edit' => [
        'title' => 'Master — Edit Bank',
        'steps' => [
            'Ubah data bank yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'warehouses.index' => [
        'title' => 'Master — Data Gudang',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan gudang baru.',
            'Isi kode dan nama gudang.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'Gudang harus sudah ada sebelum bisa dipilih saat Konfirmasi Scan In atau DO Transfer.',
        ],
    ],

    'warehouses.create' => [
        'title' => 'Master — Tambah Gudang',
        'steps' => [
            'Isi kode dan nama gudang.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'warehouses.edit' => [
        'title' => 'Master — Edit Gudang',
        'steps' => [
            'Ubah data gudang yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'stores.index' => [
        'title' => 'Master — Data Toko',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mendaftarkan toko baru.',
            'Isi kode dan nama toko.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'Toko berfungsi sebagai gudang tujuan pada DO Transfer.',
        ],
    ],

    'stores.create' => [
        'title' => 'Master — Tambah Toko',
        'steps' => [
            'Isi kode dan nama toko.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'stores.edit' => [
        'title' => 'Master — Edit Toko',
        'steps' => [
            'Ubah data toko yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // STOCK IN — PURCHASE ORDER
    // ═══════════════════════════════════════════════════════════════════════

    'purchase_order.index' => [
        'title' => 'Stock In — Daftar PO',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk membuat pesanan barang baru ke pemasok.',
            'Gunakan <strong>Saring</strong> untuk mencari PO berdasarkan tanggal atau status.',
            'Klik nomor PO untuk melihat detail atau mencetak label QR.',
            'Status PO: <em>DRAFT → APPROVED → CONFIRMED</em>. QR bisa dicetak setelah CONFIRMED.',
        ],
        'tips' => [
            'Pastikan pemasok sudah terdaftar di Master Pemasok sebelum membuat PO.',
            'Label QR yang sudah dicetak tidak bisa dicetak ulang langsung — ajukan Reprint dari halaman detail PO.',
        ],
    ],

    'purchase_order.create' => [
        'title' => 'Stock In — Buat PO Baru',
        'steps' => [
            'Pilih <strong>Pemasok</strong> dari daftar.',
            'Tambahkan barang: klik <strong>Tambah Item</strong>, pilih barang, isi jumlah.',
            'Klik <strong>Simpan</strong> — PO tersimpan dengan status <em>DRAFT</em>.',
            'Kembali ke daftar PO → klik <strong>Approve</strong> lalu <strong>Konfirmasi</strong>.',
            'Setelah CONFIRMED, klik <strong>Cetak QR</strong> untuk mencetak label barcode.',
        ],
        'tips' => [
            'Nomor PO digenerate otomatis — jangan diubah manual.',
        ],
    ],

    'purchase_order.edit' => [
        'title' => 'Stock In — Edit PO',
        'steps' => [
            'PO hanya bisa diedit selama masih berstatus <em>DRAFT</em>.',
            'Ubah pemasok, barang, atau jumlah sesuai kebutuhan.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'PO yang sudah PARTIAL (sebagian diterima gudang) tidak bisa diedit lagi.',
        ],
    ],

    'purchase_order.show' => [
        'title' => 'Stock In — Detail PO',
        'steps' => [
            'Lihat daftar barang beserta jumlah dan status QR-nya.',
            'Klik <strong>Cetak QR</strong> jika label belum dicetak.',
            'Jika label rusak/hilang, klik <strong>Ajukan Reprint</strong> dan tunggu persetujuan.',
        ],
        'tips' => [
            'QR yang sudah pernah dicetak memerlukan persetujuan reprint untuk mencegah duplikasi.',
        ],
    ],

    'purchase_order.history' => [
        'title' => 'Stock In — Riwayat PO',
        'steps' => [
            'Halaman ini menampilkan riwayat perubahan status PO.',
            'Gunakan untuk audit atau melacak kapan PO diubah dan oleh siapa.',
        ],
    ],

    'purchase_order.bin' => [
        'title' => 'Stock In — Arsip PO',
        'steps' => [
            'Halaman ini menampilkan PO yang sudah dihapus (diarsipkan).',
            'Klik <strong>Pulihkan</strong> untuk mengembalikan PO ke daftar aktif.',
        ],
    ],

    'reprint.list' => [
        'title' => 'Stock In — Daftar Reprint QR',
        'steps' => [
            'Halaman ini menampilkan semua permintaan cetak ulang label QR.',
            'Klik <strong>Approve</strong> untuk menyetujui reprint.',
            'Klik <strong>Reject</strong> untuk menolak permintaan.',
        ],
        'tips' => [
            'Periksa alasan reprint sebelum menyetujui untuk mencegah duplikasi label.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // SCAN IN — PENERIMAAN BARANG
    // ═══════════════════════════════════════════════════════════════════════

    'product_inbound.index' => [
        'title' => 'Scan In — Konfirmasi Barang Masuk',
        'steps' => [
            'Halaman ini menampilkan hasil scan masuk dari aplikasi gudang (WMS).',
            'Pilih tanggal scan yang akan dikonfirmasi.',
            'Pastikan <strong>gudang tujuan</strong> sudah benar sebelum konfirmasi.',
            'Klik <strong>Konfirmasi</strong> — stok akan bertambah di sistem.',
        ],
        'tips' => [
            'Stok TIDAK bertambah otomatis. Admin wajib klik Konfirmasi.',
            'Jika data tidak muncul, minta staf gudang untuk melakukan scan terlebih dahulu.',
        ],
    ],

    'product_inbound.detail' => [
        'title' => 'Scan In — Detail Penerimaan',
        'steps' => [
            'Halaman ini menampilkan detail barang yang masuk pada tanggal tertentu.',
            'Pastikan jumlah dan barang sudah sesuai sebelum konfirmasi.',
            'Pilih <strong>gudang tujuan</strong> dengan benar.',
            'Klik <strong>Konfirmasi</strong> untuk menambah stok.',
        ],
        'tips' => [
            'Kesalahan pilih gudang tidak bisa dibatalkan setelah dikonfirmasi — hubungi Admin sistem.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // SCAN OUT / TDO STAGING
    // ═══════════════════════════════════════════════════════════════════════

    'tdo_scan_staging.index' => [
        'title' => 'Scan Out — Generate DO dari Staging',
        'steps' => [
            'Centang satu atau beberapa tanggal yang ingin diproses.',
            'Klik <strong>Generate DO</strong> (satu tanggal) atau <strong>Generate DO Batch</strong> (beberapa tanggal).',
            'Proses berjalan di background — halaman tidak perlu ditunggu.',
            'DO yang dihasilkan otomatis muncul di menu <strong>Delivery Order</strong>.',
        ],
        'tips' => [
            'Jika status tetap PROCESSING lama, hubungi tim IT untuk cek queue worker.',
            'Centang minimal satu tanggal sebelum klik Generate.',
        ],
    ],

    'tdo_scan_staging.detail' => [
        'title' => 'Scan Out — Detail Staging per Tanggal',
        'steps' => [
            'Halaman ini menampilkan daftar barang scan keluar pada tanggal tertentu.',
            'Periksa daftar barang sebelum di-generate menjadi DO.',
            'Klik <strong>Generate DO</strong> untuk memproses tanggal ini.',
        ],
    ],

    'tdo_scan_staging.detail_all' => [
        'title' => 'Scan Out — Semua Detail Staging',
        'steps' => [
            'Halaman ini menampilkan seluruh data scan keluar yang belum diproses.',
            'Gunakan untuk memantau data staging secara keseluruhan.',
            'Kembali ke halaman utama Scan Out untuk generate DO.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // DELIVERY ORDER
    // ═══════════════════════════════════════════════════════════════════════

    'delivery_order.index' => [
        'title' => 'Delivery Order — Daftar DO',
        'steps' => [
            'DO bisa berasal dari Generate Staging atau dibuat manual.',
            'Klik <strong>Approve</strong> pada DO untuk mengonfirmasi — stok langsung berkurang.',
            'Gunakan <strong>Saring</strong> untuk mencari DO berdasarkan pelanggan atau tanggal.',
        ],
        'tips' => [
            'Pastikan stok mencukupi sebelum approve. DO yang stoknya kurang akan gagal.',
            'DO yang sudah di-approve tidak bisa diubah lagi.',
        ],
    ],

    'delivery_order.create' => [
        'title' => 'Delivery Order — Buat DO Manual',
        'steps' => [
            'Pilih <strong>Pelanggan / Toko</strong> tujuan.',
            'Tambahkan barang: klik <strong>Tambah Item</strong>, pilih barang, isi jumlah.',
            'Klik <strong>Simpan</strong>.',
            'Kembali ke daftar DO → klik <strong>Approve</strong> untuk mengonfirmasi pengiriman.',
        ],
        'tips' => [
            'Cek stok di Laporan Stok sebelum membuat DO agar tidak gagal saat approve.',
        ],
    ],

    'delivery_order.edit' => [
        'title' => 'Delivery Order — Edit DO',
        'steps' => [
            'DO hanya bisa diedit selama belum di-approve.',
            'Ubah pelanggan, barang, atau jumlah sesuai kebutuhan.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'delivery_order.show' => [
        'title' => 'Delivery Order — Detail DO',
        'steps' => [
            'Halaman ini menampilkan detail barang dan status DO.',
            'Klik <strong>Approve</strong> untuk mengonfirmasi jika belum.',
            'Klik <strong>Cetak DO</strong> untuk mencetak dokumen pengiriman.',
        ],
    ],

    'delivery_order.history' => [
        'title' => 'Delivery Order — Riwayat DO',
        'steps' => [
            'Halaman ini menampilkan riwayat perubahan status DO.',
            'Gunakan untuk audit atau melacak kapan DO diubah.',
        ],
    ],

    'delivery_order.bin' => [
        'title' => 'Delivery Order — Arsip DO',
        'steps' => [
            'Halaman ini menampilkan DO yang sudah dihapus (diarsipkan).',
            'Klik <strong>Pulihkan</strong> untuk mengembalikan DO ke daftar aktif.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // DO TRANSFER
    // ═══════════════════════════════════════════════════════════════════════

    'product_transfer.index' => [
        'title' => 'DO Transfer — Daftar Transfer',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk membuat transfer barang antar gudang/toko.',
            'Klik nama transfer untuk melihat detail.',
            'Status transfer: <em>DRAFT → APPROVED</em>. Stok bergerak setelah APPROVED.',
        ],
        'tips' => [
            'Stok berkurang di gudang asal dan bertambah di gudang tujuan setelah approve.',
        ],
    ],

    'product_transfer.create' => [
        'title' => 'DO Transfer — Buat Transfer Baru',
        'steps' => [
            'Pilih gudang / toko <strong>Asal</strong>.',
            'Pilih gudang / toko <strong>Tujuan</strong>.',
            'Pilih barang dan isi jumlah yang akan dipindah.',
            'Klik <strong>Simpan</strong> → klik <strong>Approve</strong> untuk eksekusi transfer.',
        ],
        'tips' => [
            'Pastikan stok di gudang asal mencukupi sebelum approve.',
        ],
    ],

    'product_transfer.show' => [
        'title' => 'DO Transfer — Detail Transfer',
        'steps' => [
            'Lihat detail barang yang dipindah, gudang asal, dan gudang tujuan.',
            'Klik <strong>Approve</strong> jika transfer belum dikonfirmasi.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // PRODUCT OUTBOUND
    // ═══════════════════════════════════════════════════════════════════════

    'product_outbound.index' => [
        'title' => 'Scan Keluar — Daftar Outbound',
        'steps' => [
            'Halaman ini menampilkan data hasil scan keluar dari WMS yang sudah diproses.',
            'Klik tanggal untuk melihat detail barang yang keluar.',
            'Data di sini hanya untuk referensi — tidak bisa diubah.',
        ],
    ],

    'product_outbound.detail' => [
        'title' => 'Scan Keluar — Detail Outbound',
        'steps' => [
            'Halaman ini menampilkan detail barang scan keluar pada tanggal tertentu.',
            'Gunakan untuk verifikasi barang yang sudah keluar dari gudang.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // STOK OPNAME
    // ═══════════════════════════════════════════════════════════════════════

    'stock_opname.index' => [
        'title' => 'Stok Opname — Daftar Opname',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk memulai penyesuaian stok baru.',
            'Klik nomor opname untuk melihat detail atau riwayat.',
            'Gunakan fitur ini jika ada selisih antara stok sistem dan fisik di gudang.',
        ],
        'tips' => [
            'Pastikan Scan In sudah dikonfirmasi dengan gudang yang benar sebelum opname.',
        ],
    ],

    'stock_opname.create' => [
        'title' => 'Stok Opname — Tambah Opname',
        'steps' => [
            'Pilih <strong>Gudang</strong> yang akan diopname.',
            'Pilih <strong>Barang</strong> yang akan dihitung.',
            'Masukkan jumlah fisik aktual yang ada di gudang.',
            'Klik <strong>Simpan</strong> — stok di sistem langsung disesuaikan.',
        ],
        'tips' => [
            'Hitung fisik barang dengan teliti sebelum mengisi jumlah.',
        ],
    ],

    'stock_opname.edit' => [
        'title' => 'Stok Opname — Edit Opname',
        'steps' => [
            'Ubah jumlah fisik jika ada kesalahan input.',
            'Klik <strong>Simpan</strong> — stok sistem akan diperbarui.',
        ],
    ],

    'stock_opname.history' => [
        'title' => 'Stok Opname — Riwayat Opname',
        'steps' => [
            'Halaman ini menampilkan riwayat penyesuaian stok untuk barang/gudang ini.',
            'Gunakan untuk audit atau melacak kapan stok disesuaikan.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // INVOICE
    // ═══════════════════════════════════════════════════════════════════════

    'invoice.index' => [
        'title' => 'Invoice — Daftar Invoice',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk membuat invoice baru setelah DO di-approve.',
            'Klik nomor invoice untuk melihat detail atau mengunduh PDF.',
            'Alur: <em>Invoice → Tax Invoice → Receipt Invoice → Send Invoice → Payment</em>.',
        ],
        'tips' => [
            'Invoice hanya bisa dibuat jika DO sudah di-approve dan pelanggan/bank sudah terdaftar.',
        ],
    ],

    'invoice.create' => [
        'title' => 'Invoice — Buat Invoice',
        'steps' => [
            'Pilih <strong>Pelanggan</strong> yang akan ditagih.',
            'Pilih <strong>Bank</strong> tujuan pembayaran.',
            'Pilih <strong>DO</strong> yang sudah di-approve.',
            'Isi term pembayaran dan keterangan jika perlu.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'invoice.show' => [
        'title' => 'Invoice — Detail Invoice',
        'steps' => [
            'Lihat detail tagihan, pelanggan, dan bank tujuan.',
            'Klik <strong>Unduh PDF</strong> untuk mencetak invoice.',
            'Lanjutkan ke <strong>Tax Invoice</strong> setelah invoice ini selesai.',
        ],
    ],

    'invoice.edit' => [
        'title' => 'Invoice — Edit Invoice',
        'steps' => [
            'Ubah data invoice yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'invoice.history' => [
        'title' => 'Invoice — Riwayat Invoice',
        'steps' => [
            'Menampilkan riwayat perubahan pada invoice ini.',
            'Gunakan untuk audit atau pelacakan.',
        ],
    ],

    'invoice.bin' => [
        'title' => 'Invoice — Arsip Invoice',
        'steps' => [
            'Menampilkan invoice yang sudah dihapus (diarsipkan).',
            'Klik <strong>Pulihkan</strong> untuk mengembalikan ke daftar aktif.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // TAX INVOICE
    // ═══════════════════════════════════════════════════════════════════════

    'tax_invoice.index' => [
        'title' => 'Tax Invoice — Daftar',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk membuat tax invoice setelah invoice dibuat.',
            'Tax invoice menghubungkan PO dengan DO untuk keperluan pajak.',
            'Klik nomor tax invoice untuk melihat detail atau mengunduh PDF.',
        ],
    ],

    'tax_invoice.create' => [
        'title' => 'Tax Invoice — Buat Tax Invoice',
        'steps' => [
            'Pilih <strong>PO</strong> (Purchase Order) yang terkait.',
            'Pilih <strong>DO</strong> (Delivery Order) yang terkait.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'PO dan DO harus sudah dalam status CONFIRMED/APPROVED sebelum bisa dipilih.',
        ],
    ],

    'tax_invoice.show' => [
        'title' => 'Tax Invoice — Detail',
        'steps' => [
            'Lihat detail tax invoice dan dokumen terkait.',
            'Klik <strong>Unduh PDF</strong> untuk mencetak.',
            'Lanjutkan ke <strong>Receipt Invoice</strong> setelah ini.',
        ],
    ],

    'tax_invoice.edit' => [
        'title' => 'Tax Invoice — Edit',
        'steps' => [
            'Ubah data tax invoice yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'tax_invoice.history' => [
        'title' => 'Tax Invoice — Riwayat',
        'steps' => [
            'Menampilkan riwayat perubahan pada tax invoice ini.',
        ],
    ],

    'tax_invoice.bin' => [
        'title' => 'Tax Invoice — Arsip',
        'steps' => [
            'Menampilkan tax invoice yang sudah diarsipkan.',
            'Klik <strong>Pulihkan</strong> untuk mengembalikan ke daftar aktif.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // RECEIPT INVOICE
    // ═══════════════════════════════════════════════════════════════════════

    'receipt_invoice.index' => [
        'title' => 'Receipt Invoice — Daftar',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk membuat receipt invoice.',
            'Receipt invoice mencatat pengiriman dokumen invoice via kurir.',
            'Klik nomor untuk melihat detail.',
        ],
        'tips' => [
            'Kurir harus sudah terdaftar di Master Kurir sebelum membuat receipt invoice.',
        ],
    ],

    'receipt_invoice.create' => [
        'title' => 'Receipt Invoice — Buat',
        'steps' => [
            'Pilih <strong>Invoice</strong> yang akan dikirim dokumennya.',
            'Isi <strong>nomor receipt invoice</strong>.',
            'Pilih <strong>Kurir</strong> yang akan mengirim.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'receipt_invoice.show' => [
        'title' => 'Receipt Invoice — Detail',
        'steps' => [
            'Lihat detail receipt invoice, invoice terkait, dan kurir.',
            'Lanjutkan ke <strong>Send Invoice</strong> setelah ini.',
        ],
    ],

    'receipt_invoice.edit' => [
        'title' => 'Receipt Invoice — Edit',
        'steps' => [
            'Ubah data receipt invoice yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'receipt_invoice.history' => [
        'title' => 'Receipt Invoice — Riwayat',
        'steps' => [
            'Menampilkan riwayat perubahan pada receipt invoice ini.',
        ],
    ],

    'receipt_invoice.bin' => [
        'title' => 'Receipt Invoice — Arsip',
        'steps' => [
            'Menampilkan receipt invoice yang sudah diarsipkan.',
            'Klik <strong>Pulihkan</strong> untuk mengembalikan ke daftar aktif.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // SEND INVOICE
    // ═══════════════════════════════════════════════════════════════════════

    'send_invoice.index' => [
        'title' => 'Send Invoice — Daftar',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mencatat pengiriman invoice ke pelanggan.',
            'Send invoice menyimpan nomor resi dan bukti tanda terima.',
            'Klik nomor untuk melihat detail pengiriman.',
        ],
    ],

    'send_invoice.create' => [
        'title' => 'Send Invoice — Catat Pengiriman',
        'steps' => [
            'Pilih <strong>Receipt Invoice</strong> yang akan dikirim.',
            'Isi <strong>nomor resi</strong> dari kurir.',
            'Upload <strong>bukti tanda terima</strong> (foto atau scan).',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'Pastikan foto bukti tanda terima jelas dan terbaca sebelum diupload.',
        ],
    ],

    'send_invoice.show' => [
        'title' => 'Send Invoice — Detail Pengiriman',
        'steps' => [
            'Lihat nomor resi, receipt invoice terkait, dan bukti tanda terima.',
            'Lanjutkan ke <strong>Payment</strong> setelah pelanggan membayar.',
        ],
    ],

    'send_invoice.edit' => [
        'title' => 'Send Invoice — Edit',
        'steps' => [
            'Ubah nomor resi atau upload ulang bukti tanda terima.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // PAYMENT
    // ═══════════════════════════════════════════════════════════════════════

    'payment.index' => [
        'title' => 'Payment — Daftar Pembayaran',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk mencatat pembayaran dari pelanggan.',
            'Klik nomor payment untuk melihat detail.',
            'Payment adalah langkah terakhir dalam alur invoice.',
        ],
        'tips' => [
            'Tax Invoice harus sudah dibuat sebelum bisa mencatat payment.',
        ],
    ],

    'payment.create' => [
        'title' => 'Payment — Catat Pembayaran',
        'steps' => [
            'Pilih <strong>Tax Invoice</strong> yang dibayar.',
            'Pilih <strong>PO</strong> terkait.',
            'Isi tanggal bayar, jumlah, metode pembayaran, dan bank.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'Jika Tax Invoice tidak muncul, pastikan sudah dibuat terlebih dahulu.',
        ],
    ],

    'payment.edit' => [
        'title' => 'Payment — Edit Pembayaran',
        'steps' => [
            'Ubah data pembayaran yang perlu diperbarui.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'payment.history' => [
        'title' => 'Payment — Riwayat Pembayaran',
        'steps' => [
            'Menampilkan riwayat perubahan pada catatan pembayaran ini.',
            'Gunakan untuk audit atau pelacakan pembayaran.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // LAPORAN
    // ═══════════════════════════════════════════════════════════════════════

    'stock_movement.index' => [
        'title' => 'Laporan — Pergerakan Stok',
        'steps' => [
            'Pilih rentang tanggal dan klik <strong>Tampilkan</strong>.',
            'Laporan menampilkan semua barang masuk dan keluar per periode.',
            'Klik <strong>Export Excel</strong> untuk mengunduh laporan.',
        ],
        'tips' => [
            'Gunakan untuk audit bulanan atau cek riwayat barang tertentu.',
        ],
    ],

    'stock_aging.index' => [
        'title' => 'Laporan — Umur Stok',
        'steps' => [
            'Laporan menampilkan barang berdasarkan berapa lama sudah berada di gudang.',
            'Pilih gudang dan klik <strong>Tampilkan</strong>.',
            'Klik <strong>Export Excel</strong> untuk mengunduh laporan.',
        ],
        'tips' => [
            'Gunakan untuk mengidentifikasi barang yang menumpuk atau berpotensi kadaluarsa.',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // MANAJEMEN PENGGUNA & ROLE
    // ═══════════════════════════════════════════════════════════════════════

    'users.index' => [
        'title' => 'Pengguna — Daftar Akun',
        'steps' => [
            'Klik <strong>Tambah</strong> untuk membuat akun pengguna baru.',
            'Klik nama pengguna untuk melihat detail atau mengedit.',
            'Setiap pengguna harus memiliki Role yang menentukan hak aksesnya.',
        ],
    ],

    'users.create' => [
        'title' => 'Pengguna — Tambah Akun',
        'steps' => [
            'Isi <strong>nama</strong>, <strong>email</strong>, dan <strong>password</strong>.',
            'Pilih <strong>Role</strong> yang sesuai dengan tugas pengguna.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'Role menentukan menu apa saja yang bisa diakses pengguna ini.',
        ],
    ],

    'users.edit' => [
        'title' => 'Pengguna — Edit Akun',
        'steps' => [
            'Ubah nama, email, atau role pengguna.',
            'Kosongkan kolom password jika tidak ingin mengubah password.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'users.show' => [
        'title' => 'Pengguna — Detail Akun',
        'steps' => [
            'Lihat informasi akun pengguna dan role yang dimiliki.',
            'Klik <strong>Edit</strong> untuk mengubah data.',
        ],
    ],

    'roles.index' => [
        'title' => 'Role — Daftar Hak Akses',
        'steps' => [
            'Klik nama role untuk mengedit izin akses menu.',
            'Setiap role bisa diset izin: Lihat, Tambah, Edit, Hapus, Approve, Cetak.',
            'Klik <strong>Tambah</strong> untuk membuat role baru.',
        ],
        'tips' => [
            'Jika pengguna mendapat pesan "tidak punya hak", cek dan tambahkan izin di role-nya.',
        ],
    ],

    'roles.create' => [
        'title' => 'Role — Buat Role Baru',
        'steps' => [
            'Isi nama role (contoh: Admin Gudang, Supervisor, Finance).',
            'Centang izin yang diperlukan per menu.',
            'Klik <strong>Simpan</strong>.',
        ],
    ],

    'roles.edit' => [
        'title' => 'Role — Edit Hak Akses',
        'steps' => [
            'Centang atau hapus centang izin untuk setiap menu.',
            'Izin tersedia: <strong>Lihat, Tambah, Edit, Hapus, Approve, Cetak</strong>.',
            'Klik <strong>Simpan</strong>.',
        ],
        'tips' => [
            'Perubahan role berlaku langsung untuk semua pengguna yang memakai role ini.',
        ],
    ],

];
