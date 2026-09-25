# Alur & Flow Setiap Role — Dilengkapi Studi Kasus

Dokumen ini adalah panduan praktis **langkah demi langkah** untuk setiap peran di aplikasi SPMI, dilengkapi **studi kasus nyata** (data mengacu pada akun & master data yang ada di sistem).

> Untuk ringkasan alur tanpa contoh, lihat [`ALUR-PENGGUNAAN.md`](ALUR-PENGGUNAAN.md).

---

## Kasus Utama (Konteks Bersama)

**Kasus: Menjalankan siklus AMI Ganjil 2026/2027 untuk 3 auditee.**

| Item | Nilai |
|---|---|
| Siklus AMI | `AMI 2026/2027 - Ganjil`, periode 1 Sep – 20 Des 2026 |
| Auditee | **Teknik Informatika** (prodi), **Komputerisasi Akuntansi** (prodi), **Perpustakaan** (unit) |
| Auditor | `auditor@spmi.ac.id` (Auditor Internal) |
| Standar dipakai | S.01, S.02, S.03, S.04 (Contoh: "Standar Pendidikan") |
| SK Penetapan | `24/A/SK-AMI/IX/2026` — Penetapan Tim Auditor Mutu Internal |
| Temuan utama pada audit TI | 1 KTS Mayor (proses pembelajaran tidak terdokumentasi), 2 OB |
| Hasil akhir | Laporan AMI PDF 12 halaman terarsip + risalah RTM disahkan |

**Pemain dalam kasus ini (akun yang sudah ada di sistem):**

| Nama | Akun | Peran |
|---|---|---|
| Administrator SPMI | `admin@spmi.ac.id` | administrator |
| Tim SPMI / LPM | `spmi@spmi.ac.id` | spmi |
| Ketua STMIK Mardira Indonesia | `ketua@spmi.ac.id` | pimpinan |
| Wakil Ketua | `wakil@spmi.ac.id` | pimpinan |
| Auditor Internal | `auditor@spmi.ac.id` | auditor |
| Kaprodi Teknik Informatika | `ti@spmi.ac.id` | prodi |
| Kepala Unit Layanan | `unit@spmi.ac.id` | unit |

---

## 1. Administrator — Kasus: "Menyiapkan Sistem Sebelum Siklus Dimulai"

**Tujuan:** seluruh prasyarat siap agar SPMI bisa memulai siklus tanpa hambatan.

**Langkah:**

1. **Login** `admin@spmi.ac.id` → Dashboard.
2. **Tahun Akademik** → *Pengaturan → Tahun Akademik* → buat periode **2026/2027** (Ganjil & Genap).
3. **Master Data** → pastikan **Program Studi** (Teknik Informatika, Komputerisasi Akuntansi, Manajemen Informatika) dan **Unit Kerja** (Perpustakaan, Laboratorium, dst.) sudah terdaftar lengkap dengan nama kepala.
4. **Manajemen User** → buat akun baru bila perlu (mis. `perpustakaan@spmi.ac.id` untuk kepala Perpustakaan) dan **hubungkan akun unit/prodi ke master datanya** — inilah yang membuat akun tersebut otomatis hanya melihat data miliknya.
5. **Konfigurasi Aplikasi** → atur:
   - Nama institusi (dipakai di kop PDF: *STMIK Mardira Indonesia*)
   - Logo
   - **Kode dokumen** (mis. `STMIKMI.LPMI.AMI.VIII.1`) & **Edisi** (`1`) → tampil di header tiap halaman laporan
   - **Cover halaman pertama** → unggah gambar cover (scan cover dari klien). Bila dikosongkan, sistem memakai cover teks otomatis.
6. **Halaman Publik** → perbarui profil & berita institusi.

**Studi kasus mini — salah cover:** klien mengirim cover revisi, unggah di Konfigurasi → semua laporan berikutnya otomatis memakai cover terbaru, tanpa perlu regenerasi manual.

> **Penting:** hanya Administrator yang bisa mengatur cover, kode, dan edisi. SPMI memakai hasilnya, tidak mengubahnya.

---

## 2. SPMI/LPM — Kasus: "Menjalankan AMI Ganjil 2026/2027 dari Nol sampai Laporan"

Ini alur terpanjang; dibagi menjadi 6 tahap.

### Tahap A — Penetapan (P1)

1. **Dokumen Mutu** → unggah Kebijakan Mutu & Manual Mutu (pilih kategori, isi versi & tanggal).
2. **SK Penetapan Standar** → *P1 → SK Penetapan Standar → Buat SK*:
   - Nomor `24/A/SK-AMI/IX/2026`, judul *Penetapan Tim Auditor Mutu Internal*, tanggal 1 Sep 2026
   - Status awal `draft` → **Kirim ke Pimpinan** → `menunggu_persetujuan`
3. **Standar Mutu & Indikator** → pastikan standar S.01–S.04 aktif dan indikatornya lengkap.
4. **Daftar Tilik (Instrumen)** → instrumen ini dipakai oleh Evaluasi Diri maupun auditor.

### Tahap B — Persiapan Pelaksanaan

5. **Profil Risiko Institusi** → perbarui Risk Register semester ini (skor dampak × likelihood) untuk tiap prodi/unit.
6. **Siklus AMI** → *P3 → Siklus AMI* → buat `AMI 2026/2027 - Ganjil` (status `draft` → **Aktifkan** → `aktif`).
7. **Alokasi Auditor & Surat Tugas** → buat penugasan:
   - Teknik Informatika → auditor@spmi.ac.id
   - Komputerisasi Akuntansi → auditor@spmi.ac.id
   - Perpustakaan → auditor@spmi.ac.id
   - Terbitkan **SK Penugasan Auditor**.
8. **Manajemen Survei** (opsional) → buat survei kepuasan bila diperlukan.

### Tahap C — Verifikasi Evaluasi Diri

9. **Monitoring ED** → *P2 → Monitoring Evaluasi Diri*:
   - ED Teknik Informatika masuk `submitted` → **Verifikasi** + isi kesimpulan → `verified`
   - ED Perpustakaan masih `draft` → **reminder** ke kepala unit lewat menu terkait.

### Tahap D — Audit & Persetujuan LHA

10. **Pantau Kinerja Auditor** (Kertas Kerja) → pantau progres; auditor yang belum mengisi borang bisa diklik **Reminder**.
11. Setelah auditor **finalisasi** penugasan → **Review Detail** tiap auditee di halaman Laporan AMI.
12. **Approve LHA** → status penugasan menjadi `selesai`. Bila data belum lengkap, kirim **Reminder** ke auditor.

### Tahap E — Tindak Lanjut (RTL)

13. **RTL** → untuk setiap temuan KTS, buat keputusan:
    - **Setujui** → LHA disahkan, temuan ditutup
    - **Tolak** → diminta revisi, status kembali `berlangsung`
    - **Eskalasi** → sistem membuat entri RTM otomatis (bila belum ada)
14. Isi **catatan keputusan** agar terekam di Audit Trail.

### Tahap F — Laporan AMI (fitur terbaru)

15. **Laporan AMI** → pilih siklus `AMI 2026/2027 - Ganjil`, jenis **AMI Klasik** → **Lihat Ringkasan** (KTS Mayor/Minor/OB per auditee).
16. **Generate Laporan** → masuk **Daftar Generate Laporan** → **Detail**:
    - Lampiran **terisi otomatis**: SK `24/A/SK-AMI/IX/2026`, dokumen siklus, bukti ED & temuan
    - **Tambah Daftar Hadir**: *Tambah Lampiran → Upload File* (PDF/gambar hasil scan) atau *Dari Data Existing*
    - **Geser ↑/↓** untuk mengatur urutan halaman (mis. SK → Daftar Hadir → Bukti Kegiatan)
    - **Generate PDF** → badan laporan (cover, pengesahan, BAB I–IV, Daftar Tilik, Daftar Lampiran) + lampiran digabung sesuai urutan
17. **Download PDF** → arsip tersimpan; bila susunan lampiran diubah, status menjadi "Perlu Generate Ulang".

### Tahap G — RTM & Peningkatan Standar (P5)

18. **RTM** → buat jadwal rapat dengan agenda dari temuan AMI → **undang peserta** → isi **notulensi** → status `notulensi` → kirim ke **`Pimpinan`** untuk disahkan → **`disahkan`** (risalah PDF terkunci).
19. **Peninjauan & Revisi Standar** → tinjau versi S.01, ubah isi standar → status *Revisi Draft* → **SK Perubahan Standar** → `menunggu_persetujuan` → ditetapkan → versi standar naik.

**Studi kasus mini — laporan kurang rapi:** tahun lalu semua bukti kegiatan berantakan dan tidak ada urutannya. Sekarang SPMI mengunggah Daftar Hadir lewat tombol **Upload File**, menggesernya ke posisi ke-2, lalu **Generate PDF** — hasilnya: SK di halaman lampiran 1, Daftar Hadir di lampiran 2, dst., dan bisa diunduh ulang kapan saja tanpa mengulang pekerjaan.

---

## 3. Pimpinan — Kasus: "Menyetujui SK, Memberi Catatan RTL, Mengesahkan RTM"

**Tujuan:** menjadi titik persetujuan sehingga proses mutu dapat berjalan.

**Langkah & contoh:**

1. **Dashboard** → pantau temuan terbuka & capaian siklus.
2. **SK Penetapan Standar** → *P1 → SK Penetapan Standar*:
   - SK `24/A/SK-AMI/IX/2026` berstatus `menunggu_persetujuan` → **Tetapkan** (`ditetapkan`) → SPMI bisa mengunggah file SK final
   - Alternatif: **Tolak** dengan alasan — SPMI diperbaiki lalu kirim ulang.
3. **SK Perubahan Standar** → setujui revisi standar S.01 versi baru.
4. **SK Penugasan Auditor** → pastikan SK penugasan sudah ditetapkan sebelum audit berjalan.
5. **Rapat Tindak Lanjut (RTL)** → untuk KTS Mayor pada Teknik Informatika, isi **catatan pimpinan**: "Perbaikan capstone wajib selesai akhir Oktober, dipresentasikan oleh Kaprodi."
6. **Rapat Tinjauan Manajemen (RTM)** → tinjau notulensi rapat → **Sahkan** → risalah terkunci, auditee & SPMI dapat mengunduh.
7. **Laporan AMI & RTM** → tinjau laporan yang telah dihasilkan SPMI sebagai bahan rapat berikutnya.

---

## 4. Auditor — Kasus: "Audit Teknik Informatika (12 borang, 1 KTS Mayor, 2 OB)"

**Tujuan:** menilai secara objektif & mendokumentasikan temuan.

**Langkah & contoh:**

1. **Jadwal Penugasan Saya** → cek 3 penugasan aktif + SK Penugasan.
2. **Lihat Evaluasi Diri (ED)** → baca klaim TI (mis. skor rata-rata 3,40 dari ED) sebagai pembanding.
3. **Kertas Kerja Auditor** → isi borang per standar:
   - Standar S.02 (pelatihan): skor 2,20, kategori **KTS Mayor** — "Dokumen kurikulum belum tersedia di unit"
   - Standar S.03: skor 3,00, kategori **KTS Minor**
   - Standar S.04: skor 3,80, kategori **Sesuai dengan Standar**
4. **Buat Temuan**:
   - `KTS` (Kriteria Tidak Terpenuhi) untuk KTS Mayor — lengkapi kriteria, analisis, **unggah bukti** (foto kegiatan, berita acara)
   - `OB` (Observasi) ×2 — catatan perbaikan tata kelola
5. **Finalisasi** penugasan → status `finalisasi` → tunggu **Approve LHA** dari SPMI.
6. **Panduan Etik Auditor** → rujukan independen kapanpun diperlukan.

---

## 5. Program Studi (Kaprodi) — Kasus: "Teknik Informatika Mengisi ED + Bukti Daftar Hadir"

**Tujuan:** melakukan self-assessment yang jujur & terdokumentasi.

**Langkah & contoh:**

1. **Info Jadwal Audit** → cek jadwal audit unit Anda (mis. 10–20 Oktober 2026).
2. **Isi Evaluasi Diri (ED)**:
   - Buat ED `AMI 2026/2027 Ganjil` (status `draft`)
   - **Generate butir** dari **Daftar Tilik** (S.01–S.04)
   - Isi skor, deskripsi, dan **bukti** per butir:
     - *Notulen* rapat prodi (PDF)
     - **Daftar Hadir** (scan JPG/PDF) ← **ini yang akan muncul di lampiran Laporan AMI**
     - *Dokumentasi/Foto* kegiatan
   - **Submit** → `submitted` (menunggu verifikasi SPMI)
3. **Tindak Lanjut Temuan** → setelah KTS Mayor dari auditor, isi keputusan & target perbaikan.
4. **Risk Register** → perbarui risiko prodi bila ada perubahan.
5. **Risalah RTM (Disahkan)** → baca instruksi tindak lanjut dari rapat yang telah disahkan.

**Tips:** karena berkas bukti otomatis ikut ke lampiran Laporan AMI, upload **file yang rapi & bernama jelas** (mis. `Daftar-Hadir-Rapat-Prodi-Sept-2026.pdf`).

---

## 6. Unit Kerja — Kasus: "Perpustakaan Menyusun ED + SOP"

1. **Isi ED** seperti prodi, dengan bukti khas unit (mis. statistik kunjungan, berita acara).
2. **Manajemen SOP** → susun SOP layanan (mis. *SOP Pengembalian Buku*), ajukan ke SPMI → **Validasi SOP** oleh SPMI.
3. **Info Jadwal Audit**, **Risk Register**, dan **Risalah RTM** berfungsi sama seperti prodi.

---

## 7. Pengunjung (Guest)

1. **Beranda** → profil institusi, program studi, dan berita.
2. **Dokumen Publik** → unduh dokumen yang berstatus publik.
3. Tidak dapat mengubah data apa pun.

---

## 8. Alur Lintas Role dalam Satu Kasus (Ringkasan)

```
[Admin]  Siapkan: TA, prodi/unit, user, cover & kode laporan
   ↓
[SPMI]  Penetapan: SK 24/A/SK-AMI/IX/2026 (menunggu)  ──►  [Pimpinan] tetapkan
   ↓
[SPMI]  Aktifkan siklus Ganjil 2026/2027, alokasi 3 auditor
   ↓
[Prodi/Unit]  Isi ED + bukti (Daftar Hadir, Notulen) ──submit──►  [SPMI] verifikasi
   ↓
[Auditor]  12 borang + 1 KTS Mayor + 2 OB + bukti ──finalisasi──►  [SPMI] approve LHA
   ↓
[SPMI]  Keputusan RTL (setujui/tolak/eskalasi)  ──►  [Pimpinan] catatan RTL
   ↓
[SPMI]  Generate Laporan: SK → Daftar Hadir → Bukti (urut) ──arsip PDF──►  [Pimpinan] tinjau
   ↓
[SPMI]  RTM: notulensi ──► [Pimpinan] sahkan risalah
   ↓
[SPMI]  Revisi standar S.01 ──► [Pimpinan] SK Perubahan ──► versi baru aktif
```

---

## 9. Kasus Tepi (Jika Terjadi)

| Situasi | Yang Terjadi di Sistem | Apa yang Perlu Dilakukan |
|---|---|---|
| ED prodi `draft` lupa di-submit | SPMI tidak bisa verifikasi | SPMI monitoring → remind kaprodi |
| Temuan KTS di-eskalasi | Sistem membuat entri RTM otomatis | SPMI lengkapi notulensi RTM |
| SK ditolak pimpinan | Status `ditolak` + alasan tercatat | SPMI perbaiki & kirim ulang |
| Lampiran gagal digabung saat Generate PDF | Flash "Lampiran dilewati" muncul per berkas | Periksa format (PDF/JPG/PNG, ≤20 MB) atau ganti berkas |
| Cover salah | Semua laporan terpengaruh | Admin unggah cover baru di Konfigurasi; SPMI generate ulang |
| Susunan lampiran diubah setelah generate | Status "Perlu Generate Ulang" | Klik **Generate PDF** sekali lagi |
| KTS Minor vs Mayor tertukar saat input | Kategori pada borang berbeda dari kesimpulan | Edit borang sebelum finalisasi |

---

## 10. Ringkasan Tanggung Jawab (RACI Singkat)

| Kegiatan | Admin | SPMI | Pimpinan | Auditor | Prodi/Unit |
|---|:-:|:-:|:-:|:-:|:-:|
| Konfigurasi cover/kode/edisi | ✅ | — | — | — | — |
| Penetapan SK & standar | — | ✅ | ✅ (setuju) | — | — |
| Aktifkan siklus & alokasi auditor | — | ✅ | — | — | — |
| Isi ED + bukti | — | pantau | — | lihat | ✅ |
| Penilaian borang & temuan | — | tinjau | — | ✅ | — |
| Approve LHA & keputusan RTL | — | ✅ | catat | — | eksekusi |
| Generate & arsip Laporan | konfigurasi | ✅ | tinjau | — | — |
| Pengesahan RTM | — | isi notulensi | ✅ | — | — |
| Revisi standar & SK Perubahan | — | ✅ | ✅ (setuju) | — | — |
