<?php
// Permite requisições do React
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'conexao.php'; // Altere se o nome do seu arquivo de conexão for diferente

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido."]);
    exit();
}

// Recebe os dados JSON do React
$dados = json_decode(file_get_contents("php://input"), true);

$email = $dados['email'] ?? '';
$senha = $dados['senha'] ?? '';

if (empty($email) || empty($senha)) {
    http_response_code(400);
    echo json_encode(["erro" => "Preencha e-mail e senha."]);
    exit();
}

// Consulta o usuário ativo
$sql = "SELECT id, nome, email, senha, papel_id, empresa_id, consultoria_id, ativo FROM usuarios WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($usuario = mysqli_fetch_assoc($resultado)) {
    
    // Verifica se a conta está ativa
    if ((int)$usuario['ativo'] !== 1) {
        http_response_code(403);
        echo json_encode(["erro" => "Usuário inativo no sistema."]);
        exit();
    }

    // Valida a hash da senha
    if (password_verify($senha, $usuario['senha'])) {
        
        // Simula sessão temporária para preencher a função registrarLog()
        session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        registrarLog($conexao, "Login realizado via API", "usuarios", $usuario['id']);

        // Remove a senha antes de enviar a resposta
        unset($usuario['senha']);

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Login efetuado com sucesso!",
            "usuario" => $usuario
        ]);
        exit();
    }
}

// Falha de autenticação
http_response_code(401);
echo json_encode(["erro" => "E-mail ou senha incorretos."]);