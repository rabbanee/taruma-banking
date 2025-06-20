# 💳 M-Banking Backend – Laravel 12

Sistem backend M-Banking sederhana berbasis **Laravel 12**, menggunakan **Breeze + Vue + Tailwind**. Project ini mencakup fitur autentikasi, transaksi PLN, dan manajemen saldo pengguna.

---

## 🚀 Fitur Utama

- ✅ Autentikasi (Login, Register, Logout)
- 🔐 Middleware proteksi user login
- 💡 Simulasi pembayaran PLN
- 💰 Penambahan saldo awal melalui seeder
- 📦 Struktur kode rapi dan scalable

---

## ⚙️ Setup Awal Project

### 1️⃣ Clone Repo & Install Dependency

```bash
git clone https://github.com/rabbnee/taruma-banking.git
cd taruma-banking

composer install
npm install && npm run dev
```

### 2️⃣ Konfigurasi File .env
```bash
cp .env.example .env
php artisan key:generate
```

Konfigurasikan database di .env:
```bash
DB_DATABASE=mbanking
DB_USERNAME=root
DB_PASSWORD=
```
### 3️⃣ Jalankan Seeder Otomatis
Untuk menghapus semua tabel, migrasi ulang, dan seed data dummy:
```bash
php artisan migrate:refresh --seed
```
