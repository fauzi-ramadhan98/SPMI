# Alur Penggunaan Aplikasi SPMI — Semua Peran (Dari Awal sampai Akhir)

Dokumen ini memetakan **alur kerja lengkap** aplikasi Penjaminan Mutu Internal (SPMI) untuk setiap peran, dari persiapan administrator sampai siklus AMI, audit, tindak lanjut, laporan, hingga peningkatan standar — sesuai fitur yang benar-benar ada di dalam aplikasi.

> Struktur peran mengikuti **Permendiktisaintek No. 39 Tahun 2025** (lihat `RoleSeeder`).

> Untuk versi dengan **studi kasus & contoh langkah konkret** per peran, lihat [`ALUR-PER-ROLE-STUDI-KASUS.md`](ALUR-PER-ROLE-STUDI-KASUS.md).

---

## 0. Peta Peran & Hak Akses

| Peran | Nama Singkatan | Deskripsi Singkat |
|---|---|---|
| `administrator` | Pengelola Sistem | Manajemen user & role, master data (prodi/unit/tahun akademik), Konfigurasi Aplikasi (logo, nama institusi, cover & kode laporan), Halaman Publik |
| `spmi` | LPM/SPMI | Pengelola penuh seluruh proses mutu: standar, siklus AMI, alokasi auditor, verifikasi ED, laporan, RTM, revisi standar |
| `pimpinan` | Ketua & Wakil | Persetujuan SK Penetapan/Perubahan, persetujuan RTL, pengesahan risalah RTM |
| `auditor` | Tim Auditor | Penilaian borang/instrumen, pembuatan temuan (KTS/OB) + bukti, finalisasi audit |
| `prodi` | Program Studi (Kaprodi) | Isi Evaluasi Diri, upload bukti kegiatan, tindak lanjut temuan, SOP unit |
| `unit` | Unit Kerja | Sama dengan prodi, fokus unit administratif/akademik/penunjang |
| `guest` | Pengunjung | Read-only dashboard & dokumen publik |

**Aturan sidebar (user multi-role):** sistem menampilkan **satu profil menu sesuai prioritas**:
`administrator` > `pimpinan` > `spmi` > `auditor` > `auditee (prodi/unit)` > `guest`.
Akses URL tetap mengikuti hak akses masing-masing peran.

---

## 1. Alur Besar: Satu Siklus AMI (Lintas Peran)

```
[0] Persiapan            → Administrator: master data, user, konfigurasi
        ↓
[1] Penetapan (P1)       → SPMI: dokumen & standar mutu, SK Penetapan → Pimpinan: tetapkan
        ↓
[2] Pelaksanaan Awal     → SPMI: aktifkan siklus AMI, alokasi auditor + surat tugas
        ↓
[3] Evaluasi Diri (ED)   → Prodi/Unit: isi ED + bukti → submit → SPMI: verifikasi
        ↓
[4] Audit (P3)           → Auditor: nilai borang, buat temuan KTS/OB + bukti, finalisasi
        ↓                         → SPMI: review detail, approve LHA
        ↓
[5] Tindak Lanjut (RTL)  → SPMI: putuskan tiap temuan (setujui/tolak/eskalasi)
        ↓                         → Auditee & Pimpinan: eksekusi & catat
        ↓
[6] Laporan              → SPMI: susun Laporan AMI (ringkasan + lampiran terurut) → arsip PDF
        ↓
[7] RTM                  → SPMI: jadwal, peserta, notulensi → Pimpinan: sahkan risalah
        ↓
[8] Peningkatan Standar   → SPMI: tinjauan berkala, revisi standar → SK Perubahan → tetapkan
        ↓
[9] Pemantauan           → SPMI: pantau kinerja auditor, risk register, log aktivitas → Siklus berikutnya
```

---

## 2. Alur Detail per Peran

### 2.1 Administrator (Pengelola Sistem) — dari awal sampai akhir

1. **Login** → Dashboard.
2. **Master Data** (menu *Pengaturan*):
   - Program Studi (kode, nama, jenjang, akreditasi, kaprodi)
   - Unit Kerja (nama, kepala unit)
   - Tahun Akademik (periode & semester)
3. **Manajemen User**: buat akun & assign peran; untuk akun prodi/unit, tautkan ke prodi/unit agar otomatis ter-scope ke datanya.
4. **Konfigurasi Aplikasi**:
   - Identitas institusi (nama, logo — dipakai di kop/header PDF)
   - **Pengaturan Laporan AMI**: upload **cover custom** (opsional), **Kode** & **Edisi** dokumen → dipakai di halaman pertama & kop PDF
5. **Kategori Dokumen**: pastikan kategori tersedia (mis. *Daftar Hadir RTM / Dosen / Pelatihan Tendik*).
6. **Halaman Publik**: kelola konten beranda, profil, dan dokumen yang dipublikasikan.
7. **Pemantauan** (read-only): Monitoring ED, Dokumen Mutu, Audit Trail.
8. **Tutup**: logout.

> Catatan: pengaturan cover, kode, dan edisi hanya dapat diubah oleh Administrator; SPMI cukup memakai hasilnya saat membuat laporan.

### 2.2 SPMI/LPM — Pengelola Proses (awal sampai akhir)

**A. Persiapan & Penetapan (P1)**
1. **Dokumen Mutu** → unggah Kebijakan & Manual Mutu (dengan kategori, versi, tanggal).
2. **SK Penetapan Standar** → buat SK (nomor, judul, standar, tanggal) → status `draft` → submit `menunggu_persetujuan` → **Pimpinan** menetapkan (`ditetapkan`) atau menolak (`ditolak` + alasan). Setelah ditetapkan, unggah **file SK** (PDF/scan) sebagai lampiran.
3. **Standar Mutu & Indikator** → buat standar (kode, pernyataan) & indikator/borang; checklist = **Daftar Tilik** yang dipakai ED & audit.
4. **Validasi SOP Unit** → tinjau & validasi SOP yang diajukan unit.

**B. Pelaksanaan Awal**
5. **Profil Risiko Institusi** → Risk Register per semester (skor dampak × likelihood).
6. **Siklus AMI** → buat siklus (tahun akademik, semester, periode) → `draft` → `aktif`.
7. **Alokasi Auditor & Surat Tugas** → tunjuk auditor untuk tiap prodi/unit yang akan diaudit, lalu cetak **SK Penugasan Auditor**.
8. **Manajemen Survei** (bila ada survei kepuasan).

**C. Monitoring Evaluasi Diri (P2)**
9. **Monitoring ED** → pantau progres tiap auditee; saat ED berstatus `submitted`, **verifikasi** (isi kesimpulan) → `verified`.

**D. Pengendalian Audit (P3)**
10. **Pantau Kinerja Auditor** (Kertas Kerja) → pantau progres, kirim reminder bila auditor terlambat.
11. **Review Detail LHA** per auditee (di halaman Laporan AMI) → **Approve LHA** (status penugasan `selesai`) atau **Reminder**.

**E. Tindak Lanjut (P4)**
12. **RTL** → untuk tiap temuan: putuskan **Setujui** (LHA disahkan) / **Tolak** (revisi) / **Eskalasi** (dibuatkan entri RTM). Isi catatan keputusan.
13. **RTM** → buat jadwal rapat (agenda dari hasil AMI), **undang peserta**, isi **notulensi** (`dijadwalkan`→`berlangsung`→`notulensi`), rumuskan **instruksi tindak lanjut** per auditee, ajukan ke **`Pimpinan`** untuk pengesahan → **`disahkan`** (risalah PDF terkunci & siap diunduh).

**F. Laporan AMI**
14. **Laporan AMI** → pilih siklus & jenis (Klasik / Berbasis Risiko) → **Lihat Ringkasan**.
15. **Generate Laporan** → masuk **Daftar Generate Laporan** → buka **Detail**:
    - Lampiran **terisi otomatis** dari SK, dokumen, bukti ED & temuan → **geser urutan ↑/↓**, hapus, atau tambahkan (**upload** PDF/gambar / pilih dari data existing)
    - Klik **Generate PDF** → badan laporan + lampiran digabung sesuai urutan → **arsip** tersimpan & bisa diunduh kapan saja.
16. **Audit Trail** → pantau jejak perubahan sistem.

**G. Peningkatan Standar (P5)**
17. **Peninjauan & Revisi Standar** → tinjau versi standar, ubah isi standar dengan status *Revisi Draft*.
18. **SK Perubahan Standar** → buat SK Perubahan dari revisi draft → `menunggu_persetujuan` → **Pimpinan** menetapkan → standar naik versi.

### 2.3 Pimpinan (Ketua & Wakil) — Persetujuan & Pengesahan

1. **Dashboard** → pantau capaian & temuan terbuka.
2. **P1 — Penetapan**: **SK Penetapan Standar** → setujui (`ditetapkan`) / tolak (`ditolak` + alasan) SK yang menunggu.
3. **P1**: **SK Perubahan Standar** → setujui/tolak perubahan standar.
4. **P3**: **SK Penugasan Auditor** → tinjau & tetapkan SK penugasan sebelum audit berjalan.
5. **P4 — Rapat Tindak Lanjut (RTL)**: tinjau temuan KTS, isi **catatan/instruksi** pimpinan per temuan.
6. **P4 — Rapat Tinjauan Manajemen (RTM)**: tinjau notulensi → **sahkan** → risalah terkunci & downloadable.
7. **P4**: **Laporan AMI & RTM** → tinjau laporan yang telah dihasilkan SPMI.

### 2.4 Auditor — Penilaian Field (awal → selesai)

1. **Jadwal Penugasan Saya** → cek penugasan & SK Penugasan.
2. **Lihat Evaluasi Diri (ED)** auditee + **Risk Register** (read-only) sebagai konteks.
3. **Kertas Kerja Auditor** → isi borang/instrumen per indikator:
   - skor, deskripsi, dan **kategori temuan**: *KTS Mayor / KTS Minor / Sesuai / Melampaui Standar Nasional*
4. **Buat Temuan** (KTS atau OB) → lengkapi kriteria, analisis, **unggah bukti** (foto, notulen, dokumen).
5. **Finalisasi** penugasan saat seluruh borang & temuan lengkap → assignment `finalisasi` untuk disetujui SPMI.
6. **Panduan Etik Auditor** → rujukan Independen.

### 2.5 Program Studi (Kaprodi) / Unit Kerja — Auditee (awal sampai selesai)

1. **Dashboard** → cek status siklus & jadwal audit (**Info Jadwal Audit**).
2. **Isi Evaluasi Diri (ED)**:
   - Buat ED (`draft`) → generate butir dari **Daftar Tilik**
   - Isi skor, deskripsi, bukti per butir — **kategori bukti**: *Notulen / SK / Daftar Hadir / Dokumentasi-Foto* (lampiran otomatis ikut ke lampiran Laporan AMI)
   - **Submit** → `submitted` (menunggu verifikasi SPMI)
3. **Tindak Lanjut Temuan** (saat ada KTS dari auditor): isi keputusan, target waktu, dan ukuran perbaikan.
4. **Manajemen SOP Unit** (khusus unit): susun SOP, ajukan validasi ke SPMI.
5. **Risk Register**: perbarui profil risiko unit/prodi per semester.
6. **Risalah RTM (Disahkan)**: baca instruksi tindak lanjut dari rapat yang telah disahkan pimpinan.

### 2.6 Pengunjung (Guest) — Read-only

1. **Beranda** → informasi institusi, program studi, dan dokumen yang dipublikasikan.
2. **Dokumen Publik** → unduh dokumen mutu yang berstatus publik.
3. Tidak dapat mengubah data apa pun.

---

## 3. Peta Status (Rujukan Cepat)

| Objek | Alur Status |
|---|---|
| **Siklus AMI** | `draft` → `aktif` → `selesai` |
| **Penugasan Audit** | `pending` → `berlangsung` → `finalisasi` → `selesai` |
| **Evaluasi Diri** | `draft` → `submitted` → `verified` (+ kesimpulan SPMI) |
| **Temuan Audit** | `open` → `in_progress` → `closed` → `verified` (OB/KTS) |
| **SK Penetapan / Perubahan** | `draft` → `menunggu_persetujuan` → `ditetapkan` / `ditolak` |
| **Standar Mutu** | `aktif` ↔ `draft_revisi` (menunggu SK Perubahan) |
| **Rapat RTM** | `dijadwalkan` → `berlangsung` → `notulensi` (menunggu pengesahan) → `disahkan` |
| **Laporan Generate** | `draft` → `generated` (perubahan susunan → "Perlu Generate Ulang") |

---

## 4. Alur Generate Laporan AMI (Rinci)

```
Halaman Laporan AMI
  └─ pilih siklus & jenis → [Generate Laporan]
        ↓
Daftar Generate Laporan (semua laporan yang pernah dibuat)
  └─ buka Detail
        ├─ Lampiran auto-isi: SK, dokumen, bukti ED, temuan (berkas saja)
        ├─ Tambah: upload PDF/JPG/PNG (mis. Daftar Hadir) atau pilih data existing
        ├─ Urutkan: ↑ / ↓ — urutan ini = urutan halaman lampiran di PDF
        ├─ Hapus lampiran (file asli tidak pernah tersentuh — berkas disalin)
        └─ [Generate PDF]
              ↓
        Arsip PDF tersimpan → [Download PDF] kapan saja
```

**Aturan teknis:** PDF digabung utuh (teks tetap bisa dicari), gambar (scan) menjadi halaman penuh. Maks upload 20 MB. Arsip disimpan di `storage/app/private/generated_reports/` (bukan publik).

---

## 5. Catatan & Batasan Saat Ini

- **Poin 5 catatan client** (tombol "simpan" di setiap baris) — *ditunggu klarifikasi client*.
- **5 pengujian otomatis `RiskRegisterTest`** masih gagal (di luar cakupan perbaikan sebelumnya) — tidak memengaruhi alur di atas.
- **Cover & kode/edisi laporan** hanya dapat diatur oleh Administrator (Konfigurasi Aplikasi).
- Lampiran laporan berbentuk **.docx** tidak dapat digabung ke PDF (hanya PDF & gambar); file tetap tercatat di tabel lampiran.
- Dokumen hasil generate bersifat **privat** (tidak tampil di halaman publik).
