<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

$servidor = "localhost";
$usuario  = "root"; 
$senha    = ""; 
$banco    = "robsonwe_apcc";

try {
    $conn = new mysqli($servidor, $usuario, $senha, $banco);
    $conn->set_charset("utf8mb4");

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    // Listar visitas e empresas ativas
    if ($method === 'GET') {
        if ($action === 'empresas') {
            $sql = "SELECT id, nome FROM empresas ORDER BY nome ASC";
            $res = $conn->query($sql);
            $empresas = [];
            while ($row = $res->fetch_assoc()) { $empresas[] = $row; }
            echo json_encode(["status" => "success", "empresas" => $empresas]);
            exit();
        }

        $sql = "SELECT v.id, v.empresa_id, v.data_da_visita, v.setor AS setor_vistoriado, v.observacao, e.nome AS nome_empresa 
                FROM visitas v 
                JOIN empresas e ON v.empresa_id = e.id 
                ORDER BY v.data_da_visita DESC";
        $res = $conn->query($sql);
        $visitas = [];
        while ($row = $res->fetch_assoc()) { $visitas[] = $row; }
        echo json_encode(["status" => "success", "visitas" => $visitas]);
        exit();
    }

    // Criar ou Atualizar Visita
    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php_input"), true) ?? $_POST;
        
        $empresa_id = (int)($data['empresa_id'] ?? 0);
        $data_visita = $conn->real_escape_string($data['data_da_visita'] ?? '');
        $setor = $conn->real_escape_string($data['setor_vistoriado'] ?? '');
        $observacao = $conn->real_escape_string($data['observacao'] ?? '');

        if (isset($data['id_para_atualizar']) && !empty($data['id_para_atualizar'])) {
            $id = (int)$data['id_para_atualizar'];
            $sql = "UPDATE visitas SET empresa_id=$empresa_id, data_da_visita='$data_visita', setor='$setor', observacao='$observacao' WHERE id=$id";
            $conn->query($sql);
            echo json_encode(["status" => "success", "mensagem" => "Visita atualizada com sucesso!"]);
        } else {
            $sql = "INSERT INTO visitas (empresa_id, data_da_visita, setor, observacao) VALUES ($empresa_id, '$data_visita', '$setor', '$observacao')";
            $conn->query($sql);
            echo json_encode(["status" => "success", "mensagem" => "Visita agendada com sucesso!"]);
        }
        exit();
    }

    // Excluir Visita
    if ($method === 'DELETE' || isset($_GET['delete_id'])) {
        $id = (int)($_GET['delete_id'] ?? 0);
        if ($id > 0) {
            $conn->query("DELETE FROM visitas WHERE id = $id");
            echo json_encode(["status" => "success", "mensagem" => "Visita excluída com sucesso!"]);
        } else {
            echo json_encode(["status" => "error", "mensagem" => "ID inválido."]);
        }
        exit();
    }

    $conn->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "mensagem" => "Erro de servidor: " . $e->getMessage()]);
}
?>