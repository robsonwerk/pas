<?php
// 1. VERIFICA O LOGIN E INICIA A SESSÃO
require_once 'includes/verifica_login.php';

// 2. CONECTA AO BANCO DE DADOS
require_once 'conexao.php';

// 3. EXECUTA TODA A LÓGICA DE PROCESSAMENTO (DELETAR, SALVAR, ATUALIZAR)
$mensagem = '';
$empresa_para_editar = null;

// --- LÓGICA PARA EXCLUIR UMA EMPRESA ---
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    
    $sql = "DELETE FROM empresas WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_para_deletar);

    if (mysqli_stmt_execute($stmt)) {
        // REGISTRA O LOG ANTES DO REDIRECIONAMENTO
        registrarLog($conexao, "Excluiu uma empresa", "empresas", $id_para_deletar);
        
        header("Location: empresas.php?status=deletado");
        exit;
    } else {
        $mensagem = "Erro ao deletar empresa: " . mysqli_stmt_error($stmt);
    }
}


// --- LÓGICA PARA PROCESSAR FORMULÁRIOS (CADASTRO E ATUALIZAÇÃO) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados básicos
    $nome = $_POST['nome'];
    $localidade = $_POST['localidade'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $cnpj = $_POST['cnpj'] ?? null;
    $uf = $_POST['uf'] ?? null;
    $setor_id = !empty($_POST['setor_id']) ? (int)$_POST['setor_id'] : null;

    // --- NOVOS CAMPOS CAPTURADOS DO POST ---
    $razao_social  = $_POST['razao_social'] ?? null;
    $nome_fantasia = $_POST['nome_fantasia'] ?? null;
    $endereco      = $_POST['endereco'] ?? null;
    $contato_nome  = $_POST['contato_nome'] ?? null;

    // Se for uma ATUALIZAÇÃO
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['id_para_atualizar'];
        
        $sql = "UPDATE empresas SET nome=?, localidade=?, telefone=?, uf=?, cnpj=?, setor_id=?, razao_social=?, nome_fantasia=?, endereco=?, contato_nome=? WHERE id=?";
        $stmt = mysqli_prepare($conexao, $sql);
        // "sssssi ssss i" -> s=string, i=integer
        mysqli_stmt_bind_param($stmt, "sssssissssi", $nome, $localidade, $telefone, $uf, $cnpj, $setor_id, $razao_social, $nome_fantasia, $endereco, $contato_nome, $id);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Empresa atualizada com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } 
    // Se for um CADASTRO novo
    elseif (isset($_POST['cadastrar'])) {
        $sql = "INSERT INTO empresas (nome, localidade, telefone, uf, cnpj, setor_id, razao_social, nome_fantasia, endereco, contato_nome) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sssssissss", $nome, $localidade, $telefone, $uf, $cnpj, $setor_id, $razao_social, $nome_fantasia, $endereco, $contato_nome);

        if (mysqli_stmt_execute($stmt)) {
    $mensagem = "Nova empresa cadastrada com sucesso!";
    
    // PEGUE O ID LOGO APÓS A EXECUÇÃO
    $novo_id = mysqli_insert_id($conexao);
    // CHAMADA CORRETA COM TABELA E ID
    registrarLog($conexao, "Cadastrou uma nova empresa", "empresas", $novo_id);
}else {
            $mensagem = "Erro ao cadastrar: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- LÓGICA PARA CARREGAR DADOS PARA A PÁGINA ---
if (isset($_GET['edit_id'])) {
    $id_para_editar = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM empresas WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_para_editar);
    mysqli_stmt_execute($stmt);
    $resultado_edicao = mysqli_stmt_get_result($stmt);
    $empresa_para_editar = mysqli_fetch_assoc($resultado_edicao);
}

if (isset($_GET['status']) && $_GET['status'] == 'deletado') {
    $mensagem = "Empresa excluída com sucesso!";
}

$sql_select_empresas = "SELECT empresas.*, setores.nome AS nome_setor FROM empresas LEFT JOIN setores ON empresas.setor_id = setores.id ORDER BY empresas.nome";
$resultado_empresas = mysqli_query($conexao, $sql_select_empresas);

$sql_select_setores = "SELECT id, nome FROM setores ORDER BY nome";
$resultado_setores = mysqli_query($conexao, $sql_select_setores);

require_once 'includes/header.php';
?>

<h2 class="mb-4">Gerenciamento de Empresas</h2>
<?php
if (!empty($mensagem)) {
    echo '<div class="alert alert-info">' . $mensagem . '</div>';
}
?>

<div class="card mb-5">
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
                                <a href="empresas.php?delete_id=<?php echo $empresa['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center">Nenhuma empresa cadastrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-5">
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
                    <label class="form-label">Nome de Identificação (Curto)</label>
                    <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($empresa_para_editar['nome'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Setor</label>
                    <select class="form-select" name="setor_id">
                        <option value="">-- Selecione um setor --</option>
                        <?php 
                        mysqli_data_seek($resultado_setores, 0);
                        while($setor = mysqli_fetch_assoc($resultado_setores)): 
                            $selecionado = ($empresa_para_editar && $empresa_para_editar['setor_id'] == $setor['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $setor['id']; ?>" <?php echo $selecionado; ?>><?php echo htmlspecialchars($setor['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Razão Social</label>
                    <input type="text" name="razao_social" class="form-control" value="<?php echo htmlspecialchars($empresa_para_editar['razao_social'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome Fantasia</label>
                    <input type="text" name="nome_fantasia" class="form-control" value="<?php echo htmlspecialchars($empresa_para_editar['nome_fantasia'] ?? ''); ?>">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Endereço Completo</label>
                    <input type="text" name="endereco" class="form-control" value="<?php echo htmlspecialchars($empresa_para_editar['endereco'] ?? ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Pessoa de Contato</label>
                    <input type="text" name="contato_nome" class="form-control" value="<?php echo htmlspecialchars($empresa_para_editar['contato_nome'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
    <label for="telefone" class="form-label">Telefone</label>
    <input type="text" 
           class="form-control" 
           id="telefone" 
           name="telefone" 
           placeholder="(00) 00000-0000"
           oninput="mascaraTelefone(this)" 
           value="<?php echo htmlspecialchars($empresa_para_editar['telefone'] ?? ''); ?>">
</div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Cidade/Localidade</label>
                    <input type="text" class="form-control" name="localidade" value="<?php echo htmlspecialchars($empresa_para_editar['localidade'] ?? ''); ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">UF</label>
                    <input type="text" class="form-control" name="uf" maxlength="2" value="<?php echo htmlspecialchars($empresa_para_editar['uf'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CNPJ</label>
                    <input type="text" class="form-control" name="cnpj" maxlength="18" oninput="mascaraCNPJ(this)" value="<?php echo htmlspecialchars($empresa_para_editar['cnpj'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mt-3">
                <?php if ($empresa_para_editar): ?>
                    <button type="submit" name="atualizar" class="btn btn-warning">Atualizar</button>
                    <a href="empresas.php" class="btn btn-secondary">Cancelar</a>
                <?php else: ?>
                    <button type="submit" name="cadastrar" class="btn btn-primary">Salvar Empresa</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; mysqli_close($conexao); ?>

<script>
function mascaraCNPJ(input) {
    let cnpj = input.value.replace(/\D/g, '');
    cnpj = cnpj.replace(/^(\d{2})(\d)/, '$1.$2');
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4');
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
    input.value = cnpj;
}

function mascaraTelefone(input) {
    let tel = input.value.replace(/\D/g, ''); // Remove tudo que não é número
    
    if (tel.length > 11) tel = tel.slice(0, 11); // Limita a 11 dígitos

    if (tel.length > 10) {
        // Formato Celular: (88) 99999-9999
        tel = tel.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
    } else if (tel.length > 5) {
        // Formato Fixo: (88) 3333-3333
        tel = tel.replace(/^(\d{2})(\d{4})(\d{4}).*/, '($1) $2-$3');
    } else if (tel.length > 2) {
        // Formato DDD: (88)
        tel = tel.replace(/^(\d{2})(\d)/, '($1) $2');
    } else if (tel.length > 0) {
        // Adiciona o parêntese inicial
        tel = tel.replace(/^(\d)/, '($1');
    }

    input.value = tel;
}
</script>