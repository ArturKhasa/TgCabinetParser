<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $fillable = [
        "active"
    ];

    public function activate(){
        $this->update([
            "active"        =>  false
        ]);
    }

    public function deactivate(){
        $this->update([
            "active"        =>  true
        ]);
    }
}