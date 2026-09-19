<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';

// Verifica se o ID da visita foi passado pela URL
if (!isset($_GET['visita_id']) || empty($_GET['visita_id'])) {
    die("Erro: ID da visita não fornecido.");
}
$visita_id = (int)$_GET['visita_id'];

// --- BUSCA AS INFORMAÇÕES GERAIS DA VISITA ---
$sql_visita_info = "SELECT 
                        v.data_da_visita,
                        e.nome AS nome_empresa,
                        s.nome AS nome_setor
                    FROM visitas v
                    JOIN empresas e ON v.empresa_id = e.id
                    LEFT JOIN setores s ON e.setor_id = s.id
                    WHERE v.id = $visita_id";
$resultado_visita_info = mysqli_query($conexao, $sql_visita_info);
$visita_info = mysqli_fetch_assoc($resultado_visita_info);

if (!$visita_info) {
    die("Erro: Visita não encontrada.");
}

// --- BUSCA TODOS OS ITENS DO CHECKLIST PARA ESTA VISITA, AGRUPADOS POR ETAPA ---
$sql_checklist_itens = "SELECT
                            et.nome AS nome_etapa,
                            r.numero,
                            r.descricao,
                            cr.conformidade,
                            cr.descricao_nao_conformidade
                        FROM checklist_respostas cr
                        JOIN requisitos r ON cr.requisito_id = r.id
                        JOIN etapas et ON r.etapa_id = et.id
                        WHERE cr.visita_id = $visita_id
                        ORDER BY et.nome, r.numero";
$resultado_checklist_itens = mysqli_query($conexao, $sql_checklist_itens);

$itens_por_etapa = [];
while($item = mysqli_fetch_assoc($resultado_checklist_itens)) {
    $itens_por_etapa[$item['nome_etapa']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Checklist - <?php echo htmlspecialchars($visita_info['nome_empresa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="print.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h3>Relatório de Checklist</h3>
            <button class="btn btn-primary" onclick="window.print();">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16"><path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/><path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/></svg>
                Imprimir Relatório
            </button>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Informações da Visita
            </div>
            <div class="card-body">
                <p><strong>Empresa:</strong> <?php echo htmlspecialchars($visita_info['nome_empresa']); ?></p>
                <p><strong>Setor Principal:</strong> <?php echo htmlspecialchars($visita_info['nome_setor'] ?? 'N/D'); ?></p>
                <p><strong>Data da Visita:</strong> <?php echo date('d/m/Y', strtotime($visita_info['data_da_visita'])); ?></p>
            </div>
        </div>

        <h4 class="mb-3">Itens Verificados</h4>
        <?php foreach ($itens_por_etapa as $nome_etapa => $itens): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <strong>Etapa:</strong> <?php echo htmlspecialchars($nome_etapa); ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Nº</th>
                                <th style="width: 40%;">Requisito</th>
                                <th style="width: 15%;">Conformidade</th>
                                <th>Descrição da Não Conformidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['numero']); ?></td>
                                    <td><?php echo htmlspecialchars($item['descricao']); ?></td>
                                    <td>
                                        <?php
                                            $conformidade = htmlspecialchars($item['conformidade']);
                                            $badge_class = 'bg-secondary';
                                            if ($conformidade == 'Conforme') $badge_class = 'bg-success';
                                            if ($conformidade == 'Não Conforme') $badge_class = 'bg-danger';
                                            echo "<span class='badge $badge_class'>$conformidade</span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['descricao_nao_conformidade']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</body>
</html>