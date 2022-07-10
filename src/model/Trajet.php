<?php
namespace Terminal\Model;

use Illuminate\Database\Eloquent\Model;
use Terminal\Controller\WebAppController\TripWebController;
use Terminal\Manager\DirectionManager;
use Terminal\Utils\Utils;
use function Symfony\Component\String\b;

/**
 * Class Trajet
 * @package Terminal\Model
 */
class Trajet extends Model
{
    /**
     * l'attribut repetition ne prend qu'une de ses 6 valeurs
     */
    public const UNIQUE_REPETITION = 'UNIQUE';
    public const HEBDOMADAIRE_REPETITION = 'HEBDOMADAIRE';
    public const MENSUEL_REPETITION = 'MENSUEL';
    public const ANNUEL_REPETITION = 'ANNUEL';
    public const OUVRES_REPETITION = 'OUVRES';
    public const WEEKEND_REPETITION = 'WEEKEND';

    protected $table = 'trajet';
    protected $id_trajet = 'id_trajet';
    protected $id_itineraire = 'id_itineraire';
    protected $id_utilisateur = 'id_utilisateur';
    protected $date_depart = 'date_depart';
    protected $repetition = 'repetition';
    protected $place_max = 'place_max';
    protected $latitude_conducteur = 'latitude_conducteur';
    protected $longitude_conducteur = 'longitude_conducteur';

    public $timestamps = false;
    protected $primaryKey = 'id_trajet';

    /**
     * Actualise la date du trajet si le trajet est Hebdomadaire/Mensuel/Annuel
     * Supprime le trajet s'il est Unique et passé
     */
    public function refreshDateTrip()
    {
        if ($this->attributes['repetition'] == Trajet::HEBDOMADAIRE_REPETITION ||
            $this->attributes['repetition'] == Trajet::MENSUEL_REPETITION ||
            $this->attributes['repetition'] == Trajet::ANNUEL_REPETITION ||
            $this->attributes['repetition'] == Trajet::UNIQUE_REPETITION) {

            $date_depart = strtotime($this->attributes['date_depart']);
            $time = strtotime(date('H:i:s'));
            $date = strtotime(date("Y-m-d", strtotime("now")));
            $h_end = strtotime(Horaire_arret::where("id_trajet", "=", $this->attributes['id_trajet'])->orderBy("heure_passage", "DESC")->first()->heure_passage);


            if ($date_depart < $date || ($time > $h_end && $date == $date_depart)) {
                if ($this->attributes['repetition'] == Trajet::UNIQUE_REPETITION) {
                    TripWebController::delete_trip($this);
                } else {
                    switch ($this->attributes['repetition']) {
                        case Trajet::HEBDOMADAIRE_REPETITION:
                            do {
                                $time_modified = strtotime("+7 day", strtotime(date('Y-m-d H:i:s', $date_depart)));
                            } while ($time_modified < $date);
                            break;
                        case Trajet::MENSUEL_REPETITION:
                            do {
                                $time_modified = strtotime("+1 month", strtotime(date('Y-m-d H:i:s', $date_depart)));
                            } while ($time_modified < $date);
                            break;
                        case Trajet::ANNUEL_REPETITION:
                            do {
                                $time_modified = strtotime("+1 year", strtotime(date('Y-m-d H:i:s', $date_depart)));
                            } while ($time_modified < $date);
                            break;
                    }
                    $this->attributes['date_depart'] = date("Y-m-d", $time_modified);
                    $this->save();
                }
            }
        }
    }

    /**
     * Informe si le trajet va être effectué un jour donné (timestamp)
     * @param $time
     * @return bool
     */
    public function is_valid_for_day($time)
    {
        switch ($this->attributes['repetition']) {
            case Trajet::UNIQUE_REPETITION:
            case Trajet::HEBDOMADAIRE_REPETITION:
            case Trajet::MENSUEL_REPETITION:
            case Trajet::ANNUEL_REPETITION:
                return date('Y-m-d', $time) == $this->attributes['date_depart'];
            case Trajet::OUVRES_REPETITION:
                return date('N', $time) < 6;
            case Trajet::WEEKEND_REPETITION:
                return date('N', $time) >= 6;
        }
        return false;
    }

    /**
     * Informe si le trajet va être effectué durant le prochain mois à partir d'une date donnée (timestamp)
     * @param $time
     * @return bool
     */
    public function is_valid_for_month($datetime)
    {
        $date = strtotime(date("Y-m-d", $datetime));

        $date_plus_month = strtotime("+1 month", $date);
        $time_good = true;
        $date_depart = strtotime($this->attributes['date_depart']);
        switch ($this->attributes['repetition']) {
            case Trajet::UNIQUE_REPETITION:
                $time = strtotime(date("H:i:s", $datetime));
                $h_end = strtotime(Horaire_arret::where("id_trajet", "=", $this->attributes['id_trajet'])->orderBy("heure_passage", "DESC")->first()->heure_passage);
                $time_good = $time < $h_end || $date_depart != $date;
            case Trajet::HEBDOMADAIRE_REPETITION:
            case Trajet::MENSUEL_REPETITION:
            case Trajet::ANNUEL_REPETITION:
                return $date_depart >= strtotime(date('Y-m-d', $date))
                    && $date_depart < $date_plus_month
                    && ($time_good || !$this->attributes['repetition'] == Trajet::UNIQUE_REPETITION);
            case Trajet::OUVRES_REPETITION:
            case Trajet::WEEKEND_REPETITION:
                return true;
        }
        return false;
    }

    /**
     * Informe si le trajet est en cours
     * @return bool
     */
    public function has_begun()
    {
        $first_stop = Arret::where('id_itineraire', '=', $this->attributes['id_itineraire'])
            ->select([
                'id_arret as id_stop',
                'nom_arret as name_stop',
                'num_arret as num_stop',
                'latitude',
                'longitude'
            ])
            ->orderBy('num_arret')
            ->first()->id_stop;

        $heure_depart = Horaire_arret::where("id_arret", "=", $first_stop)->where("id_trajet", "=", $this->attributes['id_trajet'])->first()->heure_passage;

        switch ($this->attributes['repetition']) {
            case Trajet::UNIQUE_REPETITION:
            case Trajet::HEBDOMADAIRE_REPETITION:
            case Trajet::MENSUEL_REPETITION:
            case Trajet::ANNUEL_REPETITION:
                $date_depart = $this->attributes['date_depart'];
                break;
            case Trajet::OUVRES_REPETITION:
                if (date('N', strtotime("now")) < 6) {
                    $date_depart = date('Y-m-d', strtotime("now"));
                } else {
                    return false;
                }
                break;
            case Trajet::WEEKEND_REPETITION:
                if (date('N', strtotime("now")) >= 6) {
                    $date_depart = date('Y-m-d', strtotime("now"));
                } else {
                    return false;
                }
                break;
        }

        $datetime = $date_depart . ' ' . $heure_depart;
        return strtotime("now") > strtotime($datetime);
    }

    /**
     * Donne le prochain jour ou ce trajet va être effectué
     * @return false|mixed|string
     */
    public function nextDayValid()
    {
        switch ($this->attributes['repetition']) {
            case Trajet::UNIQUE_REPETITION:
            case Trajet::HEBDOMADAIRE_REPETITION:
            case Trajet::MENSUEL_REPETITION:
            case Trajet::ANNUEL_REPETITION:
                $this->refreshDateTrip();
                return $this->attributes['date_depart'];
            case Trajet::OUVRES_REPETITION:
            case Trajet::WEEKEND_REPETITION:

                $date = strtotime(date('Y-m-d'));
                $date_modified = $date;
                $time = strtotime(date('H:i:s'));
                $heure_fin = Horaire_arret::where("id_trajet", "=", $this->attributes['id_trajet'])
                    ->orderBy("heure_passage", "DESC")->first()->heure_passage;

                while (true) {
                    if (
                        ((date('N', $date_modified) < 6 && $this->attributes['repetition'] == Trajet::OUVRES_REPETITION)
                            || (date('N', $date_modified) >= 6 && $this->attributes['repetition'] == Trajet::WEEKEND_REPETITION))
                        && !($time > strtotime($heure_fin) && $date == $date_modified)
                    ) {
                        return date('Y-m-d', $date_modified);
                    } else {
                        $date_modified = strtotime(date('Y-m-d', $date_modified) . " +1 day");
                    }
                }
        }
        return false;
    }

    /**
     * Donne l'arrêt précédent une coordonnée donnée dans une itinéraire
     * @param $itinerary_json
     * @param $lat
     * @param $long
     * @param $lim
     * @param $id
     * @return false|int
     */
    public static function getStopBefore($itinerary_json, $lat, $long, $lim, &$id)
    {
        $id = 0;
        $dist = 6371000;
        for ($i = 0; $i < count($itinerary_json["response"]["coordinates"]); $i++) {
            $distLocal = Utils::haversineGreatCircleDistance($lat, $long, $itinerary_json["response"]["coordinates"][$i][1], $itinerary_json["response"]["coordinates"][$i][0]);
            if ($dist > $distLocal) {
                $dist = $distLocal;
                $id = $i;
            }
        }
        if ($dist > $lim) {
            return false;
        }

        $num_stop = 0;
        for ($i = 0; $i < count($itinerary_json["response"]["segments"]); $i++) {
            if ($id >= $itinerary_json["response"]["segments"][$i]["start_stop_pos"] &&
                $id < $itinerary_json["response"]["segments"][$i]["end_stop_pos"]) {
                $num_stop = $i;
                break;
            }
        }
        return $num_stop;
    }
}

?>
