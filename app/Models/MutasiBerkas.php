<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiBerkas extends Model
{
    protected $table   = 'mutasi_berkas';
    public $timestamps = false;

    public function reg_periksa()
    {
        // one-to-one relasi
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
