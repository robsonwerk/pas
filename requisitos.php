<?php
// Inclui nossos arquivos de base

require_once 'includes/verifica_login.php'; // PRIMEIRO DE TUDO
require_once 'conexao.php';
require_once 'includes/header.php';

// --- INÍCIO DA LÓGICA PHP ---
$mensagem = '';
$requisito_para_editar = null;

// --- LÓGICA PARA EXCLUIR UM REQUISITO ---
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    $sql = "DELETE FROM requisitos WHERE id = $id_para_deletar";
    if (mysqli_query($conexao, $sql)) {
        header("Location: requisitos.php?status=deletado");
        exit;
    } else {
        $mensagem = "Erro ao deletar requisito: " . mysqli_error($conexao);
    }
}

// --- LÓGICA PARA PROCESSAR FORMULÁRIOS (CADASTRAR OU ATUALIZAR) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $etapa_id = (int)$_POST['etapa_id'];
    $numero = mysqli_real_escape_string($conexao, $_POST['numero']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $criticidade = mysqli_real_escape_string($conexao, $_POST['criticidade_padrao']);

    // Se for uma ATUALIZAÇÃO
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['id_para_atualizar'];
        $sql = "UPDATE requisitos SET etapa_id=$etapa_id, numero='$numero', descricao='$descricao', criticidade_padrao='$criticidade' WHERE id=$id";
        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Requisito atualizado com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar requisito: " . mysqli_error($conexao);
        }
    } 
    // Se for um CADASTRO novo
    elseif (isset($_POST['cadastrar'])) {
        $sql = "INSERT INTO requisitos (etapa_id, numero, descricao, criticidade_padrao) VALUES ($etapa_id, '$numero', '$descricao', '$criticidade')";
        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Novo requisito cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar requisito: " . mysqli_error($conexao);
        }
    }
}

// --- LÓGICA PARA CARREGAR DADOS PARA EDIÇÃO ---
if (isset($_GET['edit_id'])) {
    $id_para_editar = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM requisitos WHERE id = $id_para_editar";
    $resultado_edicao = mysqli_query($conexao, $sql);
    $requisito_para_editar = mysqli_fetch_assoc($resultado_edicao);
}

// Mensagem de sucesso para a exclusão (após redirecionamento)
if (isset($_GET['status']) && $_GET['status'] == 'deletado') {
    $mensagem = "Requisito excluído com sucesso!";
}

// --- LÓGICA PARA BUSCAR OS DADOS PARA A PÁGINA ---
// 1. Buscando todos os requisitos com o nome da etapa
$sql_select_requisitos = "SELECT r.*, e.nome AS nome_etapa FROM requisitos r JOIN etapas e ON r.etapa_id = e.id ORDER BY r.numero";
$resultado_requisitos = mysqli_query($conexao, $sql_select_requisitos);

// 2. Buscando todas as etapas para o formulário
$sql_select_etapas = "SELECT id, nome FROM etapas ORDER BY nome";
$resultado_etapas = mysqli_query($conexao, $sql_select_etapas);
// --- FIM DA LÓGICA PHP ---
?>

<h2 class="mb-4">Gerenciamento de Requisitos do Checklist</h2>
<?php if (!empty($mensagem)): ?>
    <div class="alert alert-info"><?php echo $mensagem; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Requisitos Cadastrados</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Descrição</th>
                        <th>Etapa</th>
                        <th>Criticidade</th>
                        <th width="150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado_requisitos) > 0):
                        while($requisito = mysqli_fetch_assoc($resultado_requisitos)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($requisito['numero']); ?></strong></td>
                            <td><?php echo htmlspecialchars($requisito['descricao']); ?></td>
                            <td><?php echo htmlspecialchars($requisito['nome_etapa']); ?></td>
                            <td><?php echo htmlspecialchars($requisito['criticidade_padrao']); ?></td>
                            <td>
                                <a href="requisitos.php?edit_id=<?php echo $requisito['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="requisitos.php?delete_id=<?php echo $requisito['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center">Nenhum requisito cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-5 mb-5">
    <div class="card-header">
        <?php if ($requisito_para_editar): echo "Editando Requisito"; else: echo "Cadastrar Novo Requisito"; endif; ?>
    </div>
    <div class="card-body">
        <form action="requisitos.php" method="POST">
            <?php if ($requisito_para_editar): ?>
                <input type="hidden" name="id_para_atualizar" value="<?php echo $requisito_para_editar['id']; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="numero" class="form-label">Número do Requisito</label>
                    <input type="text" class="form-control" id="numero" name="numero" value="<?php echo htmlspecialchars($requisito_para_editar['numero'] ?? ''); ?>" placeholder="Ex: 01.01" required>
                </div>
                <div class="col-md-9 mb-3">
                    <label for="etapa_id" class="form-label">Etapa</label>
                    <select class="form-select" id="etapa_id" name="etapa_id" required>
                        <option value="">-- Selecione uma etapa --</option>
                        <?php 
                        mysqli_data_seek($resultado_etapas, 0);
                        if (mysqli_num_rows($resultado_etapas) > 0):
                            while($etapa = mysqli_fetch_assoc($resultado_etapas)): 
                                $selecionado = ($requisito_para_editar && $requisito_para_editar['etapa_id'] == $etapa['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $etapa['id']; ?>" <?php echo $selecionado; ?>><?php echo htmlspecialchars($etapa['nome']); ?></option>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="descricao" class="form-label">Descrição do Requisito</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo htmlspecialchars($requisito_para_editar['descricao'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="criticidade_padrao" class="form-label">Criticidade Padrão</label>
                    <select class="form-select" id="criticidade_padrao" name="criticidade_padrao">
                        <?php $criticidade = $requisito_para_editar['criticidade_padrao'] ?? ''; ?>
                        <option value="Crítico" <?php if($criticidade == 'Crítico') echo 'selected'; ?>>Crítico</option>
                        <option value="Não Crítico" <?php if($criticidade == 'Não Crítico') echo 'selected'; ?>>Não Crítico</option>
                    </select>
                </div>
            </div>
            
            <?php if ($requisito_para_editar): ?>
                <button type="submit" name="atualizar" class="btn btn-warning">Atualizar</button>
                <a href="requisitos.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php else: ?>
                <button type="submit" name="cadastrar" class="btn btn-primary">Salvar</button>
            <?php endif; ?>

        </form>
    </div>
</div>

<?php
// Inclui o rodapé e fecha a conexão
require_once 'includes/footer.php';
mysqli_close($conexao);
?>