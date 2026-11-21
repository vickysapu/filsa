@extends('layouts.public')

@section('title', 'Kontak & Lokasi - Gereja Filadelfia')

@push('styles')
<style>
    .contact-page .page-hero {
        padding-bottom: 4.5rem;
    }

    .contact-layout {
        grid-template-columns: minmax(0, 0.82fr) minmax(0, 1fr) !important;
        align-items: start;
        gap: clamp(2.5rem, 7vw, 7rem) !important;
    }

    .contact-details {
        gap: 0 !important;
        border-top: 1px solid var(--zaitun-border);
    }

    .contact-detail {
        padding: 1.15rem 0;
        border-bottom: 1px solid var(--zaitun-border);
        gap: 1rem !important;
    }

    .contact-symbol {
        width: 1.5rem !important;
        height: 1.5rem !important;
        flex: 0 0 1.5rem !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--zaitun-gold) !important;
        font-size: 1.2rem !important;
    }

    .contact-form-panel {
        padding: clamp(1.5rem, 3vw, 2.5rem) !important;
        border: 1px solid var(--zaitun-border) !important;
        border-top: 3px solid var(--zaitun-deep) !important;
        border-radius: 2px !important;
        background: #FBFAF6 !important;
        box-shadow: none !important;
    }

    .contact-form-panel h2 {
        font-size: 1.8rem !important;
        font-weight: 500 !important;
    }

    .contact-page .form-control-olive {
        min-height: 48px;
        border-radius: 2px;
        background: #fff;
    }

    .contact-page textarea.form-control-olive {
        min-height: 140px;
    }

    @media (max-width: 800px) {
        .contact-layout {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('content')

    <!-- Subpage Hero Header -->
    <header class="page-hero public-page-hero public-page-hero--contact">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span style="color: var(--zaitun-primary);">Kontak & Lokasi</span>
            </nav>
            <span class="page-kicker">Pintu Kami Terbuka</span>
            <h1 class="page-hero-title">Hubungi Kami</h1>
            <p class="page-hero-desc">
                Kami siap menyambut dan melayani Anda. Jangan ragu menghubungi sekretariat untuk permohonan doa, konseling, atau informasi kehadiran ibadah.
            </p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi gereja</span>
    </header>

    <!-- Main Contact Content Section -->
    <section class="content-container contact-page">
        <div class="contact-layout lux-grid-2" style="display: grid; grid-template-columns: 1fr 1.15fr; gap: 4rem;">
            <!-- Left Info Column -->
            <div class="fade-in">
                <span class="section-kicker">
                    <i class="ri-information-line"></i> Sekretariat Jemaat
                </span>
                <h2 class="section-headline" style="margin-bottom: 2rem;">Mari Terhubung Bersama Kami</h2>

                <div class="contact-details" style="display: flex; flex-direction: column; gap: 1.8rem; margin-bottom: 3rem;">
                    <!-- Alamat -->
                    <div class="contact-detail" style="display: flex; gap: 1.2rem; align-items: flex-start;">
                        <div class="contact-symbol" style="width: 52px; height: 52px; background: rgba(82, 110, 59, 0.1); color: var(--zaitun-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                            <i class="ri-map-pin-2-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">Gedung Gereja & Sekretariat</strong>
                            <p style="color: var(--zaitun-text-secondary); line-height: 1.7; font-size: 0.95rem; margin: 0;">
                                Jl. Inhoftank No. 12, Kebon Lega, Kec. Bojongloa Kidul,<br>
                                Kota Bandung, Jawa Barat 40235
                            </p>
                        </div>
                    </div>

                    <!-- Telepon & WhatsApp -->
                    <div class="contact-detail" style="display: flex; gap: 1.2rem; align-items: flex-start;">
                        <div class="contact-symbol" style="width: 52px; height: 52px; background: rgba(192, 138, 62, 0.12); color: var(--zaitun-gold); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                            <i class="ri-phone-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">Telepon & WhatsApp</strong>
                            <p style="color: var(--zaitun-text-secondary); line-height: 1.7; font-size: 0.95rem; margin: 0;">
                                Kantor: (022) 1234-5678<br>
                                Hotline Jemaat: 0812-3456-7890 (WhatsApp Aktif)
                            </p>
                        </div>
                    </div>

                    <!-- Email Resmi -->
                    <div class="contact-detail" style="display: flex; gap: 1.2rem; align-items: flex-start;">
                        <div class="contact-symbol" style="width: 52px; height: 52px; background: rgba(52, 67, 44, 0.1); color: var(--zaitun-deep); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                            <i class="ri-mail-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">Surat Elektronik (Email)</strong>
                            <p style="color: var(--zaitun-text-secondary); line-height: 1.7; font-size: 0.95rem; margin: 0;">
                                sekretariat@gerejafiladelfia.com<br>
                                doa@gerejafiladelfia.com (Khusus Pokok Doa)
                            </p>
                        </div>
                    </div>

                    <!-- Jam Kerja -->
                    <div class="contact-detail" style="display: flex; gap: 1.2rem; align-items: flex-start;">
                        <div class="contact-symbol" style="width: 52px; height: 52px; background: rgba(200, 90, 50, 0.1); color: var(--zaitun-terracotta); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                            <i class="ri-time-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">Layanan Sekretariat</strong>
                            <p style="color: var(--zaitun-text-secondary); line-height: 1.7; font-size: 0.95rem; margin: 0;">
                                Senin – Jumat: 08:30 – 17:00 WIB<br>
                                Sabtu: 08:30 – 13:00 WIB<br>
                                Minggu: Pelayanan Hari Ibadah
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Google Maps Directions Link -->
                <div>
                    <a href="https://maps.google.com/?q=Bandung" target="_blank" class="btn-primary-olive">
                        <i class="ri-direction-line"></i>
                        <span>Buka Petunjuk Arah di Google Maps</span>
                    </a>
                </div>
            </div>

            <!-- Right Form Column -->
            <div class="fade-in">
                <div class="editorial-card contact-form-panel">
                    <h2 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.5rem;">
                        Kirim Pesan & Pokok Doa
                    </h2>
                    <p style="font-size: 0.95rem; color: var(--zaitun-text-secondary); margin-bottom: 2rem;">
                        Isi formulir di bawah ini. Tim pastoral kami akan segera menanggapi pesan Anda dengan penuh kasih.
                    </p>

                    <form onsubmit="event.preventDefault(); alert('Pesan Anda telah terkirim kepada tim pastoral Gereja Filadelfia. Terima kasih!'); this.reset();" style="display: flex; flex-direction: column; gap: 1.4rem;">
                        @csrf
                        <div>
                            <label class="form-label-olive">Nama Lengkap <span style="color: var(--zaitun-terracotta);">*</span></label>
                            <input type="text" name="nama" class="form-control-olive" placeholder="Contoh: Yohanes Pratama" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="lux-grid-2">
                            <div>
                                <label class="form-label-olive">Alamat Email <span style="color: var(--zaitun-terracotta);">*</span></label>
                                <input type="email" name="email" class="form-control-olive" placeholder="email@domain.com" required>
                            </div>
                            <div>
                                <label class="form-label-olive">Nomor WhatsApp</label>
                                <input type="tel" name="telepon" class="form-control-olive" placeholder="081234567890">
                            </div>
                        </div>

                        <div>
                            <label class="form-label-olive">Keperluan / Kategori Pesan <span style="color: var(--zaitun-terracotta);">*</span></label>
                            <select name="subjek" class="form-control-olive" required>
                                <option value="">Pilih Kategori Permohonan</option>
                                <option value="doa">Permohonan Doa Syafaat</option>
                                <option value="konseling">Konseling Pastoral / Konsultasi</option>
                                <option value="ibadah">Informasi Kehadiran Ibadah</option>
                                <option value="pelayanan">Bergabung dalam Pelayanan</option>
                                <option value="lainnya">Pertanyaan Umum Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label-olive">Isi Pesan / Pokok Pergumulan <span style="color: var(--zaitun-terracotta);">*</span></label>
                            <textarea name="pesan" rows="5" class="form-control-olive" placeholder="Tuliskan pesan atau pokok doa yang ingin didukung oleh tim pastoral kami..." required></textarea>
                        </div>

                        <div>
                            <button type="submit" class="btn-terracotta-cta" style="width: 100%; justify-content: center; padding: 0.95rem;">
                                <i class="ri-send-plane-fill"></i>
                                <span>Kirim Pesan Sekarang</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
