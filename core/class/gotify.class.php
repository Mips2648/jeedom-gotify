<?php

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Mips\Http\HttpClient;
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

    private function getClient($token) {
        $host = config::byKey('url', 'gotify');
        $client = new HttpClient($host, log::getLogger(__CLASS__));
        $client->getHttpHeaders()->setHeader('X-Gotify-Key', $token);
        return $client;
    }

    public function deleteMessage() {
        $token = config::byKey('clientToken', 'gotify');
        if ($token==='') {
            throw new RuntimeException(__('Vous devez configurer un token client pour cette action.', __FILE__));
        }
        $client = $this->getClient($token);
        $response = $client->doDelete('message');
        if (!$response->isSuccess()) {
            log::add(__CLASS__, 'error', "httpCode:{$response->getHttpStatusCode()} => {$response->getBody()}");
        }
    }

    public function postMessage($data) {
        $token = $this->getConfiguration('token');
        if ($token==='') {
            throw new RuntimeException(__('Vous devez configurer un token d\'application pour cette action.', __FILE__));
        }
        $client = $this->getClient($token);
        $response = $client->doPost('message', $data);
        if (!$response->isSuccess()) {
            log::add(__CLASS__, 'error', "httpCode:{$response->getHttpStatusCode()} => {$response->getBody()}");
        }
    }
}

class gotifyCmd extends cmd {

    private function sendMessage($_options = array()) {
        $title = trim($_options['title']);
        $message = trim($_options['message']);
        $priority = (int)$this->getConfiguration('priority', 0);
        $contentType = $this->getConfiguration('contentType', 'markdown');
        log::add('gotify', 'debug', "title:{$title} - message:{$message} - priority:{$priority} - contentType:{$contentType}");

        if ($contentType=='markdown' && isset($_options['files']) && is_array($_options['files'])) {
            log::add(__CLASS__, 'debug', "Adding images to message");
            foreach ($_options['files'] as $filepath) {
                $ext = pathinfo($filepath, PATHINFO_EXTENSION);
                if (in_array($ext, array('gif', 'jpeg', 'jpg', 'png'))) {
                    $file = file_get_contents($filepath);
                    $data = base64_encode($file);
                    $message .= "  \n![](data:image/{$ext};base64,{$data})";
                }
            }
        }

        if ($message=='') {
            $error = __('Message ne peut pas être vide.', __FILE__);
            throw new Exception($error);
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
