<?php
namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatetimePasienController extends Controller
{
    public function index(Request $request)
    {
        $today         = now()->format('Y-m-d');
        $selected_date = $request->input('selected_date', $today);

        $results = DB::select("
            SELECT reg_periksa.tgl_registrasi,
                   reg_periksa.no_rawat,
                   reg_periksa.jam_reg,
                   pasien.no_rkm_medis,
                   pasien.nm_pasien,
                   reg_periksa.status_lanjut,
                   validasi,
                   mutasi_berkas.dikirim,
                   mutasi_berkas.diterima,
                   pemeriksaan_ralan.tgl_perawatan,
                   pemeriksaan_ralan.jam_rawat
            FROM pasien
            RIGHT JOIN reg_periksa ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            LEFT JOIN referensi_mobilejkn_bpjs ON referensi_mobilejkn_bpjs.no_rawat = reg_periksa.no_rawat
            LEFT JOIN mutasi_berkas ON mutasi_berkas.no_rawat = reg_periksa.no_rawat
            LEFT JOIN pemeriksaan_ralan ON pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat
            WHERE reg_periksa.tgl_registrasi = ?
            AND reg_periksa.status_lanjut = 'Ralan'
            AND reg_periksa.kd_poli != 'IGDK'
        ", [$selected_date]);

        return view('monitoring.datetime-pasien.index', compact('selected_date', 'results'));
    }

    public function repair(Request $request)
    {
        $today         = now()->format('Y-m-d');
        $selected_date = $request->input('selected_date', $today);

        $results = DB::select("
    SELECT reg_periksa.no_rawat,
           mutasi_berkas.dikirim,
           mutasi_berkas.diterima,
           pemeriksaan_ralan.jam_rawat
    FROM reg_periksa
    LEFT JOIN mutasi_berkas ON mutasi_berkas.no_rawat = reg_periksa.no_rawat
    LEFT JOIN pemeriksaan_ralan ON pemeriksaan_ralan.no_rawat = reg_periksa.no_rawat
    WHERE reg_periksa.tgl_registrasi = ?
    AND reg_periksa.status_lanjut = 'Ralan'
", [$selected_date]);

        foreach ($results as $row) {
            if (! empty($row->dikirim) && ! empty($row->diterima) && ! empty($row->jam_rawat)) {

                $t_kirim            = strtotime($row->dikirim);
                $timestamp_diterima = strtotime($row->diterima);
                $no_rawat           = $row->no_rawat;

                // Jika dikirim >= diterima → diterima ditambah 1 menit + detik random
                if ($t_kirim >= $timestamp_diterima) {
                    $date = new DateTime($row->dikirim);
                    $date->modify('+1 minute');
                    $date->setTime($date->format('H'), $date->format('i'), rand(0, 59));
                    $diterima_baru      = $date->format('Y-m-d H:i:s');
                    $timestamp_diterima = $date->getTimestamp();

                    DB::update("
                UPDATE mutasi_berkas
                SET diterima = ?
                WHERE no_rawat = ?
            ", [$diterima_baru, $no_rawat]);
                }

                // cek jika diterima >= jam_rawat
                $date_diterima = new DateTime();
                $date_diterima->setTimestamp($timestamp_diterima);
                $jam_diterima = $date_diterima->format('H:i:s');

                if ($jam_diterima >= $row->jam_rawat) {
                    $date = clone $date_diterima;
                    $date->modify('+' . rand(10, 15) . ' minutes');
                    $date->setTime($date->format('H'), $date->format('i'), rand(0, 59));
                    $jam_rawat_baru = $date->format('H:i:s');

                    DB::update("
                UPDATE pemeriksaan_ralan
                SET jam_rawat = ?
                WHERE no_rawat = ? AND jam_rawat = ?
            ", [$jam_rawat_baru, $no_rawat, $row->jam_rawat]);
                }
            }
        }

        return redirect()->route('monitoring.mutasi_berkas.index', ['selected_date' => $selected_date])
            ->with('success', 'Repair data selesai!');

    }
}
