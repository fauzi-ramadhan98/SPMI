<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\User;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : null;

        Page::updateOrCreate(['slug' => 'sambutan-kepala'], [
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
            'author_id'        => $adminId,
            'is_published'     => true,
        ]);

        Page::updateOrCreate(['slug' => 'profil-lpm'], [
            'title' => 'Profil Lembaga Penjaminan Mutu',
            'content' => '<h2>Visi</h2><p>Menjadi lembaga penjaminan mutu yang unggul dan terpercaya dalam mengawal kualitas pendidikan.</p><h2>Misi</h2><ul><li>Membangun budaya mutu di lingkungan sivitas akademika.</li><li>Melaksanakan Sistem Penjaminan Mutu Internal (SPMI) secara berkelanjutan.</li><li>Mendampingi program studi dalam proses akreditasi.</li></ul>',
            'meta_description' => 'Profil, Visi, dan Misi Lembaga Penjaminan Mutu',
            'author_id' => $adminId,
            'is_published' => true,
        ]);
    }
}
