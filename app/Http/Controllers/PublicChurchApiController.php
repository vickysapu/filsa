<?php

namespace App\Http\Controllers;

use App\Models\ChurchProfile;
use App\Models\ChurchStructure;
use App\Models\Jemaat;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicChurchApiController extends Controller
{
    private const STRUCTURE_COLUMNS = ['id', 'parent_id', 'jemaat_id', 'position_name', 'position_level', 'sort_order', 'description', 'is_active', 'depth'];

    private const JEMAAT_COLUMNS = ['id', 'nama_lengkap', 'foto_url', 'priority'];

    public function jemaat(Request $request): JsonResponse
    {
        try {
            $ids = $this->activeStructures($this->resolveBranchId($request))
                ->whereNotNull('jemaat_id')
                ->pluck('jemaat_id')
                ->unique()
                ->values();

            $data = Jemaat::withoutGlobalScope(BranchScope::class)
                ->whereIn('id', $ids)
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'foto', 'priority'])
                ->map(fn ($j) => [
                    'id' => $j->id,
                    'nama_lengkap' => $j->nama_lengkap,
                    'foto_url' => $this->fotoUrl($j->foto, $j->id),
                    'priority' => $j->priority ?? 'none',
                ])
                ->values();

            return $this->ok('jemaats', self::JEMAAT_COLUMNS, $data);
        } catch (\Throwable $e) {
            return $this->fail('jemaat', $e);
        }
    }

    public function structures(Request $request): JsonResponse
    {
        try {
            $rows = $this->activeStructures($this->resolveBranchId($request))->get();

            $knownIds = $rows->pluck('id')->all();

            $grouped = $rows->groupBy(
                fn ($row) => in_array($row->parent_id, $knownIds, true) ? $row->parent_id : 'root'
            );

            $ordered = [];
            $seen = [];

            $walk = function (string $key, int $depth) use (&$walk, $grouped, &$ordered, &$seen) {
                foreach ($grouped->get($key, collect())->sortBy('sort_order') as $row) {
                    if (isset($seen[$row->id])) {
                        continue;
                    }

                    $seen[$row->id] = true;
                    $ordered[] = $this->structureRow($row, $depth);
                    $walk((string) $row->id, $depth + 1);
                }
            };

            $walk('root', 0);

            foreach ($rows as $row) {
                if (!isset($seen[$row->id])) {
                    $seen[$row->id] = true;
                    $ordered[] = $this->structureRow($row, 0);
                }
            }

            return $this->ok('church_structures', self::STRUCTURE_COLUMNS, $ordered);
        } catch (\Throwable $e) {
            return $this->fail('structure', $e);
        }
    }

    private function activeStructures(?string $branchId)
    {
        return ChurchStructure::query()
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    private function resolveBranchId(Request $request): ?string
    {
        $branchId = $request->query('branch_id');

        if ($branchId) {
            return (string) $branchId;
        }

        $fromProfile = ChurchProfile::query()->withoutGlobalScopes()->value('branch_id');

        return $fromProfile ? (string) $fromProfile : null;
    }

    /**
     * Ubah nilai kolom `foto` (path/URL apa pun) menjadi URL absolut yang bisa dibuka browser.
     *
     * Bentuk yang didukung:
     *  - URL penuh (http://, https://, //) atau data: URI  -> dipakai apa adanya
     *  - storage/xxx.jpg                                   -> asset('storage/xxx.jpg')
     *  - assets/xxx.jpg, uploads/xxx.jpg (file di /public)   -> asset('assets/xxx.jpg')
     *  - jemaat/xxx.jpg (file di disk "public")             -> asset('storage/jemaat/xxx.jpg')
     *
     * Mengembalikan null hanya kalau kolom foto memang kosong.
     */
    private function fotoUrl(?string $foto, $jemaatId = null): ?string
    {
        $foto = trim((string) $foto);

        if ($foto === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $foto) || str_starts_with($foto, 'data:')) {
            return $foto;
        }

        $path = ltrim((string) preg_replace('#^/?public/#', '', $foto), '/');

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        Log::warning('Foto jemaat tidak ditemukan di public/ maupun disk public', [
            'jemaat_id' => $jemaatId,
            'foto' => $foto,
        ]);

        // Tebakan terbaik supaya URL tetap terbentuk (tidak null) dan bisa dicek di browser.
        return asset(preg_match('#^(assets|images|uploads)/#', $path) ? $path : 'storage/' . $path);
    }

    private function structureRow($row, int $depth): array
    {
        return [
            'id' => $row->id,
            'parent_id' => $row->parent_id,
            'jemaat_id' => $row->jemaat_id,
            'position_name' => $row->position_name,
            'position_level' => $row->position_level,
            'sort_order' => $row->sort_order,
            'description' => $row->description,
            'is_active' => (int) $row->is_active,
            'depth' => $depth,
        ];
    }

    private function ok(string $table, array $columns, $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'table' => $table,
                'columns_returned' => $columns,
                'total_records' => count($data),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    private function fail(string $name, \Throwable $e): JsonResponse
    {
        Log::error("Public {$name} API error: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem.',
        ], 500);
    }
}