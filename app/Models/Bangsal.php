<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bangsal extends Model
{
    protected $table = 'bangsal';

    public function kamar(){
        return $this->hasMany(Kamar::class, 'kd_bangsal', 'kd_bangsal');
    }
}
