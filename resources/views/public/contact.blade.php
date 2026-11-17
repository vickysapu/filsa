@extends('layouts.public')

@section('title', 'Kontak - Gereja Filadelfia')

@section('content')

    <!-- Hero Section -->
    <section class="hero-section"
        style="min-height: 60vh; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(20, 184, 166, 0.1));">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="ri-mail-send-line"></i> Kontak
            </div>
            <h1 class="hero-title">Hubungi Kami</h1>
            <p class="hero-subtitle">
                Kami siap melayani Anda. Jangan ragu untuk menghubungi kami
                untuk informasi lebih lanjut atau kebutuhan pelayanan.
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section">
        <div class="lux-grid lux-grid-2" style="gap: 3rem;">

            <!-- Contact Info -->
            <div class="fade-in">
                <span class="section-badge">
                    <i class="ri-information-line"></i> Informasi
                </span>
                <h2 class="section-title" style="text-align: left; margin-bottom: 2rem;">Mari Terhubung</h2>

                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                        <div
                            style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-map-pin-line"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">Alamat</h3>
                            <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                                Jl. Gereja No. 123<br>
                                Jakarta, Indonesia 12345
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                        <div
                            style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--brand-accent), #f97316); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-phone-line"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">Telepon</h3>
                            <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                                (021) 1234-5678<br>
                                0812-3456-7890 (WhatsApp)
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                        <div
                            style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #14b8a6); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-mail-line"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">Email</h3>
                            <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                                3J42+5VP<br>
                                Jl. Inhoftank, Kb. Lega<br>
                                Kec. Bojongloa Kidul<br>
                                Kota Bandung, Jawa Barat 40235
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                        <div
                            style="width: 60px; height: 60px; background: linear-gradient(135deg, #8b5cf6, #6366f1); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="ri-time-line"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem;">Jam Operasional</h3>
                            <p style="color: var(--brand-dark); opacity: 0.8; line-height: 1.8;">
                                Senin - Jumat: 08:00 - 17:00 WIB<br>
                                Sabtu: 08:00 - 12:00 WIB<br>
                                Minggu: Hari Ibadah
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div style="margin-top: 3rem;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">Ikuti Kami</h3>
                    <div class="footer-social" style="justify-content: flex-start;">
                        <a href="#"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));"><i
                                class="ri-facebook-fill"></i></a>
                        <a href="#"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #e4405f, #fd1d1d);"><i
                                class="ri-instagram-fill"></i></a>
                        <a href="#"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #ff0000, #cc0000);"><i
                                class="ri-youtube-fill"></i></a>
                        <a href="#"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #25D366, #128C7E);"><i
                                class="ri-whatsapp-line"></i></a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="fade-in">
                <div class="lux-card" style="padding: 3rem;">
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Kirim Pesan</h3>

                    <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        @csrf
                        <div>
                            <label class="lux-label">Nama Lengkap *</label>
                            <input type="text" name="nama" class="lux-input" placeholder="Nama Anda" required>
                        </div>

                        <div class="lux-grid lux-grid-2" style="gap: 1rem;">
                            <div>
                                <label class="lux-label">Email *</label>
                                <input type="email" name="email" class="lux-input" placeholder="email@contoh.com"
                                    required>
                            </div>
                            <div>
                                <label class="lux-label">No. Telepon</label>
                                <input type="tel" name="telepon" class="lux-input" placeholder="08123456789">
                            </div>
                        </div>

                        <div>
                            <label class="lux-label">Subjek *</label>
                            <select name="subjek" class="lux-select" required>
                                <option value="">Pilih Subjek</option>
                                <option value="info">Informasi Umum</option>
                                <option value="doa">Permohonan Doa</option>
                                <option value="pelayanan">Pelayanan</option>
                                <option value="kesaksian">Kesaksian</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="lux-label">Pesan *</label>
                            <textarea name="pesan" class="lux-textarea" rows="5" placeholder="Tulis pesan Anda..." required></textarea>
                        </div>

                        <button type="submit" class="lux-btn lux-btn-primary" style="padding: 1rem; font-size: 1rem;">
                            <i class="ri-send-plane-fill"></i> Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top: 0;">
        <div class="fade-in">
            <div style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                <iframe
                    src="https://www.google.com/maps?q=3J42+5VP,+Jl.+Inhoftank,+Kb.+Lega,+Kec.+Bojongloa+Kidul,+Kota+Bandung,+Jawa+Barat+40235&output=embed"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

@endsection
