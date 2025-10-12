<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KlaimExport implements FromView
{
    protected $flattened;
    protected $allKeys;

    public function __construct($flattened, $allKeys)
    {
        $this->flattened = $flattened;
        $this->allKeys   = $allKeys;
    }

    public function view(): View
    {
        return view('klaim.export-excel', [
            'flattened' => $this->flattened,
            'allKeys'   => $this->allKeys,
        ]);
    }
}
