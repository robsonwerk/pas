<?php
// Inclui os arquivos de base
require_once 'conexao.php';
require_once 'includes/verifica_login.php'; // Isso já inicia a sessão para nós

// Configura o cabeçalho para responder sempre em formato JSON (padrão para requisições Fetch/AJAX)
header('Content-Type: application/json');

// Verifica se a requisição é do tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Pega os IDs principais do formulário
    $visita_id = (int)$_POST['visita_id'];
    $respostas = $_POST['respostas'] ?? [];
    $erros = 0;

    // Loop através de cada resposta enviada
    foreach ($respostas as $requisito_id => $dados) {
        $requisito_id_sanitizado = (int)$requisito_id;
        $criticidade = mysqli_real_escape_string($conexao, $dados['criticidade']);
        $conformidade = mysqli_real_escape_string($conexao, $dados['conformidade']);
        $descricao = mysqli_real_escape_string($conexao, $dados['descricao']);

        // Query para inserir ou atualizar os dados
        $sql = "INSERT INTO checklist_respostas (visita_id, requisito_id, criticidade, conformidade, descricao_nao_conformidade) 
                VALUES ($visita_id, $requisito_id_sanitizado, '$criticidade', '$conformidade', '$descricao')
                ON DUPLICATE KEY UPDATE 
                    criticidade = VALUES(criticidade),
                    conformidade = VALUES(conformidade),
                    descricao_nao_conformidade = VALUES(descricao_nao_conformidade)";

        if (!mysqli_query($conexao, $sql)) {
            $erros++;
        }
    }

    // --- VERIFICAÇÃO DE SUCESSO OU ERRO ---
    if ($erros == 0) {
        // Define código HTTP 200 (OK) e responde sucesso em formato JSON
        http_response_code(200);
        echo json_encode([
            "status" => "sucesso", 
            "mensagem" => "Checklist salvo com sucesso!"
        ]);
        exit;
    } else {
        // Define código HTTP 500 (Erro Interno) e avisa que houve falhas no banco
        http_response_code(500);
        echo json_encode([
            "status" => "erro", 
            "mensagem" => "Ocorreu um erro ao salvar alguns itens do checklist no banco de dados."
        ]);
        exit;
    }

} else {
    // Se tentarem acessar o arquivo diretamente via URL (GET), bloqueia e retorna erro
    http_response_code(405); // Método Não Permitido
    echo json_encode([
        "status" => "erro", 
        "mensagem" => "Método de requisição inválido."
    ]);
    exit;
}
?>