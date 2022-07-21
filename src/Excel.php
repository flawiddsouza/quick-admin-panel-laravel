<?php

namespace FlawidDSouza\QuickAdminPanelLaravel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Excel
{
    /** @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
    public static function download($spreadsheet, $filename)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    public static function createSpreadsheetFromArray($array)
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($array, NULL, 'A1');

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        return $spreadsheet;
    }
}
