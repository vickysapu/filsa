@php
    $strukturUtama = [
        [
            'nama' => 'Pdt. Hendra Setiawan, M.Th',
            'jabatan' => 'Gembala Sidang',
            'deskripsi' => 'Memimpin arah rohani jemaat, pengajaran firman, dan pemuridan.',
        ],
        [
            'nama' => 'Pdt. Yosef Wibowo, S.Th',
            'jabatan' => 'Wakil Gembala Sidang',
            'deskripsi' => 'Mendampingi gembala sidang dan mengoordinasi pelayanan pastoral.',
        ],
        [
            'nama' => 'Bp. Daniel Prasetyo',
            'jabatan' => 'Ketua Majelis Jemaat',
            'deskripsi' => 'Mengawasi operasional dan tata kelola organisasi gereja.',
        ],
        [
            'nama' => 'Ibu Ruth Handayani',
            'jabatan' => 'Sekretaris Majelis',
            'deskripsi' => 'Mengelola administrasi, data jemaat, dan arsip gereja.',
        ],
        [
            'nama' => 'Bp. Samuel Kurniawan',
            'jabatan' => 'Bendahara Majelis',
            'deskripsi' => 'Mengelola keuangan gereja secara akuntabel dan transparan.',
        ],
    ];

    $strukturDetail = [
        'Majelis Jemaat' => [
            ['nama' => 'Bp. Andreas Lim', 'jabatan' => 'Anggota Majelis'],
            ['nama' => 'Ibu Debora Santoso', 'jabatan' => 'Anggota Majelis'],
            ['nama' => 'Bp. Markus Tanujaya', 'jabatan' => 'Anggota Majelis'],
        ],
        'Ibadah & Musik' => [
            ['nama' => 'Bp. Yohanes Pratama', 'jabatan' => 'Koordinator Ibadah'],
            ['nama' => 'Sdri. Priscilla Wong', 'jabatan' => 'Pemimpin Pujian'],
            ['nama' => 'Bp. Timotius Gunawan', 'jabatan' => 'Koordinator Musik'],
        ],
        'Multimedia & Siaran' => [
            ['nama' => 'Bp. Kevin Alamsyah', 'jabatan' => 'Koordinator Multimedia'],
            ['nama' => 'Sdr. Nathan Hartono', 'jabatan' => 'Operator Live Streaming'],
        ],
        'Pelayanan Kategorial' => [
            ['nama' => 'Pdm. Maria Setiawan, S.Psi', 'jabatan' => 'Keluarga & Konseling'],
            ['nama' => 'Sdr. Gideon Mulyadi', 'jabatan' => 'Ketua Pemuda & Remaja'],
            ['nama' => 'Ibu Lidya Permata', 'jabatan' => 'Koordinator Sekolah Minggu'],
            ['nama' => 'Ibu Naomi Kusuma', 'jabatan' => 'Ketua Persekutuan Wanita'],
        ],
        'Diakonia & Misi' => [
            ['nama' => 'Bp. Petrus Sutanto', 'jabatan' => 'Koordinator Diakonia'],
            ['nama' => 'Ibu Hana Wijaya', 'jabatan' => 'Koordinator Misi & Penginjilan'],
            ['nama' => 'Bp. Elia Nugraha', 'jabatan' => 'Koordinator Doa'],
        ],
    ];

    $warnaLatar = ['b6e3f4', 'c0aede', 'd1d4f9', 'ffd5dc', 'ffdfbf', 'c7f0d8'];

    // 3 gaya ekspresi untuk karakter yang sama: riang, santai/oke, semangat
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

    $semua = [];
    $no = 0;

    foreach ($strukturUtama as $orang) {
        $no++;
        $id = 'p' . $no;
        $semua[] = [
            'id' => $id,
            'nama' => $orang['nama'],
            'jabatan' => $orang['jabatan'],
            'bidang' => 'Pengurus Utama',
            'deskripsi' => $orang['deskripsi'],
            'fotos' => $buatFoto($orang['nama'], $no),
        ];
    }

    foreach ($strukturDetail as $bidang => $anggota) {
        foreach ($anggota as $orang) {
            $no++;
            $id = 'p' . $no;
            $semua[] = [
                'id' => $id,
                'nama' => $orang['nama'],
                'jabatan' => $orang['jabatan'],
                'bidang' => $bidang,
                'deskripsi' => 'Melayani di bidang ' . $bidang . ' sebagai ' . $orang['jabatan'] . '.',
                'fotos' => $buatFoto($orang['nama'], $no),
            ];
        }
    }

    $totalDetail = count($semua) - count($strukturUtama);
    $kelompok = collect($semua)->groupBy('bidang');
    $semuaMap = collect($semua)->keyBy('id');
@endphp

<style>
    .structure-section {
        padding-top: 2rem;
    }

    .structure-head {
        max-width: 980px;
        margin: 0 auto 2rem;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .structure-head-text {
        max-width: 560px;
    }

    .structure-controls {
        display: flex;
        gap: 0.5rem;
    }

    .structure-btn {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zaitun-border);
        border-radius: 2px;
        background: transparent;
        color: var(--zaitun-deep-dark);
        font-size: 1.3rem;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, border-color 0.2s;
    }

    .structure-btn:hover:not(:disabled) {
        background: var(--zaitun-deep-dark);
        border-color: var(--zaitun-deep-dark);
        color: var(--zaitun-sand);
    }

    .structure-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .structure-btn:focus-visible,
    .structure-more:focus-visible,
    .structure-track:focus-visible,
    .structure-card:focus-visible {
        outline: 2px solid var(--zaitun-primary);
        outline-offset: 3px;
    }

    .structure-carousel {
        max-width: 980px;
        margin: 0 auto;
    }

    .structure-track {
        --gap: 1.25rem;
        display: flex;
        gap: var(--gap);
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .structure-track::-webkit-scrollbar {
        display: none;
    }

    .structure-card {
        position: relative;
        flex: 0 0 calc((100% - 2 * var(--gap)) / 3);
        scroll-snap-align: start;
        aspect-ratio: 3 / 4.2;
        border-radius: 2px;
        overflow: hidden;
        background: var(--zaitun-deep-dark);
        isolation: isolate;
        cursor: pointer;
    }

    .structure-card img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 30%;
        display: block;
        z-index: -2;
        transition: transform 0.6s ease;
    }

    .structure-card:hover img {
        transform: scale(1.05);
    }

    .structure-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        padding: 0.4rem 0.75rem;
        background: rgba(30, 38, 25, 0.55);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border-radius: 2px;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        z-index: 3;
    }

    .structure-overlay {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 6rem 1.25rem 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.35rem;
        text-align: right;
        color: #fff;
        background: linear-gradient(to top,
                rgba(30, 38, 25, 0.95) 0%,
                rgba(30, 38, 25, 0.8) 35%,
                rgba(30, 38, 25, 0.4) 70%,
                rgba(30, 38, 25, 0) 100%);
        z-index: 2;
    }

    .structure-role {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.75);
    }

    .structure-name {
        margin: 0;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.35rem;
        font-weight: 500;
        line-height: 1.25;
        color: #fff;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
    }

    .structure-desc {
        margin: 0.35rem 0 0;
        font-size: 0.85rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.82);
    }

    .structure-all {
        display: none;
        max-width: 980px;
        margin: 0 auto;
    }

    .is-all .structure-all {
        display: block;
    }

    .is-all .structure-carousel,
    .is-all .structure-controls {
        display: none;
    }

    .anim-out {
        animation: view-out 0.26s ease-in forwards;
    }

    .anim-in {
        animation: view-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .anim-in .structure-card {
        animation: card-in 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .anim-in .structure-card:nth-child(1) {
        animation-delay: 0.04s;
    }

    .anim-in .structure-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .anim-in .structure-card:nth-child(3) {
        animation-delay: 0.16s;
    }

    .anim-in .structure-card:nth-child(4) {
        animation-delay: 0.22s;
    }

    .anim-in .structure-card:nth-child(n+5) {
        animation-delay: 0.28s;
    }

    @keyframes view-out {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(14px);
        }
    }

    @keyframes view-in {
        from {
            opacity: 0;
            transform: translateY(18px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes card-in {
        from {
            opacity: 0;
            transform: translateY(26px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .structure-all-group {
        margin-bottom: 2.5rem;
    }

    .structure-all-title {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        margin: 0 0 1rem;
        padding-bottom: 0.6rem;
        border-bottom: 1px solid var(--zaitun-border);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--zaitun-primary);
    }

    .structure-all-title span {
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--zaitun-text-secondary);
    }

    .structure-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    .structure-grid .structure-card {
        flex: none;
        aspect-ratio: 3 / 4;
    }

    .structure-grid .structure-desc {
        display: none;
    }

    .structure-grid .structure-overlay {
        padding-top: 4.5rem;
    }

    .structure-foot {
        max-width: 980px;
        margin: 1.75rem auto 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .structure-counter {
        font-size: 0.9rem;
        color: var(--zaitun-text-secondary);
    }

    .structure-more {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.4rem;
        border: 1px solid var(--zaitun-deep-dark);
        border-radius: 2px;
        background: transparent;
        color: var(--zaitun-deep-dark);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    .structure-more:hover {
        background: var(--zaitun-deep-dark);
        color: var(--zaitun-sand);
    }

    .person-dialog {
        position: fixed;
        inset: 0;
        margin: auto;
        width: min(1100px, calc(100% - 2rem));
        height: min(740px, calc(100vh - 2rem));
        max-width: none;
        max-height: none;
        padding: 0;
        border: 0;
        border-radius: 2px;
        background: var(--zaitun-sand);
        color: var(--zaitun-deep-dark);
        overflow: hidden;
    }

    .person-dialog[open] {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        animation: dialog-in 0.38s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .person-dialog[open]::backdrop {
        background: rgba(30, 38, 25, 0.68);
        animation: backdrop-in 0.38s ease both;
    }

    .person-dialog.is-closing {
        animation: dialog-out 0.26s ease-in forwards;
    }

    .person-dialog.is-closing::backdrop {
        animation: backdrop-out 0.26s ease-in forwards;
    }

    @keyframes dialog-in {
        from {
            opacity: 0;
            transform: translateY(28px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes dialog-out {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        to {
            opacity: 0;
            transform: translateY(20px) scale(0.96);
        }
    }

    @keyframes backdrop-in {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes backdrop-out {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    .person-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 6;
        background: var(--zaitun-sand);
    }

    .person-media {
        position: relative;
        height: 100%;
        min-height: 0;
        background: var(--zaitun-deep-dark);
        overflow: hidden;
        isolation: isolate;
    }

    .person-slides {
        position: absolute;
        inset: 0;
        z-index: 0;
    }

    .person-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 30%;
        opacity: 0;
        transition: opacity 0.9s ease, transform 1.4s ease;
    }

    .person-slide.fx-zoom {
        transform: scale(1.25);
    }

    .person-slide.fx-slide {
        transform: translateX(12%);
    }

    .person-slide.fx-rotate {
        transform: scale(1.18) rotate(2.5deg);
    }

    .person-slide.is-active {
        opacity: 1;
        transform: none;
        z-index: 1;
    }

    .person-dots {
        position: absolute;
        top: 1rem;
        left: 1rem;
        right: 1rem;
        z-index: 4;
        display: flex;
        gap: 0.4rem;
    }

    .person-dot {
        position: relative;
        flex: 1;
        height: 3px;
        background: rgba(255, 255, 255, 0.35);
        border-radius: 2px;
        overflow: hidden;
    }

    .person-dot::after {
        content: '';
        position: absolute;
        inset: 0;
        background: #fff;
        transform: scaleX(0);
        transform-origin: left;
    }

    .person-dot.is-done::after {
        transform: scaleX(1);
    }

    .person-dot.is-active::after {
        animation: person-fill 3s linear forwards;
    }

    @keyframes person-fill {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
    }

    .person-media .structure-badge {
        top: 2rem;
    }

    .person-media .structure-name {
        font-size: 1.75rem;
    }

    .person-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1.25rem;
        padding: 3.5rem 2rem 2rem;
        overflow-y: auto;
    }

    .person-dialog[open] .person-info>* {
        animation: info-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .person-dialog[open] .person-info>*:nth-child(2) {
        animation-delay: 0.1s;
    }

    @keyframes info-in {
        from {
            opacity: 0;
            transform: translateX(16px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .person-info h4 {
        margin: 0 0 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--zaitun-text-secondary);
    }

    .person-info p {
        margin: 0;
        font-size: 1rem;
        line-height: 1.7;
        color: var(--zaitun-deep-dark);
    }

    .person-meta {
        margin: 0;
        padding: 0;
        list-style: none;
        border-top: 1px solid var(--zaitun-border);
    }

    .person-meta li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--zaitun-border);
        font-size: 0.92rem;
    }

    .person-meta li span:first-child {
        color: var(--zaitun-text-secondary);
    }

    .person-meta li span:last-child {
        font-weight: 600;
        text-align: right;
    }

    @media (max-width: 900px) {
        .structure-card {
            flex-basis: calc((100% - var(--gap)) / 2);
        }

        .structure-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .person-dialog[open] {
            grid-template-columns: 1.2fr 1fr;
        }
    }

    @media (max-width: 720px) {
        .person-dialog {
            height: auto;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }

        .person-dialog[open] {
            grid-template-columns: 1fr;
        }

        .person-media {
            height: 68vh;
            min-height: 380px;
        }

        .person-info {
            padding: 1.5rem 1.25rem 1.75rem;
        }
    }

    @media (max-width: 560px) {
        .structure-card {
            flex-basis: 85%;
        }

        .structure-grid {
            grid-template-columns: 1fr;
        }

        .structure-grid .structure-card {
            aspect-ratio: 4 / 5;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .structure-track {
            scroll-behavior: auto;
        }

        .structure-card img,
        .person-slide {
            transition: none;
        }

        .person-dialog[open],
        .person-dialog[open]::backdrop,
        .person-dialog[open] .person-info>*,
        .person-dialog.is-closing,
        .person-dialog.is-closing::backdrop,
        .anim-in,
        .anim-out,
        .anim-in .structure-card {
            animation-duration: 0.01s;
            animation-delay: 0s;
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
                Lima pengurus utama yang memimpin pelayanan. Pengurus bidang lainnya ada di struktur lengkap.
            </p>
        </div>

        <div class="structure-controls" id="structureControls" role="group"
            aria-label="Navigasi carousel struktur gereja">
            <button type="button" class="structure-btn" id="structurePrev" aria-label="Sebelumnya">
                <i class="ri-arrow-left-s-line"></i>
            </button>
            <button type="button" class="structure-btn" id="structureNext" aria-label="Berikutnya">
                <i class="ri-arrow-right-s-line"></i>
            </button>
        </div>
    </div>

    <div class="structure-carousel fade-in" id="structureCarousel">
        <div class="structure-track" id="structureTrack" tabindex="0" role="region"
            aria-label="Pengurus utama gereja">
            @foreach ($semua as $orang)
                @if ($orang['bidang'] === 'Pengurus Utama')
                    <article class="structure-card" data-id="{{ $orang['id'] }}" role="button" tabindex="0"
                        aria-label="Lihat detail {{ $orang['nama'] }}">
                        <img src="{{ $orang['fotos'][0] }}" alt="Karakter {{ $orang['nama'] }}" loading="lazy">
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
                            <img src="{{ $orang['fotos'][0] }}" alt="Karakter {{ $orang['nama'] }}" loading="lazy">
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
            Menampilkan {{ count($strukturUtama) }} pengurus utama
        </span>
        <button type="button" class="structure-more" id="structureToggle" aria-expanded="false">
            <i class="ri-list-check" id="structureToggleIcon"></i>
            <span id="structureToggleText">Lihat semua struktur (+{{ $totalDetail }})</span>
        </button>
    </div>
</section>

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
        var total = {{ count($strukturUtama) }};
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

        if (!track) return;

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

        prev.addEventListener('click', function() {
            track.scrollBy({
                left: -step()
            });
        });
        next.addEventListener('click', function() {
            track.scrollBy({
                left: step()
            });
        });
        track.addEventListener('scroll', function() {
            window.requestAnimationFrame(update);
        }, {
            passive: true
        });
        track.addEventListener('keydown', function(e) {
            if (e.target !== track) return;
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                track.scrollBy({
                    left: step()
                });
            }
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                track.scrollBy({
                    left: -step()
                });
            }
        });
        window.addEventListener('resize', update);

        function addClass(list, cls) {
            list.forEach(function(el) {
                el.classList.add(cls);
            });
        }

        function removeClass(list, cls) {
            list.forEach(function(el) {
                el.classList.remove(cls);
            });
        }

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
                section.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                setTimeout(function() {
                    removeClass(entering, 'anim-in');
                    busy = false;
                }, 650);
            }, 260);
        });

        var effects = ['fx-zoom', 'fx-slide', 'fx-rotate'];
        var timer = null;
        var current = 0;
        var slideEls = [];
        var dotEls = [];

        function show(index) {
            current = index;
            slideEls.forEach(function(el, i) {
                el.classList.toggle('is-active', i === index);
            });
            dotEls.forEach(function(el, i) {
                el.classList.remove('is-active', 'is-done');
                if (i < index) el.classList.add('is-done');
                if (i === index) {
                    void el.offsetWidth;
                    el.classList.add('is-active');
                }
            });
        }

        function stop() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function start() {
            stop();
            if (slideEls.length < 2) return;
            timer = setInterval(function() {
                show((current + 1) % slideEls.length);
            }, 3000);
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
                img.src = src;
                img.alt = 'Karakter ' + p.nama + ' gaya ' + (i + 1);
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
            if (card && card.dataset.id) {
                e.preventDefault();
                openDetail(card.dataset.id);
            }
        });

        closeBtn.addEventListener('click', closeDetail);
        dialog.addEventListener('click', function(e) {
            if (e.target === dialog) closeDetail();
        });
        dialog.addEventListener('cancel', function(e) {
            e.preventDefault();
            closeDetail();
        });
        dialog.addEventListener('close', stop);

        update();
    })();
</script>
