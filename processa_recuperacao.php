<?php
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // 1. Gera o Token e a Expiração
    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // 2. Verifica se o email existe na sua tabela 'usuarios'
    $stmt = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) > 0) {
        // 3. Salva o token no banco de dados
        // Certifique-se de que as colunas recuperar_token e token_expira existem na tabela
        $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET recuperar_token = ?, token_expira = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "sss", $token, $expira, $email);
        mysqli_stmt_execute($stmt);

        // 4. Exibe o link (Simulação de e-mail)
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2>Solicitação Recebida</h2>";
        echo "<p>Para redefinir sua senha, clique no botão abaixo:</p>";
        echo "<a href='redefinir_senha.php?token=$token' style='padding:10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>Redefinir Senha</a>";
        echo "</div>";
    } else {
        echo "E-mail não encontrado no sistema.";
    }
}
?>