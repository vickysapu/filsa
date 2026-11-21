@extends('layouts.public')

@section('title', 'Tentang Kami - Gereja Filadelfia')

@push('styles')
<style>
    .belief-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 3.5rem;
        max-width: 980px;
        margin: 0 auto;
        border-top: 1px solid var(--zaitun-border);
    }

    .belief-grid .editorial-card {
        min-height: 210px;
        padding: 2rem 0;
        border: 0;
        border-bottom: 1px solid var(--zaitun-border);
        border-radius: 0;
        background: transparent;
    }

    .leadership-grid {
        max-width: 980px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 2.5rem;
    }

    .leadership-grid .editorial-card {
        padding: 0 0 1.5rem;
        border: 0;
        border-bottom: 1px solid var(--zaitun-border);
        border-radius: 0;
        background: transparent;
        text-align: left !important;
    }

    .about-page .page-hero {
        padding-bottom: 4.5rem;
    }

    .about-vision-grid {
        grid-template-columns: minmax(0, 0.88fr) minmax(380px, 1fr) !important;
        align-items: start !important;
        gap: clamp(2.5rem, 6vw, 6rem) !important;
    }

    .about-principles {
        gap: 0 !important;
        margin-top: 2rem;
        border-top: 1px solid var(--zaitun-border);
    }

    .about-principle {
        padding: 1rem 0 !important;
        border: 0 !important;
        border-bottom: 1px solid var(--zaitun-border) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        align-items: flex-start !important;
    }

    .about-principle > div:first-child {
        display: none !important;
    }

    .about-vision-image {
        overflow: hidden;
        border-radius: 2px !important;
        border: 0 !important;
        box-shadow: none !important;
        aspect-ratio: 4 / 5;
        max-height: 640px;
    }

    .about-vision-image img {
        display: block;
        width: 100%;
        height: 100% !important;
        object-fit: cover;
    }

    .about-image-caption {
        padding: 0.55rem 0.7rem;
        background: var(--zaitun-deep-dark);
        color: var(--zaitun-sand);
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .about-beliefs {
        background: var(--zaitun-cream);
        padding: 5.5rem 2rem !important;
    }

    .belief-grid .editorial-card > div:first-child {
        display: none !important;
    }

    .belief-grid .editorial-card h3 {
        font-size: 1.45rem !important;
        font-weight: 500 !important;
    }

    .leadership-grid .editorial-card > div:first-child {
        display: none !important;
    }

    .leadership-grid .editorial-card {
        padding: 1.25rem 0 !important;
        border-top: 1px solid var(--zaitun-border);
        border-bottom: 1px solid var(--zaitun-border);
    }

    .leadership-grid .editorial-card h3 {
        font-size: 1.25rem !important;
        font-weight: 500 !important;
    }

    .leadership-grid .editorial-card p:nth-of-type(1) {
        font-size: 0.72rem !important;
        letter-spacing: 0.1em !important;
    }

    .about-history {
        border-top: 1px solid var(--zaitun-border);
        padding: 5.5rem 2rem !important;
    }

    .about-timeline {
        gap: 0 !important;
        border-top: 1px solid var(--zaitun-border);
    }

    .about-timeline-item {
        gap: 1.5rem !important;
        padding: 1.5rem 0;
        border-bottom: 1px solid var(--zaitun-border);
    }

    .about-timeline-year {
        width: 90px !important;
        height: auto !important;
        min-height: 0;
        padding-top: 0.3rem;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--zaitun-primary) !important;
        box-shadow: none !important;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.5rem !important;
        justify-content: flex-start !important;
    }

    .about-timeline-item .editorial-card {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }

    .about-invitation {
        padding-top: 2rem;
    }

    .about-invitation > div {
        background: var(--zaitun-deep-dark) !important;
        border-radius: 2px !important;
        padding: clamp(2.5rem, 6vw, 4.5rem) 2rem !important;
    }

    @media (max-width: 800px) {
        .belief-grid,
        .leadership-grid,
        .about-vision-grid {
            grid-template-columns: 1fr !important;
        }

        .about-vision-image {
            max-height: 400px;
            aspect-ratio: 16 / 10;
        }
    }

    @media (max-width: 520px) {
        .about-timeline-item {
            gap: 0.75rem !important;
        }

        .about-timeline-year {
            width: 55px !important;
            font-size: 1.15rem !important;
        }
    }
</style>
@endpush

@section('content')

    <!-- Subpage Hero Header -->
    <div class="about-page">
    <header class="page-hero public-page-hero public-page-hero--about">
        <div class="page-hero-container fade-in">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('public.home') }}">Beranda</a>
                <i class="ri-arrow-right-s-line"></i>
                <span style="color: var(--zaitun-primary);">Tentang Kami</span>
            </nav>
            <span class="page-kicker">Profil & Sejarah</span>
            <h1 class="page-hero-title">Gereja Filadelfia</h1>
            <p class="page-hero-desc">
                "Dan kepada malaikat jemaat di Filadelfia tulislah: ... Aku mengetahui pekerjaanmu. Lihatlah, Aku telah membuka pintu bagimu yang tidak dapat ditutup oleh seorangpun." — <strong>Wahyu 3:7-8</strong>
            </p>
        </div>
        <span class="page-image-note">Foto ilustrasi · bukan dokumentasi Gereja Filadelfia</span>
    </header>

    <!-- Vision & Mission Section -->
    <section class="content-container about-vision">
        <div class="about-vision-grid lux-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div class="fade-in">
                <span class="section-kicker">
                    <i class="ri-eye-line"></i> Visi Kami
                </span>
                <h2 class="section-headline" style="margin-bottom: 1.2rem;">
                    Menjadi Gereja yang Berakar dan Berbuah Lebat
                </h2>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--zaitun-text-secondary); margin-bottom: 2rem;">
                    Gereja Filadelfia berkomitmen menjadi wadah rohani yang sehat bagi pertumbuhan iman jemaat, menghadirkan kabar baik Kristus yang utuh, dan menjadi saluran berkat yang nyata bagi bangsa dan sesama.
                </p>

                <div class="about-principles" style="display: flex; flex-direction: column; gap: 1.2rem;">
                    <!-- Item 1 -->
                    <div class="editorial-card about-principle" style="padding: 1.4rem; display: flex; align-items: center; gap: 1.2rem;">
                        <div style="width: 52px; height: 52px; background: rgba(82, 110, 59, 0.12); color: var(--zaitun-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-book-open-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.2rem;">Pengajaran Firman yang Murni</strong>
                            <p style="font-size: 0.9rem; color: var(--zaitun-text-secondary); margin: 0;">Berpusat teguh pada kebenaran Alkitab dan Kristus sebagai kepala gereja.</p>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="editorial-card about-principle" style="padding: 1.4rem; display: flex; align-items: center; gap: 1.2rem;">
                        <div style="width: 52px; height: 52px; background: rgba(192, 138, 62, 0.14); color: var(--zaitun-gold); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-heart-3-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.2rem;">Kasih Persaudaraan Sejati</strong>
                            <p style="font-size: 0.9rem; color: var(--zaitun-text-secondary); margin: 0;">Membangun persekutuan yang saling menerima, mengampuni, dan menguatkan.</p>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="editorial-card about-principle" style="padding: 1.4rem; display: flex; align-items: center; gap: 1.2rem;">
                        <div style="width: 52px; height: 52px; background: rgba(200, 90, 50, 0.12); color: var(--zaitun-terracotta); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-global-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--zaitun-deep-dark); font-size: 1.05rem; display: block; margin-bottom: 0.2rem;">Misi & Transformasi Sosial</strong>
                            <p style="font-size: 0.9rem; color: var(--zaitun-text-secondary); margin: 0;">Menghidupi amanat agung dengan melayani masyarakat sekitar.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fade-in">
                <div class="about-vision-image" style="border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-card); border: 6px solid #FFFFFF;">
                    <img src="https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=800" alt="Foto ilustrasi suasana ibadah, bukan dokumentasi Gereja Filadelfia" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    <span class="about-image-caption">Foto ilustrasi · bukan dokumentasi gereja</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Grid Section -->
    <section class="about-beliefs" style="background: var(--zaitun-cream); padding: 6rem 2rem;">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div style="text-align: center; max-width: 650px; margin: 0 auto 3.5rem;" class="fade-in">
                <span class="section-kicker">
                    <i class="ri-shield-star-line"></i> Fondasi Teologis
                </span>
                <h2 class="section-headline">Apa yang Kami Percayai</h2>
                <p class="section-lead" style="margin: 0 auto;">
                    Prinsip-prinsip iman yang menjadi pedoman dalam setiap pengajaran, ibadah, dan pelayanan kami.
                </p>
            </div>

            <div class="belief-grid">
                <div class="editorial-card fade-in">
                    <div style="width: 50px; height: 50px; background: rgba(82, 110, 59, 0.1); color: var(--zaitun-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.2rem;">
                        <i class="ri-bible-line"></i>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem;">Otoritas Alkitab</h3>
                    <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                        Kami percaya bahwa Alkitab adalah Firman Allah yang diilhamkan tanpa salah, menjadi pedoman mutlak iman dan praktik hidup sehari-hari.
                    </p>
                </div>

                <div class="editorial-card fade-in" style="transition-delay: 0.1s;">
                    <div style="width: 50px; height: 50px; background: rgba(192, 138, 62, 0.12); color: var(--zaitun-gold); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.2rem;">
                        <i class="ri-hands-line"></i>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem;">Doa & Keintiman Rohani</h3>
                    <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                        Doa adalah napas kehidupan umat beriman. Kami percaya doa yang dinaikkan dengan iman membawa kuasa dan pemulihan nyata.
                    </p>
                </div>

                <div class="editorial-card fade-in" style="transition-delay: 0.2s;">
                    <div style="width: 50px; height: 50px; background: rgba(52, 67, 44, 0.1); color: var(--zaitun-deep); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.2rem;">
                        <i class="ri-team-line"></i>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem;">Kesatuan Tubuh Kristus</h3>
                    <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                        Setiap anggota jemaat adalah bagian penting dari tubuh Kristus yang saling melengkapi dalam berbagai karunia rohani.
                    </p>
                </div>

                <div class="editorial-card fade-in" style="transition-delay: 0.3s;">
                    <div style="width: 50px; height: 50px; background: rgba(200, 90, 50, 0.1); color: var(--zaitun-terracotta); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.2rem;">
                        <i class="ri-hand-heart-line"></i>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem;">Pelayanan Tanpa Syarat</h3>
                    <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                        Melayani sesama dengan ketulusan hati seperti Kristus yang telah terlebih dahulu melayani dan memberikan nyawa-Nya bagi kita.
                    </p>
                </div>

                <div class="editorial-card fade-in" style="transition-delay: 0.4s;">
                    <div style="width: 50px; height: 50px; background: rgba(82, 110, 59, 0.1); color: var(--zaitun-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.2rem;">
                        <i class="ri-earth-line"></i>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem;">Amanat Agung Penginjilan</h3>
                    <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                        Kami berkomitmen memberitakan kabar sukacita keselamatan kepada segala bangsa, dimulai dari lingkungan terdekat kami.
                    </p>
                </div>

                <div class="editorial-card fade-in" style="transition-delay: 0.5s;">
                    <div style="width: 50px; height: 50px; background: rgba(192, 138, 62, 0.12); color: var(--zaitun-gold); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.2rem;">
                        <i class="ri-fire-line"></i>
                    </div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.6rem;">Pekerjaan Roh Kudus</h3>
                    <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                        Kami mengandalkan bimbingan, penghiburan, dan kuasa Roh Kudus yang memperlengkapi setiap orang percaya untuk hidup berbuah.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Leadership Section -->
    <section class="content-container about-leadership">
        <div style="text-align: center; max-width: 650px; margin: 0 auto 3.5rem;" class="fade-in">
            <span class="section-kicker">
                <i class="ri-team-line"></i> Kepemimpinan Gembala
            </span>
            <h2 class="section-headline">Pelayan Sidang & Pengurus</h2>
            <p class="section-lead" style="margin: 0 auto;">
                Para hamba Tuhan yang melayani dengan kerendahan hati dalam menggembalakan dan mengarahkan jemaat.
            </p>
        </div>

        <div class="leadership-grid">
            <!-- Leader 1 -->
            <div class="editorial-card fade-in" style="text-align: center;">
                <div style="width: 100%; max-width: 220px; aspect-ratio: 4 / 5; margin: 0 auto 1.5rem; border-radius: 3px; overflow: hidden; border: 0;">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400" alt="Pdt. Hendra Setiawan" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.3rem;">Pdt. Hendra Setiawan, M.Th</h3>
                <p style="color: var(--zaitun-primary); font-weight: 700; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1rem;">Gembala Sidang</p>
                <p style="color: var(--zaitun-text-secondary); font-size: 0.92rem; line-height: 1.65;">
                    Melayani jemaat sejak berdirinya gereja dengan hati gembala yang mengasihi, membina pemuridan, dan membagikan firman Tuhan.
                </p>
            </div>

            <!-- Leader 2 -->
            <div class="editorial-card fade-in" style="text-align: center; transition-delay: 0.15s;">
                <div style="width: 100%; max-width: 220px; aspect-ratio: 4 / 5; margin: 0 auto 1.5rem; border-radius: 3px; overflow: hidden; border: 0;">
                    <img src="https://images.unsplash.com/photo-1573496359-09a6d975dd5f?w=400" alt="Pdm. Maria Setiawan" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.3rem;">Pdm. Maria Setiawan, S.Psi</h3>
                <p style="color: var(--zaitun-primary); font-weight: 700; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1rem;">Pelayanan Keluarga & Konseling</p>
                <p style="color: var(--zaitun-text-secondary); font-size: 0.92rem; line-height: 1.65;">
                    Berfokus pada pemulihan keluarga, pembinaan pranikah, dan mendampingi kaum wanita serta anak-anak dalam pertumbuhan iman.
                </p>
            </div>

            <!-- Leader 3 -->
            <div class="editorial-card fade-in" style="text-align: center; transition-delay: 0.3s;">
                <div style="width: 100%; max-width: 220px; aspect-ratio: 4 / 5; margin: 0 auto 1.5rem; border-radius: 3px; overflow: hidden; border: 0;">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400" alt="Bp. Daniel Prasetyo" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 800; color: var(--zaitun-deep-dark); margin-bottom: 0.3rem;">Bp. Daniel Prasetyo</h3>
                <p style="color: var(--zaitun-primary); font-weight: 700; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1rem;">Ketua Majelis Jemaat</p>
                <p style="color: var(--zaitun-text-secondary); font-size: 0.92rem; line-height: 1.65;">
                    Mengawasi pelaksanaan operasional, diakonia sosial, dan tata kelola organisasi gereja agar tetap akuntabel dan transparan.
                </p>
            </div>
        </div>
    </section>

    <!-- History Timeline (Refined Olive & Gold Styling) -->
    <section class="about-history" style="background: var(--zaitun-sand); border-top: 1px solid var(--zaitun-border); padding: 6rem 2rem;">
        <div style="max-width: 860px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3.5rem;" class="fade-in">
                <span class="section-kicker">
                    <i class="ri-history-line"></i> Rekam Jejak
                </span>
                <h2 class="section-headline">Perjalanan Iman Kami</h2>
                <p class="section-lead" style="margin: 0 auto;">Bagaimana tangan Tuhan menuntun langkah jemaat dari masa ke masa.</p>
            </div>

            <div class="about-timeline" style="display: flex; flex-direction: column; gap: 2.5rem;">
                <!-- 2010 -->
                <div class="about-timeline-item fade-in" style="display: flex; gap: 2rem; align-items: flex-start;">
                    <div class="about-timeline-year" style="width: 72px; height: 72px; background: var(--zaitun-deep); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: var(--zaitun-gold-light); font-weight: 800; font-size: 1.15rem; flex-shrink: 0; box-shadow: var(--shadow-subtle);">
                        2010
                    </div>
                    <div class="editorial-card" style="flex: 1; padding: 1.6rem 2rem;">
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; margin-bottom: 0.4rem; color: var(--zaitun-deep-dark);">Perintisan Persekutuan Filadelfia</h3>
                        <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                            Dimulai dari kelompok doa rumah tangga beranggotakan 12 jemaat yang rindu membangun gereja berlandaskan kasih persaudaraan sejati.
                        </p>
                    </div>
                </div>

                <!-- 2015 -->
                <div class="about-timeline-item fade-in" style="display: flex; gap: 2rem; align-items: flex-start; transition-delay: 0.1s;">
                    <div class="about-timeline-year" style="width: 72px; height: 72px; background: var(--zaitun-primary); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.15rem; flex-shrink: 0; box-shadow: var(--shadow-subtle);">
                        2015
                    </div>
                    <div class="editorial-card" style="flex: 1; padding: 1.6rem 2rem;">
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; margin-bottom: 0.4rem; color: var(--zaitun-deep-dark);">Pembangunan Sanctuary & Kelas Sekolah Minggu</h3>
                        <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                            Tuhan membuka jalan sehingga jemaat dapat menempati gedung ibadah permanen yang kini menjadi rumah doa bagi ratusan keluarga.
                        </p>
                    </div>
                </div>

                <!-- 2020 -->
                <div class="about-timeline-item fade-in" style="display: flex; gap: 2rem; align-items: flex-start; transition-delay: 0.2s;">
                    <div class="about-timeline-year" style="width: 72px; height: 72px; background: var(--zaitun-gold); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.15rem; flex-shrink: 0; box-shadow: var(--shadow-subtle);">
                        2020
                    </div>
                    <div class="editorial-card" style="flex: 1; padding: 1.6rem 2rem;">
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; margin-bottom: 0.4rem; color: var(--zaitun-deep-dark);">Ekspansi Pelayanan Digital & Diakonia</h3>
                        <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                            Mengembangkan siaran live streaming berstandar tinggi serta menggalang bantuan sembako dan logistik bagi warga terdampak pandemi.
                        </p>
                    </div>
                </div>

                <!-- 2024 - Sekarang -->
                <div class="about-timeline-item fade-in" style="display: flex; gap: 2rem; align-items: flex-start; transition-delay: 0.3s;">
                    <div class="about-timeline-year" style="width: 72px; height: 72px; background: var(--zaitun-terracotta); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.15rem; flex-shrink: 0; box-shadow: var(--shadow-subtle);">
                        Kini
                    </div>
                    <div class="editorial-card" style="flex: 1; padding: 1.6rem 2rem;">
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 800; margin-bottom: 0.4rem; color: var(--zaitun-deep-dark);">Menjadi Pohon Zaitun yang Terus Berbuah</h3>
                        <p style="color: var(--zaitun-text-secondary); font-size: 0.95rem; line-height: 1.7;">
                            Terus bertumbuh dalam kualitas pemuridan, mencetak pemimpin muda berintegritas, dan menjangkau generasi masa kini bagi kemuliaan Kristus.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Warm Call to Action -->
    <section class="content-container about-invitation" style="text-align: center;">
        <div style="background: var(--zaitun-deep); border-radius: var(--radius-xl); padding: 4.5rem 2rem; color: #FFFFFF;" class="fade-in">
            <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 800; margin-bottom: 1rem; color: #FFFFFF;">
                Mari Bertumbuh Bersama Kami
            </h2>
            <p style="font-size: 1.15rem; color: var(--zaitun-sand); opacity: 0.9; margin-bottom: 2.2rem; max-width: 620px; margin-left: auto; margin-right: auto;">
                Tuhan menyediakan komunitas ini untuk menyambut Anda. Bergabunglah dalam ibadah minggu ini dan rasakan kehangatan kasih persaudaraan.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('public.contact') }}" class="btn-terracotta-cta">
                    <i class="ri-map-pin-line"></i>
                    <span>Kunjungi Kami</span>
                </a>
                <a href="{{ route('public.events') }}" class="btn-outline-sand">
                    <i class="ri-calendar-line"></i>
                    <span>Lihat Agenda Events</span>
                </a>
            </div>
        </div>
    </section>
    </div>

@endsection
