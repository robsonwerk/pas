<?php
// Inclui nossos arquivos de base
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

// --- LÓGICA PARA BUSCAR OS CHECKLISTS REALIZADOS E CONTAR AS NÃO CONFORMIDADES ---
$sql = "SELECT 
            v.id AS visita_id,
            v.data_da_visita,
            e.nome AS nome_empresa,
            -- Esta subconsulta conta quantas respostas 'Não Conforme' existem para cada visita
            (SELECT COUNT(*) FROM checklist_respostas cr WHERE cr.visita_id = v.id AND cr.conformidade = 'Não Conforme') AS total_nao_conformes
        FROM visitas AS v
        JOIN empresas AS e ON v.empresa_id = e.id
        -- Esta cláusula garante que só apareçam na lista as visitas que já têm checklist preenchido
        WHERE v.id IN (SELECT DISTINCT visita_id FROM checklist_respostas)
        ORDER BY v.data_da_visita DESC";

$resultado = mysqli_query($conexao, $sql);

?>

<h2 class="mb-4">Checklists Realizados</h2>

<div class="card">
    <div class="card-header">
        Lista de checklists preenchidos
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Data da Visita</th>
                        <th class="text-center">Resultado</th>
                        <th class="text-center">Relatórios</th>
                    </tr>
                </thead>
                <tbody>
                     <?php 
    if ($resultado && mysqli_num_rows($resultado) > 0):
        while($visita = mysqli_fetch_assoc($resultado)): 
    ?>
        <tr>
            <td><?php echo htmlspecialchars($visita['nome_empresa']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($visita['data_da_visita'])); ?></td>
            <td class="text-center">
                <?php
                    $total_nc = $visita['total_nao_conformes'];
                    if ($total_nc > 0) {
                        echo "<span class='badge bg-danger'>{$total_nc} Não Conformidade(s)</span>";
                    } else {
                        echo "<span class='badge bg-success'>100% Conforme</span>";
                    }
                ?>
            </td>
            <td class="text-center">
                <a href="relatorio_checklist.php?visita_id=<?php echo $visita['visita_id']; ?>" 
                   class="btn btn-primary btn-sm" 
                   target="_blank" title="Ver Relatório do Checklist">
                   Ver Checklist
                </a>
                
                
                <?php if ($total_nc > 0): // Só mostra o botão se houver não conformidades ?>
                <a href="plano_de_acao.php?visita_id=<?php echo $visita['visita_id']; ?>" 
                   class="btn btn-success btn-sm" 
                   title="Criar ou Editar o Plano de Ação">
                   Plano de Ação
                </a>
                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum checklist preenchido encontrado.</td>
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