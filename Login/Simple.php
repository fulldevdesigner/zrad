<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Simple
 *
 * @author Juan Minaya León
 */
require_once 'Zrad/Login/Abstract.php';

class Zrad_Login_Simple
    extends Zrad_Login_Abstract
{    

    public function __construct()
    {        
        $this->_util = new Zrad_Helper_Util();
        $this->_path = 'application'
            . DIRECTORY_SEPARATOR . 'controllers'
            . DIRECTORY_SEPARATOR;
    }

    public function create()
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $controllerName = 'Admin';
        if (Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $this->_moduleName)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a controller named ' . $controllerName);
        }
        $this->_createControllerAdmin();
        $this->_generateForm();
        //create views
        $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Admin', null);
        $indexActionViewResource->create();
        $loginActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'login', 'Admin', null);
        $loginActionViewResource->create();
        $logoutActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'logout', 'Admin', null);
        $logoutActionViewResource->create();
        $homeActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'home', 'Admin', null);
        $homeActionViewResource->create();
        //update Bootstrap
        $this->_updateBootstrap();
    }

    protected function _createControllerAdmin()
    {
        //create class controller Admin
        $className = 'AdminController';
        $fileName = 'AdminController.php';

        //property
        $property = new Zend_CodeGenerator_Php_Property(array(
                'name' => '_baseUrl',
                'visibility' => 'private',
                'defaultValue' => null,
            ));

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setExtendedClass('Zend_Controller_Action')
            ->setProperty($property);

        //method init
        $body = $this->_indentation . '$this->_baseUrl = $this->getFrontController()->getBaseUrl();' . "\n";
        $body .= '$this->_helper->layout->setLayout(\'admin-login\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));
        $class->setMethod($method);

        //method index
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'indexAction',
                'body' => $this->_indentation . '$this->_forward(\'login\');'
            ));
        $class->setMethod($method);

        //method login
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'loginAction',
                'body' => '// action body'
            ));
        $class->setMethod($method);

        //method authenticate
        $body = $this->_indentation . '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n" . "\n";

        $body .= '$request = $this->getRequest();' . "\n";
        $body .= '$form = new Application_Form_Login();' . "\n" . "\n";

        $body .= 'if (!$this->getRequest()->isPost()) {' . "\n";
        $body .= $this->_indentation . '$result = array(\'state\' => \'deny\', \'response\' => \'Debe registrar sus credenciales\');' . "\n";
        $body .= $this->_indentation . 'echo json_encode($result);' . "\n";
        $body .= $this->_indentation . 'exit();' . "\n";
        $body .= '}' . "\n" . "\n";

        $body .= 'if (!$form->isValid($request->getPost())) {' . "\n";
        $body .= $this->_indentation . '$result = array(\'state\' => \'deny\', \'response\' => \'Revise sus credenciales\');' . "\n";
        $body .= $this->_indentation . 'echo json_encode($result);' . "\n";
        $body .= $this->_indentation . 'exit();' . "\n";
        $body .= '}' . "\n" . "\n";

        $body .= '$username = $form->getValue(\'username\');' . "\n";
        $body .= '$password = $form->getValue(\'password\');' . "\n" . "\n";

        $body .= 'if ($username == \'admin\' && $password == \'admin\') {' . "\n";
        $body .= $this->_indentation . '$logged = new Zend_Session_Namespace(\'logged\');' . "\n";
        $body .= $this->_indentation . '$logged->status = true;' . "\n";
        $body .= $this->_indentation . '$result = array(\'state\' => \'accept\', \'response\' => \'Adminisrador\');' . "\n";
        $body .= '} else {' . "\n";
        $body .= $this->_indentation . '$result = array(\'state\' => \'deny\', \'response\' => \'usuario o clave incorrecta\');' . "\n";
        $body .= '}' . "\n" . "\n";

        $body .= 'echo json_encode($result);';

        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'authenticateAction',
                'body' => $body
            ));
        $class->setMethod($method);

        //method logout
        $body = $this->_indentation . '$logged = new Zend_Session_Namespace(\'loggedInBakend\');' . "\n";
        $body .= 'unset($logged->success);' . "\n";
        $body .= '$this->_redirect(\'/admin/\');';

        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'logoutAction',
                'body' => $body
            ));
        $class->setMethod($method);

        //method home
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'homeAction',
                'body' => '$this->_helper->layout->setLayout(\'admin-default\');'
            ));
        $class->setMethod($method);

        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        //create file
        file_put_contents($this->_path . $fileName, $file->generate());
    }

}
