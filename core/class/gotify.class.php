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

    public function preInsert() {
        $this->setConfiguration('verifyhost', '2');
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
            $cmd->setName(__('Envoyer', __FILE__));
            $cmd->setType('action');
            $cmd->setSubType('message');
            $cmd->setEqLogic_id($this->getId());
            $cmd->save();
        }

        $cmd = $this->getCmd(null, 'delete');
        if (!is_object($cmd)) {
            $cmd = new gotifyCmd();
            $cmd->setLogicalId('delete');
            $cmd->setIsVisible(1);
            $cmd->setName(__('Tout supprimer', __FILE__));
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setEqLogic_id($this->getId());
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

    private function executeAction($resource, $method, $data = []) {
        $ch = curl_init();

        $headers = [];
        switch ($method) {
            case 'POST':
                $token = $this->getConfiguration('token');
                if ($token==='') {
                    throw new Exception(__('Vous devez configurer un token d\'application pour cette action.', __FILE__));
                    return;
                }
                $headers[] = "Content-Type: application/json; charset=utf-8";
                curl_setopt($ch, CURLOPT_POST, true);
                $postfields = json_encode($data);
                log::add(__CLASS__, 'debug', "data:{$postfields}");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                unset($postfields);
                break;
            case 'DELETE':
                $token = config::byKey('clientToken', 'gotify');
                if ($token==='') {
                    throw new Exception(__('Vous devez configurer un token client pour cette action.', __FILE__));
                    return;
                }
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                throw new Exception("Incorrect method: {$method}");
        }

        $host = config::byKey('url', 'gotify');
        curl_setopt($ch, CURLOPT_URL, "{$host}{$resource}");

        log::add(__CLASS__, 'debug', "{$method}:{$host}{$resource}");

        $headers[] = 'Accept: application/json';
        $headers[] = "X-Gotify-Key: {$token}";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->getConfiguration("verifyhost", '2'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        if ($code!="200") {
            log::add(__CLASS__, 'error', "httpCode:{$code} => {$result}");
        }
    }

    public function deleteMessage() {
        $this->executeAction('/message', 'DELETE');
    }

    public function postMessage($data) {
        $this->executeAction('/message', 'POST', $data);
    }
}

class gotifyCmd extends cmd {

    private function sendMessage($_options = array()) {
        if (!isset($_options['message']) || trim($_options['message'])=='') {
            $error = __('Message ne peut pas être vide.', __FILE__);
            throw new Exception($error);
        }
        $title = trim($_options['title']);
        $message = trim($_options['message']);
        $priority = (int)$this->getConfiguration('priority', 0);
        $contentType = $this->getConfiguration('contentType', 'markdown');
        log::add('gotify', 'debug', "title:{$title} - message:{$message} - priority:{$priority} - contentType:{$contentType}");

        if (isset($_options['files']) && is_array($_options['files'])) {
            log::add(__CLASS__, 'debug', "Adding images to message");
            foreach ($_options['files'] as $filepath) {
                $ext = pathinfo($filepath, PATHINFO_EXTENSION);
                if (in_array($ext, array('gif', 'jpeg', 'jpg', 'png'))) {
                    $file = file_get_contents($filepath);
                    $data = base64_encode($file);
                    if ($contentType=='markdown') {
                        $message .= "  \n![](data:image/{$ext};base64,{$data})";
                    }
                }
            }
        }

        $data = [
            "title"=> $title,
            "message"=> $message,
            "priority"=> $priority,
              "extras" => [
                "client::display" => [
                    "contentType" => "text/{$contentType}"
                ]
            ]
        ];

        $eqlogic = $this->getEqLogic();
        $eqlogic->postMessage($data);
    }

    public function execute($_options = array()) {
        switch ($this->getLogicalId()) {
            case 'send':
                $this->sendMessage($_options);
                break;
            case 'delete':
                $eqlogic = $this->getEqLogic();
                $eqlogic->deleteMessage();
                break;
        }
    }
}
