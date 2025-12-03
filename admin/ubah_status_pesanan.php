<?php
session_start();
include('../database/koneksi.php');

$id = $_POST['id']; // ID pesanan yang akan diselesaikan
$nama = $_POST['nama_pembeli'];
$telp = $_POST['no_telpon'];

// ==================== FUNGSI: PINDAHKAN KE LAPORAN ====================
function pindahkanKeLaporan($koneksi, $id) {

    // AMBIL DATA PESANAN UTAMA
    $pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, 
        "SELECT * FROM pesanan WHERE id='$id'"
    ));

    // ************ PERBAIKAN: CEK APAKAH SUDAH ADA DI TABEL LAPORAN ************
    $cek_laporan = mysqli_query($koneksi, "SELECT id FROM laporan WHERE id='$id'");
    
    // Hanya lakukan INSERT jika ID pesanan belum ada di tabel laporan (mengatasi duplikasi lama)
    if (mysqli_num_rows($cek_laporan) == 0) {
        
        // SIMPAN KE TABEL LAPORAN (Pesanan Selesai)
        // Baris 24: ID pesanan yang sama digunakan sebagai ID laporan (asumsi ID laporan adalah ID pesanan)
        mysqli_query($koneksi, "
            INSERT INTO laporan 
            (id, nama_pembeli, no_telpon, alamat, total_harga, metode_pembayaran, status, tanggal)
            VALUES 
            ('{$pesanan['id']}', '{$pesanan['nama_pembeli']}', '{$pesanan['no_telpon']}', 
            '{$pesanan['alamat']}', '{$pesanan['total_harga']}', '{$pesanan['metode_pembayaran']}', 
            'Selesai', '{$pesanan['tanggal']}')
        ");

        // PINDAHKAN DETAILNYA (Hanya jika INSERT laporan berhasil)
        $detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE id_pesanan='$id'");
        while ($d = mysqli_fetch_assoc($detail)) {
            
            // MENCARI ID PRODUK DARI NAMA PRODUK
            $nama_produk_escape = mysqli_real_escape_string($koneksi, $d['nama_produk']);
            $qProduk = mysqli_query($koneksi, "
                SELECT id FROM produk WHERE nama_produk='$nama_produk_escape'
            ");
            $p = mysqli_fetch_assoc($qProduk);
            $id_produk = $p ? $p['id'] : null;

            // 🛠️ PERBAIKAN: Menggunakan kolom 'id_laporan' (mengacu ke kolom 'id' di tabel laporan)
            if ($id_produk) {
                mysqli_query($koneksi, "
                    INSERT INTO detail_laporan 
                    (id_laporan, id_produk, jumlah)
                    VALUES 
                    ('{$d['id_pesanan']}', '$id_produk', '{$d['qty']}') 
                ");
            }
        }
    }
    
    // HAPUS DETAIL PESANAN (Menghilangkan dari Daftar Pesanan) - Ini harus selalu dilakukan
    mysqli_query($koneksi, "DELETE FROM detail_pesanan WHERE id_pesanan='$id'");

    // HAPUS PESANAN (Menghilangkan dari Daftar Pesanan) - Ini harus selalu dilakukan
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id='$id'");
}


// ==================== Aksi: SELESAIKAN PESANAN ====================
if (isset($_POST['selesai'])) {

    // 1. PINDAH DATA DARI PESANAN KE LAPORAN, KEMUDIAN HAPUS DARI PESANAN
    pindahkanKeLaporan($koneksi, $id); 

    header("Location: dashboard.php?selesai=1");
    exit;
}


// ==================== Aksi: BATALKAN PESANAN ====================
if (isset($_POST['batalkan'])) {

    // 1. UPDATE STATUS PESANAN MENJADI DIBATALKAN (Tetap di Daftar Pesanan dengan status Batal)
    mysqli_query($koneksi, "UPDATE pesanan SET status='Dibatalkan' WHERE id='$id'");

    // 2. HAPUS PELANGGAN TERKAIT (opsional)
    mysqli_query($koneksi, "
        DELETE FROM pelanggan 
        WHERE nama='$nama' AND no_telpon='$telp'
    ");

    header("Location: dashboard.php?batal=1");
    exit;
}

?>