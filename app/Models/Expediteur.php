<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expediteur extends Model
{
    protected $table = 'expediteurs';

    protected $primaryKey = 'id_expediteur';

    public $timestamps = false;

    protected $fillable = [
        'expediteur',
        'numero_exp',
        'whatsapp_exp',
    ];
}
