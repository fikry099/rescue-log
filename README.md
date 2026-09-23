# 🚨 SiGap RESCUE-LOG

> **Platform Manajemen Rantai Pasok Logistik Tanggap Darurat Bencana Terintegrasi Berbasis Progressive Web App (PWA), GIS, dan Machine Learning.**

---

## 📌 Ringkasan Platform

**SiGap RESCUE-LOG** menghadirkan solusi rantai pasok darurat pintar yang mengubah penanganan logistik bencana dari proses manual dan reaktif menjadi ekosistem digital yang terintegrasi, proaktif, transparan, serta berbasis data.

Platform ini dirancang khusus untuk memenuhi standar **6T** (*Tepat Jenis, Tepat Jumlah, Tepat Kualitas, Tepat Sasaran, Tepat Waktu, dan Tepat Biaya*) guna meredam dampak *Bullwhip Effect* saat krisis bencana terjadi.

---

## 🔑 Akun Uji Coba & Akses Role (Testing Credentials)

> 💡 **Informasi Password & Pengujian:**
> Seluruh akun uji coba menggunakan kata sandi (*password*) yang sama: **`password123`**.
> Khusus untuk **Role Petugas Lapangan**, sistem dirancang berbasis **Progressive Web App (PWA) Mobile-First** dengan kemampuan penuh *Offline-First*.

| Role Level | Email Akun | Deskripsi Hak Akses & Fitur Utama |
| :--- | :--- | :--- |
| **1. BNPB Pusat** | `bnpb@rescuelog.id` | Monitoring makro nasional, agregasi data krisis, serta persetujuan (*approval*) eskalasi bantuan tingkat nasional. |
| **2. BPBD Provinsi** | `bpbd.diy@rescuelog.id` | Pengawasan lintas kabupaten/kota, manajemen bantuan provinsi, dan eskalasi logistik ke BNPB. |
| **3. Admin BPBD Kab.** | `admin@bpbd.com` | Manajemen data bencana daerah, verifikasi laporan TRC, pengelolaan stok gudang utama, dan persetujuan alokasi posko. |
| **4. Komando Posko** | `komando.bantul@rescuelog.id` | Manajemen armada pengiriman, penetapan rute distribusi, validasi permintaan sub-posko, dan *Response Center* SOS Ambulans. |
| **📱 5. Petugas Lapangan A (PWA)** | `petugas.lapangan@rescuelog.id` | **PWA Mobile & Offline-First**: Pendataan pengungsi, pengajuan logistik AI, pencatatan penyaluran, dan panggilan darurat SOS. |
| **📱 6. Petugas Lapangan B (PWA)** | `petugas.depok@rescuelog.id` | **PWA Mobile & Offline-First**: Akses alternatif petugas lapangan Sub-Posko 2 untuk pengujian multi-posko secara simultan. |

---

## 🏗️ Arsitektur Sistem (4-Layer Architecture)

Sistem ini dibangun dengan arsitektur **4-Layer Microservices** yang ter-deploy secara terpisah dan terhubung secara publik:

1. **User Layer**: Dashboard Eksekutif berbasis Web Desktop untuk BNPB/BPBD & Mobile PWA *Offline-First* untuk petugas posko lapangan.
2. **Main Application Layer (Laravel 11 Core)**: Mengelola *5-tier Role-Based Access Control (RBAC)*, logika bisnis logistik, dan layanan GIS.
3. **AI/ML Predictive Service (Python FastAPI)**: Engine prediksi kebutuhan logistik (*demand forecasting*) menggunakan model *Random Forest* & *Prophet*.
4. **Database & Spatial Layer (PostgreSQL + PostGIS via Supabase)**: Penyimpanan data relasional dan analisis geospasial/spasial terpusat.

---

## 💡 Fitur Unggulan

- 📱 **Progressive Web App (PWA) Offline-First**: Memungkinkan pencatatan data logistik di area bencana tanpa sinyal internet menggunakan Service Worker v23 & IndexedDB (otomatis sinkronisasi saat *online*).
- 🤖 **AI-Powered Demand Forecasting**: Prediksi kebutuhan logistik secara otomatis berdasarkan demografi pengungsi dan variabel kondisi lapangan.
- 🗺️ **Interactive GIS Map (Leaflet.js)**: Visualisasi spasial titik bencana, lokasi posko, dan status stok logistik secara *real-time*.
- 🔐 **5-Tier Role Access (RBAC)**: Hak akses berjenjang mulai dari **BNPB Pusat**, **BPBD Provinsi**, **Admin BPBD Kabupaten**, **Komando Posko**, hingga **Petugas Lapangan**.

---

## 🌐 Deployed Environment (Live Production)

| Service | Technology | Status / Public URL |
| :--- | :--- | :--- |
| **Laravel Backend & Web App** | Laravel 11, Tailwind CSS, Leaflet | [https://rescue-log.up.railway.app](https://rescue-log.up.railway.app) |
| **ML Predictive Engine** | Python, FastAPI, Scikit-learn | [https://fastapi-ml-production-eeee.up.railway.app](https://fastapi-ml-production-eeee.up.railway.app) |
| **Database Server** | PostgreSQL + PostGIS | Cloud Supabase Session Pooler (Port 6543) |
| **Infrastructure Hosting** | Railway Cloud | Automatic CI/CD Deployment |

---

## ⚙️ Panduan Instalasi & Jalankan Lokal (Local Setup)

### 1. Prasyarat Sistem

- PHP >= 8.3 dengan ekstensi `pdo_pgsql`, `mbstring`, `curl`
- Composer >= 2.x
- Node.js >= 18.x & NPM
- Python >= 3.11 (untuk FastAPI ML Service)

### 2. Setup Backend Laravel

\`\`\`bash
# Clone repository & masuk ke direktori
git clone https://github.com/your-repo/rescue-log.git
cd rescue-log

# Install dependensi PHP & Node.js
composer install
npm install

# Setup Environment File
cp .env.example .env
php artisan key:generate

# Migrasi Database & Seeder
php artisan migrate:fresh --seed

# Build Aset Frontend
npm run build

# Jalankan Server
php artisan serve
\`\`\`

### 3. Setup ML Service (Python FastAPI)

\`\`\`bash
# Masuk ke folder service ML (atau direktori fastapi-ml)
cd ml-service

# Buat virtual environment Python
python -m venv .venv

# Aktifkan virtual environment
# Untuk Linux/macOS:
source .venv/bin/activate
# Untuk Windows (Command Prompt / PowerShell):
# .venv\Scripts\activate

# Install dependensi Python ML
pip install -r requirements.txt

# Jalankan server FastAPI ML
uvicorn main:app --reload --port 8001
\`\`\`

---

## 🛠️ Tech Stack

- **Core Backend**: PHP 8.3+, Laravel 11
- **ML Engine**: Python 3.11+, FastAPI, Pandas, Scikit-Learn
- **Database**: PostgreSQL 15, PostGIS Extension (Supabase)
- **Frontend**: Blade Templates, Tailwind CSS, JavaScript (ES6), Leaflet.js, LocalForage
- **Deployment & Cloud**: Railway Cloud Platform

---

## 👥 Tim Pengembang

**Universitas Jenderal Achmad Yani Yogyakarta**

- Fikri Egnafis
- Risnal Ari Syaputra S. Prakon
- Zuvera Mega Chintia
