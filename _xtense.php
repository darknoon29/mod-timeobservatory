<?php
/***************************************************************************
 *   filename    : _xtense.php
 *   desc.       : liaison avec xtense2
 *   Author      : AirBAT
 *   created     : 06/07/2016
 *   by          : Darknoon
 *   modified    : -
 *   last modif. : created
 ***************************************************************************/
if (!defined('IN_SPYOGAME')) die("Hacking attempt");
global $db, $table_prefix, $user, $xtense_version;
$xtense_version = "2.6.0";

require_once("./mod/recycleurs/core/phalanx.php");
require_once("./mod/recycleurs/core/recyclers.php");
// TEST XTENSE2
if (class_exists("Callback")) {
    /**
     * Class cdr_Callback
     */
    class recycleurs_Callback extends Callback
    {
        public $version = '2.6.0';
        /**
         * @param $system
         * @return int
         */
        public function recycleurs_import($system)
        {
            global $io;
            if (recycleurs_import($system))
                return Io::SUCCESS;
            else
                return Io::ERROR;
        }
        public function phalanx_import($system)
        {
            global $io;
            if (phalanx_import($system))
                return Io::SUCCESS;
            else
                return Io::ERROR;
        }
        /**
         * @return array
         */
        public function getCallbacks()
        {
            return array(array('function' => 'recycleurs_import', 'type' => 'fleet'), array('function' => 'phalanx_import', 'type' => 'buildings'));
        }
    }
}
/**
 * @param $system
 * @return bool
 */
function recycleurs_import($data)
{
    global $user_data, $db, $table_prefix;
    // données a traiter
    // timestamp actuel
    $date = time();
    $player_galaxy = $data['coords'][0];
    $player_system = $data['coords'][1];
    $player_position = $data['coords'][2];
    $isMoon = $data['planet_type'];
    $planet_name = $data['planet_name'];
    $coordinates = $player_galaxy . ":" . $player_system . ":" . $player_position;
    $nb_recycleurs = $data['fleet']['REC'];
    $required_recy = mod_get_option('recy_limit');
    if (mod_get_option('recy_limit') < 1) $required_recy = 1;
    if ($nb_recycleurs > $required_recy) {
        //On vérifie si il y a une porte de saut à proximité (La porte n'est dispo que sur les lunes)
        $request = "SELECT `planet_name` FROM " . TABLE_USER_BUILDING . " WHERE  `PoSa` = '1' AND `coordinates` = '" . $coordinates . "'";
        $posa = $db->sql_numrows($db->sql_query($request));
        add_recyclers($player_galaxy, $player_system, $player_position, $posa, $nb_recycleurs, true);
    }
    return true;
}
function phalanx_import($data)
{
    $player_galaxy = $data['coords'][0];
    $player_system = $data['coords'][1];
    $player_position = $data['coords'][2];
    if (isset($data['buildings']['Pha'])) {
        $lvl_phalange = $data['buildings']['Pha'];
        if ($lvl_phalange > 0)
            add_phalanx($player_galaxy, $player_system, $player_position, $lvl_phalange, true);
    }
    return true;
}
