<?php
namespace Terminal\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Utilisateur
 * @package Terminal\Model
 */
class Utilisateur extends Model
{
    /**
     * l'attribut type ne prend qu'une de ses 3 valeurs
     */
    public const PASSAGER_TYPE = 'PASSAGER';
    public const CONDUCTEUR_TYPE = 'CONDUCTEUR';
    public const ADMINISTRATEUR_TYPE = 'ADMINISTRATEUR';

    protected $table = 'utilisateur';
    protected $id_utilisateur = 'id_utilisateur';
    protected $email = 'email';
    protected $hash_pwd = 'hash_pwd';
    protected $type = 'type';
    protected $token = 'token';

    public $timestamps = false;
    protected $primaryKey = 'id_utilisateur';

    public function is_passager()
    {
        return $this->attributes['type'] == self::PASSAGER_TYPE;
    }

    public function is_conducteur()
    {
        return $this->attributes['type'] == self::CONDUCTEUR_TYPE;
    }

    public function is_admin()
    {
        return $this->attributes['type'] == self::ADMINISTRATEUR_TYPE;
    }
}

?>
