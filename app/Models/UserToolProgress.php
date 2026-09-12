<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToolProgress extends Model
{
    use HasFactory;

    // laravel meminta id setiap insert, tp PostgreSQL menolak jika tabel tsb tanpa kolom id
    public $incrementing = false;
}
