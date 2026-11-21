@extends('layouts.public')

@section('title', 'Gereja Filadelfia | Bertumbuh dalam Iman')

@push('styles')
<style>
    /* ==========================================================================
       HERO SECTION — HIGH IMPACT ARCHITECTURAL EDITORIAL
       ========================================================================== */
    .hero-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 7.5rem 2rem 4rem;
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        gap: 3.5rem;
        align-items: center;
        min-height: calc(90vh - 60px);
    }

    .hero-kicker-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        color: var(--zaitun-primary);
        margin-bottom: 1.4rem;
    }

    .hero-display-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(3rem, 5.5vw, 4.6rem);
        font-weight: 900;
        line-height: 1.05;
        letter-spacing: -0.025em;
        color: var(--zaitun-deep-dark);
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    .hero-display-title .text-accent-gold {
        color: var(--zaitun-gold);
        display: block;
    }

    .hero-lead-text {
        font-size: 1.15rem;
        line-height: 1.8;
        color: var(--zaitun-text-secondary);
        margin-bottom: 2.2rem;
        max-width: 520px;
    }

    .hero-btn-group {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        flex-wrap: wrap;
    }

    /* Hero Right Visual & Sunday Badge (Direct Reference) */
    .hero-visual-col {
        position: relative;
    }

    .hero-frame-card {
        border-radius: var(--radius-xl);
        overflow: hidden;
        aspect-ratio: 4/4.6;
        box-shadow: 0 20px 48px rgba(28, 36, 23, 0.15);
        background: var(--zaitun-deep);
        position: relative;
    }

    .hero-frame-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        filter: brightness(0.94) contrast(1.04);
        transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .hero-frame-card:hover img {
        transform: scale(1.03);
    }

    /* Reference Signature Sunday Worship Badge */
    .hero-sunday-curved-card {
        position: absolute;
        bottom: -24px;
        right: -24px;
        background: var(--zaitun-deep);
        color: #FFFFFF;
        border-radius: 28px 4px 28px 28px;
        padding: 1.6rem 2rem;
        box-shadow: 0 16px 36px rgba(28, 36, 23, 0.35);
        border: 1px solid rgba(234, 222, 184, 0.2);
        max-width: 320px;
        z-index: 5;
    }

    .sunday-badge-pill {
        display: inline-block;
        background: rgba(255, 255, 255, 0.12);
        color: var(--zaitun-gold-light);
        padding: 0.3rem 0.8rem;
        border-radius: var(--radius-pill);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
    }

    .sunday-badge-kicker {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        color: var(--zaitun-gold);
        margin-bottom: 0.2rem;
    }

    .sunday-badge-headline {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 2.1rem;
        font-weight: 900;
        line-height: 1.05;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .sunday-badge-desc {
        font-size: 0.84rem;
        line-height: 1.5;
        color: #C2CEC0;
    }

    /* ==========================================================================
       WELCOME TO FAITH CONNECT / FILADELFIA SECTION
       ========================================================================== */
    .welcome-panel {
        background: var(--zaitun-cream);
        padding: 6.5rem 2rem 5.5rem;
        position: relative;
    }

    .welcome-inner {
        max-width: 1040px;
        margin: 0 auto;
        text-align: center;
        position: relative;
    }

    /* Architectural Arch Vignette on Top Left (From Reference) */
    .welcome-arch-vignette {
        position: absolute;
        top: 0;
        left: -40px;
        width: 130px;
        height: 140px;
        border-radius: 65px 65px 16px 16px;
        overflow: hidden;
        border: 4px solid var(--zaitun-white);
        box-shadow: var(--shadow-card);
        display: none;
    }

    @media (min-width: 1200px) {
        .welcome-arch-vignette {
            display: block;
        }
    }

    .welcome-arch-vignette img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .welcome-h2 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(2.4rem, 4.6vw, 3.8rem);
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1.12;
        letter-spacing: -0.015em;
        color: var(--zaitun-deep-dark);
        margin-bottom: 1.6rem;
    }

    .welcome-p {
        font-size: 1.18rem;
        line-height: 1.85;
        color: #4A5743;
        max-width: 800px;
        margin: 0 auto 2.2rem;
    }

    .welcome-tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        background: var(--zaitun-sand);
        border: 1px solid var(--zaitun-border);
        padding: 0.55rem 1.6rem;
        border-radius: var(--radius-pill);
        box-shadow: var(--shadow-subtle);
        font-size: 0.86rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--zaitun-deep);
    }

    /* ==========================================================================
       WHO WE ARE (A CHRIST-CENTERED COMMUNITY)
       ========================================================================== */
    .who-we-are-panel {
        padding: 6.5rem 2rem 4rem;
        background: var(--zaitun-sand);
    }

    .who-grid {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 4.5rem;
        align-items: center;
    }

    .who-image-wrap {
        border-radius: 36px 36px 36px 80px;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(28, 36, 23, 0.12);
        aspect-ratio: 4/4.6;
        border: 6px solid var(--zaitun-white);
    }

    .who-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .kicker-black {
        display: inline-block;
        background: var(--zaitun-deep-dark);
        color: var(--zaitun-gold-light);
        padding: 0.35rem 0.9rem;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        border-radius: 4px;
        margin-bottom: 1.2rem;
    }

    .who-heading {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(2.2rem, 3.8vw, 3.2rem);
        font-weight: 800;
        line-height: 1.16;
        text-transform: uppercase;
        letter-spacing: -0.01em;
        color: var(--zaitun-deep-dark);
        margin-bottom: 1.5rem;
    }

    .who-desc {
        font-size: 1.08rem;
        line-height: 1.85;
        color: var(--zaitun-text-secondary);
        margin-bottom: 2rem;
    }

    /* ==========================================================================
       THREE CORE PILLARS (OUR MISSION, OUR VISION, OUR VALUES)
       ========================================================================== */
    .pillars-row-container {
        max-width: 1280px;
        margin: 4.5rem auto 0;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2.5rem;
    }

    .pillar-col {
        border-top: 3px solid var(--zaitun-gold);
        padding-top: 1.8rem;
    }

    .pillar-col-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--zaitun-deep-dark);
        margin-bottom: 0.8rem;
    }

    .pillar-col-text {
        font-size: 0.96rem;
        line-height: 1.75;
        color: var(--zaitun-text-secondary);
    }

    /* ==========================================================================
       MINISTRIES SECTION (OUR MINISTRIES)
       ========================================================================== */
    .ministries-panel {
        background: var(--zaitun-cream);
        padding: 6.5rem 2rem;
    }

    .ministries-grid-wrap {
        max-width: 1280px;
        margin: 3.5rem auto 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.8rem;
    }

    .ministry-box {
        background: var(--zaitun-white);
        border: 1px solid var(--zaitun-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-card);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .ministry-box:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: var(--zaitun-young);
    }

    .ministry-box-img {
        width: 100%;
        height: 190px;
        object-fit: cover;
    }

    .ministry-box-body {
        padding: 1.8rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .ministry-tag {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--zaitun-primary);
        margin-bottom: 0.4rem;
    }

    .ministry-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--zaitun-deep-dark);
        margin-bottom: 0.65rem;
    }

    .ministry-p {
        font-size: 0.92rem;
        line-height: 1.65;
        color: var(--zaitun-text-secondary);
    }

    /* ==========================================================================
       UPCOMING EVENTS (AGENDA KEGIATAN)
       ========================================================================== */
    .events-panel {
        padding: 6.5rem 2rem;
        background: var(--zaitun-sand);
    }

    .events-header-row {
        max-width: 1280px;
        margin: 0 auto 3.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .events-editorial-list {
        max-width: 1280px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .event-row-card {
        background: var(--zaitun-white);
        border: 1px solid var(--zaitun-border);
        border-radius: var(--radius-md);
        padding: 1.8rem 2.2rem;
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: 2.5rem;
        align-items: center;
        box-shadow: var(--shadow-subtle);
        transition: var(--transition);
    }

    .event-row-card:hover {
        transform: translateX(6px);
        border-color: var(--zaitun-primary);
        box-shadow: var(--shadow-card);
    }

    .event-date-block {
        text-align: center;
        border-right: 2px solid var(--zaitun-border-subtle);
        padding-right: 1.5rem;
    }

    .event-date-num {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 2.4rem;
        font-weight: 900;
        color: var(--zaitun-deep);
        line-height: 1;
    }

    .event-date-month {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--zaitun-terracotta);
    }

    .event-info-block h3 {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--zaitun-deep-dark);
        margin-bottom: 0.4rem;
    }

    .event-meta-line {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.88rem;
        color: var(--zaitun-text-muted);
        flex-wrap: wrap;
    }

    .event-meta-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* ==========================================================================
       BIBLICAL BANNER
       ========================================================================== */
    .quote-block-section {
        padding: 5rem 2rem;
        background: var(--zaitun-sand);
    }

    .quote-box {
        max-width: 1080px;
        margin: 0 auto;
        background: var(--zaitun-deep);
        border-radius: var(--radius-xl);
        padding: 4.5rem 3rem;
        text-align: center;
        color: #FFFFFF;
        position: relative;
        border: 1px solid rgba(234, 222, 184, 0.25);
        box-shadow: 0 16px 40px rgba(28, 36, 23, 0.2);
    }

    .quote-box p {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(1.4rem, 2.6vw, 2.1rem);
        font-style: italic;
        line-height: 1.55;
        color: var(--zaitun-sand);
        max-width: 820px;
        margin: 0 auto 1.4rem;
    }

    .quote-box cite {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-style: normal;
        font-size: 0.9rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        color: var(--zaitun-gold);
    }

    /* ==========================================================================
       GALLERY DOCUMENTATION
       ========================================================================== */
    .gallery-panel {
        background: var(--zaitun-cream);
        padding: 6.5rem 2rem;
    }

    .gallery-quad-grid {
        max-width: 1280px;
        margin: 3.5rem auto 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.6rem;
    }

    .gallery-quad-item {
        position: relative;
        border-radius: var(--radius-md);
        overflow: hidden;
        aspect-ratio: 4/3.2;
        cursor: pointer;
        box-shadow: var(--shadow-subtle);
        background: var(--zaitun-deep);
    }

    .gallery-quad-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .gallery-quad-item-empty {
        background: linear-gradient(145deg, var(--zaitun-deep), #286348);
    }

    .gallery-quad-item:hover img {
        transform: scale(1.08);
    }

    .gallery-quad-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(28, 36, 23, 0.9) 0%, rgba(28, 36, 23, 0.2) 60%, transparent 100%);
        display: flex;
        align-items: flex-end;
        padding: 1.6rem;
    }

    .gallery-quad-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #FFFFFF;
        line-height: 1.3;
    }

    .gallery-quad-meta {
        font-size: 0.82rem;
        color: var(--zaitun-gold-light);
        margin-top: 0.3rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* ==========================================================================
       WORSHIP SCHEDULE & VISIT US
       ========================================================================== */
    .schedule-panel {
        padding: 6.5rem 2rem;
        background: var(--zaitun-sand);
    }

    .schedule-three-grid {
        max-width: 1280px;
        margin: 3.5rem auto 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
    }

    .schedule-tile {
        background: var(--zaitun-white);
        border: 1px solid var(--zaitun-border);
        border-radius: var(--radius-lg);
        padding: 2.4rem 2.2rem;
        box-shadow: var(--shadow-subtle);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .schedule-tile.featured-tile {
        background: var(--zaitun-cream);
        border-color: var(--zaitun-gold);
    }

    .schedule-tile:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: var(--zaitun-primary);
    }

    .tile-kicker {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        background: rgba(52, 67, 44, 0.08);
        color: var(--zaitun-deep);
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border-radius: var(--radius-pill);
        margin-bottom: 1.2rem;
        align-self: flex-start;
    }

    .tile-time {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--zaitun-deep);
        line-height: 1.1;
        margin-bottom: 0.5rem;
    }

    .tile-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--zaitun-deep-dark);
        margin-bottom: 0.8rem;
    }

    .tile-desc {
        font-size: 0.94rem;
        line-height: 1.65;
        color: var(--zaitun-text-secondary);
        margin-bottom: 1.5rem;
    }

    /* Responsive Queries */
    @media (max-width: 1024px) {
        .hero-container {
            grid-template-columns: 1fr;
            padding-top: 6rem;
            gap: 4rem;
        }

        .who-grid {
            grid-template-columns: 1fr;
            gap: 3rem;
        }

        .pillars-row-container {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .hero-sunday-curved-card {
            position: relative;
            bottom: auto;
            right: auto;
            margin-top: 1.5rem;
            max-width: 100%;
        }

        .event-row-card {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .event-date-block {
            text-align: left;
            border-right: none;
            border-bottom: 1px solid var(--zaitun-border-subtle);
            padding-bottom: 1rem;
            display: flex;
            align-items: baseline;
            gap: 0.8rem;
        }
    }

    @media (max-width: 640px) {
        .hero-display-title {
            font-size: 2.6rem;
        }

        .welcome-h2 {
            font-size: 2.2rem;
        }

        .hero-btn-group {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-terracotta-cta, .btn-outline-sand {
            justify-content: center;
        }

        .quote-box {
            padding: 3rem 1.5rem;
        }
    }

    .hero-container {
        max-width: 1240px;
        min-height: min(760px, calc(100svh - 70px));
        padding: 8.5rem 2rem 5.5rem;
        grid-template-columns: 1fr 0.92fr;
        gap: clamp(2.5rem, 6vw, 6rem);
    }

    .hero-kicker-tag {
        color: var(--zaitun-primary);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 1.25rem;
    }

    .hero-display-title {
        font-size: clamp(2.9rem, 4vw, 4.1rem);
        font-weight: 600;
        line-height: 1.08;
        letter-spacing: -0.035em;
        text-transform: none;
        margin-bottom: 1.4rem;
    }

    .hero-display-title .text-accent-gold {
        color: var(--zaitun-primary);
    }

    .hero-lead-text {
        max-width: 490px;
        font-size: 1.05rem;
        line-height: 1.8;
        margin-bottom: 2rem;
    }

    .hero-btn-group {
        gap: 0.8rem;
    }

    .hero-frame-card {
        aspect-ratio: 5 / 4.7;
        border-radius: 4px;
        box-shadow: 0 18px 44px rgba(18, 61, 45, 0.16);
    }

    .hero-frame-card img {
        filter: brightness(0.9) saturate(0.82);
    }

    .hero-sunday-curved-card {
        right: -24px;
        bottom: 24px;
        max-width: 275px;
        padding: 1.25rem 1.5rem;
        border: 0;
        border-left: 3px solid var(--zaitun-gold);
        border-radius: 0;
        background: var(--zaitun-deep);
        box-shadow: 0 12px 30px rgba(18, 61, 45, 0.2);
    }

    .sunday-badge-pill {
        padding: 0;
        margin-bottom: 0.45rem;
        border-radius: 0;
        background: transparent;
        color: var(--zaitun-gold);
        font-size: 0.8rem;
    }

    .sunday-badge-kicker {
        margin-bottom: 0.3rem;
        font-size: 0.7rem;
        letter-spacing: 0.12em;
    }

    .sunday-badge-headline {
        font-size: 1.55rem;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
    }

    .welcome-panel,
    .ministries-panel,
    .gallery-panel {
        padding: 5.5rem 2rem;
    }

    .welcome-panel {
        border-top: 1px solid var(--zaitun-border);
        border-bottom: 1px solid var(--zaitun-border);
    }

    .welcome-h2,
    .who-heading {
        font-size: clamp(2.1rem, 3.8vw, 3.2rem);
        font-weight: 600;
        text-transform: none;
        letter-spacing: -0.025em;
        line-height: 1.15;
    }

    .welcome-p {
        max-width: 660px;
        font-size: 1.05rem;
        color: var(--zaitun-text-secondary);
    }

    .who-we-are-panel {
        padding: 6rem 2rem 5rem;
    }

    .who-grid {
        max-width: 1180px;
        gap: clamp(2.5rem, 6vw, 5rem);
    }

    .who-image-wrap {
        border: 0;
        border-radius: 4px;
        box-shadow: none;
    }

    .kicker-black {
        padding: 0;
        background: transparent;
        color: var(--zaitun-primary);
        font-size: 0.76rem;
        letter-spacing: 0.1em;
    }

    .pillars-row-container {
        max-width: 1180px;
        gap: 2rem;
    }

    .pillar-col {
        border-top: 1px solid var(--zaitun-border);
        padding-top: 1.25rem;
    }

    .pillar-col-title,
    .ministry-title,
    .event-info-block h3 {
        font-weight: 600;
    }

    .ministry-box {
        border-radius: 4px;
        border-color: var(--zaitun-border);
        box-shadow: none;
    }

    .ministry-box:hover {
        transform: translateY(-3px);
        border-color: var(--zaitun-primary);
        box-shadow: var(--shadow-card);
    }

    .ministry-box-img {
        height: 210px;
        filter: saturate(0.8);
    }

    .ministry-box-body {
        padding: 1.5rem;
    }

    .event-row-card,
    .schedule-tile {
        border-radius: 4px;
        box-shadow: none;
    }

    .event-row-card:hover {
        transform: translateY(-2px);
    }

    .event-date-month {
        color: var(--zaitun-primary);
    }

    .quote-block-section {
        padding: 1rem 2rem 5rem;
    }

    .quote-box {
        padding: 4rem 3rem;
        border: 0;
        border-top: 3px solid var(--zaitun-gold);
        border-radius: 4px;
        box-shadow: none;
    }

    .gallery-quad-item {
        border-radius: 4px;
        box-shadow: none;
    }

    .gallery-quad-item img {
        filter: saturate(0.82);
    }

    .schedule-tile {
        border-color: var(--zaitun-border);
    }

    .schedule-tile.featured-tile {
        background: var(--zaitun-cream);
        border-color: var(--zaitun-primary);
    }

    @media (max-width: 1024px) {
        .hero-container {
            min-height: auto;
            grid-template-columns: 1fr 0.9fr;
            padding-top: 8rem;
            gap: 2.5rem;
        }

        .hero-sunday-curved-card {
            right: -12px;
            bottom: 18px;
        }
    }

    @media (max-width: 800px) {
        .hero-container {
            grid-template-columns: 1fr;
            padding: 8rem 1.5rem 4rem;
        }

        .hero-visual-col {
            max-width: 620px;
        }

        .hero-frame-card {
            aspect-ratio: 5 / 3.8;
        }

        .hero-sunday-curved-card {
            right: 12px;
            bottom: 16px;
        }

        .who-grid {
            grid-template-columns: 1fr 1.1fr;
        }
    }

    @media (max-width: 640px) {
        .hero-container {
            padding: 7rem 1.25rem 3.5rem;
            gap: 2rem;
        }

        .hero-display-title {
            font-size: clamp(2.45rem, 10vw, 3.2rem);
        }

        .hero-btn-group {
            flex-direction: row;
            align-items: center;
        }

        .hero-sunday-curved-card {
            position: relative;
            right: auto;
            bottom: auto;
            max-width: none;
            margin-top: 0.75rem;
        }

        .who-grid {
            grid-template-columns: 1fr;
        }

        .who-image-wrap {
            aspect-ratio: 5 / 3.6;
        }

        .welcome-panel,
        .ministries-panel,
        .gallery-panel,
        .who-we-are-panel,
        .events-panel,
        .schedule-panel {
            padding: 4rem 1.25rem;
        }

        .pillars-row-container {
            margin-top: 3rem;
            gap: 1.5rem;
        }

        .event-row-card {
            padding: 1.35rem;
        }

        .event-row-card > div:last-child {
            justify-self: start;
        }

        .quote-block-section {
            padding: 0 1.25rem 3.5rem;
        }

        .quote-box {
            padding: 2.5rem 1.4rem;
        }

        .hero-btn-group .btn-terracotta-cta,
        .hero-btn-group .btn-outline-sand {
            padding: 0.8rem 1rem;
            font-size: 0.88rem;
        }
    }

    .hero-video-section {
        position: relative;
        isolation: isolate;
        display: flex;
        min-height: 100vh;
        min-height: 100svh;
        overflow: hidden;
        background-color: var(--zaitun-deep-dark);
        background-image: url('https://assets.mixkit.co/videos/1514/1514-thumb-720-0.jpg');
        background-position: center;
        background-size: cover;
        color: var(--zaitun-sand);
    }

    .hero-background-video,
    .hero-video-shade {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .hero-background-video {
        z-index: -2;
        object-fit: cover;
    }

    .hero-video-shade {
        z-index: -1;
        background:
            linear-gradient(90deg, rgba(14, 43, 32, 0.9) 0%, rgba(18, 61, 45, 0.72) 46%, rgba(18, 61, 45, 0.38) 100%),
            linear-gradient(0deg, rgba(14, 43, 32, 0.6), transparent 55%);
    }

    .hero-video-container {
        width: 100%;
        max-width: 1240px;
        min-height: 100vh;
        min-height: 100svh;
        margin: 0 auto;
        padding: 8rem 2rem 5rem;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(220px, 300px);
        gap: clamp(2rem, 6vw, 6rem);
        align-items: end;
    }

    .hero-video-content {
        max-width: 760px;
    }

    .hero-video-section .hero-kicker-tag {
        color: var(--zaitun-gold-light);
        font-size: 0.76rem;
        font-weight: 600;
        letter-spacing: 0.16em;
        margin-bottom: 1.25rem;
    }

    .hero-video-section .hero-display-title {
        color: var(--zaitun-sand);
        font-size: clamp(3.2rem, 7vw, 6.4rem);
        font-weight: 500;
        letter-spacing: -0.045em;
        line-height: 1.02;
        text-transform: none;
        margin-bottom: 1.5rem;
    }

    .hero-video-section .hero-display-title .text-accent-gold {
        color: var(--zaitun-gold-light);
    }

    .hero-video-section .hero-lead-text {
        max-width: 560px;
        color: rgba(245, 241, 230, 0.88);
        font-size: 1.08rem;
        line-height: 1.8;
        margin-bottom: 2rem;
    }

    .hero-video-section .hero-btn-group {
        gap: 0.75rem;
    }

    .hero-video-section .hero-button {
        min-height: 48px;
        padding: 0.8rem 1.25rem;
        border: 1px solid transparent;
        border-radius: 3px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        font-size: 0.9rem;
        font-weight: 700;
        transition: var(--transition);
    }

    .hero-video-section .hero-button-primary {
        background: var(--zaitun-gold);
        color: var(--zaitun-deep-dark);
    }

    .hero-video-section .hero-button-primary:hover {
        background: var(--zaitun-gold-light);
        color: var(--zaitun-deep-dark);
        transform: translateY(-2px);
    }

    .hero-video-section .hero-button-secondary {
        border-color: rgba(245, 241, 230, 0.55);
        color: var(--zaitun-sand);
        background: rgba(18, 61, 45, 0.2);
    }

    .hero-video-section .hero-button-secondary:hover {
        border-color: var(--zaitun-sand);
        background: rgba(245, 241, 230, 0.12);
        color: var(--zaitun-sand);
    }

    .hero-schedule-note {
        padding: 1.2rem 0 0.4rem 1.35rem;
        border-left: 1px solid var(--zaitun-gold);
        margin-bottom: 0.4rem;
    }

    .hero-schedule-label {
        display: block;
        margin-bottom: 0.65rem;
        color: var(--zaitun-gold-light);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .hero-schedule-time {
        display: block;
        margin-bottom: 0.75rem;
        color: var(--zaitun-sand);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.7rem;
        line-height: 1.2;
    }

    .hero-schedule-link {
        color: rgba(245, 241, 230, 0.82);
        font-size: 0.86rem;
    }

    .hero-schedule-link:hover {
        color: var(--zaitun-gold-light);
    }

    @media (max-width: 800px) {
        .hero-video-container {
            grid-template-columns: 1fr;
            align-content: end;
            gap: 2.25rem;
            padding: 8rem 1.5rem 3.5rem;
        }

        .hero-video-section .hero-display-title {
            font-size: clamp(3rem, 10vw, 5rem);
        }

        .hero-schedule-note {
            max-width: 360px;
            margin-bottom: 0;
        }
    }

    @media (max-width: 480px) {
        .hero-video-container {
            padding-right: 1.25rem;
            padding-left: 1.25rem;
        }

        .hero-video-section .hero-display-title {
            font-size: clamp(2.7rem, 11vw, 3.5rem);
        }

        .hero-video-section .hero-btn-group {
            align-items: stretch;
            flex-direction: column;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .hero-background-video {
            display: none;
        }

        .hero-video-section .fade-in {
            opacity: 1;
            transform: none;
            transition: none;
        }
    }
</style>
@endpush

@section('content')

    <section class="hero-video-section" aria-labelledby="hero-title">
        <video class="hero-background-video" autoplay muted loop playsinline preload="none" aria-hidden="true" tabindex="-1" poster="https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1800&amp;auto=format&amp;fit=crop&amp;q=82">
        </video>
        <div class="hero-video-shade" aria-hidden="true"></div>

        <div class="hero-video-container">
            <div class="hero-video-content fade-in">
                <span class="hero-kicker-tag">Gereja Filadelfia · Bandung</span>

                <h1 class="hero-display-title" id="hero-title">
                    Iman yang bertumbuh,<br>
                    <span class="text-accent-gold">kasih yang dibagikan.</span>
                </h1>

                <p class="hero-lead-text">
                    Kami adalah jemaat yang beribadah, belajar firman, dan melayani bersama. Anda dan keluarga selalu disambut di sini.
                </p>

                <div class="hero-btn-group">
                    <a href="#jadwal-ibadah" class="hero-button hero-button-primary">
                        <span>Lihat jadwal ibadah</span>
                        <i class="ri-arrow-right-line"></i>
                    </a>
                    <a href="{{ route('public.contact') }}" class="hero-button hero-button-secondary">
                        <span>Hubungi kami</span>
                    </a>
                </div>
            </div>

            <div class="hero-schedule-note fade-in">
                <span class="hero-schedule-label">Ibadah Minggu</span>
                <span class="hero-schedule-time">08:00 &amp; 18:00 WIB</span>
                <a class="hero-schedule-link" href="#jadwal-ibadah">
                    Lihat jadwal lengkap <i class="ri-arrow-right-line" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 2. WARM WELCOME BANNER (WELCOME TO FILADELFIA CHURCH)                     -->
    <!-- ========================================================================= -->
    <section class="welcome-panel">
        <div class="welcome-inner fade-in">
            <span class="section-kicker">
                <i class="ri-heart-3-line"></i> Selamat datang
            </span>

            <h2 class="welcome-h2">
                Ada tempat untuk Anda di sini.
            </h2>

            <p class="welcome-p">
                Datang apa adanya. Beribadah, bertemu, dan bertumbuh bersama komunitas yang saling menguatkan.
            </p>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. WHO WE ARE (A CHRIST-CENTERED COMMUNITY)                              -->
    <!-- ========================================================================= -->
    <section class="who-we-are-panel">
        <div class="who-grid">
            <div class="who-image-wrap fade-in">
                <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=900&auto=format&fit=crop&q=80" alt="Pelayanan dan Doa Bersama">
            </div>

            <div class="fade-in">
                <span class="kicker-black">Siapa Kami</span>
                <h2 class="who-heading">
                    Iman yang bertumbuh, kasih yang dibagikan.
                </h2>
                <p class="who-desc">
                    Kami berkumpul untuk mengenal Kristus, memperdalam firman, dan hadir bagi sesama. Dalam setiap tahap kehidupan, ada ruang untuk belajar dan melayani bersama.
                </p>
                <div>
                    <a href="{{ route('public.about') }}" class="btn-terracotta-cta">
                        <span>Tentang Kami Selengkapnya</span>
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 3 Pillars (Our Mission, Our Vision, Our Values) -->
        <div class="pillars-row-container">
            <div class="pillar-col fade-in">
                <h3 class="pillar-col-title">Misi Kami</h3>
                <p class="pillar-col-text">
                    Mengabarkan kasih Kristus melalui ibadah, pembelajaran firman, dan pelayanan kepada sesama.
                </p>
            </div>

            <div class="pillar-col fade-in" style="transition-delay: 0.15s;">
                <h3 class="pillar-col-title">Visi Kami</h3>
                <p class="pillar-col-text">
                    Menjadi jemaat yang berakar dalam firman, bertumbuh dalam iman, dan membawa kebaikan bagi lingkungan.
                </p>
            </div>

            <div class="pillar-col fade-in" style="transition-delay: 0.3s;">
                <h3 class="pillar-col-title">Nilai Kami</h3>
                <p class="pillar-col-text">
                    Kasih persaudaraan, kesetiaan pada firman, kerendahan hati, dan keramahan kepada semua.
                </p>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 4. OUR MINISTRIES (PELAYANAN KAMI)                                        -->
    <!-- ========================================================================= -->
    <section class="ministries-panel">
        <div style="max-width: 1280px; margin: 0 auto; text-align: center;" class="fade-in">
            <span class="section-kicker">
                <i class="ri-team-line"></i> Bertumbuh bersama
            </span>
            <h2 class="section-headline">Pelayanan Kami</h2>
            <p class="section-lead" style="margin: 0 auto;">
                Temukan ruang untuk bersekutu dan melayani sesuai minat serta tahap kehidupan Anda.
            </p>
        </div>

        <div class="ministries-grid-wrap">
            <!-- Kids -->
            <div class="ministry-box fade-in">
                <img src="https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?w=600&auto=format&fit=crop&q=80" alt="Sekolah Minggu Filadelfia Kids" class="ministry-box-img">
                <div class="ministry-box-body">
                    <span class="ministry-tag">Generasi Penerus</span>
                    <h3 class="ministry-title">Filadelfia Kids</h3>
                    <p class="ministry-p">
                        Kelas anak untuk mengenal cerita Alkitab, berdoa, dan belajar dalam suasana yang menyenangkan.
                    </p>
                </div>
            </div>

            <!-- Youth -->
            <div class="ministry-box fade-in" style="transition-delay: 0.1s;">
                <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=600&auto=format&fit=crop&q=80" alt="Pemuda dan Remaja" class="ministry-box-img">
                <div class="ministry-box-body">
                    <span class="ministry-tag">Pemuda</span>
                    <h3 class="ministry-title">Pemuda Filadelfia</h3>
                    <p class="ministry-p">
                        Ruang bagi remaja dan mahasiswa untuk bersekutu, bertanya, dan bertumbuh dalam iman.
                    </p>
                </div>
            </div>

            <!-- Praise & Worship -->
            <div class="ministry-box fade-in" style="transition-delay: 0.2s;">
                <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=600&auto=format&fit=crop&q=80" alt="Praise and Worship" class="ministry-box-img">
                <div class="ministry-box-body">
                    <span class="ministry-tag">Pujian & Musik</span>
                    <h3 class="ministry-title">Musik & Pujian</h3>
                    <p class="ministry-p">
                        Bersama-sama melayani jemaat melalui musik dan pujian dalam ibadah.
                    </p>
                </div>
            </div>

            <!-- Diakonia -->
            <div class="ministry-box fade-in" style="transition-delay: 0.3s;">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80" alt="Diakonia dan Komunitas Sel" class="ministry-box-img">
                <div class="ministry-box-body">
                    <span class="ministry-tag">Kepedulian Sosial</span>
                    <h3 class="ministry-title">Diakonia & Komunitas Sel</h3>
                    <p class="ministry-p">
                        Persekutuan kelompok kecil dan kegiatan kepedulian bagi masyarakat sekitar.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 5. UPCOMING EVENTS (AGENDA KEGIATAN MENDATANG)                             -->
    <!-- ========================================================================= -->
    <section class="events-panel">
        <div class="events-header-row fade-in">
            <div>
                <span class="section-kicker">
                    <i class="ri-calendar-line"></i> Agenda
                </span>
                <h2 class="section-headline" style="margin-bottom: 0.3rem;">Kegiatan mendatang</h2>
                <p style="color: var(--zaitun-text-secondary); font-size: 1.05rem;">
                    Lihat kegiatan dan persekutuan yang akan datang.
                </p>
            </div>
            <div>
                <a href="{{ route('public.events') }}" class="btn-outline-sand">
                    <span>Lihat semua kegiatan</span>
                    <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        </div>

        <div class="events-editorial-list">
            @forelse($upcomingEvents as $event)
                <div class="event-row-card fade-in">
                    <div class="event-date-block">
                        <div class="event-date-num">{{ $event->tanggal_mulai ? $event->tanggal_mulai->format('d') : '01' }}</div>
                        <div class="event-date-month">{{ $event->tanggal_mulai ? $event->tanggal_mulai->format('M') : 'BLN' }}</div>
                    </div>

                    <div class="event-info-block">
                        <h3>{{ $event->nama_event }}</h3>
                        <div class="event-meta-line">
                            <div class="event-meta-item">
                                <i class="ri-time-line" style="color: var(--zaitun-gold);"></i>
                                <span>{{ $event->tanggal_mulai ? $event->tanggal_mulai->format('H:i') : '08:00' }} WIB</span>
                            </div>
                            <div class="event-meta-item">
                                <i class="ri-map-pin-line" style="color: var(--zaitun-gold);"></i>
                                <span>{{ $event->lokasi ?? 'Gereja Filadelfia' }}</span>
                            </div>
                            @if ($event->pembicara)
                                <div class="event-meta-item">
                                    <i class="ri-user-voice-line" style="color: var(--zaitun-gold);"></i>
                                    <span>{{ $event->pembicara }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('public.event.detail', $event->slug) }}" class="btn-primary-olive" style="padding: 0.65rem 1.4rem; font-size: 0.88rem;">
                            <span>Detail Event</span>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            @empty
                <!-- Clean Minimalist Fallback Events -->
                <div class="event-row-card fade-in">
                    <div class="event-date-block">
                        <div class="event-date-num">MGG</div>
                        <div class="event-date-month">SETIAP</div>
                    </div>

                    <div class="event-info-block">
                        <h3>Ibadah Minggu</h3>
                        <div class="event-meta-line">
                            <div class="event-meta-item">
                                <i class="ri-time-line" style="color: var(--zaitun-gold);"></i>
                                <span>08:00 WIB (Pagi) & 18:00 WIB (Sore)</span>
                            </div>
                            <div class="event-meta-item">
                                <i class="ri-map-pin-line" style="color: var(--zaitun-gold);"></i>
                                <span>Gereja Filadelfia</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="#jadwal-ibadah" class="btn-primary-olive" style="padding: 0.65rem 1.4rem; font-size: 0.88rem;">
                            <span>Jadwal Ibadah</span>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>

                <div class="event-row-card fade-in" style="transition-delay: 0.15s;">
                    <div class="event-date-block">
                        <div class="event-date-num">RAB</div>
                        <div class="event-date-month">SETIAP</div>
                    </div>

                    <div class="event-info-block">
                        <h3>Doa Tengah Minggu & Pendalaman Alkitab</h3>
                        <div class="event-meta-line">
                            <div class="event-meta-item">
                                <i class="ri-time-line" style="color: var(--zaitun-gold);"></i>
                                <span>Pukul 19:00 - 20:30 WIB</span>
                            </div>
                            <div class="event-meta-item">
                                <i class="ri-map-pin-line" style="color: var(--zaitun-gold);"></i>
                                <span>Ruang Doa Syafaat & Zoom</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('public.contact') }}" class="btn-primary-olive" style="padding: 0.65rem 1.4rem; font-size: 0.88rem;">
                            <span>Informasi Lokasi</span>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 6. BIBLICAL INSPIRATION BANNER (THE OLIVE TREE IDENTITY)                 -->
    <!-- ========================================================================= -->
    <section class="quote-block-section">
        <div class="quote-box fade-in">
            <p>
                “Tetapi aku ini seperti pohon zaitun yang menghijau di dalam rumah Allah; aku percaya akan kasih setia Allah untuk seterusnya dan selamanya.”
            </p>
            <cite>Mazmur 52:10</cite>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 7. FEATURED GALLERY (MOMEN DOKUMENTASI KEGIATAN)                          -->
    <!-- ========================================================================= -->
    <section class="gallery-panel">
        <div style="max-width: 1280px; margin: 0 auto; text-align: center;" class="fade-in">
            <span class="section-kicker">
                <i class="ri-camera-lens-line"></i> Galeri
            </span>
            <h2 class="section-headline">Momen Bersama Jemaat</h2>
            <p class="section-lead" style="margin: 0 auto;">
                Foto dari ibadah dan kegiatan jemaat.
            </p>
        </div>

        <div class="gallery-quad-grid">
            @forelse($featuredAlbums as $album)
                <div class="gallery-quad-item fade-in" onclick="window.location.href='{{ route('public.gallery.album', $album->slug) }}'">
                    @if ($album->cover_image)
                        <img src="{{ asset('storage/' . preg_replace('#^(?:/?storage/)+#', '', $album->cover_image)) }}" alt="{{ $album->judul_album }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=900&amp;auto=format&amp;fit=crop&amp;q=80';">
                    @else
                        <img src="https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=900&auto=format&fit=crop&q=80" alt="{{ $album->judul_album }}">
                    @endif
                    <div class="gallery-quad-overlay">
                        <div>
                            <div class="gallery-quad-title">{{ $album->judul_album }}</div>
                            <div class="gallery-quad-meta">
                                <i class="ri-camera-3-line"></i>
                                <span>{{ $album->jumlah_foto ?? 0 }} foto</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="gallery-quad-item fade-in" onclick="window.location.href='{{ route('public.gallery') }}'">
                    <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=600&auto=format&fit=crop&q=80" alt="Ibadah Syukur">
                    <div class="gallery-quad-overlay">
                        <div>
                            <div class="gallery-quad-title">Ibadah Syukur & Pujian Raya</div>
                            <div class="gallery-quad-meta"><i class="ri-camera-3-line"></i> 14 Foto Momen</div>
                        </div>
                    </div>
                </div>

                <div class="gallery-quad-item fade-in" onclick="window.location.href='{{ route('public.gallery') }}'">
                    <img src="https://images.unsplash.com/photo-1544427920-c49ccfb85579?w=600&auto=format&fit=crop&q=80" alt="Pelayanan Kasih">
                    <div class="gallery-quad-overlay">
                        <div>
                            <div class="gallery-quad-title">Aksi Sosial Kasih Persaudaraan</div>
                            <div class="gallery-quad-meta"><i class="ri-camera-3-line"></i> 26 Foto Momen</div>
                        </div>
                    </div>
                </div>

                <div class="gallery-quad-item fade-in" onclick="window.location.href='{{ route('public.gallery') }}'">
                    <img src="https://images.unsplash.com/photo-1485546246426-74dc88dec4d9?w=600&auto=format&fit=crop&q=80" alt="Retreat Pemuda">
                    <div class="gallery-quad-overlay">
                        <div>
                            <div class="gallery-quad-title">Retreat & Kebersamaan Pemuda</div>
                            <div class="gallery-quad-meta"><i class="ri-camera-3-line"></i> 20 Foto Momen</div>
                        </div>
                    </div>
                </div>

                <div class="gallery-quad-item fade-in" onclick="window.location.href='{{ route('public.gallery') }}'">
                    <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=600&auto=format&fit=crop&q=80" alt="Baptisan Kudus">
                    <div class="gallery-quad-overlay">
                        <div>
                            <div class="gallery-quad-title">Momen Doa & Penyerahan Diri</div>
                            <div class="gallery-quad-meta"><i class="ri-camera-3-line"></i> 18 Foto Momen</div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div style="text-align: center; margin-top: 3.5rem;">
            <a href="{{ route('public.gallery') }}" class="btn-outline-sand">
                <i class="ri-image-line"></i>
                <span>Lihat semua foto</span>
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 8. WORSHIP SCHEDULE & VISIT US SECTION (JOIN US SUNDAYS)                 -->
    <!-- ========================================================================= -->
    <section class="schedule-panel" id="jadwal-ibadah">
        <div style="max-width: 1280px; margin: 0 auto; text-align: center;" class="fade-in">
            <span class="section-kicker">
                <i class="ri-time-line"></i> Jadwal ibadah
            </span>
            <h2 class="section-headline">Beribadah bersama kami</h2>
            <p class="section-lead" style="margin: 0 auto;">
                Silakan pilih waktu ibadah yang sesuai untuk Anda dan keluarga.
            </p>
        </div>

        <div class="schedule-three-grid">
            <!-- Ibadah 1 -->
            <div class="schedule-tile featured-tile fade-in">
                <span class="tile-kicker">Ibadah Pagi</span>
                <div class="tile-time">08:00 WIB</div>
                <h3 class="tile-title">Ibadah Raya Sesi I</h3>
                <p class="tile-desc">
                    Ibadah Minggu pagi dan Sekolah Minggu untuk anak-anak.
                </p>
                <div style="margin-top: auto; display: flex; align-items: center; gap: 0.5rem; color: var(--zaitun-primary); font-size: 0.86rem; font-weight: 700;">
                    <i class="ri-checkbox-circle-line"></i>
                    <span>Tersedia Sekolah Minggu</span>
                </div>
            </div>

            <!-- Ibadah 2 -->
            <div class="schedule-tile fade-in" style="transition-delay: 0.1s;">
                <span class="tile-kicker">Ibadah Petang</span>
                <div class="tile-time">18:00 WIB</div>
                <h3 class="tile-title">Ibadah Raya Sesi II</h3>
                <p class="tile-desc">
                    Ibadah Minggu sore terbuka bagi seluruh jemaat dan pengunjung.
                </p>
                <div style="margin-top: auto; display: flex; align-items: center; gap: 0.5rem; color: var(--zaitun-terracotta); font-size: 0.86rem; font-weight: 700;">
                    <i class="ri-broadcast-line"></i>
                    <span>Tersedia Live Streaming</span>
                </div>
            </div>

            <!-- Midweek Prayer -->
            <div class="schedule-tile fade-in" style="transition-delay: 0.2s;">
                <span class="tile-kicker">Tengah Minggu</span>
                <div class="tile-time">19:00 WIB</div>
                <h3 class="tile-title">Doa & Pendalaman Firman</h3>
                <p class="tile-desc">
                    Persekutuan doa dan pendalaman firman setiap Rabu malam.
                </p>
                <div style="margin-top: auto; display: flex; align-items: center; gap: 0.5rem; color: var(--zaitun-deep); font-size: 0.86rem; font-weight: 700;">
                    <i class="ri-global-line"></i>
                    <span>Tatap Muka & Hybrid Zoom</span>
                </div>
            </div>
        </div>

        <!-- Visit Info Box -->
        <div style="max-width: 900px; margin: 4rem auto 0; background: var(--zaitun-white); border: 1px solid var(--zaitun-border); border-radius: var(--radius-lg); padding: 2.8rem; box-shadow: var(--shadow-card); text-align: center;" class="fade-in">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 800; margin-bottom: 0.8rem; color: var(--zaitun-deep);">
                Baru pertama kali berkunjung?
            </h3>
            <p style="color: var(--zaitun-text-secondary); font-size: 1.05rem; margin-bottom: 2rem; max-width: 660px; margin-left: auto; margin-right: auto;">
                Kami siap membantu Anda menemukan lokasi dan menjawab pertanyaan seputar ibadah.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('public.contact') }}" class="btn-terracotta-cta">
                    <i class="ri-map-pin-line"></i>
                    <span>Petunjuk lokasi</span>
                </a>
                <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Gereja%20Filadelfia,%20saya%20ingin%20bertanya%20seputar%20ibadah" target="_blank" class="btn-outline-sand">
                    <i class="ri-whatsapp-line" style="color: #25D366;"></i>
                    <span>Tanya via WhatsApp</span>
                </a>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
<script>
    const heroVideo = document.querySelector('.hero-background-video');
    if (heroVideo && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        heroVideo.pause();
    }
</script>
@endpush
