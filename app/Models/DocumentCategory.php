<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'module',
        'parent_id',
        'target_roles',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Kedalaman kategori pada hasil flatTreeForModule(). Properti PHP biasa
     * (bukan atribut Eloquent) supaya tidak ikut tersimpan ke database.
     */
    public int $treeDepth = 0;

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_category_id');
    }

    public function parent()
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->orderBy('name');
    }

    /**
     * Ambil semua ID descendant (anak, cucu, dst) dari kategori ini.
     * Dipakai untuk mencegah circular reference saat memilih parent_id
     * (kategori tidak boleh menjadi anak dari descendant-nya sendiri).
     *
     * @return array<int>
     */
    public function descendantIds(): array
    {
        $ids = [];
        $queue = [$this->id];

        while (!empty($queue)) {
            $parentId = array_shift($queue);
            $childIds = static::where('parent_id', $parentId)->pluck('id')->all();
            foreach ($childIds as $childId) {
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * Ambil semua ancestor (parent, grandparent, dst) dari root sampai kategori ini,
     * tidak termasuk kategori ini sendiri. Terurut dari root -> langsung di atas.
     *
     * @return \Illuminate\Support\Collection<int, DocumentCategory>
     */
    public function ancestors()
    {
        $result = collect();
        $current = $this;

        while ($current->parent_id) {
            $current = $current->parent;
            if (!$current) {
                break;
            }
            $result->prepend($current);
        }

        return $result;
    }

    /**
     * Level kedalaman kategori ini (0 = root/tanpa induk, 1 = anak, 2 = cucu, dst).
     */
    public function depth(): int
    {
        return $this->ancestors()->count();
    }

    /**
     * Muat relasi "children" secara rekursif (anak, cucu, dst) ke kategori ini.
     * Dipakai untuk root kategori yang sudah di-paginate, agar tetap bisa
     * menampilkan sub-kategori level berapa pun tanpa mengubah logika pagination.
     */
    public function loadChildrenRecursively(): void
    {
        $children = $this->children()->get();
        foreach ($children as $child) {
            $child->loadChildrenRecursively();
        }
        $this->setRelation('children', $children);
    }

    /**
     * Bangun struktur tree (nested) untuk satu modul, dengan relasi "children"
     * di-set secara rekursif sehingga mendukung kedalaman sub-kategori tak terbatas.
     * Gunakan $category->children untuk traversal di Blade.
     *
     * @param string $module
     * @return \Illuminate\Support\Collection<int, DocumentCategory> Root nodes
     */
    public static function buildTree(string $module)
    {
        $all = static::where('module', $module)->orderBy('name')->get()->groupBy('parent_id');

        $build = function ($parentId) use (&$build, $all) {
            return ($all->get($parentId) ?? collect())->map(function ($category) use (&$build) {
                $category->setRelation('children', $build($category->id));
                return $category;
            })->values();
        };

        return $build(null);
    }

    /**
     * Ambil daftar kategori satu modul dalam bentuk flat (datar), terurut secara
     * hierarkis (parent selalu sebelum children-nya), dengan atribut "depth" terpasang
     * untuk kebutuhan indentasi pada dropdown <select>.
     *
     * @param string $module
     * @param array<int> $excludeIds ID kategori yang tidak boleh muncul (misal diri sendiri + descendant saat edit)
     * @return \Illuminate\Support\Collection<int, DocumentCategory>
     */
    public static function flatTreeForModule(string $module, array $excludeIds = [], bool $onlyActive = false)
    {
        $query = static::where('module', $module);

        // Filter aktif di level query, bukan setelah walk, supaya anak dari induk non-aktif
        // ikut hilang (tidak muncul sebagai yatim di dropdown).
        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $all = $query->orderBy('name')->get()->groupBy('parent_id');
        $result = collect();

        $walk = function ($parentId, int $depth) use (&$walk, &$result, $all, $excludeIds) {
            foreach ($all->get($parentId) ?? collect() as $category) {
                if (in_array($category->id, $excludeIds, true)) {
                    continue;
                }
                $category->treeDepth = $depth;
                $result->push($category);
                $walk($category->id, $depth + 1);
            }
        };

        $walk(null, 0);

        return $result;
    }
}
