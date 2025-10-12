<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaJalan extends Model
{
    protected $table = 'nota_jalan';
    protected $primaryKey = 'no_rawat'; // Menetapkan primary key
    public $incrementing = false;       // Karena bukan auto-increment
    protected $keyType = 'string'; 
    public $timestamps = false;
    protected $fillable = [
        'no_rawat',
        'no_nota',
        'tanggal',
        'jam',
    ];
}
