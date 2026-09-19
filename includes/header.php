<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alimento Seguro - APPCC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="empresas.php">
        <img src="img/logo.png" alt="Logotipo Alimento Seguro" style="height: 40px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="empresas.php">Empresas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="visitas.php">Visitas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="manual.php">Manual</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownChecklist" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Checklist
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownChecklist">
            <li><a class="dropdown-item" href="etapas.php">Gerenciar Etapas</a></li>
            <li><a class="dropdown-item" href="requisitos.php">Gerenciar Requisitos</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="checklist.php">Preencher Checklist</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPlanoAcao" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Plano de Ação
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownPlanoAcao">
            <li><a class="dropdown-item" href="lista_visitas_plano_acao.php">Criar Plano de Ação</a></li>
            <li><a class="dropdown-item" href="lista_planos_acao.php">Acompanhar Planos de Ação</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownRelatorios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Relatórios
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownRelatorios">
            <li><a class="dropdown-item" href="relatorios.php">Relatórios de Checklist</a></li>
            <li><a class="dropdown-item" href="lista_planos_acao.php">Acompanhar Planos de Ação</a></li>
            <li><a class="dropdown-item" href="graficos.php">Gráfico de Conformidade</a></li>
          </ul>
        </li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if ($_SESSION['usuario_papel'] == 'Administrador'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Administração
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
              <li><a class="dropdown-item" href="usuarios.php">Gerenciar Usuários</a></li>
            </ul>
          </li>
        <?php endif; ?>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
               
                <li><a class="dropdown-item" href="logout.php">Sair</a></li>
            </ul>
        </li>
      </ul>

    </div>
  </div>
</nav>

<main class="container mt-5">