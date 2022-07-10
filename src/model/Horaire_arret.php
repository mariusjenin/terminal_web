<?php

namespace Terminal\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Horaire_arret
 * @package Terminal\Model
 */
class Horaire_arret extends Model
{
    protected $table = 'horaire_arret';
    protected $id_horaire_arret = 'id_horaire_arret';
    protected $id_trajet = 'id_trajet';
    protected $id_arret = 'id_arret';
    protected $heure_passage = 'heure_passage';

    public $timestamps = false;
    protected $primaryKey = 'id_horaire_arret';

}

?>