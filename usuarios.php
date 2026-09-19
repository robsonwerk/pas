<?php
// Proteção da página: só administradores podem acessar

require_once 'includes/verifica_login.php'; // PRIMEIRO DE TUDO
require_once 'conexao.php';
require_once 'includes/header.php';

// O resto do seu código PHP vem aqui...

if ($_SESSION['usuario_papel'] !== 'Administrador') {
    // Se não for admin, redireciona para a página principal e encerra o script
    header("Location: empresas.php");
    exit;
}

// Includes de base
require_once 'conexao.php';
require_once 'includes/header.php';

// --- LÓGICA PHP (CRIAR, ATUALIZAR, EXCLUIR) ---
$mensagem = '';
$usuario_para_editar = null;

// EXCLUIR
if (isset($_GET['delete_id'])) {
    $id_para_deletar = (int)$_GET['delete_id'];
    // Impede que o usuário se auto-exclua
    if ($id_para_deletar == $_SESSION['usuario_id']) {
        $mensagem = "Erro: Você não pode excluir seu próprio usuário.";
    } else {
        $sql = "DELETE FROM usuarios WHERE id = $id_para_deletar";
        if (mysqli_query($conexao, $sql)) {
            header("Location: usuarios.php?status=deletado");
            exit;
        } else {
            $mensagem = "Erro ao deletar usuário: " . mysqli_error($conexao);
        }
    }
}

// PROCESSAR FORMULÁRIOS (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $papel_id = (int)$_POST['papel_id'];
    $empresa_id = ($papel_id == 3 && !empty($_POST['empresa_id'])) ? (int)$_POST['empresa_id'] : 'NULL';
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // ATUALIZAR
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['id_para_atualizar'];
        $sql = "UPDATE usuarios SET nome='$nome', email='$email', papel_id=$papel_id, empresa_id=$empresa_id, ativo=$ativo WHERE id=$id";
        
        // Altera a senha somente se uma nova for digitada
        if (!empty($_POST['senha'])) {
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $sql_senha = "UPDATE usuarios SET senha='$senha_hash' WHERE id=$id";
            mysqli_query($conexao, $sql_senha);
        }

        if (mysqli_query($conexao, $sql)) {
            $mensagem = "Usuário atualizado com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar: " . mysqli_error($conexao);
        }
    } 
    // CADASTRAR
    elseif (isset($_POST['cadastrar'])) {
        if (empty($_POST['senha'])) {
            $mensagem = "Erro: O campo senha é obrigatório para novos usuários.";
        } else {
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, papel_id, empresa_id, ativo) VALUES ('$nome', '$email', '$senha_hash', $papel_id, $empresa_id, $ativo)";
            if (mysqli_query($conexao, $sql)) {
                $mensagem = "Novo usuário cadastrado com sucesso!";
            } else {
                $mensagem = "Erro ao cadastrar: " . mysqli_error($conexao);
            }
        }
    }
}

// CARREGAR DADOS PARA EDIÇÃO
if (isset($_GET['edit_id'])) {
    $id_para_editar = (int)$_GET['edit_id'];
    $sql = "SELECT * FROM usuarios WHERE id = $id_para_editar";
    $resultado_edicao = mysqli_query($conexao, $sql);
    $usuario_para_editar = mysqli_fetch_assoc($resultado_edicao);
}

// MENSAGEM DE SUCESSO PÓS-DELETE
if (isset($_GET['status']) && $_GET['status'] == 'deletado') {
    $mensagem = "Usuário excluído com sucesso!";
}

// --- BUSCAR DADOS PARA A PÁGINA ---
// 1. Buscando todos os usuários
$sql_select_usuarios = "SELECT u.*, p.nome AS nome_papel, e.nome AS nome_empresa 
                        FROM usuarios u 
                        JOIN papeis p ON u.papel_id = p.id
                        LEFT JOIN empresas e ON u.empresa_id = e.id
                        ORDER BY u.nome";
$resultado_usuarios = mysqli_query($conexao, $sql_select_usuarios);

// 2. Buscando papéis e empresas para os formulários
$sql_select_papeis = "SELECT * FROM papeis ORDER BY nome";
$resultado_papeis = mysqli_query($conexao, $sql_select_papeis);
$sql_select_empresas = "SELECT * FROM empresas ORDER BY nome";
$resultado_empresas = mysqli_query($conexao, $sql_select_empresas);

?>

<h2 class="mb-4">Gerenciamento de Usuários</h2>
<?php if (!empty($mensagem)): ?>
    <div class="alert alert-info"><?php echo $mensagem; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Usuários Cadastrados</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Papel</th>
                        <th>Empresa Associada</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($usuario = mysqli_fetch_assoc($resultado_usuarios)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome_papel']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome_empresa'] ?? 'N/A'); ?></td>
                            <td><?php echo $usuario['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'; ?></td>
                            <td>
                                <a href="usuarios.php?edit_id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <?php if ($usuario['id'] != $_SESSION['usuario_id']): // Impede o botão de excluir para o próprio usuário ?>
                                <a href="usuarios.php?delete_id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?');">Excluir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-5 mb-5">
    <div class="card-header">
        <?php echo $usuario_para_editar ? "Editando Usuário" : "Cadastrar Novo Usuário"; ?>
    </div>
    <div class="card-body">
        <form id="formUsuario" action="usuarios.php" method="POST">
            <?php if ($usuario_para_editar): ?>
                <input type="hidden" name="id_para_atualizar" value="<?php echo $usuario_para_editar['id']; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario_para_editar['nome'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario_para_editar['email'] ?? ''); ?>" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" <?php echo !$usuario_para_editar ? 'required' : ''; ?>>
                    <?php if ($usuario_para_editar): ?>
                        <small class="form-text text-muted">Deixe em branco para não alterar a senha.</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="papel_id" class="form-label">Papel / Permissão</label>
                    <select class="form-select" id="papel_id" name="papel_id" required>
                        <option value="">-- Selecione --</option>
                        <?php 
                        mysqli_data_seek($resultado_papeis, 0);
                        while($papel = mysqli_fetch_assoc($resultado_papeis)): 
                            $selecionado = ($usuario_para_editar && $usuario_para_editar['papel_id'] == $papel['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $papel['id']; ?>" <?php echo $selecionado; ?>><?php echo htmlspecialchars($papel['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
              
                <div class="col-md-6 mb-3" id="campoEmpresa" style="display: none;">
                    <label for="empresa_id" class="form-label">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">-- Selecione --</option>
                        <?php 
                        mysqli_data_seek($resultado_empresas, 0);
                        while($empresa = mysqli_fetch_assoc($resultado_empresas)): 
                            $selecionado = ($usuario_para_editar && $usuario_para_editar['empresa_id'] == $empresa['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $empresa['id']; ?>" <?php echo $selecionado; ?>><?php echo htmlspecialchars($empresa['nome']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" <?php echo ($usuario_para_editar && !$usuario_para_editar['ativo']) ? '' : 'checked'; ?>>
                        <label class="form-check-label" for="ativo">Usuário Ativo</label>
                    </div>
                </div>
            </div>
            
            <?php if ($usuario_para_editar): ?>
                <button type="submit" name="atualizar" class="btn btn-warning">Atualizar</button>
                <a href="usuarios.php" class="btn btn-secondary">Cancelar Edição</a>
            <?php else: ?>
                <button type="submit" name="cadastrar" class="btn btn-primary">Salvar</button>
            <?php endif; ?>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const papelSelect = document.getElementById('papel_id');
    const campoEmpresa = document.getElementById('campoEmpresa');
    const empresaSelect = document.getElementById('empresa_id');

    function toggleEmpresaField() {
        // O ID para o papel "Empresa" é 3, conforme inserimos no banco
        if (papelSelect.value == '3') {
            campoEmpresa.style.display = 'block';
            empresaSelect.required = true;
        } else {
            campoEmpresa.style.display = 'none';
            empresaSelect.required = false;
        }
    }

    // Executa a função quando a página carrega
    toggleEmpresaField();

    // Executa a função toda vez que o papel for alterado
    papelSelect.addEventListener('change', toggleEmpresaField);
});
</script>
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