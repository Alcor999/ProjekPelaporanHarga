<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formTitle">Tambah Wilayah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="wilayahForm" method="POST" action="{{ route('admin.wilayah.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Provinsi *</label>
                        <input type="text" name="provinsi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kabupaten *</label>
                        <input type="text" name="kabupaten" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kecamatan *</label>
                        <input type="text" name="kecamatan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Desa/Kelurahan</label>
                        <input type="text" name="desa" class="form-control">
                    </div>
                    <div class="mb-3 form-check" id="wilayahActiveBox">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label">Aktif</label>
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
