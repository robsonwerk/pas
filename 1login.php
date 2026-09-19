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
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f7f6;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="card login-card shadow">
        <div class="card-body">
            <center><img class="login-logo" src="img/logo.png" alt="Logotipo Alimento Seguro" style="width:350px; height:200px;"></center>
            <h3 class="card-title text-center mb-4">Alimento Seguro</h3>
            
            <?php
            // Exibe mensagem de erro se houver
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['login_error'] . '</div>';
                unset($_SESSION['login_error']); // Limpa a mensagem após exibir
            }
            ?>

            <form action="processa_login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
               
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>