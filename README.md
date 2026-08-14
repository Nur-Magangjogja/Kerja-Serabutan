# Mastulongmas

Project: Mastulongmas — platform bantuan sosial berbasis Laravel + Livewire.

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
