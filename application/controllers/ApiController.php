<?php
class ApiController extends Zend_Controller_Action
{
  public function indexAction(){
    $this->getHelper('Layout')->disableLayout();
    $this->getHelper('ViewRenderer')->setNoRender();

    if (null != ($id = $this->_request->getQuery('id'))) {
      $contacts = new Application_Model_ContactMapper();
      $data = $contacts->findById($id);

      $this->getResponse()->setHeader('Content-Type', 'application/json');
      return $this->_helper->json->sendJson($data);
    }

    $type = $this->_request->getQuery('type') ? $this->_request->getQuery('type') : '';
    $start = intval($this->_request->getQuery('start')) ? intval($this->_request->getQuery('start')) : 0;
    $limit = intval($this->_request->getQuery('limit')) ? intval($this->_request->getQuery('limit')) : 20;
    $page = intval($this->_request->getQuery('page'));

    $contacts = new Application_Model_ContactMapper();
    $data = $contacts->fetchAll($type, '', $start, $limit);

    $this->getResponse()->setHeader('Content-Type', 'application/json');
    return $this->_helper->json->sendJson($data);
  }

  public function createAction(){
    $this->getHelper('Layout')->disableLayout();
    $this->getHelper('ViewRenderer')->setNoRender();

    $params = (array) json_decode($this->getRequest()->getPost('data'));
    unset($params['id']);

    $contact = new Application_Model_ContactMapper();
    $form = new Application_Model_Contact($params);
    $data = $contact->upsert($form);

    $this->getResponse()->setHeader('Content-Type', 'application/json');
    return $this->_helper->json->sendJson($data);
  }

  public function updateAction(){
    $this->getHelper('Layout')->disableLayout();
    $this->getHelper('ViewRenderer')->setNoRender();

    $params = (array) json_decode($this->getRequest()->getPost('data'));

    $contact = new Application_Model_ContactMapper();
    $form = new Application_Model_Contact($params);
    $data = $contact->upsert($form);

    $this->getResponse()->setHeader('Content-Type', 'application/json');
    return $this->_helper->json->sendJson($data);
  }

  public function deleteAction(){
    $this->getHelper('Layout')->disableLayout();
    $this->getHelper('ViewRenderer')->setNoRender();

    $param = json_decode($this->getRequest()->getPost('data'));

    $contact = new Application_Model_ContactMapper();
    if (count($param) > 1) {
      foreach ($param as $key => $value) {
        $data = $contact->delete($value->id);
      }
      $this->getResponse()->setHeader('Content-Type', 'application/json');
      if (isset($data['code']) && '200' == $data['code']) {
        return $this->_helper->json->sendJson([
          'code' => 200,
          'message' => 'Los contactos fueron eliminados correctamente.',
        ]);
      }
    }
    $data = $contact->delete($param->id);

    $this->getResponse()->setHeader('Content-Type', 'application/json');
    return $this->_helper->json->sendJson($data);
  }
}
