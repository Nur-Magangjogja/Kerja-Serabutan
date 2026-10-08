# Sayabantu

Project: Sayabantu — platform bantuan sosial untuk kerja serabutan berbasis Laravel + Livewire.

This repository contains the application code used for managing help requests (customers) and volunteers/providers (mitra).

---

## Quick Setup (local)

1. Install dependencies

  composer install
  npm install

2. Copy `.env` and generate app key

  cp .env.example .env
  php artisan key:generate

3. Configure `.env` (DB, mail, services)

4. Run migrations & seeders

  php artisan migrate --seed

5. Create storage link

  php artisan storage:link

6. Build assets (dev)

  npm run dev

7. Serve

  php artisan serve

---

## Useful Commands

- Clear caches: `php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear`
- Run tests: `php artisan test`
- Create Livewire component: `php artisan make:livewire Name`

---

## Layanan Terminal & Otomatisasi Background (Development Services)

Untuk menjalankan seluruh ekosistem fitur **SayaBantu** (Web, Real-time Chat, Scheduler, Pembayaran, dan Antrean), berikut daftar layanan yang dibutuhkan:

| No | Perintah Terminal | Fungsi Utama | Status / Keterangan |
|---|---|---|---|
| 1 | `php artisan serve` | Web server aplikasi utama | Sudah Aktif |
| 2 | `npm run dev` | Asset bundler (Tailwind, JS/Alpine) | Sudah Aktif |
| 3 | `php artisan reverb:start` | Server WebSocket (Chat & GPS Live) | Dibutuhkan (Dapat dijalankan via terminal atau `start-reverb-silent.vbs`) |
| 4 | `php artisan schedule:work` | Menjalankan scheduler (Timeout 10 menit, reminder, auto-cancel) | Dibutuhkan (Dapat dijalankan via terminal atau `start-scheduler-silent.vbs`) |
| 5 | `php artisan queue:work` | Memproses antrean pesan chat, webhook pembayaran, dan job sistem | Dibutuhkan (Dapat dijalankan via terminal atau `start-queue-silent.vbs`) |
| 6 | `ngrok http 8000` *(Opsional)* | Meneruskan koneksi internet ke localhost untuk pengujian webhook Midtrans | Hanya jika sedang menguji pembayaran online |

### Otomatisasi Layanan Sekali Klik (Development Launcher)

Seluruh layanan background di atas telah diotomatiskan sehingga Anda tidak perlu membuka banyak tab terminal secara manual:

1. **Peluncur Utama (`start-dev.bat`)**:
   Cukup jalankan `start-dev.bat` (atau klik dua kali), sistem akan secara otomatis menjalankan:
   - Laravel Reverb (Background silent)
   - Laravel Scheduler (`schedule:work`) (Background silent)
   - Laravel Queue Worker (`queue:work --queue=default,broadcast`) (Background silent)
   - Laravel Server (`php artisan serve` di jendela baru)
   - Vite Asset Bundler (`npm run dev` di jendela aktif)

2. **Otomatis Saat Komputer Dinyalakan (Windows Startup)**:
   Jika ingin Reverb, Scheduler, dan Queue Worker otomatis berjalan sejak Windows dinyalakan:
   - Tekan `Win + R`, ketik `shell:startup`, lalu tekan `Enter`.
   - Buat shortcut dari file `start-all-background-silent.vbs` ke dalam folder tersebut.

3. **Menghentikan Layanan Latar Belakang (`stop-all-services.bat`)**:
   Untuk mematikan seluruh proses latar belakang (Reverb, Scheduler, Queue) saat selesai bekerja, cukup jalankan `stop-all-services.bat`.

4. **Pengujian / Testing (Manual)**:
   Pengujian otomatis dijalankan secara mandiri saat dibutuhkan menggunakan:
   ```bash
   php artisan test
   ```

---

## Important Paths

- Livewire components (customer): `app/Livewire/Customer`
- Views (customer helps): `resources/views/livewire/customer/helps`
- Routes: `routes/web.php`

---

## Notes (recent changes)

- A new **Selesai** tab was added to the customer helps index to display completed helps in history style.
- A temporary experiment to make `location` and schedule fields required for mitra on the create form was added and then reverted — the customer create form is back to original behavior.

If you want a separate create flow for mitra (with required fields) I can add a new Livewire component and route so both flows coexist.

---

If you'd like the README expanded (developer guide, deployment, architecture diagram), tell me what sections to include and I'll update it.

## 🎯 Fitur yang Sudah Diimplementasikan

✅ Sistem autentikasi dengan Laravel Breeze
✅ Role-based access control (super_admin, admin, kustomer, mitra)
✅ Tampilan mobile-first dengan max-width 420px
✅ Bottom navigation seperti aplikasi mobile
✅ Halaman home dengan daftar bantuan
✅ Filter bantuan berdasarkan kategori dan kota
✅ Dashboard untuk kustomer dan mitra
✅ Form create permintaan bantuan (dengan upload foto)
✅ Mitra bisa ambil dan selesaikan bantuan
✅ Status tracking bantuan
✅ Seeder data sample (cities, categories, users, helps)

## 🚧 Fitur yang Masih Dalam Pengembangan

-   [ ] Super Admin dashboard dan CRUD lengkap
-   [ ] Admin Kota dashboard untuk verifikasi KTP dan moderasi
-   [ ] Sistem rating dan review lengkap
-   [ ] Notifikasi real-time (Laravel Echo + Pusher)
-   [ ] Upload dan verifikasi KTP otomatis
-   [ ] Sistem langganan premium untuk mitra
-   [ ] Export laporan PDF
-   [ ] Chat antara kustomer dan mitra
-   [ ] Maps integration untuk lokasi
-   [ ] Push notification

## 📝 Catatan Penting

1. **Verifikasi KTP**: Saat ini semua user test sudah di-set `verified = true`
2. **Auto Approve**: Untuk demo, posting bantuan langsung approved (status = 'approved')
3. **File Upload**: Jangan lupa jalankan `php artisan storage:link`
4. **Bottom Navigation**: Hanya tampil untuk user yang sudah login
5. **Mobile View**: Buka di browser dengan width kecil atau gunakan dev tools mobile view untuk pengalaman terbaik
6. **Sample Data**: Sudah ada 5 sample posting bantuan dari kustomer "Budi Santoso"

## 🏃 Quick Start

```bash
# 1. Install dependencies
composer install && npm install

# 2. Setup database
php artisan migrate:fresh --seed

# 3. Link storage
php artisan storage:link

# 4. Build assets
npm run build

# 5. Run server
php artisan serve
```

Buka `http://localhost:8000` dan login dengan salah satu akun di atas!

## 🤝 Kontribusi

Aplikasi ini dibuat untuk tujuan pembelajaran dan sosial. Silakan fork dan kembangkan sesuai kebutuhan Anda!

## 📄 Lisensi

Open source untuk tujuan pembelajaran dan kemanusiaan.

## 📞 Support

Untuk pertanyaan atau bantuan, silakan buat issue di repository ini.

---

**Dibuat dengan ❤️ untuk Indonesia yang lebih baik**
