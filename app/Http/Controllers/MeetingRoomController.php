<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MeetingRoomController extends Controller
{
    public function show(Meeting $meeting)
    {
        $this->authorizeJoin($meeting);

        return view('meetings.room', [
            'meeting' => $meeting,
            'guestName' => Auth::user()?->name,
            'leaveUrl' => Auth::check() ? route('dashboard') : route('public.home'),
            'hasPassword' => !empty($meeting->room_password) && !$this->isHost($meeting),
            'side' => [
                'type' => $meeting->side_type ?: 'none',
                'content' => $meeting->side_content,
            ],
        ]);
    }

    public function token(Request $request, Meeting $meeting)
    {
        $this->authorizeJoin($meeting);

        $user = Auth::user();
        $isHost = $this->isHost($meeting);

        if ($meeting->room_password && !$isHost) {
            $request->validate(['password' => 'required|string']);
            if (!Hash::check($request->input('password'), $meeting->room_password)) {
                return response()->json(['message' => 'Password meeting salah.'], 422);
            }
        }

        if ($user) {
            // Awalan h- hanya diberikan server, jadi tidak bisa dipalsukan peserta
            $identity = ($isHost ? 'h-' : 'u-') . $user->id;
            $name = $user->name;
        } else {
            $data = $request->validate(['name' => 'required|string|max:50']);
            $identity = 'g-' . Str::random(12);
            $name = $data['name'];
        }

        $canPublish = $isHost || !$meeting->viewer_mode;
        $now = time();

        $jwt = $this->makeJwt(
            [
                'iss' => config('services.livekit.key'),
                'sub' => $identity,
                'name' => $name,
                'nbf' => $now,
                'exp' => $now + 6 * 3600,
                'video' => [
                    'room' => $meeting->meeting_id,
                    'roomJoin' => true,
                    'canPublish' => $canPublish,
                    'canSubscribe' => true,
                    'canPublishData' => true,
                    'canUpdateOwnMetadata' => true,
                    'roomAdmin' => $isHost,
                ],
            ],
            config('services.livekit.secret'),
        );

        return response()->json([
            'token' => $jwt,
            'url' => config('services.livekit.url'),
            'isHost' => $isHost,
            'canPublish' => $canPublish,
        ]);
    }

    /** Aksi host: mute, mute semua, keluarkan, izinkan/cabut bicara, ubah panel samping */
    public function control(Request $request, Meeting $meeting)
    {
        $this->authorizeJoin($meeting);
        abort_unless($this->isHost($meeting), 403, 'Hanya host yang boleh.');

        $data = $request->validate([
            'action' => 'required|in:mute,mute_all,kick,allow_speak,revoke_speak,side,youtube_start,youtube_stop',
            'identity' => 'nullable|string|max:100',
            'side_type' => 'nullable|in:none,text,image,video',
            'side_content' => 'nullable|string|max:5000',
        ]);

        $action = $data['action'];
        $identity = $data['identity'] ?? null;

        if (in_array($action, ['mute', 'kick', 'allow_speak', 'revoke_speak'])) {
            abort_if(!$identity, 422, 'Peserta tidak dipilih.');
            abort_if(str_starts_with($identity, 'h-'), 422, 'Tidak bisa dilakukan pada host.');
        }

        switch ($action) {
            case 'kick':
                $this->lk($meeting, 'RemoveParticipant', ['room' => $meeting->meeting_id, 'identity' => $identity]);
                break;

            case 'mute':
                $this->muteAudio($meeting, $identity);
                break;

            case 'mute_all':
                $this->muteAudio($meeting, null);
                break;

            case 'allow_speak':
            case 'revoke_speak':
                $can = $action === 'allow_speak';
                $this->lk($meeting, 'UpdateParticipant', [
                    'room' => $meeting->meeting_id,
                    'identity' => $identity,
                    'permission' => [
                        'can_subscribe' => true,
                        'can_publish' => $can,
                        'can_publish_data' => true,
                    ],
                ]);
                break;

            case 'side':
                $type = $data['side_type'] ?? 'none';
                $content = $data['side_content'] ?? null;
                if (in_array($type, ['image', 'video']) && !preg_match('#^https?://#i', (string) $content)) {
                    abort(422, 'URL harus diawali http:// atau https://');
                }
                $meeting->update(['side_type' => $type, 'side_content' => $content]);
                break;

            case 'youtube_start':
                $key = $meeting->youtube_stream_key;
                abort_if(!$key, 422, 'Stream key YouTube belum diisi di pengaturan meeting.');
                abort_if(count($this->activeEgress($meeting)) > 0, 422, 'Siaran langsung sudah berjalan.');

                $this->egress($meeting, 'StartRoomCompositeEgress', [
                    'room_name' => $meeting->meeting_id,
                    'layout' => 'speaker', // atau 'grid' / 'single-speaker'
                    'preset' => 'H264_720P_30', // 720p cukup & hemat bandwidth
                    'stream_outputs' => [
                        [
                            'protocol' => 'RTMP',
                            'urls' => ['rtmp://a.rtmp.youtube.com/live2/' . $key],
                        ],
                    ],
                ]);
                break;

            case 'youtube_stop':
                foreach ($this->activeEgress($meeting) as $e) {
                    $this->egress($meeting, 'StopEgress', ['egress_id' => $e['egress_id']]);
                }
                break;
        }

        return response()->json(['success' => true]);
    }

    private function muteAudio(Meeting $meeting, ?string $only): void
    {
        $res = $this->lk($meeting, 'ListParticipants', ['room' => $meeting->meeting_id]);

        foreach ($res->json('participants') ?? [] as $p) {
            $id = $p['identity'] ?? '';
            if (str_starts_with($id, 'h-')) {
                continue;
            } // host tidak ikut di-mute
            if ($only && $id !== $only) {
                continue;
            }

            foreach ($p['tracks'] ?? [] as $t) {
                if (($t['type'] ?? 'AUDIO') !== 'AUDIO' || ($t['muted'] ?? false)) {
                    continue;
                }
                $this->lk($meeting, 'MutePublishedTrack', [
                    'room' => $meeting->meeting_id,
                    'identity' => $id,
                    'track_sid' => $t['sid'],
                    'muted' => true,
                ]);
            }
        }
    }

    /** Memanggil API server LiveKit (Twirp) */
    private function lk(Meeting $meeting, string $method, array $body)
    {
        $base = rtrim(str_replace(['wss://', 'ws://'], ['https://', 'http://'], config('services.livekit.url')), '/');
        $now = time();

        $jwt = $this->makeJwt(
            [
                'iss' => config('services.livekit.key'),
                'nbf' => $now,
                'exp' => $now + 60,
                'video' => ['roomAdmin' => true, 'room' => $meeting->meeting_id, 'roomList' => true],
            ],
            config('services.livekit.secret'),
        );

        $res = Http::withToken($jwt)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->post("$base/twirp/livekit.RoomService/$method", $body);

        abort_unless($res->successful(), 502, 'Server video menolak perintah.');

        return $res;
    }

    /** Memanggil API Egress LiveKit (Twirp) */
    private function egress(Meeting $meeting, string $method, array $body)
    {
        $base = rtrim(str_replace(['wss://', 'ws://'], ['https://', 'http://'], config('services.livekit.url')), '/');
        $now = time();

        $jwt = $this->makeJwt(
            [
                'iss' => config('services.livekit.key'),
                'nbf' => $now,
                'exp' => $now + 60,
                'video' => ['roomRecord' => true, 'room' => $meeting->meeting_id],
            ],
            config('services.livekit.secret'),
        );

        $res = Http::withToken($jwt)
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post("$base/twirp/livekit.Egress/$method", $body);

        abort_unless($res->successful(), 502, 'Egress gagal: ' . ($res->json('msg') ?? 'server video menolak perintah.'));

        return $res;
    }

    /** Daftar egress yang sedang aktif untuk room ini */
    private function activeEgress(Meeting $meeting): array
    {
        return $this->egress($meeting, 'ListEgress', [
            'room_name' => $meeting->meeting_id,
            'active' => true,
        ])->json('items') ?? [];
    }

    private function makeJwt(array $payload, string $secret): string
    {
        $b64 = fn($d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');

        $header = $b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body = $b64(json_encode($payload));
        $sig = $b64(hash_hmac('sha256', "$header.$body", $secret, true));

        return "$header.$body.$sig";
    }

    private function isHost(Meeting $meeting): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $meeting->created_by == $user->id || $user->hasPermission('meetings.edit') || $meeting->hosts()->where('users.id', $user->id)->exists();
    }

    private function authorizeJoin(Meeting $meeting): void
    {
        abort_unless($meeting->platform === 'internal' && $meeting->is_active, 404);
        abort_if($meeting->tanggal_selesai && $meeting->tanggal_selesai->isPast(), 403, 'Meeting sudah selesai.');
    }
}
