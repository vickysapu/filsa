@php
    $exportTypes = $exportTypes ?? \App\Http\Controllers\FinancialExportController::availableTypes();
    $exportFields = $exportFields ?? \App\Http\Controllers\FinancialExportController::fieldDefs();
    $exportIsAdmin = $exportIsAdmin ?? \App\Http\Controllers\FinancialExportController::isAdmin();
@endphp

<div class="dropdown">
    <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ri-download-2-line me-1"></i> Export
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($exportTypes as $key => $type)
            <li>
                <a class="dropdown-item" href="#" data-export-type="{{ $key }}"
                    @if (!empty($type['modal'])) data-modal="1" @endif>
                    <i class="{{ $type['icon'] }} me-2"></i>{{ $type['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

<form id="exportRunForm" method="GET" action="{{ url('admin/finance/export/run') }}" class="d-none">
    <input type="hidden" name="type">
    <input type="hidden" name="template_id">
    <input type="hidden" name="year" value="{{ request('year') }}">
    <input type="hidden" name="month" value="{{ request('month') }}">
    <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
    <input type="hidden" name="church_id" value="{{ request('church_id') }}">
</form>

<style>
    /* ===== layar terbagi: kiri = menyusun, kanan = panduan ===== */
    #exportTemplateModal .modal-body {
        padding: 0;
        overflow: hidden;
        min-height: 0;
    }

    .tb-split {
        display: flex;
        height: 100%;
        min-height: 0;
    }

    .tb-left {
        flex: 1 1 0;
        min-width: 0;
        overflow: auto;
        padding: 16px 20px 40px;
    }

    .tb-gutter {
        flex: none;
        width: 7px;
        cursor: col-resize;
        background: var(--bs-border-color);
        opacity: .5;
    }

    .tb-gutter:hover,
    .tb-gutter.drag {
        opacity: 1;
        background: rgba(var(--bs-primary-rgb), .5);
    }

    .tb-guide {
        flex: none;
        width: var(--gw, 420px);
        overflow: auto;
        padding: 16px;
        background: rgba(var(--bs-secondary-rgb), .05);
    }

    .tb-split.no-guide .tb-gutter,
    .tb-split.no-guide .tb-guide {
        display: none;
    }

    @media (max-width: 991.98px) {
        #exportTemplateModal .modal-body {
            overflow: auto;
        }

        .tb-split {
            flex-direction: column;
            height: auto;
        }

        .tb-left,
        .tb-guide {
            overflow: visible;
            width: auto;
        }

        .tb-gutter {
            display: none;
        }
    }

    /* ===== palet ===== */
    .tb-palbar {
        position: sticky;
        top: -16px;
        z-index: 5;
        background: var(--bs-body-bg);
        padding: 10px 0 8px;
        margin: 0 0 12px;
        border-bottom: 1px solid var(--bs-border-color);
    }

    .dnd-pal {
        min-height: 34px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 6px;
    }

    .dnd-chip {
        cursor: grab;
        padding: 3px 11px;
        border-radius: 999px;
        font-size: .8rem;
        user-select: none;
        background: rgba(var(--bs-primary-rgb), .1);
        border: 1px solid rgba(var(--bs-primary-rgb), .25);
    }

    .dnd-chip:hover {
        background: rgba(var(--bs-primary-rgb), .2);
    }

    .dnd-chip.val {
        background: rgba(var(--bs-success-rgb), .1);
        border-color: rgba(var(--bs-success-rgb), .3);
    }

    .dnd-chip.val:hover {
        background: rgba(var(--bs-success-rgb), .22);
    }

    .dnd-chip.misc {
        background: rgba(var(--bs-secondary-rgb), .1);
        border-color: rgba(var(--bs-secondary-rgb), .3);
    }

    .dnd-chip.pal-off {
        opacity: .35;
    }

    /* ===== kanvas tabel ===== */
    .tb-sheet {
        position: relative;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
    }

    .tb-lines .tb-line {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
        border-radius: 6px;
    }

    .tb-lines .tb-line:hover {
        background: rgba(var(--bs-primary-rgb), .05);
    }

    .tb-linein {
        flex: 1;
        min-width: 0;
        border: 0;
        background: transparent;
        padding: 4px 8px;
        border-radius: 6px;
        outline: none;
        color: inherit;
    }

    .tb-linein:focus {
        background: rgba(var(--bs-primary-rgb), .08);
    }

    #hdrLines .tb-linein {
        text-align: center;
    }

    #hdrLines .tb-line:first-child .tb-linein {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .tb-grip {
        cursor: grab;
        color: var(--bs-secondary-color);
        font-size: .95rem;
        flex: none;
    }

    .tb-x {
        border: 0;
        background: transparent;
        color: var(--bs-secondary-color);
        font-size: 1rem;
        line-height: 1;
        padding: 0 5px;
        border-radius: 4px;
        cursor: pointer;
        flex: none;
    }

    .tb-x:hover {
        color: #fff;
        background: var(--bs-danger);
    }

    .tb-ic {
        border: 0;
        background: transparent;
        cursor: pointer;
        font-size: .85rem;
        padding: 0 3px;
        flex: none;
    }

    .tb-ic:hover {
        background: rgba(var(--bs-primary-rgb), .15);
        border-radius: 4px;
    }

    .tb-wrap {
        overflow-x: auto;
        padding-bottom: 6px;
    }

    table.tb {
        border-collapse: collapse;
        font-size: .82rem;
    }

    table.tb.fixed {
        table-layout: fixed;
    }

    table.tb th,
    table.tb td {
        border: 1px solid var(--bs-border-color);
        padding: 4px 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    table.tb th.tb-h {
        background: rgba(169, 208, 142, .45);
        position: relative;
        vertical-align: top;
        text-align: left;
    }

    table.tb th.tb-h.tb-val {
        background: rgba(169, 208, 142, .6);
    }

    table.tb th.tb-fix {
        background: rgba(169, 208, 142, .45);
    }

    table.tb th.tb-p {
        background: rgba(169, 208, 142, .3);
        font-weight: 600;
        text-align: center;
    }

    table.tb th.tb-p.sub {
        background: rgba(255, 224, 130, .5);
    }

    table.tb th.tb-p.total {
        background: rgba(120, 190, 120, .45);
    }

    table.tb th.tb-add {
        background: transparent;
        border-style: dashed;
    }

    table.tb th.tb-drop {
        background: transparent;
        border: 2px dashed var(--bs-border-color);
        color: var(--bs-secondary-color);
        font-weight: 400;
        min-width: 260px;
    }

    .tb-hin {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .tb-lab {
        border: 0;
        background: transparent;
        font-weight: 600;
        width: 100%;
        min-width: 0;
        outline: none;
        color: inherit;
        border-radius: 4px;
        padding: 1px 4px;
    }

    .tb-lab:focus {
        background: rgba(255, 255, 255, .55);
        box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), .3);
    }

    .tb-st {
        font-weight: 600;
        flex: 1;
    }

    .tb-sub {
        font-size: .68rem;
        color: var(--bs-secondary-color);
        font-weight: 400;
        padding-left: 18px;
    }

    .resizer {
        position: absolute;
        right: -3px;
        top: 0;
        bottom: 0;
        width: 7px;
        cursor: col-resize;
        z-index: 2;
    }

    .resizer:hover {
        background: rgba(var(--bs-primary-rgb), .35);
    }

    table.tb td.num {
        text-align: right;
    }

    table.tb td.alt {
        background: rgba(255, 224, 130, .18);
    }

    table.tb td.pad {
        width: 70px;
        border: 0;
        text-align: left;
    }

    table.tb tr.smp td,
    table.tb tr.subrow td {
        color: var(--bs-secondary-color);
    }

    table.tb tr.subrow td {
        background: rgba(0, 0, 0, .04);
        font-weight: 600;
    }

    table.tb td.tot {
        background: rgba(255, 255, 0, .35);
        font-weight: 700;
        color: inherit;
    }

    table.tb tr.tb-addtot td,
    table.tb tr.tb-addrow td {
        border: 1px dashed var(--bs-border-color);
        text-align: left;
    }

    table.tb tbody.hgrp td:not(.tot) {
        color: var(--bs-secondary-color);
    }

    .tb-addsel {
        font-size: .75rem;
        min-width: 110px;
        display: inline-block;
        width: auto;
    }

    .tb-ghost {
        opacity: .4;
    }

    .tb-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 16px;
        align-items: center;
        margin: 10px 0 16px;
        font-size: .8rem;
    }

    .tb-strip select {
        width: auto;
    }

    .tb-foot-title {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--bs-secondary-color);
        margin-bottom: 2px;
    }

    .tb-pop {
        position: absolute;
        z-index: 20;
        width: 250px;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
    }

    .tb-pop label {
        font-size: .72rem;
        color: var(--bs-secondary-color);
        margin: 6px 0 2px;
        display: block;
    }

    /* ===== pratinjau hasil (hanya-baca) ===== */
    .tb-ro .tb-grip,
    .tb-ro .tb-x,
    .tb-ro .tb-ic,
    .tb-ro .resizer,
    .tb-ro .tb-sub,
    .tb-ro th.tb-add,
    .tb-ro td.pad,
    .tb-ro tr.tb-addtot,
    .tb-ro tr.tb-addrow,
    .tb-ro tfoot,
    .tb-ro .tb-drop {
        display: none !important;
    }

    .tb-ro .tb-lab {
        pointer-events: none;
        text-align: inherit;
    }

    .tb-ro table.tb.fixed {
        width: auto !important;
        table-layout: auto;
    }

    .tb-pv-h {
        text-align: center;
        margin-bottom: 10px;
    }

    .tb-pv-h div:first-child {
        font-weight: 700;
        font-size: 1.05rem;
    }

    .tb-pv-f {
        margin-top: 10px;
        font-size: .8rem;
        color: var(--bs-secondary-color);
    }

    .tb-live {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--bs-success);
        margin-right: 6px;
    }

    /* ===== panduan ===== */
    .gd-sec {
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        margin-bottom: 8px;
        background: var(--bs-body-bg);
    }

    .gd-sec>summary {
        cursor: pointer;
        padding: 8px 12px;
        font-weight: 600;
        font-size: .88rem;
        list-style: none;
    }

    .gd-sec>summary::-webkit-details-marker {
        display: none;
    }

    .gd-sec>summary::before {
        content: '▸';
        display: inline-block;
        width: 1.1em;
        transition: transform .15s;
    }

    .gd-sec[open]>summary::before {
        transform: rotate(90deg);
    }

    .gd-body {
        padding: 2px 12px 12px;
        font-size: .82rem;
        line-height: 1.5;
    }

    .gd-body table {
        width: 100%;
        font-size: .78rem;
        margin: 6px 0;
        border-collapse: collapse;
    }

    .gd-body td,
    .gd-body th {
        border: 1px solid var(--bs-border-color);
        padding: 3px 6px;
        vertical-align: top;
        text-align: left;
    }

    .gd-body th {
        background: rgba(var(--bs-secondary-rgb), .08);
    }

    .gd-body ol,
    .gd-body ul {
        padding-left: 1.2rem;
        margin-bottom: 6px;
    }

    .gd-code {
        background: rgba(var(--bs-secondary-rgb), .14);
        padding: 0 4px;
        border-radius: 4px;
        font-size: .75rem;
        font-family: var(--bs-font-monospace);
    }

    .gd-chip {
        border: 1px solid rgba(var(--bs-primary-rgb), .3);
        background: rgba(var(--bs-primary-rgb), .08);
        border-radius: 999px;
        padding: 2px 9px;
        font-size: .76rem;
        margin: 0 4px 4px 0;
        cursor: pointer;
        color: inherit;
    }

    .gd-chip.val {
        border-color: rgba(var(--bs-success-rgb), .35);
        background: rgba(var(--bs-success-rgb), .1);
    }

    .gd-chip:hover {
        filter: brightness(.95);
        box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), .2);
    }

    .gd-chip code {
        font-size: .66rem;
        margin-left: 4px;
        opacity: .6;
    }

    .gd-hide {
        display: none !important;
    }

    /* ===== v2: kartu, tile pilihan, kerja berdampingan ===== */
    .tb-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 12px 16px;
    }

    .tb-tiles {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tb-tile {
        display: flex;
        align-items: center;
        gap: 10px;
        text-align: left;
        padding: 8px 14px 8px 12px;
        min-width: 190px;
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        background: var(--bs-body-bg);
        color: inherit;
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }

    .tb-tile i {
        font-size: 1.45rem;
        color: var(--bs-secondary-color);
    }

    .tb-tile b {
        display: block;
        font-size: .85rem;
        font-weight: 600;
    }

    .tb-tile small {
        display: block;
        font-size: .72rem;
        line-height: 1.25;
        color: var(--bs-secondary-color);
    }

    .tb-tile:hover {
        border-color: rgba(var(--bs-primary-rgb), .5);
    }

    .tb-tile.active {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), .08);
        box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), .15);
    }

    .tb-tile.active i {
        color: var(--bs-primary);
    }

    .tb-hist .btn {
        padding: 3px 10px;
    }

    .tb-work {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
    }

    .tb-work.side {
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        align-items: start;
    }

    .tb-work.side .tb-prev {
        position: sticky;
        top: 140px;
        max-height: calc(100vh - 220px);
        overflow: auto;
    }

    @media (max-width: 1199.98px) {
        .tb-work.side {
            grid-template-columns: minmax(0, 1fr);
        }

        .tb-work.side .tb-prev {
            position: static;
            max-height: none;
        }
    }

    .dnd-chip.pal-used {
        opacity: .55;
    }

    .dnd-chip.pal-used::after {
        content: ' ✓';
        color: var(--bs-success);
    }

    /* ===== tabel tegak yang rapi ===== */
    table.tb td.num {
        font-variant-numeric: tabular-nums;
    }

    table.tb.vt {
        table-layout: fixed;
        width: 100%;
        min-width: 640px;
    }

    table.tb.vt th,
    table.tb.vt td {
        height: 42px;
        vertical-align: middle;
    }

    table.tb.vt th.tb-h {
        padding: 2px 8px;
    }

    table.tb.vt .tb-hin {
        gap: 6px;
    }

    table.tb.vt tr.hrow td {
        color: inherit;
    }

    table.tb.vt tr.hrow:nth-child(even) td:not(.tot) {
        background: rgba(0, 0, 0, .025);
    }

    table.tb.vt tr.hrow:hover td:not(.tot) {
        background: rgba(var(--bs-primary-rgb), .06);
    }

    table.tb.vt td.tot.dash {
        color: var(--bs-secondary-color);
        text-align: center;
        font-weight: 400;
        background: rgba(255, 255, 0, .12);
    }

    table.tb tbody tr.smp:hover td:not(.tot) {
        background: rgba(var(--bs-primary-rgb), .06);
    }

    .tb-tag {
        flex: none;
        max-width: 96px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: .66rem;
        color: var(--bs-secondary-color);
        background: rgba(127, 127, 127, .16);
        border-radius: 999px;
        padding: 1px 8px;
    }

    .tb-ro .tb-tag {
        display: none;
    }
</style>

<div class="modal fade" id="exportTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content modal-content-custom">
            <div class="modal-header border-bottom border-custom py-2">
                <h5 class="modal-title fw-semibold"><i class="ri-layout-grid-line me-2"></i>Template Export</h5>
                <button type="button" class="btn btn-sm btn-outline-custom ms-auto me-3" id="btnGuide"
                    title="Tampilkan / sembunyikan panduan di sebelah kanan">
                    <i class="ri-book-open-line me-1"></i> Panduan
                </button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="tb-split" id="tbSplit">

                    {{-- ================= KIRI: MENYUSUN ================= --}}
                    <div class="tb-left">
                        <div class="tb-card mb-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted-custom">Template
                                        tersimpan</label>
                                    <select id="tplSelect" class="form-select form-control-custom"></select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-muted-custom">Nama template <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="tplName" class="form-control form-control-custom"
                                        maxlength="100" placeholder="mis. Rekap PP per jemaat">
                                </div>
                                <div class="col-md-3" id="sharedWrap">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="tplShared">
                                        <label class="form-check-label small" for="tplShared">Bagikan ke semua
                                            branch</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tb-card mb-3">
                            <div class="d-flex flex-wrap gap-4 align-items-start">
                                <div>
                                    <div class="tb-foot-title mb-1">Isi tabel</div>
                                    <div class="tb-tiles">
                                        <button type="button" class="tb-tile" data-mode="detail"><i
                                                class="ri-list-check-2"></i><span><b>Daftar transaksi</b><small>Satu
                                                    baris = satu transaksi</small></span></button>
                                        <button type="button" class="tb-tile" data-mode="summary"><i
                                                class="ri-table-line"></i><span><b>Rekap per
                                                    periode</b><small>Angka dijumlahkan per bulan, kuartal,
                                                    dst</small></span></button>
                                    </div>
                                </div>
                                <div>
                                    <div class="tb-foot-title mb-1">Arah tabel</div>
                                    <div class="tb-tiles">
                                        <button type="button" class="tb-tile" data-orient="horizontal"><i
                                                class="ri-layout-top-line"></i><span><b>Mendatar</b><small>Judul
                                                    kolom di atas</small></span></button>
                                        <button type="button" class="tb-tile" data-orient="vertical"><i
                                                class="ri-layout-left-line"></i><span><b>Tegak</b><small>Judul di
                                                    kiri, data ke samping</small></span></button>
                                    </div>
                                </div>
                                <div class="ms-auto tb-hist btn-group align-self-start" role="group">
                                    <button type="button" class="btn btn-outline-custom" id="btnUndo"
                                        title="Urungkan (Ctrl+Z)"><i class="ri-arrow-go-back-line"></i></button>
                                    <button type="button" class="btn btn-outline-custom" id="btnRedo"
                                        title="Ulangi (Ctrl+Y)"><i class="ri-arrow-go-forward-line"></i></button>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                                <span class="small text-muted-custom">Contoh cepat:</span>
                                <button type="button" class="btn btn-sm btn-outline-custom" data-preset="buku">Buku kas
                                    sederhana</button>
                                <button type="button" class="btn btn-sm btn-outline-custom"
                                    data-preset="lengkap">Format laporan lengkap</button>
                                <button type="button" class="btn btn-sm btn-outline-custom" data-preset="rekap">Rekap
                                    PP per bulan</button>
                                <button type="button" class="btn btn-sm btn-outline-custom" data-preset="saldo">Saldo
                                    akhir per bulan</button>
                                <button type="button" class="btn btn-sm btn-outline-custom" data-preset="count">Jumlah
                                    transaksi per kategori</button>
                            </div>
                        </div>

                        {{-- PALET --}}
                        <div class="tb-palbar">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <div class="fw-semibold small"><i class="ri-drag-move-2-line me-1"></i>Palet kolom</div>
                                <span class="badge text-bg-light border fw-normal" id="tbCount"></span>
                                <span id="tbNote" class="small text-danger fw-semibold"></span>
                                <input type="search" id="palSearch" class="form-control form-control-sm ms-auto"
                                    style="max-width:210px" placeholder="Cari kolom…">
                            </div>
                            <div class="d-flex flex-wrap align-items-start gap-3">
                                <div style="flex:1 1 220px">
                                    <div class="tb-foot-title">Info &amp; kelompok baris</div>
                                    <div id="palInfo" class="dnd-pal"></div>
                                </div>
                                <div style="flex:3 1 320px">
                                    <div class="tb-foot-title">Angka</div>
                                    <div id="palNum" class="dnd-pal"></div>
                                </div>
                            </div>
                            <div class="small text-muted-custom">Klik chip untuk menambah, atau seret ke judul tabel.
                                Biru = info / kelompok baris, hijau = angka (satu angka boleh dipakai berkali-kali
                                dengan perhitungan berbeda).</div>
                        </div>

                        <div class="tb-work" id="tbWork">
                            {{-- KANVAS TABEL --}}
                            <section>
                                <div class="tb-sheet" id="tbSheet">
                                    <div class="tb-foot-title">Judul</div>
                                    <div id="hdrLines" class="tb-lines mb-1"></div>
                                    <div class="d-flex flex-wrap gap-1 align-items-center mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-custom"
                                            data-addline="hdrLines"><i class="ri-add-line"></i> Baris judul</button>
                                        <span class="small text-muted-custom ms-2">Sisipkan:</span>
                                        <button type="button" class="btn btn-sm btn-outline-custom"
                                            data-ph="{periode}">{periode}</button>
                                        <button type="button" class="btn btn-sm btn-outline-custom"
                                            data-ph="{cabang}">{cabang}</button>
                                        <button type="button" class="btn btn-sm btn-outline-custom"
                                            data-ph="{tanggal_cetak}">{tanggal_cetak}</button>
                                        <button type="button" class="btn btn-sm btn-outline-custom"
                                            data-ph="{pengguna}">{pengguna}</button>
                                    </div>

                                    <div id="tbCanvas"></div>
                                    <div id="tbStrip" class="tb-strip"></div>

                                    <div class="tb-foot-title">Footer</div>
                                    <div id="ftrLines" class="tb-lines mb-1"></div>
                                    <button type="button" class="btn btn-sm btn-outline-custom"
                                        data-addline="ftrLines"><i class="ri-add-line"></i> Baris footer</button>

                                    <div id="tbPop" class="tb-pop d-none">
                                        <div class="small fw-semibold" id="popTitle"></div>
                                        <label>Hitung</label><select id="popAgg"
                                            class="form-select form-select-sm"></select>
                                        <label>Digabung per</label><select id="popPer"
                                            class="form-select form-select-sm"></select>
                                        <label>Subtotal</label><select id="popSub"
                                            class="form-select form-select-sm"></select>
                                        <label>Format angka</label><select id="popFmt"
                                            class="form-select form-select-sm"></select>
                                    </div>
                                </div>
                            </section>

                            {{-- PRATINJAU LANGSUNG --}}
                            <section class="tb-prev">
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <div class="fw-semibold small"><span class="tb-live"></span>Pratinjau hasil Excel
                                    </div>
                                    <span class="small text-muted-custom">ikut berubah otomatis · data contoh</span>
                                    <button type="button" class="btn btn-sm btn-outline-custom ms-auto"
                                        id="btnPrevSide" title="Letakkan pratinjau di samping editor"><i
                                            class="ri-layout-column-line me-1"></i><span>Di samping</span></button>
                                </div>
                                <div class="tb-sheet tb-ro" id="tbPreview"></div>
                            </section>
                        </div>
                    </div>

                    <div class="tb-gutter" id="tbGutter" title="Tarik untuk mengubah lebar panduan"></div>

                    {{-- ================= KANAN: PANDUAN ================= --}}
                    <aside class="tb-guide" id="tbGuide">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h6 class="mb-0 fw-semibold"><i class="ri-book-open-line me-1"></i>Buku panduan &amp; rumus
                            </h6>
                        </div>
                        <input type="search" id="gdSearch" class="form-control form-control-sm mb-3"
                            placeholder="Cari di panduan… (mis. subtotal, saldo, rata-rata)">

                        <details class="gd-sec" open>
                            <summary>Mulai dari mana?</summary>
                            <div class="gd-body">
                                <ol>
                                    <li>Pilih <b>mode</b>: <i>Daftar transaksi</i> (satu baris = satu transaksi) atau
                                        <i>Rekap per periode</i> (angka dijumlahkan per bulan, kuartal, dst).</li>
                                    <li>Pilih <b>orientasi</b>: tabel mendatar atau tegak.</li>
                                    <li>Klik chip di palet (atau seret ke judul tabel).</li>
                                    <li>Klik teks judul di tabel untuk menggantinya. Klik ⚙ pada angka untuk mengatur
                                        rumusnya.</li>
                                    <li>Lihat <b>Pratinjau hasil</b> di bawah, langsung berubah setiap Anda
                                        mengubah sesuatu.</li>
                                    <li>Isi nama template lalu <b>Simpan &amp; Export</b>.</li>
                                    <li>Salah langkah? Tekan ↶ (Ctrl+Z) untuk mengurungkan, ↷ (Ctrl+Y) untuk mengulangi.
                                        Tombol “Di samping” pada pratinjau menaruh hasil di sebelah editor.</li>
                                </ol>
                            </div>
                        </details>

                        <details class="gd-sec">
                            <summary>Mode &amp; orientasi tabel</summary>
                            <div class="gd-body">
                                <table>
                                    <tr>
                                        <th></th>
                                        <th>Mendatar</th>
                                        <th>Tegak</th>
                                    </tr>
                                    <tr>
                                        <td><b>Daftar transaksi</b></td>
                                        <td>Kolom di atas, tiap transaksi satu baris ke bawah.</td>
                                        <td>Kolom jadi baris di kiri, tiap transaksi satu kolom ke samping.</td>
                                    </tr>
                                    <tr>
                                        <td><b>Rekap</b></td>
                                        <td>Kelompok (mis. jemaat) di baris, periode di kolom.</td>
                                        <td>Kelompok di kolom, angka &amp; periode di baris.</td>
                                    </tr>
                                </table>
                                <div>Batas: maksimal 20 kolom (Daftar), 2 kelompok baris dan 15 angka (Rekap).
                                    <i>Tanggal</i> hanya untuk mode Daftar.
                                    “Periode di baris” (pilihan di bawah tabel) hanya tersedia pada tabel mendatar.</div>
                            </div>
                        </details>

                        <details class="gd-sec" open>
                            <summary>Rumus “Hitung” pada angka</summary>
                            <div class="gd-body">
                                Klik ⚙ pada judul angka. Misal ada 3 transaksi dalam satu bulan:
                                <span class="gd-code">100</span> <span class="gd-code">250</span> <span
                                    class="gd-code">150</span>
                                <table>
                                    <tr>
                                        <th>Pilihan</th>
                                        <th>Setara Excel</th>
                                        <th>Hasil</th>
                                        <th>Dipakai untuk</th>
                                    </tr>
                                    <tr>
                                        <td>Jumlah</td>
                                        <td><span class="gd-code">SUM</span></td>
                                        <td>500</td>
                                        <td>PP, iuran, BPJS, pemasukan/pengeluaran</td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah transaksi</td>
                                        <td><span class="gd-code">COUNT</span></td>
                                        <td>3</td>
                                        <td>Berapa kali transaksi terjadi</td>
                                    </tr>
                                    <tr>
                                        <td>Rata-rata</td>
                                        <td><span class="gd-code">AVERAGE</span></td>
                                        <td>166,67</td>
                                        <td>Rata-rata setoran</td>
                                    </tr>
                                    <tr>
                                        <td>Terkecil</td>
                                        <td><span class="gd-code">MIN</span></td>
                                        <td>100</td>
                                        <td>Setoran terendah</td>
                                    </tr>
                                    <tr>
                                        <td>Terbesar</td>
                                        <td><span class="gd-code">MAX</span></td>
                                        <td>250</td>
                                        <td>Setoran tertinggi</td>
                                    </tr>
                                    <tr>
                                        <td>Nilai terakhir</td>
                                        <td>nilai transaksi paling akhir</td>
                                        <td>150</td>
                                        <td><b>Saldo</b> (jangan dijumlahkan!)</td>
                                    </tr>
                                </table>
                                <div><b>Tips Saldo:</b> pakai “Nilai terakhir”. Menjumlahkan saldo tiap transaksi akan
                                    menghasilkan angka yang salah.</div>
                            </div>
                        </details>

                        <details class="gd-sec">
                            <summary>“Digabung per” (periode)</summary>
                            <div class="gd-body">
                                <table>
                                    <tr>
                                        <th>Pilihan</th>
                                        <th>Kolom/baris yang muncul</th>
                                    </tr>
                                    <tr>
                                        <td>Minggu</td>
                                        <td>W01, W02, W03, …</td>
                                    </tr>
                                    <tr>
                                        <td>Bulan</td>
                                        <td>Jan, Feb, Mar, …</td>
                                    </tr>
                                    <tr>
                                        <td>Kuartal</td>
                                        <td>Q1 (Jan–Mar), Q2 (Apr–Jun), …</td>
                                    </tr>
                                    <tr>
                                        <td>Semester</td>
                                        <td>Sem 1 (Jan–Jun), Sem 2 (Jul–Des)</td>
                                    </tr>
                                    <tr>
                                        <td>Tahun</td>
                                        <td>2025, 2026, …</td>
                                    </tr>
                                    <tr>
                                        <td>Seluruh periode</td>
                                        <td>Satu kolom saja</td>
                                    </tr>
                                </table>
                            </div>
                        </details>

                        <details class="gd-sec">
                            <summary>Subtotal &amp; total</summary>
                            <div class="gd-body">
                                <ul>
                                    <li><b>Subtotal</b> menambah kolom Σ setelah beberapa periode. Contoh “Bulan +
                                        subtotal tiap kuartal”: Jan · Feb · Mar · <b>Σ Q1</b> · Apr · Mei · Jun · <b>Σ
                                            Q2</b>.</li>
                                    <li>Subtotal hanya aktif jika periodenya <u>lebih kecil</u> dari subtotalnya
                                        (Bulan → Kuartal/Semester/Tahun, Kuartal → Semester/Tahun, Semester → Tahun).
                                        Jika menu abu-abu, ganti dulu “Digabung per”.</li>
                                    <li><b>Total</b> (baris kuning) bisa dipindah ke atas/bawah dengan tombol ⇅ (atau
                                        seret ≡ pada tabel mendatar). Pada tabel tegak, total berupa kolom (tombol
                                        ⇄).</li>
                                    <li>Dengan 2 kelompok baris, centang “Subtotal per kelompok” untuk subtotal tiap
                                        induk (mis. per MD).</li>
                                </ul>
                            </div>
                        </details>

                        <details class="gd-sec">
                            <summary>Format angka</summary>
                            <div class="gd-body">
                                <table>
                                    <tr>
                                        <th>Pilihan</th>
                                        <th>Contoh 1250000</th>
                                    </tr>
                                    <tr>
                                        <td>Angka 2 desimal</td>
                                        <td>1.250.000,00</td>
                                    </tr>
                                    <tr>
                                        <td>Angka bulat</td>
                                        <td>1.250.000</td>
                                    </tr>
                                    <tr>
                                        <td>Rupiah</td>
                                        <td>Rp 1.250.000</td>
                                    </tr>
                                </table>
                                “Jumlah transaksi” selalu tampil sebagai bilangan bulat.
                            </div>
                        </details>

                        <details class="gd-sec">
                            <summary>Judul &amp; footer (placeholder)</summary>
                            <div class="gd-body">
                                Ketik langsung atau klik tombol “Sisipkan”. Saat export, isinya diganti otomatis:
                                <table>
                                    <tr>
                                        <td><span class="gd-code">{periode}</span></td>
                                        <td>Periode laporan yang dipilih</td>
                                    </tr>
                                    <tr>
                                        <td><span class="gd-code">{cabang}</span></td>
                                        <td>Nama branch / MD</td>
                                    </tr>
                                    <tr>
                                        <td><span class="gd-code">{tanggal_cetak}</span></td>
                                        <td>Tanggal export dibuat</td>
                                    </tr>
                                    <tr>
                                        <td><span class="gd-code">{pengguna}</span></td>
                                        <td>Nama pengguna yang export</td>
                                    </tr>
                                </table>
                                Baris pertama judul tampil lebih besar. Seret ≡ untuk mengubah urutan baris.
                            </div>
                        </details>

                        <details class="gd-sec" open>
                            <summary>Resep siap pakai</summary>
                            <div class="gd-body">
                                Klik untuk menerapkan ke tabel (susunan saat ini akan diganti).
                                <table>
                                    <tr>
                                        <td><b>Buku kas sederhana</b><br>Tanggal, keterangan, debit, kredit, saldo.</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-custom"
                                                data-preset="buku">Terapkan</button></td>
                                    </tr>
                                    <tr>
                                        <td><b>Buku kas tegak</b><br>Sama, tapi kolom disusun ke bawah.</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-custom"
                                                data-preset="tegak">Terapkan</button></td>
                                    </tr>
                                    <tr>
                                        <td><b>Rekap PP per jemaat</b><br>Jumlah PP tiap bulan + subtotal kuartal.</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-custom"
                                                data-preset="rekap">Terapkan</button></td>
                                    </tr>
                                    <tr>
                                        <td><b>Saldo akhir per bulan</b><br>Saldo = nilai terakhir tiap bulan.</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-custom"
                                                data-preset="saldo">Terapkan</button></td>
                                    </tr>
                                    <tr>
                                        <td><b>Jumlah transaksi per kategori</b><br>Berapa kali tiap kategori muncul.
                                        </td>
                                        <td><button type="button" class="btn btn-sm btn-outline-custom"
                                                data-preset="count">Terapkan</button></td>
                                    </tr>
                                </table>
                            </div>
                        </details>

                        <details class="gd-sec">
                            <summary>Kamus kolom (klik untuk menambah)</summary>
                            <div class="gd-body" id="gdFields"></div>
                        </details>

                        <details class="gd-sec">
                            <summary>Masalah umum</summary>
                            <div class="gd-body">
                                <ul>
                                    <li><b>Chip tidak bisa ditambah?</b> Cek pesan merah di bawah palet: batas penuh,
                                        sudah dipakai (mode Daftar), atau <i>Tanggal</i> dipakai di mode Rekap.</li>
                                    <li><b>Template ★ milik orang lain</b> tidak bisa diubah; menyimpannya akan membuat
                                        salinan atas nama Anda.</li>
                                    <li><b>Judul kolom diganti tapi data tetap sama:</b> benar, hanya teksnya yang
                                        berubah. Sumber data tetap sesuai chip (tertera kecil di bawah judul).</li>
                                    <li><b>Lebar kolom:</b> tarik garis di sisi kanan judul kolom (tabel mendatar,
                                        mode Daftar).</li>
                                    <li><b>Layar kecil:</b> panduan akan pindah ke bawah. Di layar lebar, tarik garis
                                        pemisah untuk mengatur lebar panduan.</li>
                                </ul>
                            </div>
                        </details>
                    </aside>
                </div>
            </div>

            <div class="modal-footer border-top border-custom py-3 px-4">
                <button type="button" class="btn btn-outline-danger me-auto" id="btnTplDelete">Hapus
                    template</button>
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-outline-custom" id="btnTplSave"><i
                        class="ri-save-line me-1"></i> Simpan</button>
                <button type="button" class="btn btn-custom" id="btnTplExport"><i
                        class="ri-download-2-line me-1"></i> Simpan &amp; Export</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    window.__EXP = {
        fields: @json($exportFields),
        admin: @json($exportIsAdmin),
        base: @json(url('admin/finance/export/templates'))
    };
</script>
@verbatim
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const X = window.__EXP;
            const FIELDS = X.fields,
                ADMIN = X.admin,
                base = X.base;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const $ = id => document.getElementById(id);
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const ls = {
                get: k => { try { return localStorage.getItem(k); } catch (_) { return null; } },
                set: (k, v) => { try { localStorage.setItem(k, v); } catch (_) {} }
            };

            document.body.appendChild($('exportTemplateModal')); // modal harus di level atas body
            const modal = new bootstrap.Modal($('exportTemplateModal'));
            const runForm = $('exportRunForm');

            // ---------- konstanta ----------
            const isNum = k => FIELDS[k][1] === 'number';
            const kindOf = k => isNum(k) ? 'val' : (k === 'date' ? 'misc' : 'dim');
            const INFO = ['date', 'description', 'pic', 'church', 'branch', 'category'];
            const DIMS = ['church', 'pic', 'branch', 'category', 'description'];
            const NUMS = Object.keys(FIELDS).filter(isNum);
            const RANK = {
                week: 0,
                month: 1,
                quarter: 2,
                semester: 3,
                year: 4,
                all: 0
            };
            const LBL = {
                agg: {
                    sum: 'Jumlah',
                    count: 'Jumlah transaksi',
                    avg: 'Rata-rata',
                    min: 'Terkecil',
                    max: 'Terbesar',
                    last: 'Nilai terakhir (cocok untuk Saldo)'
                },
                per: {
                    week: 'Minggu',
                    month: 'Bulan',
                    quarter: 'Kuartal',
                    semester: 'Semester',
                    year: 'Tahun',
                    all: 'Seluruh periode (satu kolom)'
                },
                sub: {
                    '': 'Tanpa subtotal',
                    quarter: 'Subtotal tiap kuartal',
                    semester: 'Subtotal tiap semester',
                    year: 'Subtotal tiap tahun'
                },
                fmt: {
                    dec: 'Angka 2 desimal',
                    int: 'Angka bulat',
                    rp: 'Rupiah'
                }
            };
            ['Agg', 'Per', 'Sub', 'Fmt'].forEach(n => {
                $('pop' + n).innerHTML = Object.entries(LBL[n.toLowerCase()]).map(([k, l]) =>
                    `<option value="${k}">${l}</option>`).join('');
            });

            const SAMPLE = [{
                    date: '03-Jan',
                    description: 'PP (Desember 2025)',
                    pic: 'Pdt. Yusuf Ronni Wowor',
                    church: 'GSPDI Jamblang',
                    branch: 'MD Cirebon',
                    category: 'PP',
                    debit: 1250000,
                    balance: 6250000,
                    pp: 1250000
                },
                {
                    date: '10-Jan',
                    description: 'BPJS',
                    pic: 'Pdt. Yvonne',
                    church: 'Pusdikpom',
                    branch: 'MD Cirebon',
                    category: 'BPJS',
                    credit: 300000,
                    balance: 5950000,
                    bpjs: 300000
                },
                {
                    date: '25-Jan',
                    description: 'Biaya Admin',
                    pic: '',
                    church: '',
                    branch: 'MD Cirebon',
                    category: 'ADMIN_BANK',
                    credit: 12500,
                    balance: 5937500,
                    admin: 12500
                },
                {
                    date: '28-Jan',
                    description: 'Iuran MDF',
                    pic: 'Ibu Rolline',
                    church: 'GSPDI Sumedang',
                    branch: 'MD Bandung',
                    category: 'IURAN_MDF',
                    debit: 500000,
                    balance: 6437500,
                    iuran_mdf: 500000
                },
                {
                    date: '30-Jan',
                    description: 'TAHAKA Januari',
                    pic: 'Pdt. Yvonne',
                    church: 'Pusdikpom',
                    branch: 'MD Cirebon',
                    category: 'TAHAKA',
                    credit: 150000,
                    balance: 6287500,
                    tahaka: 150000
                }
            ];
            const SAMPLE_DIM = {
                church: ['GSPDI Jamblang', 'GSPDI Sumedang', 'Pusdikpom'],
                pic: ['Pdt. Yusuf Ronni', 'Pdt. Yvonne', 'Ibu Rolline'],
                branch: ['MD Cirebon', 'MD Bandung', 'MD Garut'],
                category: ['PP', 'BPJS', 'TAHAKA'],
                description: ['PP (Januari)', 'BPJS', 'Biaya Admin']
            };
            const nf = n => (n ? Number(n).toLocaleString('id-ID') : '');
            const px = c => Math.max(64, Math.round((c.width || FIELDS[c.key][2]) * 7.2));
            const padTo = (arr, n) => Array.from({
                length: n
            }, (_, i) => arr[i] ?? '');

            // ---------- state ----------
            const defCols = () => ['date', 'description', 'debit', 'credit', 'balance'].map(k => ({
                key: k,
                label: FIELDS[k][0],
                width: FIELDS[k][2]
            }));
            const defVal = k => ({
                key: k,
                label: FIELDS[k][0],
                agg: k === 'balance' ? 'last' : 'sum',
                per: 'month',
                sub: '',
                fmt: 'dec'
            });
            const blank = () => ({
                id: null,
                name: '',
                is_shared: false,
                can_edit: true,
                config: {
                    mode: 'detail',
                    orientation: 'horizontal',
                    header: {
                        lines: ['LAPORAN KEUANGAN', '{periode}']
                    },
                    footer: {
                        enabled: false,
                        lines: []
                    },
                    columns: null,
                    rows: ['church'],
                    values: [Object.assign(defVal('pp'), {
                        sub: 'quarter'
                    })],
                    layout: 'by_value',
                    sort: 'label',
                    sheet_by: 'none',
                    fill_empty: true,
                    hide_empty: false,
                    totals: {
                        enabled: true,
                        row: true,
                        col: true,
                        sub: false,
                        position: 'bottom',
                        label: 'TOTAL'
                    }
                }
            });

            let S = null,
                templates = [],
                sorts = [],
                popVi = null,
                lastInput = null,
                noteTimer = null,
                pvFrame = null,
                hist = [],
                hi = -1,
                restoring = false;

            const isV = () => S.orient === 'vertical';

            function fromTemplate(t) {
                const c = t.config || {},
                    tot = c.totals || {};
                const cols = (c.columns || []).filter(x => FIELDS[x.key]).map(x => ({
                    key: x.key,
                    label: x.label,
                    width: x.width
                }));
                S = {
                    id: t.id,
                    name: t.name,
                    is_shared: !!t.is_shared,
                    can_edit: t.can_edit !== false,
                    mode: c.mode || 'detail',
                    orient: c.orientation === 'vertical' ? 'vertical' : 'horizontal',
                    columns: cols.length ? cols : defCols(),
                    rows: (c.rows || []).filter(k => FIELDS[k]),
                    values: (c.values || []).filter(v => FIELDS[v.key]).map(v => ({
                        key: v.key,
                        label: v.label,
                        agg: v.agg || 'sum',
                        per: v.per || 'month',
                        sub: v.sub || '',
                        fmt: v.fmt || 'dec'
                    })),
                    opts: {
                        layout: c.layout === 'period_as_rows' ? 'period_as_rows' : 'by_value',
                        sort: c.sort || 'label',
                        sheet_by: c.sheet_by || 'none',
                        fill_empty: c.fill_empty !== false,
                        hide_empty: !!c.hide_empty
                    },
                    totals: {
                        row: !!(tot.row ?? tot.enabled),
                        col: tot.col !== false,
                        sub: !!tot.sub,
                        position: tot.position || 'bottom',
                        label: tot.label || 'TOTAL'
                    }
                };
                $('tplName').value = t.name || '';
                $('tplShared').checked = S.is_shared;
                setLines('hdrLines', (c.header && c.header.lines) || []);
                setLines('ftrLines', (c.footer && c.footer.lines) || []);
                $('btnTplDelete').classList.toggle('d-none', !t.id || !S.can_edit);
                closePop();
                hist = [];
                hi = -1;
                render();
            }

            // ---------- umpan balik singkat ----------
            function note(msg) {
                const el = $('tbNote');
                el.textContent = msg;
                clearTimeout(noteTimer);
                noteTimer = setTimeout(() => el.textContent = '', 3500);
            }

            // ---------- palet ----------
            const chip = k =>
                `<div class="dnd-chip ${kindOf(k)}" data-key="${k}" data-kind="${kindOf(k)}" data-pal="1" title="Klik untuk menambah, atau seret">${esc(FIELDS[k][0])}</div>`;
            $('palInfo').innerHTML = INFO.map(chip).join('');
            $('palNum').innerHTML = NUMS.map(chip).join('');
            ['palInfo', 'palNum'].forEach(id => {
                Sortable.create($(id), {
                    group: {
                        name: 'pal',
                        pull: 'clone',
                        put: false
                    },
                    sort: false,
                    animation: 150
                });
                $(id).addEventListener('click', e => {
                    const c = e.target.closest('.dnd-chip');
                    if (c) tryAdd(c.dataset.key);
                });
            });
            $('sharedWrap').classList.toggle('d-none', !ADMIN);

            function canDrop(key, kind) {
                if (!FIELDS[key]) return false;
                if (S.mode === 'detail') return !S.columns.some(c => c.key === key) && S.columns.length < 20;
                if (kind === 'val') return S.values.length < 15;
                if (kind === 'dim') return !S.rows.includes(key) && S.rows.length < 2;
                return false;
            }

            function addField(k) {
                if (S.mode === 'detail') {
                    if (canDrop(k, kindOf(k))) {
                        S.columns.push({
                            key: k,
                            label: FIELDS[k][0],
                            width: FIELDS[k][2]
                        });
                        return true;
                    }
                } else if (kindOf(k) === 'val') {
                    if (S.values.length < 15) {
                        S.values.push(defVal(k));
                        return true;
                    }
                } else if (kindOf(k) === 'dim' && canDrop(k, 'dim')) {
                    S.rows.push(k);
                    return true;
                }
                return false;
            }

            function tryAdd(k) {
                if (!FIELDS[k]) return;
                if (addField(k)) {
                    render();
                    return;
                }
                const nm = FIELDS[k][0];
                if (S.mode === 'summary' && kindOf(k) === 'misc') note(`“${nm}” hanya untuk mode Daftar transaksi.`);
                else if (S.mode === 'detail' && S.columns.some(c => c.key === k)) note(`“${nm}” sudah ada di tabel.`);
                else if (S.mode === 'summary' && kindOf(k) === 'dim' && S.rows.includes(k)) note(`“${nm}” sudah dipakai sebagai kelompok baris.`);
                else note('Batas tercapai (Daftar maks. 20 kolom · Rekap maks. 2 kelompok & 15 angka).');
            }

            // ---------- baris judul / footer ----------
            const lineEl = t => {
                const d = document.createElement('div');
                d.className = 'tb-line';
                d.innerHTML =
                    `<i class="ri-draggable tb-grip"></i><input class="tb-linein" value="${esc(t)}" maxlength="200" placeholder="Ketik teks..."><button type="button" class="tb-x" data-rmline title="Hapus baris">×</button>`;
                return d;
            };
            const setLines = (id, arr) => $(id).replaceChildren(...arr.map(lineEl));
            const getLines = id => [...$(id).querySelectorAll('input')].map(i => i.value.trim()).filter(Boolean);
            ['hdrLines', 'ftrLines'].forEach(id => Sortable.create($(id), {
                animation: 150,
                handle: '.tb-grip',
                ghostClass: 'tb-ghost',
                onEnd: () => renderPreview()
            }));

            $('exportTemplateModal').addEventListener('click', e => {
                const rm = e.target.closest('[data-rmline]');
                if (rm) {
                    rm.closest('.tb-line').remove();
                    renderPreview();
                }
                const add = e.target.closest('[data-addline]');
                if (add) {
                    const l = lineEl('');
                    $(add.dataset.addline).append(l);
                    l.querySelector('input').focus();
                }
            });
            ['hdrLines', 'ftrLines'].forEach(id => $(id).addEventListener('input', () => renderPreview()));
            document.addEventListener('focusin', e => {
                if (e.target.classList && e.target.classList.contains('tb-linein')) lastInput = e.target;
            });
            document.querySelectorAll('[data-ph]').forEach(b => b.addEventListener('mousedown', e => {
                e.preventDefault();
                if (!lastInput) return;
                const s = lastInput.selectionStart ?? lastInput.value.length,
                    t = lastInput.selectionEnd ?? s;
                lastInput.value = lastInput.value.slice(0, s) + b.dataset.ph + lastInput.value.slice(t);
                lastInput.focus();
                renderPreview();
            }));

            // ---------- bantu tampilan tabel ----------
            const subOf = v => (v.sub && ['month', 'quarter', 'semester'].includes(v.per) && RANK[v.sub] > RANK[v
                .per]) ? v.sub : null;
            const describe = v => {
                let t = LBL.agg[v.agg].split(' (')[0] + ' · ' + (v.per === 'all' ? 'seluruh periode' : 'per ' +
                    LBL.per[v.per].toLowerCase());
                if (subOf(v)) t += ' + ' + LBL.sub[subOf(v)].toLowerCase();
                return t;
            };

            function segs(v) {
                const out = [],
                    add = (label, kind) => out.push({
                        label,
                        kind: kind || 'bucket'
                    }),
                    sub = subOf(v);
                const sl = {
                    quarter: 'Σ Q1 2026',
                    semester: 'Σ Sem 1 2026',
                    year: 'Σ 2026'
                };
                if (v.per === 'week')['W01', 'W02', 'W03'].forEach(w => add(w + ' 2026'));
                else if (v.per === 'month') {
                    ['Jan', 'Feb', 'Mar'].forEach(m => add(m + ' 2026'));
                    if (sub) add(sl[sub], 'sub');
                } else if (v.per === 'quarter') {
                    add('Q1 2026');
                    add('Q2 2026');
                    if (sub) add(sl[sub], 'sub');
                } else if (v.per === 'semester') {
                    add('Sem 1 2026');
                    add('Sem 2 2026');
                    if (sub) add(sl[sub], 'sub');
                } else if (v.per === 'year') {
                    add('2025');
                    add('2026');
                } else add('Total');
                if (S.totals.col && v.per !== 'all') add('Total', 'total');
                return out;
            }

            function fmtVal(n, v) {
                if (v.agg === 'count') return String(Math.max(1, Math.round(n / 125000)));
                if (v.fmt === 'rp') return 'Rp ' + n.toLocaleString('id-ID');
                if (v.fmt === 'int') return n.toLocaleString('id-ID');
                return n.toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                });
            }

            function cellNum(ri, s, rk, isTot) {
                let n = ((ri * 7 + s.vi * 5 + s.si * 3) % 9 + 1) * 125000;
                if (!isTot && (ri + s.si) % 5 === 4 && s.sg.kind === 'bucket') return '';
                if (s.sg.kind !== 'bucket') n *= 3;
                if (rk !== 'row') n *= 2;
                if (isTot) n *= 4;
                return fmtVal(n, S.values[s.vi]);
            }

            function sumRows() {
                const d = S.rows;
                if (!d.length) return [{
                    labels: ['Semua'],
                    kind: 'row'
                }];
                if (d.length === 1) return SAMPLE_DIM[d[0]].map(x => ({
                    labels: [x],
                    kind: 'row'
                }));
                const A = SAMPLE_DIM[d[0]].slice(0, 2),
                    B = SAMPLE_DIM[d[1]].slice(0, 2),
                    out = [];
                A.forEach(a => {
                    B.forEach((b, bi) => out.push({
                        labels: [bi === 0 ? a : '', b],
                        kind: 'row'
                    }));
                    if (S.totals.sub) out.push({
                        labels: ['Subtotal ' + a, ''],
                        kind: 'sub'
                    });
                });
                return out;
            }

            const labelInput = () =>
                `<input class="tb-lab" id="totLab" value="${esc(S.totals.label)}" maxlength="40">`;
            const totFirst = inner => `<td class="tot"><i class="ri-draggable tb-grip tot-grip"></i> ${inner}</td>`;
            const totRowHtml = cells =>
                `<tr class="tot-row">${cells.join('')}<td class="pad"><button type="button" class="tb-x" data-flip title="Pindah ke atas/bawah">⇅</button><button type="button" class="tb-x" data-rmtot title="Hapus baris total">×</button></td></tr>`;
            const addTotRow = span => S.totals.row ? '' :
                `<tr class="tb-addtot"><td colspan="${span}"><button type="button" class="btn btn-sm btn-link p-0" data-addtot>＋ Tambah baris total</button></td></tr>`;
            const orderRows = (rows, tot) => (S.totals.position === 'top' ? [tot, ...rows] : [...rows, tot]).join(
                '');

            function addSelect() {
                const opt = x => `<option value="${x}">${esc(FIELDS[x][0])}</option>`;
                let o = '';
                if (S.mode === 'detail') {
                    const used = S.columns.map(c => c.key);
                    const g = (t, keys) => {
                        const k = keys.filter(x => !used.includes(x));
                        return k.length ? `<optgroup label="${t}">${k.map(opt).join('')}</optgroup>` : '';
                    };
                    o = g('Info', INFO) + g('Angka', NUMS);
                } else {
                    const d = S.rows.length < 2 ? DIMS.filter(x => !S.rows.includes(x)) : [];
                    o = (d.length ? `<optgroup label="Kelompok baris">${d.map(opt).join('')}</optgroup>` : '') +
                        `<optgroup label="Angka">${NUMS.map(opt).join('')}</optgroup>`;
                }
                return `<select class="tb-addsel form-select form-select-sm"><option value="">＋ Tambah</option>${o}</select>`;
            }

            // axis: 'col' (judul di atas, rentang kolom) | 'row' (judul di kiri, rentang baris)
            const valTh = (v, vi, span, axis) => `<th class="tb-h tb-val" ${axis === 'row' ? 'rowspan' : 'colspan'}="${span}" data-kind="val" data-idx="${vi}" style="min-width:150px">
        <div class="tb-hin"><i class="ri-draggable tb-grip"></i><input class="tb-lab" data-labv="${vi}" value="${esc(v.label)}" maxlength="60">
        <button type="button" class="tb-ic" data-gear="${vi}" title="Atur perhitungan">⚙</button><button type="button" class="tb-x" data-rmv="${vi}" title="Hapus">×</button></div>
        <div class="tb-sub">${esc(describe(v))}</div></th>`;

            // ---------- tabel: daftar transaksi (mendatar) ----------
            function detailTable() {
                const cols = S.columns,
                    n = cols.length;
                const W = cols.reduce((a, c) => a + px(c), 0) + 130;
                const heads = cols.map((c, i) => `<th class="tb-h" data-kind="col" data-idx="${i}" style="width:${px(c)}px">
            <div class="tb-hin"><i class="ri-draggable tb-grip"></i>
            <input class="tb-lab" data-lab="${i}" value="${esc(c.label)}" maxlength="60" title="Klik untuk mengganti judul">
            <button type="button" class="tb-x" data-rm="${i}" title="Hapus kolom">×</button></div>
            <div class="tb-sub">${esc(FIELDS[c.key][0])}</div><span class="resizer" data-rs="${i}"></span></th>`).join(
                    '');
                const head =
                    `<tr id="tbHead">${heads}${n ? '' : '<th class="tb-drop">⬅ Seret / klik kolom dari palet</th>'}<th class="tb-add" style="width:130px">${addSelect()}</th></tr>`;
                const smp = SAMPLE.map(r =>
                    `<tr class="smp">${cols.map(c => `<td class="${isNum(c.key) ? 'num' : ''}">${esc(isNum(c.key) ? nf(r[c.key]) : (r[c.key] ?? ''))}</td>`).join('')}<td class="pad"></td></tr>`
                    );
                const sum = k => SAMPLE.reduce((a, r) => a + (r[k] || 0), 0);
                let tot = '';
                if (S.totals.row && n) {
                    tot = totRowHtml(cols.map((c, i) => {
                        const val = isNum(c.key) && c.key !== 'balance' ? nf(sum(c.key)) : '';
                        if (i === 0) return totFirst(isNum(c.key) ? esc(val) : labelInput());
                        return `<td class="tot ${isNum(c.key) ? 'num' : ''}">${esc(val)}</td>`;
                    }));
                }
                return `<div class="tb-wrap"><table class="tb fixed" style="width:${W}px"><thead>${head}</thead>
            <tbody id="tbBody">${orderRows(smp, tot)}${n ? addTotRow(n + 1) : ''}</tbody></table></div>`;
            }

            // ---------- tabel: daftar transaksi (tegak) ----------
            function detailVTable() {
                const cols = S.columns,
                    n = cols.length,
                    hasTot = S.totals.row,
                    first = S.totals.position === 'top';
                const sum = k => SAMPLE.reduce((a, r) => a + (r[k] || 0), 0);
                const totTh =
                    `<th class="tb-p total"><div class="tb-hin">${labelInput()}<button type="button" class="tb-x" data-flip title="Pindah kiri/kanan">⇄</button><button type="button" class="tb-x" data-rmtot title="Hapus kolom total">×</button></div></th>`;
                const addTh =
                    `<th class="tb-add"><button type="button" class="btn btn-sm btn-link p-0" data-addtot>＋ Total</button></th>`;
                const sh = SAMPLE.map((_, i) => `<th class="tb-p">Transaksi ${i + 1}</th>`).join('');
                const colg =
                    `<colgroup><col style="width:260px">${hasTot && first ? '<col style="width:150px">' : ''}${SAMPLE.map(() => '<col>').join('')}${hasTot ? (first ? '' : '<col style="width:150px">') : '<col style="width:110px">'}</colgroup>`;
                const head =
                    `<thead><tr><th class="tb-fix">Kolom</th>${hasTot && first ? totTh : ''}${sh}${hasTot ? (first ? '' : totTh) : addTh}</tr></thead>`;
                const rows = cols.map((c, i) => {
                    const num = isNum(c.key);
                    const tv = num && c.key !== 'balance' ? nf(sum(c.key)) : '';
                    const t = hasTot ? (tv ? `<td class="tot num">${esc(tv)}</td>` : `<td class="tot dash">—</td>`) : '';
                    const s = SAMPLE.map(r =>
                        `<td class="${num ? 'num' : ''}" title="${esc(num ? '' : (r[c.key] ?? ''))}">${esc(num ? nf(r[c.key]) : (r[c.key] ?? ''))}</td>`).join('');
                    return `<tr class="hrow smp" data-kind="col" data-idx="${i}"><th class="tb-h">
                <div class="tb-hin"><i class="ri-draggable tb-grip"></i>
                <input class="tb-lab" data-lab="${i}" value="${esc(c.label)}" maxlength="60" title="Klik untuk mengganti judul">
                <span class="tb-tag" title="Sumber data">${esc(FIELDS[c.key][0])}</span>
                <button type="button" class="tb-x" data-rm="${i}" title="Hapus">×</button></div></th>${first ? t : ''}${s}${first ? '' : t}</tr>`;
                }).join('');
                const foot =
                    `<tfoot><tr class="tb-addrow"><td colspan="${SAMPLE.length + 2}">${addSelect()} ${n ? '' : '<span class="small text-muted-custom ms-2">⬆ Klik / seret kolom dari palet</span>'}</td></tr></tfoot>`;
                return `<div class="tb-wrap"><table class="tb vt">${colg}${head}<tbody id="tbBody">${rows}</tbody>${foot}</table></div>`;
            }

            // ---------- tabel: rekap (mendatar) ----------
            function summaryTable() {
                if (S.opts.layout === 'period_as_rows') return periodRowsTable();
                const dims = S.rows,
                    vals = S.values,
                    lc = Math.max(1, dims.length),
                    specs = [];
                vals.forEach((v, vi) => segs(v).forEach((sg, si) => specs.push({
                    vi,
                    si,
                    sg
                })));

                const dh = dims.length ?
                    dims.map((d, i) => `<th class="tb-h tb-dim" rowspan="2" data-kind="dim" data-idx="${i}" style="min-width:150px">
                <div class="tb-hin"><i class="ri-draggable tb-grip"></i><span class="tb-st">${esc(FIELDS[d][0])}</span><button type="button" class="tb-x" data-rmd="${i}" title="Hapus">×</button></div>
                <div class="tb-sub">kelompok baris</div></th>`).join('') :
                    `<th class="tb-fix" rowspan="2" style="min-width:150px">Keterangan</th>`;
                const vh = vals.map((v, vi) => valTh(v, vi, segs(v).length, 'col')).join('');
                const drop = vals.length ? '' : '<th class="tb-drop" rowspan="2">⬅ Seret / klik “angka” dari palet</th>';
                const head1 =
                    `<tr id="tbHead">${dh}${vh}${drop}<th class="tb-add" rowspan="2" style="min-width:130px">${addSelect()}</th></tr>`;
                const head2 =
                    `<tr>${specs.map(s => `<th class="tb-p ${s.sg.kind}">${esc(s.sg.label)}</th>`).join('')}</tr>`;

                const R = sumRows();
                const body = R.map((r, ri) =>
                    `<tr class="${r.kind === 'sub' ? 'subrow' : 'smp'}">${padTo(r.labels, lc).map(l => `<td>${esc(l)}</td>`).join('')}${specs.map(s => `<td class="num ${s.sg.kind !== 'bucket' ? 'alt' : ''}">${esc(cellNum(ri, s, r.kind, false))}</td>`).join('')}<td class="pad"></td></tr>`
                    );
                let tot = '';
                if (S.totals.row) {
                    const labs = padTo([''], lc).map((_, i) => i === 0 ? totFirst(labelInput()) :
                        '<td class="tot"></td>');
                    tot = totRowHtml([...labs, ...specs.map(s =>
                        `<td class="tot num">${esc(cellNum(0, s, 'row', true))}</td>`)]);
                }
                return `<div class="tb-wrap"><table class="tb"><thead>${head1}${head2}</thead>
            <tbody id="tbBody">${orderRows(body, tot)}${addTotRow(lc + specs.length + 1)}</tbody></table></div>`;
            }

            function periodRowsTable() {
                const v = S.values[0];
                const head =
                    `<tr id="tbHead"><th class="tb-fix" style="min-width:140px">Bulan</th>${v ? valTh(v, 0, 1, 'col') : '<th class="tb-drop">⬅ Seret / klik “angka” dari palet</th>'}<th class="tb-add" style="min-width:130px">${addSelect()}</th></tr>`;
                const vv = v || defVal('pp');
                const body = ['Jan 2026', 'Feb 2026', 'Mar 2026'].map((m, i) =>
                    `<tr class="smp"><td>${m}</td><td class="num">${esc(fmtVal((i + 2) * 250000, vv))}</td><td class="pad"></td></tr>`
                    );
                const tot = S.totals.row ? totRowHtml([totFirst(labelInput()),
                    `<td class="tot num">${esc(fmtVal(2250000, vv))}</td>`
                ]) : '';
                return `<div class="tb-wrap"><table class="tb"><thead>${head}</thead><tbody id="tbBody">${orderRows(body, tot)}${addTotRow(3)}</tbody></table></div>
            <div class="small text-muted-custom mt-2">Susunan “periode di baris” memakai satu angka saja (yang paling kiri).</div>`;
            }

            // ---------- tabel: rekap (tegak) ----------
            function summaryVTable() {
                const dims = S.rows,
                    vals = S.values,
                    hasTot = S.totals.row,
                    first = S.totals.position === 'top';
                const R = sumRows();
                let lastA = '';
                const cols = R.map(r => {
                    if (r.kind === 'sub') return {
                        a: r.labels[0],
                        b: '',
                        kind: 'sub'
                    };
                    if (r.labels[0]) lastA = r.labels[0];
                    return {
                        a: dims.length === 2 ? lastA : r.labels[0],
                        b: r.labels[1] || '',
                        kind: 'row'
                    };
                });
                const nC = cols.length;
                const blank = '<th class="tb-p"></th>';
                const frontBlank = hasTot && first ? blank : '',
                    endBlank = hasTot && first ? '' : blank;
                const totTh =
                    `<th class="tb-p total"><div class="tb-hin">${labelInput()}<button type="button" class="tb-x" data-flip title="Pindah kiri/kanan">⇄</button><button type="button" class="tb-x" data-rmtot title="Hapus kolom total">×</button></div></th>`;
                const addTh =
                    `<th class="tb-add"><button type="button" class="btn btn-sm btn-link p-0" data-addtot>＋ Total</button></th>`;
                const colg =
                    `<colgroup><col style="width:190px"><col style="width:130px">${hasTot && first ? '<col style="width:140px">' : ''}${cols.map(() => '<col>').join('')}${hasTot && first ? '' : '<col style="width:140px">'}</colgroup>`;
                const topTail = hasTot ? (first ? '' : totTh) : addTh;
                const topRow =
                    `<tr class="hfix"><th class="tb-fix" colspan="2">Angka · periode</th>${hasTot && first ? totTh : ''}<th class="tb-p" colspan="${nC}">${dims.length ? 'Kelompok' : 'Keterangan'}</th>${topTail}</tr>`;
                const noDim = dims.length ? '' :
                    `<tr class="hfix"><th class="tb-fix" colspan="2">Keterangan</th>${frontBlank}${cols.map(c => `<th class="tb-p">${esc(c.a)}</th>`).join('')}${endBlank}</tr>`;
                const dimRows = dims.map((d, i) => `<tr class="hrow" data-kind="dim" data-idx="${i}">
                <th class="tb-h tb-dim" colspan="2"><div class="tb-hin"><i class="ri-draggable tb-grip"></i><span class="tb-st">${esc(FIELDS[d][0])}</span><span class="tb-tag">kelompok</span><button type="button" class="tb-x" data-rmd="${i}" title="Hapus">×</button></div></th>${frontBlank}${cols.map(c => `<th class="tb-p ${c.kind === 'sub' ? 'sub' : ''}">${esc(i === 0 ? c.a : c.b)}</th>`).join('')}${endBlank}</tr>`)
                    .join('');
                const groups = vals.map((v, vi) => {
                    const sg = segs(v);
                    const rows = sg.map((g, si) => {
                        const s = {
                            vi,
                            si,
                            sg: g
                        };
                        const cells = cols.map((c, ci) =>
                            `<td class="num ${g.kind !== 'bucket' ? 'alt' : ''}">${esc(cellNum(ci, s, c.kind, false))}</td>`).join('');
                        const t = hasTot ? `<td class="tot num">${esc(cellNum(0, s, 'row', true))}</td>` : '';
                        return `<tr>${si === 0 ? valTh(v, vi, sg.length, 'row') : ''}<th class="tb-p ${g.kind}">${esc(g.label)}</th>${first ? t : ''}${cells}${first ? '' : t}</tr>`;
                    }).join('');
                    return `<tbody class="hgrp" data-kind="val" data-idx="${vi}">${rows}</tbody>`;
                }).join('');
                const foot =
                    `<tbody class="hfoot"><tr class="tb-addrow"><td colspan="${nC + 4}">${addSelect()} ${vals.length ? '' : '<span class="small text-muted-custom ms-2">⬆ Klik / seret “angka” dari palet</span>'}</td></tr></tbody>`;
                return `<div class="tb-wrap"><table class="tb vt" id="tbVals">${colg}<thead id="tbDims">${topRow}${noDim}${dimRows}</thead>${groups}${foot}</table></div>`;
            }

            const tableHtml = () => S.mode === 'detail' ?
                (isV() ? detailVTable() : detailTable()) :
                (isV() ? summaryVTable() : summaryTable());

            // ---------- strip opsi ----------
            function renderStrip() {
                const o = S.opts,
                    t = S.totals,
                    V = isV();
                const chk = (k, label, on) =>
                    `<label class="form-check form-check-inline m-0"><input type="checkbox" class="form-check-input" data-opt="${k}" ${on ? 'checked' : ''}> ${label}</label>`;
                const sel = (k, list, cur) =>
                    `<select class="form-select form-select-sm" data-opt="${k}">${Object.entries(list).map(([v, l]) => `<option value="${v}" ${v === cur ? 'selected' : ''}>${l}</option>`).join('')}</select>`;
                $('tbStrip').innerHTML = S.mode === 'detail' ?
                    `<span class="text-muted-custom">Tips: seret ≡ untuk memindah · klik judul untuk mengganti teks${V ? '' : ' · tarik garis kanan kolom untuk melebarkan'}.</span>` :
                    chk('tcol', V ? 'Baris total tiap angka' : 'Kolom total tiap angka', t.col) +
                    (S.rows.length === 2 ? chk('tsub', 'Subtotal per kelompok', t.sub) : '') +
                    chk('fill', 'Tampilkan periode kosong', o.fill_empty) + chk('hide', 'Sembunyikan baris kosong',
                        o.hide_empty) +
                    (V ? '' : sel('layout', {
                        by_value: 'Periode di kolom',
                        period_as_rows: 'Periode di baris'
                    }, o.layout)) +
                    sel('sort', {
                        label: 'Urut nama',
                        value_desc: 'Urut nilai terbesar'
                    }, o.sort) +
                    sel('sheet', {
                        none: 'Satu sheet',
                        branch: 'Sheet per MD',
                        year: 'Sheet per tahun'
                    }, o.sheet_by);
            }
            $('tbStrip').addEventListener('change', e => {
                const k = e.target.dataset.opt;
                if (!k) return;
                const v = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
                if (k === 'tcol') S.totals.col = v;
                else if (k === 'tsub') S.totals.sub = v;
                else if (k === 'fill') S.opts.fill_empty = v;
                else if (k === 'hide') S.opts.hide_empty = v;
                else if (k === 'layout') S.opts.layout = v;
                else if (k === 'sort') S.opts.sort = v;
                else if (k === 'sheet') S.opts.sheet_by = v;
                render();
            });

            // ---------- pratinjau hasil (otomatis) ----------
            const fillPh = t => t
                .replace(/\{periode\}/g, 'Januari – Maret 2026')
                .replace(/\{cabang\}/g, 'MD Cirebon')
                .replace(/\{tanggal_cetak\}/g, new Date().toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }))
                .replace(/\{pengguna\}/g, 'Admin');

            function doPreview() {
                const el = $('tbPreview');
                if (!el || !S) return;
                const strip = h => h.replace(/ id="[^"]*"/g, '')
                    .replace(/<input /g, '<input readonly tabindex="-1" ')
                    .replace(/<select/g, '<select disabled');
                const hdr = getLines('hdrLines').map(fillPh),
                    ftr = getLines('ftrLines').map(fillPh);
                el.innerHTML =
                    `<div class="tb-pv-h">${hdr.map(t => `<div>${esc(t)}</div>`).join('')}</div>${strip(tableHtml())}<div class="tb-pv-f">${ftr.map(t => `<div>${esc(t)}</div>`).join('')}</div>`;
            }

            function renderPreview() {
                cancelAnimationFrame(pvFrame);
                pvFrame = requestAnimationFrame(doPreview);
            }

            // ---------- info palet, riwayat (undo/redo), tampilan ----------
            const palChips = () => document.querySelectorAll('#palInfo .dnd-chip, #palNum .dnd-chip');

            function updateMeta() {
                $('tbCount').textContent = S.mode === 'detail' ?
                    `${S.columns.length}/20 kolom` :
                    `${S.rows.length}/2 kelompok · ${S.values.length}/15 angka`;
                palChips().forEach(c => {
                    const k = c.dataset.key;
                    const used = S.mode === 'detail' ? S.columns.some(x => x.key === k) :
                        (c.dataset.kind === 'dim' && S.rows.includes(k));
                    c.classList.toggle('pal-used', used);
                });
            }
            $('palSearch').addEventListener('input', e => {
                const q = e.target.value.trim().toLowerCase();
                palChips().forEach(c => c.classList.toggle('d-none', !!q && !(c.textContent.toLowerCase().includes(q) ||
                    c.dataset.key.toLowerCase().includes(q))));
            });

            const snapNow = () => JSON.stringify({
                s: S,
                h: getLines('hdrLines'),
                f: getLines('ftrLines')
            });

            function syncHistBtns() {
                $('btnUndo').disabled = hi <= 0;
                $('btnRedo').disabled = hi >= hist.length - 1;
            }

            function pushHist() {
                if (restoring) return;
                const sn = snapNow();
                if (hist[hi] === sn) return syncHistBtns();
                hist = hist.slice(0, hi + 1);
                hist.push(sn);
                if (hist.length > 60) hist.shift();
                hi = hist.length - 1;
                syncHistBtns();
            }

            function goHist(d) {
                const cur = snapNow();
                if (hist[hi] !== cur) { // perubahan teks yang belum tercatat
                    hist = hist.slice(0, hi + 1);
                    hist.push(cur);
                    hi = hist.length - 1;
                }
                const ni = hi + d;
                if (ni < 0 || ni >= hist.length) return syncHistBtns();
                hi = ni;
                const o = JSON.parse(hist[hi]);
                S = o.s;
                setLines('hdrLines', o.h);
                setLines('ftrLines', o.f);
                restoring = true;
                closePop();
                render();
                restoring = false;
                syncHistBtns();
            }
            $('btnUndo').addEventListener('click', () => goHist(-1));
            $('btnRedo').addEventListener('click', () => goHist(1));
            $('exportTemplateModal').addEventListener('keydown', e => {
                if (!(e.ctrlKey || e.metaKey)) return;
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
                const k = e.key.toLowerCase();
                if (k === 'z') {
                    e.preventDefault();
                    goHist(e.shiftKey ? 1 : -1);
                } else if (k === 'y') {
                    e.preventDefault();
                    goHist(1);
                }
            });

            const setSide = on => {
                $('tbWork').classList.toggle('side', on);
                $('btnPrevSide').querySelector('span').textContent = on ? 'Di bawah' : 'Di samping';
                ls.set('exp_prev_side', on ? '1' : '0');
            };
            setSide(ls.get('exp_prev_side') === '1');
            $('btnPrevSide').addEventListener('click', () => setSide(!$('tbWork').classList.contains('side')));

            // ---------- render + drag ----------
            function render() {
                $('tbCanvas').innerHTML = tableHtml();
                document.querySelectorAll('[data-mode]').forEach(b => b.classList.toggle('active', b.dataset
                    .mode === S.mode));
                document.querySelectorAll('[data-orient]').forEach(b => b.classList.toggle('active', b.dataset
                    .orient === S.orient));
                document.querySelectorAll('#palInfo .dnd-chip, #palNum .dnd-chip').forEach(c => c.classList.toggle(
                    'pal-off', S.mode === 'summary' && c.dataset.kind === 'misc'));
                renderStrip();
                makeSortables();
                renderPreview();
                updateMeta();
                pushHist();
            }
            const rerender = () => setTimeout(render, 0);

            function syncHead() {
                const V = isV();
                const pick = (box, sel) => box ? [...box.children].filter(el => el.matches(sel) || el.dataset.pal) : [];
                if (S.mode === 'detail') {
                    const els = V ? pick($('tbBody'), '.hrow') : pick($('tbHead'), '.tb-h');
                    const out = [];
                    els.forEach(el => {
                        if (el.dataset.pal) {
                            const k = el.dataset.key;
                            if (!out.some(c => c.key === k) && out.length < 20) out.push({
                                key: k,
                                label: FIELDS[k][0],
                                width: FIELDS[k][2]
                            });
                        } else out.push(S.columns[+el.dataset.idx]);
                    });
                    S.columns = out.filter(Boolean);
                } else {
                    const dims = [],
                        vals = [];
                    const add = el => {
                        if (el.dataset.pal) {
                            const k = el.dataset.key,
                                kind = el.dataset.kind;
                            if (kind === 'dim' && !dims.includes(k) && dims.length < 2) dims.push(k);
                            else if (kind === 'val' && vals.length < 15) vals.push(defVal(k));
                        } else if (el.dataset.kind === 'dim') dims.push(S.rows[+el.dataset.idx]);
                        else if (el.dataset.kind === 'val') vals.push(S.values[+el.dataset.idx]);
                    };
                    if (V) {
                        pick($('tbDims'), '.hrow').forEach(add);
                        pick($('tbVals'), '.hgrp').forEach(add);
                    } else pick($('tbHead'), '.tb-h').forEach(add);
                    S.rows = dims.filter(Boolean);
                    S.values = vals.filter(Boolean);
                }
                rerender();
            }

            function makeSortables() {
                sorts.forEach(s => s.destroy());
                sorts = [];
                const V = isV();
                const palPut = kinds => (to, from, el) => !!el.dataset.pal && canDrop(el.dataset.key, el.dataset.kind) &&
                    (!kinds || kinds.includes(el.dataset.kind));
                const mk = (el, opts) => {
                    if (el) sorts.push(Sortable.create(el, Object.assign({
                        animation: 150,
                        ghostClass: 'tb-ghost',
                        filter: '.resizer, input, button, select',
                        preventOnFilter: false,
                        onAdd: syncHead,
                        onUpdate: syncHead
                    }, opts)));
                };
                if (!V) {
                    mk($('tbHead'), {
                        group: {
                            name: 'h_head',
                            pull: false,
                            put: palPut()
                        },
                        draggable: '.tb-h'
                    });
                    const body = $('tbBody');
                    if (body && S.totals.row) sorts.push(Sortable.create(body, {
                        draggable: '.tot-row',
                        handle: '.tot-grip',
                        animation: 150,
                        onUpdate: e => {
                            S.totals.position = body.firstElementChild === e.item ? 'top' : 'bottom';
                            rerender();
                        }
                    }));
                } else if (S.mode === 'detail') {
                    mk($('tbBody'), {
                        group: {
                            name: 'v_col',
                            pull: false,
                            put: palPut()
                        },
                        draggable: '.hrow',
                        handle: '.tb-grip'
                    });
                } else {
                    mk($('tbDims'), {
                        group: {
                            name: 'v_dim',
                            pull: false,
                            put: palPut(['dim'])
                        },
                        draggable: '.hrow',
                        handle: '.tb-grip'
                    });
                    mk($('tbVals'), {
                        group: {
                            name: 'v_val',
                            pull: false,
                            put: palPut(['val'])
                        },
                        draggable: '.hgrp',
                        handle: '.tb-grip'
                    });
                }
            }

            // ---------- interaksi di dalam tabel ----------
            const canvas = $('tbCanvas');
            canvas.addEventListener('input', e => {
                const d = e.target.dataset;
                if (d.lab !== undefined) S.columns[+d.lab].label = e.target.value;
                else if (d.labv !== undefined) S.values[+d.labv].label = e.target.value;
                else if (e.target.id === 'totLab') S.totals.label = e.target.value;
                else return;
                renderPreview();
            });
            canvas.addEventListener('keydown', e => {
                if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.target.blur();
            });
            canvas.addEventListener('click', e => {
                const b = e.target.closest(
                    '[data-rm],[data-rmd],[data-rmv],[data-gear],[data-addtot],[data-rmtot],[data-flip]'
                    );
                if (!b) return;
                const d = b.dataset;
                if (d.rm !== undefined) S.columns.splice(+d.rm, 1);
                else if (d.rmd !== undefined) S.rows.splice(+d.rmd, 1);
                else if (d.rmv !== undefined) {
                    S.values.splice(+d.rmv, 1);
                    closePop();
                } else if (d.gear !== undefined) return openPop(+d.gear, b);
                else if (d.addtot !== undefined) S.totals.row = true;
                else if (d.rmtot !== undefined) S.totals.row = false;
                else if (d.flip !== undefined) S.totals.position = S.totals.position === 'top' ? 'bottom' :
                    'top';
                render();
            });
            canvas.addEventListener('change', e => {
                if (e.target.classList.contains('tb-addsel') && e.target.value) {
                    tryAdd(e.target.value);
                }
            });
            canvas.addEventListener('mousedown', e => { // tarik garis kanan kolom untuk melebarkan
                const rs = e.target.closest('.resizer');
                if (!rs) return;
                e.preventDefault();
                e.stopPropagation();
                const i = +rs.dataset.rs,
                    th = rs.parentElement,
                    tbl = th.closest('table');
                const x0 = e.clientX,
                    w0 = th.offsetWidth,
                    t0 = parseFloat(tbl.style.width) || tbl.offsetWidth;
                const move = ev => {
                    const w = Math.max(64, w0 + ev.clientX - x0);
                    th.style.width = w + 'px';
                    tbl.style.width = (t0 + w - w0) + 'px';
                    S.columns[i].width = Math.max(5, Math.round(w / 7.2));
                };
                const up = () => {
                    document.removeEventListener('mousemove', move);
                    document.removeEventListener('mouseup', up);
                    render();
                };
                document.addEventListener('mousemove', move);
                document.addEventListener('mouseup', up);
            });

            // ---------- panel pengaturan angka (⚙) ----------
            function closePop() {
                $('tbPop').classList.add('d-none');
                popVi = null;
            }

            function openPop(vi, anchor) {
                const v = S.values[vi];
                if (!v) return;
                popVi = vi;
                $('popTitle').textContent = 'Atur: ' + v.label;
                $('popAgg').value = v.agg;
                $('popPer').value = v.per;
                $('popSub').value = v.sub || '';
                $('popFmt').value = v.fmt || 'dec';
                $('popSub').disabled = !['month', 'quarter', 'semester'].includes(v.per);
                const pop = $('tbPop'),
                    sheet = $('tbSheet').getBoundingClientRect(),
                    a = anchor.getBoundingClientRect();
                pop.classList.remove('d-none');
                pop.style.left = Math.max(8, Math.min(a.left - sheet.left, sheet.width - 266)) + 'px';
                pop.style.top = (a.bottom - sheet.top + 6) + 'px';
            }
            ['popAgg', 'popPer', 'popSub', 'popFmt'].forEach(id => $(id).addEventListener('change', () => {
                const v = S.values[popVi];
                if (!v) return;
                v.agg = $('popAgg').value;
                v.per = $('popPer').value;
                v.sub = $('popSub').value;
                v.fmt = $('popFmt').value;
                if (!['month', 'quarter', 'semester'].includes(v.per)) v.sub = '';
                const vi = popVi;
                render();
                const g = $('tbCanvas').querySelector(`[data-gear="${vi}"]`);
                if (g) openPop(vi, g);
            }));
            document.addEventListener('mousedown', e => {
                if (!e.target.closest('#tbPop, [data-gear]')) closePop();
            });

            // ---------- mode, orientasi & contoh ----------
            document.querySelectorAll('[data-mode]').forEach(b => b.addEventListener('click', () => {
                S.mode = b.dataset.mode;
                closePop();
                render();
            }));
            document.querySelectorAll('[data-orient]').forEach(b => b.addEventListener('click', () => {
                S.orient = b.dataset.orient;
                if (S.orient === 'vertical') S.opts.layout = 'by_value';
                closePop();
                render();
            }));

            const toSummary = () => {
                S.mode = 'summary';
                S.opts.layout = 'by_value';
            };
            const PRESETS = {
                buku: () => {
                    S.mode = 'detail';
                    S.orient = 'horizontal';
                    S.columns = defCols();
                    S.totals.row = true;
                },
                tegak: () => {
                    S.mode = 'detail';
                    S.orient = 'vertical';
                    S.columns = defCols();
                    S.totals.row = true;
                },
                lengkap: () => {
                    S.mode = 'detail';
                    S.totals.row = true;
                    S.columns = ['date', 'description', 'pic', 'church', 'pp', 'iuran', 'dana_misi',
                            'bunga', 'lain_debit', 'bpjs', 'iuran_mdf', 'tahaka', 'pajak_bunga', 'admin',
                            'lain_kredit', 'balance'
                        ]
                        .filter(k => FIELDS[k])
                        .map(k => ({
                            key: k,
                            label: FIELDS[k][0],
                            width: FIELDS[k][2]
                        }));
                },
                rekap: () => {
                    toSummary();
                    S.rows = ['church'];
                    S.values = [Object.assign(defVal('pp'), {
                        sub: 'quarter'
                    })];
                    S.totals.row = true;
                    S.totals.col = true;
                },
                saldo: () => {
                    toSummary();
                    S.rows = [];
                    S.values = [Object.assign(defVal('balance'), {
                        per: 'month',
                        agg: 'last'
                    })];
                    S.totals.row = false;
                    S.totals.col = false;
                },
                count: () => {
                    toSummary();
                    S.rows = ['category'];
                    S.values = [Object.assign(defVal(FIELDS.debit ? 'debit' : NUMS[0]), {
                        agg: 'count',
                        label: 'Jumlah transaksi',
                        per: 'month'
                    })];
                    S.totals.row = true;
                    S.totals.col = true;
                }
            };
            document.querySelectorAll('[data-preset]').forEach(b => b.addEventListener('click', () => {
                PRESETS[b.dataset.preset]();
                closePop();
                render();
            }));

            // ---------- panduan (sisi kanan) ----------
            const split = $('tbSplit'),
                guide = $('tbGuide');
            const gw = +ls.get('exp_guide_w');
            if (gw >= 280) split.style.setProperty('--gw', gw + 'px');
            const setGuide = on => {
                split.classList.toggle('no-guide', !on);
                $('btnGuide').classList.toggle('active', on);
                ls.set('exp_guide', on ? '1' : '0');
            };
            setGuide(ls.get('exp_guide') !== '0');
            $('btnGuide').addEventListener('click', () => setGuide(split.classList.contains('no-guide')));

            $('tbGutter').addEventListener('mousedown', e => {
                e.preventDefault();
                const x0 = e.clientX,
                    w0 = guide.offsetWidth,
                    g = $('tbGutter');
                g.classList.add('drag');
                document.body.style.userSelect = 'none';
                const move = ev => {
                    const w = Math.min(Math.max(280, w0 + x0 - ev.clientX), split.offsetWidth - 380);
                    split.style.setProperty('--gw', w + 'px');
                };
                const up = () => {
                    document.removeEventListener('mousemove', move);
                    document.removeEventListener('mouseup', up);
                    g.classList.remove('drag');
                    document.body.style.userSelect = '';
                    ls.set('exp_guide_w', guide.offsetWidth);
                };
                document.addEventListener('mousemove', move);
                document.addEventListener('mouseup', up);
            });

            // kamus kolom (dibuat otomatis dari daftar field)
            const gdChip = k =>
                `<button type="button" class="gd-chip ${kindOf(k)}" data-addkey="${k}" title="Klik untuk menambah ke tabel">${esc(FIELDS[k][0])}<code>${k}</code></button>`;
            $('gdFields').innerHTML =
                `<div class="mb-1 fw-semibold">Info</div>${INFO.map(gdChip).join('')}<div class="mt-2 mb-1 fw-semibold">Angka</div>${NUMS.map(gdChip).join('')}`;
            $('gdFields').addEventListener('click', e => {
                const b = e.target.closest('[data-addkey]');
                if (b) tryAdd(b.dataset.addkey);
            });

            // cari di panduan
            $('gdSearch').addEventListener('input', e => {
                const q = e.target.value.trim().toLowerCase();
                guide.querySelectorAll('.gd-sec').forEach(sec => {
                    const hit = !q || sec.textContent.toLowerCase().includes(q);
                    sec.classList.toggle('gd-hide', !hit);
                    if (q && hit) sec.open = true;
                });
            });

            // ---------- simpan / API ----------
            function collect() {
                const hdr = getLines('hdrLines'),
                    ftr = getLines('ftrLines');
                const subOk = v => (['month', 'quarter', 'semester'].includes(v.per) && v.sub) ? v.sub : null;
                const tot = S.totals;
                return {
                    name: $('tplName').value.trim(),
                    is_shared: ADMIN && $('tplShared').checked,
                    config: {
                        mode: S.mode,
                        orientation: S.orient,
                        header: {
                            lines: hdr
                        },
                        footer: {
                            enabled: ftr.length > 0,
                            lines: ftr
                        },
                        columns: S.columns.map(c => ({
                            key: c.key,
                            label: (c.label || '').trim() || FIELDS[c.key][0],
                            width: c.width ? +c.width : null
                        })),
                        rows: S.rows,
                        values: S.values.map(v => ({
                            key: v.key,
                            label: (v.label || '').trim() || FIELDS[v.key][0],
                            agg: v.agg,
                            per: v.per,
                            sub: subOk(v),
                            fmt: v.fmt || 'dec'
                        })),
                        layout: S.opts.layout,
                        sort: S.opts.sort,
                        sheet_by: S.opts.sheet_by,
                        fill_empty: S.opts.fill_empty,
                        hide_empty: S.opts.hide_empty,
                        totals: {
                            enabled: tot.row,
                            row: tot.row,
                            col: tot.col,
                            sub: tot.sub,
                            position: tot.position,
                            label: (tot.label || '').trim() || 'TOTAL'
                        }
                    }
                };
            }

            async function api(method, url, body) {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: body ? JSON.stringify(body) : undefined
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join('\n') : (data
                    .message || 'Terjadi kesalahan'));
                return data;
            }

            const renderSelect = () => {
                $('tplSelect').innerHTML = '<option value="">+ Template baru</option>' + templates.map(t =>
                    `<option value="${t.id}" ${S && t.id === S.id ? 'selected' : ''}>${t.is_shared ? '★ ' : ''}${esc(t.name)}</option>`
                    ).join('');
            };

            async function save() {
                const p = collect();
                if (!p.name) throw new Error('Nama template wajib diisi.');
                if (S.mode === 'detail' && !S.columns.length) throw new Error('Tambahkan minimal satu kolom.');
                if (S.mode === 'summary' && !S.values.length) throw new Error('Tambahkan minimal satu angka.');
                const edit = S.id && S
                .can_edit; // template bagikan milik orang lain tidak bisa diubah -> tersimpan sebagai salinan
                const res = edit ? await api('PUT', `${base}/${S.id}`, p) : await api('POST', base, p);
                templates = await api('GET', base);
                fromTemplate(templates.find(t => t.id === res.template.id));
                renderSelect();
                return S;
            }

            $('tplSelect').addEventListener('change', e => {
                const t = templates.find(x => x.id == e.target.value);
                fromTemplate(t || blank());
            });
            $('btnTplSave').addEventListener('click', async () => {
                try {
                    await save();
                } catch (e) {
                    alert(e.message);
                }
            });
            $('btnTplExport').addEventListener('click', async () => {
                try {
                    const t = await save();
                    runForm.elements.type.value = 'template';
                    runForm.elements.template_id.value = t.id;
                    runForm.submit();
                    modal.hide();
                } catch (e) {
                    alert(e.message);
                }
            });
            $('btnTplDelete').addEventListener('click', async () => {
                if (!S.id || !confirm('Hapus template ini?')) return;
                try {
                    await api('DELETE', `${base}/${S.id}`);
                    templates = await api('GET', base);
                    fromTemplate(blank());
                    renderSelect();
                } catch (e) {
                    alert(e.message);
                }
            });

            // ---------- dropdown ----------
            fromTemplate(blank());
            document.querySelectorAll('[data-export-type]').forEach(a => a.addEventListener('click', async e => {
                e.preventDefault();
                if (a.dataset.modal) {
                    try {
                        templates = await api('GET', base);
                    } catch (err) {
                        templates = [];
                    }
                    fromTemplate(blank());
                    renderSelect();
                    modal.show();
                    return;
                }
                runForm.elements.type.value = a.dataset.exportType;
                runForm.elements.template_id.value = '';
                runForm.submit();
            }));
        });
    </script>
@endverbatim