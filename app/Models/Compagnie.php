<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compagnie extends Model
{
    protected $table = 'compagnie';
    protected $primaryKey = 'id_compagnie';
    public $timestamps = false;

    protected $fillable = [
        'nom_compagnie',
        'libele',
        'slogant',
        'logo',
        'telephone',
        'email',
        'adresse',
        'facebook',
        'instagram',
        'whatsapp',
    ];

    protected static ?self $siteCompagnie = null;

    // La compagnie à laquelle le site public (resources/views/site/**) est dédié — voir
    // config/site.php. Memoïsée pour la durée de la requête : ce helper est appelé depuis
    // le nav partial sur chaque page en plus des contrôleurs eux-mêmes.
    public static function site(): self
    {
        return self::$siteCompagnie ??= self::findOrFail(config('site.compagnie_id'));
    }

    public function agences()
    {
        return $this->hasMany(Agence::class, 'id_compagnie', 'id_compagnie');
    }

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'id_compagnie', 'id_compagnie');
    }

    public function photos()
    {
        return $this->hasMany(CompagniePhoto::class, 'id_compagnie', 'id_compagnie')->orderBy('ordre');
    }
}
