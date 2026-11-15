<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Pelaporan Harga Komoditas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f3f6f4;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                            <h3>Admin Panel</h3>
                            <p class="text-muted">Pelaporan Harga Komoditas</p>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.login.post') }}" id="loginForm">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" name="email" id="email" class="form-control"
                                       value="{{ old('email') }}" required autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Masuk</span>
                                    <span class="btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                        Memproses...
                                    </span>
                                </button>
                            </div>
                        </form>

                        <script>
                            document.getElementById('loginForm').addEventListener('submit', function() {
                                var btn = document.getElementById('loginBtn');
                                btn.disabled = true;
                                btn.querySelector('.btn-text').classList.add('d-none');
                                btn.querySelector('.btn-loading').classList.remove('d-none');
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>