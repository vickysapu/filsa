<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\DataAccessRequestMail;
use App\Models\Branch;
use App\Models\DataAccessRequest;
use App\Models\DataTable;
use App\Services\Security\DataAccessSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataAccessRequestController extends Controller
{
    private const ADMIN_EMAIL = 'filadelfiamusa@gmail.com';

    public function __construct(private DataAccessSecurityService $security)
    {
    }

    private function getSecretToken(): string
    {
        return (string) config('app.data_access_secret');
    }

    private function isValidAccessToken(?string $token): bool
    {
        $secret = $this->getSecretToken();

        if ($secret === '' || $token === null || $token === '') {
            return false;
        }

        return hash_equals($secret, $token);
    }

    public function showForm(Request $request)
    {
        $availableTables = DataTable::where('status', true)->select('id', 'table_name')->orderBy('table_name')->pluck('table_name', 'id');

        $branches = Branch::where('is_active', true)->select('id', 'name', 'code')->orderBy('name')->pluck('name', 'id');

        return view('public.request-form', compact('availableTables', 'branches'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'requester_name' => 'required|string|max:100',
                'requester_email' => 'required|email|max:255',
                'project_name' => 'required|string|max:150',
                'project_url' => 'nullable|url|max:255',
                'purpose' => 'required|string|min:50|max:1000',
                'requested_tables' => ['required', 'array', 'min:1'],
                'requested_tables.*' => ['uuid', Rule::exists('data_tables', 'id')->where(fn($q) => $q->where('status', true))],
                'branch_id' => ['required', 'uuid', Rule::exists('branches', 'id')->where(fn($q) => $q->where('is_active', true))],
                'agreed_terms' => 'accepted',
            ]);

            $tableNames = DataTable::whereIn('id', $validated['requested_tables'])->where('status', true)->pluck('table_name')->toArray();

            $branch = Branch::find($validated['branch_id']);
            $branchName = $branch ? $branch->name : 'unknown';

            $dataRequest = DataAccessRequest::create([
                'id' => (string) Str::uuid(),
                'requester_name' => htmlspecialchars(trim($validated['requester_name']), ENT_QUOTES, 'UTF-8'),
                'requester_email' => strtolower(trim($validated['requester_email'])),
                'project_name' => htmlspecialchars(trim($validated['project_name']), ENT_QUOTES, 'UTF-8'),
                'project_url' => $validated['project_url'] ? strip_tags(trim($validated['project_url'])) : null,
                'purpose' => strip_tags(trim($validated['purpose'])),
                'requested_tables' => $tableNames,
                'branch_id' => $validated['branch_id'],
                'branch_name' => $branchName,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit($request->userAgent(), 255),
                'request_hash' => hash('sha256', $validated['requester_email'] . implode('-', $validated['requested_tables']) . now()->timestamp),
            ]);

            $oneTimeKey = $this->security->issueOneTimeKey($dataRequest->id);
            $confirmUrl = route('public.data-access.confirm', [
                'id' => $dataRequest->id,
                'key' => $oneTimeKey,
            ]);

            $this->sendEmails($dataRequest, $tableNames, $oneTimeKey, $confirmUrl);

            return redirect()
                ->route('public.data-access.request')
                ->with('success', '✅ Permintaan akses data Anda telah diterima. Silakan cek email Anda untuk konfirmasi.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Data access request error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', '⚠️ Error: ' . $e->getMessage());
        }
    }

    public function confirm(string $id, string $key)
    {
        $dataRequest = DataAccessRequest::find($id);

        if (!$dataRequest || !$this->security->consumeOneTimeKey($id, $key)) {
            return redirect()
                ->route('data-portal.verify')
                ->with('error', 'Kunci konfirmasi tidak valid, sudah dipakai, atau sudah kedaluwarsa.');
        }

        $dataRequest->update([
            'email_verified_at' => now(),
        ]);

        Log::info('Email peminta akses data terkonfirmasi', ['request_id' => $id]);

        return redirect()
            ->route('data-portal.verify')
            ->with('success', 'Email Anda berhasil dikonfirmasi. Token akses permanen akan dikirim ke email ini setelah permintaan disetujui admin.');
    }

    private function sendEmails(DataAccessRequest $dataRequest, array $tableNames, string $oneTimeKey, string $confirmUrl): void
    {
        $targets = [
            'admin' => self::ADMIN_EMAIL,
            'user' => $dataRequest->requester_email,
        ];

        foreach ($targets as $audience => $to) {
            try {
                Mail::to($to)->send(new DataAccessRequestMail(
                    $dataRequest,
                    $tableNames,
                    $oneTimeKey,
                    $audience,
                    $audience === 'user' ? $confirmUrl : null
                ));
            } catch (\Throwable $e) {
                Log::error("Gagal mengirim email {$audience} data access: " . $e->getMessage());
            }
        }
    }
}