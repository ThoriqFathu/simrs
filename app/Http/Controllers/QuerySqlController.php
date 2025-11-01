<?php
namespace App\Http\Controllers;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;

class QuerySqlController extends Controller
{
    public function index(Request $request)
    {
        $results = collect(); // default kosong
        $sql     = $request->input('sql');
        // dump($sql);

        if ($sql) {
            try {
                // Bersihkan spasi & ubah ke huruf besar buat dicek
                $trimmedSql = trim($sql);
                $upperSql   = strtoupper($trimmedSql);

                // 🚫 Blokir semua query selain SELECT
                if (! str_starts_with($upperSql, 'SELECT')) {
                    return back()->withErrors([
                        'sql' => '❌ Hanya perintah SELECT yang diperbolehkan.',
                    ]);
                }
                // jalankan query mentah
                // $data = DB::select(DB::raw($sql));
                $data = DB::select($sql);
                // dd($data);
                $results = collect($data);
                $results = collect($data)->map(function ($row) {
                    return collect((array) $row)->map(function ($value) {
                        return $value instanceof \Illuminate\Database\Query\Expression
                            ? (string) $value->getValue(DB::connection()->getQueryGrammar())
                            : (is_array($value) || is_object($value)
                                ? json_encode($value, JSON_UNESCAPED_UNICODE)
                                : $value);
                    })->toArray(); // ⬅️ ini penting banget
                });

            } catch (\Throwable $e) {
                return back()->withErrors(['sql' => $e->getMessage()]);
            }
        }
        // dd(json_decode($results));
        return view('sql.index', compact('results', 'sql'));
    }

    public function export(Request $request)
    {
        $sql = $request->input('sql');

        if (! $sql) {
            return back()->withErrors(['sql' => 'SQL tidak boleh kosong']);
        }

        try {
            $trimmedSql = trim($sql);
            $upperSql   = strtoupper($trimmedSql);

            // 🚫 Hanya boleh SELECT
            if (! str_starts_with($upperSql, 'SELECT')) {
                return back()->withErrors([
                    'sql' => '❌ Hanya perintah SELECT yang diperbolehkan.',
                ]);
            }

            // Jalankan query
            $data = DB::select($sql);
            if (empty($data)) {
                return back()->withErrors(['sql' => 'Data tidak ditemukan.']);
            }
            dd($data);
            // ✅ Konversi hasil query ke array aman
            $results = collect($data)->map(function ($row) {
                return collect((array) $row)->map(function ($value) {
                    // Jika null
                    if (is_null($value)) {
                        return '';
                    }

                    // Jika Expression (jarang tapi kita tangani)
                    if ($value instanceof \Illuminate\Database\Query\Expression) {
                        return (string) $value->getValue(DB::connection()->getQueryGrammar());
                    }

                    // Jika array atau object → jadikan JSON string
                    if (is_array($value) || is_object($value)) {
                        return json_encode($value, JSON_UNESCAPED_UNICODE);
                    }

                    // Pastikan semua dikonversi jadi string aman
                    return (string) $value;
                })->values()->toArray(); // values() reset key agar urutan rapi
            });

            // ✅ Siapkan generator efisien
            $generator = (function () use ($results) {
                foreach ($results as $row) {
                    yield $row;
                }
            })();

            $items = Items::fromIterable($generator);

            // ✅ Spout writer
            $writer   = WriterEntityFactory::createXLSXWriter();
            $fileName = 'export_' . date('Ymd_His') . '.xlsx';
            $writer->openToBrowser($fileName);

            // ✅ Header dan data
            $first = true;
            foreach ($items as $item) {
                if ($first) {
                    // Header dari array keys row pertama
                    $writer->addRow(
                        WriterEntityFactory::createRowFromArray(array_keys((array) $data[0]))
                    );
                    $first = false;
                }
                // Pastikan tiap kolom string
                $rowData = array_map(fn($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE), (array) $item);
                $writer->addRow(WriterEntityFactory::createRowFromArray($rowData));
            }

            $writer->close();
            return response()->noContent();

        } catch (\Throwable $e) {
            return back()->withErrors(['sql' => $e->getMessage()]);
        }
    }
}
