<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DataAccessRequest;
use App\Models\DataTable;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataAccessRequestController extends Controller
{
    private function getSecretToken(): string
    {
        return config('app.data_access_secret', env('DATA_ACCESS_SECRET', 'ubah_token_rahasia_ini'));
    }

    public function showForm(Request $request)
    {
        $token = $request->query('token');

        if (!hash_equals($this->getSecretToken(), $token ?? '')) {
            return response()->view('errors.403', ['message' => '🔐 Link akses tidak valid atau sudah kedaluwarsa.'], 403);
        }

        $availableTables = DataTable::where('status', true)->select('id', 'table_name')->orderBy('table_name')->pluck('table_name', 'id'); // [uuid => 'nama_tabel']

        $branches = Branch::where('is_active', true)->select('id', 'name', 'code')->orderBy('name')->pluck('name', 'id'); // [uuid => 'nama_branch']

        return view('public.request-form', compact('availableTables', 'branches'));
    }

    public function store(Request $request)
    {
        Log::info('=== [DEBUG] STORE START ===');
        Log::info('Token Query: ' . $request->query('token'));
        Log::info('Token Input: ' . $request->input('_token_access'));
        Log::info('All Inputs:', $request->all());

        $token = $request->query('token') ?? $request->input('_token_access');

        if (!hash_equals($this->getSecretToken(), $token ?? '')) {
            Log::warning('Token validation failed');
            abort(403, 'Permintaan tidak valid.');
        }

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

            Log::info('Validation Passed. Validated Data:', $validated);

            $tableNames = DataTable::whereIn('id', $validated['requested_tables'])->where('status', true)->pluck('table_name')->toArray();

            $branch = Branch::find($validated['branch_id']);
            $branchName = $branch ? $branch->name : 'unknown';

            $model = new DataAccessRequest();
            Log::info('Model Fillable:', $model->getFillable());

            $dataToSave = [
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
            ];

            Log::info('Data to Save:', $dataToSave);

            Log::info('Attempting DataAccessRequest::create()...');
            $dataRequest = DataAccessRequest::create($dataToSave);

            Log::info('✅ Create Success! ID: ' . $dataRequest->id);
            Log::info('Saved Tables (from DB):', $dataRequest->requested_tables);

            $this->sendNotificationEmail($dataRequest, $tableNames, $branchName);

            return redirect()
                ->route('public.data-access.request', ['token' => $token])
                ->with('success', '✅ Permintaan akses data Anda telah diterima.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation Failed:', $e->errors());
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('❌ CRITICAL EXCEPTION:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', '⚠️ Error: ' . $e->getMessage());
        }
    }

    private function sendNotificationEmail($dataRequest, array $tableNames, string $branchName): void
    {
        $projectUrl = $dataRequest->project_url ?: 'Tidak diisi';

        Mail::raw("🔔 Permintaan Akses Data Baru\n\n" . "👤 Nama: {$dataRequest->requester_name}\n" . "📧 Email: {$dataRequest->requester_email}\n" . "📦 Project: {$dataRequest->project_name}\n" . "🔗 URL: {$projectUrl}\n" . '🗄️ Tabel: ' . implode(', ', $tableNames) . "\n" . "🏢 Branch: {$branchName}\n\n" . "📝 Tujuan:\n{$dataRequest->purpose}", function ($message) {
            $message->to('filadelfiamusa@gmail.com')->subject('🔔 Permintaan Akses Data Baru');
        });
    }
}
