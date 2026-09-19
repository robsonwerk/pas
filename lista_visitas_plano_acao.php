<?php
// Inclui nossos arquivos de base
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

// --- LÓGICA PARA BUSCAR VISITAS QUE PRECISAM DE UM PLANO DE AÇÃO ---
// A query busca visitas que tenham pelo menos uma resposta "Não Conforme".
$sql = "SELECT DISTINCT
            v.id AS visita_id,
            v.data_da_visita,
            e.nome AS nome_empresa
        FROM visitas AS v
        JOIN checklist_respostas AS cr ON v.id = cr.visita_id
        JOIN empresas AS e ON v.empresa_id = e.id
        WHERE cr.conformidade = 'Não Conforme'
        ORDER BY v.data_da_visita DESC";

$resultado = mysqli_query($conexao, $sql);

?>

<h2 class="mb-4">Criar Plano de Ação</h2>

<div class="card">
    <div class="card-header">
        Selecione uma visita para criar ou editar o Plano de Ação
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Data da Visita</th>
                        <th class="text-center">Ação</th>
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
                                <a href="plano_de_acao.php?visita_id=<?php echo $visita['visita_id']; ?>" 
                                   class="btn btn-success">
                                   Criar / Editar Plano
                                </a>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="3" class="text-center">Nenhuma visita com "Não Conformidades" encontrada.</td>
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