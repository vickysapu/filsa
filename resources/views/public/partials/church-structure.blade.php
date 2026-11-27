@php
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    $t0 = microtime(true);
    $trace = Str::random(6);

    $debug = [
        'trace' => $trace,
        'config' => [
            'structure' => ['url' => $structureUrl, 'has_token' => $structureToken !== ''],
            'jemaat' => ['url' => $jemaatUrl, 'has_token' => $jemaatToken !== ''],
            'asset_base' => asset('/'),
        ],
        'steps' => [],
        'requests' => [],
        'cache' => [],
        'errors' => [],
        'jemaat_detail' => [],
        'people' => [],
        'summary' => [],
        'hints' => [],
        'why' => null,
    ];

    // ---------------------------------------------------------------
    // Helper log: simpan ke $debug['steps'] DAN ke storage/logs/laravel.log
    // ---------------------------------------------------------------
    $step = function (string $level, string $step, string $msg, array $ctx = []) use (&$debug, $trace) {
        $debug['steps'][] = [
            't' => now()->format('H:i:s.v'),
            'level' => $level,
            'step' => $step,
            'msg' => $msg,
            'ctx' => $ctx,
        ];
        Log::log($level, "[church:$trace] [$step] $msg", $ctx);
    };

    $step('info', '00-config', 'Mulai render struktur', [
        'structureUrl' => $structureUrl,
        'jemaatUrl' => $jemaatUrl,
        'structureToken_set' => $structureToken !== '',
        'jemaatToken_set' => $jemaatToken !== '',
    ]);

    if ($structureUrl === '') $step('error', '00-config', 'structureUrl KOSONG');
    if ($jemaatUrl === '') $step('error', '00-config', 'jemaatUrl KOSONG');
    if ($structureToken === '') $step('error', '00-config', 'structureToken KOSONG');
    if ($jemaatToken === '') $step('error', '00-config', 'jemaatToken KOSONG');

    // ---------------------------------------------------------------
    // Pemanggil API (semua kegagalan dicatat, termasuk koneksi/timeout)
    // ---------------------------------------------------------------
    $callApi = function (string $label, string $url, string $token = '', array $query = []) use (&$debug, $step) {
        if ($url === '') {
            throw new \RuntimeException('URL API belum diisi');
        }

        $started = microtime(true);
        $req = Http::timeout(5)->retry(2, 200, null, false)->acceptJson();

        if ($token !== '') {
            $req = $req->withToken($token);
        }

        try {
            $res = $req->get($url, $query);
        } catch (\Throwable $e) {
            $ms = (int) round((microtime(true) - $started) * 1000);
            $debug['requests'][] = [
                'label' => $label, 'url' => $url, 'status' => 0, 'ms' => $ms,
                'success' => null, 'rows' => null,
                'error' => class_basename($e) . ': ' . Str::limit($e->getMessage(), 150),
            ];
            $step('error', '10-http', "[$label] gagal konek/timeout", [
                'url' => $url, 'ms' => $ms, 'exception' => class_basename($e), 'message' => Str::limit($e->getMessage(), 200),
            ]);
            throw $e;
        }

        $ms = (int) round((microtime(true) - $started) * 1000);
        $payload = $res->json();
        $isJson = is_array($payload);
        $apiError = $isJson ? ($payload['error'] ?? $payload['message'] ?? null) : null;
        $data = $isJson ? ($payload['data'] ?? null) : null;

        $debug['requests'][] = [
            'label' => $label,
            'url' => $url,
            'status' => $res->status(),
            'ms' => $ms,
            'success' => $isJson ? ($payload['success'] ?? null) : null,
            'rows' => is_array($data) ? (array_is_list($data) ? count($data) : 1) : null,
            'error' => $isJson ? $apiError : Str::limit(strip_tags($res->body()), 120),
        ];

        $level = ($res->successful() && $isJson && ($payload['success'] ?? false)) ? 'info' : 'error';
        $step($level, '10-http', "[$label] HTTP {$res->status()} dalam {$ms}ms", [
            'is_json' => $isJson,
            'success' => $isJson ? ($payload['success'] ?? null) : null,
            'payload_keys' => $isJson ? array_keys($payload) : null,
            'data_type' => gettype($data),
            'api_error' => $apiError,
            'body_preview' => $isJson ? null : Str::limit(strip_tags($res->body()), 200),
        ]);

        if ($ms > 3000) {
            $step('warning', '10-http', "[$label] lambat ({$ms}ms), timeout=5s, risiko gagal di server produksi");
        }

        if (!$isJson || !($payload['success'] ?? false)) {
            throw new \RuntimeException('HTTP ' . $res->status() . ': ' . ($apiError ?? 'respons tidak valid'));
        }

        return $data ?? [];
    };

    // ---------------------------------------------------------------
    // Cache wrapper (log hit/miss + error)
    // ---------------------------------------------------------------
    $cached = function (string $label, string $key, \Closure $loader) use (&$debug, $step) {
        try {
            $cacheKey = 'church_api_' . md5($key);
            $hit = Cache::has($cacheKey);
            $value = Cache::remember($cacheKey, 300, $loader);
            $debug['cache'][] = ['label' => $label, 'hit' => $hit];
            if ($hit) {
                $step('debug', '11-cache', "[$label] diambil dari cache (data lama bisa masih dipakai 5 menit)");
            }

            return $value;
        } catch (\Throwable $e) {
            $debug['errors'][] = [
                'label' => $label,
                'type' => class_basename($e),
                'message' => Str::limit($e->getMessage(), 300),
            ];
            $step('error', '11-cache', "[$label] ERROR: " . class_basename($e), ['message' => Str::limit($e->getMessage(), 300)]);

            return null;
        }
    };

    // ===============================================================
    // 1. AMBIL STRUKTUR
    // ===============================================================
    $structureData = $cached(
        'struktur',
        $structureUrl . '|' . $structureToken,
        fn () => $callApi('struktur', $structureUrl, $structureToken, ['per_page' => 100])
    );

    if ($structureData === null) {
        $step('error', '20-struktur', 'structureData = NULL (request gagal, lihat log 10-http / 11-cache)');
    } else {
        $step('info', '20-struktur', 'Struktur diterima', [
            'type' => gettype($structureData),
            'count' => is_array($structureData) ? count($structureData) : null,
            'is_list' => is_array($structureData) ? array_is_list($structureData) : null,
            'keys_level1' => is_array($structureData) ? array_slice(array_keys($structureData), 0, 15) : null,
        ]);

        // Deteksi respons paginasi: data = {current_page, data:[...]}
        if (is_array($structureData) && !array_is_list($structureData) && isset($structureData['data']) && is_array($structureData['data'])) {
            $step('warning', '20-struktur', 'Respons berbentuk PAGINASI (data.data). Otomatis di-unwrap.', [
                'meta_keys' => array_keys($structureData),
            ]);
            $structureData = $structureData['data'];
        } elseif (is_array($structureData) && !array_is_list($structureData) && !empty($structureData)) {
            $step('warning', '20-struktur', 'Struktur bukan list (objek tunggal?), dibungkus jadi list', [
                'keys' => array_keys($structureData),
            ]);
            $structureData = [$structureData];
        }

        if (is_array($structureData) && isset($structureData[0]) && is_array($structureData[0])) {
            $step('debug', '20-struktur', 'Contoh baris pertama', [
                'fields' => array_keys($structureData[0]),
                'sample' => array_intersect_key($structureData[0], array_flip(['id', 'parent_id', 'jemaat_id', 'is_active', 'position_name', 'position_level', 'sort_order'])),
            ]);
        }
    }

    // ===============================================================
    // 2. FILTER AKTIF
    // ===============================================================
    $allRows = collect($structureData ?? []);
    $rows = $allRows->filter(fn ($s) => is_array($s) && (int) ($s['is_active'] ?? 0) === 1);
    $rowIds = $rows->pluck('id')->all();

    $step($rows->isEmpty() && $allRows->isNotEmpty() ? 'error' : 'info', '30-filter', 'Filter is_active = 1', [
        'total_baris' => $allRows->count(),
        'aktif' => $rows->count(),
        'nilai_is_active_ditemukan' => $allRows->pluck('is_active')->map(fn ($v) => var_export($v, true))->unique()->values()->all(),
    ]);

    // ===============================================================
    // 3. URUTKAN HIERARKI
    // ===============================================================
    $grouped = $rows->groupBy(fn ($s) => in_array($s['parent_id'] ?? null, $rowIds, true) ? (string) $s['parent_id'] : 'root');
    $ordered = [];

    $walk = function (string $key) use (&$walk, $grouped, &$ordered) {
        foreach ($grouped->get($key, collect())->sortBy('sort_order') as $s) {
            $ordered[] = $s;
            $walk((string) $s['id']);
        }
    };

    $walk('root');

    $step(count($ordered) < $rows->count() ? 'warning' : 'info', '40-urut', 'Urutan hirarki selesai', [
        'aktif' => $rows->count(),
        'terurut' => count($ordered),
        'root' => $grouped->get('root', collect())->count(),
        'hilang' => count($ordered) < $rows->count() ? 'ada baris tidak terjangkau (parent_id melingkar?)' : null,
    ]);

    // ===============================================================
    // 4. AMBIL DATA JEMAAT (satu request per orang)
    // ===============================================================
    $jemaatIds = collect($ordered)->pluck('jemaat_id')->filter()->unique()->values();
    $tanpaJemaatId = collect($ordered)->filter(fn ($s) => empty($s['jemaat_id']))->pluck('id')->all();
    $jemaatMap = [];
    $jemaatGagal = 0;
    $jemaatSkip = 0;

    $step($jemaatIds->isEmpty() && count($ordered) > 0 ? 'error' : 'info', '50-jemaat', 'Daftar jemaat_id dari struktur', [
        'unik' => $jemaatIds->count(),
        'baris_struktur_tanpa_jemaat_id' => $tanpaJemaatId,
        'ids' => $jemaatIds->take(30)->all(),
    ]);

    foreach ($jemaatIds as $jid) {
        $detailUrl = rtrim($jemaatUrl, '/') . '/' . $jid;
        $row = $cached(
            'jemaat:' . $jid,
            $detailUrl . '|' . $jemaatToken,
            fn () => $callApi('jemaat:' . $jid, $detailUrl, $jemaatToken)
        );

        if (is_array($row) && isset($row[0]) && is_array($row[0])) {
            $row = $row[0];
        }

        if (is_array($row) && !empty($row['id']) && empty($row['deleted_at'])) {
            $jemaatMap[$jid] = $row;
            $debug['jemaat_detail'][$jid] = 'OK';
        } elseif ($row === null) {
            $jemaatGagal++;
            $debug['jemaat_detail'][$jid] = 'GAGAL request (lihat errors / 10-http)';
            $step('error', '51-jemaat', "jemaat $jid: request GAGAL");
        } elseif (is_array($row) && !empty($row['deleted_at'])) {
            $jemaatSkip++;
            $debug['jemaat_detail'][$jid] = 'DILEWATI: sudah dihapus (deleted_at = ' . $row['deleted_at'] . ')';
            $step('warning', '51-jemaat', "jemaat $jid: soft-deleted, dilewati");
        } else {
            $jemaatSkip++;
            $debug['jemaat_detail'][$jid] = 'DILEWATI: respons kosong / tanpa field id';
            $step('warning', '51-jemaat', "jemaat $jid: respons kosong atau tanpa id", [
                'type' => gettype($row),
                'keys' => is_array($row) ? array_keys($row) : null,
            ]);
        }
    }

    $step($jemaatGagal > 0 ? 'error' : 'info', '52-jemaat', 'Ringkasan pengambilan jemaat', [
        'diminta' => $jemaatIds->count(),
        'berhasil' => count($jemaatMap),
        'gagal' => $jemaatGagal,
        'dilewati' => $jemaatSkip,
    ]);

    $jemaatData = ($structureData === null || ($jemaatIds->isNotEmpty() && $jemaatGagal === $jemaatIds->count()))
        ? null
        : $jemaatMap;

    if ($jemaatData === null) {
        $step('error', '53-gate', 'jemaatData = NULL, blok penyusunan di bawah TIDAK dijalankan', [
            'structureData_null' => $structureData === null,
            'semua_jemaat_gagal' => $jemaatIds->isNotEmpty() && $jemaatGagal === $jemaatIds->count(),
        ]);
    }

    // ===============================================================
    // 5. AVATAR FALLBACK
    // ===============================================================
    $warnaLatar = ['b6e3f4', 'c0aede', 'd1d4f9', 'ffd5dc', 'ffdfbf', 'c7f0d8'];

    $gayaEkspresi = [
        ['eyes' => 'variant01', 'eyebrows' => 'variant01', 'mouth' => 'variant01'],
        ['eyes' => 'variant05', 'eyebrows' => 'variant08', 'mouth' => 'variant09'],
        ['eyes' => 'variant12', 'eyebrows' => 'variant03', 'mouth' => 'variant17'],
    ];

    $buatFoto = function (string $nama, int $no) use ($warnaLatar, $gayaEkspresi): array {
        $seed = rawurlencode($nama);
        $bg = $warnaLatar[$no % count($warnaLatar)];

        return array_map(function ($gaya) use ($seed, $bg) {
            return 'https://api.dicebear.com/9.x/adventurer/svg?seed=' .
                $seed .
                '&backgroundColor=' .
                $bg .
                '&earringsProbability=0' .
                '&eyes=' .
                $gaya['eyes'] .
                '&eyebrows=' .
                $gaya['eyebrows'] .
                '&mouth=' .
                $gaya['mouth'];
        }, $gayaEkspresi);
    };

    // ===============================================================
    // 5b. URL FOTO: path relatif harus diarahkan ke server tempat file berada
    //     (asal API), bukan domain website ini. Bisa dioverride lewat
    //     config('services.church_api.asset_base').
    // ===============================================================
    $assetBase = rtrim((string) config('services.church_api.asset_base', ''), '/');
    $assetBaseSumber = $assetBase !== '' ? 'config(services.church_api.asset_base)' : null;

    // Host yang tidak bisa dibuka dari browser pengunjung (alamat internal/loopback/IP privat).
    $hostInternal = function (?string $host): bool {
        if (!$host) {
            return true;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) || !str_contains($host, '.')) {
            return true;
        }

        return filter_var($host, FILTER_VALIDATE_IP) !== false
            && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    };

    $apiHost = $jemaatUrl !== '' ? (parse_url($jemaatUrl, PHP_URL_HOST) ?: null) : null;

    if ($assetBase === '' && $jemaatUrl !== '' && !$hostInternal($apiHost)) {
        $u = parse_url($jemaatUrl);
        $assetBase = ($u['scheme'] ?? 'https') . '://' . $u['host'] . (isset($u['port']) ? ':' . $u['port'] : '');
        $assetBaseSumber = 'asal API ($jemaatUrl)';
    }

    if ($assetBase === '') {
        $assetBase = rtrim(asset('/'), '/');
        $assetBaseSumber = 'asset(/) situs ini' . ($apiHost && $hostInternal($apiHost) ? ' (host API internal dilewati)' : '');
    }

    // Halaman HTTPS tidak boleh memuat gambar HTTP (mixed content akan diblokir browser).
    if (request()->isSecure() && str_starts_with($assetBase, 'http://')) {
        $assetBase = 'https://' . substr($assetBase, 7);
        $assetBaseSumber .= ' + dinaikkan ke https';
    }

    $step('info', '55-foto', 'Basis URL foto', [
        'asset_base' => $assetBase,
        'sumber' => $assetBaseSumber,
        'host_api' => $apiHost,
        'host_api_internal' => $apiHost ? $hostInternal($apiHost) : null,
    ]);

    $resolveFoto = function (?string $path) use ($assetBase) {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        // Jika sudah berupa URL atau Base64, gunakan langsung
        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        // Hilangkan prefix public/storage jika sudah ada
        $path = preg_replace('#^/?public/storage/#', '', $path);

        // Hilangkan prefix storage jika ada
        $path = preg_replace('#^/?storage/#', '', $path);

        $path = ltrim($path, '/');

        return rtrim($assetBase, '/') . '/public/storage/' . $path;
    };

    // ===============================================================
    // 6. SUSUN DATA TAMPIL
    // ===============================================================
    $status = 'gagal';
    $semua = [];
    $jumlahUtama = 0;
    $totalDetail = 0;
    $itemsCount = 0;
    $adaPrimer = false;
    $kelompok = collect();
    $semuaMap = collect();
    $tidakCocok = [];

    if ($jemaatData !== null && $structureData !== null) {
        $items = collect($ordered)
            ->map(function ($s) use ($jemaatData, &$tidakCocok, $resolveFoto) {
                $p = $jemaatData[$s['jemaat_id'] ?? ''] ?? null;

                if (!$p) {
                    $tidakCocok[] = [
                        'struktur_id' => $s['id'] ?? null,
                        'jemaat_id' => $s['jemaat_id'] ?? null,
                        'jabatan' => $s['position_name'] ?? null,
                    ];

                    return null;
                }

                $fotoRaw = $p['foto_url'] ?? $p['foto'] ?? null;
                $foto = $resolveFoto($fotoRaw);

                return [
                    'nama' => $p['nama_lengkap'] ?? '-',
                    'jabatan' => $s['position_name'] ?? '',
                    'level' => $s['position_level'] ?? '',
                    'deskripsi' => $s['description'] ?? null,
                    'priority' => $p['priority'] ?? null,
                    'foto' => $foto,
                    'foto_raw' => $fotoRaw,
                ];
            })
            ->filter()
            ->values();

        $itemsCount = $items->count();

        $step($itemsCount === 0 ? 'error' : 'info', '60-cocok', 'Pencocokan struktur ↔ jemaat', [
            'cocok' => $itemsCount,
            'tidak_cocok' => count($tidakCocok),
            'contoh_tidak_cocok' => array_slice($tidakCocok, 0, 10),
            'field_nama_lengkap_ada' => count($jemaatMap) ? array_key_exists('nama_lengkap', reset($jemaatMap)) : null,
        ]);

        foreach ($items as $it) {
            $step($it['foto'] ? 'info' : 'warning', '62-foto', ($it['foto'] ? 'Foto OK' : 'Foto KOSONG (pakai avatar)') . ': ' . $it['nama'], [
                'foto_raw_dari_api' => $it['foto_raw'],
                'url_final' => $it['foto'],
            ]);
        }

        $utama = $items->filter(fn ($o) => $o['priority'] === 'primer');
        $adaPrimer = $utama->isNotEmpty();

        $step('info', '61-primer', 'Pengurus utama (priority = primer)', [
            'jumlah_primer' => $utama->count(),
            'nilai_priority_ditemukan' => $items->pluck('priority')->map(fn ($v) => var_export($v, true))->unique()->values()->all(),
        ]);

        if ($utama->isEmpty()) {
            $utama = $items->take(5);
            $step('warning', '61-primer', 'Tidak ada priority=primer, memakai 5 orang pertama');
        }

        $detail = $items->reject(fn ($o, $k) => $utama->has($k));

        $no = 0;

        foreach ($utama as $orang) {
            $no++;
            $semua[] = [
                'id' => 'p' . $no,
                'nama' => $orang['nama'],
                'jabatan' => $orang['jabatan'],
                'bidang' => 'Pengurus Utama',
                'deskripsi' => $orang['deskripsi'] ?: 'Melayani sebagai ' . $orang['jabatan'] . '.',
                'fotos' => $orang['foto'] ? [$orang['foto']] : $buatFoto($orang['nama'], $no),
                'fallback' => $buatFoto($orang['nama'], $no)[0],
            ];
            $debug['people'][] = [
                'nama' => $orang['nama'],
                'jabatan' => $orang['jabatan'],
                'kelompok' => 'Pengurus Utama',
                'priority' => $orang['priority'],
                'foto' => $orang['foto'] ?: '(tidak ada, pakai avatar)',
            ];
        }

        $jumlahUtama = count($semua);

        foreach ($detail as $orang) {
            $no++;
            $bidang = trim(ucwords(str_replace(['_', '-'], ' ', $orang['level']))) ?: 'Lainnya';
            $semua[] = [
                'id' => 'p' . $no,
                'nama' => $orang['nama'],
                'jabatan' => $orang['jabatan'],
                'bidang' => $bidang,
                'deskripsi' => $orang['deskripsi'] ?: 'Melayani di bidang ' . $bidang . ' sebagai ' . $orang['jabatan'] . '.',
                'fotos' => $orang['foto'] ? [$orang['foto']] : $buatFoto($orang['nama'], $no),
                'fallback' => $buatFoto($orang['nama'], $no)[0],
            ];
            $debug['people'][] = [
                'nama' => $orang['nama'],
                'jabatan' => $orang['jabatan'],
                'kelompok' => $bidang,
                'priority' => $orang['priority'],
                'foto' => $orang['foto'] ?: '(tidak ada, pakai avatar)',
            ];
        }

        $totalDetail = count($semua) - $jumlahUtama;
        $kelompok = collect($semua)->groupBy('bidang');
        $semuaMap = collect($semua)->keyBy('id');
        $status = count($semua) > 0 ? 'ok' : 'kosong';

        $step('info', '70-hasil', 'Data tampil tersusun', [
            'status' => $status,
            'total' => count($semua),
            'utama' => $jumlahUtama,
            'bidang_lain' => $totalDetail,
            'kelompok' => $kelompok->map->count()->all(),
        ]);
    }

    // ===============================================================
    // 7. RINGKASAN + ALASAN UTAMA
    // ===============================================================
    $debug['summary'] = [
        'status' => $status,
        'total_ms' => (int) round((microtime(true) - $t0) * 1000),
        'struktur_baris_api' => is_array($structureData) ? count($structureData) : null,
        'struktur_aktif' => $rows->count(),
        'jemaat_id_di_struktur' => $jemaatIds->count(),
        'jemaat_berhasil' => count($jemaatMap),
        'jemaat_request_gagal' => $jemaatGagal,
        'jemaat_kosong_atau_terhapus' => $jemaatSkip,
        'cocok_struktur_jemaat' => $itemsCount,
        'pengurus_utama' => $jumlahUtama,
        'bidang_lain' => $totalDetail,
        'field_struktur' => is_array($structureData) && isset($structureData[0]) && is_array($structureData[0]) ? array_keys($structureData[0]) : [],
        'field_jemaat' => count($jemaatMap) ? array_keys(reset($jemaatMap)) : [],
    ];

    // Urutan pengecekan = urutan alur; yang pertama cocok adalah penyebab utama.
    $debug['why'] = match (true) {
        $structureUrl === '' || $jemaatUrl === '' => 'URL API (struktur/jemaat) belum diisi.',
        $structureToken === '' || $jemaatToken === '' => 'Token API kosong.',
        $structureData === null => 'Request API STRUKTUR gagal (timeout / 401 / 403 / 404 / 500 / bukan JSON). Lihat log 10-http & errors.',
        $allRows->isEmpty() => 'API struktur sukses tapi datanya kosong (cek per_page, filter, atau isi tabel).',
        $rows->isEmpty() => 'Ada data struktur, tetapi tidak ada yang is_active = 1.',
        $jemaatIds->isEmpty() => 'Semua baris struktur tidak punya jemaat_id.',
        $jemaatData === null => 'SEMUA request detail JEMAAT gagal (token jemaat salah / URL salah / timeout). Lihat log 51-jemaat.',
        count($jemaatMap) === 0 => 'Detail jemaat terambil tetapi semuanya kosong / soft-deleted.',
        $itemsCount === 0 => 'jemaat_id di struktur tidak ada yang cocok dengan id jemaat yang berhasil diambil.',
        $status === 'kosong' => 'Data cocok = 0 setelah disusun.',
        $status === 'ok' => 'Data berhasil disusun (' . count($semua) . ' orang). Jika masih tidak tampil, masalah ada di bagian HTML/JS di bawah blok ini.',
        default => 'Tidak diketahui, baca $debug[steps].',
    };

    $step($status === 'ok' ? 'info' : 'error', '99-akhir', 'KESIMPULAN: ' . $debug['why'], $debug['summary']);

    $debug['hints'] = array_values(array_filter([
        $structureUrl === '' ? 'structureUrl masih kosong.' : null,
        $jemaatUrl === '' ? 'jemaatUrl masih kosong.' : null,
        $structureToken === '' ? 'structureToken kosong. API butuh token per tabel.' : null,
        $jemaatToken === '' ? 'jemaatToken kosong. API butuh token per tabel.' : null,
        $structureData === null ? 'Request API struktur gagal. Lihat bagian errors.' : null,
        ($structureData !== null && $rows->isEmpty()) ? 'API struktur berhasil, tetapi tidak ada baris dengan is_active = 1.' : null,
        ($jemaatIds->isNotEmpty() && count($jemaatMap) === 0) ? 'Tidak ada data jemaat yang terambil. Periksa token tabel jemaats dan jemaat_id di struktur.' : null,
        (count($jemaatMap) > 0 && $itemsCount === 0) ? 'Data jemaat ada, tetapi jemaat_id struktur tidak cocok dengan id jemaat.' : null,
        ($itemsCount > 0 && !$adaPrimer) ? 'Tidak ada jemaat dengan priority = primer. Memakai 5 orang pertama sebagai Pengurus Utama.' : null,
        ($jemaatIds->count() > 15) ? 'Ada ' . $jemaatIds->count() . ' request jemaat berurutan (N+1). Berisiko kena max_execution_time / timeout. Pertimbangkan endpoint bulk.' : null,
    ]));
@endphp

@if (config('app.debug'))
    @php
        $dbgSteps = collect($debug['steps'])
            ->map(fn ($x) => ['jam' => $x['t'], 'level' => $x['level'], 'step' => $x['step'], 'pesan' => $x['msg']])
            ->values()
            ->all();
        $dbgPayload = [
            'trace' => $debug['trace'],
            'why' => $debug['why'],
            'summary' => $debug['summary'],
            'steps' => $dbgSteps,
            'requests' => $debug['requests'],
            'semua' => $semua,
            'hints' => $debug['hints'],
        ];
    @endphp
    <script>
        (function () {
            var d = @json($dbgPayload);
            console.group('Church struktur [' + d.trace + ']');
            console.log('KESIMPULAN:', d.why);
            console.log('Ringkasan:', d.summary);
            console.table(d.steps);
            console.log('Request API:', d.requests);
            console.log('Orang yang akan ditampilkan:', d.semua);
            console.log('Hint:', d.hints);
            console.groupEnd();

            // Cek apakah tiap foto asli benar-benar bisa dimuat browser
            (d.semua || []).forEach(function (p) {
                var u = p.fotos && p.fotos[0];
                if (!u || u.indexOf('dicebear.com') !== -1) return;
                var im = new Image();
                im.onload = function () { console.log('%cFOTO OK', 'color:#2e7d32;font-weight:bold', p.nama, u); };
                im.onerror = function () { console.warn('FOTO GAGAL DIMUAT (404 / storage:link / host salah / mixed content):', p.nama, u); };
                im.src = u;
            });
        })();
    </script>
@endif

<style>
    .structure-section { padding-top: 2rem; }

    .structure-head {
        max-width: 980px; margin: 0 auto 2rem;
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 1.5rem; flex-wrap: wrap;
    }

    .structure-head-text { max-width: 560px; }

    .structure-message {
        max-width: 980px; margin: 0 auto; padding: 2rem 0; text-align: center;
        color: var(--zaitun-text-secondary);
        border-top: 1px solid var(--zaitun-border);
        border-bottom: 1px solid var(--zaitun-border);
    }

    .structure-controls { display: flex; gap: 0.5rem; }

    .structure-btn {
        width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--zaitun-border); border-radius: 2px; background: transparent;
        color: var(--zaitun-deep-dark); font-size: 1.3rem; cursor: pointer;
        transition: background 0.2s, color 0.2s, border-color 0.2s;
    }

    .structure-btn:hover:not(:disabled) {
        background: var(--zaitun-deep-dark); border-color: var(--zaitun-deep-dark); color: var(--zaitun-sand);
    }

    .structure-btn:disabled { opacity: 0.35; cursor: not-allowed; }

    .structure-btn:focus-visible,
    .structure-more:focus-visible,
    .structure-track:focus-visible,
    .structure-card:focus-visible {
        outline: 2px solid var(--zaitun-primary); outline-offset: 3px;
    }

    .structure-carousel { max-width: 980px; margin: 0 auto; }

    .structure-track {
        --gap: 1.25rem;
        display: flex; gap: var(--gap); overflow-x: auto;
        scroll-snap-type: x mandatory; scroll-behavior: smooth;
        scrollbar-width: none; -webkit-overflow-scrolling: touch;
    }

    .structure-track::-webkit-scrollbar { display: none; }

    .structure-card {
        position: relative; flex: 0 0 calc((100% - 2 * var(--gap)) / 3);
        scroll-snap-align: start; aspect-ratio: 3 / 4.2; border-radius: 2px;
        overflow: hidden; background: var(--zaitun-deep-dark); isolation: isolate; cursor: pointer;
    }

    .structure-card img {
        position: absolute; inset: 0; width: 100%; height: 100%;
        object-fit: cover; object-position: center 30%; display: block;
        z-index: -2; transition: transform 0.6s ease;
    }

    .structure-card:hover img { transform: scale(1.05); }

    .structure-badge {
        position: absolute; top: 1rem; left: 1rem; padding: 0.4rem 0.75rem;
        background: rgba(30, 38, 25, 0.55);
        backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
        border-radius: 2px; color: #fff; font-size: 0.72rem; font-weight: 600;
        letter-spacing: 0.08em; text-transform: uppercase; z-index: 3;
    }

    .structure-overlay {
        position: absolute; left: 0; right: 0; bottom: 0; padding: 6rem 1.25rem 1.25rem;
        display: flex; flex-direction: column; align-items: flex-end; gap: 0.35rem;
        text-align: right; color: #fff;
        background: linear-gradient(to top,
            rgba(30, 38, 25, 0.95) 0%, rgba(30, 38, 25, 0.8) 35%,
            rgba(30, 38, 25, 0.4) 70%, rgba(30, 38, 25, 0) 100%);
        z-index: 2;
    }

    .structure-role {
        margin: 0; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.06em;
        text-transform: uppercase; color: rgba(255, 255, 255, 0.75);
    }

    .structure-name {
        margin: 0; font-family: 'Playfair Display', Georgia, serif; font-size: 1.35rem;
        font-weight: 500; line-height: 1.25; color: #fff; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
    }

    .structure-desc {
        margin: 0.35rem 0 0; font-size: 0.85rem; line-height: 1.55; color: rgba(255, 255, 255, 0.82);
    }

    .structure-all { display: none; max-width: 980px; margin: 0 auto; }
    .is-all .structure-all { display: block; }
    .is-all .structure-carousel, .is-all .structure-controls { display: none; }

    .anim-out { animation: view-out 0.26s ease-in forwards; }
    .anim-in { animation: view-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .anim-in .structure-card { animation: card-in 0.55s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .anim-in .structure-card:nth-child(1) { animation-delay: 0.04s; }
    .anim-in .structure-card:nth-child(2) { animation-delay: 0.1s; }
    .anim-in .structure-card:nth-child(3) { animation-delay: 0.16s; }
    .anim-in .structure-card:nth-child(4) { animation-delay: 0.22s; }
    .anim-in .structure-card:nth-child(n+5) { animation-delay: 0.28s; }

    @keyframes view-out { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(14px); } }
    @keyframes view-in { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes card-in { from { opacity: 0; transform: translateY(26px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

    .structure-all-group { margin-bottom: 2.5rem; }

    .structure-all-title {
        display: flex; align-items: baseline; justify-content: space-between; gap: 1rem;
        margin: 0 0 1rem; padding-bottom: 0.6rem; border-bottom: 1px solid var(--zaitun-border);
        font-family: 'Playfair Display', Georgia, serif; font-size: 1.3rem; font-weight: 600;
        color: var(--zaitun-primary);
    }

    .structure-all-title span {
        font-family: inherit; font-size: 0.85rem; font-weight: 500; color: var(--zaitun-text-secondary);
    }

    .structure-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
    .structure-grid .structure-card { flex: none; aspect-ratio: 3 / 4; }
    .structure-grid .structure-desc { display: none; }
    .structure-grid .structure-overlay { padding-top: 4.5rem; }

    .structure-foot {
        max-width: 980px; margin: 1.75rem auto 0; display: flex; align-items: center;
        justify-content: space-between; gap: 1rem; flex-wrap: wrap;
    }

    .structure-counter { font-size: 0.9rem; color: var(--zaitun-text-secondary); }

    .structure-more {
        display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.4rem;
        border: 1px solid var(--zaitun-deep-dark); border-radius: 2px; background: transparent;
        color: var(--zaitun-deep-dark); font-weight: 600; font-size: 0.95rem; cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    .structure-more:hover { background: var(--zaitun-deep-dark); color: var(--zaitun-sand); }

    .person-dialog {
        position: fixed; inset: 0; margin: auto; width: min(1100px, calc(100% - 2rem));
        height: min(740px, calc(100vh - 2rem)); max-width: none; max-height: none; padding: 0;
        border: 0; border-radius: 2px; background: var(--zaitun-sand); color: var(--zaitun-deep-dark);
        overflow: hidden;
    }

    .person-dialog[open] {
        display: grid; grid-template-columns: 1.4fr 1fr;
        animation: dialog-in 0.38s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .person-dialog[open]::backdrop { background: rgba(30, 38, 25, 0.68); animation: backdrop-in 0.38s ease both; }
    .person-dialog.is-closing { animation: dialog-out 0.26s ease-in forwards; }
    .person-dialog.is-closing::backdrop { animation: backdrop-out 0.26s ease-in forwards; }

    @keyframes dialog-in { from { opacity: 0; transform: translateY(28px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes dialog-out { from { opacity: 1; transform: translateY(0) scale(1); } to { opacity: 0; transform: translateY(20px) scale(0.96); } }
    @keyframes backdrop-in { from { opacity: 0; } to { opacity: 1; } }
    @keyframes backdrop-out { from { opacity: 1; } to { opacity: 0; } }

    .person-close { position: absolute; top: 0.75rem; right: 0.75rem; z-index: 6; background: var(--zaitun-sand); }

    .person-media {
        position: relative; height: 100%; min-height: 0; background: var(--zaitun-deep-dark);
        overflow: hidden; isolation: isolate;
    }

    .person-slides { position: absolute; inset: 0; z-index: 0; }

    .person-slide {
        position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
        object-position: center 30%; opacity: 0; transition: opacity 0.9s ease, transform 1.4s ease;
    }

    .person-slide.fx-zoom { transform: scale(1.25); }
    .person-slide.fx-slide { transform: translateX(12%); }
    .person-slide.fx-rotate { transform: scale(1.18) rotate(2.5deg); }
    .person-slide.is-active { opacity: 1; transform: none; z-index: 1; }

    .person-dots { position: absolute; top: 1rem; left: 1rem; right: 1rem; z-index: 4; display: flex; gap: 0.4rem; }

    .person-dot {
        position: relative; flex: 1; height: 3px; background: rgba(255, 255, 255, 0.35);
        border-radius: 2px; overflow: hidden;
    }

    .person-dot::after {
        content: ''; position: absolute; inset: 0; background: #fff;
        transform: scaleX(0); transform-origin: left;
    }

    .person-dot.is-done::after { transform: scaleX(1); }
    .person-dot.is-active::after { animation: person-fill 3s linear forwards; }

    @keyframes person-fill { from { transform: scaleX(0); } to { transform: scaleX(1); } }

    .person-media .structure-badge { top: 2rem; }
    .person-media .structure-name { font-size: 1.75rem; }

    .person-info {
        display: flex; flex-direction: column; justify-content: center; gap: 1.25rem;
        padding: 3.5rem 2rem 2rem; overflow-y: auto;
    }

    .person-dialog[open] .person-info > * { animation: info-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
    .person-dialog[open] .person-info > *:nth-child(2) { animation-delay: 0.1s; }

    @keyframes info-in { from { opacity: 0; transform: translateX(16px); } to { opacity: 1; transform: translateX(0); } }

    .person-info h4 {
        margin: 0 0 0.5rem; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: var(--zaitun-text-secondary);
    }

    .person-info p { margin: 0; font-size: 1rem; line-height: 1.7; color: var(--zaitun-deep-dark); }

    .person-meta { margin: 0; padding: 0; list-style: none; border-top: 1px solid var(--zaitun-border); }

    .person-meta li {
        display: flex; justify-content: space-between; gap: 1rem; padding: 0.75rem 0;
        border-bottom: 1px solid var(--zaitun-border); font-size: 0.92rem;
    }

    .person-meta li span:first-child { color: var(--zaitun-text-secondary); }
    .person-meta li span:last-child { font-weight: 600; text-align: right; }

    @media (max-width: 900px) {
        .structure-card { flex-basis: calc((100% - var(--gap)) / 2); }
        .structure-grid { grid-template-columns: repeat(2, 1fr); }
        .person-dialog[open] { grid-template-columns: 1.2fr 1fr; }
    }

    @media (max-width: 720px) {
        .person-dialog { height: auto; max-height: calc(100vh - 2rem); overflow-y: auto; }
        .person-dialog[open] { grid-template-columns: 1fr; }
        .person-media { height: 68vh; min-height: 380px; }
        .person-info { padding: 1.5rem 1.25rem 1.75rem; }
    }

    @media (max-width: 560px) {
        .structure-card { flex-basis: 85%; }
        .structure-grid { grid-template-columns: 1fr; }
        .structure-grid .structure-card { aspect-ratio: 4 / 5; }
    }

    @media (prefers-reduced-motion: reduce) {
        .structure-track { scroll-behavior: auto; }
        .structure-card img, .person-slide { transition: none; }
        .person-dialog[open], .person-dialog[open]::backdrop, .person-dialog[open] .person-info > *,
        .person-dialog.is-closing, .person-dialog.is-closing::backdrop,
        .anim-in, .anim-out, .anim-in .structure-card {
            animation-duration: 0.01s; animation-delay: 0s;
        }
    }
</style>

<section class="content-container structure-section about-structure" id="struktur">
    <div class="structure-head fade-in">
        <div class="structure-head-text">
            <span class="section-kicker">
                <i class="ri-organization-chart"></i> Struktur Gereja
            </span>
            <h2 class="section-headline" style="margin-bottom: 0.8rem;">Siapa yang Melayani di Gereja Ini</h2>
            <p class="section-lead" style="margin: 0;">
                Para pengurus utama yang memimpin pelayanan. Pengurus bidang lainnya ada di struktur lengkap.
            </p>
        </div>

        @if ($status === 'ok')
            <div class="structure-controls" id="structureControls" role="group"
                aria-label="Navigasi carousel struktur gereja">
                <button type="button" class="structure-btn" id="structurePrev" aria-label="Sebelumnya">
                    <i class="ri-arrow-left-s-line"></i>
                </button>
                <button type="button" class="structure-btn" id="structureNext" aria-label="Berikutnya">
                    <i class="ri-arrow-right-s-line"></i>
                </button>
            </div>
        @endif
    </div>

    @if ($status === 'gagal')
        <p class="structure-message">Data struktur gereja sementara tidak dapat ditampilkan. Silakan kembali beberapa saat lagi.</p>
    @elseif ($status === 'kosong')
        <p class="structure-message">Data struktur gereja belum tersedia.</p>
    @else
        <div class="structure-carousel fade-in" id="structureCarousel">
            <div class="structure-track" id="structureTrack" tabindex="0" role="region"
                aria-label="Pengurus utama gereja">
                @foreach ($semua as $orang)
                    @if ($orang['bidang'] === 'Pengurus Utama')
                        <article class="structure-card" data-id="{{ $orang['id'] }}" role="button" tabindex="0"
                            aria-label="Lihat detail {{ $orang['nama'] }}">
                            <img src="{{ $orang['fotos'][0] }}" alt="Foto {{ $orang['nama'] }}" loading="lazy"
                                data-fallback="{{ $orang['fallback'] }}" onerror="this.onerror=null;this.src=this.dataset.fallback">
                            <span class="structure-badge">Pengurus Utama</span>
                            <div class="structure-overlay">
                                <p class="structure-role">{{ $orang['jabatan'] }}</p>
                                <h3 class="structure-name">{{ $orang['nama'] }}</h3>
                                <p class="structure-desc">{{ $orang['deskripsi'] }}</p>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="structure-all" id="structureAll">
            @foreach ($kelompok as $bidang => $anggota)
                <div class="structure-all-group">
                    <h3 class="structure-all-title">{{ $bidang }} <span>{{ count($anggota) }} orang</span></h3>
                    <div class="structure-grid">
                        @foreach ($anggota as $orang)
                            <article class="structure-card" data-id="{{ $orang['id'] }}" role="button" tabindex="0"
                                aria-label="Lihat detail {{ $orang['nama'] }}">
                                <img src="{{ $orang['fotos'][0] }}" alt="Foto {{ $orang['nama'] }}" loading="lazy"
                                data-fallback="{{ $orang['fallback'] }}" onerror="this.onerror=null;this.src=this.dataset.fallback">
                                <span class="structure-badge">{{ $bidang }}</span>
                                <div class="structure-overlay">
                                    <p class="structure-role">{{ $orang['jabatan'] }}</p>
                                    <h3 class="structure-name">{{ $orang['nama'] }}</h3>
                                    <p class="structure-desc">{{ $orang['deskripsi'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="structure-foot fade-in">
            <span class="structure-counter" id="structureCounter" aria-live="polite">
                Menampilkan {{ $jumlahUtama }} pengurus utama
            </span>
            @if ($totalDetail > 0)
                <button type="button" class="structure-more" id="structureToggle" aria-expanded="false">
                    <i class="ri-list-check" id="structureToggleIcon"></i>
                    <span id="structureToggleText">Lihat semua struktur (+{{ $totalDetail }})</span>
                </button>
            @endif
        </div>
    @endif
</section>

@if ($status === 'ok')
    <dialog class="person-dialog" id="personDialog" aria-labelledby="personName">
        <button type="button" class="structure-btn person-close" id="personClose" aria-label="Tutup">
            <i class="ri-close-line"></i>
        </button>

        <div class="person-media">
            <div class="person-slides" id="personSlides"></div>
            <div class="person-dots" id="personDots"></div>
            <span class="structure-badge" id="personBidang"></span>
            <div class="structure-overlay">
                <p class="structure-role" id="personRole"></p>
                <h3 class="structure-name" id="personName"></h3>
            </div>
        </div>

        <div class="person-info">
            <div>
                <h4>Tentang Pelayanan</h4>
                <p id="personDesc"></p>
            </div>
            <ul class="person-meta">
                <li><span>Jabatan</span><span id="personMetaRole"></span></li>
                <li><span>Bidang</span><span id="personMetaBidang"></span></li>
            </ul>
        </div>
    </dialog>

    <script>
        (function() {
            var data = @json($semuaMap);
            var total = {{ $jumlahUtama }};
            var totalAll = {{ count($semua) }};
            var totalDetail = {{ $totalDetail }};

            var section = document.getElementById('struktur');
            var track = document.getElementById('structureTrack');
            var carouselBox = document.getElementById('structureCarousel');
            var controlsBox = document.getElementById('structureControls');
            var allBox = document.getElementById('structureAll');
            var prev = document.getElementById('structurePrev');
            var next = document.getElementById('structureNext');
            var counter = document.getElementById('structureCounter');
            var toggle = document.getElementById('structureToggle');
            var toggleText = document.getElementById('structureToggleText');
            var toggleIcon = document.getElementById('structureToggleIcon');

            var dialog = document.getElementById('personDialog');
            var closeBtn = document.getElementById('personClose');
            var slidesBox = document.getElementById('personSlides');
            var dotsBox = document.getElementById('personDots');

            if (!track || !track.children.length) return;

            var showAll = false;
            var busy = false;

            function step() {
                var card = track.children[0];
                var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
                return card.getBoundingClientRect().width + gap;
            }

            function update() {
                if (showAll) return;
                var maxScroll = track.scrollWidth - track.clientWidth;
                prev.disabled = track.scrollLeft <= 4;
                next.disabled = track.scrollLeft >= maxScroll - 4;

                var perView = Math.max(1, Math.round(track.clientWidth / step()));
                var first = Math.round(track.scrollLeft / step()) + 1;
                var last = Math.min(total, first + perView - 1);
                counter.textContent = perView >= total ?
                    'Menampilkan ' + total + ' pengurus utama' :
                    'Menampilkan ' + first + '–' + last + ' dari ' + total + ' pengurus utama';
            }

            prev.addEventListener('click', function() { track.scrollBy({ left: -step() }); });
            next.addEventListener('click', function() { track.scrollBy({ left: step() }); });
            track.addEventListener('scroll', function() { window.requestAnimationFrame(update); }, { passive: true });
            track.addEventListener('keydown', function(e) {
                if (e.target !== track) return;
                if (e.key === 'ArrowRight') { e.preventDefault(); track.scrollBy({ left: step() }); }
                if (e.key === 'ArrowLeft') { e.preventDefault(); track.scrollBy({ left: -step() }); }
            });
            window.addEventListener('resize', update);

            function addClass(list, cls) { list.forEach(function(el) { el.classList.add(cls); }); }
            function removeClass(list, cls) { list.forEach(function(el) { el.classList.remove(cls); }); }

            if (toggle) {
                toggle.addEventListener('click', function() {
                    if (busy) return;
                    busy = true;

                    var leaving = showAll ? [allBox] : [carouselBox, controlsBox];
                    var entering = showAll ? [carouselBox, controlsBox] : [allBox];

                    addClass(leaving, 'anim-out');

                    setTimeout(function() {
                        removeClass(leaving, 'anim-out');
                        showAll = !showAll;
                        section.classList.toggle('is-all', showAll);
                        toggle.setAttribute('aria-expanded', showAll ? 'true' : 'false');

                        if (showAll) {
                            toggleText.textContent = 'Kembali ke pengurus utama';
                            toggleIcon.className = 'ri-layout-column-line';
                            counter.textContent = 'Menampilkan semua ' + totalAll + ' pelayan';
                        } else {
                            toggleText.textContent = 'Lihat semua struktur (+' + totalDetail + ')';
                            toggleIcon.className = 'ri-list-check';
                            track.scrollLeft = 0;
                            update();
                        }

                        addClass(entering, 'anim-in');
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });

                        setTimeout(function() {
                            removeClass(entering, 'anim-in');
                            busy = false;
                        }, 650);
                    }, 260);
                });
            }

            var effects = ['fx-zoom', 'fx-slide', 'fx-rotate'];
            var timer = null;
            var current = 0;
            var slideEls = [];
            var dotEls = [];

            function show(index) {
                current = index;
                slideEls.forEach(function(el, i) { el.classList.toggle('is-active', i === index); });
                dotEls.forEach(function(el, i) {
                    el.classList.remove('is-active', 'is-done');
                    if (i < index) el.classList.add('is-done');
                    if (i === index) { void el.offsetWidth; el.classList.add('is-active'); }
                });
            }

            function stop() {
                if (timer) { clearInterval(timer); timer = null; }
            }

            function start() {
                stop();
                if (slideEls.length < 2) return;
                timer = setInterval(function() { show((current + 1) % slideEls.length); }, 3000);
            }

            function openDetail(id) {
                var p = data[id];
                if (!p) return;

                slidesBox.innerHTML = '';
                dotsBox.innerHTML = '';
                slideEls = [];
                dotEls = [];

                p.fotos.slice(0, 3).forEach(function(src, i) {
                    var img = document.createElement('img');
                    img.className = 'person-slide ' + effects[i % effects.length];
                    img.onerror = function() { this.onerror = null; if (p.fallback) this.src = p.fallback; };
                    img.src = src;
                    img.alt = 'Foto ' + p.nama + ' ' + (i + 1);
                    slidesBox.appendChild(img);
                    slideEls.push(img);

                    var dot = document.createElement('span');
                    dot.className = 'person-dot';
                    dotsBox.appendChild(dot);
                    dotEls.push(dot);
                });

                document.getElementById('personName').textContent = p.nama;
                document.getElementById('personRole').textContent = p.jabatan;
                document.getElementById('personBidang').textContent = p.bidang;
                document.getElementById('personDesc').textContent = p.deskripsi;
                document.getElementById('personMetaRole').textContent = p.jabatan;
                document.getElementById('personMetaBidang').textContent = p.bidang;

                dialog.classList.remove('is-closing');
                dialog.showModal();
                dialog.scrollTop = 0;
                show(0);
                start();
            }

            function closeDetail() {
                if (!dialog.open || dialog.classList.contains('is-closing')) return;
                dialog.classList.add('is-closing');
                stop();
                setTimeout(function() {
                    dialog.classList.remove('is-closing');
                    dialog.close();
                }, 260);
            }

            section.addEventListener('click', function(e) {
                var card = e.target.closest('.structure-card');
                if (card && card.dataset.id) openDetail(card.dataset.id);
            });

            section.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var card = e.target.closest('.structure-card');
                if (card && card.dataset.id) { e.preventDefault(); openDetail(card.dataset.id); }
            });

            closeBtn.addEventListener('click', closeDetail);
            dialog.addEventListener('click', function(e) { if (e.target === dialog) closeDetail(); });
            dialog.addEventListener('cancel', function(e) { e.preventDefault(); closeDetail(); });
            dialog.addEventListener('close', stop);

            update();
        })();
    </script>
@endif