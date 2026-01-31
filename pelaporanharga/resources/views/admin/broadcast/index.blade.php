@extends('layouts.admin')

@section('title', 'Broadcast Pesan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-bullhorn text-primary"></i> Broadcast Pesan</h1>
            <a href="{{ route('admin.broadcast.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Kirim Broadcast
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <p class="text-muted">Pesan broadcast akan dikirim ke semua petani yang akunnya aktif.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>Pesan</th>
                        <th>Dikirim oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($broadcasts as $b)
                    <tr>
                        <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>
                        <td><strong>{{ $b->title }}</strong></td>
                        <td>{{ Str::limit($b->message, 80) }}</td>
                        <td>{{ $b->creator->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada broadcast</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $broadcasts->links() }}
    </div>
</div>
@endsection
