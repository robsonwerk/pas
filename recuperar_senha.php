<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - Alimento Seguro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Recuperar Senha</h3>
                        <p class="text-muted text-center">Informe seu e-mail para receber um link de redefinição.</p>
                        
                        <form action="processa_recuperacao.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">E-mail cadastrado</label>
                                <input type="email" name="email" class="form-control" required placeholder="exemplo@email.com">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enviar Link</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="login.php" class="text-decoration-none">Voltar ao Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>