<?php
namespace App\Exports;

use JsonMachine\Items;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TindakanExport implements FromGenerator, WithHeadings
{
    protected $filePath;
    protected $headings;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        // Baca HANYA satu baris untuk deteksi headings
        $jsonStream = Items::fromFile($filePath);
        $first      = [];
        foreach ($jsonStream as $row) {
            $first = (array) $row;
            break; // stop setelah 1 baris
        }

        $this->headings = array_keys($first);
    }

    public function generator(): \Generator
    {
        foreach (Items::fromFile($this->filePath) as $row) {
            yield array_values((array) $row);
        }
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
