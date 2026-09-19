<?php
session_start(); // Inicia a sessão
session_unset(); // Limpa todas as variáveis da sessão
session_destroy(); // Destrói a sessão
header("Location: login.php"); // Redireciona para a página de login
exit;
?>