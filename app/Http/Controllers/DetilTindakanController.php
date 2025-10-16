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

        // dd($noRawats);

        // dd($get_detil);
        $get_detil = [];
        $allKeys   = [];
        if (! empty($get_detil)) {
            $firstRow = (array) $get_detil[0];
            $allKeys  = array_keys($firstRow);
        }
        $allKeys = array_unique($allKeys);

        return view('keuangan.detil-tindakan.index', [
            'flattened'    => $get_detil,
            'allKeys'      => $allKeys,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'jaminan'      => $jaminan,
            'status_bayar' => $status_bayar,
            'jnsPelayanan' => $jnsPelayanan,
        ]);
    }
    public function loadData(Request $request)
    {
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
            $sql .= " AND reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli != 'IGDK'";
        } elseif ($jnsPelayanan == 3) {
            $sql .= " AND reg_periksa.status_lanjut = 'Ralan' AND reg_periksa.kd_poli = 'IGDK'";
        }
        // $sql .= "  LIMIT $limit OFFSET $offset";
        if ($jaminan == 'bpjs') {
            $jp      = $jnsPelayanan == 3 ? 2 : $jnsPelayanan;
            $periode = new \DatePeriod(
                new \DateTime($tanggalAwal),
                new \DateInterval('P1D'),
                (new \DateTime($tanggalAkhir))->modify('+1 day')
            );

            $allKlaim    = [];
            $httpcode    = 200;
            $statusKlaim = 3;
            foreach ($periode as $dt) {
                $tgl = $dt->format('Y-m-d');
                $url = "$this->baseUrl/Monitoring/Klaim/Tanggal/$tgl/JnsPelayanan/$jp/Status/$statusKlaim";

                $result   = get_ws_bpjs($url);
                $response = $result['response'];
                $key      = $result['key'];
                $httpcode = $response->status();

                if ($httpcode == 200 && $response->body()) {
                    $dec = $response->json();
                    if (! empty($dec['response'])) {
                        $hasilRes = lz_decompress(string_decrypt($key, $dec['response']));
                        $datsaAp  = json_decode($hasilRes, true);
                        // dump($datsaAp);
                        if (! empty($datsaAp['klaim'])) {
                            if ($jnsPelayanan == 3) {
                                $filtered = array_filter($datsaAp['klaim'], function ($item) {
                                    return isset($item['poli']) && $item['poli'] === 'INSTALASI GAWAT DARURAT';
                                });

                                // gabungkan ke allKlaim
                                $allKlaim = array_merge($allKlaim, $filtered);
                            } else if ($jnsPelayanan == 2) {
                                $filtered = array_filter($datsaAp['klaim'], function ($item) {
                                    return isset($item['poli']) && $item['poli'] !== 'INSTALASI GAWAT DARURAT';
                                });

                                // gabungkan ke allKlaim
                                $allKlaim = array_merge($allKlaim, $filtered);
                            } else {
                                $allKlaim = array_merge($allKlaim, $datsaAp['klaim']);
                            }
                        }
                    }
                }
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
            $noRawats = [];
            $data     = [];
            foreach ($allKlaim as $data_bpjs) {

                if ($jnsPelayanan == 1) {
                    $get_sep = get_sep_bpjs($data_bpjs['noSEP']);
                    $kd_dpjp = $get_sep['kontrol']['kdDokter'] ?? '';
                    $nm_dpjp = $get_sep['kontrol']['nmDokter'] ?? '';
                    // dd($get_sep);
                } else {
                    $get_sep = get_sep_bpjs($data_bpjs['noSEP']);
                    $kd_dpjp = $get_sep['dpjp']['kdDPJP'] ?? '';
                    $nm_dpjp = $get_sep['dpjp']['nmDPJP'] ?? '';
                }
                $bSep = DB::select($sql_bsep, [$data_bpjs['noSEP']]);
                if ($bSep == null) {
                    $_data = DB::select($sql_reg, [$data_bpjs['peserta']['noKartu'], $data_bpjs['tglSep']]);
                    if (count($_data) == 0) {
                        $no_rawat = null;
                    } else {
                        $noRawats[]        = $_data[0]->no_rawat;
                        $row               = new \stdClass();
                        $row->no_rawat     = $_data[0]->no_rawat;
                        $row->no_rkm_medis = $_data[0]->no_rkm_medis;
                        $row->nm_pasien    = $_data[0]->nm_pasien;
                        $row->layanan_asal = $_data[0]->layanan_asal;
                        $row->jaminan      = $_data[0]->jaminan;
                        $row->kd_dokter    = $kd_dpjp;
                        $row->nm_dokter    = $nm_dpjp;

                        $data[] = $row;
                    }
                } else {
                    $noRawats[]        = $bSep[0]->no_rawat;
                    $row               = new \stdClass();
                    $row->no_rawat     = $bSep[0]->no_rawat;
                    $row->no_rkm_medis = $bSep[0]->no_rkm_medis;
                    $row->nm_pasien    = $bSep[0]->nm_pasien;
                    $row->layanan_asal = $bSep[0]->layanan_asal;
                    $row->jaminan      = $bSep[0]->jaminan;
                    $row->kd_dokter    = $kd_dpjp;
                    $row->nm_dokter    = $nm_dpjp;

                    $data[] = $row;
                }
            }
            // dd($data);
            $get_detil = get_data_detil_tindakan($data);
        } else {
            $data = DB::select($sql, $params);

            $get_detil = get_data_detil_tindakan($data);
        }

        return response()->json([
            'data'   => $get_detil,
            'offset' => $offset + $limit,
            'done'   => count($data) < $limit,
        ]);
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
