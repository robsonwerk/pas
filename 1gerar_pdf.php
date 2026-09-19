<?php
require_once 'includes/verifica_login.php';
require_once 'conexao.php';
require('fpdf/fpdf.php'); // Certifique-se de que o caminho para a FPDF está correto

if (!isset($_GET['visita_id']) || empty($_GET['visita_id'])) {
    die("ID da visita não fornecido.");
}

$visita_id = (int)$_GET['visita_id'];

// --- ETAPA 1: BUSCAR DADOS DA VISITA E DA EMPRESA ---
$sql_visita = "SELECT v.data_da_visita, e.nome AS nome_empresa, e.endereco 
               FROM visitas v 
               JOIN empresas e ON v.empresa_id = e.id 
               WHERE v.id = ?";
$stmt_visita = mysqli_prepare($conexao, $sql_visita);
mysqli_stmt_bind_param($stmt_visita, "i", $visita_id);
mysqli_stmt_execute($stmt_visita);
$resultado_visita = mysqli_stmt_get_result($stmt_visita);
$visita = mysqli_fetch_assoc($resultado_visita);

if (!$visita) {
    die("Visita não encontrada.");
}

// --- ETAPA 2: BUSCAR AS NÃO CONFORMIDADES E SEUS ANEXOS ---
$nao_conformidades = [];
$sql_nc = "SELECT 
                cr.id as resposta_id,
                r.numero,
                r.descricao AS requisito_descricao,
                cr.descricao_nao_conformidade
            FROM checklist_respostas cr
            JOIN requisitos r ON cr.requisito_id = r.id
            WHERE cr.conformidade = 'Não Conforme' AND cr.visita_id = ?
            ORDER BY r.numero ASC";

$stmt_nc = mysqli_prepare($conexao, $sql_nc);
mysqli_stmt_bind_param($stmt_nc, "i", $visita_id);
mysqli_stmt_execute($stmt_nc);
$resultado_nc = mysqli_stmt_get_result($stmt_nc);

if ($resultado_nc) {
    while ($row = mysqli_fetch_assoc($resultado_nc)) {
        // Para cada não conformidade, buscamos seus anexos
        $row['anexos'] = [];
        $sql_anexos = "SELECT nome_arquivo, nome_original FROM anexos_evidencias WHERE checklist_resposta_id = ?";
        $stmt_anexos = mysqli_prepare($conexao, $sql_anexos);
        mysqli_stmt_bind_param($stmt_anexos, "i", $row['resposta_id']);
        mysqli_stmt_execute($stmt_anexos);
        $resultado_anexos = mysqli_stmt_get_result($stmt_anexos);
        if ($resultado_anexos) {
            while ($anexo = mysqli_fetch_assoc($resultado_anexos)) {
                $row['anexos'][] = $anexo;
            }
        }
        $nao_conformidades[] = $row;
    }
}


// --- ETAPA 3: GERAR O PDF ---
class PDF extends FPDF
{
    // Cabeçalho
    function Header()
    {
        // Logo
        $this->Image('img/logo.png', 10, 6, 30);
        // Título
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'Relatorio de Vistoria', 0, 0, 'C');
        $this->Ln(20);
    }

    // Rodapé
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Informações da Vistoria
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Empresa:');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($visita['nome_empresa']));
$pdf->Ln(7);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Endereco:');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($visita['endereco']));
$pdf->Ln(7);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Data da Vistoria:');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, date('d/m/Y', strtotime($visita['data_da_visita'])));
$pdf->Ln(15);

// Título da seção de não conformidades
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Nao Conformidades Encontradas', 0, 1, 'C');
$pdf->Ln(5);

// Lista de Não Conformidades
if (empty($nao_conformidades)) {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Nenhuma nao conformidade encontrada nesta vistoria.', 0, 1);
} else {
    foreach ($nao_conformidades as $nc) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->MultiCell(0, 7, utf8_decode('Requisito ' . $nc['numero'] . ': ' . $nc['requisito_descricao']));
        
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->MultiCell(0, 7, utf8_decode('Evidencia: ' . $nc['descricao_nao_conformidade']));
        $pdf->Ln(3);

        // --- NOVA LÓGICA PARA INSERIR IMAGENS ---
        if (!empty($nc['anexos'])) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 10, 'Evidencias Fotograficas:', 0, 1);
            
            foreach ($nc['anexos'] as $anexo) {
                $caminho_imagem = 'uploads/' . $anexo['nome_arquivo'];
                if (file_exists($caminho_imagem)) {
                    // Adiciona a imagem, definindo uma largura máxima de 80mm
                    // O FPDF ajusta a altura automaticamente para manter a proporção
                    $pdf->Image($caminho_imagem, $pdf->GetX(), $pdf->GetY(), 90);
                    $pdf->Ln(5); // Adiciona um pequeno espaço após cada imagem
                }
            }
        }
        // --- FIM DA NOVA LÓGICA ---
        
        $pdf->Ln(5);
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY()); // Linha separadora
        $pdf->Ln(5);
    }
}

$pdf->Output('D', 'Relatorio_Vistoria_' . $visita_id . '.pdf'); // 'D' para forçar o download

mysqli_close($conexao);
?>