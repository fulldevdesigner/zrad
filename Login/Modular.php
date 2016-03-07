<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Modular
 *
 * @author Juan Minaya León
 */
require_once 'Zrad/Login/Abstract.php';

class Zrad_Login_Modular extends Zrad_Login_Abstract
{

    public function __construct($moduleName = null)
    {
        $this->_moduleName = $moduleName;
        $this->_appnamespace = ucfirst($moduleName);
        $this->_util = new Zrad_Helper_Util();
        $this->_path = 'application'
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . $this->_moduleName
            . DIRECTORY_SEPARATOR . 'controllers'
            . DIRECTORY_SEPARATOR;
    }

    public function create()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $controllerName = 'Index';
        if (Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $this->_moduleName)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a controller named ' . $controllerName);
        }
        $controllerName = 'Login';
        if (Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $this->_moduleName)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a controller named ' . $controllerName);
        }
        $controllerName = 'Dashboard';
        if (Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $this->_moduleName)) {
            throw new Zend_Tool_Project_Provider_Exception('This project already has a controller named ' . $controllerName);
        }

        $this->_createControllerIndex();
        $this->_createControllerLogin();
        $this->_createControllerDashboard();
        $this->_generateForm();
        //create views
        $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Login', $this->_moduleName);
        $indexActionViewResource->create();
        $loginActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'preloading', 'Login', $this->_moduleName);
        $loginActionViewResource->create();
        $logoutActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Index', $this->_moduleName);
        $logoutActionViewResource->create();
        $indexDashboardActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Dashboard', $this->_moduleName);
        $indexDashboardActionViewResource->create();
    }

    protected function _createControllerIndex()
    {
        //create class controller Login
        $className = $this->_util->format($this->_moduleName, 6) . '_IndexController';
        $fileName = 'IndexController.php';

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
        $body .= '$this->_helper->layout->setLayout(\'admin-default\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));
        $class->setMethod($method);

        //method preDispatch
        $body = $this->_indentation . '$logged = new Zend_Session_Namespace(\'logged\');' . "\n";
        $body .= 'if ($logged->success === null)' . "\n";
        $body .= $this->_indentation . '$this->_redirect(\'/admin/login\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'preDispatch',
                'body' => $body
            ));
        $class->setMethod($method);

        //method index
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'indexAction',
                'body' => '// action body'
            ));
        $class->setMethod($method);

        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        //create file
        file_put_contents($this->_path . $fileName, $file->generate());
    }

    protected function _createControllerLogin()
    {
        // Create class controller Login
        $className = $this->_util->format($this->_moduleName, 6) . '_LoginController';
        $fileName = 'LoginController.php';

        // Property BaseUrl
        $propertyA = new Zend_CodeGenerator_Php_Property(array(
                'name' => '_baseUrl',
                'visibility' => 'private',
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'string'
                        )
                    )
                ))
            ));        
        
        // Property Logged
        $propertyB = new Zend_CodeGenerator_Php_Property(array(
                'name' => '_logged',
                'visibility' => 'private',
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'Zend_Session_Namespace'
                        )
                    )
                ))
            ));
        
        // Add
        $properties = array(); 
        $properties[] = $propertyA;
        $properties[] = $propertyB;

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setExtendedClass('Zend_Controller_Action')
            ->setProperties($properties);

        // Method init
        $body = $this->_indentation . '$this->_baseUrl = $this->getFrontController()->getBaseUrl();' . "\n";
        $body .= '$this->_logged = new Zend_Session_Namespace(\'loggedInBakend\');' . "\n\n";
        $body .= '// Solo vista' . "\n";
        $body .= '$this->_helper->layout()->disableLayout();';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));
        $class->setMethod($method);

        // Method index
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'indexAction',
                'body' => '// Verificamos si ya ha iniciado sesion' . "\n"
                . 'if (!empty($this->_logged->success)) {' . "\n"
                . $this->_indentation . '// Redireccionamos' . "\n"
                . $this->_indentation . '$this->_redirect(\'admin/dashboard\');' . "\n"
                . '}' . "\n\n"
                . '$values = array(\'\', \'\', \'\');' . "\n"
                . '$password = $this->getRequest()->getCookie(\'cla\');' . "\n"
                . '$username = $this->getRequest()->getCookie(\'usu\');' . "\n"
                . 'if ($username !== null && $password !== null) {' . "\n"
                . $this->_indentation . '$values[0] = $username;' . "\n"
                . $this->_indentation . '$values[1] = $password;' . "\n"
                . $this->_indentation . '$values[2] = \'checked="checked"\';' . "\n"
                . '}' . "\n"
                . '$this->view->values = $values;' . "\n"
            ));
        $class->setMethod($method);

        // Method authenticate
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'authenticateAction',
                'body' => $this->_getAuthenticateMethod()
            ));
        $class->setMethod($method);

        // Method logout        
        $body = $this->_indentation . 'unset($this->_logged->success);' . "\n";
        $body .= '$this->_redirect(\'/' . $this->_moduleName . '/login/\');';

        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'logoutAction',
                'body' => $body
            ));
        $class->setMethod($method);

        // Method preloading
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'preloadingAction',
                'body' => '// action body'
            ));
        $class->setMethod($method);

        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        //create file
        file_put_contents($this->_path . $fileName, $file->generate());
    }

    protected function _createControllerDashboard()
    {
        // Create Class Controller Dashboard
        $className = $this->_util->format($this->_moduleName, 6) . '_DashboardController';
        $fileName = 'DashboardController.php';        
        
        // Property BaseUrl
        $propertyA = new Zend_CodeGenerator_Php_Property(array(
                'name' => '_baseUrl',
                'visibility' => 'private',
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'string'
                        )
                    )
                ))
            ));        
        
        // Property Logged
        $propertyB = new Zend_CodeGenerator_Php_Property(array(
                'name' => '_logged',
                'visibility' => 'private',
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'Zend_Session_Namespace'
                        )
                    )
                ))
            ));
        
        // Add
        $properties = array(); 
        $properties[] = $propertyA;
        $properties[] = $propertyB;

        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setExtendedClass('Zend_Controller_Action')
            ->setProperties($properties);
        
        // Method init
        $body = $this->_indentation . '$this->_baseUrl = $this->getFrontController()->getBaseUrl();' . "\n";
        $body .= '$this->_logged = new Zend_Session_Namespace(\'loggedInBakend\');' . "\n\n";
        $body .= '$this->_helper->layout->setLayout(\'admin-default\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));
        $class->setMethod($method);
        
        // Method preDispatch
        $body = $this->_indentation . 'if ($this->_logged->success === null)' . "\n";
        $body .= $this->_indentation . '$this->_redirect(\'/admin/login/preloading\');' . "\n";        
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'preDispatch',
                'body' => $body
            ));
        $class->setMethod($method);
        
        // Method index
        $body = $this->_indentation . '$date = new ZradAid_Date();' . "\n";
        $body .= '$fecha = date(\'Y/m/d\');' . "\n"; 
        $body .= '$sFecha = $date->getDisplayDate($fecha, 2);' . "\n"; 
        $body .= '$this->view->sFecha = $sFecha;' . "\n"; 
        $body .= '$this->view->marginBottom = \'style="margin-bottom: 0 !important"\';' . "\n"; 
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'indexAction',
                'body' => $body
            ));
        $class->setMethod($method);
        
        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        //create file
        file_put_contents($this->_path . $fileName, $file->generate());    
    }

}