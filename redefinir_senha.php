<?php
require_once 'conexao.php';
$token = $_GET['token'] ?? '';

// Verifica se o token é válido e não expirou
$stmt = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE recuperar_token = ? AND token_expira > NOW()");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($res);

if (!$usuario) { die("Link inválido ou expirado."); }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card p-4 shadow">
                    <h4 class="text-center">Criar Nova Senha</h4>
                    <form action="salvar_nova_senha.php" method="POST">
                        <input type="hidden" name="token" value="<?php echo $token; ?>">
                        <div class="mb-3">
                            <label>Nova Senha</label>
                            <input type="password" name="nova_senha" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Salvar Nova Senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>