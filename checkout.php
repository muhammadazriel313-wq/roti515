<?php
include('database/koneksi.php');

/* === FUNGSI KIRIM WA === */
function kirim_wa($target, $pesan, $token) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $target,
            'message' => $pesan,
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: $token"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    file_put_contents(
        "log_wa_checkout.txt",
        date("Y-m-d H:i:s") . " | Target: $target | Response: $response\n",
        FILE_APPEND
    );

    return $response;
}

/* === PROSES CHECKOUT === */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $metode = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
    $total = mysqli_real_escape_string($koneksi, $_POST['total_harga']);
    $daftar_item = $_POST['daftar_item'];
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_customer']); 

    $bukti_file = '';
    // Status awal 'Belum Dibayar'
    $status = 'Belum Dibayar';


    /* === 1. SIMPAN PESANAN (HANYA KE TABEL PESANAN) === */
    $sql = "INSERT INTO pesanan (nama_pembeli, no_telpon, alamat, total_harga, tanggal, metode_pembayaran, bukti_pembayaran, status)
            VALUES ('$nama', '$no_telp', '$alamat', '$total', NOW(), '$metode', '$bukti_file', '$status')";

    if (mysqli_query($koneksi, $sql)) {

        $id_pesanan = mysqli_insert_id($koneksi);

        /* === 2. SIMPAN / UPDATE PELANGGAN === */
        $cek = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE no_telpon='$no_telp'");

        if(mysqli_num_rows($cek) == 0){
            // pelanggan baru
            mysqli_query($koneksi, "
                INSERT INTO pelanggan (no_telpon, nama, jenis, status)
                VALUES ('$no_telp', '$nama', '$jenis', 'Aktif')
            ");
        } else {
            // pelanggan lama → update jenis & status
            mysqli_query($koneksi, "
                UPDATE pelanggan 
                SET nama='$nama', jenis='$jenis', status='Aktif'
                WHERE no_telpon='$no_telp'
            ");
        }

        /* === 3. DETAIL PESANAN (HANYA KE TABEL DETAIL_PESANAN) === */
        $items = explode(",", $daftar_item);

        foreach ($items as $it) {
            if (trim($it) === '') continue;

            list($nama_produk, $qty, $harga) = explode("|", $it);
            $qty = (int)$qty;
            $harga = (int)$harga;
            $subtotal = $qty * $harga;

            mysqli_query($koneksi, "
                INSERT INTO detail_pesanan (id_pesanan, nama_produk, qty, harga, subtotal)
                VALUES ('$id_pesanan', 
                        '" . mysqli_real_escape_string($koneksi, $nama_produk) . "',
                        '$qty',
                        '$harga',
                        '$subtotal')
            ");

            /* === 4. UPDATE STOK PRODUK === */
            // Ambil id produk berdasarkan nama
            $qProduk = mysqli_query($koneksi, "
                SELECT id FROM produk WHERE nama_produk='" . mysqli_real_escape_string($koneksi, $nama_produk) . "'
            ");
            $p = mysqli_fetch_assoc($qProduk);
            $id_produk = $p['id'];

            if ($id_produk) {
                // Kurangi stok
                mysqli_query($koneksi, "
                    UPDATE produk 
                    SET stok = stok - $qty
                    WHERE id = '$id_produk'
                ");
            }
        }


        /* === 5. FORMAT & KIRIM WA (TIDAK ADA PERUBAHAN) === */
        $teks_item = "";
        foreach ($items as $it) {
            if (trim($it) === '') continue;
            list($nama_produk, $qty, $harga) = explode("|", $it);

            $teks_item .= "- $nama_produk ($qty x Rp" . number_format($harga,0,',','.') . ")\n";
        }

        $token = "K9asMQwnsqqzJyF85tj7";

        if (substr($no_telp, 0, 1) === "0") {
            $no_telp = "62" . substr($no_telp, 1);
        }

        $pesan = 
"Hallo $nama 👋

Terima kasih sudah memesan di Roti 515 🍞✨

🧾 ID Pesanan: #$id_pesanan
📦 Detail Pesanan:
 $teks_item
💰 Total: Rp" . number_format($total,0,',','.') . "

📍 Alamat: $alamat
👥 Jenis Pelanggan: $jenis
💳 Metode Pembayaran: $metode

Pesanan kamu sudah kami terima dan sedang diproses ✔
Terima kasih 🙏🙂

*_Note : Kirim Bukti Pembayaran di nomer ini (jika transfer)_*";

        kirim_wa($no_telp, $pesan, $token);

        echo 'success';

    } else {
        echo 'error: ' . mysqli_error($koneksi);
    }
}
?>