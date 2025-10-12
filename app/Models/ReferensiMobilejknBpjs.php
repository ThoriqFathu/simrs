<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferensiMobilejknBpjs extends Model
{
    protected $table   = 'referensi_mobilejkn_bpjs';
    public $timestamps = false;

    // kalau primary key bukan "id"
    protected $primaryKey = 'nobooking';
    // kalau primary key bukan auto-increment
    public $incrementing = false;
    protected $keyType   = 'string';

    public function reg_periksa()
    {
        // one-to-one relasi
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
