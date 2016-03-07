<?php

/**
 * @see Zrad_Crud_Controller
 */
require_once 'Zrad/Crud/Controller.php';

/**
 * @see Zrad_Model
 */
require_once 'Zrad/Model.php';

/**
 * @see Zrad_Form
 */
require_once 'Zrad/Form.php';

/**
 * @see Zrad_Directory
 */
require_once 'Zrad/Directory.php';

/**
 * @see Zrad_Menu
 */
require_once 'Zrad/Menu.php';

/**
 * @see Zrad_Crud_Jscript
 */
require_once 'Zrad/Crud/Jscript.php';

/**
 * @see Zrad_Crud_View
 */
require_once 'Zrad/Crud/View.php';

class Zrad_Crud extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{

    /**
     * @var Zrad_Crud_Controller
     */
    private $_controller = null;
    /**
     * @var Zrad_Model
     */
    private $_model = null;
    /**
     * @var Zrad_Form
     */
    private $_form = null;
    /**
     * @var Zrad_Menu
     */
    private $_menu = null;
    /**
     * @var string|null
     */
    private $_moduleName = null;
    /**
     * @var string|null
     */
    private $_tableName = null;
    /**
     * @var string|null
     */
    private $_controllerName = null;
    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;
    /**
     * @var Zrad_Directory
     */
    private $_directory = null;
    /**
     * @var Zrad_Crud_Jscript
     */
    private $_jscript = null;
    /**
     * @var Zrad_Crud_Jscript
     */
    private $_view = null;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        if (!isset($config['tableName'])) {
            throw new Exception('No se ha definido la tabla o nodo');
        }
        if (!isset($config['moduleName'])) {
            throw new Exception('No se ha definido un modulo');
        }
        if (!isset($config['controllerName'])) {
            throw new Exception('No se ha definido un controlador');
        }
        if (!isset($config['registry'])) {
            throw new Exception('No se ha encontrado instacia de Registry');
        }
        $registry = $config['registry'];
        if (!($registry instanceof Zend_Tool_Framework_Registry_Interface)) {
            throw new Exception('config[registry] debe ser instancia de Zend_Tool_Framework_Registry_Interface');
        }
        $this->_tableName = $config['tableName'];
        $this->_moduleName = $config['moduleName'];
        $this->_controllerName = $config['controllerName'];
        $this->setRegistry($registry);
        $this->_util = new Zrad_Helper_Util();
        $this->_directory = new Zrad_Directory();

        $method = $config['method'];
        //creamos los recursos
        if ($method == 'create') {
            //$this->_createResources();
        }

        if ($this->_moduleName !== null) {
            $config['moduleName'] = 'admin';
        }

        //creamos el controlador*/
        //$this->_controller = new Zrad_Crud_Controller($config);
        //$this->_jscript = new Zrad_Crud_Jscript($config);
        //$this->_view = new Zrad_Crud_View($config);
    }

    private function _createResources()
    {
        $messages = array();
        $message = 'A';
        array_push($messages, $message);
        $this->_util->output($messages);
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        // determine if testing is enabled in the project
        require_once 'Zend/Tool/Project/Provider/Test.php';
        $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);

        if ($testingEnabled && !Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
            $testingEnabled = false;
            $message = 'Nota: PHPUnit es requerido para generar tests a los controladores';
            array_push($messages, $message);
        }

        // Creamos el controlador
        $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $this->_controllerName, $this->_moduleName);
        $controllerResource->create();

        if ($testingEnabled) {
            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $this->_controllerName, 'index', $this->_moduleName);
            $testActionResource->getParentResource()->create();
            $testActionResource->create();
        }

        // Action index no contiene test ya que se ha creado en el controlador
        $actionName = 'index';
        $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $indexActionResource->create();

        // Action list
        $actionName = 'list';
        $listActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $listActionResource->create();
        $listActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $listActionViewResource->create();

        if ($testingEnabled) {
            $testListActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $this->_controllerName, $actionName, $this->_moduleName);
            $testListActionMethodResource->create();
        }

        // Action pagination
        $actionName = 'pagination';
        $paginationActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $paginationActionResource->create();

        if ($testingEnabled) {
            $testPaginationActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $this->_controllerName, $actionName, $this->_moduleName);
            $testPaginationActionMethodResource->create();
        }

        // Action new
        $actionName = 'new';
        $newActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $newActionResource->create();
        $newActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $newActionViewResource->create();

        if ($testingEnabled) {
            $testPaginationActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $this->_controllerName, $actionName, $this->_moduleName);
            $testPaginationActionMethodResource->create();
        }

        // Action edit
        $actionName = 'edit';
        $editActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $editActionResource->create();
        $editActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $editActionViewResource->create();

        if ($testingEnabled) {
            $testEditActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $this->_controllerName, $actionName, $this->_moduleName);
            $testEditActionMethodResource->create();
        }

        // Action delete
        $actionName = 'delete';
        $deleteActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $this->_controllerName, $this->_moduleName);
        $deleteActionResource->create();

        if ($testingEnabled) {
            $testDeleteActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $this->_controllerName, $actionName, $this->_moduleName);
            $testDeleteActionMethodResource->create();
        }

        // Actualizamos .zfproject
        // Replace function $this->_storeProfile()
        $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
        $projectProfileFile->getContext()->save();
    }

    public function create()
    {
        try {
            $this->createBackend();
            $this->createFrontend();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function update()
    {
        try {
            $this->updateBackend();
            $this->updateFrontend();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createBackend()
    {
        try {
            //$this->_controller->createBackend();
            //creating view and js validation
            /* $jscript = $this->_jscript->createList();
              $this->_view->createList($jscript);
              $jscript = $this->_jscript->createNew();
              $this->_view->createNew($jscript);
              $this->_view->createEdit($jscript); */
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createFrontend()
    {
        try {
            //comming soon
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateBackend()
    {
        try {
            $this->_controller->createBackend();
            //creating view and js validation
            $jscript = $this->_jscript->createList();
            $this->_view->createList($jscript);
            $jscript = $this->_jscript->createNew();
            $this->_view->createNew($jscript);
            $this->_view->createEdit($jscript);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateFrontend()
    {
        try {
            //comming soon
        } catch (Exception $e) {
            throw $e;
        }
    }

}