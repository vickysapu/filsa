@extends('layouts.admin')
@section('title', 'Mapping Kategori Keuangan')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Mapping Kategori Keuangan</h1>
                <p class="text-muted mb-0">
                    <i class="ri-exchange-line me-1"></i>
                    Petakan kategori lama ke kategori yang valid. Detail nama (seperti nama gereja) akan otomatis
                    dipertahankan.
                </p>
            </div>
            <a href="{{ route('admin.finance.reports.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="ri-list-check me-2"></i>Daftar Kategori untuk Dipetakan</h5>
            </div>
            <div class="card-body">
                <form id="mappingForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Kategori Lama (Code)</th>
                                    <th width="30%">Kategori Lama (Name)</th>
                                    <th width="30%">Petakan ke Kategori Baru</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="mappingTableBody">
                                @forelse($oldCategories as $index => $category)
                                    <tr data-index="{{ $index }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{-- Kirim old_base_name agar backend tahu cara memisahkan suffix --}}
                                            <input type="hidden" name="mappings[{{ $index }}][old_code]"
                                                value="{{ $category->category_code }}">
                                            <input type="hidden" name="mappings[{{ $index }}][old_base_name]"
                                                value="{{ $category->base_name }}">
                                            <code>{{ $category->category_code }}</code>
                                        </td>
                                        <td>{{ $category->base_name ?? '-' }}</td>
                                        <td>
                                            <select name="mappings[{{ $index }}][new_code]"
                                                class="form-select form-select-sm" required>
                                                <option value="">-- Pilih Kategori --</option>
                                                @foreach ($validCategories as $code => $cat)
                                                    <option value="{{ $code }}">
                                                        {{ $code }} - {{ $cat['name'] }}
                                                        ({{ ucfirst($cat['group']) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"
                                                data-index="{{ $index }}" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="ri-checkbox-circle-line display-6 d-block mb-2 opacity-50"></i>
                                            <h6 class="fw-semibold">Tidak ada kategori yang perlu dipetakan</h6>
                                            <p class="small">Semua kategori sudah valid</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($oldCategories->count() > 0)
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="btnReset">
                                <i class="ri-refresh-line me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="ri-save-line me-1"></i> Simpan Mapping
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.btn-remove-row', function() {
                if (confirm('Yakin ingin menghapus baris ini?')) {
                    $(this).closest('tr').remove();
                }
            });

            $('#btnReset').click(function() {
                location.reload();
            });

            $('#mappingForm').submit(function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#btnSubmit');
                let originalText = btn.html();

                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('admin.finance.reports.category-mapping.update') }}",
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        if (res.success) {
                            alert('Berhasil! ' + res.message);
                            setTimeout(() => location.href =
                                "{{ route('admin.finance.reports.index') }}", 1500);
                        } else {
                            alert('Gagal: ' + res.message);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.status === 422) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        alert('Error: ' + msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endsection
