<?php
// 1. Inclusões e Conexão
require_once 'includes/verifica_login.php'; 
require_once 'conexao.php';
require_once 'includes/header.php';

// --- BUSCA DE DADOS PARA OS FILTROS ---
$sql_visitas = "SELECT v.id, v.data_da_visita, e.nome AS nome_empresa FROM visitas v JOIN empresas e ON v.empresa_id = e.id ORDER BY v.data_da_visita DESC";
$resultado_visitas = mysqli_query($conexao, $sql_visitas);

$sql_etapas = "SELECT id, nome FROM etapas ORDER BY nome";
$resultado_etapas = mysqli_query($conexao, $sql_etapas);

// --- LÓGICA DE SELEÇÃO ---
$requisitos = []; 
$respostas_salvas = []; 
$visita_selecionada_id = $_GET['visita_id'] ?? 0;
$etapa_selecionada_id = $_GET['etapa_id'] ?? 0;

if ($etapa_selecionada_id > 0) {
    // Busca Requisitos
    $sql_req = "SELECT id, numero, descricao, criticidade_padrao FROM requisitos WHERE etapa_id = $etapa_selecionada_id ORDER BY numero";
    $res_req = mysqli_query($conexao, $sql_req);
    while ($row = mysqli_fetch_assoc($res_req)) { $requisitos[] = $row; }

    // Busca Respostas já existentes
    if ($visita_selecionada_id > 0) {
        $sql_resp = "SELECT * FROM checklist_respostas WHERE visita_id = $visita_selecionada_id";
        $res_resp = mysqli_query($conexao, $sql_resp);
        while ($row = mysqli_fetch_assoc($res_resp)) { $respostas_salvas[$row['requisito_id']] = $row; }
    }
}
?>

<style>
    .bg-conforme { background-color: #d1e7dd !important; color: #0f5132; }
    .bg-nao-conforme { background-color: #f8d7da !important; color: #842029; }
    .bg-nao-aplicavel { background-color: #e2e3e5 !important; color: #41464b; }
    .bg-nao-observado { background-color: #fff3cd !important; color: #664d03; }
    .necessita-justificativa { border: 2px solid #dc3545 !important; }
    .auto-expand { min-height: 38px; resize: none; overflow:hidden; transition: background 0.3s; }
</style>

<h2 class="mb-4">Checklist de Verificação</h2>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="checklist.php" method="GET">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Visita</label>
                    <select class="form-select" name="visita_id" required onchange="this.form.submit()">
                        <option value="">-- Selecione --</option>
                        <?php mysqli_data_seek($resultado_visitas, 0); 
                        while($v = mysqli_fetch_assoc($resultado_visitas)): ?>
                            <option value="<?= $v['id'] ?>" <?= ($v['id'] == $visita_selecionada_id) ? 'selected' : '' ?>>
                                <?= date('d/m/Y', strtotime($v['data_da_visita'])) . " - " . $v['nome_empresa'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Etapa</label>
                    <select class="form-select" name="etapa_id" required onchange="this.form.submit()">
                        <option value="">-- Selecione --</option>
                        <?php mysqli_data_seek($resultado_etapas, 0);
                        while($e = mysqli_fetch_assoc($resultado_etapas)): ?>
                            <option value="<?= $e['id'] ?>" <?= ($e['id'] == $etapa_selecionada_id) ? 'selected' : '' ?>>
                                <?= $e['nome'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($requisitos)): ?>
    <form id="form-checklist" action="salvar_checklist.php" method="POST">
        <input type="hidden" name="visita_id" value="<?= $visita_selecionada_id ?>">
        
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between">
                <strong>Itens de Verificação</strong>
                <button type="submit" class="btn btn-success btn-sm">Salvar Tudo</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Requisito</th>
                            <th width="150">Criticidade</th>
                            <th width="200">Conformidade</th>
                            <th>Descrição da Não Conformidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requisitos as $req): 
                            $resp = $respostas_salvas[$req['id']] ?? null;
                            $conf = $resp['conformidade'] ?? '';
                            $crit = $resp['criticidade'] ?? $req['criticidade_padrao'];
                            
                            // Classe de cor inicial
                            $classe_cor = '';
                            if($conf == 'Conforme') $classe_cor = 'bg-conforme';
                            elseif($conf == 'Não Conforme') $classe_cor = 'bg-nao-conforme';
                        ?>
                        <tr>
                            <td><?= $req['numero'] ?></td>
                            <td><small><?= $req['descricao'] ?></small></td>
                            <td>
                                <select class="form-select form-select-sm" name="respostas[<?= $req['id'] ?>][criticidade]">
                                    <option value="Crítico" <?= ($crit == 'Crítico') ? 'selected' : '' ?>>Crítico</option>
                                    <option value="Não Crítico" <?= ($crit == 'Não Crítico') ? 'selected' : '' ?>>Não Crítico</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm selector-conformidade <?= $classe_cor ?>" 
                                        name="respostas[<?= $req['id'] ?>][conformidade]" required
                                        onchange="atualizarEstilo(this)">
                                    <option value="">Selecione...</option>
                                    <option value="Conforme" <?= ($conf == 'Conforme') ? 'selected' : '' ?>>Conforme</option>
                                    <option value="Não Conforme" <?= ($conf == 'Não Conforme') ? 'selected' : '' ?>>Não Conforme</option>
                                    <option value="Não Aplicável" <?= ($conf == 'Não Aplicável') ? 'selected' : '' ?>>Não Aplicável</option>
                                </select>
                            </td>
                            <td>
                                <textarea class="form-control auto-expand <?= ($conf == 'Não Conforme') ? 'necessita-justificativa' : '' ?>" 
                                          name="respostas[<?= $req['id'] ?>][descricao]"
                                          oninput="ajustaAltura(this)"
                                          placeholder="Descreva aqui..."><?= htmlspecialchars($resp['descricao_nao_conformidade'] ?? '') ?></textarea>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
<?php endif; ?>

<script>
function ajustaAltura(el) {
    el.style.height = '';
    el.style.height = el.scrollHeight + 'px';
}

function atualizarEstilo(sel) {
    // Remove cores
    sel.classList.remove('bg-conforme', 'bg-nao-conforme', 'bg-nao-aplicavel');
    
    const txt = sel.closest('tr').querySelector('textarea');
    txt.classList.remove('necessita-justificativa');

    if(sel.value === 'Conforme') sel.classList.add('bg-conforme');
    if(sel.value === 'Não Aplicável') sel.classList.add('bg-nao-aplicavel');
    
    if(sel.value === 'Não Conforme') {
        sel.classList.add('bg-nao-conforme');
        txt.classList.add('necessita-justificativa');
        txt.focus();
    }
}

// Inicializações ao carregar o DOM
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.auto-expand').forEach(ajustaAltura);
    
    // Captura e gerencia o envio do formulário do checklist
    const form = document.getElementById('form-checklist');
    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Impede o recarregamento padrão da página
            
            const formData = new FormData(this);
            
            // Verifica se o dispositivo possui conexão ativa com a internet
            if (navigator.onLine) {
                // Envio síncrono/assíncrono tradicional via AJAX (Fetch) para o servidor
                fetch('salvar_checklist.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        alert('Checklist enviado e salvo com sucesso no servidor!');
                        // Opcional: recarrega a página para atualizar os dados salvos
                        window.location.reload();
                    } else {
                        alert('O servidor falhou ao salvar. Salvando localmente em segurança...');
                        salvarNoIndexedDBLocal(formData);
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição, salvando em modo Offline:', error);
                    salvarNoIndexedDBLocal(formData);
                });
            } else {
                // Dispositivo completamente sem sinal de internet (Aciona db-local.js)
                salvarNoIndexedDBLocal(formData);
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; mysqli_close($conexao); ?>