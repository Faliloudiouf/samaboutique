<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion · SamaBoutique</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">S</div>
    <h1 class="h3 text-center mb-1">SamaBoutique</h1>
    <p class="text-center muted mb-4" style="font-size:14px">Connectez-vous à votre espace de gestion</p>

    @if($errors->any())
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="vous@samaboutique.sn">
      </div>
      <div class="mb-3">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="password" class="form-control" required placeholder="••••••••">
      </div>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <label style="font-size:13px"><input type="checkbox" name="remember"> Se souvenir de moi</label>
      </div>
      <button class="btn btn-primary btn-lg w-100 justify-content-center"><i class="bi bi-box-arrow-in-right"></i> Se connecter</button>
    </form>
  </div>
</div>
</body>
</html>
