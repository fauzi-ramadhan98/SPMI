<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentCategory;

/**
 * Service untuk generate kode dokumen otomatis dengan template yang bisa dikonfigurasi.
 * Support unlimited depth category hierarchy.
 * 
 * Template format kode mendukung placeholder:
 * - {prefix}: prefix institusi (misal: STMIK-MI/SPMI)
 * - {parent_code}: kode kategori root/induk tertinggi (misal: KEBIJAKAN)
 * - {child_code}: kode kategori paling dalam (misal: Q-DETAIL, atau K-SKP jika depth 2)
 * - {path}: full path dari root sampai kategori saat ini, dipisah "/" (misal: KEBIJAKAN/K-SKP/Q-DETAIL)
 * - {seq}: nomor urut dokumen di kategori ini (misal: 001, 002, ...)
 * - {MM}: bulan (misal: 01, 02, ..., 12)
 * - {YYYY}: tahun (misal: 2025)
 * - {YY}: tahun 2 digit (misal: 25)
 * 
 * Contoh template:
 * - "{prefix}/{parent_code}.{child_code}.{seq}" 
 *   → depth 2: "STMIK-MI/SPMI/KEBIJAKAN.K-SKP.001"
 *   → depth 3: "STMIK-MI/SPMI/KEBIJAKAN.Q-DETAIL.001"
 * 
 * - "{prefix}/{path}/{seq}" 
 *   → depth 2: "STMIK-MI/SPMI/KEBIJAKAN/K-SKP/001"
 *   → depth 3: "STMIK-MI/SPMI/KEBIJAKAN/K-SKP/Q-DETAIL/001"
 * 
 * - "{prefix}.{parent_code}.{child_code}.{seq}.{MM}{YYYY}"
 *   → "STMIK-MI.SPMI.KEBIJAKAN.K-SKP.001.012025"
 */
class DocumentCodeGenerator
{
    /**
     * Generate kode dokumen berdasarkan template, kategori, dan sequence.
     * Support unlimited depth — akan build full path dari root.
     * 
     * @param DocumentCategory $category Kategori dokumen (bisa di level apapun)
     * @param ?string $templateFormat Template format kode (misal dari setting)
     * @return string Kode dokumen yang sudah jadi
     */
    public static function generate(DocumentCategory $category, ?string $templateFormat = null): string
    {
        // Ambil dari setting jika tidak disediakan
        $templateFormat ??= setting('document_code_format', '{prefix}/{parent_code}.{child_code}.{seq}');
        
        $prefix = setting('document_code_prefix', 'STMIK-MI/SPMI');
        
        // Build full path dari root sampai kategori saat ini
        $path = static::buildCategoryPath($category);
        
        // Untuk backward compatibility: gunakan parent code (root) dan child code (direct parent atau kategori sendiri jika root)
        $parentCode = $path[0] ?? $category->code;
        $childCode = count($path) > 1 ? end($path) : $category->code;
        
        // Hitung sequence number untuk kategori ini
        $seq = static::getNextSequence($category);
        
        // Parse placeholder
        $code = $templateFormat;
        $code = str_replace('{prefix}', $prefix, $code);
        $code = str_replace('{parent_code}', $parentCode, $code);
        $code = str_replace('{child_code}', $childCode, $code);
        $code = str_replace('{seq}', str_pad($seq, 3, '0', STR_PAD_LEFT), $code);
        
        // Support new placeholder {path} untuk full path (misal KEBIJAKAN/K-SKP/Q-DETAIL)
        $fullPath = implode('/', $path);
        $code = str_replace('{path}', $fullPath, $code);
        
        // Placeholder tanggal
        $code = str_replace('{MM}', date('m'), $code);
        $code = str_replace('{YYYY}', date('Y'), $code);
        $code = str_replace('{YY}', date('y'), $code);
        
        return $code;
    }
    
    /**
     * Build array path dari root sampai kategori saat ini.
     * Misal KEBIJAKAN > K-SKP > Q-DETAIL → ['KEBIJAKAN', 'K-SKP', 'Q-DETAIL']
     * 
     * @param DocumentCategory $category
     * @return array Array kode kategori dari root sampai kategori saat ini
     */
    public static function buildCategoryPath(DocumentCategory $category): array
    {
        $path = [$category->code];
        
        // Traverse up sampai root
        $current = $category;
        while ($current->parent_id) {
            $current = $current->parent;
            array_unshift($path, $current->code);
        }
        
        return $path;
    }
    
    /**
     * Hitung nomor urut berikutnya untuk kategori tertentu.
     * Jika parent_id ada, hitung berdasarkan parent; jika tidak, hitung berdasarkan kategori sendiri.
     * 
     * @param DocumentCategory $category
     * @return int Nomor urut berikutnya (1-based)
     */
    public static function getNextSequence(DocumentCategory $category): int
    {
        // Jika kategori ini adalah child, hitung dari parent-nya
        $countCategory = $category->parent_id ? $category->parent_id : $category->id;
        
        $count = Document::where('document_category_id', $category->id)
            ->count();
        
        return $count + 1;
    }
    
    /**
     * Validasi template format kode — pastikan minimal ada {seq}.
     * 
     * @param string $template
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateTemplate(string $template): array
    {
        if (empty(trim($template))) {
            return ['valid' => false, 'message' => 'Template tidak boleh kosong.'];
        }
        
        if (!str_contains($template, '{seq}')) {
            return ['valid' => false, 'message' => 'Template harus mengandung placeholder {seq} untuk nomor urut.'];
        }
        
        return ['valid' => true, 'message' => 'Template valid.'];
    }
}
