<?php
// Inicia a sessão para poder usar as variáveis de sessão
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alimento Seguro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f4f7f6 0%, #e2e9e7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            max-width: 850px;
            width: 100%;
        }
        .login-brand-side {
            background-color: #f8faf9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            border-right: 1px solid #edf2f0;
        }
        .login-form-side {
            padding: 3.5rem 3rem;
        }
        .login-logo {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .login-logo:hover {
            transform: scale(1.02);
        }
        .input-group-text {
            background-color: #f4f7f6;
            border-color: #dee2e6;
            color: #6c757d;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-login {
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
        }
        /* Ajustes responsivos para celulares */
        @media (max-width: 767.98px) {
            .login-brand-side {
                padding: 2rem 1.5rem 1rem 1.5rem;
                border-right: none;
                border-bottom: 1px solid #edf2f0;
            }
            .login-form-side {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center p-3">
    <div class="login-container row g-0">
        
        <div class="col-md-5 login-brand-side text-center">
            <img class="login-logo mb-2" src="img/logo.png" alt="Logotipo Alimento Seguro" style="max-height: 180px;">
            <p class="text-muted small px-2">Garantindo a qualidade e a conformidade da porteira à mesa.</p>
        </div>
        
        <div class="col-md-7 login-form-side d-flex flex-column justify-content-center">
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Seja bem-vindo</h3>
                <p class="text-secondary small">Insira suas credenciais para acessar o painel de inspeção.</p>
            </div>
            
            <?php
            // Exibe mensagem de erro se houver
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>' . htmlspecialchars($_SESSION['login_error']) . '
                      </div>';
                unset($_SESSION['login_error']); // Limpa a mensagem após exibir
            }
            ?>

            <form action="processa_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold small">Usuário / E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="usuario" class="form-control" placeholder="exemplo@email.com" required autocomplete="username">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-secondary fw-semibold small">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="senha" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100 shadow-sm mb-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Entrar no Sistema
                </button>
            </form>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>