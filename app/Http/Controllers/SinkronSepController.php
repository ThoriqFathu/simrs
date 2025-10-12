<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SinkronSepController extends Controller
{
    public function index(Request $request)
    {

        // ambil parameter form
        $tanggalAwal  = $request->get('tanggal_awal', date('Y-m-d'));
        $tanggalAkhir = $request->get('tanggal_akhir', date('Y-m-d'));

        // $data = DB::select("
        //     SELECT pegawai.nama ,bridging_sep.* FROM bridging_sep
        //     LEFT JOIN pegawai ON bridging_sep.user = pegawai.nik
        //     WHERE bridging_sep.tglsep BETWEEN ? AND ?
        // ", [$tanggalAwal, $tanggalAkhir]);
        $data = DB::table('bridging_sep')
            ->leftJoin('pegawai', 'bridging_sep.user', '=', 'pegawai.nik')
            ->whereBetween('bridging_sep.tglsep', [$tanggalAwal, $tanggalAkhir])
            ->select('pegawai.nama', 'bridging_sep.*')
            ->orderBy('bridging_sep.tglsep', 'desc')
            ->paginate(20);

        $rekap = [];
        foreach ($data as $item) {
            $get_sep       = get_sep_bpjs($item->no_sep);
            $item->sinkron = $get_sep === null ? 0 : 1;
            $rekap[]       = (array) $item;
        }

        $allKeys = [];
        foreach ($rekap as $row) {
            $rowArray = (array) $row;
            $allKeys  = array_merge($allKeys, array_keys($rowArray));
        }
        $allKeys = array_unique($allKeys);

        return view('sinkron-sep.index', [
            'flattened'    => $rekap,
            'allKeys'      => $allKeys,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'data'         => $data, // ⬅️ penting untuk pagination
        ]);

    }
}
