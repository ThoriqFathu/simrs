<?php
namespace App\Http\Controllers;

use App\Exports\KlaimExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KlaimController extends Controller
{
    protected $baseUrl;
    protected $data;
    protected $secretKey;

    public function __construct()
    {
        // ambil dari env atau config
        $this->baseUrl   = env("BPJS_BASE_URL_VCLAIM_REST");
        $this->data      = env("BPJS_CONS_ID");
        $this->secretKey = env("BPJS_SECRET_KEY");
    }

    public function index(Request $request)
    {
        ini_set('max_execution_time', 500);

        // ambil parameter form
        $tanggalAwal  = $request->get('tanggal_awal', date('Y-m-d'));
        $tanggalAkhir = $request->get('tanggal_akhir', date('Y-m-d'));
        $jnsPelayanan = $request->get('jns', 1);
        $statusKlaim  = $request->get('status', 1);

        $periode = new \DatePeriod(
            new \DateTime($tanggalAwal),
            new \DateInterval('P1D'),
            (new \DateTime($tanggalAkhir))->modify('+1 day')
        );

        $allKlaim = [];
        $httpcode = 200;

        foreach ($periode as $dt) {
            $tgl = $dt->format('Y-m-d');
            $url = "$this->baseUrl/Monitoring/Klaim/Tanggal/$tgl/JnsPelayanan/$jnsPelayanan/Status/$statusKlaim";

            $result   = get_ws_bpjs($url);
            $response = $result['response'];
            $key      = $result['key'];
            $httpcode = $response->status();

            if ($httpcode == 200 && $response->body()) {
                $dec = $response->json();
                if (! empty($dec['response'])) {
                    $hasilRes = lz_decompress(string_decrypt($key, $dec['response']));
                    $datsaAp  = json_decode($hasilRes, true);
                    if (! empty($datsaAp['klaim'])) {
                        $allKlaim = array_merge($allKlaim, $datsaAp['klaim']);
                    }
                }
            }
        }

        // flatten array untuk tabel
        $klaims    = $allKlaim;
        $allKeys   = [];
        $flattened = [];
        if (! empty($klaims)) {
            $result = flattened($klaims);

            // ambil masing-masing
            $flattened = $result['data'];
            $allKeys   = $result['allKeys'];
        }
        // dd($flattened);

        $data_merge  = get_data_khanza($flattened, $jnsPelayanan);
        $result      = flattened($data_merge);
        $allKeys     = $result['allKeys'];
        $flattened   = $result['data'];
        $excludeKeys = [
            'peserta.noMR',
            'peserta.noKartu',
            'noFPK',
            'kode_dpjp',
        ];
        $flattened_dot = formatFlattened($result['data'], 'dot', $excludeKeys);

        // dd(($flattened));
        return view('klaim.index', [
            'httpcode'      => $httpcode,
            'flattened_dot' => $flattened_dot,
            'flattened'     => $flattened,
            'allKeys'       => $allKeys,
            'tanggalAwal'   => $tanggalAwal,
            'tanggalAkhir'  => $tanggalAkhir,
            'jnsPelayanan'  => $jnsPelayanan,
            'statusKlaim'   => $statusKlaim,
        ]);
    }

    public function mode_copy(Request $request)
    {
        $excludeKeys = [
            'peserta.noMR',
            'peserta.noKartu',
            'noFPK',
        ];
        // hasilnya array, bukan stdClass
        $data      = json_decode($request->data, true);
        $result    = flattened($data);
        $allKeys   = $result['allKeys'];
        $flattened = formatFlattened($result['data'], 'comma', $excludeKeys);
        // dd($flattened);
        return view('klaim.mode-copy', compact('flattened', 'allKeys'));
    }
    public function exportExcel(Request $request)
    {
        $excludeKeys = [
            'peserta.noMR',
            'peserta.noKartu',
            'noFPK',
        ];
        // hasilnya array, bukan stdClass
        $data      = json_decode($request->data, true);
        $result    = flattened($data);
        $allKeys   = $result['allKeys'];
        $flattened = $result['data'];
        // $flattened = formatFlattened($result['data'], 'comma', $excludeKeys);
        // Hapus dd() agar kode ini jalan
        return Excel::download(new KlaimExport($flattened, $allKeys), 'data.xlsx');
    }

}
