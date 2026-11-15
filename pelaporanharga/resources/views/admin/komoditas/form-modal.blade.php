<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formTitle">{{ old('_modal_title', 'Tambah Komoditas') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="komoditasForm" method="POST" action="{{ old('_form_action', route('admin.komoditas.store')) }}">
                @csrf
                <input type="hidden" name="_method" value="{{ old('_form_method', 'POST') }}">
                <input type="hidden" name="_form_action" value="{{ old('_form_action', route('admin.komoditas.store')) }}">
                <input type="hidden" name="_form_method" value="{{ old('_form_method', 'POST') }}">
                <input type="hidden" name="_modal_title" value="{{ old('_modal_title', 'Tambah Komoditas') }}">
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Kategori *</label>
                        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" placeholder="padi, jagung, kedelai, cabai, dll" value="{{ old('kategori', 'padi') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Komoditas *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-control @error('grade') is-invalid @enderror" placeholder="Premium, Medium, dll" value="{{ old('grade') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="komoditasIsActive" value="1" {{ old('_form_action') ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                        <label class="form-check-label" for="komoditasIsActive">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
