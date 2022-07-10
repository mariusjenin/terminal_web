<?php
namespace Terminal\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Itineraire
 * @package Terminal\Model
 */
class Itineraire extends Model
{
    protected $table = 'itineraire';
    protected $id_itineraire = 'id_itineraire';
    protected $nom_itineraire = 'nom_itineraire';

    public $timestamps = false;
    protected $primaryKey = 'id_itineraire';

    public function arrets()
    {
        return $this->hasMany(Arret::class, 'id_itineraire', 'id_itineraire');
    }

    public function trajets()
    {
        return $this->hasMany(Trajet::class, 'id_itineraire', 'id_itineraire');
    }
}

?>
