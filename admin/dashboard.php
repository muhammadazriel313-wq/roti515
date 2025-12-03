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
  <title>Dashboard Admin - Roti 515</title>
  <link rel="stylesheet" href="iya.css">
  <style>
    /* Dropdown menu styling */
    .dropdown {
      position: relative;
      display: inline-block;
    }
    
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #f9f9f9;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
      right: 0;
    }
    
    .dropdown-content a {
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }
    
    .dropdown-content a:hover {
      background-color: #f1f1f1
    }
    
    .show {
      display: block;
    }
    
    .btn-aksi {
      background-color: #4CAF50;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    
    .btn-hapus {
      background-color: #f44336;
      color: white;
    }
    
    .btn-detail {
      background-color: #2196F3;
      color: white;
      padding: 6px 12px;
      text-decoration: none;
      border-radius: 4px;
      display: inline-block;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>ROTI<span>515</span></h2>
    <ul>
      <li><a href="dashboard.php" class="active">📦 Pesanan</a></li>
      <li><a href="produk.php">🍞 Produk</a></li>
      <li><a href="pelanggan.php">👤 Pelanggan</a></li>
      <li><a href="laporan.php">📊 Laporan</a></li>
      <li><a href="logout.php" class="logout">🚪 Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <header>
      <h1>Dashboard Admin</h1>
      <p>Selamat datang, <strong><?= $_SESSION['admin'] ?></strong> 👋</p>
    </header>
    <?php if (isset($_GET['selesai'])): ?>
<div style="background:#00c853;color:white;padding:10px 15px;border-radius:8px;margin-bottom:20px;box-shadow:0 3px 10px rgba(0,0,0,0.3);">
  ✅ Pesanan telah diselesaikan dan dipindahkan ke laporan.
</div>
<?php endif; ?>

    <section class="cards">
  <div class="card">
    <h3>Total Pesanan</h3>
    <?php
      $total = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pesanan"));
      echo "<p>$total Pesanan</p>";
    ?>
  </div>

  <div class="card">
    <h3>Pesanan Selesai</h3>
    <?php
      $selesai1 = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE status='Selesai'"));
      $selesai2 = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM laporan WHERE status='Selesai'"));
      $selesai_total = $selesai1 + $selesai2;
      echo "<p>$selesai_total</p>";
    ?>
  </div>

  <div class="card">
    <h3>Pesanan Dibatalkan</h3>
    <?php
      $batal1 = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pesanan WHERE status='Dibatalkan'"));
      $batal2 = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM laporan WHERE status='Dibatalkan'"));
      $batal_total = $batal1 + $batal2;
      echo "<p>$batal_total</p>";
    ?>
  </div>
</section>

    <section class="table-section">
      <h2>Daftar Pesanan</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>No.Telpon</th>
            <th>Alamat</th>
            <th>Total Harga</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php
 $query = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY tanggal DESC LIMIT 10");

while ($pesanan = mysqli_fetch_assoc($query)) {

    // Update otomatis hanya jika pesanan masih Pending
    if ($pesanan['metode_pembayaran'] == 'transfer' 
        && !empty($pesanan['bukti_pembayaran']) 
        && $pesanan['status'] == 'Pending') {

        mysqli_query($koneksi, "UPDATE pesanan SET status='Sudah Dibayar' WHERE id='".$pesanan['id']."'");
        $pesanan['status'] = 'Sudah Dibayar';
    }

    $class = ($pesanan['status'] == 'Dibatalkan') ? "style='text-decoration: line-through; opacity:0.5;'" : "";
    echo "<tr $class>";
    echo "<td>".$pesanan['id']."</td>";
    echo "<td>".$pesanan['nama_pembeli']."</td>";
    echo "<td>".$pesanan['no_telpon']."</td>";
    echo "<td>".$pesanan['alamat']."</td>";
    echo "<td>Rp".number_format($pesanan['total_harga'],0,',','.')."</td>";
    echo "<td>".$pesanan['metode_pembayaran']."</td>";
    echo "<td>".$pesanan['status']."</td>";
    echo "<td>".$pesanan['tanggal']."</td>";

    echo "<td>";
    $status = trim($pesanan['status']); 

    if (strcasecmp($status, 'Selesai') == 0) {
        echo "<span style='font-size:22px; color:#00ff88;'>✔</span>";

    } elseif ($status == 'Pending') {
        ?>
        <div class="dropdown">
          <button onclick="toggleDropdown('dropdown<?= $pesanan['id'] ?>')" class="btn-aksi">👁</button>
          <div id="dropdown<?= $pesanan['id'] ?>" class="dropdown-content">
            <form action="ubah_status.php" method="POST" style="margin:0;">
                <input type="hidden" name="id" value="<?= $pesanan['id'] ?>">
                <input type="hidden" name="nama_pembeli" value="<?= $pesanan['nama_pembeli'] ?>">
                <input type="hidden" name="no_telpon" value="<?= $pesanan['no_telpon'] ?>">
                <button type="submit" name="selesai" style="background:#00c853;color:white;border:none;width:100%;text-align:left;padding:12px 16px;cursor:pointer;">Selesai</button>
            </form>
            <form action="ubah_status.php" method="POST" style="margin:0;">
                <input type="hidden" name="id" value="<?= $pesanan['id'] ?>">
                <input type="hidden" name="nama_pembeli" value="<?= $pesanan['nama_pembeli'] ?>">
                <input type="hidden" name="no_telpon" value="<?= $pesanan['no_telpon'] ?>">
                <button type="submit" name="batalkan" style="background:#e53935;color:white;border:none;width:100%;text-align:left;padding:12px 16px;cursor:pointer;">Batalkan</button>
            </form>
          </div>
        </div>
        <?php

    } else {
        if ($pesanan['status'] == 'Dibatalkan') {
            ?>
            <div class="dropdown">
              <button onclick="toggleDropdown('dropdown<?= $pesanan['id'] ?>')" class="btn-aksi">👁</button>
              <div id="dropdown<?= $pesanan['id'] ?>" class="dropdown-content">
                <a href="detail_pesanan.php?id=<?= $pesanan['id'] ?>" class="btn-detail" style="background:#2196F3;color:white;width:100%;text-align:left;padding:12px 16px;box-sizing:border-box;">Lihat Detail</a>
                <form action="hapus_pesanan.php" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus pesanan ini?')">
                    <input type="hidden" name="id" value="<?= $pesanan['id'] ?>">
                    <button type="submit" class="btn-hapus" style="width:100%;text-align:left;padding:12px 16px;cursor:pointer;">Hapus Pesanan</button>
                </form>
              </div>
            </div>
            <?php
        } else {
            ?>
            <div class="dropdown">
              <button onclick="toggleDropdown('dropdown<?= $pesanan['id'] ?>')" class="btn-aksi">👁</button>
              <div id="dropdown<?= $pesanan['id'] ?>" class="dropdown-content">
                <a href="detail_pesanan.php?id=<?= $pesanan['id'] ?>" class="btn-detail" style="background:#2196F3;color:white;width:100%;text-align:left;padding:12px 16px;box-sizing:border-box;">Lihat Detail</a>
              </div>
            </div>
            <?php
        }
    }

    echo "</td>";
    echo "</tr>";
}
?>

        </tbody>
      </table>
    </section>
  </div>

  <script>
    // Toggle dropdown menu
    function toggleDropdown(id) {
      document.getElementById(id).classList.toggle("show");
    }
    
    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
      if (!event.target.matches('.btn-aksi')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
          var openDropdown = dropdowns[i];
          if (openDropdown.classList.contains('show')) {
            openDropdown.classList.remove('show');
          }
        }
      }
    }
  </script>
</body>
</html>