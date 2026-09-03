<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageContact extends Model
{
    protected $table = 'messages_contact';

    protected $fillable = [
        'id_compagnie',
        'nom',
        'telephone',
        'email',
        'message',
        'origine',
        'traite',
    ];

    protected $casts = [
        'traite' => 'boolean',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
