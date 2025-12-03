<?php
include('database/koneksi.php');
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang Belanja - Roti 515</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="satu.css">
  <script defer src="satu.js"></script>
  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.4);
      overflow: auto;
    }
    .modal-content {
      background: #f1ad38;
      margin: 5% auto;
      padding: 20px;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      position: relative;
      max-height: 90vh;
      overflow-y: auto;
    }
    .close {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 24px;
      cursor: pointer;
    }
    button.btn {
      background: #c97b3c;
      border: none;
      padding: 10px 20px;
      color: white;
      border-radius: 8px;
      cursor: pointer;
    }
    button.btn:hover {
      background: #a15e29;
    }
    /* Tambahkan style untuk input quantity */
    .quantity input {
      width: 50px;
      text-align: center;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 5px;
    }
    
  </style>
</head>
<body>
  <!-- Header -->
 <header class="navbar">

  <!-- BAGIAN YANG BISA DIKLIK -->
  <div class="nav-toggle" id="toggleMenu">
      <img src="logo 515.png" id="logoMenu">
      <div class="logo">ROTI<span>515</span></div>
  </div>
   <!-- Hamburger Menu (mobile only) -->
<div class="hamburger" id="hamburger">
  <span></span>
  <span></span>
  <span></span>
</div>


  <!-- MENU NAVBAR -->
  <nav>
    <ul id="menuList">
      <li><a href="index.php" class="nav-link">Beranda</a></li>
      <li><a href="tentang.php" class="nav-link">Tentang</a></li>
      <li><a href="produk.php" class="nav-link">Produk</a></li>
      <li><a href="kontak.php" class="nav-link">Kontak</a></li>
      <li class="cart-icon">
        <a href="keranjang.php" class="nav-link">🛒 Keranjang</a>
      </li>
    </ul>
  </nav>
</header>

  <!-- Kotak menu mobile overlay -->
<div class="mobile-menu" id="mobileMenu">
  <ul>
    <li><a href="index.php">Beranda</a></li>
    <li><a href="tentang.php">Tentang</a></li>
    <li><a href="produk.php">Produk</a></li>
    <li><a href="kontak.php">Kontak</a></li>
    <li><a href="keranjang.php">🛒 Keranjang</a></li>
  </ul>
</div>
<section id="produk" class="produk section">
  <h2>Belanja Produk</h2>

    <select id="jenis-global" style="padding:8px;border-radius:6px;margin-bottom:15px;">
  <option value="perorangan">Perorangan</option>
  <option value="mitra">Mitra</option>
</select>

  <div class="produk-container">
    <?php
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status='Aktif' ORDER BY id DESC");
    while ($p = mysqli_fetch_assoc($query)) {
        // Harga default
 $harga = isset($p['harga']) ? $p['harga'] : 4000;

// Harga manual per-produk
switch ($p['nama_produk']) {

    case "Pia Kacang":
        $harga = 4000;
        break;

    case "Roti Marie Kelapa":
        $harga = 4000;
        break;

    case "Roti Plemben":
        $harga = 4000;
        break;

    case "Roti Wijen":
        $harga = 4000;
        break;

    case "Onde-Onde Ketawa":
        $harga = 4000;
        break;

    case "Roti Susu":
        $harga = 4000;
        break;
}

        echo '
        <div class="card" 
        data-price="'.$harga.'" 
        data-name="'.htmlspecialchars($p['nama_produk']).'" 
        data-stok="'.$p['stok'].'">
          <img src="uploads/'.htmlspecialchars($p['gambar']).'" alt="'.htmlspecialchars($p['nama_produk']).'">
          <h3>'.htmlspecialchars($p['nama_produk']).'</h3>
          <p>'.htmlspecialchars($p['deskripsi']).'</p>
          <p class="harga">Rp'.number_format($harga, 0, ',', '.').'</p>
          <div class="quantity">
            <button class="minus">-</button>
            <input type="number" class="jumlah" value="0" min="0" max="999">
            <button class="plus">+</button>
          </div>
        </div>
        ';
    }
    ?>
  </div>

  <div class="total-belanja" style="text-align:center; margin-top:30px;">
    <h3>Total Belanja: <span id="total-harga">Rp0</span></h3>
    <p id="total-diskon" style="font-size:18px; color:yellow; font-weight:bold; display:none;">
  Diskon: <span id="diskon-value">Rp0</span>
</p>

<p id="total-setelah-diskon" style="font-size:20px; color:#00ff7f; font-weight:bold; display:none;">
  Total Setelah Diskon: <span id="harga-final">Rp0</span>
</p>

    <p><strong>Jumlah Item:</strong> <span id="total-item">0</span></p>

    <div id="daftar-belanja" style="margin:10px auto; text-align:left; display:inline-block;">
      <p id="empty-msg" style="color:#ccc; font-style:italic; text-align:center;">
        Belum ada item di keranjang.
      </p>
      <ul id="list-item" style="list-style:none; padding:0; margin:0; color:#fff; text-align:left;"></ul>
    </div>

    <button class="btn" id="checkout" style="margin-top:15px; display:block; margin-left:auto; margin-right:auto;">
      Checkout
    </button>
  </div>

</section>


<!-- ======================= -->
<!--      MODAL CHECKOUT     -->
<!-- ======================= -->
<div id="checkoutModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Form Checkout</h2>
    
    <!-- TIPS: Tambahkan keterangan di atas form -->
    <p><small>Field yang ditandai dengan <span style="color: red;">*</span> wajib diisi.</small></p>
    <hr>

    <form id="checkoutForm" action="checkout.php" method="POST" enctype="multipart/form-data">

      <!-- PILIHAN JENIS CUSTOMER (DIUBAH KEMBALI KE SELECT) -->
      <label>Jenis Customer <span style="color: red;">*</span></label>
      <select name="jenis_customer" id="jenis_customer" required 
        style="padding:6px;border-radius:5px;border:1px solid #ccc;">
        <option value="">--Pilih Jenis--</option>
        <option value="perorangan">Perorangan</option>
        <option value="mitra">Mitra</option>
      </select>

      <br>

      <label>Nama <span style="color: red;">*</span></label>
      <input type="text" name="nama" required style="padding:6px;border-radius:5px;border:1px solid #ccc;">
      
      <label>Nomor Telp / WA <span style="color: red;">*</span></label>
      <input type="tel" name="no_telp" required style="padding:6px;border-radius:5px;border:1px solid #ccc;">
      <small style="font-size: 12px; color: #666; display: block; margin-top: 5px;">Cth: 6281234567890 (bukan 0 atau +62)</small>
      
      <label>Alamat <span style="color: red;">*</span></label>
      <textarea name="alamat" required style="padding:6px;border-radius:5px;border:1px solid #ccc;"></textarea>

      <label>Metode Pembayaran <span style="color: red;">*</span></label>
      <select id="metode" name="metode_pembayaran" required style="padding:6px;border-radius:5px;border:1px solid #ccc;">
        <option value="">--Pilih Metode--</option>
        <option value="transfer">Transfer Bank</option>
        <option value="cod">Cash on Delivery (COD)</option>
      </select>

      <div id="transferArea" style="display:none; margin-top:10px;">
        <p><strong>Transfer ke rekening:</strong></p>
        <p id="noRek">BRI  a.n. Roti 515</p>
          <p>(Silahkan Upload Bukti Pembayaran di WA Kami)</p>
      </div>

      <input type="hidden" name="total_harga" id="input-total-harga" value="0">
      <input type="hidden" name="daftar_item" id="input-daftar-item" value="">

      <button type="submit" name="pesan"
        style="background:#a15e29;color:#fff;padding:10px;border:none;border-radius:5px;cursor:pointer;">
        Pesan Sekarang
      </button>

    </form>

  </div>
</div>

<!-- POPUP PERINGATAN STOK -->
<div id="popupStok" class="modal">
  <div class="modal-content" style="background:#ff0000;">
    <span class="closeStok" style="float:right;cursor:pointer;font-size:22px;">&times;</span>
    <h3 style="color:white;">Peringatan!</h3>
    <p id="pesanStok" style="color:white;font-size:18px;font-weight:bold;"></p>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {

  // --- ELEMEN YANG SERING DIGUNAKAN ---
  const popupStok = document.getElementById("popupStok");
  const pesanStok = document.getElementById("pesanStok");
  const checkoutModal = document.getElementById("checkoutModal");
  const jenisCustomerSelect = document.getElementById("jenis_customer");
  const mitraArea = document.getElementById("mitraArea");
  const namaTokoInput = document.getElementById('nama_toko');

  // --- LOGIKA UNTUK POPUP PERINGATAN (STOK / KOSONG) ---
  document.querySelector(".closeStok").onclick = () => {
    popupStok.style.display = "none";  
    // Tidak perlu menutup checkoutModal di sini, karena jika keranjang kosong, modal tidak akan pernah terbuka
  };
  
  // --- LOGIKA UNTUK HAMBURGER MENU ---
  const hamburger = document.getElementById("hamburger");
  const menu = document.querySelector("nav ul");
  hamburger.addEventListener("click", () => {
    menu.classList.toggle("show");
    hamburger.classList.toggle("active");
  });
  menu.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", () => {
      menu.classList.remove("show");
      hamburger.classList.remove("active");
    });
  });

  // --- LOGIKA UNTUK MENGATUR PERUBAHAN DI DROPDOWN JENIS CUSTOMER ---
  function updateJenisCustomer(selectedValue) {
    if (selectedValue === 'mitra') {
      mitraArea.style.display = "block";
      namaTokoInput.setAttribute('required', '');
    } else {
      mitraArea.style.display = "none";
      namaTokoInput.removeAttribute('required');
    }
  }
  
  // Event listener jika user mengubah dropdown secara manual
  jenisCustomerSelect.addEventListener('change', function() {
    updateJenisCustomer(this.value);
  });

  // --- LOGIKA UTAMA SAAT TOMBOL CHECKOUT DIKLIK ---
  document.getElementById("checkout").addEventListener("click", function(e) {
    
    // 0. CEK KERANJANG KOSONG (DITAMBAHKAN)
    let totalItem = parseInt(document.getElementById("total-item").innerText);
    if (totalItem === 0) {
      return; // Hentikan fungsi di sini
    }

    // 1. CEK STOK TERLEBIH DAHULU
    let cards = document.querySelectorAll(".card");
    let adaErrorStok = false;
    let pesanErrorStok = "";

    cards.forEach(card => {
      let stok = parseInt(card.getAttribute("data-stok"));
      let qty = parseInt(card.querySelector(".jumlah").value);

      if (qty > stok) {
        adaErrorStok = true;
        pesanErrorStok += `Pembelian ${card.getAttribute("data-name")} melebihi stok tersedia.<br>`;
      }
    });

    if (adaErrorStok) {
      e.preventDefault();
      pesanStok.innerHTML = pesanErrorStok; // Tampilkan pesan error stok
      popupStok.style.display = "block";
      return; // Hentikan fungsi
    }

    // 2. JIKA TIDAK ADA ERROR, LANJUTKAN PROSES CHECKOUT
    checkoutModal.style.display = "block";

    // 3. LOGIKA UNTUK MENGUBAH JENIS CUSTOMER SECARA OTOMATIS
    setTimeout(() => {
      let jenisGlobal = document.getElementById("jenis-global").value;
      let finalJenisCustomer = '';
      
      if (jenisGlobal === "mitra") {
        finalJenisCustomer = 'mitra';
      } else { // jika perorangan
        if (totalItem >= 100) {
          finalJenisCustomer = 'mitra';
        } else {
          finalJenisCustomer = 'perorangan';
        }
      }

      // Atur nilai dropdown dan tampilan area mitra
      jenisCustomerSelect.value = finalJenisCustomer;
      updateJenisCustomer(finalJenisCustomer);

    }, 100); // delay singkat supaya modal udah muncul sempurna
  });
  
  // --- LOGIKA UNTUK TUTUP MODAL CHECKOUT ---
  document.querySelector(".close").onclick = () => {
    checkoutModal.style.display = "none";
  };
});
</script>

<script src="keranjang.js"></script>

</body>
</html>