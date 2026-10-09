<?php

namespace Tests\Feature;

use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->syncRoles(['administrator']);
        return $user;
    }

    public function test_admin_can_view_document_categories_index(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/admin/document-categories')
            ->assertStatus(200)
            ->assertSee('Kategori Dokumen');
    }

    public function test_admin_can_create_parent_category(): void
    {
        $this->actingAs($this->adminUser())
            ->post('/admin/document-categories', [
                'name' => 'Kategori Uji',
                'code' => 'UJI-01',
                'module' => 'dokumen_mutu',
                'parent_id' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/document-categories?module=dokumen_mutu');

        $this->assertDatabaseHas('document_categories', [
            'code' => 'UJI-01',
            'name' => 'Kategori Uji',
            'parent_id' => null,
        ]);
    }

    public function test_admin_can_create_child_category_under_parent(): void
    {
        $parent = DocumentCategory::create([
            'name' => 'Parent Uji',
            'code' => 'PAR-01',
            'module' => 'dokumen_mutu',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser())
            ->post('/admin/document-categories', [
                'name' => 'Child Uji',
                'code' => 'PAR-01-A',
                'module' => 'dokumen_mutu',
                'parent_id' => $parent->id,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/document-categories?module=dokumen_mutu');

        $this->assertDatabaseHas('document_categories', [
            'code' => 'PAR-01-A',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_admin_can_update_category_code(): void
    {
        $category = DocumentCategory::create([
            'name' => 'Kategori Lama',
            'code' => 'LAMA-01',
            'module' => 'dokumen_mutu',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser())
            ->put("/admin/document-categories/{$category->id}", [
                'name' => 'Kategori Baru',
                'code' => 'BARU-01',
                'parent_id' => '',
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/document-categories?module=dokumen_mutu');

        $this->assertDatabaseHas('document_categories', [
            'id' => $category->id,
            'code' => 'BARU-01',
            'name' => 'Kategori Baru',
        ]);
    }

    public function test_admin_can_create_grandchild_category(): void
    {
        $root = DocumentCategory::create([
            'name' => 'Root Uji',
            'code' => 'ROOT-GC',
            'module' => 'dokumen_mutu',
            'is_active' => true,
        ]);

        $child = DocumentCategory::create([
            'name' => 'Child Uji',
            'code' => 'CHILD-GC',
            'module' => 'dokumen_mutu',
            'parent_id' => $root->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser())
            ->post('/admin/document-categories', [
                'name' => 'Grandchild Uji',
                'code' => 'GC-UJI',
                'module' => 'dokumen_mutu',
                'parent_id' => $child->id,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/document-categories?module=dokumen_mutu');

        $this->assertDatabaseHas('document_categories', [
            'code' => 'GC-UJI',
            'parent_id' => $child->id,
        ]);
    }

    public function test_update_rejects_parent_id_that_is_a_descendant(): void
    {
        $root = DocumentCategory::create([
            'name' => 'Root Cek',
            'code' => 'ROOT-CK',
            'module' => 'dokumen_mutu',
            'is_active' => true,
        ]);

        $child = DocumentCategory::create([
            'name' => 'Child Cek',
            'code' => 'CHILD-CK',
            'module' => 'dokumen_mutu',
            'parent_id' => $root->id,
            'is_active' => true,
        ]);

        $grandchild = DocumentCategory::create([
            'name' => 'Grandchild Cek',
            'code' => 'GC-CK',
            'module' => 'dokumen_mutu',
            'parent_id' => $child->id,
            'is_active' => true,
        ]);

        // Root tidak boleh dijadikan anak dari grandchild-nya sendiri (akan membuat loop)
        $this->actingAs($this->adminUser())
            ->put("/admin/document-categories/{$root->id}", [
                'name' => 'Root Cek',
                'code' => 'ROOT-CK',
                'parent_id' => $grandchild->id,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['parent_id']);

        // Anak tidak boleh dijadikan anak dari dirinya sendiri
        $this->actingAs($this->adminUser())
            ->put("/admin/document-categories/{$child->id}", [
                'name' => 'Child Cek',
                'code' => 'CHILD-CK',
                'parent_id' => $child->id,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['parent_id']);
    }

    public function test_uploaded_document_code_uses_full_path_for_deep_category(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $admin = $this->adminUser();

        \App\Models\Setting::set('document_code_prefix', 'STMIK-MI/SPMI', 'dokumen');
        \App\Models\Setting::set('document_code_format', '{prefix}/{path}/{seq}', 'dokumen');
        \App\Models\Setting::set('document_auto_generate', '1', 'dokumen');

        // Buat struktur 3 level: KEBIJAKAN > K-SKP > Q-DETAIL
        $root = DocumentCategory::create([
            'name' => 'Kebijakan SPMI', 'code' => 'KEBIJAKAN', 'module' => 'dokumen_mutu', 'is_active' => true,
        ]);
        $child = DocumentCategory::create([
            'name' => 'SK Penetapan Standar', 'code' => 'K-SKP', 'module' => 'dokumen_mutu', 'parent_id' => $root->id, 'is_active' => true,
        ]);
        $grandchild = DocumentCategory::create([
            'name' => 'Detail Sub Kebijakan', 'code' => 'Q-DETAIL', 'module' => 'dokumen_mutu', 'parent_id' => $child->id, 'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/documents', [
            'title' => 'Dokumen Kebijakan Detail',
            'module' => 'dokumen_mutu',
            'document_category_id' => $grandchild->id,
            'file' => \Illuminate\Http\UploadedFile::fake()->create('deep.pdf', 100, 'application/pdf'),
            'version' => 0,
        ]);

        $response->assertRedirect('/admin/documents?module=dokumen_mutu');

        $this->assertDatabaseHas('documents', [
            'title' => 'Dokumen Kebijakan Detail',
            'code' => 'STMIK-MI/SPMI/KEBIJAKAN/K-SKP/Q-DETAIL/001',
        ]);
    }

    public function test_document_create_and_edit_forms_render_for_deep_category_module(): void
    {
        $admin = $this->adminUser();

        DocumentCategory::create([
            'name' => 'Kebijakan SPMI', 'code' => 'KEBIJAKAN', 'module' => 'dokumen_mutu', 'is_active' => true,
        ]);

        // Form create dokumen SPMI harus render tanpa error
        $this->actingAs($admin)
            ->get('/admin/documents/create?module=dokumen_mutu')
            ->assertOk()
            ->assertSee('Kategori Dokumen')
            ->assertSee('Kebijakan SPMI');

        // Form edit dokumen SPMI juga harus render (dropdown kategori ber-indentasi)
        $this->actingAs($admin)
            ->get('/admin/documents?module=dokumen_mutu')
            ->assertOk()
            ->assertSee('Semua Kategori');
    }

    public function test_duplicate_category_code_is_rejected(): void
    {
        DocumentCategory::create([
            'name' => 'Kategori Satu',
            'code' => 'DUP-01',
            'module' => 'dokumen_mutu',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser())
            ->post('/admin/document-categories', [
                'name' => 'Kategori Dua',
                'code' => 'DUP-01',
                'module' => 'dokumen_mutu',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['code']);
    }

    public function test_category_with_documents_cannot_be_deleted(): void
    {
        $category = DocumentCategory::create([
            'name' => 'Kategori Terpakai',
            'code' => 'PAKAI-01',
            'module' => 'dokumen_mutu',
            'is_active' => true,
        ]);

        \App\Models\Document::create([
            'code' => 'DOC-001',
            'title' => 'Dokumen Uji',
            'document_type' => 'Lainnya',
            'module' => 'dokumen_mutu',
            'document_category_id' => $category->id,
            'file_path' => 'documents/dummy.pdf',
            'file_name' => 'dummy.pdf',
            'file_size' => 100,
            'is_public' => false,
            'uploaded_by' => $this->adminUser()->id,
            'version' => 0,
            'status' => 'draft',
        ]);

        $this->actingAs($this->adminUser())
            ->delete("/admin/document-categories/{$category->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('document_categories', ['id' => $category->id]);
    }

    public function test_admin_can_update_document_code_format_setting(): void
    {
        $this->actingAs($this->adminUser())
            ->put('/admin/settings', [
                'institution_name' => 'STMIK Mardira Indonesia',
                'document_code_prefix' => 'STMIK-MI.SPMI',
                'document_code_format' => '{prefix}.{parent_code}.{child_code}.{seq}',
                'document_auto_generate' => '1',
            ])
            ->assertRedirect('/admin/settings');

        $this->assertDatabaseHas('settings', [
            'key' => 'document_code_format',
            'value' => '{prefix}.{parent_code}.{child_code}.{seq}',
        ]);
    }

    public function test_invalid_document_code_format_without_seq_is_rejected(): void
    {
        $this->actingAs($this->adminUser())
            ->put('/admin/settings', [
                'institution_name' => 'STMIK Mardira Indonesia',
                'document_code_format' => '{prefix}.{parent_code}.{child_code}',
            ])
            ->assertSessionHasErrors(['document_code_format']);
    }

    public function test_uploaded_document_code_follows_custom_dot_format(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        // Seeder kini hanya berisi header; buat struktur parent-child untuk test ini.
        $this->seed(\Database\Seeders\DocumentCategorySeeder::class);

        $admin = $this->adminUser();

        $kebijakan = DocumentCategory::where('code', 'KEBIJAKAN')->firstOrFail();
        $child = DocumentCategory::create([
            'name' => 'SK Penetapan Standar',
            'code' => 'K-SKP',
            'module' => 'dokumen_mutu',
            'target_roles' => 'spmi',
            'parent_id' => $kebijakan->id,
        ]);

        // Ubah format kode dokumen dari slash ke dot, tanpa bulan/tahun.
        \App\Models\Setting::set('document_code_prefix', 'STMIK-MI.SPMI', 'dokumen');
        \App\Models\Setting::set('document_code_format', '{prefix}.{parent_code}.{child_code}.{seq}', 'dokumen');
        \App\Models\Setting::set('document_auto_generate', '1', 'dokumen');

        $response = $this->actingAs($admin)->post('/admin/documents', [
            'title' => 'SK Penetapan Standar Uji',
            'module' => 'dokumen_mutu',
            'document_category_id' => $child->id,
            'file' => \Illuminate\Http\UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            'version' => 0,
        ]);

        $response->assertRedirect('/admin/documents?module=dokumen_mutu');

        $this->assertDatabaseHas('documents', [
            'title' => 'SK Penetapan Standar Uji',
            'code' => 'STMIK-MI.SPMI.KEBIJAKAN.K-SKP.001',
        ]);
    }

    public function test_uploaded_document_code_follows_format_with_month_year(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        // Seeder kini hanya berisi header; buat struktur parent-child untuk test ini.
        $this->seed(\Database\Seeders\DocumentCategorySeeder::class);

        $admin = $this->adminUser();

        $standar = DocumentCategory::where('code', 'STANDAR')->firstOrFail();
        $child = DocumentCategory::create([
            'name' => 'Standar Pembelajaran',
            'code' => 'S-BELAJAR',
            'module' => 'dokumen_mutu',
            'target_roles' => 'spmi',
            'parent_id' => $standar->id,
        ]);

        \App\Models\Setting::set('document_code_prefix', 'STMIK-MI/SPMI', 'dokumen');
        \App\Models\Setting::set('document_code_format', '{prefix}/{parent_code}.{child_code}.{seq}/{MM}.{YYYY}', 'dokumen');
        \App\Models\Setting::set('document_auto_generate', '1', 'dokumen');

        $response = $this->actingAs($admin)->post('/admin/documents', [
            'title' => 'Standar Pembelajaran Uji',
            'module' => 'dokumen_mutu',
            'document_category_id' => $child->id,
            'file' => \Illuminate\Http\UploadedFile::fake()->create('dokumen2.pdf', 100, 'application/pdf'),
            'version' => 0,
        ]);

        $response->assertRedirect('/admin/documents?module=dokumen_mutu');

        $expectedCode = 'STMIK-MI/SPMI/STANDAR.S-BELAJAR.001/' . date('m') . '.' . date('Y');

        $this->assertDatabaseHas('documents', [
            'title' => 'Standar Pembelajaran Uji',
            'code' => $expectedCode,
        ]);
    }
}
