<?php
session_start();
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 // No processa_login.php, mude para:
// Pega o valor do campo 'usuario' (que é o que está no seu HTML)
$login_input = trim($_POST['usuario']); 
$senha_digitada = $_POST['senha'] ?? '';

// CHAMADA DO LOG
registrarLog($conexao, "Realizou login com sucesso");

// Na query, usamos $login_input
$sql = "SELECT u.id, u.nome, u.senha, u.empresa_id, p.nome AS nome_papel 
        FROM usuarios u
        JOIN papeis p ON u.papel_id = p.id
        WHERE u.email = ? AND u.ativo = 1";
            
$stmt = mysqli_prepare($conexao, $sql);

// O ERRO ESTAVA AQUI: Você usou $email, mas o correto é $login_input
mysqli_stmt_bind_param($stmt, "s", $login_input); 

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

    // 3. Verifica se encontrou o usuário
    if ($resultado && $usuario = mysqli_fetch_assoc($resultado)) {
        
        // 4. VERIFICAÇÃO DE SENHA REAL
        // Compara a senha digitada com o hash guardado no banco
        if (password_verify($senha_digitada, $usuario['senha'])) {
            
            // Login bem-sucedido! Salva os dados na sessão.
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_papel'] = $usuario['nome_papel'];
            $_SESSION['usuario_empresa_id'] = $usuario['empresa_id'];
            
            header("Location: index.php");
            exit;
        } else {
            // Senha incorreta
            $_SESSION['login_error'] = "Senha incorreta.";
        }
    } else {
        // Email não encontrado ou usuário inativo
        $_SESSION['login_error'] = "Usuário não encontrado ou inativo.";
    }

    // Se houve erro (chegou aqui), redireciona de volta
    header("Location: login.php");
    exit;
}
?>