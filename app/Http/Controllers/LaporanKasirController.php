<?php
namespace App\Http\Controllers;

use Box\Spout\Common\Entity\Style\StyleBuilder; // spout
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKasirController extends Controller
{
    /**
     * Tampilkan halaman laporan dengan tabel
     * Query memakai DB::select (raw SQL)
     */
    public function index(Request $request)
    {
        // Validasi & default tanggal
        $tgl1 = $request->query('tgl1') ?: date('Y-m-d');
        $tgl2 = $request->query('tgl2') ?: date('Y-m-d');

        // Pastikan format: YYYY-MM-DD
        // (anda bisa tambah validasi lebih ketat)
        // $start = $tgl1;
        // $end   = $tgl2;
        $start = $tgl1 . ' 00:00:00';
        $end   = $tgl2 . ' 23:59:59';

        // Ambil daftar akun bayar (kolom header dinamis)
        $akunbayar = DB::select("
            SELECT rekening.kd_rek, rekening.nm_rek
            FROM rekening
            WHERE (rekening.kd_rek IN (SELECT akun_bayar.kd_rek FROM akun_bayar GROUP BY akun_bayar.kd_rek))
               OR (rekening.kd_rek IN (SELECT kategori_pemasukan_lain.kd_rek2 FROM kategori_pemasukan_lain GROUP BY kategori_pemasukan_lain.kd_rek2))
            ORDER BY rekening.nm_rek
        ");

        // Ambil semua tagihan_sadewa dalam rentang tanggal
        $tagihans = DB::select("
            SELECT no_nota, tgl_bayar, nama_pasien, jumlah_bayar, petugas.nama as nama_petugas
            FROM tagihan_sadewa
            INNER JOIN petugas ON tagihan_sadewa.petugas = petugas.nip
            WHERE tgl_bayar BETWEEN ? AND ?
            ORDER BY tgl_bayar, no_nota
        ", [$start, $end]);
                            // dd($tagihans);
                            // Prepare struktur rows dan total per akun
        $rows         = []; // setiap elemen: ['no_nota'=>..., 'tgl_bayar'=>..., 'nama_pasien'=>..., 'jumlah_bayar'=>..., 'petugas'=>..., 'detail' => [kd_rek => jumlah]]
        $totalPerAkun = [];
        foreach ($akunbayar as $a) {
            $totalPerAkun[$a->kd_rek] = 0.0;
        }

        // Loop tagihan satu per satu dan cari sumber transaksinya
        foreach ($tagihans as $t) {
            $no_nota = $t->no_nota;
            $norawat = $nomornotatrnasaksi = $norawatinap = $norawatjalan = $notajual = $nodeposit = $nopemasukanlain = '';

            // cek nota_inap
            $x = DB::selectOne("select nota_inap.no_nota from nota_inap where nota_inap.no_rawat = ?", [$no_nota]);
            if ($x && isset($x->no_nota) && $x->no_nota !== '') {
                $norawatinap        = $no_nota;
                $norawat            = $norawatinap;
                $nomornotatrnasaksi = $x->no_nota;
            } else {
                // nota_jalan
                $x = DB::selectOne("select nota_jalan.no_nota from nota_jalan where nota_jalan.no_rawat = ?", [$no_nota]);
                if ($x && isset($x->no_nota) && $x->no_nota !== '') {
                    $norawatjalan       = $no_nota;
                    $norawat            = $norawatjalan;
                    $nomornotatrnasaksi = $x->no_nota;
                } else {
                    // penjualan
                    $x = DB::selectOne("select penjualan.nota_jual from penjualan where penjualan.nota_jual = ?", [$no_nota]);
                    if ($x && isset($x->nota_jual) && $x->nota_jual !== '') {
                        $notajual           = $no_nota;
                        $nomornotatrnasaksi = $no_nota;
                    } else {
                        // deposit
                        $x = DB::selectOne("select deposit.no_deposit, no_rawat from deposit where deposit.no_deposit = ?", [$no_nota]);
                        if ($x && isset($x->no_deposit) && $x->no_deposit !== '') {
                            $nodeposit          = $no_nota;
                            $norawat            = $x->no_rawat;
                            $nomornotatrnasaksi = $no_nota;
                        } else {
                            // pemasukan_lain
                            $x = DB::selectOne("select pemasukan_lain.no_masuk from pemasukan_lain where pemasukan_lain.no_masuk = ?", [$no_nota]);
                            if ($x && isset($x->no_masuk) && $x->no_masuk !== '') {
                                $nopemasukanlain    = $no_nota;
                                $nomornotatrnasaksi = $no_nota;
                            } else {
                                // transaksi tidak ditemukan -> treat as pemasukan lain kosong
                                // skip atau tetap ditampilkan? disini tetap ditampilkan tanpa detail akun
                            }
                        }
                    }
                }
            }

            // hitung tiap akun bayar untuk baris ini
            $detail = [];
            foreach ($akunbayar as $a) {
                $kd    = $a->kd_rek;
                $bayar = 0.0;

                if ($norawatinap !== '') {
                    $q = "select IFNULL(sum(detail_nota_inap.besar_bayar),0) as jumlah
                          from detail_nota_inap
                          inner join akun_bayar on detail_nota_inap.nama_bayar = akun_bayar.nama_bayar
                          where detail_nota_inap.no_rawat = ? and akun_bayar.kd_rek = ?";
                    $r     = DB::selectOne($q, [$norawatinap, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($norawatjalan !== '') {
                    $q = "select IFNULL(sum(detail_nota_jalan.besar_bayar),0) as jumlah
                          from detail_nota_jalan
                          inner join akun_bayar on detail_nota_jalan.nama_bayar = akun_bayar.nama_bayar
                          where detail_nota_jalan.no_rawat = ? and akun_bayar.kd_rek = ?";
                    $r     = DB::selectOne($q, [$norawatjalan, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($notajual !== '') {
                    $q = "select IFNULL((sum(detailjual.total) + IFNULL(penjualan.ongkir,0) + IFNULL(penjualan.ppn,0)),0) as jumlah
                          from detailjual
                          inner join penjualan on penjualan.nota_jual = detailjual.nota_jual
                          inner join akun_bayar on penjualan.nama_bayar = akun_bayar.nama_bayar
                          where penjualan.nota_jual = ? and akun_bayar.kd_rek = ?";
                    $r     = DB::selectOne($q, [$notajual, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($nodeposit !== '') {
                    $q = "select IFNULL(sum(deposit.besar_deposit),0) as jumlah
                          from deposit
                          inner join akun_bayar on deposit.nama_bayar = akun_bayar.nama_bayar
                          where deposit.no_deposit = ? and akun_bayar.kd_rek = ?";
                    $r     = DB::selectOne($q, [$nodeposit, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($nopemasukanlain !== '') {
                    $q = "select IFNULL(sum(pemasukan_lain.besar),0) as jumlah
                          from pemasukan_lain
                          inner join kategori_pemasukan_lain on kategori_pemasukan_lain.kode_kategori = pemasukan_lain.kode_kategori
                          where pemasukan_lain.no_masuk = ? and kategori_pemasukan_lain.kd_rek2 = ?";
                    $r     = DB::selectOne($q, [$nopemasukanlain, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else {
                    // tidak ketemu sumber -> bayar 0 (atau simpan keterangan)
                    $bayar = 0.0;
                }

                $detail[$kd] = $bayar;
                $totalPerAkun[$kd] += $bayar;
            }

            $rows[] = [
                'no_rawat'     => $norawat,
                'no_nota'      => $nomornotatrnasaksi,
                'tgl_bayar'    => $t->tgl_bayar,
                'nama_pasien'  => $t->nama_pasien,
                'jumlah_bayar' => (float) $t->jumlah_bayar,
                'petugas'      => $t->nama_petugas,
                'detail'       => $detail,
            ];
        } // end foreach tagihans

        // kirim ke blade
        return view('keuangan.laporan.kasir', [
            'akunbayar'    => $akunbayar,
            'rows'         => $rows,
            'totalPerAkun' => $totalPerAkun,
            'tgl1'         => $tgl1,
            'tgl2'         => $tgl2,
        ]);
    }

    /**
     * Export ke XLSX (Spout)
     */
    public function export(Request $request)
    {
        $tgl1  = $request->query('tgl1') ?: date('Y-m-d');
        $tgl2  = $request->query('tgl2') ?: date('Y-m-d');
        $start = $tgl1 . ' 00:00:00';
        $end   = $tgl2 . ' 23:59:59';

        // ambil akunbayar & rows (duplikasi logika index) -- untuk ringkas saya panggil index-like logic
        // (boleh refactor menjadi private method untuk menghindari duplikasi)
        $akunbayar = DB::select("
            SELECT rekening.kd_rek, rekening.nm_rek
            FROM rekening
            WHERE (rekening.kd_rek IN (SELECT akun_bayar.kd_rek FROM akun_bayar GROUP BY akun_bayar.kd_rek))
               OR (rekening.kd_rek IN (SELECT kategori_pemasukan_lain.kd_rek2 FROM kategori_pemasukan_lain GROUP BY kategori_pemasukan_lain.kd_rek2))
            ORDER BY rekening.nm_rek
        ");

        $tagihans = DB::select("
            SELECT no_nota, tgl_bayar, nama_pasien, jumlah_bayar, petugas.nama as nama_petugas
            FROM tagihan_sadewa
            INNER JOIN petugas ON tagihan_sadewa.petugas = petugas.nip
            WHERE tgl_bayar BETWEEN ? AND ?
            ORDER BY tgl_bayar, no_nota
        ", [$start, $end]);

        $rows = [];
        foreach ($tagihans as $t) {
            $no_nota = $t->no_nota;
            $norawat = $nomornotatrnasaksi = $norawatinap = $norawatjalan = $notajual = $nodeposit = $nopemasukanlain = '';

            // cek nota_inap
            $x = DB::selectOne("select nota_inap.no_nota from nota_inap where nota_inap.no_rawat = ?", [$no_nota]);
            if ($x && isset($x->no_nota) && $x->no_nota !== '') {
                $norawatinap        = $no_nota;
                $norawat            = $norawatinap;
                $nomornotatrnasaksi = $x->no_nota;
            } else {
                // nota_jalan
                $x = DB::selectOne("select nota_jalan.no_nota from nota_jalan where nota_jalan.no_rawat = ?", [$no_nota]);
                if ($x && isset($x->no_nota) && $x->no_nota !== '') {
                    $norawatjalan       = $no_nota;
                    $norawat            = $norawatjalan;
                    $nomornotatrnasaksi = $x->no_nota;
                } else {
                    // penjualan
                    $x = DB::selectOne("select penjualan.nota_jual from penjualan where penjualan.nota_jual = ?", [$no_nota]);
                    if ($x && isset($x->nota_jual) && $x->nota_jual !== '') {
                        $notajual           = $no_nota;
                        $nomornotatrnasaksi = $no_nota;
                    } else {
                        // deposit
                        $x = DB::selectOne("select deposit.no_deposit, no_rawat from deposit where deposit.no_deposit = ?", [$no_nota]);
                        if ($x && isset($x->no_deposit) && $x->no_deposit !== '') {
                            $nodeposit          = $no_nota;
                            $norawat            = $x->no_rawat;
                            $nomornotatrnasaksi = $no_nota;
                        } else {
                            // pemasukan_lain
                            $x = DB::selectOne("select pemasukan_lain.no_masuk from pemasukan_lain where pemasukan_lain.no_masuk = ?", [$no_nota]);
                            if ($x && isset($x->no_masuk) && $x->no_masuk !== '') {
                                $nopemasukanlain    = $no_nota;
                                $nomornotatrnasaksi = $no_nota;
                            } else {
                                // transaksi tidak ditemukan -> treat as pemasukan lain kosong
                                // skip atau tetap ditampilkan? disini tetap ditampilkan tanpa detail akun
                            }
                        }
                    }
                }
            }

            $detail = [];
            foreach ($akunbayar as $a) {
                $kd    = $a->kd_rek;
                $bayar = 0.0;

                if ($norawatinap !== '') {
                    $r = DB::selectOne("select IFNULL(sum(detail_nota_inap.besar_bayar),0) as jumlah
                        from detail_nota_inap
                        inner join akun_bayar on detail_nota_inap.nama_bayar = akun_bayar.nama_bayar
                        where detail_nota_inap.no_rawat = ? and akun_bayar.kd_rek = ?", [$norawatinap, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($norawatjalan !== '') {
                    $r = DB::selectOne("select IFNULL(sum(detail_nota_jalan.besar_bayar),0) as jumlah
                        from detail_nota_jalan
                        inner join akun_bayar on detail_nota_jalan.nama_bayar = akun_bayar.nama_bayar
                        where detail_nota_jalan.no_rawat = ? and akun_bayar.kd_rek = ?", [$norawatjalan, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($notajual !== '') {
                    $r = DB::selectOne("select IFNULL((sum(detailjual.total) + IFNULL(penjualan.ongkir,0) + IFNULL(penjualan.ppn,0)),0) as jumlah
                        from detailjual
                        inner join penjualan on penjualan.nota_jual = detailjual.nota_jual
                        inner join akun_bayar on penjualan.nama_bayar = akun_bayar.nama_bayar
                        where penjualan.nota_jual = ? and akun_bayar.kd_rek = ?", [$notajual, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($nodeposit !== '') {
                    $r = DB::selectOne("select IFNULL(sum(deposit.besar_deposit),0) as jumlah
                        from deposit
                        inner join akun_bayar on deposit.nama_bayar = akun_bayar.nama_bayar
                        where deposit.no_deposit = ? and akun_bayar.kd_rek = ?", [$nodeposit, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else if ($nopemasukanlain !== '') {
                    $r = DB::selectOne("select IFNULL(sum(pemasukan_lain.besar),0) as jumlah
                        from pemasukan_lain
                        inner join kategori_pemasukan_lain on kategori_pemasukan_lain.kode_kategori = pemasukan_lain.kode_kategori
                        where pemasukan_lain.no_masuk = ? and kategori_pemasukan_lain.kd_rek2 = ?", [$nopemasukanlain, $kd]);
                    $bayar = $r ? (float) $r->jumlah : 0.0;
                } else {
                    $bayar = 0.0;
                }

                $detail[$kd] = $bayar;
            }

            $rows[] = [
                'no_rawat'     => $norawat,
                'no_nota'      => $nomornotatrnasaksi,
                'tgl_bayar'    => $t->tgl_bayar,
                'nama_pasien'  => $t->nama_pasien,
                'jumlah_bayar' => (float) $t->jumlah_bayar,
                'petugas'      => $t->nama_petugas,
                'detail'       => $detail,
            ];
        }

        // prepare XLSX via Spout
        $writer   = WriterEntityFactory::createXLSXWriter();
        $filename = 'laporan_kasir_' . $tgl1 . '_s_d_' . $tgl2 . '.xlsx';
        $writer->openToBrowser($filename);

        // header row
        $header = ['No', 'Tanggal Bayar', 'No Rawat', 'No Nota', 'Nama Pasien', 'Jumlah Bayar', 'Petugas'];
        foreach ($akunbayar as $a) {
            $header[] = $a->nm_rek;
        }

        $writer->addRow(WriterEntityFactory::createRowFromArray($header));

        // data rows
        $no = 1;
        foreach ($rows as $r) {
            $row = [
                $no,
                $r['tgl_bayar'],
                $r['no_rawat'],
                $r['no_nota'],
                $r['nama_pasien'],
                $r['jumlah_bayar'],
                $r['petugas'],
            ];
            foreach ($akunbayar as $a) {
                $row[] = $r['detail'][$a->kd_rek] ?? 0;
            }
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));
            $no++;
        }

        $writer->close();
        // Spout sudah mengirim response ke browser
        return response()->noContent();
    }
}
