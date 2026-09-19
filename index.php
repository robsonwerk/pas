<?php
// Inclui os arquivos de base
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

// --- LÓGICA PARA OS CARDS DE TOTAIS ---
$sql_total_empresas = "SELECT COUNT(id) AS total FROM empresas";
$res_emp = mysqli_query($conexao, $sql_total_empresas);
$total_empresas = mysqli_fetch_assoc($res_emp)['total'];

$sql_total_visitas = "SELECT COUNT(id) AS total FROM visitas";
$res_vis = mysqli_query($conexao, $sql_total_visitas);
$total_visitas = mysqli_fetch_assoc($res_vis)['total'];

$sql_nao_conformidades = "SELECT COUNT(id) AS total FROM checklist_respostas WHERE conformidade = 'Não Conforme'";
$res_nc = mysqli_query($conexao, $sql_nao_conformidades);
$total_nao_conformidades = mysqli_fetch_assoc($res_nc)['total'];

$sql_planos_pendentes = "SELECT COUNT(id) AS total FROM planos_de_acao WHERE status = 'Pendente'";
$res_pp = mysqli_query($conexao, $sql_planos_pendentes);
$total_planos_pendentes = mysqli_fetch_assoc($res_pp)['total'];


// --- NOVA LÓGICA PARA AS LISTAS DINÂMICAS ---

// 1. Buscar as próximas 5 visitas agendadas (a partir de hoje)
$hoje = date('Y-m-d');
$sql_proximas_visitas = "SELECT v.id, v.data_da_visita, e.nome AS nome_empresa 
                        FROM visitas v 
                        JOIN empresas e ON v.empresa_id = e.id 
                        WHERE v.data_da_visita >= '$hoje' 
                        ORDER BY v.data_da_visita ASC 
                        LIMIT 5";
$resultado_proximas_visitas = mysqli_query($conexao, $sql_proximas_visitas);

// 2. Buscar os 5 planos de ação pendentes mais urgentes
$sql_planos_urgentes = "SELECT 
                            pa.prazo, 
                            pa.responsavel, 
                            e.nome AS nome_empresa, 
                            cr.visita_id 
                        FROM planos_de_acao pa 
                        JOIN checklist_respostas cr ON pa.checklist_resposta_id = cr.id 
                        JOIN visitas v ON cr.visita_id = v.id 
                        JOIN empresas e ON v.empresa_id = e.id 
                        WHERE pa.status = 'Pendente' 
                        ORDER BY pa.prazo ASC 
                        LIMIT 5";

$resultado_planos_urgentes = mysqli_query($conexao, $sql_planos_urgentes);

if (!$resultado_planos_urgentes) {
    die("Erro técnico no Dashboard: " . mysqli_error($conexao));
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Dashboard Principal</h2>
    <span class="text-muted">Hoje, <?php echo date('d/m/Y'); ?></span>
</div>

<div class="row">
    <div class="col-md-3 mb-4"><div class="card text-white bg-primary shadow h-100"><div class="card-body"><h5 class="card-title">Empresas Cadastradas</h5><p class="card-text fs-2 fw-bold"><?php echo $total_empresas; ?></p></div><a href="empresas.php" class="card-footer text-white text-decoration-none small">Ver detalhes <i class="bi bi-arrow-right"></i></a></div></div>
    <div class="col-md-3 mb-4"><div class="card text-white bg-info shadow h-100"><div class="card-body"><h5 class="card-title">Visitas Agendadas</h5><p class="card-text fs-2 fw-bold"><?php echo $total_visitas; ?></p></div><a href="visitas.php" class="card-footer text-white text-decoration-none small">Ver detalhes <i class="bi bi-arrow-right"></i></a></div></div>
    <div class="col-md-3 mb-4"><div class="card text-white bg-warning shadow h-100"><div class="card-body"><h5 class="card-title">Não Conformidades</h5><p class="card-text fs-2 fw-bold"><?php echo $total_nao_conformidades; ?></p></div><a href="relatorios.php" class="card-footer text-white text-decoration-none small">Criar planos de ação <i class="bi bi-arrow-right"></i></a></div></div>
    <div class="col-md-3 mb-4"><div class="card text-white bg-danger shadow h-100"><div class="card-body"><h5 class="card-title">Planos Pendentes</h5><p class="card-text fs-2 fw-bold"><?php echo $total_planos_pendentes; ?></p></div><a href="lista_planos_acao.php" class="card-footer text-white text-decoration-none small">Acompanhar planos <i class="bi bi-arrow-right"></i></a></div></div>
</div>

<div class="row mt-4">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white fw-bold">Próximas Visitas Agendadas</div>
            <div class="list-group list-group-flush">
                <?php if ($resultado_proximas_visitas && mysqli_num_rows($resultado_proximas_visitas) > 0): ?>
                    <?php while($visita = mysqli_fetch_assoc($resultado_proximas_visitas)): ?>
                        <a href="visitas.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div><strong><?php echo htmlspecialchars($visita['nome_empresa']); ?></strong></div>
                            <span class="badge bg-primary rounded-pill"><?php echo date('d/m/Y', strtotime($visita['data_da_visita'])); ?></span>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="list-group-item">Nenhuma visita agendada.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white fw-bold">Planos de Ação Pendentes (Mais Urgentes)</div>
            <div class="list-group list-group-flush">
     <?php if ($resultado_planos_urgentes && mysqli_num_rows($resultado_planos_urgentes) > 0): ?>
        <?php while($plano = mysqli_fetch_assoc($resultado_planos_urgentes)): ?>
            <a href="plano_de_acao.php?visita_id=<?php echo $plano['visita_id']; ?>" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1"><?php echo htmlspecialchars($plano['nome_empresa']); ?></h6>
                    <small>
                        Prazo: 
                        <?php 
                            // Correção para datas nulas ou vazias
                            if (!empty($plano['prazo'])) {
                                echo date('d/m/Y', strtotime($plano['prazo']));
                            } else {
                                echo '<span class="text-muted">Não definido</span>';
                            }
                        ?>
                    </small>
                </div>
                <p class="mb-1 small">Responsável: <?php echo htmlspecialchars($plano['responsavel'] ?? 'Não atribuído'); ?></p>
                
                <?php 
                    // Só exibe "Vencido" se houver um prazo e ele for menor que hoje
                    if (!empty($plano['prazo']) && $plano['prazo'] < $hoje): 
                ?>
                    <span class="badge bg-danger">Vencido</span>
                <?php endif; ?>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="list-group-item">Nenhum plano de ação pendente.</div>
    <?php endif; ?>
</div>
        </div>
    </div>
</div>

<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js')
      .then(reg => console.log('Service Worker registrado com sucesso!', reg))
      .catch(err => console.error('Erro ao registrar Service Worker:', err));
  });
}
</script>

<?php
require_once 'includes/footer.php';
mysqli_close($conexao);
?>