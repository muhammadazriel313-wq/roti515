<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}
include('../database/koneksi.php');

$id_pesanan = (int)$_GET['id'];
$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id=$id_pesanan"));
$detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE id_pesanan=$id_pesanan");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pesanan #<?php echo $pesanan['id']; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root {
    --bg-dark: #0d0d0d;
    --bg-card: #1a1a1a;
    --accent: #ff6600;
    --danger: #ff3b3b;
    --text-light: #f5f5f5;
    --radius: 12px;
}

body {
    background: var(--bg-dark);
    color: var(--text-light);
    font-family: 'Poppins', sans-serif;
    padding: 30px;
    margin: 0;
}

.container {
    max-width: 900px;
    margin: auto;
    background: var(--bg-card);
    border-radius: var(--radius);
    padding: 30px;
}

table {
    width: 100%;
    margin-top: 10px;
    background: #141414;
    border-radius: var(--radius);
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #333;
}

.btn {
    padding: 10px 20px;
    border-radius: var(--radius);
    border: none;
    cursor: pointer;
    margin-top: 20px;
    font-weight: 600;
    color: white;
}

.btn-selesai { background: var(--accent); }
.btn-batal { background: var(--danger); margin-left: 10px; }
.btn-back { background: #444; margin-left: 10px; }

img { border-radius: 10px; margin-top: 10px; }
</style>
</head>

<body>
<div class="container">
    <h2>Detail Pesanan #<?php echo $pesanan['id']; ?></h2>

    <p><strong>Nama:</strong> <?php echo $pesanan['nama_pembeli']; ?></p>
    <p><strong>No. Telpon:</strong> <?php echo $pesanan['no_telpon']; ?></p>
    <p><strong>Alamat:</strong> <?php echo $pesanan['alamat']; ?></p>
    <p><strong>Metode Pembayaran:</strong> <?php echo $pesanan['metode_pembayaran']; ?></p>
    <p><strong>Status:</strong> <span><?php echo $pesanan['status']; ?></span></p>

    <h3>Produk yang Dipesan</h3>
    <table>
        <tr>
            <th>Nama Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
        <?php while($d = mysqli_fetch_assoc($detail)){ ?>
        <tr>
            <td><?php echo $d['nama_produk']; ?></td>
            <td><?php echo $d['qty']; ?></td>
            <td>Rp<?php echo number_format($d['harga'],0,',','.'); ?></td>
            <td>Rp<?php echo number_format($d['subtotal'],0,',','.'); ?></td>
        </tr>
        <?php } ?>
    </table>

    <p><strong>Total:</strong> Rp<?php echo number_format($pesanan['total_harga'],0,',','.'); ?></p>

    <?php if (!empty($pesanan['bukti_pembayaran'])): ?>
        <h3>Bukti Pembayaran:</h3>
        <img src="../uploads/<?php echo $pesanan['bukti_pembayaran']; ?>" width="250">
    <?php else: ?>
        <p><em>Belum ada bukti pembayaran.</em></p>
    <?php endif; ?>

    <!-- FORM UPDATE STATUS -->

    <form method="POST" action="ubah_status_pesanan.php" style="margin-top:20px;">
        <input type="hidden" name="id" value="<?php echo $pesanan['id']; ?>">
        <input type="hidden" name="nama_pembeli" value="<?php echo $pesanan['nama_pembeli']; ?>">
        <input type="hidden" name="no_telpon" value="<?php echo $pesanan['no_telpon']; ?>">

        <button type="submit" name="selesai" class="btn btn-selesai">✅ Selesaikan Pesanan</button>
        <button type="submit" name="batalkan" class="btn btn-batal" onclick="return confirm('Yakin ingin membatalkan?')">❌ Batalkan Pesanan</button>
        <a href="dashboard.php" class="btn btn-back">← Kembali</a>
    </form>

</div>
</body>
</html>