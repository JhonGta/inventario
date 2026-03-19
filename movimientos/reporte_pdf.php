<?php
require_once '../tcpdf-main/tcpdf.php';
require_once '../config/conexion.php';

class CustomPDF extends TCPDF {
    public function Header() {
        // Logo
        $image_file = 'https://consorciobiocity.com/wp-content/uploads/2023/01/logo-biocity.png';
        $this->Image($image_file, 12, 8, 38, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Título
        $this->SetFont('helvetica', 'B', 18);
        $this->SetXY(55, 12);
        $this->Cell(100, 12, 'REPORTE DE INVENTARIO', 0, 0, 'C', false, '', 1, false, 'M', 'M');
        // Ya no se muestra la fecha
        $this->Ln(15);
    }
}

// Totales por estado
$stmt = $pdo->query('SELECT estado, COUNT(*) as total FROM productos_detalle GROUP BY estado');
$totales_estado = [];
while ($row = $stmt->fetch()) {
    $totales_estado[$row['estado']] = $row['total'];
}

// Total de productos entrados
$stmt = $pdo->query('SELECT COUNT(*) as total FROM productos_detalle');
$total_entrados = $stmt->fetchColumn();

// Total en stock (ACTIVO o DEVUELTO)
$total_stock = ($totales_estado['ACTIVO'] ?? 0) + ($totales_estado['DEVUELTO'] ?? 0);

// Total salidos (ENTREGADO)
$total_salidos = $totales_estado['ENTREGADO'] ?? 0;

// Total dados de baja (BAJA)
$total_baja = $totales_estado['BAJA'] ?? 0;

$pdf = new CustomPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Inventario');
$pdf->SetAuthor('Sistema de Inventario');
$pdf->SetTitle('Reporte de Inventario');
$pdf->setHeaderFont(Array('helvetica', 'B', 13));
$pdf->setFooterFont(Array('helvetica', '', 9));
$pdf->SetMargins(8, 32, 8); // margen superior mayor por logo
$pdf->SetHeaderMargin(8);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();
$pdf->Ln(8);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetFillColor(210, 240, 210); // Verde claro institucional
$pdf->SetTextColor(0, 80, 0); // Verde oscuro para texto
$pdf->Cell(80, 8, 'Total productos entrados:', 0, 0, 'L', true);
$pdf->Cell(20, 8, $total_entrados, 0, 1, 'L', true);
$pdf->Cell(80, 8, 'Total productos en stock:', 0, 0, 'L', true);
$pdf->Cell(20, 8, $total_stock, 0, 1, 'L', true);
$pdf->Cell(80, 8, 'Total productos salidos:', 0, 0, 'L', true);
$pdf->Cell(20, 8, $total_salidos, 0, 1, 'L', true);
$pdf->Cell(80, 8, 'Total productos dados de baja:', 0, 0, 'L', true);
$pdf->Cell(20, 8, $total_baja, 0, 1, 'L', true);
$pdf->Ln(10);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(60, 180, 75); // Verde medio fuerte para encabezado
$pdf->SetTextColor(255,255,255); // Texto blanco en encabezado
$w = [30, 22, 28, 32, 28, 28, 32];
$pdf->Cell($w[0], 9, 'Producto', 1, 0, 'L', true);
$pdf->Cell($w[1], 9, 'Marca', 1, 0, 'L', true);
$pdf->Cell($w[2], 9, 'Serie', 1, 0, 'L', true);
$pdf->Cell($w[3], 9, 'Estado', 1, 0, 'L', true);
$pdf->Cell($w[4], 9, 'Responsable', 1, 0, 'L', true);
$pdf->Cell($w[5], 9, 'Ubicación', 1, 0, 'L', true);
$pdf->Cell($w[6], 9, 'Observación', 1, 1, 'L', true);
$pdf->SetFont('helvetica', '', 10); // Un solo tamaño de letra para toda la tabla
$pdf->SetTextColor(0,0,0); // Texto negro para filas

$stmt = $pdo->query('SELECT pd.*, p.nombre AS producto FROM productos_detalle pd JOIN productos p ON pd.id_producto = p.id_producto');
$fill = false;
while ($row = $stmt->fetch()) {
    $stmt_mov = $pdo->prepare('SELECT m.responsable, u.nombre_ubicacion, m.observacion FROM movimiento_detalle md JOIN movimientos m ON md.id_movimiento = m.id_movimiento LEFT JOIN ubicaciones u ON m.id_ubicacion = u.id_ubicacion WHERE md.id_detalle = ? ORDER BY m.fecha DESC LIMIT 1');
    $stmt_mov->execute([$row['id_detalle']]);
    $mov = $stmt_mov->fetch();
    $responsable = $mov['responsable'] ?? '-';
    $ubicacion = $mov['nombre_ubicacion'] ?? '-';
    $observacion = $mov['observacion'] ?? '-';

    $data = [
        $row['producto'],
        $row['marca'],
        $row['serie'],
        $row['estado'],
        $responsable,
        $ubicacion,
        $observacion
    ];
    $cellHeights = [];
    for ($i = 0; $i < count($w); $i++) {
        $cellHeights[] = $pdf->getStringHeight($w[$i], $data[$i]);
    }
    $maxHeight = max($cellHeights);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    // Verde claro igual que el resumen para filas alternas
    if ($fill) {
        $pdf->SetFillColor(210, 240, 210);
    } else {
        $pdf->SetFillColor(255,255,255);
    }
    for ($i = 0; $i < count($w); $i++) {
        $pdf->MultiCell($w[$i], $maxHeight, $data[$i], 1, 'L', $fill, 0, '', '', true, 0, false, true, $maxHeight, 'M');
        $pdf->SetXY($x + array_sum(array_slice($w, 0, $i + 1)), $y);
    }
    $pdf->SetXY($x, $y + $maxHeight);
    $fill = !$fill;
}
$pdf->Output('reporte_inventario.pdf', 'I');
exit;
