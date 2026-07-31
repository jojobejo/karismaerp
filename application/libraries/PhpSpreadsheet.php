<?php

if (file_exists(FCPATH . 'vendor/autoload.php')) {
    require_once FCPATH . 'vendor/autoload.php';
} else if (file_exists(APPPATH . 'third_party/PhpSpreadsheet/src/Bootstrap.php')) {
    require_once APPPATH . 'third_party/PhpSpreadsheet/src/Bootstrap.php';
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PhpSpreadsheetLib
{
    public function spreadsheet()
    {
        return new Spreadsheet();
    }

    public function writer($spreadsheet)
    {
        return new Xlsx($spreadsheet);
    }
}
