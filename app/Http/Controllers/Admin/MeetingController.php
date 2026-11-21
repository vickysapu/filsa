<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::with('creator')->latest();

        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('status')) {
            if ($request->status === 'upcoming') {
                $query->where('tanggal_mulai', '>', now());
            } elseif ($request->status === 'ongoing') {
                $query->where('tanggal_mulai', '<=', now())->where(function ($q) {
                    $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', now());
                });
            } elseif ($request->status === 'completed') {
                $query->where('tanggal_selesai', '<', now());
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_meeting', 'like', "%{$search}%")->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $meetings = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.meetings._table', compact('meetings'))->render();
        }

        return view('admin.meetings.index', compact('meetings'));
    }

    public function create()
    {
        return view('admin.meetings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_meeting' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'platform' => 'required|in:jitsi,zoom,google_meet',
            'meeting_id' => 'nullable|string|max:100',
            'meeting_url' => 'nullable|url|max:500',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable',
            'is_active' => 'boolean',
            'show_in_website' => 'boolean',
        ]);

        if ($validated['platform'] === 'jitsi') {
            $validated['meeting_id'] = $validated['meeting_id'] ?? 'filadelfia-' . Str::random(12);
            $validated['meeting_url'] = $validated['meeting_url'] ?? 'https://meet.jit.si/' . $validated['meeting_id'];
        }

        $validated['created_by'] = Auth::id();
        $validated['branch_id'] = Auth::user()?->roles->first()?->branch_id;
        $validated['is_active'] = $request->has('is_active');
        $validated['show_in_website'] = $request->has('show_in_website');

        Meeting::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil dibuat!',
            ]);
        }

        return redirect()->route('admin.meetings.index')->with('success', 'Meeting berhasil dibuat!');
    }

    public function edit(Request $request, Meeting $meeting)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $meeting->branch_id !== $branchId) {
            abort(403);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $meeting,
            ]);
        }
        return view('admin.meetings.edit', compact('meeting'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $meeting->branch_id !== $branchId) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_meeting' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'platform' => 'required|in:jitsi,zoom,google_meet',
            'meeting_id' => 'nullable|string|max:100',
            'meeting_url' => 'nullable|url|max:500',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date',
            'is_active' => 'boolean',
            'show_in_website' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['show_in_website'] = $request->has('show_in_website');

        $meeting->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil diupdate!',
            ]);
        }

        return redirect()->route('admin.meetings.index')->with('success', 'Meeting berhasil diupdate!');
    }

    public function destroy(Meeting $meeting)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $meeting->branch_id !== $branchId) {
            abort(403);
        }

        $meeting->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Meeting berhasil dihapus!',
            ]);
        }

        return redirect()->route('admin.meetings.index')->with('success', 'Meeting berhasil dihapus!');
    }

    public function show(Meeting $meeting, Request $request)
    {
        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId && $meeting->branch_id !== $branchId) {
            abort(403);
        }

        $meeting->load('creator');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'meeting' => $meeting,
            ]);
        }

        return view('admin.meetings.show', compact('meeting'));
    }

    public function apiUpcoming()
    {
        $query = Meeting::active()->where('tanggal_mulai', '>', now());

        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $meetings = $query->orderBy('tanggal_mulai')->limit(10)->get();

        return response()->json($meetings);
    }

    public function apiOngoing()
    {
        $query = Meeting::active()
            ->where('tanggal_mulai', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', now());
            });

        $branchId = Auth::user()?->roles->first()?->branch_id;
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $meetings = $query->orderBy('tanggal_mulai')->get();

        return response()->json($meetings);
    }
}
