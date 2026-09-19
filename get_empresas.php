<?php
// Permite requisições do seu frontend React (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Desativa exibição de erros HTML para não quebrar a resposta JSON do React
ini_set('display_errors', 0);
error_reporting(E_ALL);

// --- Informações de Conexão com o Banco ---
$servidor = "localhost";
$usuario  = "root"; 
$senha    = ""; 
$banco    = "robsonwe_apcc";

try {
    $conn = new mysqli($servidor, $usuario, $senha, $banco);
    $conn->set_charset("utf8mb4");

    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        $sql = "SELECT e.id, e.nome, e.razao_social, e.nome_fantasia, e.cnpj, e.email, e.telefone, 
                       e.localidade, e.uf, e.endereco, e.contato_nome, e.setor_id, s.nome AS setor_nome 
                FROM empresas e 
                LEFT JOIN setores s ON e.setor_id = s.id 
                ORDER BY e.id DESC";
        
        $result = $conn->query($sql);
        $empresas = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $empresas[] = $row;
            }
        }

        echo json_encode(["status" => "success", "empresas" => $empresas]);
    }

    $conn->close();
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        "status" => "error",
        "mensagem" => "Erro na conexão com o banco: " . $e->getMessage()
    ]);
}
?>