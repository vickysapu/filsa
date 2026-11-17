<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CategoryMappingController extends Controller
{
    // Helper untuk menjaga konsistensi daftar kategori valid
    private function getValidCategories()
    {
        return [
            'PP' => ['name' => 'PERSEPULUHAN (PP)', 'group' => 'income'],
            'IURAN_PEJABAT' => ['name' => 'IURAN', 'group' => 'income'],
            'DANA_MISI' => ['name' => 'DANA MISI', 'group' => 'income'],
            'BUNGA_BANK' => ['name' => 'BUNGA', 'group' => 'income'],
            'PENERIMAAN_LAIN' => ['name' => 'LAIN-LAIN (DEBIT)', 'group' => 'income'],

            'BPJS' => ['name' => 'BPJS', 'group' => 'expense'],
            'IURAN_MDF' => ['name' => 'IURAN MDF', 'group' => 'expense'],
            'TAHAKA' => ['name' => 'TAHAKA', 'group' => 'expense'],
            'PAJAK_BUNGA' => ['name' => 'PAJAK BUNGA', 'group' => 'expense'],
            'ADMIN_BANK' => ['name' => 'ADMIN BANK', 'group' => 'expense'],
            'PENGELUARAN_LAIN' => ['name' => 'LAIN-LAIN (KREDIT)', 'group' => 'expense'],
        ];
    }

    public function index()
    {
        $validCategories = $this->getValidCategories();
        $validCodes = array_keys($validCategories);

        // Ambil semua kategori unik dari kedua tabel, lalu filter yang belum valid
        $allCategories = DB::table('financial_report_details')
            ->select('category_code', 'category_name')
            ->distinct()
            ->get()
            ->merge(DB::table('financial_transactions')->select('category_code', 'category_name')->distinct()->get())
            ->unique(fn($item) => $item->category_code . '|' . $item->category_name)
            ->filter(fn($item) => !in_array($item->category_code, $validCodes))
            ->groupBy('category_code');

        $oldCategories = collect();

        foreach ($allCategories as $code => $items) {
            // Ekstrak "Base Name" (nama tanpa suffix gereja/cabang)
            // Cari yang tidak memiliki ' - ', jika semua punya, ambil sebelum ' - ' pertama
            $noDash = $items->first(fn($i) => !str_contains($i->category_name, ' - '));
            $baseName = $noDash ? $noDash->category_name : explode(' - ', $items->first()->category_name, 2)[0];

            $oldCategories->push(
                (object) [
                    'category_code' => $code,
                    'base_name' => $baseName,
                ],
            );
        }

        $oldCategories = $oldCategories->sortBy('category_code')->values();

        return view('admin.finance.reports.category-mapping', compact('oldCategories', 'validCategories'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.old_code' => 'required|string',
            'mappings.*.old_base_name' => 'required|string',
            'mappings.*.new_code' => 'required|string',
        ]);

        $validCategories = $this->getValidCategories();

        DB::beginTransaction();
        try {
            foreach ($request->mappings as $mapping) {
                if ($mapping['old_code'] === $mapping['new_code']) {
                    continue;
                }

                $newCatInfo = $validCategories[$mapping['new_code']] ?? null;
                if (!$newCatInfo) {
                    throw new \Exception('Kategori baru tidak valid: ' . $mapping['new_code']);
                }

                $newBaseName = $newCatInfo['name'];
                $oldBaseName = $mapping['old_base_name'];
                $newGroup = $newCatInfo['group'];
                $newType = $newGroup === 'income' ? 'income' : 'expense';

                // Fungsi helper untuk memisahkan suffix dari nama lama
                $getSuffix = function ($fullName) use ($oldBaseName) {
                    if (str_starts_with($fullName, $oldBaseName)) {
                        return substr($fullName, strlen($oldBaseName)); // Mengambil " - Ray Manurung"
                    } elseif (str_contains($fullName, ' - ')) {
                        return ' - ' . explode(' - ', $fullName, 2)[1]; // Fallback
                    }
                    return '';
                };

                // 1. Update financial_report_details (per-record untuk mempertahankan suffix)
                $details = DB::table('financial_report_details')
                    ->where('category_code', $mapping['old_code'])
                    ->get(['id', 'category_name']);

                foreach ($details as $detail) {
                    $suffix = $getSuffix($detail->category_name ?? '');
                    DB::table('financial_report_details')
                        ->where('id', $detail->id)
                        ->update([
                            'category_code' => $mapping['new_code'],
                            'category_name' => $newBaseName . $suffix, // Gabungkan nama baru + suffix lama
                            'category_group' => $newGroup, // Update group juga
                            'type' => $newType, // Update type juga
                        ]);
                }

                // 2. Update financial_transactions
                $transactions = DB::table('financial_transactions')
                    ->where('category_code', $mapping['old_code'])
                    ->get(['id', 'category_name']);

                foreach ($transactions as $tx) {
                    $suffix = $getSuffix($tx->category_name ?? '');
                    $updateData = [
                        'category_code' => $mapping['new_code'],
                        'category_name' => $newBaseName . $suffix,
                    ];

                    // Update group & type jika kolom tersebut ada di tabel transactions
                    if (Schema::hasColumn('financial_transactions', 'category_group')) {
                        $updateData['category_group'] = $newGroup;
                    }
                    if (Schema::hasColumn('financial_transactions', 'type')) {
                        $updateData['type'] = $newType;
                    }

                    DB::table('financial_transactions')->where('id', $tx->id)->update($updateData);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Semua kategori berhasil dipetakan dan diperbarui di database.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui kategori: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memperbarui kategori: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
