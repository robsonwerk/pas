<?php
// --- Informações para conexão com o banco de dados ---
$servidor = "localhost"; // Geralmente é 'localhost'
$usuario = "root";       // Usuário padrão do XAMPP
$senha = "";             // Senha padrão do XAMPP é vazia
$banco = "robsonwe_apcc"; // O nome do banco de dados que criamos

// --- Criando a conexão ---
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// --- Verificando a conexão ---
if (!$conexao) {
    // Se a conexão falhar, exibe uma mensagem de erro e interrompe o script
    die("Falha na conexão: " . mysqli_connect_error());
}

// Define o charset para utf8, para evitar problemas com acentos
mysqli_set_charset($conexao, "utf8");

function registrarLog($conexao, $acao, $tabela = null, $id_registro = null) {
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Sistema/Deslogado';
    $ip = $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO logs (usuario_id, usuario_nome, acao, tabela_afetada, registro_id, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "isssis", $usuario_id, $usuario_nome, $acao, $tabela, $id_registro, $ip);
    mysqli_stmt_execute($stmt);
}
?>