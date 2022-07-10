<?php

namespace Terminal\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Participe_a_trajet
 * @package Terminal\Model
 */
class Participe_a_trajet extends Model
{
    protected $table = 'participe_a_trajet';
    protected $id_reservation = 'id_reservation';
    protected $id_utilisateur = 'id_utilisateur';
    protected $id_trajet = 'id_trajet';
    protected $date_participation = 'date_participation';
    protected $id_arret_depart = 'id_arret_depart';
    protected $id_arret_fin = 'id_arret_fin';
    protected $nb_places = 'nb_places';
    protected $latitude_utilisateur = 'latitude_utilisateur';
    protected $longitude_utilisateur = 'longitude_utilisateur';

    public $timestamps = false;
    protected $primaryKey = 'id_reservation';

}

?>