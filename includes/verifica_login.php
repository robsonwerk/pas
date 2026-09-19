<?php
// Inicia a sessão se ela não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se a variável de sessão 'usuario_id' NÃO existe
if (!isset($_SESSION['usuario_id'])) {
    // Se não existir, redireciona para a página de login e encerra o script
    header("Location: login.php");
    exit;
}
?>