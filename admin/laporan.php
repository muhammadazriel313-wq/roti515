<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

include('../database/koneksi.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan - Roti 515</title>
  <link rel="stylesheet" href="iya.css">
  <style>
    .btn-hapus {
        color: red;
        font-size: 20px;
        text-decoration: none;
        font-weight: bold;
    }
    .btn-hapus:hover {
        opacity: .7;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>ROTI<span>515</span></h2>
    <ul>
      <li><a href="dashboard.php">📦 Pesanan</a></li>
      <li><a href="produk.php">🍞 Produk</a></li>
      <li><a href="pelanggan.php">👤 Pelanggan</a></li>
      <li><a href="laporan.php" class="active">📊 Laporan</a></li>
      <li><a href="logout.php" class="logout">🚪 Logout</a></li>
    </ul> 
  </div>

  <div class="main-content">
    <header>
      <h1>Laporan Penjualan</h1>
      <p>Rekap pesanan yang telah selesai ✅</p>
    </header>

    <!-- TABEL PESANAN SELESAI -->
    <section class="table-section">
      <h2>Pesanan Selesai</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>No. Telpon</th>
            <th>Alamat</th>
            <th>Total Harga</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            
          </tr>
        </thead>
        <tbody>
        <?php
        $query = mysqli_query($koneksi, "SELECT * FROM laporan ORDER BY tanggal DESC");
        while ($laporan = mysqli_fetch_assoc($query)) {
            echo "<tr>";
            echo "<td>".$laporan['id']."</td>";
            echo "<td>".$laporan['nama_pembeli']."</td>";
            echo "<td>".$laporan['email']."</td>";
            echo "<td>".$laporan['alamat']."</td>";
            echo "<td>Rp".number_format($laporan['total_harga'],0,',','.')."</td>";
            echo "<td>".$laporan['metode_pembayaran']."</td>";
            echo "<td class='status Selesai'>".$laporan['status']."</td>";
            echo "<td>".$laporan['tanggal']."</td>";
            echo "<td style='color:#00ff88;font-size:22px;'>
             <a class='btn-hapus' 
                       href='hapus_laporan.php?id=".$laporan['id']."' 
                       onclick='return confirm(\"Yakin ingin menghapus laporan ini?\")'>
                       🗑
                    </a>
                    </td>";
            // ========= TOMBOL HAPUS =========
            echo "<td>
                   
                  </td>";

            echo "</tr>";
        }
        ?>
        </tbody>
      </table>
    </section>

    <!-- TOTAL PENDAPATAN -->
    <section class="cards">
      <?php
      $totalPendapatanQuery = mysqli_query($koneksi, "SELECT SUM(total_harga) AS total_pendapatan FROM laporan");
      $totalPendapatan = mysqli_fetch_assoc($totalPendapatanQuery)['total_pendapatan'];
      ?>
      <div class="card">
        <h3>Total Pendapatan</h3>
        <p>Rp<?= number_format($totalPendapatan,0,',','.') ?></p>
      </div>
    </section>

    <!-- STOK PRODUK & PRODUK TERJUAL -->
    <section class="cards dua-kotak">
        <?php
        // ==================== STOK PRODUK ====================
        $stokQuery = mysqli_query($koneksi, "SELECT nama_produk, stok FROM produk LIMIT 6");

        // ==================== PRODUK TERJUAL ====================
        $terjualQuery = mysqli_query($koneksi, "
            SELECT 
                p.nama_produk, 
                COALESCE(SUM(dl.jumlah), 0) AS total_terjual
            FROM produk p
            LEFT JOIN detail_laporan dl ON dl.id_produk = p.id
            GROUP BY p.id, p.nama_produk
            LIMIT 6
        ");
        ?>

        <!-- KOTAK STOK PRODUK -->
        <div class="card stok-card">
            <h3>Stok Produk</h3>
            <table class="small-table">
                <tr>
                    <th>Nama Produk</th>
                    <th>Stok</th>
                </tr>
                <?php
                while ($p = mysqli_fetch_assoc($stokQuery)) {
                    echo "<tr>
                        <td>{$p['nama_produk']}</td>
                        <td>{$p['stok']}</td>
                    </tr>";
                }
                ?>
            </table>
        </div>

        <!-- KOTAK PRODUK TERJUAL -->
        <div class="card terjual-card">
            <h3>Produk Terjual</h3>
            <table class="small-table">
                <tr>
                    <th>Nama Produk</th>
                    <th>Terjual</th>
                </tr>
                <?php
                while($row = mysqli_fetch_assoc($terjualQuery)){
                    echo "<tr>
                        <td>{$row['nama_produk']}</td>
                        <td>{$row['total_terjual']}</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </section>

  </div>
</body>
</html>
