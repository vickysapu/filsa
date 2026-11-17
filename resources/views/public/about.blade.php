@extends('layouts.public')

@section('title', 'Tentang Kami - Gereja Filadelfia')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section"
        style="min-height: 60vh; background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="ri-information-line"></i> Tentang Kami
            </div>
            <h1 class="hero-title">Gereja Filadelfia</h1>
            <p class="hero-subtitle">
                "Dan kepada malaikat jemaat di Filadelfia tulislah: ... Aku mengetahui pekerjaanmu.
                Lihatlah, Aku telah membuka pintu bagimu dan tidak ada seorangpun yang dapat menutupnya."
                <br><strong>- Wahyu 3:7-8</strong>
            </p>
        </div>
    </section>

    <!-- Vision & Mission -->
    <section class="section">
        <div class="lux-grid lux-grid-2" style="gap: 3rem; align-items: center;">
            <div class="fade-in">
                <span class="section-badge">
                    <i class="ri-eye-line"></i> Visi Kami
                </span>
                <h2 class="section-title" style="text-align: left; margin-bottom: 1.5rem;">
                    Menjadi Gereja Yang Memberitakan Injil
                </h2>
                <p
                    style="font-size: 1.1rem; line-height: 1.8; color: var(--brand-dark); opacity: 0.8; margin-bottom: 1.5rem;">
                    Gereja Filadelfia berkomitmen untuk menjadi gereja yang memberitakan Injil Kristus
                    secara utuh, membangun komunitas yang penuh kasih, dan menjadi berkat bagi bangsa
                    dan negara.
                </p>
                <div style="display: grid; gap: 1rem;">
                    <div
                        style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                        <div
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="ri-book-open-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--brand-dark);">Pengajaran Alkitab</strong>
                            <p style="font-size: 0.9rem; opacity: 0.7;">Berpusat pada kebenaran Firman Tuhan</p>
                        </div>
                    </div>
                    <div
                        style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                        <div
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--brand-accent), #f97316); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="ri-heart-3-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--brand-dark);">Pelayanan Kasih</strong>
                            <p style="font-size: 0.9rem; opacity: 0.7;">Melayani dengan kasih Kristus</p>
                        </div>
                    </div>
                    <div
                        style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                        <div
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #14b8a6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="ri-global-line"></i>
                        </div>
                        <div>
                            <strong style="color: var(--brand-dark);">Misi & Penginjilan</strong>
                            <p style="font-size: 0.9rem; opacity: 0.7;">Menjangkau jiwa bagi Kristus</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fade-in">
                <img src="https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=800" alt="Gereja"
                    style="width: 100%; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="section"
        style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(6, 182, 212, 0.05)); border-radius: 40px;">
        <div class="section-header fade-in">
            <span class="section-badge">
                <i class="ri-star-line"></i> Nilai-Nilai Kami
            </span>
            <h2 class="section-title">Apa Yang Kami Percayai</h2>
        </div>

        <div class="card-grid">
            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-bible-line"></i>
                </div>
                <h3 class="feature-title">Firman Tuhan</h3>
                <p class="feature-description">
                    Kami percaya Alkitab adalah Firman Tuhan yang diilhamkan dan menjadi dasar
                    iman dan praktik kehidupan kami.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-pray-line"></i>
                </div>
                <h3 class="feature-title">Doa & Penyembahan</h3>
                <p class="feature-description">
                    Doa dan penyembahan adalah napas kehidupan gereja. Kami berkomitmen
                    untuk hidup dalam doa dan penyembahan yang sungguh-sungguh.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-team-line"></i>
                </div>
                <h3 class="feature-title">Persekutuan</h3>
                <p class="feature-description">
                    Kami percaya pada pentingnya persekutuan yang erat antar jemaat,
                    saling mendukung dan bertumbuh bersama dalam iman.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-hand-heart-line"></i>
                </div>
                <h3 class="feature-title">Pelayanan</h3>
                <p class="feature-description">
                    Setiap anggota tubuh Kristus dipanggil untuk melayani. Kami mendorong
                    setiap jemaat untuk menemukan dan menggunakan karunia mereka.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-earth-line"></i>
                </div>
                <h3 class="feature-title">Misi</h3>
                <p class="feature-description">
                    Amanat Agung adalah mandat kami. Kami berkomitmen untuk memberitakan
                    Injil hingga ke ujung bumi.
                </p>
            </div>

            <div class="feature-card fade-in">
                <div class="feature-icon">
                    <i class="ri-fire-line"></i>
                </div>
                <h3 class="feature-title">Roh Kudus</h3>
                <p class="feature-description">
                    Kami percaya pada pekerjaan Roh Kudus yang aktif dalam kehidupan
                    orang percaya dan gereja-Nya.
                </p>
            </div>
        </div>
    </section>

    <!-- Leadership -->
    <section class="section">
        <div class="section-header fade-in">
            <span class="section-badge">
                <i class="ri-team-line"></i> Kepemimpinan
            </span>
            <h2 class="section-title">Gembala & Pemimpin</h2>
            <p class="section-subtitle">
                Tuhan telah memanggil hamba-hamba-Nya untuk menggembalakan dan memimpin
                Gereja Filadelfia dalam kasih dan kebenaran.
            </p>
        </div>

        <div class="card-grid">
            <div class="feature-card fade-in" style="text-align: center;">
                <div
                    style="width: 120px; height: 120px; margin: 0 auto 1.5rem; border-radius: 50%; overflow: hidden; border: 4px solid var(--brand-primary);">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400" alt="Pendeta"
                        style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 class="feature-title">Pdt. John Doe</h3>
                <p style="color: var(--brand-primary); font-weight: 600; margin-bottom: 1rem;">Gembala Sidang</p>
                <p class="feature-description">
                    Melayani sebagai gembala sejak 2010, berkomitmen untuk memberitakan
                    Injil dan menggembalakan umat Tuhan dengan kasih.
                </p>
            </div>

            <div class="feature-card fade-in" style="text-align: center;">
                <div
                    style="width: 120px; height: 120px; margin: 0 auto 1.5rem; border-radius: 50%; overflow: hidden; border: 4px solid var(--brand-secondary);">
                    <img src="https://images.unsplash.com/photo-1573496359-09a6d975dd5f?w=400" alt="Pendeta"
                        style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 class="feature-title">Pdt. Jane Doe</h3>
                <p style="color: var(--brand-secondary); font-weight: 600; margin-bottom: 1rem;">Gembala Sidang</p>
                <p class="feature-description">
                    Melayani bersama suami dalam pelayanan gembala, fokus pada pelayanan
                    keluarga dan konseling pastoral.
                </p>
            </div>

            <div class="feature-card fade-in" style="text-align: center;">
                <div
                    style="width: 120px; height: 120px; margin: 0 auto 1.5rem; border-radius: 50%; overflow: hidden; border: 4px solid var(--brand-accent);">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400" alt="Elder"
                        style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 class="feature-title">Bp. Michael Smith</h3>
                <p style="color: var(--brand-accent); font-weight: 600; margin-bottom: 1rem;">Penatua</p>
                <p class="feature-description">
                    Melayani sebagai penatua gereja, bertanggung jawab dalam penggembalaan
                    dan pengawasan pelayanan gereja.
                </p>
            </div>
        </div>
    </section>

    <!-- History Timeline -->
    <section class="section"
        style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(236, 72, 153, 0.05)); border-radius: 40px;">
        <div class="section-header fade-in">
            <span class="section-badge">
                <i class="ri-time-line"></i> Sejarah
            </span>
            <h2 class="section-title">Perjalanan Iman Kami</h2>
        </div>

        <div style="max-width: 800px; margin: 0 auto;">
            <div class="fade-in" style="display: flex; gap: 2rem; margin-bottom: 3rem; align-items: flex-start;">
                <div
                    style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                    2010
                </div>
                <div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem;">Pendirian Gereja</h3>
                    <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                        Gereja Filadelfia didirikan dengan visi untuk menjadi gereja yang
                        memberitakan Injil dan membangun komunitas yang penuh kasih.
                    </p>
                </div>
            </div>

            <div class="fade-in" style="display: flex; gap: 2rem; margin-bottom: 3rem; align-items: flex-start;">
                <div
                    style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--brand-accent), #f97316); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                    2015
                </div>
                <div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem;">Gedung Baru</h3>
                    <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                        Tuhan berkarya secara luar biasa sehingga kami dapat membangun
                        gedung gereja baru untuk menampung jemaat yang terus bertambah.
                    </p>
                </div>
            </div>

            <div class="fade-in" style="display: flex; gap: 2rem; margin-bottom: 3rem; align-items: flex-start;">
                <div
                    style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #14b8a6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                    2020
                </div>
                <div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem;">Digital Ministry</h3>
                    <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                        Di masa pandemi, kami mengembangkan pelayanan digital untuk tetap
                        menjangkau jemaat dan mereka yang mencari Tuhan melalui internet.
                    </p>
                </div>
            </div>

            <div class="fade-in" style="display: flex; gap: 2rem; align-items: flex-start;">
                <div
                    style="width: 80px; height: 80px; background: linear-gradient(135deg, #8b5cf6, #6366f1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem; flex-shrink: 0;">
                    2024
                </div>
                <div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.5rem;">Masa Kini</h3>
                    <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                        Kini Gereja Filadelfia terus bertumbuh dalam jumlah dan kualitas,
                        berkomitmen untuk menjadi berkat bagi lebih banyak jiwa.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section" style="text-align: center;">
        <div
            style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); border-radius: 40px; padding: 5rem 2rem; color: white; position: relative; overflow: hidden;">
            <div
                style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);">
            </div>
            <div style="position: relative; z-index: 10;">
                <h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; margin-bottom: 1rem;">
                    Bergabunglah Bersama Kami
                </h2>
                <p
                    style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Kami menanti kedatangan Anda. Mari bersama-sama mengalami kasih
                    dan persekutuan yang hangat di Gereja Filadelfia.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('public.contact') }}" class="hero-btn"
                        style="background: white; color: var(--brand-primary);">
                        <i class="ri-map-pin-line"></i> Kunjungi Kami
                    </a>
                    <a href="{{ route('public.events') }}" class="hero-btn"
                        style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                        <i class="ri-calendar-event-line"></i> Lihat Events
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
