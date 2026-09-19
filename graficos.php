<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

// --- BUSCA LISTA DE VISITAS ---
$sql_visitas = "SELECT v.id, v.data_da_visita, e.nome AS nome_empresa FROM visitas v JOIN empresas e ON v.empresa_id = e.id ORDER BY v.data_da_visita DESC";
$resultado_visitas = mysqli_query($conexao, $sql_visitas);

$visita_selecionada_id = isset($_GET['visita_id']) ? (int)$_GET['visita_id'] : 0;
$dados_pizza = null;
$dados_treemap = [];
$totais = ['itens' => 0, 'nao_conformes' => 0, 'conformes' => 0];

if ($visita_selecionada_id > 0) {
    // 1. BUSCA TOTAIS PARA OS CARDS (NÚMEROS)
    $sql_totais = "SELECT 
                    COUNT(*) as total_itens,
                    SUM(CASE WHEN conformidade = 'Conforme' THEN 1 ELSE 0 END) AS conforme,
                    SUM(CASE WHEN conformidade = 'Não Conforme' THEN 1 ELSE 0 END) AS nao_conforme
                  FROM checklist_respostas WHERE visita_id = $visita_selecionada_id";
    $res_totais = mysqli_query($conexao, $sql_totais);
    $totais = mysqli_fetch_assoc($res_totais);

    // 2. DADOS PARA O GRÁFICO DE ROSCA
    $dados_pizza = [
        $totais['conforme'] ?? 0, 
        $totais['nao_conforme'] ?? 0, 
        ($totais['total_itens'] - $totais['conforme'] - $totais['nao_conforme'])
    ];

    // 3. DADOS PARA O TREEMAP
    $sql_treemap = "SELECT et.nome AS etapa, COUNT(cr.id) AS total
                    FROM checklist_respostas cr
                    JOIN requisitos r ON cr.requisito_id = r.id
                    JOIN etapas et ON r.etapa_id = et.id
                    WHERE cr.visita_id = $visita_selecionada_id
                    GROUP BY et.nome";
    $res_tree = mysqli_query($conexao, $sql_treemap);
    while($row = mysqli_fetch_assoc($res_tree)) {
        $dados_treemap[] = $row;
    }
}
?>

<h2 class="mb-4">Dashboard PGRS - Diagnóstico</h2>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="graficos.php" method="GET" class="row align-items-end">
            <div class="col-md-10">
                <label class="form-label">Filtrar por Visita</label>
                <select class="form-select" name="visita_id" required>
                    <option value="">-- Selecione uma visita --</option>
                    <?php mysqli_data_seek($resultado_visitas, 0); while($v = mysqli_fetch_assoc($resultado_visitas)): ?>
                        <option value="<?= $v['id'] ?>" <?= ($v['id'] == $visita_selecionada_id) ? 'selected' : '' ?>>
                            <?= date('d/m/Y', strtotime($v['data_da_visita'])) ?> - <?= htmlspecialchars($v['nome_empresa']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Gerar</button>
            </div>
        </form>
    </div>
</div>

<?php if ($visita_selecionada_id > 0): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow border-left-primary py-2 text-center">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Itens</div>
                <div class="h2 mb-0 font-weight-bold text-gray-800"><?= $totais['total_itens'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow border-left-success py-2 text-center">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Itens Conformes</div>
                <div class="h2 mb-0 font-weight-bold text-gray-800"><?= $totais['conforme'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow border-left-danger py-2 text-center">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Não Conformidades</div>
                <div class="h2 mb-0 font-weight-bold text-gray-800"><?= $totais['nao_conforme'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-white"><strong>Ambientes/Etapas</strong></div>
            <div class="card-body">
                <div id="treemap_div" style="width: 100%; height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-white"><strong>Conformidade (%)</strong></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="width: 80%;"><canvas id="graficoRosca"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// (O código JavaScript de renderização do Treemap e Rosca permanece o mesmo do passo anterior)
// ...
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dados para o Treemap (Blocos)
    // Verificamos se há dados antes de tentar desenhar
    const dadosTreemapRaw = <?php echo json_encode($dados_treemap); ?>;
    
    if (dadosTreemapRaw.length > 0) {
        google.charts.load('current', {'packages':['treemap']});
        google.charts.setOnLoadCallback(function() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'ID');
            data.addColumn('string', 'Parent');
            data.addColumn('number', 'Quantidade');
            
            data.addRow(['Total', null, 0]);
            dadosTreemapRaw.forEach(item => {
                data.addRow([item.etapa, 'Total', parseInt(item.total)]);
            });

            var tree = new google.visualization.TreeMap(document.getElementById('treemap_div'));
            tree.draw(data, {
                minColor: '#a5c8ff',
                midColor: '#4d94ff',
                maxColor: '#0052cc',
                headerHeight: 0,
                showScale: false
            });
        });
    }

    // 2. Dados para a Rosca (Chart.js)
    const dadosPizza = <?php echo json_encode(array_values($dados_pizza)); ?>;
    const ctx = document.getElementById('graficoRosca');
    
   new Chart(ctx, {
    type: 'doughnut',
    plugins: [ChartDataLabels], // Ativa o plugin de labels
    data: {
        labels: ['Conforme', 'Não Conforme', 'Outros'],
        datasets: [{
            data: <?= json_encode($dados_pizza) ?>,
            backgroundColor: ['#0052cc', '#ff4d4d', '#ffcc00']
        }]
    },
    options: {
        plugins: {
            legend: { position: 'right' },
            datalabels: {
                color: '#fff',
                font: { weight: 'bold', size: 14 },
                formatter: (value, ctx) => {
                    let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    let percentage = ((value * 100) / sum).toFixed(1) + "%";
                    return value + "\n(" + percentage + ")"; // Exibe valor e %
                }
            }
        }
    }
});
function drawChart() {
    var data = google.visualization.arrayToDataTable([
        ['ID', 'Parent', 'Quantidade'],
        ['Total', null, 0],
        <?php foreach($dados_treemap as $item): ?>
            // Concatenamos o nome com o valor para aparecer no bloco
            ['<?= $item['etapa'] ?>: <?= $item['total'] ?>', 'Total', <?= $item['total'] ?>],
        <?php endforeach; ?>
    ]);

    var tree = new google.visualization.TreeMap(document.getElementById('treemap_div'));
    tree.draw(data, {
        headerHeight: 0,
        showLabels: true, // Garante que os rótulos apareçam
        fontColor: 'white',
        fontSize: 14,
        minColor: '#a5c8ff',
        midColor: '#4d94ff',
        maxColor: '#0052cc'
    });
}
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>