<?php

/**
 * @see Zrad_Helper_Util
 */
require_once 'Zrad/Helper/Util.php';

/**
 * @see Zrad_Directory
 */
require_once 'Zrad/Directory.php';

/**
 * @see Zrad_Menu
 */
require_once 'Zrad/Menu.php';

/**
 * @see Zrad_Login_Modular
 */
require_once 'Zrad/Login/Modular.php';

/**
 * @see Zrad_Login_Simple
 */
require_once 'Zrad/Login/Simple.php';

class Zrad_Login extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;

    /**
     * @var Zrad_Menu
     */
    private $_menu = null;

    /**
     * @var bool
     */
    private $_moduleName = null;

    /**
     * @var Zrad_Directory
     */
    private $_directory = null;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        if (!isset($config['registry'])) {
            throw new Exception('No se ha encontrado instacia de Registry');
        }
        $registry = $config['registry'];
        if (!($registry instanceof Zend_Tool_Framework_Registry_Interface)) {
            throw new Exception('config[registry] debe ser instancia de Zend_Tool_Framework_Registry_Interface');
        }
        $this->setRegistry($registry);

        $this->_util = new Zrad_Helper_Util();
        $this->_menu = new Zrad_Menu();
        $this->_directory = new Zrad_Directory();
        $this->_preCreate();
    }

    /**
     * Pre create
     */
    private function _preCreate()
    {
        if ($this->_util->isModular()) {
            $this->_moduleName = 'admin';
        }
    }

    public function create()
    {
        try {
            //pattern template method implementation
            if ($this->_moduleName == null) {
                $login = new Zrad_Login_Simple();
            } else {
                $login = new Zrad_Login_Modular($this->_moduleName);
            }
            $login->create();

            //update menu
            $this->_menu->setModuleName($this->_moduleName);
            $this->_menu->setControllerName('Index');
            $this->_menu->setLabel('Inicio');
            $this->_menu->create();

            //update profile
            $this->_updateProfile($this->_moduleName);

            //files to login
            $this->_directory->createLogin($this->_moduleName);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    protected function _updateProfile($moduleName)
    {
        if ($moduleName === null) {
            $this->_updateProfileSimple();
        } else {
            $this->_updateProfileModular($moduleName);
        }
    }

    private function _updateProfileModular($moduleName)
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $messages = array();

        $profileSearchParams = array();
        if ($moduleName != null && is_string($moduleName)) {
            $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        }

        // Form Auth
        $profileSearchParams = array();
        $profileSearchParams[] = 'formsDirectory';
        $formsDirectory = $profile->search($profileSearchParams);

        if (!$formsDirectory->isEnabled()) {
            $formsDirectory->setEnabled(true);
        }

        // Verificamos si tiene habilitado  PHPUnit
        require_once 'Zend/Tool/Project/Provider/Test.php';
        $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);
        if ($testingEnabled && !Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
            $testingEnabled = false;
            $message = 'Nota: PHPUnit es requerido para generar tests a los controladores';
            array_push($messages, $message);
        }

        $formName = 'Login';
        Zend_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $formName, $moduleName);

        // Controller Login
        $controllerName = 'Login';
        // Crea sólo el recurso en .zfproject.xml
        Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
        if ($testingEnabled) {
            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
            $testActionResource->getParentResource()->create();
            $testActionResource->create();
        }

        $actionName = 'logout';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        if ($testingEnabled) {
            $testActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testActionMethodResource->create();
        }

        // Action authenticate
        $actionName = 'authenticate';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        if ($testingEnabled) {
            $testActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testActionMethodResource->create();
        }        
        
        // Action authenticate
        $actionName = 'preloading';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        if ($testingEnabled) {
            $testActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testActionMethodResource->create();
        }

        // Controller Index
        $controllerName = 'Index';
        Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
        // Update unit test                
        if ($testingEnabled) {
            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
            $testActionResource->getParentResource()->create();
            $testActionResource->create();
        }
        
        // Controller Index
        $controllerName = 'Dashboard';
        Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
        // Update unit test                
        if ($testingEnabled) {
            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
            $testActionResource->getParentResource()->create();
            $testActionResource->create();
        }

        $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
        $projectProfileFile->getContext()->save();

        if (count($messages) > 0) {
            $this->_util->output($messages);
        }
    }

    private function _updateProfileSimple()
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $moduleName = null;
        //form Auth
        $profileSearchParams = array();
        $profileSearchParams[] = 'formsDirectory';
        $formsDirectory = $profile->search($profileSearchParams);

        if (!$formsDirectory->isEnabled()) {
            $formsDirectory->setEnabled(true);
        }

        $formName = 'Login';
        $formResource = Zend_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $formName, $moduleName);

        //layout Login -> enable profile hasn't been created by Zend
        //
        //controller Admin
        $controllerName = 'Admin';
        Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);

        //action index
        $actionName = 'index';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);

        //action login
        $actionName = 'login';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);

        //action logout
        $actionName = 'logout';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);

        //action authenticate
        $actionName = 'authenticate';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);

        //action home
        $actionName = 'home';
        Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);

        //update unit test
        $testControllerResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
        $testControllerResource->create();

        //replace function $this->_storeProfile()
        $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
        $projectProfileFile->getContext()->save();
    }

}
