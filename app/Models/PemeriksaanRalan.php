<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanRalan extends Model
{
    protected $table   = 'pemeriksaan_ralan';
    public $timestamps = false;

    public function reg_periksa()
    {
        // one-to-one relasi
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
