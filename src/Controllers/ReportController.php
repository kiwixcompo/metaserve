<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/Report.php';

class ReportController {
    private $reportModel;

    public function __construct() {
        $this->reportModel = new Report();
    }

    // Retrieve consolidated stats for Admin Dashboards
    public function getDashboardStats() {
        return [
            'total_revenue' => $this->reportModel->getTotalRevenue(),
            'active_enrollments' => $this->reportModel->getTotalActiveEnrollments(),
            'programme_stats' => $this->reportModel->getEnrollmentStatsByProgramme()
        ];
    }

    // Native PHP CSV Export Logic
    public function exportToCSV() {
        $data = $this->reportModel->getEnrollmentStatsByProgramme();
        $filename = "metaserve_enrollment_report_" . date('Y-m-d') . ".csv";
        
        // Set Headers to force download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        // Output CSV Column Headers
        fputcsv($output, ['Programme Name', 'Active Students', 'Total Revenue (NGN)']);
        
        // Output Data Rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row['programme_name'],
                $row['student_count'],
                number_format((float)$row['revenue'], 2, '.', '') // Format currency for CSV
            ]);
        }
        
        fclose($output);
        exit(); // Stop execution to ensure clean CSV output
    }

    // PDF Export Logic (Demonstration utilizing FPDF)
    public function exportToPDF() {
        /**
         * REQUIREMENT: To utilize this, the system needs the FPDF library.
         * The administrator can install it via composer:
         * 1. Run: composer require setasign/fpdf
         * 2. Uncomment the code block below.
         */
        
        /*
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $data = $this->reportModel->getEnrollmentStatsByProgramme();

        $pdf = new \FPDF();
        $pdf->AddPage();
        
        // Header Text
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 10, 'Metaserve Digital Skills - Enrollment & Revenue Report', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Table Headers
        $pdf->SetFont('Arial', 'B', 12);
        // RGB Colors for the header (Dark Green matching the theme)
        $pdf->SetFillColor(30, 86, 49); 
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 10, 'Programme Name', 1, 0, 'L', true);
        $pdf->Cell(40, 10, 'Active Students', 1, 0, 'C', true);
        $pdf->Cell(60, 10, 'Revenue (NGN)', 1, 0, 'R', true);
        $pdf->Ln();
        
        // Table Data
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Reset text to black
        foreach ($data as $row) {
            $pdf->Cell(90, 10, $row['programme_name'], 1);
            $pdf->Cell(40, 10, $row['student_count'], 1, 0, 'C');
            $pdf->Cell(60, 10, number_format((float)$row['revenue'], 2), 1, 0, 'R');
            $pdf->Ln();
        }
        
        // Output the generated PDF to the browser
        $pdf->Output('D', 'metaserve_report_' . date('Y-m-d') . '.pdf');
        exit();
        */
        
        echo "<h3>PDF Export Module</h3>";
        echo "<p>This feature requires the FPDF library to be installed on the server.</p>";
        echo "<p>Please run <code>composer require setasign/fpdf</code> in the root directory and uncomment the PDF generation block in <strong>src/Controllers/ReportController.php</strong>.</p>";
    }
}

// Minimal Router to handle the Export Action Links (e.g. from the Admin Dashboard)
if (isset($_GET['action'])) {
    $controller = new ReportController();
    if ($_GET['action'] === 'export_csv') {
        $controller->exportToCSV();
    } elseif ($_GET['action'] === 'export_pdf') {
        $controller->exportToPDF();
    }
}
