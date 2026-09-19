<?php
// Exibir erros em modo de depuração para retornar no JSON em caso de falha
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Configurações do Banco de Dados do Servidor
    $host = "localhost"; // Se o banco estiver no servidor de banco de dados do SENAI, troque pelo IP/Host do banco
    $usuario_db = "root"; // Exemplo: root ou usuario_apcc
    $senha_db = "";     // Insira a senha real do MySQL do servidor
    $banco = "robsonwe_apcc";

    // Forçar exibição de exceções no MySQLi
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($host, $usuario_db, $senha_db, $banco);
    $conn->set_charset("utf8mb4");

    // Lendo o corpo da requisição JSON do React
    $input = file_get_contents("php://input");
    $dados = json_decode($input, true);

    $email = filter_var($dados['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $senha = $dados['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        echo json_encode(["status" => "error", "mensagem" => "Preencha e-mail e senha."]);
        exit();
    }

    // Consulta na tabela de usuários
    $stmt = $conn->prepare("SELECT id, nome, email, senha, papel_id, empresa_id, consultoria_id, ativo FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if ((int)$usuario['ativo'] !== 1) {
            echo json_encode(["status" => "error", "mensagem" => "Usuário inativo no sistema."]);
            exit();
        }

        if (password_verify($senha, $usuario['senha'])) {
            unset($usuario['senha']);
            echo json_encode([
                "status" => "success",
                "mensagem" => "Login realizado com sucesso!",
                "usuario" => $usuario
            ]);
        } else {
            echo json_encode(["status" => "error", "mensagem" => "Senha incorreta."]);
        }
    } else {
        echo json_encode(["status" => "error", "mensagem" => "Usuário não encontrado."]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Retorna a causa exata do erro 500 dentro de uma estrutura JSON válida
    http_response_code(200); 
    echo json_encode([
        "status" => "error",
        "mensagem" => "Erro no servidor: " . $e->getMessage()
    ]);
}
?>