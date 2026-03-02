# 🗺️ OpenStreetMap Integration

## Implementasi Peta Gratis dengan Leaflet.js + OpenStreetMap

Aplikasi ini menggunakan **OpenStreetMap** dengan library **Leaflet.js** untuk fitur peta interaktif.

### ✅ **Keuntungan:**

-   **100% GRATIS** selamanya
-   **TANPA API Key** - tidak perlu registrasi
-   **TANPA batasan quota** - unlimited usage
-   Open source dan reliable
-   Data dari komunitas global (seperti Wikipedia untuk peta)

---

## 📍 **Fitur yang Tersedia:**

### 1. **Form Create Bantuan (Customer)**

-   Peta interaktif untuk memilih lokasi
-   Klik pada peta → Marker muncul
-   Marker bisa di-drag untuk pindah posisi
-   Koordinat (latitude, longitude) tersimpan otomatis
-   Auto-detect lokasi user (jika diizinkan browser)

### 2. **Detail Bantuan (Mitra)**

-   Tampilkan lokasi bantuan di peta
-   Marker dengan popup judul bantuan
-   Link "Buka di Peta" → redirect ke Google Maps
-   Koordinat ditampilkan

---

## 🔧 **Teknologi yang Digunakan:**

-   **Leaflet.js v1.9.4** - Library JavaScript untuk peta interaktif
-   **OpenStreetMap Tiles** - Sumber data peta gratis
-   **CDN Integration** - Load dari unpkg.com (fast & reliable)

---

## 🌐 **Cara Kerja:**

### **Di Form Create:**

```javascript
// User klik peta → Marker muncul
map.on("click", function (e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;

    // Tampilkan marker
    L.marker([lat, lng]).addTo(map);

    // Simpan koordinat
    saveCoordinates(lat, lng);
});
```

### **Di Detail View:**

```javascript
// Tampilkan peta dengan marker di lokasi tertentu
const map = L.map("detailMap").setView([lat, lng], 15);
L.marker([lat, lng]).addTo(map).bindPopup("Lokasi Bantuan").openPopup();
```

---

## 📝 **Data yang Disimpan:**

| Field          | Type          | Keterangan                         |
| -------------- | ------------- | ---------------------------------- |
| `latitude`     | decimal(10,8) | Koordinat lintang (-90 s/d 90)     |
| `longitude`    | decimal(11,8) | Koordinat bujur (-180 s/d 180)     |
| `full_address` | text          | Alamat lengkap manual (input user) |
| `location`     | varchar(255)  | Lokasi singkat (opsional)          |

---

## 🔗 **Link External:**

Saat klik "Buka di Peta" di detail view, akan redirect ke:

```
https://www.google.com/maps?q={latitude},{longitude}
```

User bisa pilih aplikasi peta favorit mereka (Google Maps, Waze, dll).

---

## ⚙️ **Konfigurasi (Optional):**

### **Ganti Default Location:**

Edit file `create.blade.php` dan `help-detail.blade.php`:

```javascript
// Ganti koordinat default (saat ini: Ponorogo, Jawa Timur)
const defaultLocation = [-7.8664, 111.462];

// Contoh koordinat lain:
// Jakarta: [-6.2088, 106.8456]
// Surabaya: [-7.2575, 112.7521]
// Bandung: [-6.9175, 107.6191]
```

### **Ganti Zoom Level:**

```javascript
// Zoom level: 1 (dunia) s/d 19 (street level)
const map = L.map("map").setView(location, 13); // 13 = kota
```

### **Ganti Tile Style (Optional):**

```javascript
// Default: OpenStreetMap Standard
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);

// Alternatif tile providers (gratis):
// CartoDB Positron (lebih terang):
// https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png

// CartoDB Dark Matter (mode gelap):
// https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png
```

---

## 🆚 **Perbandingan dengan Google Maps:**

| Aspek               | OpenStreetMap  | Google Maps          |
| ------------------- | -------------- | -------------------- |
| **Biaya**           | 100% GRATIS    | $200 kredit/bulan    |
| **API Key**         | ❌ Tidak perlu | ✅ Wajib             |
| **Registrasi**      | ❌ Tidak perlu | ✅ Wajib (+ billing) |
| **Batasan Quota**   | ❌ Unlimited   | ✅ Ada limit         |
| **Kualitas Peta**   | ⭐⭐⭐⭐       | ⭐⭐⭐⭐⭐           |
| **Update Data**     | Komunitas      | Google               |
| **Offline Support** | ✅ Bisa        | ❌ Terbatas          |

---

## 📚 **Dokumentasi & Resources:**

-   **Leaflet.js Docs:** https://leafletjs.com/reference.html
-   **OpenStreetMap:** https://www.openstreetmap.org/
-   **Tile Providers:** https://leaflet-extras.github.io/leaflet-providers/preview/
-   **Tutorial Leaflet:** https://leafletjs.com/examples.html

---

## 🐛 **Troubleshooting:**

### **Peta tidak muncul (area abu-abu)**

✅ **Solusi:**

-   Cek console browser (F12) untuk error
-   Pastikan `#map` atau `#detailMap` div ada di HTML
-   Pastikan Leaflet CSS sudah di-load

### **Marker tidak muncul saat klik**

✅ **Solusi:**

-   Pastikan event listener sudah aktif
-   Cek console untuk JavaScript errors

### **Peta blur/pecah**

✅ **Solusi:**

-   Refresh halaman
-   Clear browser cache
-   Periksa koneksi internet

---

## 💡 **Tips & Best Practices:**

1. ✅ **Validasi Koordinat:** Pastikan latitude (-90 s/d 90) dan longitude (-180 s/d 180)
2. ✅ **Fallback:** Tetap sediakan input manual untuk alamat lengkap
3. ✅ **Mobile Friendly:** Leaflet responsive by default
4. ✅ **Loading State:** Tambahkan spinner saat peta loading
5. ✅ **Error Handling:** Handle jika user block geolocation

---

## 🎯 **Catatan Penting:**

-   OpenStreetMap **100% legal** untuk penggunaan komersial
-   Data peta di-update oleh komunitas global
-   Tidak ada vendor lock-in (bisa ganti provider kapan saja)
-   Performa sangat baik untuk aplikasi skala kecil-menengah

---

Selamat menggunakan peta gratis! 🗺️✨
