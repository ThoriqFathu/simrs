<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use LZCompressor\LZString;

function cache_get($key)
{
    $path = "cache/sepKlaim/{$key}.json";
    if (Storage::exists($path)) {
        $json = Storage::get($path);
        return json_decode($json, true);
    }
    return null;
}

function cache_put($key, $data)
{
    $path = "cache/sepKlaim/{$key}.json";
    Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));
}
if (! function_exists('data_petugas_vk_ponek')) {
    function data_petugas_vk_ponek()
    {
        $kode = [
            "PWT052",
            "PWT037",
            "PWT076",
            "PWT071",
            "PWT021",
            "PWT102",
            "PWT045",
            "PWT112",
            "PWT031",
            "PWT083",
            "PWT148",
            "PWT056",
            "PWT026",
            "PWT082",
            "PWT092",
            "PWT096",
            "PWT046",
            "PWT176",
        ];
        return $kode;
    }
}
if (! function_exists('data_bangsal_vk')) {
    function data_bangsal_vk()
    {
        $kode = [
            "kl3.8",
            "kl3.9",
        ];
        return $kode;
    }
}
if (! function_exists('get_map_dokter_poli')) {
    function get_map_dokter_poli($kd_dokter)
    {
        $spesialis = [
            'DR001'    => 'POLI BEDAH',
            'DR010'    => 'POLI KESEHATAN JIWA',
            'DR061'    => 'POLI ANAK',
            'DR006'    => 'POLI KANDUNGAN',
            'DR007'    => 'POLI KANDUNGAN',
            'DR045'    => 'Patalogi Klinik',
            'DR014'    => 'POLI PENYAKIT DALAM',
            'DR004'    => 'POLI RADIOLOGI',
            'D0000003' => 'POLI UROLOGI',
            'DR078'    => 'POLI UROLOGI',
            // 'DR003'    => 'POLI UMUM',
            // 'DR009'    => 'POLI UMUM',
            // 'DR015'    => 'POLI UMUM',
            // 'DR018'    => 'POLI UMUM',
            // 'DR020'    => 'POLI UMUM',
            // 'DR022'    => 'POLI UMUM',
            // 'DR031'    => 'POLI UMUM',
            // 'DR041'    => 'POLI UMUM',
            // 'DR057'    => 'POLI UMUM',
            // 'DR058'    => 'POLI UMUM',
            // 'DR059'    => 'POLI UMUM',
            // 'DR068'    => 'POLI GIGI',
        ];

        return isset($spesialis[$kd_dokter]) ? $spesialis[$kd_dokter] : false;
    }
}
if (! function_exists('get_map_paramedis_poli')) {
    function get_map_paramedis_poli($kd_paramedis)
    {
        $poli = [
            'PWT106'      => 'POLI PENYAKIT DALAM',
            // '20250601006' => 'POLI BEDAH',
            'PWT144'      => 'POLI RADIOLOGI',
            '20250601001' => 'POLI RADIOLOGI',
            'PWT138'      => 'POLI RADIOLOGI',
            'PWT132'      => 'POLI RADIOLOGI',
            'PWT130'      => 'POLI ANAK',
            'PWT086'      => 'POLI ANAK',
            'PWT119'      => 'POLI KESEHATAN JIWA',
        ];

        return isset($poli[$kd_paramedis]) ? $poli[$kd_paramedis] : false;
    }
}

if (! function_exists('get_name_rs')) {
    function get_name_rs()
    {
        return env('BPJS_CONS_ID') == 16303 ? 'RSUD Ketapang' : "RSIA HIKMAH SAWI";
    }
}
if (! function_exists('get_key')) {
    function get_key($tStamp)
    {
        $data      = env("BPJS_CONS_ID");
        $secretKey = env("BPJS_SECRET_KEY");
        return $data . $secretKey . $tStamp;
    }
}
if (! function_exists('convertToMilliseconds')) {
    function convertToMilliseconds($timestamp)
    {
        // Set timezone ke WIB (UTC+7)
        date_default_timezone_set('Asia/Jakarta');
        // Ubah timestamp menjadi detik Unix
        $seconds = strtotime($timestamp);

        // Jika timestamp tidak valid, kembalikan null
        if ($seconds === false) {
            return null;
        }

        // Konversi ke milidetik
        $milliseconds = $seconds * 1000;

        return $milliseconds;
    }
}
if (! function_exists('convertToTimestamp')) {
    function convertToTimestamp($milliseconds)
    {
        // Set timezone ke WIB (UTC+7)
        date_default_timezone_set('Asia/Jakarta');

        // Konversi milidetik ke detik Unix
        $seconds = $milliseconds / 1000;

        // Ubah ke format timestamp
        return date('Y-m-d H:i:s', $seconds);
    }
}
if (! function_exists('string_decrypt')) {
    function string_decrypt($key, $string)
    {
        $encrypt_method = 'AES-256-CBC';
        $key_hash       = hex2bin(hash('sha256', $key));
        $iv             = substr(hex2bin(hash('sha256', $key)), 0, 16);

        return openssl_decrypt(
            base64_decode($string),
            $encrypt_method,
            $key_hash,
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}

if (! function_exists('lz_decompress')) {
    function lz_decompress($string)
    {
        return LZString::decompressFromEncodedURIComponent($string);
    }
}

if (! function_exists('array_flatten_dot')) {
    function array_flatten_dot(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $result += array_flatten_dot($value, $newKey); // panggil diri sendiri
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}
// SERVICE BPJS
if (! function_exists('get_ws_bpjs')) {
    function get_ws_bpjs($url)
    {
        $data      = env("BPJS_CONS_ID");
        $secretKey = env("BPJS_SECRET_KEY");
        date_default_timezone_set('UTC');
        $tStamp           = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature        = hash_hmac('sha256', $data . "&" . $tStamp, $secretKey, true);
        $encodedSignature = base64_encode($signature);

        $headers = [
            "X-cons-id"    => $data,
            "X-timestamp"  => $tStamp,
            "X-signature"  => $encodedSignature,
            "Content-Type" => "application/json; charset=utf-8",
        ];
        // $response = Http::withHeaders($headers)->get($url);
        $response = Http::withHeaders($headers)
            ->connectTimeout(15) // waktu maksimal mencoba membuka koneksi (detik)
            ->timeout(120)       // waktu maksimal menunggu respons penuh (detik)
            ->get($url);
        $key = get_key($tStamp);
        // return 2 nilai sebagai array
        return [
            'key'      => $key,
            'response' => $response,
        ];
    }
}
if (! function_exists('get_sep_bpjs')) {
    function get_sep_bpjs($no_sep)
    {
        $baseUrl = env("BPJS_BASE_URL_VCLAIM_REST");
        $url     = "$baseUrl/SEP/$no_sep";

        $result   = get_ws_bpjs($url);
        $response = $result['response'];
        $key      = $result['key'];
        $httpcode = $response->status();

        if ($httpcode == 200 && $response->body()) {
            $dec = $response->json();
            if (! empty($dec['response'])) {
                $hasilRes = lz_decompress(string_decrypt($key, $dec['response']));
                $datsaAp  = json_decode($hasilRes, true);
                return $datsaAp;
            }
        }
        return null;
    }
}
if (! function_exists('get_data_decrypt')) {
    function get_data_decrypt($key, $response)
    {
        $httpcode = $response->status();
        $data     = [];
        if ($httpcode == 200 && $response->body()) {
            $dec = $response->json();
            if (! empty($dec['response'])) {
                $hasilRes = lz_decompress(string_decrypt($key, $dec['response']));
                $data     = json_decode($hasilRes, true);
                // ubah nilai timestamp menjadi tanggal-jam yang lebih terbaca
                $data = array_map(function ($item) {
                    // cek key createdtime
                    if (isset($item['createdtime']) && is_numeric($item['createdtime'])) {
                        $item['createdtime'] = convertToTimestamp($item['createdtime']);
                    }

                    // cek key estimasidilayani
                    if (isset($item['estimasidilayani']) && is_numeric($item['estimasidilayani'])) {
                        $item['estimasidilayani'] = convertToTimestamp($item['estimasidilayani']);
                    }

                    return $item;
                }, $data);
                // dd($data);
            }
        }
        return $data;
    }
}
if (! function_exists('get_status_sep')) {
    function get_status_sep($data_antrian, $data_kunjungan_sep)
    {

        // 1. Data SEP yang tidak ada di antrian sama sekali
        $sepTidakAdaDiAntrian = array_filter($data_kunjungan_sep, function ($sep) use ($data_antrian) {
            return ! in_array($sep['noKartu'], array_column($data_antrian, 'nokapst'));
        });
        // 2. Hitung jumlah SEP per poli

        // 2. Data SEP yang ada di antrian tapi semua statusnya ≠ 'Selesai dilayani'
        $countSumberDataSelesai = [
            "MJKN"     => 0,
            "Non_MJKN" => 0,
            "Batal"    => 0,
        ];

        $sepAdaBelumSelesai = array_map(function ($sep) use ($data_antrian, &$countSumberDataSelesai) {
            $noKartu = $sep['noKartu'];

            // cari semua antrian peserta ini
            $antrianPeserta = array_filter($data_antrian, function ($antrian) use ($noKartu) {
                return $antrian['nokapst'] === $noKartu;
            });

            // kalau tidak ada di antrian sama sekali → skip
            if (empty($antrianPeserta)) {
                return null;
            }

            // cek apakah ada yang selesai
            foreach ($antrianPeserta as $antrian) {
                if (isset($antrian['status']) && $antrian['status'] === 'Selesai dilayani') {
                    if (isset($antrian['sumberdata']) && $antrian['sumberdata'] === "Mobile JKN") {
                        $countSumberDataSelesai['MJKN'] += 1;
                    } else {
                        $countSumberDataSelesai['Non_MJKN'] += 1;
                    }
                    return null; // ada yang selesai → skip
                } else if (isset($antrian['status']) && $antrian['status'] === 'Batal') {
                    $countSumberDataSelesai['Batal'] += 1;
                    return null;
                }
            }

            // ambil semua kodebooking yang belum selesai dan jadikan string
            $sep['kodebooking'] = implode(', ', array_map(fn($a) => $a['kodebooking'], $antrianPeserta));

            return $sep;

        }, $data_kunjungan_sep);

        // hapus elemen null (SEP yang selesai atau tidak ada di antrian)
        $sepAdaBelumSelesai = array_filter($sepAdaBelumSelesai);

        // reset index array hasilnya
        $sepTidakAdaDiAntrian = array_values($sepTidakAdaDiAntrian);
        $sepAdaBelumSelesai   = array_values($sepAdaBelumSelesai);

        return [
            'sep_tidak_ada_di_antrian' => $sepTidakAdaDiAntrian,
            'sep_ada_belum_selesai'    => $sepAdaBelumSelesai,
            'count_sumberdata_selesai' => $countSumberDataSelesai,
        ];
    }
}

if (! function_exists('updateWaktuAntrean')) {
    /**
     * Update waktu antrean ke BPJS
     *
     * @param string $kodebooking
     * @param string $taskid
     * @param string $waktu
     * @param string $secretKey
     * @param string $data_key
     * @param string $encodedSignature
     * @param string $tStamp
     * @return array|string
     */
    function updateWaktuAntrean($kodebooking, $taskid, $waktu)
    {
        $payload = [
            'kodebooking' => $kodebooking,
            'taskid'      => $taskid,
            'waktu'       => convertToMilliseconds($waktu),
            // 'waktu'       => $waktu,
        ];
        // dump($payload);
        // return $payload;
        $data      = env("BPJS_CONS_ID");
        $secretKey = env("BPJS_SECRET_KEY");
        date_default_timezone_set('UTC');
        $tStamp           = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature        = hash_hmac('sha256', $data . "&" . $tStamp, $secretKey, true);
        $encodedSignature = base64_encode($signature);

        $headers = [
            "X-cons-id"    => $data,
            "X-timestamp"  => $tStamp,
            "X-signature"  => $encodedSignature,
            "Content-Type" => "application/json; charset=utf-8",
        ];

        try {
            $response = Http::withHeaders($headers)
                ->post('https://new-apijkn.bpjs-kesehatan.go.id/antreanrs/antrean/updatewaktu', $payload);

            if ($response->successful()) {
                return $response->json(); // mengembalikan array
            }

            return [
                'error'  => true,
                'status' => $response->status(),
                'body'   => $response->body(),
            ];

        } catch (\Exception $e) {
            return [
                'error'   => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}

// ============================ KLAIM =========================
if (! function_exists('get_data_khanza')) {
    function get_data_khanza($flattened, $jns)
    {
        $sql = "
       SELECT * FROM `pasien` INNER JOIN reg_periksa ON pasien.no_rkm_medis = reg_periksa.no_rkm_medis WHERE pasien.no_peserta = ? AND reg_periksa.tgl_registrasi = ?
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
        $data = [];
        foreach ($flattened as $data_bpjs) {
            $sep_no = $data_bpjs['noSEP'];

            // cek dulu di cache
            $cached = cache_get($sep_no);
            if ($cached) {
                $data[] = $cached; // langsung ambil dari cache
                continue;          // lewati pemrosesan selanjutnya
            }

            $bSep = DB::select($sql_bsep, [$data_bpjs['noSEP']]);
            if ($bSep == null) {
                $_data = DB::select($sql, [$data_bpjs['peserta.noKartu'], $data_bpjs['tglSep']]);
                if (count($_data) == 0) {
                    $no_rawat = null;
                } else {
                    $no_rawat     = $_data[0]->no_rawat;
                    $nm_pasien    = $_data[0]->nm_pasien;
                    $no_rkm_medis = $_data[0]->no_rkm_medis;
                }
            } else {
                $no_rawat     = $bSep[0]->no_rawat;
                $nm_pasien    = $bSep[0]->nm_pasien;
                $no_rkm_medis = $bSep[0]->no_rkm_medis;
            }
            if ($no_rawat == null) {
                $data[] = [
                    "data_bpjs"   => $data_bpjs,
                    "no_rawat"    => null,
                    "nama_pasien" => null,
                    "no_mr_rs"    => null,
                ];
            } else {
                $select_poli = DB::selectOne("
                    SELECT poliklinik.nm_poli
                    FROM reg_periksa
                    INNER JOIN poliklinik ON poliklinik.kd_poli=reg_periksa.kd_poli
                    WHERE reg_periksa.no_rawat = ?
                ", [$no_rawat]);

                // ============= START GET DPJP ===========

                if ($jns == 1) {
                    //     $select_dpjp = DB::select("
                    //     SELECT dpjp_ranap.kd_dokter, nm_dokter
                    //     FROM dpjp_ranap INNER JOIN dokter ON dpjp_ranap.kd_dokter=dokter.kd_dokter
                    //     WHERE dpjp_ranap.no_rawat = ?
                    // ", [$no_rawat]);
                    //     if (count($select_dpjp) == 0) {
                    //         $select_dpjp = DB::select("
                    //     SELECT reg_periksa.kd_dokter, nm_dokter
                    //     FROM reg_periksa INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter
                    //     WHERE reg_periksa.no_rawat = ?
                    // ", [$no_rawat]);
                    //     }
                    //     $kd_dpjp = collect($select_dpjp)->pluck('kd_dokter')->implode(', ');
                    //     $nm_dpjp = collect($select_dpjp)->pluck('nm_dokter')->implode(', ');
                    $get_sep = get_sep_bpjs($data_bpjs['noSEP']);
                    $kd_dpjp = $get_sep['kontrol']['kdDokter'] ?? '';
                    $nm_dpjp = $get_sep['kontrol']['nmDokter'] ?? '';
                    // dd($get_sep);
                } else {
                    $get_sep = get_sep_bpjs($data_bpjs['noSEP']);
                    $kd_dpjp = $get_sep['dpjp']['kdDPJP'] ?? '';
                    $nm_dpjp = $get_sep['dpjp']['nmDPJP'] ?? '';
                }
                // ============= END GET DPJP ===========

                // dump($select_dpjp);
                // $ralan_paramedis = get_ralan_paramedis($no_rawat);
                // $ralan_dokter           = get_ralan_dokter($no_rawat);
                // $ralan_dokter_paramedis = get_ralan_dokter_paramedis($no_rawat);
                // $total_ralan            = $ralan_paramedis['total'] + $ralan_dokter + $ralan_dokter_paramedis['total'];
                $data_rujukan           = get_klaim_rujukan_internal_poli($no_rawat);
                $ralan_batch            = get_ralan_batch($no_rawat);
                $operasi                = get_operasi($no_rawat);
                $ranap_paramedis        = get_ranap_paramedis($no_rawat);
                $ranap_dokter           = get_ranap_dokter($no_rawat);
                $ranap_dokter_paramedis = get_ranap_dokter_paramedis($no_rawat);
                $data_ranap             = array_merge($ranap_dokter, $ranap_dokter_paramedis, $ranap_paramedis);
                $data_unit              = get_data_unit($data_ranap);
                $data_kamar             = get_tarif_kamar($no_rawat);
                // dump($no_rawat);
                // dump($data_unit);
                $data_unit['vk']    = ($data_unit['vk'] ?? 0) + ($data_kamar['vk'] ?? 0);
                $data_unit['nicu']  = ($data_unit['nicu'] ?? 0) + ($data_kamar['nicu'] ?? 0);
                $data_unit['icu']   = ($data_unit['icu'] ?? 0) + ($data_kamar['icu'] ?? 0);
                $data_unit['ranap'] = ($data_unit['ranap'] ?? 0) + ($data_kamar['ranap'] ?? 0);

                // dump($data_kamar);
                // dump($data_unit);
                $radiologi       = get_radiologi($no_rawat);
                $lab             = get_lab($no_rawat);
                $resutls_farmasi = get_farmasi($no_rawat);
                // if ($ralan_dokter_paramedis['is_ponek'] || $ralan_paramedis['is_ponek']) {
                //     $map_poli = get_map_poli('PONEK', $total_ralan);

                // } else {
                //     $map_poli = get_map_poli($select_poli->nm_poli, $total_ralan);
                // }
                $map_poli = get_map_poli($ralan_batch, $select_poli->nm_poli, $data_rujukan);
                $final    = array_replace($data_bpjs, $map_poli);
                $final    = array_replace($final, $data_unit);

                // dd($data_bpjs);
                $item = [
                    "kode_dpjp"       => $kd_dpjp,
                    "nama_dpjp"       => $nm_dpjp,
                    "no_rawat"        => $no_rawat,
                    "no_mr_rs"        => $no_rkm_medis,
                    "nama_pasien"     => $nm_pasien,
                    ""                => $final,
                    "operasi"         => $operasi,
                    "radiologi"       => $radiologi,
                    "laboratorium"    => $lab['total_lab'],
                    "bank_darah"      => $lab['total_bank_darah'],
                    "obat"            => $resutls_farmasi['total_obat'],
                    "keuntungan_obat" => $resutls_farmasi['keuntungan_obat'],
                    "jaspel_farmasi"  => $resutls_farmasi['jaspel_farmasi'],
                ];
                cache_put($sep_no, $item);
                $data[] = $item;

            }
        }
        // dd($data);
        return ($data);
    }
}
if (! function_exists('get_data_unit')) {
    function get_data_unit($data_tindakan)
    {
        $data_unit = [
            'cssd'  => [],
            'gizi'  => [],
            'nicu'  => [],
            'icu'   => [],
            'vk'    => [],
            'ranap' => [],
        ];

        $totals_per_unit = [
            'cssd'  => 0,
            'gizi'  => 0,
            'nicu'  => 0,
            'icu'   => 0,
            'vk'    => 0,
            'ranap' => 0,
        ];
        // dd($data_tindakan);
        foreach ($data_tindakan as $data) {
            // Normalize string sekali aja biar hemat
            $nm_perawatan = strtolower((string) $data->nm_perawatan);
            $kd_bangsal   = strtolower((string) $data->kd_bangsal);
            $kd_prw       = strtolower((string) $data->kd_jenis_prw);
            $kd_ruang     = strtolower((string) $data->ruang_tindakan);
            $nm_pasien    = strtolower(str_replace('.', '', (string) $data->nm_pasien));

            $unit = 'ranap'; // default fallback
            if (str_contains($nm_perawatan, 'cssd') || str_contains($kd_prw, 'cssd')) {
                $unit = 'cssd';
            } elseif (str_contains($nm_perawatan, 'gizi') || str_contains($nm_perawatan, 'makan')) {
                $unit = 'gizi';
            } elseif (str_contains($nm_perawatan, 'vk') || str_contains($kd_prw, 'vk')) {
                $unit = 'vk';
            } elseif (in_array($data->kode_paramedis, data_petugas_vk_ponek()) || in_array(strtolower($kd_bangsal), array_map('strtolower', data_bangsal_vk()))) {
                $unit = 'vk';
            } elseif (str_contains($kd_bangsal, 'nicu') || str_contains($kd_prw, 'nicu')) {
                $unit = 'nicu';
            } elseif (str_contains($kd_bangsal, 'icu') || str_contains($kd_prw, 'icu')) {
                $unit = 'icu';
                // dd($data);
            }
            if (in_array(strtolower($kd_bangsal), array_map('strtolower', data_bangsal_vk()))) {
                $unit_bangsal = 'VK';
            } else if (str_contains($kd_bangsal, 'nicu')) {
                $unit_bangsal = 'nicu';
            } else if (str_contains($kd_bangsal, 'icu')) {
                $unit_bangsal = 'icu';
            } else {
                $unit_bangsal = 'ranap';
            }

            if ($kd_ruang) {

                if (in_array(strtolower($kd_ruang), array_map('strtolower', data_bangsal_vk()))) {
                    $ruang_tindakan = 'vk';
                } else if (str_contains($kd_ruang, 'nicu')) {
                    $ruang_tindakan = 'nicu';
                } else if (str_contains($kd_ruang, 'icu')) {
                    $ruang_tindakan = 'icu';
                } else {
                    $ruang_tindakan = 'ranap';
                }
            } else {
                // dd($kd_ruang, $unit_bangsal);
                $ruang_tindakan = $unit_bangsal;
            }

            if (($unit != 'cssd') && ($unit != 'gizi')) {
                if (($unit == 'ranap') && ($ruang_tindakan != 'ranap')) {
                    $unit = $ruang_tindakan;
                } else if (($unit == 'VK') && ($ruang_tindakan != 'VK')) {
                    $unit = $ruang_tindakan;
                }
            }

            // Masukkan data ke unit
            $data_unit[$unit][] = $data;
            // Tambahkan total langsung (hemat looping kedua)
            $totals_per_unit[$unit] += (float) $data->biaya_rawat;
        }
        // dd($totals_per_unit);
        return $totals_per_unit;
    }
}

if (! function_exists('get_map_poli')) {
    function get_map_poli($data_tindakan, $nm_poli_reg, $data_rujukan)
    {
        $polis = [
            "POLI ANAK"           => 0,
            "POLI BEDAH"          => 0,
            "POLI PENYAKIT DALAM" => 0,
            "POLI GIGI"           => 0,
            "IGD"                 => 0,
            "PONEK"               => 0,
            "POLI KESEHATAN JIWA" => 0,
            "POLI NYERI"          => 0,
            "POLI KANDUNGAN"      => 0,
            // "POLI PATALOGI KLINIK" => 0,
            "POLI RADIOLOGI"      => 0,
            // "UMUM"                 => 0,
            "POLI UMUM"           => 0,
            "POLI UROLOGI"        => 0,
        ];
        $unit = $nm_poli_reg;
        foreach ($data_tindakan as $data) {
            $poli = get_map_dokter_poli($data->kd_dokter);
            if ($poli) {
                $unit = $poli;
            }
            if ((! empty($data_rujukan))) {

                if (! $data->kd_dokter) {
                    // if ($counter_paramedis != 0 && $counter_paramedis <= count($data_rujukan)) {
                    //     // dump($data_rujukan);
                    //     $data_poli = $data_rujukan[($counter_paramedis - 1)];
                    //     $unit      = $data_poli->nm_poli;
                    //     // dump($data_rujukan);
                    // }
                    $paramedis_poli = get_map_paramedis_poli($data->kode_paramedis);
                    if ($paramedis_poli) {
                        $unit = $paramedis_poli;
                    }
                    // dump($data_rujukan, $data);
                    // $counter_paramedis += 1;
                }
            }

            if (in_array($data->kode_paramedis, data_petugas_vk_ponek())) {
                $unit = "PONEK";
            }
            $polis[$unit] += $data->biaya_rawat;
        }

        return $polis;
    }
}

if (! function_exists('get_ralan_paramedis')) {
    function get_ralan_paramedis($no_rawat)
    {
        $sql = "
            SELECT
                IFNULL(SUM(rawat_jl_pr.biaya_rawat),0) AS total_biaya, MAX(rawat_jl_pr.nip) AS kode_paramedis
            FROM reg_periksa
            INNER JOIN rawat_jl_pr ON rawat_jl_pr.no_rawat = reg_periksa.no_rawat
            WHERE reg_periksa.no_rawat = ?
        ";

        $data     = DB::selectOne($sql, [$no_rawat]);
        $is_ponek = false;
        if (in_array($data->kode_paramedis, data_petugas_vk_ponek())) {
            $is_ponek = true;
        }
        $result = [
            'total'    => $data->total_biaya,
            'is_ponek' => $is_ponek,
        ];
        return $result;
    }
}

if (! function_exists('get_ralan_dokter')) {
    function get_ralan_dokter($no_rawat)
    {
        $sql = "
            SELECT
                IFNULL(SUM(rawat_jl_dr.biaya_rawat),0) AS total_biaya
            FROM reg_periksa
            INNER JOIN rawat_jl_dr ON reg_periksa.no_rawat = rawat_jl_dr.no_rawat
            WHERE reg_periksa.no_rawat = ?
        ";

        $total = DB::selectOne($sql, [$no_rawat]);

        return $total->total_biaya;
    }
}

if (! function_exists('get_ralan_dokter_paramedis')) {
    function get_ralan_dokter_paramedis($no_rawat)
    {
        $data = DB::selectOne("
            SELECT IFNULL(SUM(rawat_jl_drpr.biaya_rawat),0) AS total_biaya, MAX(rawat_jl_drpr.nip) AS kode_paramedis
            FROM reg_periksa
            INNER JOIN rawat_jl_drpr ON rawat_jl_drpr.no_rawat=reg_periksa.no_rawat
            WHERE reg_periksa.no_rawat = ?
        ", [$no_rawat]);
        $is_ponek = false;
        if (in_array($data->kode_paramedis, data_petugas_vk_ponek())) {
            $is_ponek = true;
        }
        $result = [
            'total'    => $data->total_biaya,
            'is_ponek' => $is_ponek,
        ];
        return $result;
    }
}
if (! function_exists('get_ralan_batch')) {
    function get_ralan_batch($no_rawat)
    {
        $query = "
             SELECT
            r.no_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            d.nm_dokter,
            d.kd_dokter,
            NULL AS nama_paramedis,
            NULL AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            NULL AS kd_bangsal,
            NULL AS ruang_tindakan
        FROM rawat_jl_dr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat = ?

        UNION ALL

        SELECT
            r.no_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            d.nm_dokter,
            d.kd_dokter,
            pt.nama AS nama_paramedis,
            pt.nip AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            NULL AS kd_bangsal,
            NULL AS ruang_tindakan
        FROM rawat_jl_drpr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN petugas pt ON pt.nip = r.nip
        LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat = ?

        UNION ALL

        SELECT
            r.no_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            NULL AS nm_dokter,
            NULL AS kd_dokter,
            pt.nama AS nama_paramedis,
            pt.nip AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            NULL AS kd_bangsal,
            NULL AS ruang_tindakan
        FROM rawat_jl_pr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN petugas pt ON pt.nip = r.nip
        LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat = ?;
        ";
        $data = DB::select($query, [$no_rawat, $no_rawat, $no_rawat]);

        // $data = DB::selectOne("
        //     SELECT IFNULL(SUM(rawat_jl_drpr.biaya_rawat),0) AS total_biaya, MAX(rawat_jl_drpr.nip) AS kode_paramedis
        //     FROM reg_periksa
        //     INNER JOIN rawat_jl_drpr ON rawat_jl_drpr.no_rawat=reg_periksa.no_rawat
        //     WHERE reg_periksa.no_rawat = ?
        // ", [$no_rawat]);
        // $is_ponek = false;
        // if (in_array($data->kode_paramedis, data_petugas_vk_ponek())) {
        //     $is_ponek = true;
        // }
        // $result = [
        //     'total'    => $data->total_biaya,
        //     'is_ponek' => $is_ponek,
        // ];
        return $data;
    }
}

if (! function_exists('get_operasi')) {
    function get_operasi($no_rawat)
    {
        $total = DB::selectOne("
        SELECT IFNULL(SUM(
            operasi.biayaoperator1 +
            operasi.biayaoperator2 +
            operasi.biayaoperator3 +
            operasi.biayaasisten_operator1 +
            operasi.biayaasisten_operator2 +
            operasi.biayaasisten_operator3 +
            operasi.biayainstrumen +
            operasi.biayadokter_anak +
            operasi.biayaperawaat_resusitas +
            operasi.biayadokter_anestesi +
            operasi.biayaasisten_anestesi +
            operasi.biayaasisten_anestesi2 +
            operasi.biayabidan +
            operasi.biayabidan2 +
            operasi.biayabidan3 +
            operasi.biayaperawat_luar +
            operasi.biaya_omloop +
            operasi.biaya_omloop2 +
            operasi.biaya_omloop3 +
            operasi.biaya_omloop4 +
            operasi.biaya_omloop5 +
            operasi.biaya_dokter_pjanak +
            operasi.biaya_dokter_umum +
            operasi.biayaalat +
            operasi.biayasewaok +
            operasi.akomodasi +
            operasi.bagian_rs +
            operasi.biayasarpras
        ),0) AS total_biaya
        FROM operasi
        INNER JOIN reg_periksa ON operasi.no_rawat=reg_periksa.no_rawat
        WHERE reg_periksa.no_rawat = ?
    ", [$no_rawat]);

        return $total->total_biaya;
    }
}

if (! function_exists('get_ranap_dokter')) {
    function get_ranap_dokter($no_rawat)
    {
        $data = DB::select("
        SELECT pasien.nm_pasien,r.biaya_rawat, jns_perawatan_inap.kd_jenis_prw, nm_perawatan, bangsal.kd_bangsal, NULL AS kode_paramedis,
        -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
        (
            SELECT bangsal.kd_bangsal
            FROM kamar_inap
            INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
            INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
            WHERE kamar_inap.no_rawat = r.no_rawat
            AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
                BETWEEN CONCAT(
                    IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                    ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
                )
                AND CONCAT(
                    IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                    ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                )
            LIMIT 1
        ) AS ruang_tindakan
        FROM reg_periksa
        INNER JOIN rawat_inap_dr r ON r.no_rawat=reg_periksa.no_rawat
        LEFT JOIN jns_perawatan_inap ON jns_perawatan_inap.kd_jenis_prw=r.kd_jenis_prw
        LEFT JOIN bangsal ON jns_perawatan_inap.kd_bangsal=bangsal.kd_bangsal
        INNER JOIN pasien ON pasien.no_rkm_medis=reg_periksa.no_rkm_medis
        WHERE reg_periksa.no_rawat = ?
    ", [$no_rawat]);

        return $data;
    }
}

if (! function_exists('get_ranap_paramedis')) {
    function get_ranap_paramedis($no_rawat)
    {
        //     $total = DB::selectOne("
        //     SELECT IFNULL(SUM(rawat_inap_pr.biaya_rawat),0) AS total_biaya
        //     FROM reg_periksa
        //     INNER JOIN rawat_inap_pr ON rawat_inap_pr.no_rawat=reg_periksa.no_rawat
        //     WHERE reg_periksa.no_rawat = ?
        // ", [$no_rawat]);

        //     return $total->total_biaya;
        $data = DB::select("
        SELECT pasien.nm_pasien, r.biaya_rawat, jns_perawatan_inap.kd_jenis_prw, nm_perawatan, bangsal.kd_bangsal, r.nip AS kode_paramedis,
        -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
        (
            SELECT bangsal.kd_bangsal
            FROM kamar_inap
            INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
            INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
            WHERE kamar_inap.no_rawat = r.no_rawat
            AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
                BETWEEN CONCAT(
                    IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                    ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
                )
                AND CONCAT(
                    IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                    ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                )
            LIMIT 1
        ) AS ruang_tindakan
        FROM reg_periksa
        INNER JOIN rawat_inap_pr r ON r.no_rawat=reg_periksa.no_rawat
        LEFT JOIN jns_perawatan_inap ON jns_perawatan_inap.kd_jenis_prw=r.kd_jenis_prw
        LEFT JOIN bangsal ON jns_perawatan_inap.kd_bangsal=bangsal.kd_bangsal
        INNER JOIN pasien ON pasien.no_rkm_medis=reg_periksa.no_rkm_medis
        WHERE reg_periksa.no_rawat = ?
    ", [$no_rawat]);
        return $data;
    }
}
if (! function_exists('get_ranap_dokter_paramedis')) {
    function get_ranap_dokter_paramedis($no_rawat)
    {
        $data = DB::select("
        SELECT pasien.nm_pasien, r.biaya_rawat, jns_perawatan_inap.kd_jenis_prw, nm_perawatan, bangsal.kd_bangsal, r.nip AS kode_paramedis,
        -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
        (
            SELECT bangsal.kd_bangsal
            FROM kamar_inap
            INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
            INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
            WHERE kamar_inap.no_rawat = r.no_rawat
            AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
                BETWEEN CONCAT(
                    IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                    ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
                )
                AND CONCAT(
                    IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                    ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                )
            LIMIT 1
        ) AS ruang_tindakan
        FROM reg_periksa
        INNER JOIN rawat_inap_drpr r ON r.no_rawat=reg_periksa.no_rawat
        LEFT JOIN jns_perawatan_inap ON jns_perawatan_inap.kd_jenis_prw=r.kd_jenis_prw
        LEFT JOIN bangsal ON jns_perawatan_inap.kd_bangsal=bangsal.kd_bangsal
        INNER JOIN pasien ON pasien.no_rkm_medis=reg_periksa.no_rkm_medis
        WHERE reg_periksa.no_rawat = ?
    ", [$no_rawat]);

        return $data;
    }
}
if (! function_exists('get_radiologi')) {
    function get_radiologi($no_rawat)
    {
        $total = DB::selectOne("
        SELECT IFNULL(SUM(periksa_radiologi.biaya),0) AS total_biaya
        FROM reg_periksa
        INNER JOIN periksa_radiologi ON periksa_radiologi.no_rawat=reg_periksa.no_rawat
        WHERE reg_periksa.no_rawat = ?
    ", [$no_rawat]);

        return $total->total_biaya;
    }
}
if (! function_exists('get_lab')) {
    function get_lab($no_rawat)
    {
        // daftar kode bank darah
        $kodeBankDarah = [
            '2025-LAB-00140',
            // tambahkan kode lain kalau ada
        ];

        // buat placeholder ? sesuai jumlah kode
        $placeholders = implode(',', array_fill(0, count($kodeBankDarah), '?'));

        // query 1 kali saja
        $sql = "
        SELECT
            IFNULL(SUM(CASE
                WHEN periksa_lab.kd_jenis_prw NOT IN ($placeholders)
                THEN periksa_lab.biaya ELSE 0 END), 0) AS total_lab,
            IFNULL(SUM(CASE
                WHEN periksa_lab.kd_jenis_prw IN ($placeholders)
                THEN periksa_lab.biaya ELSE 0 END), 0) AS total_bank_darah
        FROM reg_periksa
        INNER JOIN periksa_lab ON periksa_lab.no_rawat = reg_periksa.no_rawat
        WHERE reg_periksa.no_rawat = ?
    ";

        // parameter = kode bank darah (untuk NOT IN) + kode bank darah (untuk IN) + no_rawat
        $params = array_merge($kodeBankDarah, $kodeBankDarah, [$no_rawat]);

        $totals = DB::selectOne($sql, $params);

        return [
            'total_lab'        => $totals->total_lab,
            'total_bank_darah' => $totals->total_bank_darah,
        ];
    }
}
if (! function_exists('flattened')) {
    function flattened($data)
    {
        $allKeys   = [];
        $flattened = [];
        $flattened = array_map(function ($arr) {
            return array_flatten_dot($arr);
        }, $data);
        foreach ($flattened as $row) {
            $allKeys = array_merge($allKeys, array_keys($row));
        }
        $allKeys = array_unique($allKeys);
        return [
            'data'    => $flattened,
            'allKeys' => $allKeys,
        ];
    }
}
if (! function_exists('formatFlattened')) {
    /**
     * Format angka dalam array flatten sesuai style
     *
     * @param array $flattened
     * @param string $style 'dot' atau 'comma'
     * @param array $excludeKeys
     * @return array
     */
    function formatFlattened(array $flattened, string $style = 'dot', array $excludeKeys = [])
    {
        $result = $flattened;

        foreach ($result as &$row) {
            foreach ($row as $key => &$val) {
                if (is_numeric($val) && ! in_array($key, $excludeKeys)) {
                    if ($style === 'dot') {
                        // format Indonesia (1.000.000)
                        $val = number_format($val, 0, ',', '.');
                    } else {
                        // format English (1,000,000)
                        $val = number_format($val, 0, '.', ',');
                    }
                }
            }
        }
        unset($row);

        return $result;
    }
}

if (! function_exists('get_farmasi')) {
    function get_farmasi($no_rawat)
    {
        $pelayanan_kode = [
            'B00000850',
            'FAR000621',
            'FAR000446',
            'FAR00622',
            'FAR00623',
            'FAR00624',
        ];

        $pelayanan_list = "'" . implode("','", $pelayanan_kode) . "'";

        $totals = DB::selectOne("
    SELECT
        -- Total pelayanan & keuntungan pelayanan
        COALESCE(SUM(CASE WHEN databarang.kode_brng IN ($pelayanan_list) THEN detail_pemberian_obat.total ELSE 0 END), 0) AS total_pelayanan,
        COALESCE(SUM(CASE WHEN databarang.kode_brng IN ($pelayanan_list) THEN (detail_pemberian_obat.total - (detail_pemberian_obat.h_beli * detail_pemberian_obat.jml)) ELSE 0 END), 0) AS keuntungan_pelayanan,
        -- Total obat & keuntungan obat (selain kode pelayanan)
        COALESCE(SUM(CASE WHEN databarang.kode_brng NOT IN ($pelayanan_list) THEN detail_pemberian_obat.total ELSE 0 END), 0) AS total_obat,
        COALESCE(SUM(CASE WHEN databarang.kode_brng NOT IN ($pelayanan_list) THEN (detail_pemberian_obat.total - (detail_pemberian_obat.h_beli * detail_pemberian_obat.jml)) ELSE 0 END), 0) AS keuntungan_obat
    FROM detail_pemberian_obat
    INNER JOIN databarang ON detail_pemberian_obat.kode_brng = databarang.kode_brng
    INNER JOIN kodesatuan ON databarang.kode_sat = kodesatuan.kode_sat
    WHERE detail_pemberian_obat.no_rawat = ?
", [$no_rawat]);

        return [
            'jaspel_farmasi'  => (float) $totals->total_pelayanan + (float) $totals->keuntungan_pelayanan,
            'total_obat'      => (float) $totals->total_obat,
            'keuntungan_obat' => (float) $totals->keuntungan_obat,
        ];

    }
}
if (! function_exists('get_tarif_kamar')) {
    function get_tarif_kamar($no_rawat)
    {
        $data_kamar = DB::select("
        SELECT kamar_inap.ttl_biaya, kd_bangsal
        FROM kamar_inap
        INNER JOIN kamar ON kamar_inap.kd_kamar=kamar.kd_kamar
        WHERE kamar_inap.no_rawat = ?
    ", [$no_rawat]);
        $total = [
            'vk'    => 0,
            'nicu'  => 0,
            'icu'   => 0,
            'ranap' => 0,
        ];
        foreach ($data_kamar as $data) {
            $kd_bangsal = strtolower((string) $data->kd_bangsal);
            if (in_array(strtolower($kd_bangsal), array_map('strtolower', data_bangsal_vk()))) {
                $total['vk'] += $data->ttl_biaya;
            } else if (str_contains($kd_bangsal, 'nicu')) {
                $total['nicu'] += $data->ttl_biaya;
            } else if (str_contains($kd_bangsal, 'icu')) {
                $total['icu'] += $data->ttl_biaya;
            } else {
                $total['ranap'] += $data->ttl_biaya;
            }
        }
        return $total;
    }
}

if (! function_exists('get_data_detil_tindakan')) {
//     function get_data_detil_tindakan($data_range, $jns = 1)
//     {
//         // dd($jns);
//         // if ($jns == 'umum') {
//         //     $noRawats = collect($data_range)->pluck('no_rawat')->toArray();
//         // }
//         $noRawats = collect($data_range)->pluck('no_rawat')->toArray();
//         if (empty($noRawats)) {
//             return [];
//         }
//         // dd($data_range);
//         // dd($noRawats);
// // Ambil semua data tindakan dalam 1 batch
//         if ($jns == 1) {
//             $data_rawat = get_detil_tindakan_ranap_batch($noRawats);
//         } else if ($jns == 2) {
//             $data_rawat = get_detil_tindakan_ralan_batch($noRawats, false);
//         } else if ($jns == 3) {
//             $data_rawat = get_detil_tindakan_ralan_batch($noRawats, true);
//         } else {
//             $data_rawat = get_detil_tindakan_rawat_batch($noRawats);
//         }
//         // dd($data_rawat);
//         $data_operasi   = get_operasi_detil_batch($noRawats);
//         $data_radiologi = get_radiologi_detil_batch($noRawats);
//         $data_lab       = get_lab_detil_batch($noRawats);
//         $data_farmasi   = get_farmasi_detil_batch($noRawats);

// // 🔸 Grouping data berdasarkan no_rawat agar gak perlu array_filter di setiap loop
//         $groupByNoRawat = function ($data, $key = 'no_rawat') {
//             $grouped = [];
//             foreach ($data as $d) {
//                 $rawat             = is_array($d) ? $d[$key] : $d->$key;
//                 $grouped[$rawat][] = $d;
//             }
//             return $grouped;
//         };
//         // dump($data_rawat);
//         $group_rawat     = $groupByNoRawat($data_rawat);
//         $group_operasi   = $groupByNoRawat($data_operasi);
//         $group_radiologi = $groupByNoRawat($data_radiologi);
//         $group_lab       = $groupByNoRawat($data_lab);
//         // dd($group_rawat);
// // $data_farmasi tidak perlu di-group kalau get_data_farmasi_detil sudah handle filter

//         $hasil = [];

//         foreach ($data_range as $item) {
//             $no = $item->no_rawat;

//             $hasil = array_merge(
//                 $hasil,
//                 get_data_detil_unit($group_rawat[$no] ?? [], $item),
//                 get_data_radiologi_detil($group_radiologi[$no] ?? [], $item),
//                 get_data_lab_detil($group_lab[$no] ?? [], $item),
//                 get_data_farmasi_detil($data_farmasi, $item),
//                 $group_operasi[$no] ?? []
//             );

//         }
//         // dd($hasil);
//         // 🔸 Kosongkan kolom identitas pada baris berikutnya jika no_rawat sama
//         $lastNoRawat = null;
//         foreach ($hasil as $i => &$row) {
//             if ($row['no_rawat'] === $lastNoRawat) {
//                 $row['no_rawat']    = '';
//                 $row['mr']          = '';
//                 $row['nama_pasien'] = '';
//             } else {
//                 $lastNoRawat = $row['no_rawat'];
//             }
//         }
//         unset($row); // penting untuk menghindari reference bug
//                      // dd($hasil);
//         return $hasil;

//     }
    function get_data_detil_tindakan($data_range, $jns = 1, $tanggalAwal, $tanggalAkhir, $jaminan, $status_bayar)
    {
        // Helper: buat folder & bersihkan file lama
        $prepareOutputDir = function ($dir) {
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $files = glob("$dir/tindakan_*.json");
            if (count($files) >= 3) {
                usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));
                $toDelete = array_slice($files, 0, count($files) - 2);
                foreach ($toDelete as $f) {
                    @unlink($f);
                }

            }
        };

        // Helper: grupkan berdasarkan no_rawat
        $groupByNoRawat = function ($data, $key = 'no_rawat') {
            $grouped = [];
            foreach ($data as $d) {
                $rawat             = is_array($d) ? $d[$key] : $d->$key;
                $grouped[$rawat][] = $d;
            }
            return $grouped;
        };

        // Helper: tulis data JSON streaming agar hemat memori
        $writeJsonStream = function ($filepath, $dataIterator) {
            $handle = fopen($filepath, 'w');
            fwrite($handle, "[\n");

            $first       = true;
            $lastNoRawat = null;
            $total       = 0;

            foreach ($dataIterator as $rows) {
                foreach ($rows as &$row) {
                    if (($row['no_rawat'] ?? '') === $lastNoRawat) {
                        $row['no_rawat']    = '';
                        $row['mr']          = '';
                        $row['nama_pasien'] = '';
                    } else {
                        $lastNoRawat = $row['no_rawat'];
                    }
                }
                unset($row);

                foreach ($rows as $row) {
                    if (! $first) {
                        fwrite($handle, ",\n");
                    }

                    fwrite($handle, json_encode($row, JSON_UNESCAPED_UNICODE));
                    $first = false;
                    $total++;
                }
            }

            fwrite($handle, "\n]");
            fclose($handle);
            return $total;
        };

        // ========================================
        // ====== BAGIAN BPJS =====================
        // ========================================
        if ($jaminan === 'bpjs') {
            $noRawats = collect($data_range)->pluck('no_rawat')->toArray();
            if (empty($noRawats)) {
                return ['file' => null, 'total' => 0, 'message' => 'Tidak ada data.'];
            }

            // Buat folder public/json_output (bisa diakses lewat URL)
            $dir = storage_path('app/public/json_output');
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            // === 2. Hapus file lama jika lebih dari 4 ===
            $files = glob($dir . '/tindakan_*.json');
            if (count($files) >= 3) {
                // Urutkan berdasarkan waktu modifikasi (terlama dulu)
                usort($files, function ($a, $b) {
                    return filemtime($a) <=> filemtime($b);
                });

                // Hapus file paling lama (sisakan 3 yang terbaru)
                $toDelete = array_slice($files, 0, count($files) - 2);
                foreach ($toDelete as $f) {
                    @unlink($f);
                }
            }

            // Nama file unik
            $filename = 'tindakan_' . date('Ymd_His') . '.json';
            $filepath = $dir . '/' . $filename;

            // Mulai tulis JSON manual
            $handle = fopen($filepath, 'w');
            fwrite($handle, "[\n");

            // Ambil semua data batch
            $is_rawat_jalan = false;
            if ($jns == 1) {
                $data_rawat     = get_detil_tindakan_ranap_batch($noRawats);
                $is_rawat_jalan = false;
            } elseif ($jns == 2) {
                $data_rawat     = get_detil_tindakan_ralan_batch($noRawats, false);
                $is_rawat_jalan = true;
            } elseif ($jns == 3) {
                $data_rawat     = get_detil_tindakan_ralan_batch($noRawats, true);
                $is_rawat_jalan = true;
            } else {
                $data_rawat = get_detil_tindakan_rawat_batch($noRawats);
            }

            $data_operasi   = get_operasi_detil_batch($noRawats);
            $data_radiologi = get_radiologi_detil_batch($noRawats);
            $data_lab       = get_lab_detil_batch($noRawats);
            $data_farmasi   = get_farmasi_detil_batch($noRawats);
            $data_rujukan   = get_rujukan_internal_poli($noRawats);
            // dump($data_rujukan);
            // Helper untuk group by no_rawat
            $groupByNoRawat = function ($data, $key = 'no_rawat') {
                $grouped = [];
                foreach ($data as $d) {
                    $rawat             = is_array($d) ? $d[$key] : $d->$key;
                    $grouped[$rawat][] = $d;
                }
                return $grouped;
            };

            $group_rawat   = $groupByNoRawat($data_rawat);
            $group_rujukan = $groupByNoRawat($data_rujukan);
            // dump($group_rujukan);
            $group_operasi   = $groupByNoRawat($data_operasi);
            $group_radiologi = $groupByNoRawat($data_radiologi);
            $group_lab       = $groupByNoRawat($data_lab);
            $group_farmasi   = $groupByNoRawat($data_farmasi);

            $first       = true;
            $lastNoRawat = null;
            $totalRows   = 0;

            foreach ($data_range as $item) {
                $no = $item->no_rawat;

                $data = array_merge(
                    get_data_detil_unit($group_rawat[$no] ?? [], $item, $is_rawat_jalan, $group_rujukan[$no] ?? []),
                    get_data_radiologi_detil($group_radiologi[$no] ?? [], $item),
                    get_data_lab_detil($group_lab[$no] ?? [], $item),
                    get_data_farmasi_detil($group_farmasi[$no] ?? [], $item),
                    $group_operasi[$no] ?? []
                );

                foreach ($data as &$row) {
                    if ($row['no_rawat'] === $lastNoRawat) {
                        $row['no_rawat']    = '';
                        $row['mr']          = '';
                        $row['nama_pasien'] = '';
                    } else {
                        $lastNoRawat = $row['no_rawat'];
                    }
                }
                unset($row);

                foreach ($data as $row) {
                    if (! $first) {
                        fwrite($handle, ",\n");
                    }

                    fwrite($handle, json_encode($row, JSON_UNESCAPED_UNICODE));
                    $first = false;
                    $totalRows++;
                }
                unset($data);
            }

            fwrite($handle, "\n]");
            fclose($handle);
        }

        // ========================================
        // ====== BAGIAN NON BPJS ================
        // ========================================
        else {
            $noRawat_inap  = [];
            $noRawat_jalan = [];

            foreach ($data_range as $t) {
                $no_nota = $t->no_nota;
                $x       = DB::selectOne("SELECT no_nota FROM nota_inap WHERE no_rawat = ?", [$no_nota]);
                if ($x && ! empty($x->no_nota)) {
                    $noRawat_inap[] = $no_nota;
                } else {
                    $x = DB::selectOne("SELECT no_nota FROM nota_jalan WHERE no_rawat = ?", [$no_nota]);
                    if ($x && ! empty($x->no_nota)) {
                        $noRawat_jalan[] = $no_nota;
                    }

                }
            }

            $noRawat_inap  = $noRawat_inap ?: ['-'];
            $noRawat_jalan = $noRawat_jalan ?: ['-'];
            // dump($noRawat_inap);
            // dump($noRawat_jalan);
            $makeQuery = function ($status, $placeholders) {
                return "
                SELECT
                    '$status' AS status_rawat,
                    pasien.nm_pasien,
                    reg_periksa.no_rawat,
                    reg_periksa.no_rkm_medis,
                    dokter_reg.kd_dokter AS reg_kd_dokter,
                    dokter_reg.nm_dokter AS reg_nm_dokter,
                    COALESCE(dokter_dpjp.kd_dokter, dokter_reg.kd_dokter) AS kd_dokter,
                    COALESCE(dokter_dpjp.nm_dokter, dokter_reg.nm_dokter) AS nm_dokter,
                    penjab.png_jawab AS jaminan,
                    poliklinik.nm_poli AS layanan_asal
                FROM reg_periksa
                INNER JOIN dokter AS dokter_reg ON reg_periksa.kd_dokter = dokter_reg.kd_dokter
                LEFT JOIN (
                    SELECT no_rawat, MIN(kd_dokter) AS kd_dokter FROM dpjp_ranap GROUP BY no_rawat
                ) AS dpjp_pertama ON dpjp_pertama.no_rawat = reg_periksa.no_rawat
                LEFT JOIN dokter AS dokter_dpjp ON dpjp_pertama.kd_dokter = dokter_dpjp.kd_dokter
                INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj
                INNER JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli
                INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
                WHERE reg_periksa.no_rawat IN ($placeholders)
            ";
            };

            $placeholders_ranap = implode(',', array_fill(0, count($noRawat_inap), '?'));
            $placeholders_ralan = implode(',', array_fill(0, count($noRawat_jalan), '?'));

            $data_ranap = DB::select($makeQuery('RANAP', $placeholders_ranap), $noRawat_inap);
            $data_ralan = DB::select($makeQuery('RALAN', $placeholders_ralan), $noRawat_jalan);
            $data_final = array_merge($data_ranap, $data_ralan);

            $noRawats = collect($data_final)->pluck('no_rawat')->toArray();
            if (empty($noRawats)) {
                return ['file' => null, 'total' => 0, 'message' => 'Tidak ada data.'];
            }

            $dir = storage_path('app/public/json_output');
            $prepareOutputDir($dir);
            $filename = 'tindakan_' . date('Ymd_His') . '.json';
            $filepath = "$dir/$filename";

            $data_rawat = array_merge(
                get_detil_tindakan_ranap_batch($noRawats),
                get_detil_tindakan_ralan_batch($noRawats)
            );

            $datasets = [
                'operasi'   => get_operasi_detil_batch($noRawats),
                'radiologi' => get_radiologi_detil_batch($noRawats),
                'lab'       => get_lab_detil_batch($noRawats),
                'farmasi'   => get_farmasi_detil_batch($noRawats),
                'rujukan'   => get_rujukan_internal_poli($noRawats),
            ];

            $groups = [];
            foreach ($datasets as $key => $data) {
                $groups[$key] = $groupByNoRawat($data);
            }
            $groups['rawat'] = $groupByNoRawat($data_rawat);

            $iterator = [];
            foreach ($data_final as $item) {
                $no         = $item->no_rawat;
                $iterator[] = array_merge(
                    get_data_detil_unit($groups['rawat'][$no] ?? [], $item, false, $groups['rujukan'][$no] ?? []),
                    get_data_radiologi_detil($groups['radiologi'][$no] ?? [], $item),
                    get_data_lab_detil($groups['lab'][$no] ?? [], $item),
                    get_data_farmasi_detil($groups['farmasi'][$no] ?? [], $item),
                    $groups['operasi'][$no] ?? []
                );
            }

            $totalRows = $writeJsonStream($filepath, $iterator);
        }

        return [
            'file'    => $filepath,
            'total'   => $totalRows,
            'message' => 'Data disimpan ke JSON agar tidak memakan memori besar',
        ];
    }

}

if (! function_exists('get_farmasi_detil_batch')) {
    function get_farmasi_detil_batch($noRawats)
    {
        if (empty($noRawats)) {
            return [];
        }

        $in = implode(',', array_fill(0, count($noRawats), '?'));
        // dump($noRawats);
        // dd($in);
        //     $query = "
        //     SELECT
        //         ro.no_rawat,
        //         ro.tgl_perawatan,
        //         ro.jam,
        //         ro.kd_dokter,
        //         dokter.nm_dokter,
        //         dpo.kode_brng,
        //         dpo.h_beli,
        //         dpo.jml,
        //         db.nama_brng,
        //         dpo.total,
        //         IFNULL(
        //     (
        //         -- Prioritas 1: kamar saat obat diberikan
        //         SELECT bangsal.kd_bangsal
        //         FROM kamar_inap
        //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
        //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
        //         WHERE kamar_inap.no_rawat = ro.no_rawat
        //         AND CONCAT(ro.tgl_peresepan, ' ', ro.jam_peresepan)
        //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
        //                 AND CONCAT(
        //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
        //                     ' ',
        //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
        //                 )
        //         LIMIT 1
        //     ),
        //     IFNULL(
        //         (
        //         -- Prioritas 2: kamar pertama setelah waktu obat
        //         SELECT bangsal.kd_bangsal
        //         FROM kamar_inap
        //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
        //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
        //         WHERE kamar_inap.no_rawat = ro.no_rawat
        //             AND CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
        //                 > CONCAT(ro.tgl_peresepan, ' ', ro.jam_peresepan)
        //         ORDER BY CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')) ASC
        //         LIMIT 1
        //         ),
        //         (
        //         -- Prioritas 3: kamar terakhir sebelum waktu obat
        //         SELECT bangsal.kd_bangsal
        //         FROM kamar_inap
        //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
        //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
        //         WHERE kamar_inap.no_rawat = ro.no_rawat
        //             AND CONCAT(
        //                 IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), kamar_inap.tgl_masuk),
        //                 ' ',
        //                 IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
        //                 )
        //                 < CONCAT(ro.tgl_peresepan, ' ', ro.jam_peresepan)
        //         ORDER BY CONCAT(
        //                 IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), kamar_inap.tgl_masuk),
        //                 ' ',
        //                 IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
        //                 ) DESC
        //         LIMIT 1
        //         )
        //     )
        //     ) AS kd_bangsal
        //     FROM resep_obat ro
        //     INNER JOIN detail_pemberian_obat dpo
        //         ON ro.no_rawat = dpo.no_rawat
        //         AND ro.tgl_perawatan = dpo.tgl_perawatan
        //         AND ro.jam = dpo.jam
        //     INNER JOIN databarang db ON dpo.kode_brng = db.kode_brng
        //     INNER JOIN dokter ON ro.kd_dokter = dokter.kd_dokter
        //     WHERE dpo.no_rawat IN ($in)
        // ";
        $query = "
                    SELECT
                    ro.no_rawat,
                    ro.tgl_perawatan,
                    ro.jam,
                    ro.kd_dokter,
                    dokter.nm_dokter,
                    dpo.kode_brng,
                    dpo.h_beli,
                    dpo.jml,
                    COALESCE(db.nama_brng, 'Data tidak ditemukan') AS nama_brng,
                    dpo.total,
                    IFNULL(
                        (
                            -- 🩺 Prioritas 1: kamar saat obat diberikan
                            SELECT bangsal.kd_bangsal
                            FROM kamar_inap
                            INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                            INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                            WHERE kamar_inap.no_rawat = ro.no_rawat
                            AND CONCAT(ro.tgl_peresepan, ' ', ro.jam_peresepan)
                                BETWEEN CONCAT(
                                    IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                                    ' ',
                                    IFNULL(kamar_inap.jam_masuk, '00:00:00')
                                )
                                AND CONCAT(
                                    IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                                    ' ',
                                    IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                                )
                            LIMIT 1
                        ),
                        IFNULL(
                            (
                                -- 🕓 Prioritas 2: kamar pertama setelah waktu obat
                                SELECT bangsal.kd_bangsal
                                FROM kamar_inap
                                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                                WHERE kamar_inap.no_rawat = ro.no_rawat
                                AND CONCAT(
                                    IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                                    ' ',
                                    IFNULL(kamar_inap.jam_masuk, '00:00:00')
                                ) > CONCAT(ro.tgl_peresepan, ' ', ro.jam_peresepan)
                                ORDER BY CONCAT(
                                    IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                                    ' ',
                                    IFNULL(kamar_inap.jam_masuk, '00:00:00')
                                ) ASC
                                LIMIT 1
                            ),
                            (
                                -- 🕘 Prioritas 3: kamar terakhir sebelum waktu obat
                                SELECT bangsal.kd_bangsal
                                FROM kamar_inap
                                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                                WHERE kamar_inap.no_rawat = ro.no_rawat
                                AND CONCAT(
                                    IFNULL(NULLIF(CAST(
                                        IFNULL(kamar_inap.tgl_keluar, kamar_inap.tgl_masuk) AS CHAR
                                    ), '0000-00-00'), '1900-01-01'),
                                    ' ',
                                    IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                                ) < CONCAT(ro.tgl_peresepan, ' ', ro.jam_peresepan)
                                ORDER BY CONCAT(
                                    IFNULL(NULLIF(CAST(
                                        IFNULL(kamar_inap.tgl_keluar, kamar_inap.tgl_masuk) AS CHAR
                                    ), '0000-00-00'), '1900-01-01'),
                                    ' ',
                                    IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                                ) DESC
                                LIMIT 1
                            )
                        )
                    ) AS kd_bangsal
                FROM resep_obat ro
                INNER JOIN detail_pemberian_obat dpo
                    ON ro.no_rawat = dpo.no_rawat
                AND ro.tgl_perawatan = dpo.tgl_perawatan
                AND ro.jam = dpo.jam
                LEFT JOIN databarang db ON dpo.kode_brng = db.kode_brng
                INNER JOIN dokter ON ro.kd_dokter = dokter.kd_dokter
                WHERE dpo.no_rawat IN ($in);

";

        return DB::select($query, $noRawats);
    }

}
if (! function_exists('get_data_farmasi_detil')) {
    function get_data_farmasi_detil($data_tindakan, $item)
    {
        $pelayanan_kode = [
            'B00000850',
            'FAR000621',
            'FAR000446',
            'FAR00622',
            'FAR00623',
            'FAR00624',
        ];
        $hasil = [];
        foreach ($data_tindakan as $data) {
            $nama_dokter = in_array($data->kode_brng, $pelayanan_kode) ? '' : $data->nm_dokter;

            $margin = in_array($data->kode_brng, $pelayanan_kode) ? '' : ($data->total - ($data->h_beli * $data->jml));
            if (in_array($data->kode_brng, $pelayanan_kode)) {
                $nama_dokter = $data->nm_dokter;
                $kd_bangsal  = strtolower((string) $data->kd_bangsal);
                if (in_array(strtolower($kd_bangsal), array_map('strtolower', data_bangsal_vk()))) {
                    $unit = 'VK';
                } else if (str_contains($kd_bangsal, 'nicu')) {
                    $unit = 'NICU';
                } else if (str_contains($kd_bangsal, 'icu')) {
                    $unit = 'ICU';
                } else {
                    $unit = 'RANAP';
                }
                $hasil[] = [
                    "waktu"              => $data->tgl_perawatan . " " . $data->jam,
                    "no_rawat"           => $item->no_rawat,
                    "mr"                 => $item->no_rkm_medis,
                    "nama_pasien"        => $item->nm_pasien,
                    "layanan_asal"       => $item->layanan_asal,
                    "jaminan"            => $item->jaminan,
                    "registrasi_kd_dpjp" => $item->reg_kd_dokter,
                    "registrasi_dpjp"    => $item->reg_nm_dokter,
                    "kd_dpjp"            => $item->kd_dokter,
                    "dpjp"               => $item->nm_dokter,
                    "kode"               => $data->kode_brng,
                    "keterangan"         => $data->nama_brng,
                    "layanan"            => "FARMASI",
                    // "bangsal"            => "",
                    "ruang_tindakan"     => $unit,
                    // "kd_bangsal"         => $kd_bangsal,
                    "dokter_pelaksana"   => $nama_dokter,
                    "dokter_operator"    => "",
                    "asisten_operator"   => "",
                    "dokter_anestesi"    => "",
                    "asisten_anestesi"   => "",
                    "dokter_anak"        => "",
                    "instrumen"          => "",
                    "paramedis"          => "",
                    "margin_obat"        => $margin,
                    "total"              => $data->total,
                ];
            }

        }
        // dump($hasil);
        return $hasil;
    }
}
if (! function_exists('get_lab_detil_batch')) {
    function get_lab_detil_batch($noRawats)
    {
        if (empty($noRawats)) {
            return [];
        }

        $in = implode(',', array_fill(0, count($noRawats), '?'));

        $query = "
        SELECT
            periksa_lab.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            periksa_lab.kd_jenis_prw,
            COALESCE(jns_perawatan_lab.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            periksa_lab.kd_dokter,
            COALESCE(dokter.nm_dokter, 'Data tidak ditemukan') AS nm_dokter,
            periksa_lab.nip,
            COALESCE(petugas.nama, 'Data tidak ditemukan') AS nama,
            periksa_lab.dokter_perujuk,
            COALESCE(perujuk.nm_dokter, 'Data tidak ditemukan') AS perujuk,
            periksa_lab.tgl_periksa,
            periksa_lab.jam,
            penjab.png_jawab,
            periksa_lab.bagian_rs,
            periksa_lab.bhp,
            periksa_lab.tarif_perujuk,
            periksa_lab.tarif_tindakan_dokter,
            periksa_lab.tarif_tindakan_petugas,
            periksa_lab.kso,
            periksa_lab.menejemen,
            periksa_lab.biaya,
            IF(
                periksa_lab.status = 'Ralan',
                (SELECT nm_poli
                FROM poliklinik
                WHERE poliklinik.kd_poli = reg_periksa.kd_poli),
                (SELECT bangsal.nm_bangsal
                FROM kamar_inap
                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                WHERE kamar_inap.no_rawat = periksa_lab.no_rawat
                LIMIT 1)
            ) AS ruangan
        FROM periksa_lab
        INNER JOIN reg_periksa ON periksa_lab.no_rawat = reg_periksa.no_rawat
        INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
        LEFT JOIN dokter ON periksa_lab.kd_dokter = dokter.kd_dokter
        LEFT JOIN dokter AS perujuk ON periksa_lab.dokter_perujuk = perujuk.kd_dokter
        LEFT JOIN petugas ON periksa_lab.nip = petugas.nip
        INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj
        LEFT JOIN jns_perawatan_lab ON periksa_lab.kd_jenis_prw = jns_perawatan_lab.kd_jenis_prw
        WHERE periksa_lab.no_rawat IN ($in);

    ";

        return DB::select($query, $noRawats);
    }

}
if (! function_exists('get_data_lab_detil')) {
    function get_data_lab_detil($data_tindakan, $item)
    {
        $kodeBankDarah = [
            '2025-LAB-00140',
            // tambahkan kode lain kalau ada
        ];
        $hasil = [];
        foreach ($data_tindakan as $data) {
            $layanan = in_array($data->kd_jenis_prw, $kodeBankDarah) ? 'BANK DARAH' : 'LABORATORIUM';

            $hasil[] = [
                "waktu"              => $data->tgl_periksa . " " . $data->jam,
                "no_rawat"           => $item->no_rawat,
                "mr"                 => $item->no_rkm_medis,
                "nama_pasien"        => $item->nm_pasien,
                "layanan_asal"       => $item->layanan_asal,
                "jaminan"            => $item->jaminan,
                "registrasi_kd_dpjp" => $item->reg_kd_dokter,
                "registrasi_dpjp"    => $item->reg_nm_dokter,
                "kd_dpjp"            => $item->kd_dokter,
                "dpjp"               => $item->nm_dokter,
                "kode"               => $data->kd_jenis_prw,
                "keterangan"         => $data->nm_perawatan,
                "layanan"            => $layanan,
                // "bangsal"            => "",
                "ruang_tindakan"     => "",
                // "kd_bangsal"         => "",
                "dokter_pelaksana"   => isset($data->nm_dokter) ? $data->nm_dokter : "",
                "dokter_operator"    => "",
                "asisten_operator"   => "",
                "dokter_anestesi"    => "",
                "asisten_anestesi"   => "",
                "dokter_anak"        => "",
                "instrumen"          => "",
                "paramedis"          => isset($data->nama) ? $data->nama : "",
                "margin_obat"        => "",
                "total"              => $data->biaya,
            ];
        }
        return $hasil;
    }
}

if (! function_exists('get_detil_tindakan_rawat_batch')) {
    function get_detil_tindakan_rawat_batch($noRawats)
    {
        $in = implode(',', array_fill(0, count($noRawats), '?'));

        $query = "
        SELECT r.no_rawat, j.kd_jenis_prw, j.nm_perawatan, d.nm_dokter,d.kd_dokter, NULL as nama_paramedis,
               tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat, NULL as kd_bangsal
        FROM rawat_jl_dr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT r.no_rawat, j.kd_jenis_prw, j.nm_perawatan, d.nm_dokter,d.kd_dokter, pt.nama as nama_paramedis,
               tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat, NULL as kd_bangsal
        FROM rawat_jl_drpr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN petugas pt ON pt.nip = r.nip
        INNER JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT r.no_rawat, j.kd_jenis_prw, j.nm_perawatan, NULL as nm_dokter, pt.nama as nama_paramedis,
               tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat, NULL as kd_bangsal
        FROM rawat_jl_pr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN petugas pt ON pt.nip = r.nip
        INNER JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT r.no_rawat, j.kd_jenis_prw, j.nm_perawatan, d.nm_dokter,d.kd_dokter, NULL as nama_paramedis,
               tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat, b.kd_bangsal
        FROM rawat_inap_dr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT r.no_rawat, j.kd_jenis_prw, j.nm_perawatan, d.nm_dokter,d.kd_dokter, pt.nama as nama_paramedis,
               tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat, b.kd_bangsal
        FROM rawat_inap_drpr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN petugas pt ON pt.nip = r.nip
        INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT r.no_rawat, j.kd_jenis_prw, j.nm_perawatan, NULL as nm_dokter, pt.nama as nama_paramedis,
               tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat, b.kd_bangsal
        FROM rawat_inap_pr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN petugas pt ON pt.nip = r.nip
        INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)
    ";

        // Karena ada 6 UNION, ulang parameter 6x
        $bindings = array_merge(
            $noRawats,
            $noRawats,
            $noRawats,
            $noRawats,
            $noRawats,
            $noRawats
        );

        return DB::select($query, $bindings);
    }

}
if (! function_exists('get_klaim_rujukan_internal_poli')) {
    function get_klaim_rujukan_internal_poli($noRawat)
    {

        $query = "
            SELECT
            rujukan_internal_poli.no_rawat,
            poliklinik.nm_poli,
            reg_periksa.kd_dokter AS kd_dokter_reg,
            reg_periksa.kd_poli AS poli_reg,
            rujukan_internal_poli.kd_dokter AS kd_dokter_rujukan,
            rujukan_internal_poli.kd_poli AS poli_rujuk,
            reg_periksa.kd_pj,
            reg_periksa.tgl_registrasi,
            UPPER(
                CASE DAYOFWEEK(reg_periksa.tgl_registrasi)
                    WHEN 1 THEN 'MINGGU'
                    WHEN 2 THEN 'SENIN'
                    WHEN 3 THEN 'SELASA'
                    WHEN 4 THEN 'RABU'
                    WHEN 5 THEN 'KAMIS'
                    WHEN 6 THEN 'JUMAT'
                    WHEN 7 THEN 'SABTU'
                END
            ) AS hari,

            -- jadwal dokter utama (dari reg_periksa)
            jadwal_reg.jam_mulai AS jam_mulai_dokter_reg,
            jadwal_reg.jam_selesai AS jam_selesai_dokter_reg,

            -- jadwal dokter rujukan
            jadwal_rujuk.jam_mulai AS jam_mulai_dokter_rujuk,
            jadwal_rujuk.jam_selesai AS jam_selesai_dokter_rujuk

        FROM
            rujukan_internal_poli
        INNER JOIN
            reg_periksa
                ON rujukan_internal_poli.no_rawat = reg_periksa.no_rawat
        INNER JOIN poliklinik ON rujukan_internal_poli.kd_poli = poliklinik.kd_poli
        -- join jadwal dokter utama (dari reg_periksa)
        LEFT JOIN
            jadwal AS jadwal_reg
                ON jadwal_reg.kd_dokter = reg_periksa.kd_dokter
                AND UPPER(jadwal_reg.hari_kerja) = UPPER(
                    CASE DAYOFWEEK(reg_periksa.tgl_registrasi)
                        WHEN 1 THEN 'MINGGU'
                        WHEN 2 THEN 'SENIN'
                        WHEN 3 THEN 'SELASA'
                        WHEN 4 THEN 'RABU'
                        WHEN 5 THEN 'KAMIS'
                        WHEN 6 THEN 'JUMAT'
                        WHEN 7 THEN 'SABTU'
                    END
                )

        -- join jadwal dokter rujukan (dari rujukan_internal_poli)
        LEFT JOIN
            jadwal AS jadwal_rujuk
                ON jadwal_rujuk.kd_dokter = rujukan_internal_poli.kd_dokter
                AND UPPER(jadwal_rujuk.hari_kerja) = UPPER(
                    CASE DAYOFWEEK(reg_periksa.tgl_registrasi)
                        WHEN 1 THEN 'MINGGU'
                        WHEN 2 THEN 'SENIN'
                        WHEN 3 THEN 'SELASA'
                        WHEN 4 THEN 'RABU'
                        WHEN 5 THEN 'KAMIS'
                        WHEN 6 THEN 'JUMAT'
                        WHEN 7 THEN 'SABTU'
                    END
                )

            WHERE rujukan_internal_poli.no_rawat = ?
        ";

        return DB::select($query, [$noRawat]);

    }
}
if (! function_exists('get_rujukan_internal_poli')) {
    function get_rujukan_internal_poli($noRawats)
    {
        $in = implode(',', array_fill(0, count($noRawats), '?'));

        $query = "
            SELECT
            rujukan_internal_poli.no_rawat,
            poliklinik.nm_poli,
            reg_periksa.kd_dokter AS kd_dokter_reg,
            reg_periksa.kd_poli AS poli_reg,
            rujukan_internal_poli.kd_dokter AS kd_dokter_rujukan,
            rujukan_internal_poli.kd_poli AS poli_rujuk,
            reg_periksa.kd_pj,
            reg_periksa.tgl_registrasi,
            UPPER(
                CASE DAYOFWEEK(reg_periksa.tgl_registrasi)
                    WHEN 1 THEN 'MINGGU'
                    WHEN 2 THEN 'SENIN'
                    WHEN 3 THEN 'SELASA'
                    WHEN 4 THEN 'RABU'
                    WHEN 5 THEN 'KAMIS'
                    WHEN 6 THEN 'JUMAT'
                    WHEN 7 THEN 'SABTU'
                END
            ) AS hari,

            -- jadwal dokter utama (dari reg_periksa)
            jadwal_reg.jam_mulai AS jam_mulai_dokter_reg,
            jadwal_reg.jam_selesai AS jam_selesai_dokter_reg,

            -- jadwal dokter rujukan
            jadwal_rujuk.jam_mulai AS jam_mulai_dokter_rujuk,
            jadwal_rujuk.jam_selesai AS jam_selesai_dokter_rujuk

        FROM
            rujukan_internal_poli
        INNER JOIN
            reg_periksa
                ON rujukan_internal_poli.no_rawat = reg_periksa.no_rawat
        INNER JOIN poliklinik ON rujukan_internal_poli.kd_poli = poliklinik.kd_poli
        -- join jadwal dokter utama (dari reg_periksa)
        LEFT JOIN
            jadwal AS jadwal_reg
                ON jadwal_reg.kd_dokter = reg_periksa.kd_dokter
                AND UPPER(jadwal_reg.hari_kerja) = UPPER(
                    CASE DAYOFWEEK(reg_periksa.tgl_registrasi)
                        WHEN 1 THEN 'MINGGU'
                        WHEN 2 THEN 'SENIN'
                        WHEN 3 THEN 'SELASA'
                        WHEN 4 THEN 'RABU'
                        WHEN 5 THEN 'KAMIS'
                        WHEN 6 THEN 'JUMAT'
                        WHEN 7 THEN 'SABTU'
                    END
                )

        -- join jadwal dokter rujukan (dari rujukan_internal_poli)
        LEFT JOIN
            jadwal AS jadwal_rujuk
                ON jadwal_rujuk.kd_dokter = rujukan_internal_poli.kd_dokter
                AND UPPER(jadwal_rujuk.hari_kerja) = UPPER(
                    CASE DAYOFWEEK(reg_periksa.tgl_registrasi)
                        WHEN 1 THEN 'MINGGU'
                        WHEN 2 THEN 'SENIN'
                        WHEN 3 THEN 'SELASA'
                        WHEN 4 THEN 'RABU'
                        WHEN 5 THEN 'KAMIS'
                        WHEN 6 THEN 'JUMAT'
                        WHEN 7 THEN 'SABTU'
                    END
                )

            WHERE rujukan_internal_poli.no_rawat IN ($in)
        ";

        return DB::select($query, $noRawats);

    }
}
if (! function_exists('get_radiologi_detil_batch')) {
    function get_radiologi_detil_batch($noRawats)
    {
        $in = implode(',', array_fill(0, count($noRawats), '?'));

        $query = "
            SELECT
            periksa_radiologi.no_rawat,
            reg_periksa.no_rkm_medis,
            pasien.nm_pasien,
            periksa_radiologi.kd_jenis_prw,
            COALESCE(jns_perawatan_radiologi.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            periksa_radiologi.kd_dokter,
            dokter.nm_dokter,
            periksa_radiologi.nip,
            petugas.nama,
            periksa_radiologi.dokter_perujuk,
            perujuk.nm_dokter AS perujuk,
            periksa_radiologi.tgl_periksa,
            periksa_radiologi.jam,
            penjab.png_jawab,
            periksa_radiologi.biaya,
            IF(
                periksa_radiologi.status = 'Ralan',
                (SELECT nm_poli
                FROM poliklinik
                WHERE poliklinik.kd_poli = reg_periksa.kd_poli),
                (SELECT bangsal.nm_bangsal
                FROM kamar_inap
                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                WHERE kamar_inap.no_rawat = periksa_radiologi.no_rawat
                LIMIT 1)
            ) AS ruangan
        FROM periksa_radiologi
        INNER JOIN reg_periksa ON periksa_radiologi.no_rawat = reg_periksa.no_rawat
        INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
        INNER JOIN dokter ON periksa_radiologi.kd_dokter = dokter.kd_dokter
        INNER JOIN dokter AS perujuk ON periksa_radiologi.dokter_perujuk = perujuk.kd_dokter
        INNER JOIN petugas ON periksa_radiologi.nip = petugas.nip
        INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj
        LEFT JOIN jns_perawatan_radiologi
            ON periksa_radiologi.kd_jenis_prw = jns_perawatan_radiologi.kd_jenis_prw
        WHERE periksa_radiologi.no_rawat IN ($in);

        ";

        return DB::select($query, $noRawats);

    }
}
if (! function_exists('get_data_radiologi_detil')) {
    function get_data_radiologi_detil($data_tindakan, $item)
    {
        $hasil = [];
        foreach ($data_tindakan as $data) {
            $hasil[] = [
                "waktu"              => $data->tgl_periksa . " " . $data->jam,
                "no_rawat"           => $item->no_rawat,
                "mr"                 => $item->no_rkm_medis,
                "nama_pasien"        => $item->nm_pasien,
                "layanan_asal"       => $item->layanan_asal,
                "jaminan"            => $item->jaminan,
                "registrasi_kd_dpjp" => $item->reg_kd_dokter,
                "registrasi_dpjp"    => $item->reg_nm_dokter,
                "kd_dpjp"            => $item->kd_dokter,
                "dpjp"               => $item->nm_dokter,
                "kode"               => $data->kd_jenis_prw,
                "keterangan"         => $data->nm_perawatan,
                "layanan"            => "RADIOLOGI",
                // "bangsal"            => "",
                "ruang_tindakan"     => "",
                // "kd_bangsal"         => "",
                "dokter_pelaksana"   => isset($data->nm_dokter) ? $data->nm_dokter : "",
                "dokter_operator"    => "",
                "asisten_operator"   => "",
                "dokter_anestesi"    => "",
                "asisten_anestesi"   => "",
                "dokter_anak"        => "",
                "instrumen"          => "",
                "paramedis"          => isset($data->nama) ? $data->nama : "",
                "margin_obat"        => "",
                "total"              => $data->biaya,
            ];
        }
        return $hasil;
    }
}
if (! function_exists('get_detil_tindakan_ralan_batch')) {
    function get_detil_tindakan_ralan_batch($noRawats, $igd = false)
    {
        $in = implode(',', array_fill(0, count($noRawats), '?'));
        if ($igd) {
            $query = "
            SELECT
                (SELECT 'RALAN') AS tindakan_rawat,
                r.no_rawat,
                j.kd_jenis_prw,
                COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
                d.nm_dokter,
                d.kd_dokter,
                NULL AS nama_paramedis,
                NULL AS kode_paramedis,
                tgl_perawatan,
                jam_rawat,
                p.nm_pasien,
                biaya_rawat,
                NULL AS kd_bangsal,
                NULL AS ruang_tindakan
            FROM rawat_jl_dr r
            INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
            INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
            LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
            INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
            WHERE rp.kd_poli = 'IGDK' AND r.no_rawat IN ($in)

            UNION ALL

            SELECT
                (SELECT 'RALAN') AS tindakan_rawat,
                r.no_rawat,
                j.kd_jenis_prw,
                COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
                d.nm_dokter,
                d.kd_dokter,
                pt.nama AS nama_paramedis,
                pt.nip AS kode_paramedis,
                tgl_perawatan,
                jam_rawat,
                p.nm_pasien,
                biaya_rawat,
                NULL AS kd_bangsal,
                NULL AS ruang_tindakan
            FROM rawat_jl_drpr r
            INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
            INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
            INNER JOIN petugas pt ON pt.nip = r.nip
            LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
            INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
            WHERE rp.kd_poli = 'IGDK' AND r.no_rawat IN ($in)

            UNION ALL

            SELECT
                (SELECT 'RALAN') AS tindakan_rawat,
                r.no_rawat,
                j.kd_jenis_prw,
                COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
                NULL AS nm_dokter,
                NULL AS kd_dokter,
                pt.nama AS nama_paramedis,
                pt.nip AS kode_paramedis,
                tgl_perawatan,
                jam_rawat,
                p.nm_pasien,
                biaya_rawat,
                NULL AS kd_bangsal,
                NULL AS ruang_tindakan
            FROM rawat_jl_pr r
            INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
            INNER JOIN petugas pt ON pt.nip = r.nip
            LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
            INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
            WHERE rp.kd_poli = 'IGDK' AND r.no_rawat IN ($in);

        ";
        } else {
            $query = "
            SELECT
            (SELECT 'RALAN') AS tindakan_rawat,
                r.no_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            d.nm_dokter,
            d.kd_dokter,
            NULL AS nama_paramedis,
            NULL AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            NULL AS kd_bangsal,
            NULL AS ruang_tindakan
        FROM rawat_jl_dr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT
            (SELECT 'RALAN') AS tindakan_rawat,
            r.no_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            d.nm_dokter,
            d.kd_dokter,
            pt.nama AS nama_paramedis,
            pt.nip AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            NULL AS kd_bangsal,
            NULL AS ruang_tindakan
        FROM rawat_jl_drpr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN petugas pt ON pt.nip = r.nip
        LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT
            (SELECT 'RALAN') AS tindakan_rawat,
            r.no_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            NULL AS nm_dokter,
            NULL AS kd_dokter,
            pt.nama AS nama_paramedis,
            pt.nip AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            NULL AS kd_bangsal,
            NULL AS ruang_tindakan
        FROM rawat_jl_pr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN petugas pt ON pt.nip = r.nip
        LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in);

        ";
        }

        return DB::select($query, array_merge($noRawats, $noRawats, $noRawats));

    }
}
// if (! function_exists('get_detil_tindakan_ralan_batch')) {
//     function get_detil_tindakan_ralan_batch($noRawats, $igd = false)
//     {
//         $in = implode(',', array_fill(0, count($noRawats), '?'));
//         if ($igd) {
//             $query = "
//             SELECT
//                 r.no_rawat,
//                 j.kd_jenis_prw,
//                 COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//                 d.nm_dokter,
//                 d.kd_dokter,
//                 NULL AS nama_paramedis,
//                 NULL AS kode_paramedis,
//                 tgl_perawatan,
//                 jam_rawat,
//                 p.nm_pasien,
//                 biaya_rawat,
//                 NULL AS kd_bangsal,
//                 NULL AS ruang_tindakan
//             FROM rawat_jl_dr r
//             INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//             INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//             LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
//             INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//             WHERE rp.kd_poli = 'IGDK' AND r.no_rawat IN ($in)

//             UNION ALL

//             SELECT
//                 r.no_rawat,
//                 j.kd_jenis_prw,
//                 COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//                 d.nm_dokter,
//                 d.kd_dokter,
//                 pt.nama AS nama_paramedis,
//                 pt.nip AS kode_paramedis,
//                 tgl_perawatan,
//                 jam_rawat,
//                 p.nm_pasien,
//                 biaya_rawat,
//                 NULL AS kd_bangsal,
//                 NULL AS ruang_tindakan
//             FROM rawat_jl_drpr r
//             INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//             INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//             INNER JOIN petugas pt ON pt.nip = r.nip
//             LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
//             INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//             WHERE rp.kd_poli = 'IGDK' AND r.no_rawat IN ($in)

//             UNION ALL

//             SELECT
//                 r.no_rawat,
//                 j.kd_jenis_prw,
//                 COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//                 NULL AS nm_dokter,
//                 NULL AS kd_dokter,
//                 pt.nama AS nama_paramedis,
//                 pt.nip AS kode_paramedis,
//                 tgl_perawatan,
//                 jam_rawat,
//                 p.nm_pasien,
//                 biaya_rawat,
//                 NULL AS kd_bangsal,
//                 NULL AS ruang_tindakan
//             FROM rawat_jl_pr r
//             INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//             INNER JOIN petugas pt ON pt.nip = r.nip
//             LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
//             INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//             WHERE rp.kd_poli = 'IGDK' AND r.no_rawat IN ($in);

//         ";
//         } else {
//             $query = "
//             SELECT
//             r.no_rawat,
//             j.kd_jenis_prw,
//             COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//             d.nm_dokter,
//             d.kd_dokter,
//             NULL AS nama_paramedis,
//             NULL AS kode_paramedis,
//             tgl_perawatan,
//             jam_rawat,
//             p.nm_pasien,
//             biaya_rawat,
//             NULL AS kd_bangsal,
//             NULL AS ruang_tindakan
//         FROM rawat_jl_dr r
//         INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//         LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
//         INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         WHERE r.no_rawat IN ($in)

//         UNION ALL

//         SELECT
//             r.no_rawat,
//             j.kd_jenis_prw,
//             COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//             d.nm_dokter,
//             d.kd_dokter,
//             pt.nama AS nama_paramedis,
//             pt.nip AS kode_paramedis,
//             tgl_perawatan,
//             jam_rawat,
//             p.nm_pasien,
//             biaya_rawat,
//             NULL AS kd_bangsal,
//             NULL AS ruang_tindakan
//         FROM rawat_jl_drpr r
//         INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//         INNER JOIN petugas pt ON pt.nip = r.nip
//         LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
//         INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         WHERE r.no_rawat IN ($in)

//         UNION ALL

//         SELECT
//             r.no_rawat,
//             j.kd_jenis_prw,
//             COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//             NULL AS nm_dokter,
//             NULL AS kd_dokter,
//             pt.nama AS nama_paramedis,
//             pt.nip AS kode_paramedis,
//             tgl_perawatan,
//             jam_rawat,
//             p.nm_pasien,
//             biaya_rawat,
//             NULL AS kd_bangsal,
//             NULL AS ruang_tindakan
//         FROM rawat_jl_pr r
//         INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         INNER JOIN petugas pt ON pt.nip = r.nip
//         LEFT JOIN jns_perawatan j ON j.kd_jenis_prw = r.kd_jenis_prw
//         INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         WHERE r.no_rawat IN ($in);

//         ";
//         }

//         return DB::select($query, array_merge($noRawats, $noRawats, $noRawats));

//     }
// }
if (! function_exists('get_detil_tindakan_ranap_batch')) {
    function get_detil_tindakan_ranap_batch($noRawats)
    {
        $in = implode(',', array_fill(0, count($noRawats), '?'));

        // $query = "
        //     SELECT r.no_rawat, d.nm_dokter,d.kd_dokter, NULL as nama_paramedis,NULL as kode_paramedis,
        //            tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat,
        //            j.kd_jenis_prw, j.nm_perawatan, b.kd_bangsal,

        //     -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
        //     (
        //         SELECT bangsal.kd_bangsal
        //         FROM kamar_inap
        //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
        //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
        //         WHERE kamar_inap.no_rawat = r.no_rawat
        //         AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
        //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
        //                 AND CONCAT(
        //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
        //                     ' ',
        //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
        //                 )
        //         LIMIT 1
        //     ) AS ruang_tindakan
        //     FROM rawat_inap_dr r
        //     INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        //     INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        //     INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        //     INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        //     INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        //     WHERE r.no_rawat IN ($in)

        //     UNION ALL

        //     SELECT r.no_rawat, d.nm_dokter,d.kd_dokter, pt.nama as nama_paramedis,pt.nip as kode_paramedis,
        //            tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat,
        //            j.kd_jenis_prw, j.nm_perawatan, b.kd_bangsal,

        //     -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
        //     (
        //         SELECT bangsal.kd_bangsal
        //         FROM kamar_inap
        //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
        //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
        //         WHERE kamar_inap.no_rawat = r.no_rawat
        //         AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
        //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
        //                 AND CONCAT(
        //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
        //                     ' ',
        //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
        //                 )
        //         LIMIT 1
        //     ) AS ruang_tindakan
        //     FROM rawat_inap_drpr r
        //     INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        //     INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        //     INNER JOIN petugas pt ON pt.nip = r.nip
        //     INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        //     INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        //     INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        //     WHERE r.no_rawat IN ($in)

        //     UNION ALL

        //     SELECT r.no_rawat, NULL as nm_dokter, pt.nama as nama_paramedis,pt.nip as kode_paramedis,
        //            tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat,
        //            j.kd_jenis_prw, j.nm_perawatan, b.kd_bangsal,

        //     -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
        //     (
        //         SELECT bangsal.kd_bangsal
        //         FROM kamar_inap
        //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
        //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
        //         WHERE kamar_inap.no_rawat = r.no_rawat
        //         AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
        //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
        //                 AND CONCAT(
        //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
        //                     ' ',
        //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
        //                 )
        //         LIMIT 1
        //     ) AS ruang_tindakan
        //     FROM rawat_inap_pr r
        //     INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        //     INNER JOIN petugas pt ON pt.nip = r.nip
        //     INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        //     INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        //     INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        //     WHERE r.no_rawat IN ($in)
        // ";
        $query = "
            SELECT
            (SELECT 'RANAP') AS tindakan_rawat,
            r.no_rawat,
            d.nm_dokter,
            d.kd_dokter,
            NULL AS nama_paramedis,
            NULL AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            b.kd_bangsal,

            -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
            (
                SELECT bangsal.kd_bangsal
                FROM kamar_inap
                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                WHERE kamar_inap.no_rawat = r.no_rawat
                AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
                    BETWEEN CONCAT(
                        IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                        ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
                    )
                    AND CONCAT(
                        IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                        ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                    )
                LIMIT 1
            ) AS ruang_tindakan
        FROM rawat_inap_dr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        LEFT JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        LEFT JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT
            (SELECT 'RANAP') AS tindakan_rawat,
            r.no_rawat,
            d.nm_dokter,
            d.kd_dokter,
            pt.nama AS nama_paramedis,
            pt.nip AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            b.kd_bangsal,
            (
                SELECT bangsal.kd_bangsal
                FROM kamar_inap
                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                WHERE kamar_inap.no_rawat = r.no_rawat
                AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
                    BETWEEN CONCAT(
                        IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                        ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
                    )
                    AND CONCAT(
                        IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                        ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                    )
                LIMIT 1
            ) AS ruang_tindakan
        FROM rawat_inap_drpr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
        INNER JOIN petugas pt ON pt.nip = r.nip
        LEFT JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        LEFT JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in)

        UNION ALL

        SELECT
            (SELECT 'RANAP') AS tindakan_rawat,
            r.no_rawat,
            NULL AS nm_dokter,
            NULL AS kd_dokter,
            pt.nama AS nama_paramedis,
            pt.nip AS kode_paramedis,
            tgl_perawatan,
            jam_rawat,
            p.nm_pasien,
            biaya_rawat,
            j.kd_jenis_prw,
            COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
            b.kd_bangsal,
            (
                SELECT bangsal.kd_bangsal
                FROM kamar_inap
                INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                WHERE kamar_inap.no_rawat = r.no_rawat
                AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
                    BETWEEN CONCAT(
                        IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
                        ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
                    )
                    AND CONCAT(
                        IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
                        ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
                    )
                LIMIT 1
            ) AS ruang_tindakan
        FROM rawat_inap_pr r
        INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
        INNER JOIN petugas pt ON pt.nip = r.nip
        LEFT JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
        LEFT JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
        INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
        WHERE r.no_rawat IN ($in);

        ";

        return DB::select($query, array_merge($noRawats, $noRawats, $noRawats));
    }
}
// if (! function_exists('get_detil_tindakan_ranap_batch')) {
//     function get_detil_tindakan_ranap_batch($noRawats)
//     {
//         $in = implode(',', array_fill(0, count($noRawats), '?'));

//         // $query = "
//         //     SELECT r.no_rawat, d.nm_dokter,d.kd_dokter, NULL as nama_paramedis,NULL as kode_paramedis,
//         //            tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat,
//         //            j.kd_jenis_prw, j.nm_perawatan, b.kd_bangsal,

//         //     -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
//         //     (
//         //         SELECT bangsal.kd_bangsal
//         //         FROM kamar_inap
//         //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
//         //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
//         //         WHERE kamar_inap.no_rawat = r.no_rawat
//         //         AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
//         //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
//         //                 AND CONCAT(
//         //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
//         //                     ' ',
//         //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
//         //                 )
//         //         LIMIT 1
//         //     ) AS ruang_tindakan
//         //     FROM rawat_inap_dr r
//         //     INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         //     INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//         //     INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
//         //     INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
//         //     INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         //     WHERE r.no_rawat IN ($in)

//         //     UNION ALL

//         //     SELECT r.no_rawat, d.nm_dokter,d.kd_dokter, pt.nama as nama_paramedis,pt.nip as kode_paramedis,
//         //            tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat,
//         //            j.kd_jenis_prw, j.nm_perawatan, b.kd_bangsal,

//         //     -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
//         //     (
//         //         SELECT bangsal.kd_bangsal
//         //         FROM kamar_inap
//         //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
//         //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
//         //         WHERE kamar_inap.no_rawat = r.no_rawat
//         //         AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
//         //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
//         //                 AND CONCAT(
//         //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
//         //                     ' ',
//         //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
//         //                 )
//         //         LIMIT 1
//         //     ) AS ruang_tindakan
//         //     FROM rawat_inap_drpr r
//         //     INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         //     INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//         //     INNER JOIN petugas pt ON pt.nip = r.nip
//         //     INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
//         //     INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
//         //     INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         //     WHERE r.no_rawat IN ($in)

//         //     UNION ALL

//         //     SELECT r.no_rawat, NULL as nm_dokter, pt.nama as nama_paramedis,pt.nip as kode_paramedis,
//         //            tgl_perawatan, jam_rawat, p.nm_pasien, biaya_rawat,
//         //            j.kd_jenis_prw, j.nm_perawatan, b.kd_bangsal,

//         //     -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
//         //     (
//         //         SELECT bangsal.kd_bangsal
//         //         FROM kamar_inap
//         //         INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
//         //         INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
//         //         WHERE kamar_inap.no_rawat = r.no_rawat
//         //         AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
//         //             BETWEEN CONCAT(kamar_inap.tgl_masuk, ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00'))
//         //                 AND CONCAT(
//         //                     IFNULL(NULLIF(kamar_inap.tgl_keluar, '0000-00-00'), '9999-12-31'),
//         //                     ' ',
//         //                     IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
//         //                 )
//         //         LIMIT 1
//         //     ) AS ruang_tindakan
//         //     FROM rawat_inap_pr r
//         //     INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         //     INNER JOIN petugas pt ON pt.nip = r.nip
//         //     INNER JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
//         //     INNER JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
//         //     INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         //     WHERE r.no_rawat IN ($in)
//         // ";
//         $query = "
//             SELECT
//             r.no_rawat,
//             d.nm_dokter,
//             d.kd_dokter,
//             NULL AS nama_paramedis,
//             NULL AS kode_paramedis,
//             tgl_perawatan,
//             jam_rawat,
//             p.nm_pasien,
//             biaya_rawat,
//             j.kd_jenis_prw,
//             COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//             b.kd_bangsal,

//             -- 💡 Menentukan ruang (bangsal) berdasarkan waktu tindakan
//             (
//                 SELECT bangsal.kd_bangsal
//                 FROM kamar_inap
//                 INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
//                 INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
//                 WHERE kamar_inap.no_rawat = r.no_rawat
//                 AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
//                     BETWEEN CONCAT(
//                         IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
//                         ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
//                     )
//                     AND CONCAT(
//                         IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
//                         ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
//                     )
//                 LIMIT 1
//             ) AS ruang_tindakan
//         FROM rawat_inap_dr r
//         INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//         LEFT JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
//         LEFT JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
//         INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         WHERE r.no_rawat IN ($in)

//         UNION ALL

//         SELECT
//             r.no_rawat,
//             d.nm_dokter,
//             d.kd_dokter,
//             pt.nama AS nama_paramedis,
//             pt.nip AS kode_paramedis,
//             tgl_perawatan,
//             jam_rawat,
//             p.nm_pasien,
//             biaya_rawat,
//             j.kd_jenis_prw,
//             COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//             b.kd_bangsal,
//             (
//                 SELECT bangsal.kd_bangsal
//                 FROM kamar_inap
//                 INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
//                 INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
//                 WHERE kamar_inap.no_rawat = r.no_rawat
//                 AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
//                     BETWEEN CONCAT(
//                         IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
//                         ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
//                     )
//                     AND CONCAT(
//                         IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
//                         ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
//                     )
//                 LIMIT 1
//             ) AS ruang_tindakan
//         FROM rawat_inap_drpr r
//         INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         INNER JOIN dokter d ON d.kd_dokter = r.kd_dokter
//         INNER JOIN petugas pt ON pt.nip = r.nip
//         LEFT JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
//         LEFT JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
//         INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         WHERE r.no_rawat IN ($in)

//         UNION ALL

//         SELECT
//             r.no_rawat,
//             NULL AS nm_dokter,
//             NULL AS kd_dokter,
//             pt.nama AS nama_paramedis,
//             pt.nip AS kode_paramedis,
//             tgl_perawatan,
//             jam_rawat,
//             p.nm_pasien,
//             biaya_rawat,
//             j.kd_jenis_prw,
//             COALESCE(j.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
//             b.kd_bangsal,
//             (
//                 SELECT bangsal.kd_bangsal
//                 FROM kamar_inap
//                 INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
//                 INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
//                 WHERE kamar_inap.no_rawat = r.no_rawat
//                 AND CONCAT(r.tgl_perawatan, ' ', r.jam_rawat)
//                     BETWEEN CONCAT(
//                         IFNULL(NULLIF(CAST(kamar_inap.tgl_masuk AS CHAR), '0000-00-00'), '1900-01-01'),
//                         ' ', IFNULL(kamar_inap.jam_masuk, '00:00:00')
//                     )
//                     AND CONCAT(
//                         IFNULL(NULLIF(CAST(kamar_inap.tgl_keluar AS CHAR), '0000-00-00'), '9999-12-31'),
//                         ' ', IFNULL(NULLIF(kamar_inap.jam_keluar, '00:00:00'), '23:59:59')
//                     )
//                 LIMIT 1
//             ) AS ruang_tindakan
//         FROM rawat_inap_pr r
//         INNER JOIN reg_periksa rp ON rp.no_rawat = r.no_rawat
//         INNER JOIN petugas pt ON pt.nip = r.nip
//         LEFT JOIN jns_perawatan_inap j ON j.kd_jenis_prw = r.kd_jenis_prw
//         LEFT JOIN bangsal b ON j.kd_bangsal = b.kd_bangsal
//         INNER JOIN pasien p ON p.no_rkm_medis = rp.no_rkm_medis
//         WHERE r.no_rawat IN ($in);

//         ";

//         return DB::select($query, array_merge($noRawats, $noRawats, $noRawats));
//     }
// }
if (! function_exists('get_operasi_detil_batch')) {
    function get_operasi_detil_batch(array $noRawats)
    {
        if (empty($noRawats)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($noRawats), '?'));

        $rs = DB::select("
            SELECT
                operasi.no_rawat,
                reg_periksa.no_rkm_medis,
                pasien.nm_pasien,
                operasi.kode_paket,
                COALESCE(paket_operasi.nm_perawatan, 'Data tidak ditemukan') AS nm_perawatan,
                operasi.tgl_operasi,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal,
                dokter_reg.kd_dokter AS reg_kd_dokter,
                dokter_reg.nm_dokter AS reg_nm_dokter,

                -- ambil DPJP dari dpjp_ranap kalau ada, kalau tidak ambil dari reg_periksa
                COALESCE(dokter_dpjp.kd_dokter, dokter_reg.kd_dokter) AS kd_dpjp,
                COALESCE(dokter_dpjp.nm_dokter, dokter_reg.nm_dokter) AS nm_dpjp,

                operator1.nm_dokter AS operator1, operasi.biayaoperator1,
                operator2.nm_dokter AS operator2, operasi.biayaoperator2,
                operator3.nm_dokter AS operator3, operasi.biayaoperator3,

                asisten_operator1.nama AS asisten_operator1, operasi.biayaasisten_operator1,
                asisten_operator2.nama AS asisten_operator2, operasi.biayaasisten_operator2,
                asisten_operator3.nama AS asisten_operator3, operasi.biayaasisten_operator3,

                instrumen.nama AS instrumen, operasi.biayainstrumen,
                dokter_anak.nm_dokter AS dokter_anak, operasi.biayadokter_anak,
                perawaat_resusitas.nama AS perawaat_resusitas, operasi.biayaperawaat_resusitas,
                dokter_anestesi.nm_dokter AS dokter_anestesi, operasi.biayadokter_anestesi,
                asisten_anestesi.nama AS asisten_anestesi, operasi.biayaasisten_anestesi,

                (SELECT nama FROM petugas WHERE petugas.nip = operasi.asisten_anestesi2) AS asisten_anestesi2,
                operasi.biayaasisten_anestesi2,

                bidan.nama AS bidan, operasi.biayabidan,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.bidan2) AS bidan2, operasi.biayabidan2,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.bidan3) AS bidan3, operasi.biayabidan3,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.perawat_luar) AS perawat_luar, operasi.biayaperawat_luar,

                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop) AS omloop, operasi.biaya_omloop,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop2) AS omloop2, operasi.biaya_omloop2,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop3) AS omloop3, operasi.biaya_omloop3,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop4) AS omloop4, operasi.biaya_omloop4,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop5) AS omloop5, operasi.biaya_omloop5,

                (SELECT nm_dokter FROM dokter WHERE dokter.kd_dokter = operasi.dokter_pjanak) AS dokter_pjanak, operasi.biaya_dokter_pjanak,
                (SELECT nm_dokter FROM dokter WHERE dokter.kd_dokter = operasi.dokter_umum) AS dokter_umum, operasi.biaya_dokter_umum,

                IFNULL(
                    operasi.biayaoperator1 +
                    operasi.biayaoperator2 +
                    operasi.biayaoperator3 +
                    operasi.biayaasisten_operator1 +
                    operasi.biayaasisten_operator2 +
                    operasi.biayaasisten_operator3 +
                    operasi.biayainstrumen +
                    operasi.biayadokter_anak +
                    operasi.biayaperawaat_resusitas +
                    operasi.biayadokter_anestesi +
                    operasi.biayaasisten_anestesi +
                    operasi.biayaasisten_anestesi2 +
                    operasi.biayabidan +
                    operasi.biayabidan2 +
                    operasi.biayabidan3 +
                    operasi.biayaperawat_luar +
                    operasi.biaya_omloop +
                    operasi.biaya_omloop2 +
                    operasi.biaya_omloop3 +
                    operasi.biaya_omloop4 +
                    operasi.biaya_omloop5 +
                    operasi.biaya_dokter_pjanak +
                    operasi.biaya_dokter_umum +
                    operasi.biayaalat +
                    operasi.biayasewaok +
                    operasi.akomodasi +
                    operasi.bagian_rs +
                    operasi.biayasarpras
                , 0) AS total_biaya

            FROM operasi
            INNER JOIN reg_periksa ON operasi.no_rawat = reg_periksa.no_rawat
            INNER JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            LEFT JOIN paket_operasi ON operasi.kode_paket = paket_operasi.kode_paket
            INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj

            LEFT JOIN dpjp_ranap ON dpjp_ranap.no_rawat = reg_periksa.no_rawat
            LEFT JOIN dokter AS dokter_dpjp ON dokter_dpjp.kd_dokter = dpjp_ranap.kd_dokter
            INNER JOIN dokter AS dokter_reg ON dokter_reg.kd_dokter = reg_periksa.kd_dokter

            INNER JOIN dokter AS operator1 ON operator1.kd_dokter = operasi.operator1
            INNER JOIN dokter AS operator2 ON operator2.kd_dokter = operasi.operator2
            INNER JOIN dokter AS operator3 ON operator3.kd_dokter = operasi.operator3
            INNER JOIN dokter AS dokter_anak ON dokter_anak.kd_dokter = operasi.dokter_anak
            INNER JOIN dokter AS dokter_anestesi ON dokter_anestesi.kd_dokter = operasi.dokter_anestesi
            INNER JOIN petugas AS asisten_operator1 ON asisten_operator1.nip = operasi.asisten_operator1
            INNER JOIN petugas AS asisten_operator2 ON asisten_operator2.nip = operasi.asisten_operator2
            INNER JOIN petugas AS asisten_operator3 ON asisten_operator3.nip = operasi.asisten_operator3
            INNER JOIN petugas AS asisten_anestesi ON asisten_anestesi.nip = operasi.asisten_anestesi
            INNER JOIN petugas AS bidan ON bidan.nip = operasi.bidan
            INNER JOIN petugas AS instrumen ON instrumen.nip = operasi.instrumen
            INNER JOIN petugas AS perawaat_resusitas ON perawaat_resusitas.nip = operasi.perawaat_resusitas

            WHERE operasi.no_rawat IN ($placeholders)
        ", $noRawats);

        // Mapping hasil seperti fungsi awal
        $hasil = [];
        foreach ($rs as $data) {
            $nama_operator2         = $data->operator2 == '-' ? "" : ", " . $data->operator2;
            $nama_operator3         = $data->operator3 == '-' ? "" : ", " . $data->operator3;
            $nama_asisten_operator2 = $data->asisten_operator2 == '-' ? "" : ", " . $data->asisten_operator2;
            $nama_asisten_operator3 = $data->asisten_operator3 == '-' ? "" : ", " . $data->asisten_operator3;
            $nama_asisten_anestesi2 = $data->asisten_anestesi2 == '-' ? "" : ", " . $data->asisten_anestesi2;

            $hasil[] = [
                "waktu"              => $data->tgl_operasi,
                "no_rawat"           => $data->no_rawat,
                "mr"                 => $data->no_rkm_medis,
                "nama_pasien"        => $data->nm_pasien,
                "layanan_asal"       => $data->layanan_asal,
                "jaminan"            => $data->jaminan,
                "registrasi_kd_dpjp" => $data->reg_kd_dokter,
                "registrasi_dpjp"    => $data->reg_nm_dokter,
                "kd_dpjp"            => $data->kd_dpjp,
                "dpjp"               => $data->nm_dpjp,
                "kode"               => $data->kode_paket,
                "keterangan"         => $data->nm_perawatan,
                "layanan"            => "OPERASI",
                // "bangsal"            => "",
                "ruang_tindakan"     => "",
                // "kd_bangsal"         => "",
                "dokter_pelaksana"   => "",
                "dokter_operator"    => $data->operator1 . $nama_operator2 . $nama_operator3,
                "asisten_operator"   => $data->asisten_operator1 . $nama_asisten_operator2 . $nama_asisten_operator3,
                "dokter_anestesi"    => $data->dokter_anestesi,
                "asisten_anestesi"   => $data->asisten_anestesi . $nama_asisten_anestesi2,
                "dokter_anak"        => $data->dokter_anak,
                "instrumen"          => $data->instrumen,
                "paramedis"          => "",
                "margin_obat"        => "",
                "total"              => $data->total_biaya,
            ];
        }

        return $hasil;
    }
}

if (! function_exists('get_data_detil_ralan')) {
    function get_data_detil_ralan($data_tindakan, $item)
    {
        {

            $hasil = [];
            // dump($data_tindakan);
            foreach ($data_tindakan as $data) {

                $hasil[] = [
                    "waktu"            => $data->tgl_perawatan . $data->jam_rawat,
                    "no_rawat"         => $item->no_rawat,
                    "mr"               => $item->no_rkm_medis,
                    "nama_pasien"      => $item->nm_pasien,
                    "layanan_asal"     => $item->layanan_asal,
                    "jaminan"          => $item->jaminan,
                    "kd_dpjp"          => $item->kd_dokter,
                    "dpjp"             => $item->nm_dokter,
                    "kode"             => $data->kd_jenis_prw,
                    "keterangan"       => $data->nm_perawatan,
                    "layanan"          => $item->layanan_asal,
                    "dokter_pelaksana" => isset($data->nm_dokter) ? $data->nm_dokter : "",
                    "dokter_operator"  => "",
                    "asisten_operator" => "",
                    "dokter_anestesi"  => "",
                    "asisten_anestesi" => "",
                    "dokter_anak"      => "",
                    "instrumen"        => "",
                    "paramedis"        => isset($data->nama_paramedis) ? $data->nama_paramedis : "",
                    "margin_obat"      => "",
                    "total"            => $data->biaya_rawat,
                ];
            }
            // dump($hasil);
            return $hasil;
        }
    }
}
if (! function_exists('get_data_detil_unit')) {
    function get_data_detil_unit($data_tindakan, $item, $is_rawat_jalan = false, $data_rujukan)
    {
        {

            // $data_unit = [
            //     'cssd'         => [],
            //     'rehabilitasi' => [],
            //     'gizi'         => [],
            //     'nicu'         => [],
            //     'icu'          => [],
            //     'vk'           => [],
            //     'ranap'        => [],
            // ];
            $hasil = [];
            // dump("");
            // dd($data_rujukan);
            $counter_paramedis = 0;
            $lastNoRawat       = null;

            foreach ($data_tindakan as $data) {
                // Normalize string sekali aja biar hemat
                $nm_perawatan = strtolower((string) $data->nm_perawatan);
                $kd_bangsal   = strtolower((string) $data->kd_bangsal);
                $kd_ruang     = strtolower((string) $data->ruang_tindakan);
                $kd_prw       = strtolower((string) $data->kd_jenis_prw);
                $nm_pasien    = strtolower(str_replace('.', '', (string) $data->nm_pasien));
                $unit         = $kd_bangsal != null ? 'RANAP' : $item->layanan_asal; // default fallback
                if (property_exists($data, 'tindakan_rawat')) {
                    if ($data->tindakan_rawat == 'RALAN') {
                        $is_rawat_jalan = true;
                    } else {
                        $is_rawat_jalan = false;
                    }
                }
                if ($is_rawat_jalan) {
                    $poli = get_map_dokter_poli($data->kd_dokter);
                    if ($poli) {
                        $unit = $poli;
                    }
                    if ((! empty($data_rujukan))) {

                        if (! $data->kd_dokter) {
                            // if ($counter_paramedis != 0 && $counter_paramedis <= count($data_rujukan)) {
                            //     // dump($data_rujukan);
                            //     $data_poli = $data_rujukan[($counter_paramedis - 1)];
                            //     $unit      = $data_poli->nm_poli;
                            //     // dump($data_rujukan);
                            // }
                            $paramedis_poli = get_map_paramedis_poli($data->kode_paramedis);
                            if ($paramedis_poli) {
                                $unit = $paramedis_poli;
                            }
                            // dump($data_rujukan, $data);
                            // $counter_paramedis += 1;
                        }
                    }
                    if (in_array($data->kode_paramedis, data_petugas_vk_ponek())) {
                        $unit = "PONEK";
                    }

                } else {

                    if (str_contains($nm_perawatan, 'cssd') || str_contains($kd_prw, 'cssd')) {
                        $unit = 'CSSD';
                    } elseif (str_contains($nm_perawatan, 'gizi') || str_contains($nm_perawatan, 'makan')) {
                        $unit = 'GIZI';
                    } elseif (str_contains($nm_perawatan, 'vk') || str_contains($kd_prw, 'vk')) {
                        $unit = $is_rawat_jalan ? 'PONEK' : 'VK';
                    } elseif (in_array($data->kode_paramedis, data_petugas_vk_ponek()) || in_array(strtolower($kd_bangsal), array_map('strtolower', data_bangsal_vk()))) {
                        $unit = $is_rawat_jalan ? 'PONEK' : 'VK';
                    } elseif (str_contains($kd_bangsal, 'nicu') || str_contains($kd_prw, 'nicu')) {
                        $unit = 'NICU';
                    } elseif (str_contains($kd_bangsal, 'icu') || str_contains($kd_prw, 'icu')) {
                        $unit = 'ICU';
                        // dd($data);
                    }
                }

                // filter bangsal unit untuk tindakan yang dilakukan pada kamar
                if (in_array(strtolower($kd_bangsal), array_map('strtolower', data_bangsal_vk()))) {
                    $unit_bangsal = 'VK';
                } else if (str_contains($kd_bangsal, 'nicu')) {
                    $unit_bangsal = 'NICU';
                } else if (str_contains($kd_bangsal, 'icu')) {
                    $unit_bangsal = 'ICU';
                } else {
                    $unit_bangsal = $unit;
                }
                // filter bangsal unit untuk tindakan yang dilakukan pada kamar
                if ($kd_ruang) {

                    if (in_array(strtolower($kd_ruang), array_map('strtolower', data_bangsal_vk()))) {
                        $ruang_tindakan = 'VK';
                    } else if (str_contains($kd_ruang, 'nicu')) {
                        $ruang_tindakan = 'NICU';
                    } else if (str_contains($kd_ruang, 'icu')) {
                        $ruang_tindakan = 'ICU';
                    } else {
                        $ruang_tindakan = 'RANAP';
                    }
                } else {
                    // dd($kd_ruang, $unit_bangsal);
                    $ruang_tindakan = $unit_bangsal;
                }

                if (($unit != 'CSSD') && ($unit != 'GIZI')) {
                    if (($unit == 'RANAP') && ($ruang_tindakan != 'RANAP')) {
                        $unit = $ruang_tindakan;
                    } else if (($unit == 'VK') && ($ruang_tindakan != 'VK')) {
                        $unit = $ruang_tindakan;
                    }
                }
                // Masukkan data ke unit
                // $data_unit[$unit][] = $data;
                $hasil[] = [
                    "waktu"              => $data->tgl_perawatan . " " . $data->jam_rawat,
                    "no_rawat"           => $item->no_rawat,
                    "mr"                 => $item->no_rkm_medis,
                    "nama_pasien"        => $item->nm_pasien,
                    "layanan_asal"       => $item->layanan_asal,
                    "jaminan"            => $item->jaminan,
                    "registrasi_kd_dpjp" => $item->reg_kd_dokter,
                    "registrasi_dpjp"    => $item->reg_nm_dokter,
                    "kd_dpjp"            => $item->kd_dokter,
                    "dpjp"               => $item->nm_dokter,
                    "kode"               => $data->kd_jenis_prw,
                    "keterangan"         => $data->nm_perawatan,
                    "layanan"            => $unit,
                    // "bangsal"            => $unit_bangsal,
                    "ruang_tindakan"     => $is_rawat_jalan ? '' : $ruang_tindakan,
                    // "kd_bangsal"         => $is_rawat_jalan ? '' : $kd_ruang,
                    "dokter_pelaksana"   => isset($data->nm_dokter) ? $data->nm_dokter : "",
                    "dokter_operator"    => "",
                    "asisten_operator"   => "",
                    "dokter_anestesi"    => "",
                    "asisten_anestesi"   => "",
                    "dokter_anak"        => "",
                    "instrumen"          => "",
                    "paramedis"          => isset($data->nama_paramedis) ? $data->nama_paramedis : "",
                    "margin_obat"        => "",
                    "total"              => $data->biaya_rawat,
                ];
            }
            // dump($hasil);
            return $hasil;
        }
    }
}

if (! function_exists('get_operasi_detil')) {
    function get_operasi_detil($no_rawat, $item)
    {
        $rs = DB::select("
            SELECT
                operasi.no_rawat,
                reg_periksa.no_rkm_medis,
                pasien.nm_pasien,
                operasi.kode_paket,
                paket_operasi.nm_perawatan,
                operasi.tgl_operasi,
                penjab.png_jawab AS jaminan,
                poliklinik.nm_poli AS layanan_asal
                IF(operasi.status='Ralan',
                    (SELECT nm_poli FROM poliklinik WHERE poliklinik.kd_poli = reg_periksa.kd_poli),
                    (SELECT bangsal.nm_bangsal
                    FROM kamar_inap
                    INNER JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar
                    INNER JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal
                    WHERE kamar_inap.no_rawat = operasi.no_rawat LIMIT 1)
                ) AS ruangan,

                operator1.nm_dokter AS operator1, operasi.biayaoperator1,
                operator2.nm_dokter AS operator2, operasi.biayaoperator2,
                operator3.nm_dokter AS operator3, operasi.biayaoperator3,

                asisten_operator1.nama AS asisten_operator1, operasi.biayaasisten_operator1,
                asisten_operator2.nama AS asisten_operator2, operasi.biayaasisten_operator2,
                asisten_operator3.nama AS asisten_operator3, operasi.biayaasisten_operator3,

                instrumen.nama AS instrumen, operasi.biayainstrumen,
                dokter_anak.nm_dokter AS dokter_anak, operasi.biayadokter_anak,
                perawaat_resusitas.nama AS perawaat_resusitas, operasi.biayaperawaat_resusitas,
                dokter_anestesi.nm_dokter AS dokter_anestesi, operasi.biayadokter_anestesi,
                asisten_anestesi.nama AS asisten_anestesi, operasi.biayaasisten_anestesi,

                (SELECT nama FROM petugas WHERE petugas.nip = operasi.asisten_anestesi2) AS asisten_anestesi2,
                operasi.biayaasisten_anestesi2,

                bidan.nama AS bidan, operasi.biayabidan,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.bidan2) AS bidan2, operasi.biayabidan2,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.bidan3) AS bidan3, operasi.biayabidan3,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.perawat_luar) AS perawat_luar, operasi.biayaperawat_luar,

                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop) AS omloop, operasi.biaya_omloop,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop2) AS omloop2, operasi.biaya_omloop2,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop3) AS omloop3, operasi.biaya_omloop3,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop4) AS omloop4, operasi.biaya_omloop4,
                (SELECT nama FROM petugas WHERE petugas.nip = operasi.omloop5) AS omloop5, operasi.biaya_omloop5,

                (SELECT nm_dokter FROM dokter WHERE dokter.kd_dokter = operasi.dokter_pjanak) AS dokter_pjanak, operasi.biaya_dokter_pjanak,
                (SELECT nm_dokter FROM dokter WHERE dokter.kd_dokter = operasi.dokter_umum) AS dokter_umum, operasi.biaya_dokter_umum,

                IFNULL(
                    operasi.biayaoperator1 +
                    operasi.biayaoperator2 +
                    operasi.biayaoperator3 +
                    operasi.biayaasisten_operator1 +
                    operasi.biayaasisten_operator2 +
                    operasi.biayaasisten_operator3 +
                    operasi.biayainstrumen +
                    operasi.biayadokter_anak +
                    operasi.biayaperawaat_resusitas +
                    operasi.biayadokter_anestesi +
                    operasi.biayaasisten_anestesi +
                    operasi.biayaasisten_anestesi2 +
                    operasi.biayabidan +
                    operasi.biayabidan2 +
                    operasi.biayabidan3 +
                    operasi.biayaperawat_luar +
                    operasi.biaya_omloop +
                    operasi.biaya_omloop2 +
                    operasi.biaya_omloop3 +
                    operasi.biaya_omloop4 +
                    operasi.biaya_omloop5 +
                    operasi.biaya_dokter_pjanak +
                    operasi.biaya_dokter_umum +
                    operasi.biayaalat +
                    operasi.biayasewaok +
                    operasi.akomodasi +
                    operasi.bagian_rs +
                    operasi.biayasarpras
                , 0) AS total_biaya

            FROM operasi
            INNER JOIN reg_periksa ON operasi.no_rawat = reg_periksa.no_rawat
            INNER JOIN poliklinik ON reg_periksa.kd_poli = poliklinik.kd_poli
            INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis
            INNER JOIN paket_operasi ON operasi.kode_paket = paket_operasi.kode_paket
            INNER JOIN penjab ON reg_periksa.kd_pj = penjab.kd_pj
            INNER JOIN dokter AS operator1 ON operator1.kd_dokter = operasi.operator1
            INNER JOIN dokter AS operator2 ON operator2.kd_dokter = operasi.operator2
            INNER JOIN dokter AS operator3 ON operator3.kd_dokter = operasi.operator3
            INNER JOIN dokter AS dokter_anak ON dokter_anak.kd_dokter = operasi.dokter_anak
            INNER JOIN dokter AS dokter_anestesi ON dokter_anestesi.kd_dokter = operasi.dokter_anestesi
            INNER JOIN petugas AS asisten_operator1 ON asisten_operator1.nip = operasi.asisten_operator1
            INNER JOIN petugas AS asisten_operator2 ON asisten_operator2.nip = operasi.asisten_operator2
            INNER JOIN petugas AS asisten_operator3 ON asisten_operator3.nip = operasi.asisten_operator3
            INNER JOIN petugas AS asisten_anestesi ON asisten_anestesi.nip = operasi.asisten_anestesi
            INNER JOIN petugas AS bidan ON bidan.nip = operasi.bidan
            INNER JOIN petugas AS instrumen ON instrumen.nip = operasi.instrumen
            INNER JOIN petugas AS perawaat_resusitas ON perawaat_resusitas.nip = operasi.perawaat_resusitas

            WHERE reg_periksa.no_rawat = ?
        ", [$no_rawat]);
        $hasil = [];
        foreach ($rs as $data) {
            $nama_operator2         = $data->operator2 == '-' ? "" : ", " . $data->operator2;
            $nama_operator3         = $data->operator3 == '-' ? "" : ", " . $data->operator3;
            $nama_asisten_operator2 = $data->asisten_operator2 == '-' ? "" : ", " . $data->asisten_operator2;
            $nama_asisten_operator3 = $data->asisten_operator3 == '-' ? "" : ", " . $data->asisten_operator3;
            $nama_asisten_anestesi2 = $data->asisten_anestesi2 == '-' ? "" : ", " . $data->asisten_anestesi2;
            $hasil[]                = [
                "waktu"            => $data->tgl_operasi,
                "no_rawat"         => $item->no_rawat,
                "mr"               => $item->no_rkm_medis,
                "nama_pasien"      => $item->nm_pasien,
                "layanan_asal"     => $item->layanan_asal,
                "jaminan"          => $item->jaminan,
                "kd_dpjp"          => $item->kd_dokter,
                "dpjp"             => $item->nm_dokter,
                "kode"             => $data->kode_paket,
                "keterangan"       => $data->nm_perawatan,
                "layanan"          => "operasi",
                "dokter_pelaksana" => "",
                "dokter_operator"  => $data->operator1 . $nama_operator2 . $nama_operator3,
                "asisten_operator" => $data->asisten_operator1 . $nama_asisten_operator2 . $nama_asisten_operator3,
                "dokter_anestesi"  => $data->dokter_anestesi,
                "asisten_anestesi" => $data->asisten_anestesi . $nama_asisten_anestesi2,
                "dokter_anak"      => $data->dokter_anak,
                "instrumen"        => $data->instrumen,
                "paramedis"        => "",
                "margin_obat"      => "",
                "total"            => $data->total_biaya,
            ];
        }

        // dump($hasil);

        return $hasil;
    }
}
if (! function_exists('get_detil_ranap_dokter')) {
    function get_detil_ranap_dokter($no_rawat)
    {
        {
            $data = DB::select("
            SELECT dokter.nm_dokter,rawat_inap_dr.tgl_perawatan, rawat_inap_dr.jam_rawat, pasien.nm_pasien,rawat_inap_dr.biaya_rawat, jns_perawatan_inap.kd_jenis_prw, nm_perawatan, bangsal.kd_bangsal
            FROM reg_periksa
            INNER JOIN rawat_inap_dr ON rawat_inap_dr.no_rawat=reg_periksa.no_rawat
            INNER JOIN dokter ON rawat_inap_dr.kd_dokter=dokter.kd_dokter
            INNER JOIN jns_perawatan_inap ON jns_perawatan_inap.kd_jenis_prw=rawat_inap_dr.kd_jenis_prw
            INNER JOIN bangsal ON jns_perawatan_inap.kd_bangsal=bangsal.kd_bangsal
            INNER JOIN pasien ON pasien.no_rkm_medis=reg_periksa.no_rkm_medis
            WHERE reg_periksa.no_rawat = ?
        ", [$no_rawat]);

            return $data;
        }
    }
}
if (! function_exists('get_detil_ranap_dokter_paramedis')) {
    function get_detil_ranap_dokter_paramedis($no_rawat)
    {
        {
            $data = DB::select("
                SELECT petugas.nama as nama_paramedis,dokter.nm_dokter,rawat_inap_drpr.tgl_perawatan, rawat_inap_drpr.jam_rawat, pasien.nm_pasien,rawat_inap_drpr.biaya_rawat, jns_perawatan_inap.kd_jenis_prw, nm_perawatan, bangsal.kd_bangsal
                FROM reg_periksa
                INNER JOIN rawat_inap_drpr ON rawat_inap_drpr.no_rawat=reg_periksa.no_rawat
                INNER JOIN dokter ON rawat_inap_drpr.kd_dokter=dokter.kd_dokter
                INNER JOIN petugas ON rawat_inap_drpr.nip=petugas.nip
                INNER JOIN jns_perawatan_inap ON jns_perawatan_inap.kd_jenis_prw=rawat_inap_drpr.kd_jenis_prw
                INNER JOIN bangsal ON jns_perawatan_inap.kd_bangsal=bangsal.kd_bangsal
                INNER JOIN pasien ON pasien.no_rkm_medis=reg_periksa.no_rkm_medis
                WHERE reg_periksa.no_rawat = ?
            ", [$no_rawat]);

            return $data;
        }
    }
}
if (! function_exists('get_detil_ranap_paramedis')) {
    function get_detil_ranap_paramedis($no_rawat)
    {
        {
            $data = DB::select("
            SELECT petugas.nama as nama_paramedis, rawat_inap_pr.tgl_perawatan, rawat_inap_pr.jam_rawat, pasien.nm_pasien,rawat_inap_pr.biaya_rawat, jns_perawatan_inap.kd_jenis_prw, nm_perawatan, bangsal.kd_bangsal
            FROM reg_periksa
            INNER JOIN rawat_inap_pr ON rawat_inap_pr.no_rawat=reg_periksa.no_rawat
            INNER JOIN petugas ON rawat_inap_pr.nip=petugas.nip
            INNER JOIN jns_perawatan_inap ON jns_perawatan_inap.kd_jenis_prw=rawat_inap_pr.kd_jenis_prw
            INNER JOIN bangsal ON jns_perawatan_inap.kd_bangsal=bangsal.kd_bangsal
            INNER JOIN pasien ON pasien.no_rkm_medis=reg_periksa.no_rkm_medis
            WHERE reg_periksa.no_rawat = ?
        ", [$no_rawat]);

            return $data;
        }
    }
}
if (! function_exists('get__')) {
    function get__($no_rawat)
    {

    }
}
