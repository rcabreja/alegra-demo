<?php

class Application_Model_ContactMapper
{
  private $_dir;
  private $_email;
  private $_token;
  private $_client;

  public function __construct()
  {
    
    require_once('Api.php');//este archivo es el que incluye la informacion de conexion de la api de alegra

    $dataBootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
    $this->_user = $alegraUser;
    $this->_token = $alegraToken;
    $this->_dir = $alegraDir;
    $this->_client = new Zend_Http_Client();
    $this->_client->setUri($this->_dir);
    $this->_client->setConfig(array('timeout' => 30));
    $this->_client->setAuth($this->_user, $this->_token, Zend_Http_Client::AUTH_BASIC);
  }


  public function upsert(Application_Model_Contact $contact)
  {
    $type = array();
    if ($contact->getIsClient()){
      $type[] = 'client';
    }
    if ($contact->getIsProvider()){
      $type[] = 'provider';
    }
    $address = (object) [
      'address' => $contact->getAddress(),
      'city' => $contact->getCity(),
    ];

    $params = array(
      'id' => $contact->getId(),
      'name' => $contact->getName(),
      'identification' => $contact->getIdentification(),
      'phonePrimary' => $contact->getPhoneprimary(),
      'phoneSecondary' => $contact->getPhonesecondary(),
      'fax' => $contact->getFax(),
      'mobile' => $contact->getMobile(),
      'observations' => $contact->getObservations(),
      'email' => $contact->getEmail(),
      'priceList' => empty($contact->getPriceList()) ? null : $contact->getPriceList(),
      'seller' => empty($contact->getSeller()) ? null : $contact->getSeller(),
      'term' => empty($contact->getTerm()) ? null : $contact->getTerm(),
      'address' => $address,
      'type' => $type,
      'internalContacts' => $contact->getInternalContacts(),
    );

    if (null === ($id = $contact->getId())){
      $this->_client->setUri($this->_dir);
      $response = $this->_client->setRawData(json_encode($params))->request('POST');
      $data = $response->getBody();
      $data = json_decode($data, true);
    } else {
      $this->_client->setUri($this->_dir . "/$id");
      $response = $this->_client->setRawData(json_encode($params))->request('PUT');
      $data = $response->getBody();
      $data = json_decode($data, true);
    }
    return $data;
  }

  public function fetchAll($type = '', $query = '', $start = 0, $limit = 20)
  {
    $params = "?start=$start&limit=$limit&metadata=true";
    if (!empty($type) && in_array($type, array('client', 'provider'))){
      $params.= "&type=$type";
    }
    if (!empty($query)){
      $params.= "&query=$query";
    }

    $this->_client->setUri($this->_dir . $params);
    $response = $this->_client->request('GET');
    $data = $response->getBody();
    $data = json_decode($data, true);

    if (isset($data['code']) && $data['code'] !== 200){
      return $data;
    }

    $results = self::_parseData($data['data']);
    $contacts = array();

    foreach ($results as $row) {
      $contact = new Application_Model_Contact($row);
      $contacts[] = $contact;
    }

    return [
      'total' => $data['metadata']['total'],
      'data' => $contacts,
    ];
  }

  public function findById($id)//funcion para consultar un contacto
  {
    $this->_client->setUri($this->_dir . "/$id");
    $response = $this->_client->request('GET');

    $data = $response->getBody();
    $data = json_decode($data, true);

    if (isset($data['code']) && $data['code'] !== 200){
      return $data;
    }

    $result = self::_parseData([$data]);
    $contact = new Application_Model_Contact($result[0]);

    return [
      'data' => $contact,
    ];
  }

  public function delete($id)//funcion para eliminar contacto
  {
    $this->_client->setUri($this->_dir . "/$id");
    $response = $this->_client->request('DELETE');
    $data = $response->getBody();
    $data = json_decode($data, true);

    if (isset($data['code']) && $data['code'] !== 200){
      return $data;
    }

    return $data;
  }

  private function _parseData($data = []) {
    $counter = 0;
    foreach ($data as $key => $value) {
      $data[$counter]['isClient'] = false;
      $data[$counter]['isProvider'] = false;
      if (isset($value['priceList']['id'])) {
        $data[$counter]['priceList'] = [$value['priceList']['name']];
      }
      if (isset($value['seller']['id'])) {
        $data[$counter]['seller'] = [$value['seller']['name']];
      }
      if (isset($value['term']['id'])) {
        $data[$counter]['term'] = [$value['term']['name']];
      }
      if ((isset($value['type'][0]) && 'client' === $value['type'][0]) || (isset($value['type'][1]) && 'client' === $value['type'][1])) {
        $data[$counter]['isClient'] = true;
      }
      if ((isset($value['type'][0]) && 'provider' === $value['type'][0]) || (isset($value['type'][1]) && 'provider' === $value['type'][1])) {
        $data[$counter]['isProvider'] = true;
      }
      $counter++;
    }
    return $data;
  }
}
