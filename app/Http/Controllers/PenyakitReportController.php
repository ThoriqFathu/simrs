<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyakitReportController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal    = $request->tgl_awal ?? date('Y-m-01');
        $tgl_akhir   = $request->tgl_akhir ?? date('Y-m-d');
        $kd_penyakit = $request->kd_penyakit;
        $umur_min    = $request->umur_min;
        $umur_max    = $request->umur_max;

        // 🔍 Query dasar
        $query = DB::table('diagnosa_pasien')
            ->select(
                'diagnosa_pasien.kd_penyakit',
                'penyakit.nm_penyakit',
                DB::raw('COUNT(DISTINCT diagnosa_pasien.no_rawat) as total_pasien')
            )
            ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->join('reg_periksa', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tgl_awal, $tgl_akhir]);

        // 🔠 Filter kode penyakit
        if ($kd_penyakit) {
            $query->where('diagnosa_pasien.kd_penyakit', $kd_penyakit);
        }

        // 🎯 Filter umur (konversi ke tahun)
        if ($umur_min || $umur_max) {
            $query->where(function ($q) use ($umur_min, $umur_max) {
                $q->whereRaw("
                    CASE reg_periksa.sttsumur
                        WHEN 'Th' THEN reg_periksa.umurdaftar
                        WHEN 'Bl' THEN reg_periksa.umurdaftar / 12
                        WHEN 'Hr' THEN reg_periksa.umurdaftar / 365
                        ELSE 0
                    END BETWEEN ? AND ?
                ", [
                    $umur_min ?? 0,
                    $umur_max ?? 200,
                ]);
            });
        }

        $query->groupBy('diagnosa_pasien.kd_penyakit', 'penyakit.nm_penyakit')
            ->orderByDesc('total_pasien');

        $data = $query->get();

        return view('laporan.penyakit', compact(
            'data', 'tgl_awal', 'tgl_akhir', 'kd_penyakit', 'umur_min', 'umur_max'
        ));
    }
}
