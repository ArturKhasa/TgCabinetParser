<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffPlan extends Model
{
    use HasFactory;

    protected $casts = [
        'prices' => Json::class
    ];
}
