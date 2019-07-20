<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class gotify extends eqLogic {
    /*     * *************************Attributs****************************** */

    // public static function health() {
    //     $return = array();

    //     $inMaintenance = true;
    //     $return[] = array(
    //         'test' => __('API gotify', __FILE__),
    //         'result' => $inMaintenance ? 'NOK': 'OK',
    //         'advice' =>  $inMaintenance ? __('Veuillez réessayer dans quelques minutes', __FILE__) : '',
    //         'state' => !$inMaintenance,
    //     );

    //     $result = true;
    //     $return[] = array(
    //         'test' => __('Utilisateur et mot de passe', __FILE__),
    //         'result' => $result==='' ? 'OK': 'NOK',
    //         'advice' =>  $result,
    //         'state' => $result==='',
    //     );

    //     return $return;
    // }

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */
    /*
    public static function cron5() {

    }
    */
/*
    public static function cronHourly() {

    }
*/
     /*
      public static function cronDaily() {

      }
    */


    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    public function postSave() {
		$cmd = $this->getCmd(null, 'send');
		if (!is_object($cmd)) {
			$cmd = new gotifyCmd();
			$cmd->setLogicalId('send');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Envoi', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('message');
			$cmd->setEqLogic_id($this->getId());
			//$cmd->setDisplay('title_placeholder', __('Options', __FILE__));
			//$cmd->setDisplay('message_placeholder', __('Message', __FILE__));
			$cmd->save();
		}
    }

    public function preUpdate() {

    }

    public function postUpdate() {

    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    // private function createApp() {

    //     $url = "http://192.168.100.102:32768/application";
    //     $headers = [
    //         "Content-Type: application/json; charset=utf-8"
    //     ];
    //     $data = [
    //         "name"=> "test2",
    //         "description"=> "tutorial test"
    //     ];
    //     $data_string = json_encode($data);

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    //     curl_setopt($ch, CURLOPT_USERPWD, "admin:admin" );
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

    //     $result = curl_exec($ch);
    //     $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //     curl_close ($ch);

    //     log::add(__CLASS__, 'debug', "{$code}:{$result}");
    //     // curl -u admin:admin -X POST https://yourdomain.com/application -F "name=test" -F "description=tutorial"
    // }

    public function sendMessage($_options = array()) {
        $title = $_options['title'];
        $message = $_options['message'];
        log::add(__CLASS__, 'debug', "title:{$title} - message:{$message}");
        $data = [
            "title"=> $title,
            "message"=> $message,
            "priority"=> 5,
              "extras" => [
                "client::display" => [
                    "contentType" => "text/markdown"
                ]
            ]
        ];
        $data_string = json_encode($data);

        $token = $this->getConfiguration('token');
        $domain = config::byKey('url', 'gotify');
        $url = "{$domain}/message?token={$token}";
        log::add(__CLASS__, 'debug', "url:{$url}");

        $headers = [
            "Content-Type: application/json; charset=utf-8"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close ($ch);

        if ($code!="200") {
            log::add(__CLASS__, 'error', "httpCode:{$code} => {$result}");
        }
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */


    /*     * **********************Getteur Setteur*************************** */

}

class gotifyCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
		$eqlogic = $this->getEqLogic();
		switch ($this->getLogicalId()) {
            case 'send':
                $eqlogic->sendMessage($_options);
				break;
        }
    }
    /*     * **********************Getteur Setteur*************************** */
}
