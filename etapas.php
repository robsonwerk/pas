<?php
// Inclui nossos arquivos de base

require_once 'includes/verifica_login.php'; // PRIMEIRO DE TUDO
require_once 'conexao.php';
require_once 'includes/header.php';
// --- INÍCIO DA LÓGICA PHP ---
$mensagem = '';
$etapa_para_editar = null;

// --- LÓGICA PARA EXCLUIR UMA ETAPA ---
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    // Adicionamos uma verificação para não excluir etapas em uso
    $sql_check = "SELECT COUNT(*) AS total FROM requisitos WHERE etapa_id = $id_para_deletar";
    $result_check = mysqli_query($conexao, $sql_check);
    $row_check = mysqli_fetch_assoc($result_check);

    if ($row_check['total'] > 0) {
        $mensagem = "Erro: Esta etapa não pode ser excluída pois está sendo usada em requisitos do checklist.";
    } else {
        $sql = "DELETE FROM etapas WHERE id = $id_para_deletar";
        if (mysqli_query($conexao, $sql)) {
            header("Location: etapas.php?status=deletado"); // Redireciona para evitar re-exclusão
            exit;
        } else {
            $mensagem = "Erro ao deletar etapa: " . mysqli_error($conexao);
        }
    }
}

// --- LÓGICA PARA PROCESSAR FORMULÁRIOS (CADASTRAR OU ATUALIZAR) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Se for uma ATUALIZAÇÃO
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['id_para_atualizar'];
        $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
        
        $sql = "UPDATE etapas SET nome='$nome' WHERE id=$id";
        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Etapa atualizada com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar etapa: " . mysqli_error($conexao);
        }
    } 
    // Se for um CADASTRO novo
    elseif (isset($_POST['cadastrar'])) {
        $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
        
        $sql = "INSERT INTO etapas (nome) VALUES ('$nome')";
        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Nova etapa cadastrada com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar etapa: " . mysqli_error($conexao);
        }
    }
}

// --- LÓGICA PARA CARREGAR DADOS PARA EDIÇÃO ---
if (isset($_GET['edit_id'])) {
    $id_para_editar = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM etapas WHERE id = $id_para_editar";
    $resultado_edicao = mysqli_query($conexao, $sql);
    $etapa_para_editar = mysqli_fetch_assoc($resultado_edicao);
}

// Mensagem de sucesso para a exclusão (após redirecionamento)
if (isset($_GET['status']) && $_GET['status'] == 'deletado') {
    $mensagem = "Etapa excluída com sucesso!";
}

// --- LÓGICA PARA BUSCAR AS ETAPAS NO BANCO ---
$sql_select = "SELECT id, nome FROM etapas ORDER BY nome";
$resultado = mysqli_query($conexao, $sql_select);
// --- FIM DA LÓGICA PHP ---
?>

<h2 class="mb-4">Gerenciamento de Etapas do Checklist</h2>
<?php if (!empty($mensagem)): ?>
    <div class="alert alert-info"><?php echo $mensagem; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Etapas Cadastradas</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome da Etapa</th>
                        <th width="150px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0):
                        while($etapa = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($etapa['nome']); ?></td>
                            <td>
                                <a href="etapas.php?edit_id=<?php echo $etapa['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="etapas.php?delete_id=<?php echo $etapa['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta etapa?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="2" class="text-center">Nenhuma etapa cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-5 mb-5">
    <div class="card-header">
        <?php 
        // Muda o título do formulário se estiver em modo de edição
        if ($etapa_para_editar): echo "Editando Etapa"; else: echo "Cadastrar Nova Etapa"; endif; 
        ?>
    </div>
    <div class="card-body">
        <form action="etapas.php" method="POST">
            <?php if ($etapa_para_editar): // Adiciona campo escondido com o ID para edição ?>
                <input type="hidden" name="id_para_atualizar" value="<?php echo $etapa_para_editar['id']; ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Etapa</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($etapa_para_editar['nome'] ?? ''); ?>" placeholder="Ex: 07-Aspectos gerais de..." required>
            </div>

            <?php if ($etapa_para_editar): // Mostra botão de Atualizar ?>
                <button type="submit" name="atualizar" class="btn btn-warning">Atualizar</button>
                <a href="etapas.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php else: // Mostra botão de Cadastrar ?>
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