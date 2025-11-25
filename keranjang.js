document.addEventListener("DOMContentLoaded", () => {

  const cards = document.querySelectorAll(".produk-container .card");
  const totalHargaEl = document.getElementById("total-harga");
  const totalItemEl = document.getElementById("total-item");

  const jenisGlobalSelect = document.getElementById("jenis-global");
  const modal = document.getElementById("checkoutModal");
  const checkoutBtn = document.getElementById("checkout");
  const closeBtn = document.querySelector(".modal .close");
  const metodeSelect = document.getElementById("metode");
  const transferArea = document.getElementById("transferArea");
  const checkoutForm = document.getElementById("checkoutForm");

  let jenisCustomer = "perorangan";
  let keranjang = [];

  /* -------------------------
       UPDATE TAMPILAN
  ------------------------- */
  function updateDisplay() {
    let totalHarga = 0;
    let totalItem = 0;

    const itemList = document.getElementById("list-item");
    const emptyMsg = document.getElementById("empty-msg");
    const diskonEl = document.getElementById("total-diskon");
    const hargaFinalEl = document.getElementById("total-setelah-diskon");
    const diskonValueEl = document.getElementById("diskon-value");
    const hargaFinalValueEl = document.getElementById("harga-final");

    itemList.innerHTML = "";

    keranjang.forEach(item => {
      totalHarga += item.harga * item.qty;
      totalItem += item.qty;

      const li = document.createElement("li");
      li.textContent = `• ${item.nama} (${item.qty}x | Rp${item.harga.toLocaleString("id-ID")})`;
      itemList.appendChild(li);
    });

    totalHargaEl.textContent = "Rp" + totalHarga.toLocaleString("id-ID");
    totalItemEl.textContent = totalItem;

    emptyMsg.style.display = keranjang.length === 0 ? "block" : "none";

    // ---- HITUNG DISKON PERORANGAN ----
    let diskon = 0;
    let totalSetelahDiskon = totalHarga;

    if (jenisCustomer === "perorangan" && totalItem >= 100) {
        diskon = Math.round(totalHarga * 0.05); // 5% diskon
        totalSetelahDiskon = totalHarga - diskon;

        diskonEl.style.display = "block";
        diskonValueEl.textContent = "Rp" + diskon.toLocaleString("id-ID");

        hargaFinalEl.style.display = "block";
        hargaFinalValueEl.textContent = "Rp" + totalSetelahDiskon.toLocaleString("id-ID");
    } else {
        diskonEl.style.display = "none";
        hargaFinalEl.style.display = "none";
    }
}


  /* -------------------------
     Jika ganti tipe customer, sesuaikan harga
  ------------------------- */
  if (jenisGlobalSelect) {
    jenisGlobalSelect.addEventListener("change", () => {
      jenisCustomer = jenisGlobalSelect.value;

      // update tampilan harga di kartu
      cards.forEach(card => {
        const hargaAsli = parseInt(card.dataset.price);
        const hargaBaru = jenisCustomer === "mitra"
          ? Math.round(hargaAsli * 0.95)
          : hargaAsli;

        const hargaElem = card.querySelector(".harga");
        hargaElem.textContent = "Rp" + hargaBaru.toLocaleString("id-ID");
      });

      // update harga dalam keranjang
      keranjang.forEach(item => {
        item.harga_asli = parseInt(item.harga_asli);
        item.harga = jenisCustomer === "mitra"
          ? Math.round(item.harga_asli * 0.95)
          : item.harga_asli;
      });

      updateDisplay();
    });
  }

  /* -------------------------
          PLUS & MINUS
  ------------------------- */
  cards.forEach((card, index) => {

    const plus = card.querySelector(".plus");
    const minus = card.querySelector(".minus");
    const jumlahInput = card.querySelector(".jumlah");

    const nama = card.dataset.name;
    const hargaAsli = parseInt(card.dataset.price);
    const id = index + 1;

    // PLUS
    plus.addEventListener("click", () => {
      let item = keranjang.find(p => p.id === id);

      if (item) {
        item.qty++;

        // Reset harga asli & hitung harga baru sesuai jenis
        item.harga_asli = hargaAsli;
        item.harga = jenisCustomer === "mitra"
          ? Math.round(hargaAsli * 0.8)
          : hargaAsli;

      } else {
        keranjang.push({
          id,
          nama,
          harga_asli: hargaAsli,
          harga: jenisCustomer === "mitra" ? Math.round(hargaAsli * 0.8) : hargaAsli,
          qty: 1
        });
      }

      jumlahInput.value = keranjang.find(p => p.id === id).qty;
      updateDisplay();
    });

    // MINUS
    minus.addEventListener("click", () => {
      let item = keranjang.find(p => p.id === id);
      if (!item) return;

      item.qty--;

      if (item.qty > 0) {
        item.harga_asli = hargaAsli;
        item.harga = jenisCustomer === "mitra"
          ? Math.round(hargaAsli * 0.8)
          : hargaAsli;
      }

      if (item.qty <= 0) {
        keranjang = keranjang.filter(p => p.id !== id);
      }

      jumlahInput.value = item.qty > 0 ? item.qty : 0;
      updateDisplay();
    });

    // Atur jika user mengetik jumlah langsung
    jumlahInput.addEventListener("input", () => {
      // Ubah nilai input menjadi bilangan bulat
      let newQty = parseInt(jumlahInput.value) || 0;
      
      // Pastikan angkanya minimal 0, tidak negatif
      newQty = Math.max(0, Math.min(999, newQty));
      jumlahInput.value = newQty;
      
      let item = keranjang.find(p => p.id === id);
      
      if (newQty > 0) {
        if (item) {
          item.qty = newQty;
          item.harga_asli = hargaAsli;
          item.harga = jenisCustomer === "mitra"
            ? Math.round(hargaAsli * 0.8)
            : hargaAsli;
        } else {
          keranjang.push({
            id,
            nama,
            harga_asli: hargaAsli,
            harga: jenisCustomer === "mitra" ? Math.round(hargaAsli * 0.8) : hargaAsli,
            qty: newQty
          });
        }
      } else if (item) {
        // Hapus item jika jumlahnya 0
        keranjang = keranjang.filter(p => p.id !== id);
      }
      
      updateDisplay();
    });

    // Cegah input selain angka
    jumlahInput.addEventListener("keypress", (e) => {
      // Hanya izinkan angka dan tombol penting seperti hapus, tab, escape, enter
      if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
         // Izinkan shortcut pilih semua (Ctrl+A / Command+A)
          (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
          // Izinkan tombol navigasi (home, end, panah)
          (e.keyCode >= 35 && e.keyCode <= 40)) {
        return;
      }
      
      // Pastikan itu adalah angka dan hentikan penekanan tombol
      if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
      }
    });

  });

  /* -------------------------
            CHECKOUT
  ------------------------- */
  checkoutBtn.addEventListener("click", () => {

    if (keranjang.length === 0) {
      alert("Keranjang masih kosong!");
      return;
    }

    // CEK MINIMAL PESANAN UNTUK MITRA
const totalItem = keranjang.reduce((sum, item) => sum + item.qty, 0);
if (jenisCustomer === "mitra" && totalItem < 100) {
    alert("Minimal pemesanan untuk Mitra adalah 100 pcs!");
     modal.style.display = "none";
    return false; // Stop jika belum mencapai minimal
}
    document.getElementById("jenis_customer").value = jenisCustomer;
    modal.style.display = "block";
  });

  closeBtn.addEventListener("click", () => (modal.style.display = "none"));
  window.addEventListener("click", e => {
    if (e.target === modal) modal.style.display = "none";
  });

  metodeSelect.addEventListener("change", () => {
    transferArea.style.display =
      metodeSelect.value === "transfer" ? "block" : "none";
  });


  /* -------------------------
        SUBMIT ORDER
  ------------------------- */
  checkoutForm.addEventListener("submit", e => {
    e.preventDefault();

    const totalHarga = parseInt(
      totalHargaEl.textContent.replace(/[Rp.]/g, "")
    );
    document.getElementById("input-total-harga").value = totalHarga;

    const items = keranjang.map(i => `${i.nama}|${i.qty}|${i.harga}`);
    document.getElementById("input-daftar-item").value = items.join(",");

    const formData = new FormData(checkoutForm);

    fetch("checkout.php", { method: "POST", body: formData })
      .then(res => res.text())
      .then(() => {
        alert("Pesanan berhasil dikirim!");
        window.location.href = "index.html";
      })
      .catch(err => {
        alert("Terjadi error.");
        console.error(err);
      });
  });

});