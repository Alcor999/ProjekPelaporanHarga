<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formTitle">{{ old('_modal_title', 'Tambah Wilayah') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="wilayahForm" method="POST" action="{{ old('_form_action', route('admin.wilayah.store')) }}">
                @csrf
                <input type="hidden" name="_method" value="{{ old('_form_method', 'POST') }}">
                <input type="hidden" name="_form_action" value="{{ old('_form_action', route('admin.wilayah.store')) }}">
                <input type="hidden" name="_form_method" value="{{ old('_form_method', 'POST') }}">
                <input type="hidden" name="_modal_title" value="{{ old('_modal_title', 'Tambah Wilayah') }}">
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
                        <label class="form-label">Provinsi *</label>
                        <input type="text" name="provinsi" class="form-control @error('provinsi') is-invalid @enderror" value="{{ old('provinsi') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kabupaten *</label>
                        <input type="text" name="kabupaten" class="form-control @error('kabupaten') is-invalid @enderror" value="{{ old('kabupaten') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kecamatan *</label>
                        <input type="text" name="kecamatan" class="form-control @error('kecamatan') is-invalid @enderror" value="{{ old('kecamatan') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Desa/Kelurahan</label>
                        <input type="text" name="desa" class="form-control @error('desa') is-invalid @enderror" value="{{ old('desa') }}">
                    </div>
                    <div class="mb-3 form-check" id="wilayahActiveBox">
                        <input type="checkbox" name="is_active" class="form-check-input" id="wilayahIsActive" value="1" {{ old('_form_action') ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                        <label class="form-check-label" for="wilayahIsActive">Aktif</label>
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
