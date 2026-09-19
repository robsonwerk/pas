<?php
// 1. Proteção: Só Admins acessam
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require_once 'includes/header.php';

if ($_SESSION['usuario_papel'] !== 'Administrador') {
    header("Location: index.php");
    exit;
}

// 2. Busca os logs unindo com a tabela de usuários para saber QUEM fez a ação
$sql = "SELECT l.*, u.nome as usuario_nome 
        FROM logs l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id 
        ORDER BY l.data_hora DESC 
        LIMIT 100"; // Limitamos aos últimos 100 para não pesar
$resultado = mysqli_query($conexao, $sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-journal-text"></i> Logs de Atividades</h2>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload();">
        <i class="bi bi-arrow-clockwise"></i> Atualizar
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Tabela</th>
                        <th>ID Reg.</th>
                        <th>Endereço IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td class="small"><?php echo date('d/m/Y H:i:s', strtotime($log['data_hora'])); ?></td>
                        <td>
                            <span class="badge bg-info text-dark">
                                <?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($log['acao']); ?></td>
                        <td><code class="text-primary"><?php echo htmlspecialchars($log['tabela_afetada'] ?? '-'); ?></code></td>
                        <td><?php echo $log['registro_id'] ?? '-'; ?></td>
                        <td class="text-muted small"><?php echo $log['ip_address']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($resultado) == 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Nenhum log registrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
require_once 'includes/footer.php';
mysqli_close($conexao);
?>