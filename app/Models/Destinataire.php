<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destinataire extends Model
{
    protected $table = 'destinataires';

    protected $primaryKey = 'id_destinataire';

    public $timestamps = false;

    protected $fillable = [
        'destinataire',
        'numero_dest',
        'whatsapp_dest',
        'id_exp',
    ];
}
