<?php
session_start();
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';

    if (empty($token) || strlen($nova_senha) < 6) {
        die("Dados inválidos. A senha deve ter no mínimo 6 caracteres.");
    }

    // 1. Gera o hash seguro da nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // 2. Atualiza a senha e remove o token de recuperação (segurança)
    // Só faz a alteração se o token for válido e não tiver expirado
    $sql = "UPDATE usuarios 
            SET senha = ?, 
                recuperar_token = NULL, 
                token_expira = NULL 
            WHERE recuperar_token = ? AND token_expira > NOW()";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $senha_hash, $token);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        // Sucesso!
        $_SESSION['login_success'] = "Senha atualizada com sucesso! Faça login agora.";
        header("Location: login.php");
        exit;
    } else {
        // Token expirou enquanto o usuário digitava ou erro no banco
        die("Erro ao atualizar a senha. O link pode ter expirado. Tente novamente o processo de recuperação.");
    }
}
?>