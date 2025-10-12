<?php
namespace App\Http\Controllers;

use App\Models\ReferensiMobilejknBpjs;
use App\Models\RegPeriksa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReferensiMjknController extends Controller
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
        $tanggal_awal    = $request->input('tanggal_awal', now()->format('Y-m-d'));
        $tanggal_akhir   = $request->input('tanggal_akhir', now()->format('Y-m-d'));
        $tanggal_mulai   = Carbon::parse($tanggal_awal);
        $tanggal_selesai = Carbon::parse($tanggal_akhir);

        $data_antrian = [];
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
            $data_ref_mjkn = ReferensiMobilejknBpjs::whereBetween('tanggalperiksa', [$tanggal_awal, $tanggal_akhir])->get();
            // Ambil semua kodebooking dari data_antrian
            $kodebookingAntrian  = array_column($data_antrian, 'kodebooking');
            $data_ref_mjkn_array = $data_ref_mjkn->toArray();

            foreach ($data_ref_mjkn_array as &$ref) {
                // simpan nilai match_antrol
                $match = in_array($ref['nobooking'], $kodebookingAntrian);

                // buat array baru dengan match_antrol di depan
                $ref = array_merge(['match_antrol' => $match], $ref);
            }

            // dd($data_ref_mjkn_array);
        }
        return view("monitoring.referensi-mjkn.index", compact('data_ref_mjkn_array'));
    }

    public function destroy(Request $request)
    {
        $data_ref = ReferensiMobilejknBpjs::find($request->nobooking);
        $data_reg = RegPeriksa::where('no_rawat', $data_ref->no_rawat)
            ->where('no_rkm_medis', $data_ref->norm)
            ->first();
        // 3. Hapus data reg_periksa jika ada
        if ($data_reg) {
            $data_reg->delete();
        }

        // 4. Hapus data referensi MJKN
        $data_ref->delete();
        return redirect()->back()->with('status', 'Sukses');
    }
}
