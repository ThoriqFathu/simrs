<?php
namespace App\Http\Controllers;

use App\Models\BridgingSep;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AntrolBpjsController extends Controller
{
    protected $BPJS_BASE_URL_VCLAIM_REST;
    protected $BPJS_BASE_URL_ANTREAN_RS;
    protected $data;
    protected $secretKey;

    public function __construct()
    {
        // ambil dari env atau config
        $this->BPJS_BASE_URL_VCLAIM_REST = env("BPJS_BASE_URL_VCLAIM_REST");
        $this->BPJS_BASE_URL_ANTREAN_RS  = env("BPJS_BASE_URL_ANTREAN_RS");
        $this->data                      = env("BPJS_CONS_ID");
        $this->secretKey                 = env("BPJS_SECRET_KEY");
    }
    public function index(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 menit
                                            // ambil dari request, kalau kosong pakai default
        $tanggal_awal    = $request->input('tanggal_awal', now()->format('Y-m-d'));
        $tanggal_akhir   = $request->input('tanggal_akhir', now()->format('Y-m-d'));
        $tanggal_mulai   = Carbon::parse($tanggal_awal);
        $tanggal_selesai = Carbon::parse($tanggal_akhir);

        $data_antrian           = []; // untuk gabungan semua tanggal
        $data_kunjungan_sep     = []; // untuk gabungan semua tanggal
        $data_sep_gagal         = []; // untuk gabungan semua tanggal
        $data_sep_belum_selesai = []; // untuk gabungan semua tanggal
        $countSumberDataSelesai = [
            "MJKN"     => 0,
            "Non_MJKN" => 0,
        ];
        // loop setiap tanggal
        for ($date = $tanggal_mulai->copy(); $date->lte($tanggal_selesai); $date->addDay()) {
            $tanggal = $date->format('Y-m-d');

            // ================ Start data antrian ====================
            $url_antrean = $this->BPJS_BASE_URL_ANTREAN_RS . "/antrean/pendaftaran/tanggal/$tanggal";

            // panggil webservice
            $result   = get_ws_bpjs($url_antrean);
            $response = $result['response'];
            $key      = $result['key'];

            // ambil data antrian per tanggal
            $temp_data_antrian = get_data_decrypt($key, $response);

            // kalau $temp_data_antrian berupa array, gabungkan ke $data_antrian
            if (is_array($temp_data_antrian)) {
                $data_antrian = array_merge($data_antrian, $temp_data_antrian);
            }
            // ================ END DATA ANTRIAN ====================

            // ================ START SEP KUNJUNGAN ====================
            $url_kunjungan_sep = $this->BPJS_BASE_URL_VCLAIM_REST . "/Monitoring/Kunjungan/Tanggal/" . $tanggal . "/JnsPelayanan/2";
            // panggil webservice
            $result   = get_ws_bpjs($url_kunjungan_sep);
            $response = $result['response'];
            $key      = $result['key'];
            // ambil data antrian per tanggal
            // dump(get_data_decrypt($key, $response));
            $temp_data_decrypt = get_data_decrypt($key, $response);
            // pastikan key 'sep' ada
            $temp_data_kunjungan_sep = isset($temp_data_decrypt['sep']) ? $temp_data_decrypt['sep'] : [];
            // filter hanya poli != 'IGD'
            $temp_data_kunjungan_sep = array_filter(
                $temp_data_kunjungan_sep,
                function ($item) {
                    // kalau key poli tidak ada, kita anggap lolos filter
                    return isset($item['poli']) && $item['poli'] !== 'IGD';
                }
            );

            // reindex array supaya indexnya mulai dari 0 lagi
            $temp_data_kunjungan_sep = array_values($temp_data_kunjungan_sep);
            if (is_array($temp_data_kunjungan_sep)) {
                $data_kunjungan_sep = array_merge($data_kunjungan_sep, $temp_data_kunjungan_sep);
            }
            // dd($temp_data_kunjungan_sep);

            // ================ END SEP KUNJUNGAN ====================

            // ================ START STATUS ====================
            $getStatusSep               = get_status_sep($temp_data_antrian, $temp_data_kunjungan_sep);
            $tempSepTidakAdaDiAntrian   = $getStatusSep['sep_tidak_ada_di_antrian'];
            $tempSepBelumSelesai        = $getStatusSep['sep_ada_belum_selesai'];
            $tempCountSumberDataSelesai = $getStatusSep['count_sumberdata_selesai'];

            foreach ($tempCountSumberDataSelesai as $key => $value) {
                $countSumberDataSelesai[$key] = ($countSumberDataSelesai[$key] ?? 0) + $value;
            }

            // kalau $tempSepTidakAdaDiAntrian berupa array, gabungkan ke $data_antrian
            if (is_array($tempSepTidakAdaDiAntrian)) {
                $data_sep_gagal = array_merge($data_sep_gagal, $tempSepTidakAdaDiAntrian);
            }
            if (is_array($tempSepBelumSelesai)) {
                $data_sep_belum_selesai = array_merge($data_sep_belum_selesai, $tempSepBelumSelesai);
            }
            // ================ END STATUS SEP ====================

        }
        $allPoli      = array_column($data_kunjungan_sep, 'poli'); // ambil semua poli
        $countSepPoli = array_count_values($allPoli);
        // dd($countPoli);
        // dd(($total_kunjungan - $total_gagal), $persen_all_antrol);
        return view('monitoring.antrol.index', compact('data_antrian', 'data_sep_gagal', 'data_kunjungan_sep', 'data_sep_belum_selesai', 'countSumberDataSelesai', 'countSepPoli'));
    }

    public function send_taskid(Request $request)
    {
        ini_set('max_execution_time', 300);
        $dataSep = json_decode($request->input('data_sep_belum_selesai'), true);
        // dd($dataSep);
        $result = [];

        foreach ($dataSep as $data) {
            $str_kd_booking = $data['kodebooking'];
            $array          = explode(',', $str_kd_booking);
            // hapus spasi di tiap elemen
            $array_kd_booking = array_map('trim', $array);
            $bSep             = BridgingSep::with(['reg_periksa', 'mutasi_berkas', 'pemeriksaan_ralan'])
                ->find($data['noSep']);

            if (! $bSep) {
                $result[] = [
                    'noSep'   => $data['noSep'],
                    'status'  => 'failed',
                    'message' => 'BridgingSep not found',
                ];
                continue;
            }
            foreach ($array_kd_booking as $kd_booking) {
                $t_rawat = $data['tglSep'] . " " . ($bSep->pemeriksaan_ralan->jam_rawat ?? '00:00:00');

                $updates = [];
                foreach ([3 => $bSep->mutasi_berkas->dikirim ?? null,
                    4           => $bSep->mutasi_berkas->diterima ?? null,
                    5           => $t_rawat] as $taskid => $waktu) {
                    if ($waktu) {
                        try {
                            $response         = updateWaktuAntrean($kd_booking, $taskid, $waktu);
                            $updates[$taskid] = [
                                'success'  => isset($response['status']) && $response['status'] === 'OK',
                                'response' => $response,
                            ];
                        } catch (\Exception $e) {
                            $updates[$taskid] = [
                                'success'  => false,
                                'response' => $e->getMessage(),
                            ];
                        }
                    } else {
                        $updates[$taskid] = [
                            'success'  => false,
                            'response' => 'Waktu not available',
                        ];
                    }
                }

                $result[] = [
                    'noSep'       => $data['noSep'],
                    'kodebooking' => $kd_booking,
                    'updates'     => $updates,
                ];
            }

        }
        // Simpan ke file JSON di storage/app/public/sep_update.json
        $fileName = 'taksId_update_' . now()->format('Ymd_His') . '.json';
        Storage::disk('public')->put($fileName, json_encode($result, JSON_PRETTY_PRINT));

        // Simpan ke session
        return redirect()->back()->with('status_update_waktu', $result);
    }
}
