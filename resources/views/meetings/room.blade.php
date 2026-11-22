<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->nama_meeting }}</title>
    <style>
        [hidden] {
            display: none !important
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #111;
            color: #fff;
            font-family: system-ui, sans-serif
        }

        header,
        footer {
            padding: 10px 16px;
            background: #1b1b1b
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        footer {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap
        }

        #main {
            flex: 1;
            display: flex;
            min-height: 0
        }

        #grid {
            flex: 1;
            display: grid;
            gap: 8px;
            padding: 8px;
            overflow: auto;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            grid-auto-rows: minmax(180px, 1fr)
        }

        .tile {
            position: relative;
            background: #222;
            border-radius: 10px;
            overflow: hidden
        }

        .tile video {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .tile.screen video {
            object-fit: contain;
            background: #000
        }

        .tile .name {
            position: absolute;
            left: 8px;
            bottom: 8px;
            background: rgba(0, 0, 0, .6);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 13px
        }

        button {
            padding: 9px 14px;
            border: 0;
            border-radius: 8px;
            background: #333;
            color: #fff;
            cursor: pointer;
            font-size: 14px
        }

        button:disabled {
            opacity: .4;
            cursor: not-allowed
        }

        button.off {
            background: #b45309
        }

        button.on {
            background: #2563eb
        }

        button.leave {
            background: #dc2626
        }

        button.sm {
            padding: 4px 8px;
            font-size: 12px
        }

        #sidebar {
            position: relative;
            width: var(--sbw, 340px);
            min-width: 240px;
            max-width: 70vw;
            background: #1b1b1b;
            border-left: 1px solid #2a2a2a;
            display: none;
            flex-direction: column;
            min-height: 0
        }

        #sidebar.open {
            display: flex
        }

        #resizer {
            position: absolute;
            left: -4px;
            top: 0;
            bottom: 0;
            width: 9px;
            cursor: col-resize;
            z-index: 6;
            touch-action: none
        }

        #resizer::after {
            content: "";
            position: absolute;
            left: 3px;
            top: 50%;
            width: 3px;
            height: 48px;
            margin-top: -24px;
            border-radius: 3px;
            background: #555;
            transition: background .15s
        }

        #resizer:hover::after,
        #resizer.dragging::after,
        #resizer:focus-visible::after {
            background: #2563eb
        }

        body.resizing {
            user-select: none;
            cursor: col-resize
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid #2a2a2a
        }

        .tabs button {
            flex: 1;
            border-radius: 0;
            background: transparent;
            padding: 12px 6px
        }

        .tabs button.active {
            background: #2a2a2a
        }

        .pane {
            flex: 1;
            display: none;
            flex-direction: column;
            min-height: 0
        }

        .pane.active {
            display: flex
        }

        #peopleList,
        #chatLog,
        #sideBody {
            flex: 1;
            overflow: auto;
            padding: 10px
        }

        .person {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
            padding: 7px 0;
            border-bottom: 1px solid #2a2a2a;
            font-size: 14px
        }

        .pact {
            display: flex;
            gap: 4px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end
        }

        .badge {
            background: #7c3aed;
            border-radius: 5px;
            padding: 1px 6px;
            font-size: 11px;
            margin-left: 6px
        }

        .msg {
            margin-bottom: 10px;
            font-size: 14px;
            word-break: break-word
        }

        .msg b {
            display: block;
            font-size: 12px;
            color: #9ca3af
        }

        .msg.mine b {
            color: #60a5fa
        }

        #chatForm {
            display: flex;
            gap: 6px;
            padding: 8px;
            border-top: 1px solid #2a2a2a
        }

        #chatForm input {
            flex: 1;
            padding: 9px;
            border-radius: 8px;
            border: 0
        }

        #sideBody .lyrics {
            white-space: pre-wrap;
            font-size: 20px;
            line-height: 1.6;
            text-align: center
        }

        #sideBody img,
        #sideBody video {
            width: 100%;
            border-radius: 8px
        }

        #sideBody iframe {
            width: 100%;
            aspect-ratio: 16/9;
            border: 0;
            border-radius: 8px
        }

        #sideEditor {
            padding: 8px;
            border-top: 1px solid #2a2a2a;
            display: flex;
            flex-direction: column;
            gap: 6px
        }

        #sideEditor select,
        #sideEditor textarea {
            padding: 8px;
            border-radius: 8px;
            border: 0;
            width: 100%
        }

        #toast {
            position: fixed;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            background: #2563eb;
            padding: 8px 16px;
            border-radius: 8px;
            z-index: 20
        }

        @media(max-width:768px) {
            #sidebar {
                position: fixed;
                inset: 0 0 56px 0;
                width: auto;
                max-width: none;
                z-index: 5
            }

            #resizer {
                display: none
            }

            footer button {
                padding: 8px 10px;
                font-size: 13px
            }
        }
    </style>
</head>

<body>

    <div id="joinGate"
        style="position:fixed;inset:0;background:#111;display:flex;align-items:center;justify-content:center;z-index:30">
        <div style="background:#1b1b1b;padding:28px;border-radius:12px;width:320px;text-align:center">
            <h3 style="margin:0 0 6px">{{ $meeting->nama_meeting }}</h3>
            <p style="margin:0 0 16px;color:#aaa;font-size:14px">Masukkan nama Anda untuk bergabung</p>
            <input id="guestName" maxlength="50" placeholder="Nama Anda" value="{{ $guestName }}"
                style="width:100%;padding:10px;border-radius:8px;border:0;margin-bottom:10px">
            <input id="roomPass" type="password" placeholder="Password meeting" {{ $hasPassword ? '' : 'hidden' }}
                style="width:100%;padding:10px;border-radius:8px;border:0;margin-bottom:10px">
            <button id="btnJoinNow" style="width:100%;background:#2563eb">Gabung Meeting</button>
        </div>
    </div>

    <header>
        <strong>{{ $meeting->nama_meeting }}</strong>
        <span>
            <span id="liveBadge" class="badge" style="background:#dc2626" hidden>● LIVE</span>
            <span id="status">Menghubungkan…</span>
        </span>
    </header>
    <div id="main">
        <div id="grid"></div>

        <aside id="sidebar">
            <div id="resizer" role="separator" aria-orientation="vertical" tabindex="0"
                title="Geser untuk ubah lebar (klik 2x untuk reset)"></div>
            <div class="tabs">
                <button data-tab="people" class="active">Peserta (<span id="peopleCount">0</span>)</button>
                <button data-tab="chat">Chat <span id="chatBadge" class="badge" hidden>0</span></button>
                <button data-tab="side" id="tabSide" hidden>Panel</button>
            </div>

            <div class="pane active" id="pane-people">
                <div class="host-only" style="padding:8px;border-bottom:1px solid #2a2a2a" hidden>
                    <button class="sm" id="btnMuteAll">Mute semua peserta</button>
                </div>
                <div id="peopleList"></div>
            </div>

            <div class="pane" id="pane-chat">
                <div id="chatLog"></div>
                <form id="chatForm">
                    <input id="chatInput" maxlength="500" placeholder="Tulis pesan…" autocomplete="off">
                    <button type="submit" class="on">Kirim</button>
                </form>
            </div>

            <div class="pane" id="pane-side">
                <div id="sideBody"></div>
                <div id="sideEditor" class="host-only" hidden>
                    <select id="edSideType">
                        <option value="none">Tidak dipakai</option>
                        <option value="text">Teks / Lirik</option>
                        <option value="image">Gambar (URL)</option>
                        <option value="video">Video (YouTube / .mp4)</option>
                    </select>
                    <textarea id="edSideContent" rows="4" placeholder="Isi lirik atau URL"></textarea>
                    <button id="btnSideApply" class="on">Tampilkan ke semua peserta</button>
                </div>
            </div>
        </aside>
    </div>

    <footer>
        <button id="btnMic">Mikrofon</button>
        <button id="btnCam">Kamera</button>
        <button id="btnScreen">Bagikan Layar</button>
        <button id="btnHand">✋ Angkat Tangan</button>
        <button id="btnPanel">Peserta &amp; Chat</button>
        <button id="btnLive" class="host-only" hidden>🔴 Live YouTube</button>
        <button id="btnLeave" class="leave">Keluar</button>
    </footer>

    <div id="toast" hidden></div>

    <script src="https://cdn.jsdelivr.net/npm/livekit-client@2/dist/livekit-client.umd.min.js"></script>
    <script>
        const $ = id => document.getElementById(id);
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const tokenUrl = @json(route('meetings.token', $meeting->id));
        const controlUrl = @json(route('meetings.control', $meeting->id));
        const leaveUrl = @json($leaveUrl);
        const presetName = @json($guestName);
        const needPassword = @json($hasPassword);
        const viewerMode = @json((bool) $meeting->viewer_mode);
        let currentSide = @json($side);

        const {
            Room,
            RoomEvent,
            Track
        } = LivekitClient;
        const enc = new TextEncoder(),
            dec = new TextDecoder();
        const room = new Room({
            adaptiveStream: true,
            dynacast: true
        });

        let amHost = false,
            canPublish = true,
            handRaised = false,
            unread = 0,
            activeTab = 'people';
        const isHostIdentity = p => (p?.identity || '').startsWith('h-');

        /* ---------- Live YouTube ---------- */
        function updateLive() {
            const on = room.isRecording; // true selama egress aktif
            $('liveBadge').hidden = !on; // semua peserta melihat badge LIVE
            $('btnLive').textContent = on ? '⏹ Hentikan Live' : '🔴 Live YouTube';
            $('btnLive').classList.toggle('leave', on);
        }
        room.on(RoomEvent.RecordingStatusChanged, updateLive);

        $('btnLive').onclick = async () => {
            const btn = $('btnLive');
            const starting = !room.isRecording;
            if (!confirm(starting ? 'Mulai siaran langsung ke YouTube?' : 'Hentikan siaran langsung?')) return;
            btn.disabled = true;
            await hostAction(starting ? 'youtube_start' : 'youtube_stop');
            setTimeout(() => btn.disabled = false, 4000); // beri waktu egress menyala/berhenti
        };

        /* ---------- Util ---------- */
        function toast(msg) {
            const t = $('toast');
            t.textContent = msg;
            t.hidden = false;
            clearTimeout(toast._t);
            toast._t = setTimeout(() => t.hidden = true, 3500);
        }

        async function hostAction(action, identity = null, extra = {}) {
            const res = await fetch(controlUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action,
                    identity,
                    ...extra
                })
            });
            if (!res.ok) {
                const j = await res.json().catch(() => ({}));
                alert(j.message || 'Aksi gagal dijalankan.');
                return false;
            }
            return true;
        }

        /* ---------- Video tiles ---------- */
        const grid = $('grid');

        function getTile(key, label, cls = '') {
            let el = $('tile-' + key);
            if (!el) {
                el = document.createElement('div');
                el.id = 'tile-' + key;
                el.className = 'tile ' + cls;
                el.innerHTML = '<span class="name"></span>';
                el.querySelector('.name').textContent = label;
                grid.appendChild(el);
            }
            return el;
        }

        function attachTrack(track, pub, p) {
            if (track.kind === Track.Kind.Video) {
                const screen = pub.source === Track.Source.ScreenShare;
                const key = p.identity + (screen ? '-screen' : '');
                const label = (p.name || p.identity) + (screen ? ' (layar)' : '') + (p.isLocal && !screen ? ' (Anda)' : '');
                const tile = getTile(key, label, screen ? 'screen' : '');
                const v = track.attach();
                if (p.isLocal && !screen) v.style.transform = 'scaleX(-1)';
                tile.prepend(v);
            } else if (track.kind === Track.Kind.Audio && !p.isLocal) {
                document.body.appendChild(track.attach());
            }
        }

        function detachTrack(track, pub, p) {
            track.detach().forEach(el => el.remove());
            if (pub.source === Track.Source.ScreenShare) $('tile-' + p.identity + '-screen')?.remove();
        }

        /* ---------- Daftar peserta ---------- */
        function refreshPeople() {
            const list = $('peopleList');
            list.innerHTML = '';
            const all = [room.localParticipant, ...room.remoteParticipants.values()];
            $('peopleCount').textContent = all.length;

            all.forEach(p => {
                const row = document.createElement('div');
                row.className = 'person';
                const left = document.createElement('div');
                left.textContent = (p.name || p.identity) + (p.isLocal ? ' (Anda)' : '');
                if (isHostIdentity(p)) {
                    const b = document.createElement('span');
                    b.className = 'badge';
                    b.textContent = 'Host';
                    left.appendChild(b);
                }
                if (p.attributes?.hand === '1') left.append(' ✋');
                row.appendChild(left);

                const right = document.createElement('div');
                right.className = 'pact';
                right.append(p.isMicrophoneEnabled ? '🎤' : '🔇');

                if (amHost && !p.isLocal && !isHostIdentity(p)) {
                    const add = (label, fn) => {
                        const b = document.createElement('button');
                        b.className = 'sm';
                        b.textContent = label;
                        b.onclick = fn;
                        right.appendChild(b);
                    };
                    add('Mute', () => hostAction('mute', p.identity));
                    if (viewerMode) {
                        const can = !!p.permissions?.canPublish;
                        add(can ? 'Cabut' : 'Izinkan', () => hostAction(can ? 'revoke_speak' : 'allow_speak', p
                            .identity));
                    }
                    add('Keluarkan', () => {
                        if (confirm('Keluarkan ' + (p.name || 'peserta') + '?')) hostAction('kick', p
                            .identity);
                    });
                }
                row.appendChild(right);
                list.appendChild(row);
            });
        }

        /* ---------- Chat ---------- */
        function addChat(name, text, mine) {
            const box = document.createElement('div');
            box.className = 'msg' + (mine ? ' mine' : '');
            const b = document.createElement('b');
            b.textContent = name;
            const t = document.createElement('span');
            t.textContent = text;
            box.append(b, t);
            $('chatLog').appendChild(box);
            $('chatLog').scrollTop = $('chatLog').scrollHeight;
        }

        $('chatForm').onsubmit = async e => {
            e.preventDefault();
            const text = $('chatInput').value.trim();
            if (!text || room.state !== 'connected') return;
            await room.localParticipant.publishData(enc.encode(JSON.stringify({
                text
            })), {
                reliable: true,
                topic: 'chat'
            });
            addChat('Anda', text, true);
            $('chatInput').value = '';
        };

        /* ---------- Panel samping ---------- */
        function youtubeId(u) {
            const m = u.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([\w-]{11})/);
            return m ? m[1] : null;
        }

        function renderSide(s) {
            currentSide = s || {
                type: 'none',
                content: ''
            };
            const body = $('sideBody');
            body.innerHTML = '';
            const has = currentSide.type !== 'none' && currentSide.content;
            $('tabSide').hidden = !(has || amHost);

            if (!has) return;
            const c = currentSide.content;

            if (currentSide.type === 'text') {
                const d = document.createElement('div');
                d.className = 'lyrics';
                d.textContent = c;
                body.appendChild(d);
            } else if (currentSide.type === 'image' && /^https?:\/\//i.test(c)) {
                const i = document.createElement('img');
                i.src = c;
                body.appendChild(i);
            } else if (currentSide.type === 'video' && /^https?:\/\//i.test(c)) {
                const yt = youtubeId(c);
                if (yt) {
                    const f = document.createElement('iframe');
                    f.src = 'https://www.youtube.com/embed/' + yt;
                    f.allow = 'autoplay; encrypted-media; picture-in-picture';
                    f.allowFullscreen = true;
                    body.appendChild(f);
                } else if (/\.(mp4|webm)(\?.*)?$/i.test(c)) {
                    const v = document.createElement('video');
                    v.src = c;
                    v.controls = true;
                    body.appendChild(v);
                } else {
                    body.textContent = 'Format video tidak didukung. Gunakan link YouTube atau file .mp4';
                }
            }
        }

        $('btnSideApply').onclick = async () => {
            const s = {
                type: $('edSideType').value,
                content: $('edSideContent').value.trim()
            };
            if (s.type !== 'none' && !s.content) {
                alert('Isi panel masih kosong.');
                return;
            }
            if (!await hostAction('side', null, {
                    side_type: s.type,
                    side_content: s.content
                })) return;
            await room.localParticipant.publishData(enc.encode(JSON.stringify(s)), {
                reliable: true,
                topic: 'side'
            });
            renderSide(s);
            openTab('side');
        };

        /* ---------- Sidebar & tab ---------- */
        function openTab(name) {
            activeTab = name;
            document.querySelectorAll('.tabs button').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
            document.querySelectorAll('.pane').forEach(p => p.classList.toggle('active', p.id === 'pane-' + name));
            if (name === 'chat') {
                unread = 0;
                $('chatBadge').hidden = true;
            }
        }
        document.querySelectorAll('.tabs button').forEach(b => b.onclick = () => openTab(b.dataset.tab));
        $('btnPanel').onclick = () => $('sidebar').classList.toggle('open');

        /* ---------- Ubah lebar panel samping ---------- */
        (function() {
            const sidebar = $('sidebar'),
                handle = $('resizer');
            const MIN = 240,
                DEFAULT = 340;
            const maxW = () => Math.round(window.innerWidth * 0.7);
            const clamp = w => Math.min(Math.max(w, MIN), maxW());

            function setWidth(w, save = true) {
                w = clamp(w);
                sidebar.style.setProperty('--sbw', w + 'px');
                if (save) {
                    try {
                        localStorage.setItem('meetingSidebarWidth', w);
                    } catch (e) {}
                }
            }

            try {
                const saved = parseInt(localStorage.getItem('meetingSidebarWidth'), 10);
                if (saved) setWidth(saved, false);
            } catch (e) {}

            let startX = 0,
                startW = 0;

            handle.addEventListener('pointerdown', e => {
                startX = e.clientX;
                startW = sidebar.getBoundingClientRect().width;
                handle.setPointerCapture(e.pointerId); // tetap menangkap gerakan walau melewati iframe/video
                handle.classList.add('dragging');
                document.body.classList.add('resizing');
            });

            handle.addEventListener('pointermove', e => {
                if (!handle.hasPointerCapture(e.pointerId)) return;
                setWidth(startW + (startX - e.clientX), false); // geser ke kiri = melebar
            });

            const stop = e => {
                if (!handle.hasPointerCapture(e.pointerId)) return;
                handle.releasePointerCapture(e.pointerId);
                handle.classList.remove('dragging');
                document.body.classList.remove('resizing');
                setWidth(sidebar.getBoundingClientRect().width); // simpan lebar akhir
            };
            handle.addEventListener('pointerup', stop);
            handle.addEventListener('pointercancel', stop);

            handle.addEventListener('dblclick', () => setWidth(DEFAULT));

            // Keyboard: panah kiri/kanan
            handle.addEventListener('keydown', e => {
                if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
                e.preventDefault();
                const cur = sidebar.getBoundingClientRect().width;
                setWidth(cur + (e.key === 'ArrowLeft' ? 24 : -24));
            });
        })();

        /* ---------- Event LiveKit ---------- */
        room
            .on(RoomEvent.TrackSubscribed, (t, pub, p) => {
                attachTrack(t, pub, p);
                refreshPeople();
            })
            .on(RoomEvent.TrackUnsubscribed, (t, pub, p) => {
                detachTrack(t, pub, p);
                refreshPeople();
            })
            .on(RoomEvent.LocalTrackPublished, (pub, p) => {
                attachTrack(pub.track, pub, p);
                refreshPeople();
            })
            .on(RoomEvent.LocalTrackUnpublished, (pub, p) => {
                detachTrack(pub.track, pub, p);
                refreshPeople();
            })
            .on(RoomEvent.TrackMuted, refreshPeople)
            .on(RoomEvent.TrackUnmuted, refreshPeople)
            .on(RoomEvent.ParticipantConnected, p => {
                getTile(p.identity, p.name || p.identity);
                refreshPeople();
            })
            .on(RoomEvent.ParticipantDisconnected, p => {
                $('tile-' + p.identity)?.remove();
                $('tile-' + p.identity + '-screen')?.remove();
                refreshPeople();
            })
            .on(RoomEvent.ParticipantAttributesChanged, (changed, p) => {
                if (amHost && changed.hand === '1') toast((p.name || 'Peserta') + ' mengangkat tangan ✋');
                refreshPeople();
            })
            .on(RoomEvent.ParticipantPermissionsChanged, (prev, p) => {
                if (p.isLocal) {
                    const was = canPublish;
                    canPublish = !!p.permissions?.canPublish;
                    applyPublishState();
                    if (canPublish && !was) {
                        toast('Anda diizinkan bicara');
                        p.enableCameraAndMicrophone().catch(() => {});
                    } else if (!canPublish && was) {
                        toast('Izin bicara Anda dicabut');
                    }
                }
                refreshPeople();
            })
            .on(RoomEvent.DataReceived, (payload, p, kind, topic) => {
                let msg;
                try {
                    msg = JSON.parse(dec.decode(payload));
                } catch {
                    return;
                }

                if (topic === 'chat') {
                    addChat(p?.name || 'Peserta', String(msg.text || '').slice(0, 500), false);
                    if (!($('sidebar').classList.contains('open') && activeTab === 'chat')) {
                        unread++;
                        $('chatBadge').textContent = unread;
                        $('chatBadge').hidden = false;
                    }
                } else if (topic === 'side' && isHostIdentity(p)) { // hanya host yang boleh mengubah panel
                    renderSide(msg);
                    if (msg.type !== 'none') toast('Panel samping diperbarui');
                }
            })
            .on(RoomEvent.Disconnected, () => {
                $('status').textContent = 'Terputus';
            });

        /* ---------- Kontrol tombol ---------- */
        function bindToggle(btnId, isOn, toggle) {
            const btn = $(btnId);
            btn.onclick = async () => {
                try {
                    await toggle(!isOn());
                } catch (e) {
                    alert('Tidak bisa mengubah perangkat: ' + (e.message || e));
                }
                btn.classList.toggle('off', !isOn());
                refreshPeople();
            };
        }
        bindToggle('btnMic', () => room.localParticipant.isMicrophoneEnabled, v => room.localParticipant
            .setMicrophoneEnabled(v));
        bindToggle('btnCam', () => room.localParticipant.isCameraEnabled, v => room.localParticipant.setCameraEnabled(v));
        bindToggle('btnScreen', () => room.localParticipant.isScreenShareEnabled, v => room.localParticipant
            .setScreenShareEnabled(v));

        $('btnHand').onclick = async () => {
            handRaised = !handRaised;
            await room.localParticipant.setAttributes({
                hand: handRaised ? '1' : '0'
            });
            $('btnHand').classList.toggle('on', handRaised);
        };
        $('btnMuteAll').onclick = () => {
            if (confirm('Mute semua peserta (kecuali host)?')) hostAction('mute_all');
        };
        $('btnLeave').onclick = () => {
            room.disconnect();
            location.href = leaveUrl;
        };

        function applyPublishState() {
            ['btnMic', 'btnCam', 'btnScreen'].forEach(id => $(id).disabled = !canPublish);
        }

        /* ---------- Bergabung ---------- */
        async function join(name, password) {
            const res = await fetch(tokenUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    password
                })
            });
            if (!res.ok) {
                let msg = 'Tidak bisa bergabung ke meeting ini.';
                try {
                    const j = await res.json();
                    msg = j.message || msg;
                } catch {}
                alert(msg);
                return;
            }

            let info;
            try {
                info = await res.json();
                await room.connect(info.url, info.token);
            } catch (e) {
                console.error(e);
                $('status').textContent = 'Gagal: ' + (e.message || e);
                alert('Gagal terhubung ke server video: ' + (e.message || e));
                return;
            }

            amHost = info.isHost;
            canPublish = info.canPublish;
            $('joinGate').remove();
            $('status').textContent = 'Terhubung';
            document.querySelectorAll('.host-only').forEach(el => el.hidden = !amHost);
            if (amHost) {
                $('edSideType').value = currentSide.type;
                $('edSideContent').value = currentSide.content || '';
            }
            applyPublishState();

            const lp = room.localParticipant;
            getTile(lp.identity, (lp.name || lp.identity) + ' (Anda)');
            room.remoteParticipants.forEach(p => getTile(p.identity, p.name || p.identity));

            renderSide(currentSide);
            if (currentSide.type !== 'none' && currentSide.content) openTab('side'), $('sidebar').classList.add('open');
            refreshPeople();

            updateLive();

            if (canPublish) {
                try {
                    await lp.enableCameraAndMicrophone();
                } catch (e) {
                    alert('Izin kamera/mikrofon ditolak. Anda masuk sebagai penonton.');
                }
            } else {
                toast('Mode penonton. Angkat tangan jika ingin bicara.');
            }
        }

        $('btnJoinNow').onclick = () => {
            const name = $('guestName').value.trim();
            if (!name) {
                alert('Nama wajib diisi');
                return;
            }
            join(name, $('roomPass').value);
        };

        // User login tanpa password langsung masuk
        if (presetName && !needPassword) join(presetName, '');
    </script>
</body>

</html>
