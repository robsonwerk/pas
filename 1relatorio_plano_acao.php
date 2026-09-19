<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';

// Verifica se o ID da visita foi passado pela URL
if (!isset($_GET['visita_id']) || empty($_GET['visita_id'])) {
    die("Erro: ID da visita não fornecido.");
}
$visita_id = (int)$_GET['visita_id'];

// --- 1. BUSCA AS INFORMAÇÕES GERAIS DA VISITA (ADICIONADO e.cnpj) ---
$sql_visita_info = "SELECT 
                        v.data_da_visita,
                        e.nome AS nome_empresa,
                        e.cnpj 
                    FROM visitas v
                    JOIN empresas e ON v.empresa_id = e.id
                    WHERE v.id = $visita_id";
$resultado_visita_info = mysqli_query($conexao, $sql_visita_info);
$visita_info = mysqli_fetch_assoc($resultado_visita_info);

if (!$visita_info) {
    die("Erro: Visita não encontrada.");
}

// --- BUSCA OS ITENS DO PLANO DE AÇÃO PARA ESTA VISITA ---
$sql_plano_itens = "SELECT
                        pa.acao_corretiva,
                        pa.responsavel,
                        pa.prazo,
                        pa.custo,
                        r.numero AS requisito_numero,
                        r.descricao AS requisito_descricao,
                        cr.descricao_nao_conformidade,
                        et.nome AS nome_etapa
                    FROM planos_de_acao pa
                    JOIN checklist_respostas cr ON pa.checklist_resposta_id = cr.id
                    JOIN requisitos r ON cr.requisito_id = r.id
                    JOIN etapas et ON r.etapa_id = et.id
                    WHERE cr.visita_id = $visita_id
                    ORDER BY et.nome, r.numero";
$resultado_plano_itens = mysqli_query($conexao, $sql_plano_itens);

$itens_por_etapa = [];
while($item = mysqli_fetch_assoc($resultado_plano_itens)) {
    $itens_por_etapa[$item['nome_etapa']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Plano de Ação - <?php echo htmlspecialchars($visita_info['nome_empresa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h3>Relatório de Plano de Ação</h3>
            <button class="btn btn-primary" onclick="window.print();">Imprimir Relatório</button>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Informações da Visita</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Empresa:</strong> <?php echo htmlspecialchars($visita_info['nome_empresa']); ?></p>
                        <p><strong>CNPJ:</strong> <?php echo htmlspecialchars($visita_info['cnpj']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Data da Visita:</strong> <?php echo date('d/m/Y', strtotime($visita_info['data_da_visita'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="mb-3">Ações Corretivas Planejadas</h4>
        <?php if (empty($itens_por_etapa)): ?>
            <div class="alert alert-info">Nenhum item de plano de ação encontrado para esta visita.</div>
        <?php else: ?>
            <?php foreach ($itens_por_etapa as $nome_etapa => $itens): ?>
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <strong>Etapa:</strong> <?php echo htmlspecialchars($nome_etapa); ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Não Conformidade</th>
                                    <th>Ação Corretiva</th>
                                    <th>Responsável</th>
                                    <th>Prazo</th>
                                    <th>Custo (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td style="width: 35%;">
                                            <strong>Requisito <?php echo htmlspecialchars($item['requisito_numero']); ?>:</strong>
                                            <p class="text-muted small mb-1"><?php echo htmlspecialchars($item['requisito_descricao']); ?></p>
                                            <hr class="my-1">
                                            <strong>Evidência:</strong>
                                            <p class="text-danger mb-0 small"><?php echo htmlspecialchars($item['descricao_nao_conformidade']); ?></p>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['acao_corretiva']); ?></td>
                                        <td><?php echo htmlspecialchars($item['responsavel']); ?></td>
                                        <td><?php echo $item['prazo'] ? date('d/m/Y', strtotime($item['prazo'])) : '-'; ?></td>
                                        <td><?php echo $item['custo'] ? number_format($item['custo'], 2, ',', '.') : '0,00'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</body>
</html>