<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formTitle">{{ old('_modal_title', 'Tambah Supplier') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="supplierForm" method="POST" action="{{ old('_form_action', route('admin.supplier.store')) }}">
                @csrf
                <input type="hidden" name="_method" value="{{ old('_form_method', 'POST') }}">
                <input type="hidden" name="_form_action" value="{{ old('_form_action', route('admin.supplier.store')) }}">
                <input type="hidden" name="_form_method" value="{{ old('_form_method', 'POST') }}">
                <input type="hidden" name="_modal_title" value="{{ old('_modal_title', 'Tambah Supplier') }}">
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
                        <label class="form-label">Nama Supplier *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required placeholder="Nama supplier" value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="08xxxx" value="{{ old('phone') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wilayah</label>
                        <select name="wilayah_id" class="form-select @error('wilayah_id') is-invalid @enderror">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($wilayah ?? [] as $w)
                            <option value="{{ $w->id }}" {{ old('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->kabupaten }} - {{ $w->kecamatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Info tambahan">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="supplierIsActive" value="1" {{ old('_form_action') ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                        <label class="form-check-label" for="supplierIsActive">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
