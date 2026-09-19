<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

// --- LÓGICA PARA FILTROS (continua a mesma) ---
$sql_visitas = "SELECT v.id, v.data_da_visita, e.nome AS nome_empresa FROM visitas v JOIN empresas e ON v.empresa_id = e.id ORDER BY v.data_da_visita DESC";
$resultado_visitas = mysqli_query($conexao, $sql_visitas);
// ... (outros filtros se houver)

$nao_conformidades = [];
$visita_selecionada_id = $_GET['visita_id'] ?? 0;

if ($visita_selecionada_id > 0) {
    // Query principal agora busca também os dados do plano de ação (status e data)
    $sql = "SELECT 
                cr.id AS resposta_id,
                r.numero,
                r.descricao AS requisito_descricao,
                cr.descricao_nao_conformidade,
                pa.acao_corretiva,
                pa.responsavel,
                pa.prazo,
                pa.custo,
                pa.status,
                pa.data_conclusao
            FROM checklist_respostas cr
            JOIN requisitos r ON cr.requisito_id = r.id
            LEFT JOIN planos_de_acao pa ON cr.id = pa.checklist_resposta_id
            WHERE cr.conformidade = 'Não Conforme' AND cr.visita_id = $visita_selecionada_id
            ORDER BY r.numero";
    
    $resultado = mysqli_query($conexao, $sql);
    if($resultado) {
        while($row = mysqli_fetch_assoc($resultado)) {
            $nao_conformidades[] = $row;
        }
    }
}
?>
<h2 class="mb-4">Plano de Ação</h2>
<?php
if (isset($_SESSION['mensagem_sucesso'])) {
    echo '<div class="alert alert-success">' . $_SESSION['mensagem_sucesso'] . '</div>';
    unset($_SESSION['mensagem_sucesso']);
}
?>

<div class="card shadow mb-4">
    <div class="card-header">Filtros</div>
    <div class="card-body">
        <form action="plano_de_acao.php" method="GET">
            <div class="row align-items-end">
                <div class="col-md-10">
                    <label for="visita_id" class="form-label">Selecione a Visita</label>
                    <select class="form-select" id="visita_id" name="visita_id" onchange="this.form.submit()">
                        <option value="">-- Selecione --</option>
                        <?php mysqli_data_seek($resultado_visitas, 0); while($visita = mysqli_fetch_assoc($resultado_visitas)): ?>
                            <option value="<?php echo $visita['id']; ?>" <?php if($visita['id'] == $visita_selecionada_id) echo 'selected'; ?>>
                                <?php echo date('d/m/Y', strtotime($visita['data_da_visita'])) . ' - ' . htmlspecialchars($visita['nome_empresa']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($nao_conformidades)): ?>
<form action="salvar_plano.php" method="POST">
    <input type="hidden" name="visita_id" value="<?php echo $visita_selecionada_id; ?>">
    <div class="card shadow mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Não Conformidades Encontradas</span>
            <button type="submit" class="btn btn-success">Salvar Plano</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Não Conformidade</th>
                            <th>Ação Corretiva</th>
                            <th>Responsável</th>
                            <th>Prazo</th>
                            <th>Custo</th>
                            <th style="width: 150px;">Status</th>
                            <th style="width: 180px;">Data de Conclusão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($nao_conformidades as $item): 
                            // Define a cor da linha com base no status
                            $status_class = $item['status'] == 'Concluído' ? 'table-success' : '';
                        ?>
                            <tr class="<?php echo $status_class; ?>">
                                <td>
                                    <strong>Req. <?php echo $item['numero']; ?>:</strong> <?php echo htmlspecialchars($item['requisito_descricao']); ?><br>
                                    <small class="text-danger"><strong>Evidência:</strong> <?php echo htmlspecialchars($item['descricao_nao_conformidade']); ?></small>
                                </td>
                                <td><input type="text" class="form-control" name="plano[<?php echo $item['resposta_id']; ?>][acao_corretiva]" value="<?php echo htmlspecialchars($item['acao_corretiva'] ?? ''); ?>"></td>
                                <td><input type="text" class="form-control" name="plano[<?php echo $item['resposta_id']; ?>][responsavel]" value="<?php echo htmlspecialchars($item['responsavel'] ?? ''); ?>"></td>
                                <td><input type="date" class="form-control" name="plano[<?php echo $item['resposta_id']; ?>][prazo]" value="<?php echo htmlspecialchars($item['prazo'] ?? ''); ?>"></td>
                                <td><input type="number" step="0.01" class="form-control" name="plano[<?php echo $item['resposta_id']; ?>][custo]" value="<?php echo htmlspecialchars($item['custo'] ?? ''); ?>"></td>
                                <td>
                                    <select class="form-select" name="plano[<?php echo $item['resposta_id']; ?>][status]">
                                        <option value="Pendente" <?php if(($item['status'] ?? 'Pendente') == 'Pendente') echo 'selected'; ?>>Pendente</option>
                                        <option value="Concluído" <?php if(($item['status'] ?? '') == 'Concluído') echo 'selected'; ?>>Concluído</option>
                                    </select>
                                </td>
                                <td><input type="date" class="form-control" name="plano[<?php echo $item['resposta_id']; ?>][data_conclusao]" value="<?php echo htmlspecialchars($item['data_conclusao'] ?? ''); ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
mysqli_close($conexao);
?>