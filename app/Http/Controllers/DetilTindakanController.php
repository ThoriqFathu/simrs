<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetilTindakanController extends Controller
{
    public function index(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '1024M');
        // ambil parameter form
        $tanggalAwal  = $request->get('tanggal_awal', "2025-08-01");
        $tanggalAkhir = $request->get('tanggal_akhir', "2025-08-01");

        $data = DB::select("
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
            WHERE reg_periksa.status_bayar = 'Sudah Bayar'
            AND reg_periksa.tgl_registrasi BETWEEN ? AND ?
        ", [$tanggalAwal, $tanggalAkhir]);

        $get_detil = get_data_detil_tindakan($data);
        // dd($get_detil);

        $allKeys = [];
        foreach ($get_detil as $row) {
            $rowArray = (array) $row;
            $allKeys  = array_merge($allKeys, array_keys($rowArray));
        }
        $allKeys = array_unique($allKeys);

        return view('keuangan.detil-tindakan.index', [
            'flattened'    => $get_detil,
            'allKeys'      => $allKeys,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ]);
    }
}
