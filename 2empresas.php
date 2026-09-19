<?php
// 1. VERIFICA O LOGIN E INICIA A SESSÃO
require_once 'includes/verifica_login.php';

// 2. CONECTA AO BANCO DE DADOS
require_once 'conexao.php';

// 3. EXECUTA TODA A LÓGICA DE PROCESSAMENTO (DELETAR, SALVAR, ATUALIZAR)
$mensagem = '';
$empresa_para_editar = null;

// --- LÓGICA PARA EXCLUIR UMA EMPRESA (COM PREPARED STATEMENTS) ---
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    
    $sql = "DELETE FROM empresas WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_para_deletar); // i = integer

    if (mysqli_stmt_execute($stmt)) {
        header("Location: empresas.php?status=deletado");
        exit;
    } else {
        $mensagem = "Erro ao deletar empresa: " . mysqli_stmt_error($stmt);
    }
}

// --- LÓGICA PARA PROCESSAR FORMULÁRIOS (COM PREPARED STATEMENTS) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados do formulário (sem a necessidade de mysqli_real_escape_string)
    $nome = $_POST['nome'];
    $localidade = $_POST['localidade'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $cnpj = $_POST['cnpj'] ?? null;
    $uf = $_POST['uf'] ?? null;
    
    $setor_id = !empty($_POST['setor_id']) ? (int)$_POST['setor_id'] : null;

    // Se for uma ATUALIZAÇÃO
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['id_para_atualizar'];
        
        $sql = "UPDATE empresas SET nome=?, localidade=?, telefone=?, uf=?, cnpj=?, setor_id=?  WHERE id=?";
        $stmt = mysqli_prepare($conexao, $sql);
        // "ssssii" -> string, string, string, string, integer, integer
        mysqli_stmt_bind_param($stmt, "sssssii", $nome, $localidade, $telefone, $uf, $cnpj, $setor_id, $id);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Empresa atualizada com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } 
    // Se for um CADASTRO novo
    elseif (isset($_POST['cadastrar'])) {
        $sql = "INSERT INTO empresas (nome, localidade, telefone, uf, cnpj, setor_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        // "ssssi" -> string, string, string, string, integer
        mysqli_stmt_bind_param($stmt, "sssssi", $nome, $localidade, $telefone, $uf, $cnpj, $setor_id);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Nova empresa cadastrada com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- LÓGICA PARA CARREGAR DADOS PARA A PÁGINA ---
// Carregar dados para edição
if (isset($_GET['edit_id'])) {
    $id_para_editar = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM empresas WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_para_editar);
    mysqli_stmt_execute($stmt);
    $resultado_edicao = mysqli_stmt_get_result($stmt);
    $empresa_para_editar = mysqli_fetch_assoc($resultado_edicao);
}

// Mensagem de sucesso para a exclusão
if (isset($_GET['status']) && $_GET['status'] == 'deletado') {
    $mensagem = "Empresa excluída com sucesso!";
}

// Buscar todas as empresas para a lista
$sql_select_empresas = "SELECT empresas.*, setores.nome AS nome_setor FROM empresas LEFT JOIN setores ON empresas.setor_id = setores.id ORDER BY empresas.nome";
$resultado_empresas = mysqli_query($conexao, $sql_select_empresas);

// Buscar todos os setores para o formulário
$sql_select_setores = "SELECT id, nome FROM setores ORDER BY nome";
$resultado_setores = mysqli_query($conexao, $sql_select_setores);

// 4. AGORA, E SOMENTE AGORA, INCLUA O CABEÇALHO HTML
require_once 'includes/header.php';

// 5. O RESTO DA PÁGINA QUE MOSTRA CONTEÚDO
?>

<h2 class="mb-4">Gerenciamento de Empresas</h2>
<?php
// Exibe mensagem de sucesso vinda de outras páginas (se houver)
if (isset($_SESSION['mensagem_sucesso'])) {
    echo '<div class="alert alert-success">' . $_SESSION['mensagem_sucesso'] . '</div>';
    unset($_SESSION['mensagem_sucesso']);
}
// Exibe mensagem de feedback da própria página
if (!empty($mensagem)) {
    echo '<div class="alert alert-info">' . $mensagem . '</div>';
}
?>

<div class="card">
    <div class="card-header">Empresas Cadastradas</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Setor</th>
                        <th>UF</th>
                        <th>CNPJ</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultado_empresas) > 0):
                        while($empresa = mysqli_fetch_assoc($resultado_empresas)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($empresa['nome']); ?></td>
                            <td><?php echo htmlspecialchars($empresa['nome_setor'] ?? 'N/D'); ?></td>
                            <td><?php echo htmlspecialchars($empresa['uf']?? ''); ?></td>
                            <td><?php echo htmlspecialchars($empresa['cnpj']?? ''); ?></td>
                            <td>
                                <a href="empresas.php?edit_id=<?php echo $empresa['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="empresas.php?delete_id=<?php echo $empresa['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta empresa?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center">Nenhuma empresa cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-5 mb-5">
    <div class="card-header">
        <?php echo $empresa_para_editar ? "Editando Empresa" : "Cadastrar Nova Empresa"; ?>
    </div>
    <div class="card-body">
        <form action="empresas.php" method="POST">

            <?php if ($empresa_para_editar): ?>
                <input type="hidden" name="id_para_atualizar" value="<?php echo $empresa_para_editar['id']; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa_para_editar['nome'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="setor_id" class="form-label">Setor</label>
                    <select class="form-select" id="setor_id" name="setor_id">
                        <option value="">-- Selecione um setor --</option>
                        <?php 
                        mysqli_data_seek($resultado_setores, 0);
                        if (mysqli_num_rows($resultado_setores) > 0):
                            while($setor = mysqli_fetch_assoc($resultado_setores)): 
                                $selecionado = ($empresa_para_editar && $empresa_para_editar['setor_id'] == $setor['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $setor['id']; ?>" <?php echo $selecionado; ?>><?php echo htmlspecialchars($setor['nome']); ?></option>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="localidade" class="form-label">Localidade</label>
                    <input type="text" class="form-control" id="localidade" name="localidade" value="<?php echo htmlspecialchars($empresa_para_editar['localidade'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($empresa_para_editar['telefone'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="uf" class="form-label">UF</label>
                    <input type="text" class="form-control" id="uf" name="uf" maxlength="2" value="<?php echo htmlspecialchars($empresa_para_editar['uf'] ?? ''); ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="cnpj" class="form-label">CNPJ</label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" maxlength="18" oninput="mascaraCNPJ(this)" value="<?php echo htmlspecialchars($empresa_para_editar['cnpj'] ?? ''); ?>">
                </div>
            </div>
            
            <?php if ($empresa_para_editar): ?>
                <button type="submit" name="atualizar" class="btn btn-warning">Atualizar</button>
                <a href="empresas.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php else: ?>
                <button type="submit" name="cadastrar" class="btn btn-primary">Salvar</button>
            <?php endif; ?>

        </form>
    </div>
</div>

<?php
// Inclui o rodapé da página e fecha a conexão
require_once 'includes/footer.php';
mysqli_close($conexao);
?>


<script>
function mascaraCNPJ(input) {
    let cnpj = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    cnpj = cnpj.replace(/^(\d{2})(\d)/, '$1.$2'); // Formata .
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3'); // Formata .
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4'); // Formata /
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5'); // Formata -
    input.value = cnpj; // Aplica a máscara
}
</script>