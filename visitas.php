<?php
// 1. PRIMEIRO DE TUDO: Inicia sessão e verifica login
require_once 'includes/verifica_login.php'; 
require_once 'conexao.php';

// 2. LÓGICA DE PROCESSAMENTO (Sempre antes do HTML/Header)
$mensagem = '';
$visita_para_editar = null;

// --- LÓGICA PARA EXCLUIR UMA VISITA ---
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    
    // Opcional: Registrar Log antes de deletar
    if (function_exists('registrarLog')) {
        registrarLog($conexao, "Excluiu a visita ID: $id_para_deletar", "visitas", $id_para_deletar);
    }

    $sql = "DELETE FROM visitas WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_para_deletar);

    if (mysqli_stmt_execute($stmt)) {
        // Agora o redirecionamento vai funcionar pois nada foi enviado ao navegador ainda
        header("Location: visitas.php?status=deletado");
        exit;
    } else {
        $mensagem = "Erro ao deletar visita: " . mysqli_error($conexao);
    }
}

// --- LÓGICA PARA PROCESSAR FORMULÁRIOS (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (Coloque aqui toda a sua lógica de INSERT e UPDATE que estava no arquivo)
}
// Inclui nosso arquivo de conexão e o cabeçalho HTML

require_once 'includes/verifica_login.php'; // PRIMEIRO DE TUDO
require_once 'conexao.php';
require_once 'includes/header.php';
// --- INÍCIO DA LÓGICA PHP ---
$mensagem = '';
$visita_para_editar = null;

// --- LÓGICA PARA EXCLUIR UMA VISITA ---
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    $sql = "DELETE FROM visitas WHERE id = $id_para_deletar";
    if (mysqli_query($conexao, $sql)) {
        header("Location: visitas.php?status=deletado"); // Redireciona para evitar re-exclusão
        exit;
    } else {
        $mensagem = "Erro ao deletar visita: " . mysqli_error($conexao);
    }
}

// --- LÓGICA PARA PROCESSAR FORMULÁRIOS (AGENDAR OU ATUALIZAR) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Se for uma ATUALIZAÇÃO
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['id_para_atualizar'];
        $empresa_id = (int)$_POST['empresa_id'];
        $data_da_visita = mysqli_real_escape_string($conexao, $_POST['data_da_visita']);
        $setor_vistoriado = mysqli_real_escape_string($conexao, $_POST['setor_vistoriado']);
        $observacao = mysqli_real_escape_string($conexao, $_POST['observacao']);
        
        $sql = "UPDATE visitas SET empresa_id=$empresa_id, data_da_visita='$data_da_visita', setor='$setor_vistoriado', observacao='$observacao' WHERE id=$id";
        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Visita atualizada com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar visita: " . mysqli_error($conexao);
        }
    }
    // Se for um AGENDAMENTO novo
    elseif (isset($_POST['agendar'])) {
        $empresa_id = (int)$_POST['empresa_id'];
        $data_da_visita = mysqli_real_escape_string($conexao, $_POST['data_da_visita']);
        $setor_vistoriado = mysqli_real_escape_string($conexao, $_POST['setor_vistoriado']);
        $observacao = mysqli_real_escape_string($conexao, $_POST['observacao']);

        $sql = "INSERT INTO visitas (empresa_id, data_da_visita, setor, observacao) VALUES ('$empresa_id', '$data_da_visita', '$setor_vistoriado', '$observacao')";
        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Nova visita agendada com sucesso!";
        } else {
            $mensagem = "Erro ao agendar visita: " . mysqli_error($conexao);
        }
    }
}

// --- LÓGICA PARA CARREGAR DADOS PARA EDIÇÃO ---
if (isset($_GET['edit_id'])) {
    $id_para_editar = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM visitas WHERE id = $id_para_editar";
    $resultado_edicao = mysqli_query($conexao, $sql);
    $visita_para_editar = mysqli_fetch_assoc($resultado_edicao);
}

// Mensagem de sucesso para a exclusão (após redirecionamento)
if (isset($_GET['status']) && $_GET['status'] == 'deletado') {
    $mensagem = "Visita excluída com sucesso!";
}

// --- LÓGICA PARA BUSCAR OS DADOS PARA A PÁGINA ---
// 1. Buscando todas as visitas
$sql_select_visitas = "SELECT v.id, v.data_da_visita, v.setor AS setor_vistoriado, e.nome AS nome_empresa FROM visitas v JOIN empresas e ON v.empresa_id = e.id ORDER BY v.data_da_visita DESC";
$resultado_visitas = mysqli_query($conexao, $sql_select_visitas);

// 2. Buscando todas as empresas para o formulário
$sql_select_empresas = "SELECT id, nome FROM empresas ORDER BY nome";
$resultado_empresas = mysqli_query($conexao, $sql_select_empresas);
// --- FIM DA LÓGICA PHP ---
?>

<h2 class="mb-4">Gerenciamento de Visitas</h2>
<?php if (!empty($mensagem)): ?>
    <div class="alert alert-info"><?php echo $mensagem; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Visitas Agendadas</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Setor Vistoriado</th>
                        <th>Data da Visita</th>
                        <th>Ações</th> </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado_visitas) > 0):
                        while($visita = mysqli_fetch_assoc($resultado_visitas)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($visita['nome_empresa']); ?></td>
                            <td><strong><?php echo htmlspecialchars($visita['setor_vistoriado']); ?></strong></td>
                            <td><?php echo date('d/m/Y', strtotime($visita['data_da_visita'])); ?></td>
                            <td>
                                <a href="visitas.php?edit_id=<?php echo $visita['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="visitas.php?delete_id=<?php echo $visita['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta visita?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center">Nenhuma visita agendada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php
// Verifica se existe uma mensagem de sucesso na sessão
if (isset($_SESSION['mensagem_sucesso'])) {
    // Exibe a mensagem em um alerta de sucesso
    echo '<div class="alert alert-success">' . $_SESSION['mensagem_sucesso'] . '</div>';
    // Remove a mensagem da sessão para que ela não apareça novamente
    unset($_SESSION['mensagem_sucesso']);
}
// Verifica se existe uma mensagem de erro na sessão
if (isset($_SESSION['mensagem_erro'])) {
    // Exibe a mensagem em um alerta de perigo
    echo '<div class="alert alert-danger">' . $_SESSION['mensagem_erro'] . '</div>';
    // Remove a mensagem da sessão
    unset($_SESSION['mensagem_erro']);
}
?>




<div class="card mt-5 mb-5">
    <div class="card-header">
        <?php 
        // Muda o título do formulário se estiver em modo de edição
        if ($visita_para_editar): echo "Editando Visita"; else: echo "Agendar Nova Visita"; endif; 
        ?>
    </div>
    <div class="card-body">
        <form action="visitas.php" method="POST">
            <?php if ($visita_para_editar): // Adiciona campo escondido com o ID para edição ?>
                <input type="hidden" name="id_para_atualizar" value="<?php echo $visita_para_editar['id']; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="empresa_id" class="form-label">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id" required>
                        <option value="">-- Selecione uma empresa --</option>
                        <?php 
                        mysqli_data_seek($resultado_empresas, 0); // Reseta o ponteiro do resultado
                        if (mysqli_num_rows($resultado_empresas) > 0):
                            while($empresa = mysqli_fetch_assoc($resultado_empresas)): 
                                $selecionado = ($visita_para_editar && $visita_para_editar['empresa_id'] == $empresa['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $empresa['id']; ?>" <?php echo $selecionado; ?>><?php echo htmlspecialchars($empresa['nome']); ?></option>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="data_da_visita" class="form-label">Data da Visita</label>
                    <input type="date" class="form-control" id="data_da_visita" name="data_da_visita" value="<?php echo htmlspecialchars($visita_para_editar['data_da_visita'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="setor_vistoriado" class="form-label">Setor a ser Vistoriado</label>
                    <input type="text" class="form-control" id="setor_vistoriado" name="setor_vistoriado" value="<?php echo htmlspecialchars($visita_para_editar['setor'] ?? ''); ?>" placeholder="Ex: Cozinha, Produção...">
                </div>
                <div class="col-md-12 mb-3">
                    <label for="observacao" class="form-label">Observação (Opcional)</label>
                    <textarea class="form-control" id="observacao" name="observacao" rows="3"><?php echo htmlspecialchars($visita_para_editar['observacao'] ?? ''); ?></textarea>
                </div>
            </div>

            <?php if ($visita_para_editar): // Mostra botão de Atualizar ?>
                <button type="submit" name="atualizar" class="btn btn-warning">Atualizar</button>
                <a href="visitas.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php else: // Mostra botão de Agendar ?>
                <button type="submit" name="agendar" class="btn btn-primary">Agendar</button>
            <?php endif; ?>
<td>
    <a href="visitas.php?edit_id=<?php echo $visita['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
    <a href="visitas.php?delete_id=<?php echo $visita['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?');">Excluir</a>
    
  
    <a href="relatorio_checklist.php?visita_id=<?php echo $visita['id']; ?>" class="btn btn-info btn-sm" target="_blank">Relatório</a>
</td>
        </form>
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
// Inclui o rodapé da página e fecha a conexão com o banco
require_once 'includes/footer.php';
mysqli_close($conexao);
?>