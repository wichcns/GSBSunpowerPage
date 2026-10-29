<?php

namespace App\Crm\Exports;

use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementReportWorkbook
{
    private const HEADERS = ['รหัสสถานที่', 'ชื่อสถานที่', 'พื้นที่', 'ประเภทสถานที่', 'ผู้รับผิดชอบ',
        'ขั้นตอนการขาย', 'ขั้นวิศวกรรม', 'วันหมดอายุสัญญา', 'งานถัดไป', 'กำหนดติดตาม'];

    public function download(array $report, array $filters): StreamedResponse
    {
        $spreadsheet = $this->make($report, $filters);
        $filename = 'gsb-ev-crm-report-'.now('Asia/Bangkok')->format('Y-m-d').'.xlsx';

        return response()->streamDownload(static function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function make(array $report, array $filters): Spreadsheet
    {
        $book = new Spreadsheet;
        $book->getProperties()->setCreator('GSB EV CRM')->setTitle('รายงาน CRM');
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('รายงาน CRM');
        $sheet->mergeCells('A1:J1')->setCellValue('A1', 'รายงาน GSB EV CRM');
        $sheet->mergeCells('A2:J2')->setCellValue('A2', 'สร้างเมื่อ '.now('Asia/Bangkok')->format('d/m/Y H:i'));
        $sheet->mergeCells('A3:J3')->setCellValue('A3', $this->filterSummary($filters));

        foreach (self::HEADERS as $offset => $header) {
            $sheet->setCellValueExplicit([$offset + 1, 5], $header, DataType::TYPE_STRING);
        }

        $rowNumber = 6;
        foreach ($report['rows'] as $row) {
            $values = [$row['site_code'], $row['site_name'], $row['area'], $row['location_type'], $row['owner_name'],
                $row['sales_stage_label'], $row['engineering_stage_label'], null, $row['next_action'], null];
            foreach ($values as $offset => $value) {
                $sheet->setCellValueExplicit([$offset + 1, $rowNumber], (string) ($value ?? ''), DataType::TYPE_STRING);
            }
            $this->setDate($sheet, 'H'.$rowNumber, $row['contract_expiry'], 'dd/mm/yyyy');
            $this->setDate($sheet, 'J'.$rowNumber, $row['next_action_at'], 'dd/mm/yyyy hh:mm');
            $rowNumber++;
        }

        if ($report['rows'] === []) {
            $sheet->setCellValueExplicit('A6', 'ไม่พบข้อมูลตามตัวกรอง', DataType::TYPE_STRING);
            $rowNumber = 7;
        }
        $lastRow = $rowNumber - 1;
        $table = new Table("A5:J{$lastRow}", 'CrmReportTable');
        $style = new TableStyle;
        $style->setTheme(TableStyle::TABLE_STYLE_MEDIUM2);
        $style->setShowRowStripes(true);
        $table->setStyle($style);
        $sheet->addTable($table);

        $sheet->freezePane('A6');
        $sheet->setAutoFilter("A5:J{$lastRow}");
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '073B4C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getStyle('A2:J3')->getFont()->setColor(new Color('64748B'));
        $sheet->getStyle("A5:J{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D7E2EC');
        $sheet->getStyle("A5:J{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("B6:J{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle('A5:J5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');

        foreach (['A' => 17, 'B' => 30, 'C' => 18, 'D' => 20, 'E' => 20, 'F' => 26, 'G' => 22, 'H' => 18, 'I' => 34, 'J' => 21] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4)->setRight(0.3)->setBottom(0.4)->setLeft(0.3);

        return $book;
    }

    private function setDate($sheet, string $cell, mixed $value, string $format): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }
        try {
            $date = CarbonImmutable::parse($value, 'Asia/Bangkok');
            $sheet->setCellValue($cell, Date::PHPToExcel($date));
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode($format);
        } catch (\Throwable) {
            $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
        }
    }

    private function filterSummary(array $filters): string
    {
        $labels = ['owner' => 'ผู้รับผิดชอบ', 'area' => 'พื้นที่', 'location_type' => 'ประเภทสถานที่',
            'sales_stage' => 'ขั้นตอนการขาย', 'engineering_stage' => 'ขั้นวิศวกรรม',
            'date_from' => 'ติดตามตั้งแต่', 'date_to' => 'ถึงวันที่'];
        $active = [];
        foreach ($labels as $key => $label) {
            if (($filters[$key] ?? '') !== '') {
                $active[] = $label.': '.$filters[$key];
            }
        }

        return $active === [] ? 'ตัวกรอง: ทั้งหมด' : 'ตัวกรอง: '.implode(' | ', $active);
    }
}
