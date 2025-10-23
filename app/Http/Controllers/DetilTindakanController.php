<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DetilTindakanController extends Controller
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
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '1024M');
        // ambil parameter form
        $tanggalAwal  = $request->get('tanggal_awal', "2025-08-01");
        $tanggalAkhir = $request->get('tanggal_akhir', "2025-08-01");
        $jnsPelayanan = $request->get('jns', 1);
        $jaminan      = $request->get('jaminan', 'bpjs');
        $status_bayar = $request->get('status_bayar', "Sudah Bayar");

        $get_detil['total'] = 0;
        $get_detil['file']  = 0;
        // dd('');
        $sql = "
            SELECT
                pasien.nm_pasien,
                reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                COALESCE(dokter_dpjp.kd_dokter, dokter_reg.kd_dokter) AS kd_dokter,
                COALESCE(dokter_dpjp.nm_dokter, dokter_reg.nm_dokter) AS nm_dokter,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
            FROM reg_periksa
            INNER JOIN dokter AS dokter_reg
                ON reg_periksa.kd_dokter = dokter_reg.kd_dokter
            LEFT JOIN (
                SELECT no_rawat, MIN(kd_dokter) AS kd_dokter
                FROM dpjp_ranap
                GROUP BY no_rawat
            ) AS dpjp_pertama
                ON dpjp_pertama.no_rawat = reg_periksa.no_rawat
            LEFT JOIN dokter AS dokter_dpjp
                ON dpjp_pertama.kd_dokter = dokter_dpjp.kd_dokter
            INNER JOIN penjab
                ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN poliklinik
                ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien
                ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE reg_periksa.status_bayar = ?
            AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
        ";

        $params = [$status_bayar, $tanggalAwal, $tanggalAkhir];

        // Filter jaminan
        if ($jaminan === 'umum') {
            $sql .= " AND reg_periksa.kd_pj = ?";
            $params[] = 'A09';
        } elseif ($jaminan === 'bpjs') {
            $sql .= " AND reg_periksa.kd_pj = ?";
            $params[] = 'BPJ';
        } else {
            $sql .= " AND reg_periksa.kd_pj != ? AND reg_periksa.kd_pj != ?";
            $params[] = 'A09';
            $params[] = 'BPJ';
        }

        // Filter jenis pelayanan
        if ($jnsPelayanan == 1) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ranap'";
        } elseif ($jnsPelayanan == 2) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ralan'";
        } elseif ($jnsPelayanan == 3) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ranap' AND reg_periksa.kd_poli = 'IGDK'";
        }
        // $sql .= "  LIMIT $limit OFFSET $offset";
        if ($jaminan == 'bpjs') {
            if ($jnsPelayanan != 4) {
                $jp      = $jnsPelayanan == 3 ? 1 : $jnsPelayanan;
                $periode = new \DatePeriod(
                    new \DateTime($tanggalAwal),
                    new \DateInterval('P1D'),
                    (new \DateTime($tanggalAkhir))->modify('+1 day')
                );

                $allKlaim    = [];
                $httpcode    = 200;
                $statusKlaim = 3;

// Direktori cache utama
                $cacheDir = storage_path('app/cache_bpjs');
                if (! file_exists($cacheDir)) {
                    mkdir($cacheDir, 0777, true);
                }

                foreach ($periode as $dt) {
                    $tgl       = $dt->format('Y-m-d');
                    $url       = "$this->baseUrl/Monitoring/Klaim/Tanggal/$tgl/JnsPelayanan/$jp/Status/$statusKlaim";
                    $cacheFile = $cacheDir . '/' . md5($url) . '.json';
                    $cacheTime = 6 * 3600; // cache 6 jam

                    $dataKlaim = [];

                    // 🔹 Cek apakah file cache masih valid
                    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
                        $dataKlaim = json_decode(file_get_contents($cacheFile), true);
                        if (! is_array($dataKlaim)) {
                            $dataKlaim = [];
                        }

                        // logger("Cache hit $tgl");
                    } else {
                        // 🔹 Hit API baru
                        $result   = get_ws_bpjs($url);
                        $response = $result['response'] ?? null;
                        $key      = $result['key'] ?? '';

                        if ($response && method_exists($response, 'status')) {
                            $httpcode = $response->status();

                            if ($httpcode == 200 && $response->body()) {
                                $dec = $response->json();

                                if (! empty($dec['response'])) {
                                    $hasilRes  = @lz_decompress(string_decrypt($key, $dec['response']));
                                    $dataAp    = json_decode($hasilRes, true);
                                    $dataKlaim = $dataAp['klaim'] ?? [];
                                }
                            }
                        }

                        // 🔹 Simpan hasil baru ke cache
                        file_put_contents(
                            $cacheFile,
                            json_encode($dataKlaim, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                        );
                    }

                    // 🔹 Jika hasil kosong, skip biar tidak loop terus
                    if (empty($dataKlaim)) {
                        continue;
                    }

                    // 🔹 Proses langsung data klaim hari itu
                    foreach ($dataKlaim as $data_bpjs) {
                        $noSEP = $data_bpjs['noSEP'] ?? null;
                        if (empty($noSEP)) {
                            continue;
                        }

                        // === Cache per SEP ===
                        $cacheDirSep = storage_path('app/cache_sep');
                        if (! file_exists($cacheDirSep)) {
                            mkdir($cacheDirSep, 0777, true);
                        }

                        $cacheFileSep = $cacheDirSep . '/' . $noSEP . '.json';
                        $cacheTimeSep = 24 * 3600;

                        if (file_exists($cacheFileSep) && (time() - filemtime($cacheFileSep)) < $cacheTimeSep) {
                            $get_sep = json_decode(file_get_contents($cacheFileSep), true);
                        } else {
                            $get_sep = get_sep_bpjs($noSEP);
                            file_put_contents($cacheFileSep, json_encode($get_sep, JSON_PRETTY_PRINT));
                        }

                        // === Tentukan DPJP ===
                        if ($jnsPelayanan == 1 || $jnsPelayanan == 3) {
                            $kd_dpjp = $get_sep['kontrol']['kdDokter'] ?? '';
                            $nm_dpjp = $get_sep['kontrol']['nmDokter'] ?? '';
                        } else {
                            $kd_dpjp = $get_sep['dpjp']['kdDPJP'] ?? '';
                            $nm_dpjp = $get_sep['dpjp']['nmDPJP'] ?? '';
                        }
                        $sql_reg = "
            SELECT
                pasien.nm_pasien,
                reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
            FROM reg_periksa
            INNER JOIN penjab
                ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN poliklinik
                ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien
                ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE pasien.no_peserta = ? AND reg_periksa.tgl_registrasi = ?
                ";
                        $sql_bsep = "
            SELECT
                pasien.nm_pasien,
                reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
            FROM reg_periksa
            INNER JOIN bridging_sep
                ON reg_periksa.no_rawat = bridging_sep.no_rawat
            INNER JOIN penjab
                ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN poliklinik
                ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien
                ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE bridging_sep.no_sep = ?
                ";
                        // === Ambil data rawat dari DB ===
                        $bSep = DB::select($sql_bsep, [$noSEP]);
                        if (empty($bSep)) {
                            $_data = DB::select($sql_reg, [$data_bpjs['peserta']['noKartu'], $data_bpjs['tglSep']]);
                        } else {
                            $_data = $bSep;
                        }

                        if (empty($_data)) {
                            continue;
                        }

                        // === Filter dan rakit data ===
                        $rowData = $_data[0];
                        if ($jnsPelayanan == 3 && ! in_array($rowData->layanan_asal, ['IGD', 'IGDK'])) {
                            continue;
                        }

                        $row               = new \stdClass();
                        $row->no_rawat     = $rowData->no_rawat;
                        $row->no_rkm_medis = $rowData->no_rkm_medis;
                        $row->nm_pasien    = $rowData->nm_pasien;
                        $row->layanan_asal = $rowData->layanan_asal;
                        $row->jaminan      = $rowData->jaminan;
                        $row->kd_dokter    = $kd_dpjp;
                        $row->nm_dokter    = $nm_dpjp;

                        $allKlaim[] = $row;
                    }

                    // 🔹 Hapus variabel besar tiap iterasi untuk hemat RAM
                    unset($dataKlaim, $dataAp, $hasilRes);
                }

                // dd($data);
                $get_detil = get_data_detil_tindakan($allKlaim, $jnsPelayanan, $tanggalAwal, $tanggalAkhir, $jaminan, $status_bayar);
                // dd(count($get_detil));

            }

        } else {
            $data = DB::select($sql, $params);

            $get_detil = get_data_detil_tindakan($data, $jnsPelayanan, $tanggalAwal, $tanggalAkhir, $jaminan, $status_bayar);
        }

        // dd($noRawats);

        // dd($get_detil['total']);
        // $get_detil = [];
        // $allKeys = [];
        // if (! empty($get_detil)) {
        //     $firstRow = (array) $get_detil[0];
        //     $allKeys  = array_keys($firstRow);
        // }
        // $allKeys = array_unique($allKeys);

        return view('keuangan.detil-tindakan.index', [
            'total'        => $get_detil['total'],
            'filepath'     => $get_detil['file'],
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'jaminan'      => $jaminan,
            'status_bayar' => $status_bayar,
            'jnsPelayanan' => $jnsPelayanan,
        ]);
    }
    public function loadData(Request $request)
    {
        set_time_limit(300); // 5 menit
        ini_set('max_execution_time', 300);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $offset = $request->get('offset', 0);
        $limit  = $request->get('limit', 200); // ambil 200 baris per batch

        $tanggalAwal  = $request->get('tanggal_awal');
        $tanggalAkhir = $request->get('tanggal_akhir');
        $jnsPelayanan = $request->get('jns');
        $jaminan      = $request->get('jaminan');
        $status_bayar = $request->get('status_bayar');

        $sql = "
            SELECT
                pasien.nm_pasien,
                reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                COALESCE(dokter_dpjp.kd_dokter, dokter_reg.kd_dokter) AS kd_dokter,
                COALESCE(dokter_dpjp.nm_dokter, dokter_reg.nm_dokter) AS nm_dokter,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
            FROM reg_periksa
            INNER JOIN dokter AS dokter_reg
                ON reg_periksa.kd_dokter = dokter_reg.kd_dokter
            LEFT JOIN (
                SELECT no_rawat, MIN(kd_dokter) AS kd_dokter
                FROM dpjp_ranap
                GROUP BY no_rawat
            ) AS dpjp_pertama
                ON dpjp_pertama.no_rawat = reg_periksa.no_rawat
            LEFT JOIN dokter AS dokter_dpjp
                ON dpjp_pertama.kd_dokter = dokter_dpjp.kd_dokter
            INNER JOIN penjab
                ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN poliklinik
                ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien
                ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE reg_periksa.status_bayar = ?
            AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
        ";

        $params = [$status_bayar, $tanggalAwal, $tanggalAkhir];

        // Filter jaminan
        if ($jaminan === 'umum') {
            $sql .= " AND reg_periksa.kd_pj = ?";
            $params[] = 'A09';
        } elseif ($jaminan === 'bpjs') {
            $sql .= " AND reg_periksa.kd_pj = ?";
            $params[] = 'BPJ';
        } else {
            $sql .= " AND reg_periksa.kd_pj = ? AND reg_periksa.kd_pj != ?";
            $params[] = 'A09';
            $params[] = 'BPJ';
        }

        // Filter jenis pelayanan
        if ($jnsPelayanan == 1) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ranap'";
        } elseif ($jnsPelayanan == 2) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ralan'";
        } elseif ($jnsPelayanan == 3) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ranap' AND reg_periksa.kd_poli = 'IGDK'";
        }
        // $sql .= "  LIMIT $limit OFFSET $offset";
        if ($jaminan == 'bpjs') {
            $jp      = $jnsPelayanan == 3 ? 1 : $jnsPelayanan;
            $periode = new \DatePeriod(
                new \DateTime($tanggalAwal),
                new \DateInterval('P1D'),
                (new \DateTime($tanggalAkhir))->modify('+1 day')
            );

            $allKlaim    = [];
            $httpcode    = 200;
            $statusKlaim = 3;

// Direktori cache utama
            $cacheDir = storage_path('app/cache_bpjs');
            if (! file_exists($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }

            foreach ($periode as $dt) {
                $tgl       = $dt->format('Y-m-d');
                $url       = "$this->baseUrl/Monitoring/Klaim/Tanggal/$tgl/JnsPelayanan/$jp/Status/$statusKlaim";
                $cacheFile = $cacheDir . '/' . md5($url) . '.json';
                $cacheTime = 6 * 3600; // cache 6 jam

                $dataKlaim = [];

                // 🔹 Cek apakah file cache masih valid
                if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
                    $dataKlaim = json_decode(file_get_contents($cacheFile), true);
                    if (! is_array($dataKlaim)) {
                        $dataKlaim = [];
                    }

                    // logger("Cache hit $tgl");
                } else {
                    // 🔹 Hit API baru
                    $result   = get_ws_bpjs($url);
                    $response = $result['response'] ?? null;
                    $key      = $result['key'] ?? '';

                    if ($response && method_exists($response, 'status')) {
                        $httpcode = $response->status();

                        if ($httpcode == 200 && $response->body()) {
                            $dec = $response->json();

                            if (! empty($dec['response'])) {
                                $hasilRes  = @lz_decompress(string_decrypt($key, $dec['response']));
                                $dataAp    = json_decode($hasilRes, true);
                                $dataKlaim = $dataAp['klaim'] ?? [];
                            }
                        }
                    }

                    // 🔹 Simpan hasil baru ke cache
                    file_put_contents(
                        $cacheFile,
                        json_encode($dataKlaim, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                    );
                }

                // 🔹 Jika hasil kosong, skip biar tidak loop terus
                if (empty($dataKlaim)) {
                    continue;
                }

                // 🔹 Proses langsung data klaim hari itu
                foreach ($dataKlaim as $data_bpjs) {
                    $noSEP = $data_bpjs['noSEP'] ?? null;
                    if (empty($noSEP)) {
                        continue;
                    }

                    // === Cache per SEP ===
                    $cacheDirSep = storage_path('app/cache_sep');
                    if (! file_exists($cacheDirSep)) {
                        mkdir($cacheDirSep, 0777, true);
                    }

                    $cacheFileSep = $cacheDirSep . '/' . $noSEP . '.json';
                    $cacheTimeSep = 24 * 3600;

                    if (file_exists($cacheFileSep) && (time() - filemtime($cacheFileSep)) < $cacheTimeSep) {
                        $get_sep = json_decode(file_get_contents($cacheFileSep), true);
                    } else {
                        $get_sep = get_sep_bpjs($noSEP);
                        file_put_contents($cacheFileSep, json_encode($get_sep, JSON_PRETTY_PRINT));
                    }

                    // === Tentukan DPJP ===
                    if ($jnsPelayanan == 1 || $jnsPelayanan == 3) {
                        $kd_dpjp = $get_sep['kontrol']['kdDokter'] ?? '';
                        $nm_dpjp = $get_sep['kontrol']['nmDokter'] ?? '';
                    } else {
                        $kd_dpjp = $get_sep['dpjp']['kdDPJP'] ?? '';
                        $nm_dpjp = $get_sep['dpjp']['nmDPJP'] ?? '';
                    }
                    $sql_reg = "
            SELECT
                pasien.nm_pasien,
                reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
            FROM reg_periksa
            INNER JOIN penjab
                ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN poliklinik
                ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien
                ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE pasien.no_peserta = ? AND reg_periksa.tgl_registrasi = ?
                ";
                    $sql_bsep = "
            SELECT
                pasien.nm_pasien,
                reg_periksa.no_rawat,
                reg_periksa.no_rkm_medis,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
            FROM reg_periksa
            INNER JOIN bridging_sep
                ON reg_periksa.no_rawat = bridging_sep.no_rawat
            INNER JOIN penjab
                ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN poliklinik
                ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien
                ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            WHERE bridging_sep.no_sep = ?
                ";
                    // === Ambil data rawat dari DB ===
                    $bSep = DB::select($sql_bsep, [$noSEP]);
                    if (empty($bSep)) {
                        $_data = DB::select($sql_reg, [$data_bpjs['peserta']['noKartu'], $data_bpjs['tglSep']]);
                    } else {
                        $_data = $bSep;
                    }

                    if (empty($_data)) {
                        continue;
                    }

                    // === Filter dan rakit data ===
                    $rowData = $_data[0];
                    if ($jnsPelayanan == 3 && ! in_array($rowData->layanan_asal, ['IGD', 'IGDK'])) {
                        continue;
                    }

                    $row               = new \stdClass();
                    $row->no_rawat     = $rowData->no_rawat;
                    $row->no_rkm_medis = $rowData->no_rkm_medis;
                    $row->nm_pasien    = $rowData->nm_pasien;
                    $row->layanan_asal = $rowData->layanan_asal;
                    $row->jaminan      = $rowData->jaminan;
                    $row->kd_dokter    = $kd_dpjp;
                    $row->nm_dokter    = $nm_dpjp;

                    $allKlaim[] = $row;
                }

                // 🔹 Hapus variabel besar tiap iterasi untuk hemat RAM
                unset($dataKlaim, $dataAp, $hasilRes);
            }

            // dd($data);
            $get_detil = get_data_detil_tindakan($allKlaim, $jnsPelayanan, $tanggalAwal, $tanggalAkhir, $jaminan, $status_bayar);

        } else {
            $data = DB::select($sql, $params);

            $get_detil = get_data_detil_tindakan($data, $jnsPelayanan, $tanggalAwal, $tanggalAkhir, $jaminan, $status_bayar);
        }
        if ($jaminan == 'bpjs') {
            return response()->json([
                'file'   => $get_detil['file'],  // path file JSON
                'total'  => $get_detil['total'], // total baris
                'offset' => 0,
                'done'   => true,
            ]);
        } else {
            return response()->json([
                'file'   => $get_detil['file'],
                'total'  => $get_detil['total'],
                'offset' => $offset + $limit,
                'done'   => count($data) < $limit,
            ]);
        }

    }

    public function export(Request $request)
    {
        $data = json_decode($request->input('data'), true);

        if (empty($data)) {
            return back()->with('error', 'Data export kosong');
        }

        $fileName = 'Rekap_Tindakan_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
        {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return new Collection($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data[0]);
            }
        }, $fileName);
    }

}
