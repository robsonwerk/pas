<?php
// É essencial iniciar a sessão em todos os arquivos que a manipulam
session_start();
require_once 'conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    // A linha da senha foi removida

    // Busca o usuário no banco de dados pelo email e verifica se está ativo
    $sql = "SELECT u.id, u.nome, u.empresa_id, p.nome AS nome_papel 
            FROM usuarios u
            JOIN papeis p ON u.papel_id = p.id
            WHERE u.email = '$email' AND u.ativo = 1";
            
    $resultado = mysqli_query($conexao, $sql);

    // Verifica se encontrou um usuário
    if ($resultado && mysqli_num_rows($resultado) == 1) {
        $usuario = mysqli_fetch_assoc($resultado);
        
        // A VERIFICAÇÃO DE SENHA (password_verify) FOI REMOVIDA
        // Se encontrou o usuário, o login é bem-sucedido.
        
        // Salva os dados na sessão.
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_papel'] = $usuario['nome_papel'];
        $_SESSION['usuario_empresa_id'] = $usuario['empresa_id'];
        
        // Redireciona para a página principal do sistema
        header("Location: index.php");
        exit;
    }

    // Se chegou até aqui, o login falhou (email não encontrado ou inativo)
    $_SESSION['login_error'] = "Email não encontrado ou inativo.";
    header("Location: login.php");
    exit;
}
?>