<?php
// Inclui nossos arquivos de base
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

// --- LÓGICA PARA BUSCAR TODOS OS PLANOS DE AÇÃO CRIADOS ---
$sql = "SELECT 
            pa.id AS plano_id,
            pa.responsavel,
            pa.prazo,
            v.id AS visita_id,
            v.data_da_visita,
            e.nome AS nome_empresa
        FROM planos_de_acao AS pa
        JOIN checklist_respostas AS cr ON pa.checklist_resposta_id = cr.id
        JOIN visitas AS v ON cr.visita_id = v.id
        JOIN empresas AS e ON v.empresa_id = e.id
        ORDER BY pa.prazo ASC, v.data_da_visita DESC";

$resultado = mysqli_query($conexao, $sql);

?>

<h2 class="mb-4">Acompanhamento de Planos de Ação</h2>

<div class="card">
    <div class="card-header">
        Lista de Planos de Ação Gerados
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Data da Visita</th>
                        <th>Responsável pela Ação</th>
                        <th>Prazo Final</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($resultado && mysqli_num_rows($resultado) > 0):
                        while($plano = mysqli_fetch_assoc($resultado)): 
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($plano['nome_empresa']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($plano['data_da_visita'])); ?></td>
                            <td><?php echo htmlspecialchars($plano['responsavel']); ?></td>
                            <td>
                                <?php 
                                    if ($plano['prazo']) {
                                        echo date('d/m/Y', strtotime($plano['prazo']));
                                    } else {
                                        echo 'N/D';
                                    }
                                ?>
                            </td>
                            <td class="text-center">
                                <a href="relatorio_plano_acao.php?visita_id=<?php echo $plano['visita_id']; ?>" 
                                   class="btn btn-success" 
                                   target="_blank">
                                   Ver Relatório
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum plano de ação encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Inclui o rodapé e fecha a conexão
require_once 'includes/footer.php';
mysqli_close($conexao);
?>