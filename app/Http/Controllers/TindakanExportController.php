<?php
namespace App\Http\Controllers;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use JsonMachine\Items;

class TindakanExportController extends Controller
{
    public function export(Request $request)
    {
        ini_set('max_execution_time', 0);
        $filepath = $request->input('filepath');

        if (! file_exists($filepath)) {
            return redirect()->back()->with('error', 'File JSON tidak ditemukan.');
        }

        $fileName = 'rekap_tindakan_' . now()->format('Ymd_His') . '.xlsx';

        // Stream download XLSX
        return response()->streamDownload(function () use ($filepath, $fileName) {

            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToBrowser($fileName);

            $jsonStream = Items::fromFile($filepath);

            $first = true;
            foreach ($jsonStream as $row) {
                $row = (array) $row;

                // Tulis heading hanya sekali
                if ($first) {
                    $writer->addRow(WriterEntityFactory::createRowFromArray(array_keys($row)));
                    $first = false;
                }

                $writer->addRow(WriterEntityFactory::createRowFromArray(array_values($row)));
            }

            $writer->close();

        }, $fileName);
    }

    public function exportCsv(Request $request)
    {
        $filepath = $request->input('filepath');
        if (! file_exists($filepath)) {
            return redirect()->back()->with('error', 'File JSON tidak ditemukan.');
        }

        $fileName = 'rekap_tindakan_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filepath) {
            $handle = fopen('php://output', 'w');

            $jsonStream = \JsonMachine\Items::fromFile($filepath);
            $first      = true;

            foreach ($jsonStream as $row) {
                $row = (array) $row;
                if ($first) {
                    fputcsv($handle, array_keys($row)); // headings
                    $first = false;
                }
                fputcsv($handle, array_values($row));
            }

            fclose($handle);
        }, $fileName);
    }
}
