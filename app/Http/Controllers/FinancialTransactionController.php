<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FinancialReport;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialTransactionController extends Controller
{
    private function getUserBranchId()
    {
        return Auth::user()?->roles->first()?->branch_id;
    }

    private function getCategories()
    {
        return [
            'income' => [
                'persepuluhan' => [['code' => 'pp', 'name' => 'Persepuluhan']],
                'iuran_pejabat' => [['code' => 'iuran_pejabat', 'name' => 'Iuran Pejabat']],
                'dana_misi_pendidikan' => [['code' => 'dana_misi', 'name' => 'Dana Misi dan Pendidikan']],
                'penerimaan_lain' => [['code' => 'lain_lain', 'name' => 'Penerimaan Lain-lain'], ['code' => 'setor_tunai', 'name' => 'Setor Tunai']],
                'bunga_bank' => [['code' => 'bunga', 'name' => 'Bunga Bank']],
            ],
            'expense' => [
                'biaya_majelis' => [['code' => 'setoran_pp_mp', 'name' => 'Setoran Persepuluhan ke MP'], ['code' => 'setoran_iuran_mp', 'name' => 'Setoran Iuran Pejabat ke MP'], ['code' => 'biaya_mikeria', 'name' => 'Biaya Misi, Mikeria, Upgrading'], ['code' => 'biaya_paskah_natal', 'name' => 'Biaya Paskah, HUT, Natal'], ['code' => 'biaya_rapat', 'name' => 'Biaya Rapat'], ['code' => 'biaya_kurikulum', 'name' => 'Biaya Kurikulum dan Kerja Dinas'], ['code' => 'biaya_sekretariat', 'name' => 'Biaya Sekretariat'], ['code' => 'biaya_lain_majelis', 'name' => 'Biaya Lain-lain Majelis'], ['code' => 'biaya_admin_bank', 'name' => 'Biaya Administrasi Bank']],
                'biaya_penatalayanan' => [['code' => 'biaya_buku_penatalayanan', 'name' => 'Biaya Pembelian Buku Penatalayanan'], ['code' => 'biaya_penatalayanan_lain', 'name' => 'Biaya Lain-lain Penatalayanan']],
                'biaya_penggembalaan' => [['code' => 'biaya_sekolah_minggu', 'name' => 'Biaya Program Sekolah Minggu'], ['code' => 'biaya_pemuda', 'name' => 'Biaya Program Pemuda'], ['code' => 'biaya_wanita', 'name' => 'Biaya Program Wanita'], ['code' => 'biaya_camp_seminar', 'name' => 'Biaya Camp, Seminar Hamba-hamba Tuhan']],
                'biaya_misi_pendidikan' => [['code' => 'setoran_dana_misi_mp', 'name' => 'Setoran Dana Misi dan Pendidikan ke MP'], ['code' => 'biaya_misi_seminar', 'name' => 'Biaya Misi, Seminar Misi MD'], ['code' => 'biaya_pendidikan_md', 'name' => 'Biaya Pendidikan MD']],
                'biaya_pelayanan_masyarakat' => [['code' => 'bantuan_sakit_bpjs', 'name' => 'Bantuan Sakit (Termasuk BPJS)'], ['code' => 'bantuan_dukacita', 'name' => 'Bantuan Dukacita'], ['code' => 'bantuan_pembangunan', 'name' => 'Bantuan Pembangunan Gereja'], ['code' => 'bantuan_bencana', 'name' => 'Bantuan Bencana Alam'], ['code' => 'biaya_multimedia', 'name' => 'Biaya Bidang dan Multi Media']],
            ],
        ];
    }

    public function index(Request $request)
    {
        $branchId = $this->getUserBranchId();
        $categories = $this->getCategories();

        // ✅ PASTIKAN $groups DIBANGUN DENGAN BENAR
        $groups = [
            'income' => [
                'title' => 'PEMASUKAN',
                'cats' => collect($categories['income'])->flatten(1)->pluck('code')->toArray(),
            ],
            'expense_majelis' => [
                'title' => 'BIAYA MAJELIS DAERAH',
                'cats' => collect($categories['expense']['biaya_majelis'])->pluck('code')->toArray(),
            ],
            'expense_penatalayanan' => [
                'title' => 'BIAYA BIDANG PENATALAYANAN',
                'cats' => collect($categories['expense']['biaya_penatalayanan'])->pluck('code')->toArray(),
            ],
            'expense_penggembalaan' => [
                'title' => 'BIAYA BIDANG PENGGEMBALAAN',
                'cats' => collect($categories['expense']['biaya_penggembalaan'])->pluck('code')->toArray(),
            ],
            'expense_misi' => [
                'title' => 'BIAYA BIDANG PENDIDIKAN DAN MISI',
                'cats' => collect($categories['expense']['biaya_misi_pendidikan'])->pluck('code')->toArray(),
            ],
            'expense_pelayanan' => [
                'title' => 'BIAYA BIDANG PELAYANAN MASYARAKAT',
                'cats' => collect($categories['expense']['biaya_pelayanan_masyarakat'])->pluck('code')->toArray(),
            ],
        ];

        $query = FinancialTransaction::with(['branch', 'creator'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->type === 'pemasukan') {
            $query->where('type', 'income');
        } elseif ($request->type === 'pengeluaran') {
            $query->where('type', 'expense');
        } elseif ($request->type === 'approved') {
            $query->whereNotNull('approved_at');
        }
        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
        if ($request->category) {
            $query->where('category_code', $request->category);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'transactions' => $query->get(),
                'categories' => $categories,
                'groups' => $groups, // ✅ PASTIKAN DIKIRIM JUGA DI AJAX
            ]);
        }

        $transactions = $query->paginate(20);

        return view('features.finance.transactions.index', compact('transactions', 'categories', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:50',
            'description' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'category_group' => 'required|string',
            'category_code' => 'required|string',
            'category_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'recipient_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $transaction = FinancialTransaction::create([
                'branch_id' => $this->getUserBranchId(),
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => $validated['description'],
                'type' => $validated['type'],
                'category_group' => $validated['category_group'],
                'category_code' => $validated['category_code'],
                'category_name' => $validated['category_name'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'recipient_name' => $validated['recipient_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil disimpan', 'data' => $transaction]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show(FinancialTransaction $transaction)
    {
        $userBranchId = Auth::user()?->roles->first()?->branch_id;

        if ($userBranchId && $transaction->branch_id !== $userBranchId) {
            abort(403);
        }

        $transaction->load([
            'branch',
            'category',   
            'creator',    
            'approver',   
        ]);

        return view('features.finance.transactions.show', [
            'transaction' => $transaction,
        ]);
    }
    
    private function getReportCategories()
    {
        return [
            'SALDO_AWAL' => ['name' => 'SALDO AWAL', 'group' => 'balance'],
            'PP' => ['name' => 'PERSEPULUHAN', 'group' => 'income'],
            'IURAN_PEJABAT' => ['name' => 'IURAN PEJABAT', 'group' => 'income'],
            'DANA_MISI' => ['name' => 'DANA MISI DAN PENDIDIKAN', 'group' => 'income'],
            'PENERIMAAN_LAIN' => ['name' => 'PENERIMAAN LAIN-LAIN', 'group' => 'income'],
            'BUNGA_BANK' => ['name' => 'BUNGA BANK', 'group' => 'income'],
            'SETORAN_PP_MP' => ['name' => 'Setoran Persepuluhan ke MP', 'group' => 'expense_majelis'],
            'SETORAN_IURAN_MP' => ['name' => 'Setoran Iuran Pejabat ke MP', 'group' => 'expense_majelis'],
            'BIAYA_MIKERIA' => ['name' => 'Biaya Misi, Mikeria, Upgrading', 'group' => 'expense_majelis'],
            'BIAYA_PASKAH_NATAL' => ['name' => 'Biaya Paskah, HUT, Natal', 'group' => 'expense_majelis'],
            'BIAYA_RAPAT' => ['name' => 'Biaya Rapat', 'group' => 'expense_majelis'],
            'BIAYA_KURIKULUM' => ['name' => 'Biaya Kurikulum dan Kerja Dinas', 'group' => 'expense_majelis'],
            'BIAYA_SEKRETARIAT' => ['name' => 'Biaya Sekretariat', 'group' => 'expense_majelis'],
            'BIAYA_LAIN_MAJELIS' => ['name' => 'Biaya Lain-lain Majelis', 'group' => 'expense_majelis'],
            'BIAYA_ADMIN_BANK' => ['name' => 'Biaya Administrasi Bank', 'group' => 'expense_majelis'],
            'BIAYA_BUKU_PENATALAYANAN' => ['name' => 'Biaya Pembelian Buku Penatalayanan', 'group' => 'expense_penatalayanan'],
            'BIAYA_PENATALAYANAN_LAIN' => ['name' => 'Biaya Lain-lain Penatalayanan', 'group' => 'expense_penatalayanan'],
            'BIAYA_SEKOLAH_MINGGU' => ['name' => 'Biaya Program Sekolah Minggu', 'group' => 'expense_penggembalaan'],
            'BIAYA_PEMUDA' => ['name' => 'Biaya Program Pemuda', 'group' => 'expense_penggembalaan'],
            'BIAYA_WANITA' => ['name' => 'Biaya Program Wanita', 'group' => 'expense_penggembalaan'],
            'BIAYA_CAMP_SEMINAR' => ['name' => 'Biaya Camp, Seminar Hamba-hamba Tuhan', 'group' => 'expense_penggembalaan'],
            'SETORAN_DANA_MISI_MP' => ['name' => 'Setoran Dana Misi dan Pendidikan ke MP', 'group' => 'expense_misi'],
            'BIAYA_MISI_SEMINAR' => ['name' => 'Biaya Misi, Seminar Misi MD', 'group' => 'expense_misi'],
            'BIAYA_PENDIDIKAN_MD' => ['name' => 'Biaya Pendidikan MD', 'group' => 'expense_misi'],
            'BANTUAN_SAKIT' => ['name' => 'Bantuan Sakit (Termasuk BPJS)', 'group' => 'expense_pelayanan'],
            'BANTUAN_DUKACITA' => ['name' => 'Bantuan Dukacita', 'group' => 'expense_pelayanan'],
            'BANTUAN_PEMBANGUNAN' => ['name' => 'Bantuan Pembangunan Gereja', 'group' => 'expense_pelayanan'],
            'BANTUAN_BENCANA' => ['name' => 'Bantuan Bencana Alam', 'group' => 'expense_pelayanan'],
            'BIAYA_MULTIMEDIA' => ['name' => 'Biaya Bidang dan Multi Media', 'group' => 'expense_pelayanan'],
        ];
    }

    public function edit(FinancialTransaction $transaction)
    {
        $branchId = $this->getUserBranchId();

        if ($branchId && $transaction->branch_id != $branchId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($transaction->approved_at) {
            return response()->json(['success' => false, 'message' => 'Transaksi yang sudah disetujui tidak dapat diedit.'], 400);
        }

        $transaction->transaction_date = $transaction->transaction_date?->format('Y-m-d');

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    public function update(Request $request, FinancialTransaction $transaction)
    {
        if ($transaction->approved_at) {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah disetujui'], 400);
        }

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'reference_number' => 'nullable|string|max:50',
            'description' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'category_group' => 'required|string',
            'category_code' => 'required|string',
            'category_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'recipient_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $transaction->update([
                'transaction_date' => $validated['transaction_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'description' => $validated['description'],
                'type' => $validated['type'],
                'category_group' => $validated['category_group'],
                'category_code' => $validated['category_code'],
                'category_name' => $validated['category_name'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'recipient_name' => $validated['recipient_name'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui', 'data' => $transaction]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(FinancialTransaction $transaction)
    {
        if ($transaction->approved_at) {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah disetujui tidak dapat dihapus'], 403);
        }
        $transaction->delete();
        return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
    }

    public function approve(FinancialTransaction $transaction)
    {
        if ($transaction->approved_at) {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah disetujui'], 400);
        }
        $transaction->update([
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);
        return response()->json(['success' => true, 'message' => 'Transaksi berhasil disetujui']);
    }

    public function getCategoriesByGroup(Request $request)
    {
        $type = $request->get('type');
        $categories = $this->getCategories();
        return response()->json(['success' => true, 'data' => $categories[$type] ?? []]);
    }
}
