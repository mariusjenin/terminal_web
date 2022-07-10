<?php

namespace Terminal\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Arret
 * @package Terminal\Model
 */
class Arret extends Model
{
    protected $table = 'arret';
    protected $id_arret = 'id_arret';
    protected $id_itineraire = 'id_itineraire';
    protected $nom_arret = 'nom_arret';
    protected $num_arret = 'num_arret';
    protected $latitude = 'latitude';
    protected $longitude = 'longitude';

    public $timestamps = false;
    protected $primaryKey = 'id_arret';

}

?>