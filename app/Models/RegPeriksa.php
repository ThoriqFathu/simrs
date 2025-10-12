<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegPeriksa extends Model
{
    protected $table   = 'reg_periksa';
    public $timestamps = false;

    // kalau primary key bukan "id"
    protected $primaryKey = 'no_rawat';
    // kalau primary key bukan auto-increment
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['status_bayar'];
}
