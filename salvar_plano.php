<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $visita_id = (int)$_POST['visita_id']; // Pega o visita_id para o redirect
    $plano = $_POST['plano'] ?? [];
    $erros = 0;

    foreach ($plano as $resposta_id => $dados) {
        $resposta_id_sanitizado = (int)$resposta_id;
        $acao_corretiva = mysqli_real_escape_string($conexao, $dados['acao_corretiva']);
        $responsavel = mysqli_real_escape_string($conexao, $dados['responsavel']);
        $status = mysqli_real_escape_string($conexao, $dados['status']);
        
        $prazo = !empty($dados['prazo']) ? "'" . mysqli_real_escape_string($conexao, $dados['prazo']) . "'" : "NULL";
        $custo = !empty($dados['custo']) ? (float)$dados['custo'] : "NULL";
        
        // Lógica inteligente para a data de conclusão
        $data_conclusao = "NULL";
        if ($status == 'Concluído') {
            if (!empty($dados['data_conclusao'])) {
                $data_conclusao = "'" . mysqli_real_escape_string($conexao, $dados['data_conclusao']) . "'";
            } else {
                // Se marcou como concluído mas não informou a data, usa a data de hoje
                $data_conclusao = "'" . date('Y-m-d') . "'";
            }
        }

        $sql = "INSERT INTO planos_de_acao (checklist_resposta_id, acao_corretiva, responsavel, prazo, custo, status, data_conclusao) 
                VALUES ($resposta_id_sanitizado, '$acao_corretiva', '$responsavel', $prazo, $custo, '$status', $data_conclusao)
                ON DUPLICATE KEY UPDATE 
                    acao_corretiva = VALUES(acao_corretiva),
                    responsavel = VALUES(responsavel),
                    prazo = VALUES(prazo),
                    custo = VALUES(custo),
                    status = VALUES(status),
                    data_conclusao = VALUES(data_conclusao)";

        if (!mysqli_query($conexao, $sql)) {
            $erros++;
        }
    }

    if ($erros == 0) {
        $_SESSION['mensagem_sucesso'] = "Plano de Ação salvo com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Ocorreu um erro ao salvar o Plano de Ação.";
    }
    
    // Redireciona de volta para a mesma tela de edição
    header("Location: plano_de_acao.php?visita_id=" . $visita_id);
    exit;

} else {
    header("Location: plano_de_acao.php");
    exit;
}
?>