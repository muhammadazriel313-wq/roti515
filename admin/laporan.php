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
    <!-- MODIFIKASI: Tambahkan style untuk tombol dan elemen tersembunyi -->
    <style>
      .btn-reset, .btn-history {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 15px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
        border: none;
        cursor: pointer;
      }
      .btn-reset {
        background-color: #007bff; /* Warna biru */
      }
      .btn-reset:hover {
        background-color: #0056b3;
      }
      .btn-history {
        background-color: #28a745; /* Warna hijau */
        margin-left: 5px;
      }
      .btn-history:hover {
        background-color: #218838;
      }
      .hidden-riwayat {
        display: none;
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
            </tr>
          </thead>
          <tbody>
          <?php
          $query = mysqli_query($koneksi, "SELECT * FROM laporan ORDER BY tanggal DESC");
          while ($laporan = mysqli_fetch_assoc($query)) {
              echo "<tr>";
              echo "<td>".$laporan['id']."</td>";
              echo "<td>".$laporan['nama_pembeli']."</td>";
              echo "<td>".$laporan['no_telpon']."</td>";
              echo "<td>".$laporan['alamat']."</td>";
              echo "<td>Rp".number_format($laporan['total_harga'],0,',','.')."</td>";
              echo "<td>".$laporan['metode_pembayaran']."</td>";
              echo "<td class='status Selesai'>selesai</td>";
              echo "<td>".$laporan['tanggal']."</td>";
              echo "</tr>";
          }
          ?>
          </tbody>
        </table>
      </section>

      <!-- TOTAL PENDAPATAN -->
      <section class="cards">
        <?php
        $trackerQuery = mysqli_query($koneksi, "SELECT last_reset_date FROM pendapatan_tracker WHERE id = 1");
        $tracker = mysqli_fetch_assoc($trackerQuery);
        $lastResetDate = $tracker['last_reset_date'];

        $sumQuery = "SELECT SUM(total_harga) AS total_pendapatan FROM laporan";
        if ($lastResetDate) {
            $sumQuery .= " WHERE tanggal > '$lastResetDate'";
        }
        
        $totalPendapatanQuery = mysqli_query($koneksi, $sumQuery);
        $totalPendapatan = mysqli_fetch_assoc($totalPendapatanQuery)['total_pendapatan'];
        ?>
        <div class="card">
          <h3>Total Pendapatan Saat Ini</h3>
          <p>Rp<?= number_format($totalPendapatan,0,',','.') ?></p>
          <!-- MODIFIKASI: Tambahkan tombol riwayat -->
          <div>
              <a href="reset_pendapatan.php" class="btn-reset" onclick="return confirm('Apakah Anda yakin ingin mereset pendapatan saat ini? Pendapatan ini akan disimpan ke riwayat.')">🔄 Reset & Simpan</a>
              <button id="toggleRiwayatBtn" class="btn-history">📈 Lihat Riwayat</button>
          </div>
        </div>
      </section>

      <!-- MODIFIKASI: Bungkus tabel riwayat ke dalam div tersembunyi -->
      <div id="riwayat-container" class="hidden-riwayat">
          <section class="table-section">
              <h2>Riwayat Pemasukan Per Periode</h2>
              <table>
                  <thead>
                      <tr>
                          <th>No.</th>
                          <th>Total Pemasukan</th>
                          <th>Tanggal Dicatat</th>
                          <th>Di-Reset Oleh</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php
                      $riwayatQuery = mysqli_query($koneksi, "SELECT * FROM riwayat_pendapatan ORDER BY tanggal_reset DESC");
                      $no = 1;
                      if(mysqli_num_rows($riwayatQuery) > 0) {
                          while($riwayat = mysqli_fetch_assoc($riwayatQuery)){
                              echo "<tr>";
                              echo "<td>".$no++."</td>";
                              echo "<td>Rp".number_format($riwayat['total_pendapatan'],0,',','.')."</td>";
                              echo "<td>".date('d F Y, H:i', strtotime($riwayat['tanggal_reset']))."</td>";
                              echo "<td>".$riwayat['admin_username']."</td>"; // Tambahan disini//
                              echo "</tr>";
                          }
                      } else {
                          echo "<tr><td colspan='3' style='text-align:center;'>Belum ada riwayat pemasukan.</td></tr>";
                      }
                      ?>
                  </tbody>
              </table>
          </section>
      </div>

      <!-- STOK PRODUK & PRODUK TERJUAL -->
      <section class="cards dua-kotak">
          <?php
          $stokQuery = mysqli_query($koneksi, "SELECT nama_produk, stok FROM produk LIMIT 6");
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

    <!-- MODIFIKASI: Tambahkan JavaScript untuk toggle -->
    <script>
      const toggleBtn = document.getElementById('toggleRiwayatBtn');
      const riwayatContainer = document.getElementById('riwayat-container');

      toggleBtn.addEventListener('click', function() {
        // Cek apakah container sedang disembunyikan
        if (riwayatContainer.classList.contains('hidden-riwayat')) {
          // Jika ya, tampilkan container dan ganti teks tombol
          riwayatContainer.classList.remove('hidden-riwayat');
          toggleBtn.textContent = '📉 Tutup Riwayat';
        } else {
          // Jika tidak, sembunyikan container dan kembalikan teks tombol
          riwayatContainer.classList.add('hidden-riwayat');
          toggleBtn.textContent = '📈 Lihat Riwayat';
        }
      });
    </script>

  </body>
  </html>