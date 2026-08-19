# BLUEPRINT APLIKASI AMI BERBASIS RISIKO & PPEPP

- **Institusi:** STMIK (Sekolah Tinggi Manajemen Informatika dan Komputer)
- **Kepatuhan:** Permendiktisaintek No. 39 Tahun 2025 & Siklus PPEPP
- **Integrasi Utama:** siakadmi.stmik-mi.ac.id, PD DIKTI, SISTER, SINTA

---

## A. Arsitektur Modul Berbasis Siklus PPEPP
Sistem dibagi menjadi 5 kelompok modul utama yang mewakili urutan napas mutu institusi:

### 1. Modul Penetapan (P1)
- **Manajemen Standar Mutu**: Fitur untuk SPMI menginput dan memperbarui Indikator Kinerja Utama (IKU) dan Indikator Kinerja Tambahan (IKT) sesuai regulasi terbaru.
- **Risk Register (Profil Risiko)**: Formulir digital bagi Kaprodi/Unit untuk mendaftarkan potensi risiko (probabilitas vs dampak) di awal tahun akademik. Menghasilkan skor risiko (Low, Medium, High).

### 2. Modul Pelaksanaan (P2)
- **API Gateway & Sinkronisasi**: Mesin penarik data harian/berkala dari sumber eksternal untuk meminimalisir input manual dosen.
- **Manajemen Evidence (E-Filing)**: Tempat Kaprodi/Unit mengunggah dokumen bukti yang tidak ada di sistem lain (misal: MoU, laporan kegiatan).

### 3. Modul Evaluasi (E)
- **Perencanaan Audit**: Generator SK Auditor dan ploting jadwal. Sistem otomatis merekomendasikan Prodi/Unit dengan "Skor Risiko High" untuk diaudit lebih detail.
- **E-Kertas Kerja (Instrumen Audit)**: Daftar tilik digital untuk Desk Evaluation (Audit Dokumen) dan Field Evaluation (Visitasi Lapangan).
- **Manajemen Temuan**: Fitur auditor untuk mencatat Observasi (OB) dan Ketidaksesuaian (KTS), lengkap dengan referensi standar yang dilanggar dan akar masalah.
- **Generator LHA**: Sistem otomatis merangkum temuan menjadi draf Laporan Hasil Audit (LHA) untuk ditandatangani secara elektronik (TTE).

### 4. Modul Pengendalian (P3)
- **Dashboard RTM (Rapat Tinjauan Manajemen)**: Halaman khusus Ketua STMIK untuk memantau LHA, memberikan catatan persetujuan, dan mengeluarkan instruksi pimpinan.
- **Manajemen RTL (Rencana Tindak Lanjut)**: Ruang kerja Auditee untuk menjawab KTS, mengunggah bukti perbaikan, dan fitur bagi Auditor untuk mengubah status temuan menjadi Closed atau Open.

### 5. Modul Peningkatan (P4)
- **Analytics & Trend Mutu**: Grafik perbandingan ketercapaian standar dari tahun ke tahun. Digunakan SPMI sebagai dasar menaikkan standar (P1) untuk siklus tahun berikutnya.

---

## B. Skema Integrasi Data (Interoperabilitas)
Tim IT harus membangun REST API untuk menjembatani aplikasi AMI dengan sistem berikut:

| Sistem Sumber | Data yang Ditarik (Endpoint) | Pemanfaatan di Aplikasi AMI |
| :--- | :--- | :--- |
| **siakadmi.stmik-mi.ac.id** | Mahasiswa aktif, jadwal & presensi dosen, sebaran nilai akhir, kuesioner EDOM. | Otomatisasi pengisian Desk Evaluation Standar Pendidikan. |
| **PD DIKTI (Feeder)** | Rasio dosen-mahasiswa, masa studi, lulusan tepat waktu. | Validasi data internal vs nasional. |
| **SISTER** | BKD, jabatan fungsional, kepangkatan dosen. | Otomatisasi evaluasi Standar SDM (Kecukupan & Kualifikasi). |
| **SINTA** | Jumlah publikasi, sitasi, kekayaan intelektual (HaKI). | Otomatisasi evaluasi Standar Penelitian & PkM. |

---

## C. Alur Manajemen Dokumen (Masuk & Keluar)
Sistem harus memiliki penyimpanan cloud/lokal yang mumpuni untuk mengelola lalu lintas dokumen berikut:

### 📥 Dokumen Masuk (Input Auditee/SPMI)
- Kebijakan, Manual, Standar, dan Formulir SPMI.
- Dokumen Kurikulum, RPS, dan Rubrik Penilaian.
- SK Mengajar, Pembimbingan, dan Penguji.
- Bukti kinerja Tridharma (di luar data integrasi).
- Bukti perbaikan (Tindak Lanjut KTS).

### 📤 Dokumen Keluar (Output/Generate dari Sistem)
- Formulir Risk Register (PDF/Excel).
- Kertas Kerja Audit yang terisi (Checklist).
- Lembar PTK/KTS (Permintaan Tindakan Koreksi).
- LHA (Laporan Hasil Audit) lengkap dengan TTE (Tanda Tangan Elektronik).
- Notulensi & Keputusan RTM (Rapat Tinjauan Manajemen).
- Laporan Progres RTL (Status Open/Closed).

---

## D. Rekomendasi Teknologi (Tech Stack) untuk Tim IT
Agar sistem ini beroperasi secara ringan, memiliki keamanan yang handal, dan mudah di-maintenance, maka direkomendasikan susunan Tech Stack berikut:

- **Backend:** `PHP (Laravel)` atau `Node.js (Express)` karena sangat andal untuk pembuatan API dan manajemen database relasional.
- **Frontend:** `Vue.js` atau `React.js` agar antarmuka (dashboard) interaktif dan loading-nya cepat (Single Page Application / SPA).
- **Database:** `PostgreSQL` atau `MySQL`.
- **Storage:** `Amazon S3` atau `MinIO` (jika menggunakan server lokal kampus) untuk mengelola ratusan file evidence tanpa membebani storage utama server.
- **Keamanan:** Menerapkan otentikasi menggunakan standar `JWT (JSON Web Tokens)` untuk keamanan API serta `Role-Based Access Control (RBAC)` untuk mengatur hak akses setiap level pengguna.
