<?php
namespace Univie\UniviePure\Service;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Christian Klettner <christian.klettner@univie.ac.at>, univie
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * PublicationController
 */
class WebService
{

    /**
     * @var $server String
     */
    protected $server = '';

    /**
     * @var $apiKey String
     */
    protected $apiKey = '';

    /**
     * @var $versionPath String
     */
    protected $versionPath = '';

    /**
     * init
     */
    public function __construct()
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['univie_pure']);
        $this->setServer($extensionConfiguration['pure_server']);
        $this->setApiKey($extensionConfiguration['apiKey']);
        $this->setVersionPath($extensionConfiguration['versionPath']);
    }


    private function checkReturnCodeErrorMsg($data)
    {
        if (isset($data['code']) && $data['code'] != '200') {
            /** @var $flashMessage FlashMessage */
            $flashMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                htmlspecialchars($data['title']),
                htmlspecialchars('PURE API Error'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, false);
            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
        }
    }

    /**
     * the call to the web service
     * @return String json or XML
     */
    public function getResponse($endpoint, $data_string, $responseType)
    {
        #	\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($data_string);

        //curl -X GET --header 'Accept: application/xml' --header 'api-key: 751734f0-a671-4183-8865-dbd771042b46' 'https://cris-entw.univie.ac.at/ws/api/59/research-outputs-meta/orderings'
        $url = $this->getServer() . $this->getVersionPath() . $endpoint;
        #	\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($url	);

        $headers = array(
            "api-key: " . $this->getApiKey() . "",
            "Content-Type: application/xml",
            "Accept: application/" . $responseType . "",
            "charset=utf-8"
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_PRIVATE, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * request a json result
     * @return array result
     */
    public function getJson($endpoint, $data_string)
    {
        $json = $this->getResponse($endpoint, $data_string, 'json');
        $result = json_decode($json, true);
        $this->checkReturnCodeErrorMsg($result);

        return $result;

    }

    public function getXml($endpoint, $data_string)
    {
        $xmlResult = $this->getResponse($endpoint, $data_string, 'xml');

        $xml = simplexml_load_string($xmlResult, null, LIBXML_PEDANTIC);
        $result = json_decode(json_encode((array)$xml), 1);
        $this->checkReturnCodeErrorMsg($result);

        return $result;

    }

    /**
     * setter for server
     */
    private function setServer($server)
    {
        $this->server = $server;
    }

    /** getter for server
     * @return String server
     */
    private function getServer()
    {
        return $this->server;
    }

    /**
     * setter for api-key
     */
    private function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * getter for api-key
     * @return String api-key
     */
    private function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * setter for version path e.g. /ws/api/59/
     */
    private function setVersionPath($versionPath)
    {
        $this->versionPath = $versionPath;
    }

    /**
     * getter for version path
     * @return String versionPath
     */
    private function getVersionPath()
    {
        return $this->versionPath;
    }
}

?>
