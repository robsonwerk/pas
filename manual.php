<?php
require_once 'includes/verifica_login.php';
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-dark text-white">Sumário</div>
                <div class="list-group list-group-flush">
                    <a href="#fluxo" class="list-group-item list-group-item-action">Fluxo de Trabalho</a>
                    <a href="#empresas" class="list-group-item list-group-item-action">Gestão de Empresas</a>
                    <a href="#visitas" class="list-group-item list-group-item-action">Agendamento de Visitas</a>
                    <a href="#checklist" class="list-group-item list-group-item-action">Preenchimento de Checklist</a>
                    <a href="#planos" class="list-group-item list-group-item-action">Planos de Ação</a>
                    <a href="#dashboards" class="list-group-item list-group-item-action">Gráficos e Relatórios</a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <h1 class="mb-4">Manual do Usuário - Sistema Alimento Seguro</h1>

            <section id="fluxo" class="mb-5">
                <h3 class="text-primary border-bottom pb-2">1. Fluxo Operacional Padrão</h3>
                <p>Para garantir a integridade dos dados, o sistema segue esta ordem de execução:</p>
                
                <ol>
                    <li><strong>Cadastrar Empresa:</strong> Registrar o cliente.</li>
                    <li><strong>Agendar Visita:</strong> Definir data e setor da inspeção.</li>
                    <li><strong>Checklist:</strong> Realizar a inspeção técnica no local.</li>
                    <li><strong>Plano de Ação:</strong> Corrigir as Não Conformidades encontradas.</li>
                    <li><strong>Relatórios:</strong> Analisar indicadores de desempenho.</li>
                </ol>
            </section>

            <section id="empresas" class="mb-5">
                <h3 class="text-primary border-bottom pb-2">2. Gestão de Empresas</h3>
                <p>No menu <strong>Empresas</strong>, você gerencia os dados dos clientes.</p>
                <ul>
                    <li><strong>Cadastro:</strong> É obrigatório o CNPJ (com máscara automática), e-mail e telefone.</li>
                    <li><strong>Edição:</strong> Use o botão "Editar" para atualizar contatos ou endereços.</li>
                    <li><strong>Rastreabilidade:</strong> Toda exclusão ou cadastro gera um log com o ID da empresa para auditoria.</li>
                </ul>
            </section>

            <section id="visitas" class="mb-5">
                <h3 class="text-primary border-bottom pb-2">3. Agendamento de Visitas</h3>
                <p>As visitas são o "gatilho" para o checklist. Sem uma visita agendada, não é possível iniciar a inspeção.</p>
                <ul>
                    <li><strong>Setor:</strong> Especifique se a visita é na Cozinha, Estoque, Produção, etc.</li>
                    <li><strong>Dashboard Principal:</strong> As próximas 5 visitas agendadas aparecem automaticamente na tela inicial para facilitar seu controle.</li>
                </ul>
            </section>

            <section id="checklist" class="mb-5">
                <h3 class="text-primary border-bottom pb-2">4. Preenchimento de Checklist</h3>
                <p>Esta é a ferramenta de campo. O sistema divide os requisitos por <strong>Etapas</strong> (ex: Edificação, Higiene, Manipuladores).</p>
                <div class="alert alert-info">
                    <strong>Regra de Ouro:</strong> Toda resposta marcada como <strong>"Não Conforme"</strong> exige obrigatoriamente uma descrição detalhada da falha.
                </div>
                <ul>
                    <li><strong>Cores Dinâmicas:</strong> Azul (Conforme), Vermelho (Não Conforme) e Amarelo (N/A).</li>
                    <li><strong>Salvamento:</strong> Você pode salvar por etapas e continuar depois. O sistema mantém o histórico das respostas.</li>
                </ul>
            </section>

            <section id="planos" class="mb-5">
                <h3 class="text-primary border-bottom pb-2">5. Plano de Ação (PGRS)</h3>
                <p>Quando uma falha é detectada, o sistema gera uma pendência no menu de Planos de Ação.</p>
                <ul>
                    <li><strong>Urgência:</strong> No Dashboard, os planos são listados por prazo de vencimento.</li>
                    <li><strong>Status "Vencido":</strong> O sistema destaca automaticamente em vermelho se o prazo para correção expirou.</li>
                    <li><strong>Responsável:</strong> Defina quem é o encarregado pela correção daquela irregularidade específica.</li>
                </ul>
            </section>

            <section id="dashboards" class="mb-5">
                <h3 class="text-primary border-bottom pb-2">6. Gráficos e Relatórios</h3>
                <p>A análise visual é feita através de dois gráficos principais:</p>
                
                <ol>
                    <li><strong>Gráfico de Rosca:</strong> Mostra a porcentagem geral de conformidade da unidade visitada.</li>
                    <li><strong>Treemap:</strong> Mostra quais áreas (Etapas) possuem o maior volume de requisitos avaliados. Blocos maiores indicam maior concentração de dados.</li>
                </ol>
                <p>Para visualizar, selecione a <strong>Visita</strong> específica no topo da página de Gráficos.</p>
            </section>

            <section class="mt-5 p-3 bg-light border-start border-warning border-4">
                <h5><i class="bi bi-shield-check"></i> Segurança e Auditoria</h5>
                <p class="small mb-0">
                    Todas as suas ações (Login, Cadastro, Exclusão, Checklist) são registradas no banco de <strong>Logs</strong>, incluindo seu endereço IP e horário, garantindo que o sistema seja auditável e seguro.
                </p>
            </section>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>