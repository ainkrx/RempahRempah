<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReaction extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['type'];

    // laravel meminta id setiap insert, tp PostgreSQL menolak jika tabel tsb tanpa kolom id
    public $incrementing = false;
}
