<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BridgingSep extends Model
{
    protected $table   = 'bridging_sep';
    public $timestamps = false;

    // kalau primary key bukan "id"
    protected $primaryKey = 'no_sep';
    // kalau primary key bukan auto-increment
    public $incrementing = false;
    protected $keyType   = 'string';

    public function reg_periksa()
    {
        // one-to-one relasi
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
    public function mutasi_berkas()
    {
        // one-to-one relasi
        return $this->belongsTo(MutasiBerkas::class, 'no_rawat', 'no_rawat');
    }
    public function pemeriksaan_ralan()
    {
        // one-to-one relasi
        return $this->belongsTo(PemeriksaanRalan::class, 'no_rawat', 'no_rawat');
    }
}
