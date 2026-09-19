<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';

// 1. CAPTURA O ID DA VISITA (Garante que o nome seja o mesmo em todo o script)
// Verificamos 'visita_id' ou 'id' para evitar erros de link
$visita_id = 0;
if (isset($_GET['visita_id'])) {
    $visita_id = (int)$_GET['visita_id'];
} elseif (isset($_GET['id'])) {
    $visita_id = (int)$_GET['id'];
}

if ($visita_id <= 0) {
    die("Erro: ID da visita não fornecido ou inválido.");
}

// 2. BUSCA AS INFORMAÇÕES GERAIS DA VISITA E DA EMPRESA
$sql_visita = "SELECT v.*, 
                      e.nome AS nome_empresa, 
                      e.razao_social, 
                      e.nome_fantasia, 
                      e.cnpj, 
                      e.endereco, 
                      e.contato_nome,
                      e.telefone
               FROM visitas v 
               JOIN empresas e ON v.empresa_id = e.id 
               WHERE v.id = $visita_id";

// CORREÇÃO: Usando a variável correta $sql_visita
$resultado_visita_info = mysqli_query($conexao, $sql_visita);

if (!$resultado_visita_info) {
    die("Erro na consulta: " . mysqli_error($conexao));
}

$visita_info = mysqli_fetch_assoc($resultado_visita_info);

if (!$visita_info) {
    die("Erro: Visita não encontrada no banco de dados.");
}

// 3. BUSCA OS ITENS DO PLANO DE AÇÃO
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
if ($resultado_plano_itens) {
    while($item = mysqli_fetch_assoc($resultado_plano_itens)) {
        $itens_por_etapa[$item['nome_etapa']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
   <style>
    /* Configuração padrão A4 */
    @page {
        size: A4;
        margin: 1cm;
    }

    /* Estilos para a tela (Layout Fluido) */
    .container-relatorio {
        width: 100%;
        margin: auto;
        transition: all 0.3s ease;
    }

    /* Ajustes específicos para o modo PAISAGEM via botão */
    .modo-paisagem {
        max-width: 100% !important;
    }
    
    .modo-paisagem table {
        font-size: 11px; /* Letra menor para caber mais colunas */
    }

    /* Ajustes específicos para o modo RETRATO via botão */
    .modo-retrato {
        max-width: 800px; /* Limita a largura para simular folha em pé */
    }

    @media print {
        .no-print { display: none !important; }
        .container-relatorio { width: 100% !important; max-width: 100% !important; margin: 0; }
        
        /* Garante que cores de fundo saiam na impressão */
        .card-header {
            background-color: #212529 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
        }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }

    .logo-img { max-height: 70px; }
</style>
    <style>
@media print {
    .card-header {
        background-color: #212529 !important;
        color: white !important;
        -webkit-print-color-adjust: exact;
    }
    .badge {
        border: 1px solid #0d6efd !important;
        color: #0d6efd !important;
    }
}
</style>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print bg-light p-3 rounded border">
    <div>
        <h3 class="mb-0">Relatório de Plano de Ação</h3>
        <small class="text-muted">Escolha o layout antes de imprimir</small>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-secondary" onclick="mudarLayout('retrato')">
            <i class="bi bi-file-earmark-post"></i> Retrato (Em pé)
        </button>
        <button class="btn btn-outline-secondary" onclick="mudarLayout('paisagem')">
            <i class="bi bi-file-earmark-ruled"></i> Paisagem (Deitado)
        </button>
        <button class="btn btn-primary ms-2" onclick="window.print();">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

        <div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Identificação da Unidade e Visita</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <p><strong>Razão Social:</strong> <?php echo htmlspecialchars($visita_info['razao_social'] ?? 'Não informado'); ?></p>
                <p><strong>Nome Fantasia:</strong> <?php echo htmlspecialchars($visita_info['nome_fantasia'] ?? $visita_info['nome_empresa']); ?></p>
                <p><strong>CNPJ:</strong> <?php echo htmlspecialchars($visita_info['cnpj'] ?? '---'); ?></p>
                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($visita_info['endereco'] ?? 'Não cadastrado'); ?></p>
            </div>
            
            <div class="col-md-5 border-start">
                <p><strong>Responsável no Local:</strong> <?php echo htmlspecialchars($visita_info['contato_nome'] ?? 'Não informado'); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($visita_info['telefone'] ?? '---'); ?></p>
                <hr>
                <p class="mb-0"><strong>Data da Auditoria:</strong> 
                    <span class="badge bg-primary fs-6">
                        <?php echo date('d/m/Y', strtotime($visita_info['data_da_visita'])); ?>
                    </span>
                </p>
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

<div class="row mt-5 pt-5 mb-5 text-center">
    <div class="col-md-6">
        <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
        <p><strong>Assinatura do Auditor</strong></p>
    </div>
    <div class="col-md-6">
        <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>
        <p><strong>Responsável pela Unidade: <?php echo htmlspecialchars($visita_info['contato_nome'] ?? ''); ?></strong></p>
    </div>
</div>

    </div>
    <div id="relatorio-container" class="container-relatorio modo-paisagem">
    </div>
    
    <footer class="d-none d-print-block">
    Relatório Gerado pelo Sistema de Gestão de Segurança de Alimentos - PAS/SENAI
</footer>

<script>
function mudarLayout(tipo) {
    const container = document.getElementById('relatorio-container');
    
    if (tipo === 'paisagem') {
        container.classList.remove('modo-retrato');
        container.classList.add('modo-paisagem');
        alert('Layout ajustado para PAISAGEM. Na tela de impressão, lembre-se de selecionar "Paisagem" em "Orientação".');
    } else {
        container.classList.remove('modo-paisagem');
        container.classList.add('modo-retrato');
        alert('Layout ajustado para RETRATO. Na tela de impressão, lembre-se de selecionar "Retrato" em "Orientação".');
    }
}
</script>
</body>

</html>