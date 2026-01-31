@extends('layouts.admin')

@section('title', 'Kirim Broadcast')

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.broadcast.index') }}">Broadcast</a></li>
                <li class="breadcrumb-item active">Kirim Baru</li>
            </ol>
        </nav>
        <h1 class="mb-4"><i class="fas fa-bullhorn text-primary"></i> Kirim Broadcast</h1>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.broadcast.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Judul *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Pesan *</label>
                <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="5" required></textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim ke Semua Petani</button>
            <a href="{{ route('admin.broadcast.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
