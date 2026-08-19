<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pages')->where('slug', 'sambutan-kepala')->update([
            'title'            => 'Sambutan Kepala SPMI STMIK Mardira Indonesia',
            'meta_description' => 'Sambutan Kepala SPMI STMIK Mardira Indonesia - Heri Wahyudi',
            'content'          => '<h2>Sambutan Kepala SPMI STMIK Mardira Indonesia</h2>
<p>Selamat datang di laman resmi Satuan Penjaminan Mutu Internal (SPMI) STMIK Mardira Indonesia.</p>
<p>Sebagai bagian integral dari dunia pendidikan tinggi, STMIK Mardira Indonesia (STMIK MI) memegang komitmen teguh untuk terus meningkatkan mutu akademik dan tata kelola institusi secara berkelanjutan. Dalam hal ini, SPMI hadir sebagai motor penggerak utama untuk mewujudkan komitmen tersebut melalui pembentukan budaya mutu yang mengakar kuat, sejalan dengan nilai-nilai luhur keilmuan, inovasi, dan kebermanfaatan bagi masyarakat luas.</p>
<p>SPMI mengemban peran strategis dalam memastikan implementasi Tri Dharma Perguruan Tinggi di lingkungan STMIK MI—mulai dari kurikulum, proses pembelajaran, penelitian, hingga pengabdian kepada masyarakat. Kami hadir untuk menjamin bahwa seluruh aspek tersebut senantiasa memenuhi Standar Nasional Pendidikan Tinggi yang ditetapkan pemerintah maupun standar mutu internal institusi. Bagi kami, mutu bukanlah sebuah garis akhir pencapaian, melainkan proses perbaikan dan evaluasi yang tidak pernah berhenti.</p>
<p>Mari bersinergi mewujudkan STMIK Mardira Indonesia sebagai pusat keunggulan pendidikan teknologi dan informasi yang mampu mencetak lulusan berdaya saing unggul dan berakhlak mulia. Kami mengundang seluruh sivitas akademika dan pemangku kepentingan untuk berkolaborasi aktif dalam setiap inisiatif peningkatan mutu kampus kita.</p>
<p>Terima kasih atas perhatian, dedikasi, dan dukungan Anda.</p>
<p><em>Wassalamualaikum warahmatullahi wabarakatuh.</em></p>
<p><strong>Heri Wahyudi</strong><br>Kepala Satuan Penjaminan Mutu Internal<br>STMIK Mardira Indonesia Bandung</p>
<hr>
<h3>Kontak Resmi SPMI STMIK Mardira Indonesia Bandung</h3>
<ul>
    <li><strong>WhatsApp:</strong> +62 896-3001-6469</li>
    <li><strong>Email:</strong> <a href="mailto:spmi@stmik-mi.ac.id">spmi@stmik-mi.ac.id</a></li>
</ul>',
            'updated_at' => now(),
        ]);
    }

    public function down(): void {}
};
