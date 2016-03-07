<?php

/**
 * Description Zrad
 *
 * @author Juan Minaya
 */
require_once 'Zend/Tool/Framework/Registry.php';
require_once 'Zend/Tool/Project/Provider/Abstract.php';
require_once 'Zend/Tool/Framework/Provider/Pretendable.php';
require_once 'Zend/Tool/Project/Profile/Iterator/ContextFilter.php';
require_once 'Zend/Tool/Project/Profile/Iterator/EnabledResourceFilter.php';

/**
 * @see Zrad_Model
 */
require_once 'Zrad/Model.php';

/**
 * @see Zrad_Project
 */
require_once 'Zrad/Project.php';

/**
 * @see Zrad_Form
 */
require_once 'Zrad/Form.php';

/**
 * @see Zrad_Module
 */
require_once 'Zrad/Module.php';

/**
 * @see Zrad_Crud
 */
require_once 'Zrad/Crud.php';

/**
 * @see Zrad_Mapper
 */
require_once 'Zrad/Mapper.php';

/**
 * @see Zrad_Db
 */
require_once 'Zrad/Db/Model.php';

require_once 'Zrad/Db/SqlImport.php';

require_once 'Zrad/Js/Build.php';

require_once 'Zrad/View/Build.php';

require_once 'Zrad/Helper/Db.php';

class Zrad_Tool_Project_Provider_Zrad extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{
    /**
     * Update -  esta opcion solo las tendrá los siguientes métodos:
     * update-model,
     * update-form-frontend,
     * update-crud-backend,   
     * update-crud
     */

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;

    /**
     * @var array
     */
    private $_messages = array();

    /**
     * @var array
     */
    protected $_specialties = array('DomainObject', 'Mapper');

    /**
     * @var const
     */

    const DB_ERROR = 'Los parametros de conexion a la BD son incorrectos';

    /**
     * Valida si es freeware o comercial version
     * @var const
     */
    const FREEWARE = false;

    /**
     * Contruct
     */
    public function __construct()
    {
        $this->_util = new Zrad_Helper_Util();
        //context
        $contextRegistry = Zend_Tool_Project_Context_Repository::getInstance();
        $contextRegistry->addContextsFromDirectory(
            dirname(dirname(__FILE__)) . '/Context/Zf/', 'Zrad_Tool_Project_Context_Zf_'
        );
    }

    /**
     * @param string $name Nombre del proyecto     
     */
    public function createProject($name)
    {
        try {
            require_once 'Zend/Tool/Project/Provider/Test.php';
            $unit = (!Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) ? false : true;

            $config = array(
                'name' => $name,
                'registry' => $this->_registry
            );
            $project = new Zrad_Project($config);
            $project->createModular($unit);
            //$project->createSimple();
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    private function hasResourceForm(Zend_Tool_Project_Profile $profile, $tableName, $formName, $moduleName, $controllerName = null, $actionName = null)
    {
        try {
            // Camel cased.
            if (preg_match('#[_-]#', $formName)) {
                throw new Zend_Tool_Project_Provider_Exception('El nombre del formulario debe ser camel cased.');
            }

            // Verificamos si existe la tabla
            $model = new Zrad_db_Model();
            if (!$model->existsTable($tableName)) {
                throw new Exception('Tabla "' . $tableName . '" no existe.');
            }

            // Verificamos si el modulo existe
            if ($this->_util->isModular() && !$this->_util->existsModule($moduleName)) {
                throw new Exception('Modulo "' . $moduleName . '" no ha sido encontrado.');
            }

            // Verificamos si la accion y el controlador existen
            if ($controllerName !== null && $actionName !== null) {
                if (!Zend_Tool_Project_Provider_Action::hasResource($profile, $actionName, $controllerName, $moduleName)) {
                    throw new Zend_Tool_Project_Provider_Exception('Accion "' . $actionName . '" no ha sido encontrado.');
                }
            }

            return Zend_Tool_Project_Provider_Form::hasResource($profile, $formName, $moduleName);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function _createResourcesCrudBackend($controllerName, $moduleName)
    {
        // Resource to controller
        $messages = array();
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
        $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
        $controllerResource->create();

        if ($testingEnabled) {
            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
            $testActionResource->getParentResource()->create();
            $testActionResource->create();
        }

        // Action index no contiene test ya que se ha creado en el controlador
        $actionName = 'index';
        $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $indexActionResource->create();

        // Action list
        $actionName = 'list';
        $listActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $listActionResource->create();
        $listActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $listActionViewResource->create();

        if ($testingEnabled) {
            $testListActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testListActionMethodResource->create();
        }

        // Action pagination
        $actionName = 'pagination';
        $paginationActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $paginationActionResource->create();

        if ($testingEnabled) {
            $testPaginationActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testPaginationActionMethodResource->create();
        }

        // Action new
        $actionName = 'new';
        $newActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $newActionResource->create();
        //$newActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        //$newActionViewResource->create();

        if ($testingEnabled) {
            $testPaginationActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testPaginationActionMethodResource->create();
        }

        // Action edit
        $actionName = 'edit';
        $editActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $editActionResource->create();
        $editActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'entidad', $controllerName, $moduleName);
        $editActionViewResource->create();

        if ($testingEnabled) {
            $testEditActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testEditActionMethodResource->create();
        }

        // Action delete
        $actionName = 'delete';
        $deleteActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, $actionName, $controllerName, $moduleName);
        $deleteActionResource->create();

        if ($testingEnabled) {
            $testDeleteActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, $actionName, $moduleName);
            $testDeleteActionMethodResource->create();
        }

        // Actualizamos .zfproject
        // Replace function $this->_storeProfile()
        $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
        $projectProfileFile->getContext()->save();
    }

    /**
     * Crea un formulario en el backend integrado con Zrad UI
     * 
     * @param string $tableName Nombre fisica de la tabla
     * @param string $name Nombre que tendra el formulario (sie no existe el formulario, se crea uno con el nombre de la tabla)     
     * @param string|int $inCaptcha Si lo crea con captcha o no "false" o 1
     * @param string $module Nombre del modulo por defecto admin
     */
    public function createFormBackend($tableName, $name = null, $inCaptcha = 'false', $module = 'admin')
    {
        try {
            $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;
            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verficamos los datos de conexion a la BD
            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();

            $messages = array();
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

            //verificamos captcha
            /* if (!$includedCaptcha) {
              $config = new Zend_Config_Ini(
              'application' . DIRECTORY_SEPARATOR .
              'configs' . DIRECTORY_SEPARATOR . 'zrad.ini');

              $includedCaptcha = (isset($config->form->frontend->includedCaptcha) && $config->form->frontend->includedCaptcha == 1) ? true : false;
              } */
            // Seteamos el nombre del formulario
            if ($name === null) {
                $name = $this->_util->format($tableName, 8);
            }

            // Check that threr is string
            if (!is_string($name)) {
                throw new Zend_Tool_Project_Provider_Exception('Nombre del formulario debe ser tipo texto.');
            }

            // Check that there is not a dash or underscore, return if doesnt match regex
            if (preg_match('#[_-]#', $name)) {
                throw new Zend_Tool_Project_Provider_Exception('Nombre del formulario debe ser camel cased.');
            }

            // Seteamos el nombre del modulo
            if ($this->_util->isModular() && $module === null) {
                $module = $this->_util->generateNameModule($tableName);
            }

            //salvamos el nombre original de la tabla
            $originalTableName = $tableName;

            if (!Zend_Tool_Project_Provider_Form::hasResource($this->_loadedProfile, $name, $module)) {
                //creamos el formulario
                $formResource = Zend_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $name, $module);
                $formResource->create();

                //actualizamos el proyecto
                $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                $projectProfileFile->getContext()->save();
            }

            //mapeamos la tabla
            $mapper = new Zrad_Mapper($originalTableName);
            $mapper->run();

            $config = array(
                'originalTableName' => $originalTableName,
                'tableName' => $tableName,
                'moduleName' => $module,
                'registry' => $this->_registry,
                'mapper' => $mapper->getResult(),
                'target' => 'backend',
                'captcha' => $inCaptcha
            );

            $form = new Zrad_Form_Build($config);
            $form->setFormName($name);
            $form->setTarget($config['target']);
            $form->create();

            $message = 'Formulario "' . $name . '" ha sido creada en: "modules/' . $module . '/forms".';
            array_push($messages, $message);
            $this->_util->output($messages);
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    /**
     * Crea un formulario simple
     * 
     * @param string $tableName Nombre fisica de la tabla
     * @param string $name Nombre que tendra el formulario (se asume que no existe el formulario)
     * @param string $module Nombre del modulo donde se va ha crear (se asume que no existe el modulo)
     * @param string|int $inCaptcha Si lo crea con captcha o no "false" o 1
     */
    public function createForm($tableName, $name, $module, $inCaptcha = 'false')
    {
        try {

            $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;

            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verificamos si existe conexion a la BD Mediante model
            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();

            // Verificamos si el modulo existe
            if (!$this->_util->existsModule($module)) {
                throw new Exception('El modulo no existe');
            }

            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

            /* if ($this->hasResourceForm($this->_loadedProfile, $tableName, $formName, $moduleName)) {
              throw new Exception('Formulario "' . $formName . '" ya existe.');
              } */

            $messages = array();

            // Salvamos el nombre original de la tabla
            $originalTableName = $tableName;

            // Mapeamos la tabla
            $mapper = new Zrad_Mapper($originalTableName);
            $mapper->run();

            $config = array(
                'originalTableName' => $originalTableName,
                'tableName' => $tableName,
                'moduleName' => $module,
                'registry' => $this->_registry,
                'mapper' => $mapper->getResult(),
                'target' => 'frontend',
                'captcha' => $inCaptcha
            );

            // Creamos el formulario
            $formResource = Zend_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $name, $module);
            $formResource->create();
            // Actualizamos el proyecto
            $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();

            $form = new Zrad_Form_Build($config);
            $form->setFormName($name);
            $form->setTarget($config['target']);
            $form->create();

            if ($inCaptcha) {
                $source = $this->_util->getPathTemplate()
                    . DIRECTORY_SEPARATOR . 'Public'
                    . DIRECTORY_SEPARATOR . 'captcha';

                $destination = 'public' . DIRECTORY_SEPARATOR . 'resources'
                    . DIRECTORY_SEPARATOR . 'captcha';
                $this->_util->fullCopy($source, $destination);
            }

            $message = 'Formulario "' . $name . '" ha sido creado en: "' . $module . '"';
            array_push($messages, $message);
            $this->_util->output($messages);
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    public function createFormFrontend($tableName, $name, $module = null, $controller = null, $action = 'index', $inCaptcha = 'false')
    {
        $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;
        $this->_processFormFrontend('create', $tableName, $name, $module, $inCaptcha, $controller, $action);
    }

    public function updateFormFrontend($tableName, $name, $module, $controller, $actionName, $inCaptcha = 'false')
    {
        $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;
        $this->_processFormFrontend('update', $tableName, $name, $module, $inCaptcha, $controller, $actionName);
    }

    /**
     * @param string $method Metodo "create" o "update"
     * @param string $tableName Nombre de tabla
     * @param string $formName Nombre del Formulario
     * @param string $moduleName Nombre del modulo por defecto es nulo y te crea un modulo
     * @param boolean $captcha true o false
     * @param string $controllerName El valor puede ser "create" o coloca el nombre del controlador 
     * @param string $actionName El nombre de la accion
     * @param boolean $facebook Si es de tipo facebook o no
     */
    private function _processFormFrontend($method, $tableName, $formName = 'Nuevo', $moduleName = null, $captcha = false, $controllerName = null, $actionName = 'index', $facebook = false)
    {
        try {

            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verificamos si existe conexion a la BD Mediante model
            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();

            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

            // Seteamos el nombre del modulo
            if ($this->_util->isModular() && $moduleName === null) {
                $moduleName = $this->_util->generateNameModule($tableName);
            }

            // Verificamos si existe un formulario creado con tu mismo nombre
            if ($method == 'create') {
                if ($this->hasResourceForm($this->_loadedProfile, $tableName, $formName, $moduleName)) {
                    throw new Exception('Formulario "' . $formName . '" ya existe.');
                }
            } else {
                if (!$this->hasResourceForm($this->_loadedProfile, $tableName, $formName, $moduleName, $controllerName, $actionName)) {
                    throw new Exception('Formulario "' . $formName . '" no existe.');
                }
            }

            // Verificamos captcha
            /* if (!$includedCaptcha) {
              $config = new Zend_Config_Ini(
              'application' . DIRECTORY_SEPARATOR .
              'configs' . DIRECTORY_SEPARATOR . 'zrad.ini');
              // ? : se llama operador ternario
              $includedCaptcha = (isset($config->form->includedCaptcha) && $config->form->includedCaptcha == 1) ? true : false;
              } */

            // Si no se especifica el controlador se crea uno
            if (($controllerName === null && $method == 'create') && !$facebook) {
                $controllerName = 'Registro';
                if (!Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $moduleName)) {
                    $messages = array();
                    //creamos el controlador
                    require_once 'Zend/Tool/Project/Provider/Test.php';
                    $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);
                    if ($testingEnabled && !Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
                        $testingEnabled = false;
                        $message = 'Nota: PHPUnit es requerido para generar tests a los controladores';
                        array_push($messages, $message);
                    }

                    $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
                    $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', $controllerName, $moduleName);
                    $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', $controllerName, $moduleName);
                    if ($testingEnabled) {
                        $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
                    }
                    $controllerResource->create();

                    $indexActionResource->create();
                    $indexActionViewResource->create();
                    if ($testingEnabled) {
                        $testActionResource->getParentResource()->create();
                        $testActionResource->create();
                    }
                    $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                    $projectProfileFile->getContext()->save();
                    $message = 'Controlador "Registro" ha sido creado';
                    array_push($messages, $message);
                    $this->_util->output($messages);
                } else {
                    throw new Exception('El controlador "' . $controllerName . '" ya existe en el modulo "' . $moduleName . '"');
                }
            } else {
                // Verificamos si existe el controlador y accion ingresados
                if (!Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $moduleName)) {
                    throw new Exception('El controlador "' . $controllerName . '" no existe en el modulo "' . $moduleName . '"');
                }
                // Verificamos si existe la accion
                if (!Zend_Tool_Project_Provider_Action::hasResource($this->_loadedProfile, $actionName, $controllerName, $moduleName)) {
                    throw new Exception('La accion "' . $actionName . '" no existe en el controlador "' . $controllerName . '"');
                }
            }

            $messages = array();

            // Salvamos el nombre original de la tabla
            $originalTableName = $tableName;

            // Mapeamos la tabla
            $mapper = new Zrad_Mapper($originalTableName);
            $mapper->run();

            $config = array(
                'originalTableName' => $originalTableName,
                'tableName' => $tableName,
                'moduleName' => $moduleName,
                'controllerName' => $controllerName,
                'actionName' => $actionName,
                'registry' => $this->_registry,
                'mapper' => $mapper->getResult(),
                'target' => 'frontend',
                'captcha' => $captcha
            );

            if ($facebook) {
                $config['isFacebook'] = true;
            }

            // Creamos el formulario
            $formResource = Zend_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $formName, $moduleName);
            $formResource->create();
            // Actualizamos el proyecto
            $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();

            $form = new Zrad_Form_Build($config);
            $form->setFormName($formName);
            $form->setTarget($config['target']);
            $form->create();

            if (!$facebook && $method == 'create') {
                $controller = new Zrad_Crud_Controller($config);
                $controller->setFormName($formName);
                $controller->createFrontend();
            }

            $js = new Zrad_Js_Build($config);
            $js->setTarget($config['target']);
            $jscript = $js->createForm($actionName);

            $view = new Zrad_View_Build($config);
            $view->setJscript($jscript);
            $view->setFormName($formName);
            $view->createForm($actionName);

            if ($captcha) {
                $source = $this->_util->getPathTemplate()
                    . DIRECTORY_SEPARATOR . 'Public'
                    . DIRECTORY_SEPARATOR . 'captcha';

                $destination = 'public' . DIRECTORY_SEPARATOR . 'resources'
                    . DIRECTORY_SEPARATOR . 'captcha';
                $this->_util->fullCopy($source, $destination);
            }

            $message = 'Formulario "' . $formName . '" ha sido creado en: "' . $moduleName . '/' . $this->_util->format($controllerName, 2) . '/' . $this->_util->format($actionName, 2) . '"';
            array_push($messages, $message);
            $this->_util->output($messages);
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            //i18n
            $message = 'Controller ' . $controllerName . ' was not found.';
            $eMessage = $e->getMessage();

            if ($eMessage == $message) {
                $eMessage = 'Controlador "' . $controllerName . '" no ha sido encontrado.';
            }

            //fin i18n
            $messages = array($eMessage);
            $this->_util->output($messages);
        }
    }

    public function createModel($tableName, $moduleName = null, $basic = 'false')
    {
        $this->_processModel($tableName, $moduleName, $basic);
    }

    public function updateModel($tableName, $moduleName)
    {
        $this->_processModel($tableName, $moduleName);
    }

    private function _processModel($tableName, $moduleName, $basic)
    {
        try {
            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();

            $messages = array();
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

            // Verificamos si existe conexion a la BD Mediante model
            $model = new Zrad_Db_Model();

            // Verificamos si es modelo basico o completo(paginacion, filtros, etc)
            $basic = (((int) $basic) == 1) ? true : false;

            if ($moduleName === null) {
                $moduleName = $this->_util->generateNameModule($tableName);
            }

            // Salvamos el nombre original de la tabla
            $originalTableName = $tableName;

            // Formateamos el nombre de la tabla
            $tableName = $this->_util->formatTableName($tableName);

            // Verificamos si existe el modulo
            if (!$this->_util->existsModule($moduleName)) {
                throw new Exception('El modulo "' . $moduleName . '" no existe');
            }

            //mapeamos la tabla
            $mapper = new Zrad_Mapper($originalTableName);
            $mapper->run();

            $config = array(
                'originalTableName' => $originalTableName,
                'tableName' => $tableName,
                'moduleName' => $moduleName,
                'registry' => $this->_registry,
                'mapper' => $mapper->getResult(),
                'basic' => $basic
            );

            $model = new Zrad_Model($config);
            $model->create();
            $messages = array(
                'Modelo de la entidad "' . $tableName . '" ha sido creado'
            );

            if ($model->getIsReflection()) {
                $messages = array(
                    'Modelo de la entidad "' . $tableName . '" ha sido actualizado'
                );
            }
            $this->_util->output($messages);
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    /**
     * Create Domain Object this method not use Reflection
     *
     * @param string $actualTableName
     * @param string|null $moduleName
     */
    public function createModelDomainObject($tableName, $moduleName = null)
    {
        try {

            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verificamos la conexion con la BD
            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();

            $config = array(
                'tableName' => $tableName,
                'moduleName' => $moduleName,
                'registry' => $this->_registry
            );
            $model = new Zrad_Model($config);
            $model->createDomainObject();
            //mesages
            $messages = array('Domain Object de la entidad "' . $tableName . '", ha sido creado');
            $this->_util->output($messages);
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    /**
     * Create Mapper this method use Reflection
     *
     * @param string $actualTableName
     * @param string|null $moduleName
     */
    public function createModelMapper($tableName, $moduleName = null, $basic = 'false')
    {

        try {

            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verificamos la conexion con la BD
            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();

            // Verificamos si es modelo basico o completo(paginacion, filtros, etc)
            $basic = (((int) $basic) == 1) ? true : false;

            $config = array(
                'tableName' => $tableName,
                'moduleName' => $moduleName,
                'basic' => $basic,
                'registry' => $this->_registry
            );
            $model = new Zrad_Model($config);
            $model->createMapper();
            //messsages
            $messages = array('Mapper de la entidad "' . $tableName . '", ha sido creado');
            if ($model->getIsReflection()) {
                $messages = array('Mapper de la entidad "' . $tableName . '", ha sido actualizado');
            }
            $this->_util->output($messages);
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    public function initBackend()
    {
        try {

            if (self::FREEWARE) {
                throw new Exception('Visite www.zend-rad.com y consulte sobre la version completa');
            } else {
                $config = array(
                    'registry' => $this->_registry
                );
                $module = new Zrad_Module($config);
                $module->initBackend();
                $messages = array('El backend se ha inicializado');
                $this->_util->output($messages);
            }
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    /**
     * Crea el modulo de Sorteo
     * 
     * @param string $competidorTable Tabla donde se guardan los datos del concursante
     * @param string|null $workTable Nombre de la tabla donde se guarda el trabajo del concursante (Foto, Video, Frase, etc)
     */
    public function initFacebookSorteo($competidorTable = 'participante', $workTable = null)
    {
        $messages = array();
        try {
            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verificamos si ha inicializado en backend
            if (!$this->_util->existsModule('admin')) {
                throw new Exception('El Backend no ha sido inicializado');
            }

            // Verificamos si existe el modulo ganador
            if ($this->_util->existsModule('ganador')) {
                throw new Exception('El modulo "sorteo" ya existe');
            }

            // Helper de Modelos
            $model = new Zrad_Db_Model();

            // Verificamos si existe la tabla participante
            if (!$model->existsTable($competidorTable)) {
                throw new Exception('La tabla "' . $competidorTable . '" no existe, necesario para sorteo');
            }

            // Creamos el modulo sorteo
            //$this->createModule('sorteo');
            // Creamos la tabla sorteo
            $db = $model->getDb()->getAdapter();
            $db->getConnection();
            $sql = 'CREATE TABLE IF NOT EXISTS `ganador` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `participante_id` INT(11) NOT NULL,
            `tabla1_id` INT DEFAULT NULL,
            `tabla2_id` INT DEFAULT NULL,
            `campo1` VARCHAR(100) DEFAULT NULL,
            `tipo` TINYINT(1) DEFAULT NULL,
            `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `index_participante_idx` (`participante_id` ASC),
            CONSTRAINT `fk_ganador_participante1`
            FOREIGN KEY (`participante_id`)
            REFERENCES `participante` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE) 
            ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
            $db->query($sql);

            // Cramos el modulo ganador
            $this->createModule('ganadores');

            // Creamos el modelo de la tabla ganadores
            $this->createModel('ganador', 'ganadores');

            // Creamos los recursos para el controlador Sorteo
            $this->_createResourcesRaffle();

            // Creamos el controlador
            $config = array(
                'moduleName' => 'admin',
                'controllerName' => 'Sorteo',
                'registry' => $this->_registry,
                'mapper' => array(),
                'tableName' => $workTable
            );

            // Verificamos si existe la tabla
            if (null !== $workTable) {

                // Verificamos si existe la tabla workTable
                if (!$model->existsTable($workTable)) {
                    throw new Exception('La tabla "' . $workTable . '" no existe, necesario para sorteo');
                }

                // Mapeamos la tabla run() lanza una excepcion en caso no exista la tabla
                $mapper = new Zrad_Mapper($workTable);
                $mapper->run();


                $config['tableName'] = $workTable;
                $config['mapper'] = $mapper->getResult();
            }

            $controller = new Zrad_Crud_Controller($config);
            $controller->createRaffle();

            // Creeamos el modelo
            $mapper = new Zrad_Model_Mapper($config);
            $mapper->createRaffle($workTable);

            // Creamos los Js
            $js = new Zrad_Js_Build($config);
            $view = new Zrad_View_Build($config);
            $jscript = $js->createListRaffle($workTable);

            // Vista list
            $view->setJscript($jscript);
            $view->createListRaffle();

            // Creamos el menu
            $menu = new Zrad_Menu();
            $menu->create('sorteo', 'Sorteo');

            $message = 'El modulo "Sorteo" se ha creado';
            array_push($messages, $message);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    private function _createResourcesRaffle()
    {
        $controllerName = 'Sorteo';
        $moduleName = 'admin';

        // Resource to controller
        $messages = array();
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
        $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
        $controllerResource->create();

        if ($testingEnabled) {
            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
            $testActionResource->getParentResource()->create();
            $testActionResource->create();
        }

        // Action index no contiene test ya que se ha creado en el controlador        
        $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', $controllerName, $moduleName);
        $indexActionResource->create();

        $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', $controllerName, $moduleName);
        $indexActionViewResource->create();

        // Action pagination
        $paginationActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'pagination', $controllerName, $moduleName);
        $paginationActionResource->create();
        if ($testingEnabled) {
            $testPaginationActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'pagination', $moduleName);
            $testPaginationActionMethodResource->create();
        }

        // Action ganador        
        $newActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'ganador', $controllerName, $moduleName);
        $newActionResource->create();
        if ($testingEnabled) {
            $testPaginationActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'ganador', $moduleName);
            $testPaginationActionMethodResource->create();
        }

        // Action guardarGanador
        $editActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'guardarGanador', $controllerName, $moduleName);
        $editActionResource->create();
        if ($testingEnabled) {
            $testEditActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'guardarGanador', $moduleName);
            $testEditActionMethodResource->create();
        }

        // Action guardarSuplente
        $deleteActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'guardarSuplente', $controllerName, $moduleName);
        $deleteActionResource->create();
        if ($testingEnabled) {
            $testDeleteActionMethodResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'guardarSuplente', $moduleName);
            $testDeleteActionMethodResource->create();
        }

        // Actualizamos .zfproject
        // Replace function $this->_storeProfile()
        $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
        $projectProfileFile->getContext()->save();
    }

    /**
     * INicia un entorno Facebook
     */
    public function initFacebook()
    {
        $fan = 1;
        $messages = array();
        try {
            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            if ($this->_util->existsModule('facebook')) {
                throw new Exception('El modulo "facebook" ya existe');
            }

            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

            $model = new Zrad_Db_Model();
            $db = $model->getDb()->getAdapter();
            $db->getConnection();

            // Creamos la tabla participante
            $sql = 'CREATE TABLE IF NOT EXISTS `participante` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `fb_uid` BIGINT(20) NOT NULL ,
            `fb_username` VARCHAR(100) NOT NULL ,
            `nombres` VARCHAR(100) NULL ,
            `apellidos` VARCHAR(100) NULL ,
            `dni` VARCHAR(8) NULL ,
            `email` VARCHAR(100) NULL ,
            `telefono` VARCHAR(15) NULL ,
            `celular` VARCHAR(15) NULL ,
            `ciudad` VARCHAR(100) NULL ,
            `direccion` VARCHAR(150) NULL ,            
            `fecha_nacimiento` DATE NULL ,
            `edad` TINYINT(2) DEFAULT NULL,
            `paso` TINYINT(2) DEFAULT NULL,
            `es_activo` TINYINT(1) NULL DEFAULT 1 ,
            `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `ip` VARCHAR(15) NULL ,
            PRIMARY KEY (`id`) ,
            INDEX `FBUID` (`fb_uid` ASC) )
            ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
            $db->query($sql);

            // Ubigeo
            $this->initUbigeo();

            // Creamos el modulo participante
            $this->createModule('participantes');

            // Creamos el modelo
            $this->createModel('participante', 'participantes');

            // Creamos el modulo
            $this->createModule('facebook');

            // Creamos el modulo concurso
            $this->createModule('concurso');

            // Creamos el controlador Redirect
            $moduleName = 'facebook';
            $controllerName = 'Redirect';
            require_once 'Zend/Tool/Project/Provider/Test.php';
            $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);
            if ($testingEnabled && !Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
                $testingEnabled = false;
                $message = 'Nota: PHPUnit es requerido para generar tests a los controladores';
                array_push($messages, $message);
            }

            $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $moduleName);
            $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', $controllerName, $moduleName);
            $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', $controllerName, $moduleName);
            $preloadingActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'preloading', 'Index', $moduleName);
            $preloadingActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'preloading', 'Index', $moduleName);

            // Fin
            $finActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'fin', 'Index', 'default');
            $finActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'fin', 'Index', 'default');

            $launchControllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, 'Launch', 'concurso');
            $indexLaunchActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', 'Launch', 'concurso');
            $indexLaunchViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Launch', 'concurso');

            // Registro Concurso
            $controllerRegistroResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, 'Registro', 'concurso');
            $indexRegistroActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', 'Registro', 'concurso');
            $indexRegistroActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Registro', 'concurso');
            $procesarRegistroActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'procesar', 'Registro', 'concurso');
            $finRegistroActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'fin', 'Registro', 'concurso');
            $finRegistroActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'fin', 'Registro', 'concurso');

            if ($testingEnabled) {
                $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $moduleName);
                $testPreloadingActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Index', 'preloading', $moduleName);
                $testFinActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Index', 'fin', 'default');
                $testIndexLaunchActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Launch', 'index', 'concurso');

                $testIndexRegistroActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Registro', 'index', 'concurso');
                $testProcesarRegistroActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Registro', 'procesar', 'concurso');
                $testFinRegistroActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Registro', 'fin', 'concurso');
            }

            $controllerResource->create();

            $indexActionResource->create();
            $indexActionViewResource->create();
            if ($testingEnabled) {
                $testActionResource->getParentResource()->create();
                $testActionResource->create();
            }

            $preloadingActionResource->create();
            $preloadingActionViewResource->create();
            if ($testingEnabled) {
                $testPreloadingActionResource->getParentResource()->create();
                $testPreloadingActionResource->create();
            }

            $finActionResource->create();
            $finActionViewResource->create();
            if ($testingEnabled) {
                $testFinActionResource->getParentResource()->create();
                $testFinActionResource->create();
            }

            $launchControllerResource->create();

            $indexLaunchActionResource->create();
            $indexLaunchViewResource->create();
            if ($testingEnabled) {
                $testIndexLaunchActionResource->getParentResource()->create();
                $testIndexLaunchActionResource->create();
            }

            // Para el controlador Registro
            $controllerRegistroResource->create();
            $indexRegistroActionResource->create();
            $indexRegistroActionViewResource->create();
            $procesarRegistroActionResource->create();
            $finRegistroActionResource->create();
            $finRegistroActionViewResource->create();
            if ($testingEnabled) {
                $testIndexRegistroActionResource->getParentResource()->create();
                $testIndexRegistroActionResource->create();

                $testProcesarRegistroActionResource->getParentResource()->create();
                $testProcesarRegistroActionResource->create();

                $testFinRegistroActionResource->getParentResource()->create();
                $testFinRegistroActionResource->create();
            }

            $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();

            // Reemplazamos Redirect
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'FbRedirectController.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'facebook'
                . DIRECTORY_SEPARATOR . 'controllers'
                . DIRECTORY_SEPARATOR . 'RedirectController.php';

            $this->_util->fullCopy($source, $destination);

            // Reemplazamos Index
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'FbIndexController.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'facebook'
                . DIRECTORY_SEPARATOR . 'controllers'
                . DIRECTORY_SEPARATOR . 'IndexController.php';

            $this->_util->fullCopy($source, $destination);

            // Reemplazamos vista preloading                        
            $sourceView = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Views'
                . DIRECTORY_SEPARATOR . 'facebook'
                . DIRECTORY_SEPARATOR . 'preloading.phtml';

            $destinationView = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'facebook'
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'scripts'
                . DIRECTORY_SEPARATOR . 'index'
                . DIRECTORY_SEPARATOR . 'preloading.phtml';

            $this->_util->fullCopy($sourceView, $destinationView);

            // Reemplazamos vista index
            $sourceView = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Views'
                . DIRECTORY_SEPARATOR . 'facebook'
                . DIRECTORY_SEPARATOR . 'redirect.phtml';

            $destinationView = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'facebook'
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'scripts'
                . DIRECTORY_SEPARATOR . 'redirect'
                . DIRECTORY_SEPARATOR . 'index.phtml';
            $this->_util->fullCopy($sourceView, $destinationView);

            // Layout Facebook
            $sourceLayout = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Layouts'
                . DIRECTORY_SEPARATOR . 'Fb';
            $destinationLayout = 'application'
                . DIRECTORY_SEPARATOR . 'layouts'
                . DIRECTORY_SEPARATOR . 'scripts';
            $this->_util->fullCopy($sourceLayout, $destinationLayout);

            // Reemplazamos el controlador Launch
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'FbLaunchController.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'controllers'
                . DIRECTORY_SEPARATOR . 'LaunchController.php';
            $this->_util->fullCopy($source, $destination);

            // Ruta del modelo
            $pathMapperParticipante = getcwd()
                . DIRECTORY_SEPARATOR . 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'participantes'
                . DIRECTORY_SEPARATOR . 'models';
            // Traemos el dominio
            require_once $pathMapperParticipante . DIRECTORY_SEPARATOR . 'Participante.php';

            // Agregamos los metodos "exists", "findByIdFacebook" y "actualizarPaso"
            $path = $pathMapperParticipante . DIRECTORY_SEPARATOR . 'ParticipanteMapper.php';
            $generator = Zend_CodeGenerator_Php_File::fromReflectedFileName($path);
            $class = $generator->getClass();

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'exists',
                    'parameters' => array(
                        array(
                            'name' => 'participante',
                            'type' => 'Participantes_Model_Participante'
                        ),
                        array(
                            'name' => 'action',
                            'defaultValue' => 'new',
                        )
                    ),
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Verifica si el participante existe',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'participante',
                                'datatype' => 'Participantes_Model_Participante'
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'action',
                                'datatype' => 'string'
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                'datatype' => 'bool'
                            )),
                        )
                    )),
                    'visibility' => 'public',
                    'body' => '$select = $this->getDbTable()->select()' . "\n"
                    . '    ->from(array(\'p\' => \'participante\'), array(\'COUNT(id) AS amount\'))' . "\n"
                    . '    ->where(\'email = ?\', $participante->getEmail())' . "\n"
                    . '    ->orWhere(\'dni = ?\', $participante->getDni())' . "\n"
                    . '    ->orWhere(\'fb_uid = ?\', $participante->getFbUid());' . "\n" . "\n"
                    . 'if ($action == \'edit\') {;' . "\n"
                    . '    $select->where(\'id <> ?\', $participante->getId());' . "\n"
                    . '}' . "\n" . "\n"
                    . '$amount = $this->getDbTable()->fetchRow($select)->amount;' . "\n"
                    . '$result = ($amount > 0) ? true : false;' . "\n"
                    . 'return $result;'
                ));
            $class->setMethod($method);

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'findByIdFacebook',
                    'parameters' => array(
                        array('name' => 'fbUid')
                    ),
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Verfica si existe el ID de facebook',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'bigint',
                                'datatype' => 'fbUid'
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                'datatype' => 'Zend_Db_Table_Row_Abstract|null'
                            )),
                        )
                    )),
                    'visibility' => 'public',
                    'body' => '$select = $this->getDbTable()->select()' . "\n"
                    . '    ->from(array(\'p\' => \'participante\'), array(\'id\', \'nombres\', \'apellidos\',\'email\', \'paso\'))' . "\n"
                    . '    ->where(\'fb_uid = ?\', $fbUid);' . "\n"
                    . 'return $this->getDbTable()->fetchRow($select);'
                ));
            $class->setMethod($method);

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'actualizarPaso',
                    'parameters' => array(
                        array('name' => 'id'),
                        array('name' => 'paso')
                    ),
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Actualizamos',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'id',
                                'datatype' => 'int'
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'paso',
                                'datatype' => 'int'
                            )),
                        )
                    )),
                    'visibility' => 'public',
                    'body' => '$data = array(\'paso\' => $paso);' . "\n"
                    . '$where = $this->getDbTable()->getAdapter()->quoteInto(\'id = ?\', $id);' . "\n"
                    . '$this->getDbTable()->update($data, $where);'
                ));
            $class->setMethod($method);

            $file = new Zend_CodeGenerator_Php_File();
            $file->setClass($class);
            file_put_contents($pathMapperParticipante . DIRECTORY_SEPARATOR . 'ParticipanteMapper.php', $file->generate());

            // Creamos el modelo sistema
            if (!Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, 'Sistema', 'default')) {
                Zend_Tool_Project_Provider_Model::createResource($this->_loadedProfile, 'Sistema', 'default');
                //replace function $this->_storeProfile()
                $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                $projectProfileFile->getContext()->save();
            }

            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Models'
                . DIRECTORY_SEPARATOR . 'Sistema.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'models'
                . DIRECTORY_SEPARATOR . 'Sistema.php';
            $this->_util->fullCopy($source, $destination);

            // Reemplazamos el IndexController
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'FbIndexDefaultController.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'controllers'
                . DIRECTORY_SEPARATOR . 'IndexController.php';
            $this->_util->fullCopy($source, $destination);

            // Reemplazamos el IndexController de Concurso
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'FbIndexConcursoController.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'controllers'
                . DIRECTORY_SEPARATOR . 'IndexController.php';
            $this->_util->fullCopy($source, $destination);

            // Reemplazamos el RegistroController de Concurso
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'FbRegistroConcursoController.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'controllers'
                . DIRECTORY_SEPARATOR . 'RegistroController.php';
            $this->_util->fullCopy($source, $destination);

            // Cambiamos el index del default
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Views'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'me-gusta.phtml';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'scripts'
                . DIRECTORY_SEPARATOR . 'index'
                . DIRECTORY_SEPARATOR . 'index.phtml';
            $this->_util->fullCopy($source, $destination);

            // Cambiamos el fin del concurso
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Views'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'fin-campana.phtml';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'scripts'
                . DIRECTORY_SEPARATOR . 'index'
                . DIRECTORY_SEPARATOR . 'fin.phtml';
            $this->_util->fullCopy($source, $destination);

            // Cambiamos el index del concurso
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Views'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'bienvenido.phtml';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'scripts'
                . DIRECTORY_SEPARATOR . 'index'
                . DIRECTORY_SEPARATOR . 'index.phtml';
            $this->_util->fullCopy($source, $destination);

            // Cambiamos el fin de Registro
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Views'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'fin.phtml';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'concurso'
                . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'scripts'
                . DIRECTORY_SEPARATOR . 'registro'
                . DIRECTORY_SEPARATOR . 'fin.phtml';
            $this->_util->fullCopy($source, $destination);

            $projectDirectory = getcwd();
            $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION, $projectDirectory);
            $applicationConfigResource = $profile->search('ApplicationConfigFile');

            // Facebook
            $applicationConfigResource->addStringItem('; Facebook API', 'true', 'production', false);
            $applicationConfigResource->addStringItem('facebook.appId', 'xxxxxxxxxx');
            $applicationConfigResource->addStringItem('facebook.secret', 'xxxxxxxxxx');
            $applicationConfigResource->addStringItem('facebook.scope', 'email,user_photos,publish_stream,user_birthday');
            $applicationConfigResource->addStringItem('facebook.fanPage', 'www.facebook.com/xxxxx');
            $applicationConfigResource->addStringItem('facebook.appPage', 'https://apps.facebook.com/xxxxx');
            $applicationConfigResource->addStringItem('; Facebook Id de fbdeveloper', 'fbdeveloper@emediala.com', 'production', false);
            $applicationConfigResource->addStringItem('facebook.og.admins', '100003346763245');
            $applicationConfigResource->create();

            // Agregando al BootStrap
            $path = getcwd() . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
            $generator = Zend_CodeGenerator_Php_File::fromReflectedFileName($path);
            $class = $generator->getClass();
            // Init Facebook
            $methodD = new Zend_CodeGenerator_Php_Method(array(
                    'name' => '_initFacebook',
                    'visibility' => 'protected',
                    'body' => '// Iniciamos la vista' . "\n"
                    . '$this->bootstrap(\'view\');' . "\n"
                    . '$view = $this->getResource(\'view\');' . "\n" . "\n"
                    . '$configs = new Zend_Config_Ini(APPLICATION_PATH . \'/configs/application.ini\', APPLICATION_ENV);' . "\n" . "\n"
                    . '$protocol = ZradAid_Helper::getProtocol();' . "\n"
                    . '$view->fbAppId = $configs->facebook->appId;' . "\n"
                    . '$view->fbSecret = $configs->facebook->secret;' . "\n"
                    . '$view->fbScope = $configs->facebook->scope;' . "\n"
                    . '$view->fbFanPage = (!empty($configs->facebook->fanPage)) ? $protocol . $configs->facebook->fanPage . \'?sk=app_\' . $view->fbAppId : \'\';' . "\n"
                    . '$view->fbEnvironment = APPLICATION_ENV;' . "\n"
                    . '$view->fbCanvasHeight = 830;' . "\n" . "\n"
                    . '// Zend / Facebook API Bug' . "\n"
                    . 'Zend_Session::start(true);' . "\n"
                ));
            $class->setMethod($methodD);

            $file = new Zend_CodeGenerator_Php_File();
            $file->setClass($class);
            file_put_contents(getcwd() . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php', $file->generate());

            // Creamos el formulario
            $this->_processFormFrontend('create', 'participante', 'Participante', 'concurso', false, 'Registro', 'index', true);

            $message = 'Proyecto "Facebook" se ha iniciado correctamente';
            array_push($messages, $message);

            // Creamos el fanPage
            if ($fan == 1) {
                //array_push($messages, 'Se ha creado en Fan Page');
            }

            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    /**
     * Crea la tabla ubigeo el contenido y el modelo
     */
    public function initUbigeo()
    {
        try {
            $messages = array();
            $message = 'Ubigeo creado en el modulo "default"';
            // Verificamos si la tabla no existe
            $model = new Zrad_Db_Model();
            if (!$model->existsTable('ubigeo')) {
                // Creamos el ubigeo
                $pathUbigeo = $this->_util->getPathTemplate()
                    . DIRECTORY_SEPARATOR . 'Db'
                    . DIRECTORY_SEPARATOR . 'ubigeo.sql';
                $sqlImport = new Zrad_Db_SqlImport($pathUbigeo);
                $sqlImport->import();
            } else {
                array_push($messages, 'Nota: La tabla "ubigeo" ya existe');
            }

            // Creando los modelos de ubigeo
            $this->createModel('ubigeo', 'default');
            // Reemplazamos el Mapper
            $source = $this->_util->getPathTemplate()
                . DIRECTORY_SEPARATOR . 'Models'
                . DIRECTORY_SEPARATOR . 'UbigeoMapper.php';

            $destination = 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'models'
                . DIRECTORY_SEPARATOR . 'UbigeoMapper.php';
            $this->_util->fullCopy($source, $destination);


            array_push($messages, $message);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    private function _getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }
        return (string) $indent;
    }

    public function createModule($name)
    {
        try {
            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            //$messages = array();
            $name = strtolower(trim($name));
            if ($this->_util->existsModule($name)) {
                throw new Exception('Modulo "' . $name . '" ya existe');
            }
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            $moduleResources = Zend_Tool_Project_Provider_Module::createResources($this->_loadedProfile, $name);
            $enabledFilter = new Zend_Tool_Project_Profile_Iterator_EnabledResourceFilter($moduleResources);
            foreach (new RecursiveIteratorIterator($enabledFilter, RecursiveIteratorIterator::SELF_FIRST) as $resource) {
                $resource->create();
            }
            //actualizamos el bootstrap by reflection
            $path = 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
            $generator = Zend_CodeGenerator_Php_File::fromReflectedFileName($path);
            $class = $generator->getClass();
            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => '_initAutoloader' . $this->_util->format($name, 6),
                    'visibility' => 'protected',
                    'body' => '$autoloader = new Zend_Application_Module_Autoloader(array(' . "\n"
                    . $this->_getWhitespace(4) . '\'namespace\' => \'' . $this->_util->format($name, 6) . '_\',' . "\n"
                    . $this->_getWhitespace(4) . '\'basePath\' => dirname(__FILE__) . \'/modules/' . $name . '\'));' . "\n"
                    . 'return $autoloader;' . "\n"
                ));
            $class->setMethod($method);

            $file = new Zend_CodeGenerator_Php_File();
            $file->setClass($class);
            file_put_contents('application' . DIRECTORY_SEPARATOR . 'Bootstrap.php', $file->generate());

            //creamos el controlador
            require_once 'Zend/Tool/Project/Provider/Test.php';
            $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);
            if ($testingEnabled && !Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
                $testingEnabled = false;
                //$message = 'Nota: PHPUnit es requerido para generar tests a los controladores';
                //array_push($messages, $message);
            }

            $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, 'Index', $name);
            $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', 'Index', $name);
            $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', 'Index', $name);
            if ($testingEnabled) {
                $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, 'Index', 'index', $name);
            }
            $controllerResource->create();

            $indexActionResource->create();
            $indexActionViewResource->create();
            if ($testingEnabled) {
                $testActionResource->getParentResource()->create();
                $testActionResource->create();
            }

            //habilitamos el form
            $profileSearchParams = array();
            if ($name != null && is_string($name)) {
                $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $name));
            }
            $profileSearchParams[] = 'formsDirectory';
            $formDirectoryResource = $this->_loadedProfile->search($profileSearchParams);
            $formDirectoryResource->setEnabled(true);
            $formDirectoryResource->create();

            //actualizamos .zfproject
            //replace function $this->_storeProfile()
            $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
            $projectProfileFile->getContext()->save();

            //$messages = array('Modulo "' . $name . '" ha sido creado en: ' . $moduleResources->getContext()->getPath());
            $messages = array('Modulo "' . $name . '" ha sido creado');

            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    public function createCrudBackend($tableName, $module = null, $inCaptcha = 'false', $generateForm = 1)
    {
        try {

            // Verificamos version comercial o freeware
            if (self::FREEWARE) {
                throw new Exception('Visite www.zend-rad.com y consulte sobre la version completa');
            } else {
                $oinCaptcha = $inCaptcha;
                $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;
                $generateForm = (((int) $generateForm) == 1) ? true : false;

                // Verificamos si estamos dentro del proyecto
                if (!$this->_findProfileDirectory()) {
                    throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
                }

                // Verificamos si tenemos conexion
                $model = new Zrad_Db_Model();
                $model->getDb()->getAdapter()->getConnection();

                // Profile
                $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

                // Salida
                $messages = array('MAPEANDO la entidad "' . $tableName . '"...');
                $this->_util->output($messages);

                // Verificamos si ha inicializado en backend
                if (!$this->_util->existsModule('admin')) {
                    throw new Exception('El Backend no ha sido inicializado.');
                }

                // Verificamos si ya existe el formulario
                $formName = $this->_util->format($tableName, 8);
                if ($this->_util->existsForm($formName, 'admin')) {
                    throw new Exception('El CRUD ya ha sido creado, use el comando "update-crud-backend".');
                }

                $messages = array();
                // Model
                $model = new Zrad_Db_Model();
                if (!$model->existsTable($tableName)) {
                    throw new Exception('La tabla "' . $tableName . '" no existe en la Base de Datos "' . $model->getDb()->getDbName() . '"');
                }

                if ($this->_util->isModular() && $module === null) {
                    $module = $this->_util->generateNameModule($tableName);
                }

                // Salvamos el nombre original de la tabla
                $originalTableName = $tableName;

                // Formateamos el nombre de la tabla
                $tableName = $this->_util->formatTableName($tableName);

                // Formateamos el nombre del controlador
                $controllerName = $this->_util->format($tableName, 8);

                // Verificamos si existe el modulo
                if (!$this->_util->existsModule($module)) {
                    $this->createModule($module);
                }

                // Verificamos si existe el modelo
                $modelName = $this->_util->format($tableName, 8);
                $modelName = ucfirst($modelName);
                $mapperName = $modelName . 'Mapper';
                if (!Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $modelName, $module) &&
                    !Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $mapperName, $module) &&
                    !Zend_Tool_Project_Provider_DbTable::hasResource($this->_loadedProfile, $modelName, $module)) {
                    $this->createModel($tableName);
                }

                // Creamos el menu
                $menu = new Zrad_Menu();
                $menu->create($module, $controllerName);

                // Creamos el formulario
                if ($generateForm) {
                    $this->createFormBackend($tableName, null, $oinCaptcha);
                }

                // Mapeamos la tabla
                $mapper = new Zrad_Mapper($originalTableName);
                $mapper->run();

                $config = array(
                    'originalTableName' => $originalTableName,
                    'tableName' => $tableName,
                    'moduleName' => $module,
                    'controllerName' => $controllerName,
                    'registry' => $this->_registry,
                    'mapper' => $mapper->getResult(),
                    'target' => 'backend'
                );

                $config['moduleName'] = 'admin';

                // Creamos recursos para el controlador
                $this->_createResourcesCrudBackend($controllerName, 'admin');
                // Creamos el controlador
                $controller = new Zrad_Crud_Controller($config);
                $controller->createBackend();
                // Creamos los componentes javascript
                $js = new Zrad_Js_Build($config);
                $view = new Zrad_View_Build($config);
                $jscript = $js->createList();
                // Vista list
                $jscript = $js->createList();
                $view->setJscript($jscript);
                $view->createList();
                // Vista new
                //$jscript = $js->createForm('new');
                //$view->setJscript($jscript);
                //$view->createForm('new');
                // Vista edit
                $jscript = $js->createForm('edit');
                $view->setJscript($jscript);
                $view->createForm('edit');
                // Salida
                $messages = array('CRUD BACKEND para la entidad "' . $originalTableName . '" ha sido creada.');
                $this->_util->output($messages);

                // Verificamos si esta habilitado crear tablas relacionadas
                if ($this->_util->createRelationsTables()) {
                    // Buscamos claves foraneas en la tabla                
                    $relationships = $mapper->getResult('relationships');
                    if (!empty($relationships['reference'])) {
                        foreach ($relationships['reference'] as $tableName) {
                            // Verificamos si la tabla esta activa para ser mapeada
                            if ($this->_util->isEnabled($tableName)) {
                                $messages = array(
                                    'Se encontro un Tabla RELACIONADA',
                                    '------------------------------------------'
                                );
                                $this->_util->output($messages);
                                $this->createCrudBackend($tableName);
                            }
                        }
                    }
                }
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    public function updateCrudBackend($tableName, $module = null, $inCaptcha = 'false', $generateForm = 1)
    {
        try {

            // Verificamos version comercial o freeware
            if (self::FREEWARE) {
                throw new Exception('Visite www.zend-rad.com y consulte sobre la version completa');
            } else {
                $oinCaptcha = $inCaptcha;
                $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;
                $generateForm = (((int) $generateForm) == 1) ? true : false;
                // Verificamos si estamos dentro del proyecto
                if (!$this->_findProfileDirectory()) {
                    throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
                }

                // Verificamos si tenemos conexion
                $model = new Zrad_Db_Model();
                $model->getDb()->getAdapter()->getConnection();

                // Profile
                $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

                // Salida
                $messages = array('MAPEANDO la entidad "' . $tableName . '"...');
                $this->_util->output($messages);

                // Verificamos si ha inicializado en backend
                if (!$this->_util->existsModule('admin')) {
                    throw new Exception('El Backend no ha sido inicializado.');
                }

                // Verificamos si ya existe el formulario
                $formName = $this->_util->format($tableName, 8);
                if (!$this->_util->existsForm($formName, 'admin')) {
                    throw new Exception('El CRUD no  ha sido creado, use el comando "create-crud-backend".');
                }

                $messages = array();

                if (!$model->existsTable($tableName)) {
                    throw new Exception('La tabla "' . $tableName . '" no existe en la Base de Datos "' . $model->getDb()->getDbName() . '"');
                }

                if ($this->_util->isModular() && $module === null) {
                    $module = $this->_util->generateNameModule($tableName);
                }

                // Salvamos el nombre original de la tabla
                $originalTableName = $tableName;

                // Formateamos el nombre de la tabla
                $tableName = $this->_util->formatTableName($tableName);

                // Formateamos el nombre del controlador
                $controllerName = $this->_util->format($tableName, 8);

                // Verificamos si existe el modulo
                if ($this->_util->isModular() && !$this->_util->existsModule($module)) {
                    throw new Exception('El modulo "' . $module . '" no existe en el proyecto');
                }

                // Verificamos si existe el modelo
                $modelName = $this->_util->format($tableName, 8);
                $modelName = ucfirst($modelName);
                $mapperName = $modelName . 'Mapper';
                if (!Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $modelName, $module) &&
                    !Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $mapperName, $module) &&
                    !Zend_Tool_Project_Provider_DbTable::hasResource($this->_loadedProfile, $modelName, $module)) {
                    $this->createModel($tableName);
                }

                // Creamos el formulario
                if ($generateForm) {
                    $this->createFormBackend($tableName, null, $oinCaptcha);
                }

                // Mapeamos la tabla
                $mapper = new Zrad_Mapper($originalTableName);
                $mapper->run();

                $config = array(
                    'originalTableName' => $originalTableName,
                    'tableName' => $tableName,
                    'moduleName' => $module,
                    'controllerName' => $controllerName,
                    'registry' => $this->_registry,
                    'mapper' => $mapper->getResult(),
                    'target' => 'backend'
                );

                $config['moduleName'] = 'admin';

                // Creamos el controlador
                $controller = new Zrad_Crud_Controller($config);
                $controller->createBackend();
                // Creamos los componentes javascript
                $js = new Zrad_Js_Build($config);
                $view = new Zrad_View_Build($config);
                $jscript = $js->createList();
                // Vista list
                $jscript = $js->createList();
                $view->setJscript($jscript);
                $view->createList();
                // Vista new
                //$jscript = $js->createForm('new');
                //$view->setJscript($jscript);
                //$view->createForm('new');
                // Vista edit
                $jscript = $js->createForm('edit');
                $view->setJscript($jscript);
                $view->createForm('edit');
                // Salida
                $messages = array('CRUD BACKEND para la entidad "' . $originalTableName . '" ha sido actualizado.');
                $this->_util->output($messages);
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

    public function createCrudFrontend($tableName, $module = null, $inCaptcha = 1, $generateForm = 1)
    {
        try {
            
            // Verificamos si estamos dentro del proyecto
            if (!$this->_findProfileDirectory()) {
                throw new Exception('Debe ingresar a un proyecto para ejecutar este comando');
            }

            // Verificamos si tenemos conexion
            $model = new Zrad_Db_Model();
            $model->getDb()->getAdapter()->getConnection();
            if (!$model->existsTable($tableName)) {
                throw new Exception('La tabla "' . $tableName . '" no existe en la Base de Datos "' . $model->getDb()->getDbName() . '"');
            }
            
            // Ingresar "false" para poder colocar a 0
            $inCaptcha = (((int) $inCaptcha) == 1) ? true : false;
            $generateForm = (((int) $generateForm) == 1) ? true : false;

            // Salida            
            $this->_util->output(array('MAPEANDO la entidad "' . $tableName . '"...'));

            // Perfil
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

            if ($this->_util->isModular() && $module === null) {
                $module = $this->_util->generateNameModule($tableName);
            }

            // Salvamos el nombre original de la tabla
            $originalTableName = $tableName;
 
            //formateamos el nombre de la tabla
            $tableName = $this->_util->formatTableName($tableName);

            // Verificamos si existe el modulo
            if ($this->_util->isModular() && !$this->_util->existsModule($module)) {
                $this->createModule($module);
            }

            // Verificamos si existe el modelo
            $modelName = ucfirst($this->_util->format($tableName, 8));
            $mapperName = $modelName . 'Mapper';
            if (!Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $modelName, $module) &&
                !Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $mapperName, $module) &&
                !Zend_Tool_Project_Provider_DbTable::hasResource($this->_loadedProfile, $modelName, $module)) {
                $this->createModel($tableName, $module);
            }

            if ($generateForm) {
                $createNameModule = $this->_util->generateNameModule($tableName);
                if ($module != $createNameModule) {
                    // creamos el controlador asociado a la tabla
                    // p.e si tabla participante, entonces Modulo_ParticipanteController
                    $controllerName = $this->_util->format($tableName, 8);
                    if (!Zend_Tool_Project_Provider_Controller::hasResource($this->_loadedProfile, $controllerName, $module)) {
                        //creamos el controlador
                        require_once 'Zend/Tool/Project/Provider/Test.php';
                        $testingEnabled = Zend_Tool_Project_Provider_Test::isTestingEnabled($this->_loadedProfile);
                        if ($testingEnabled && !Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
                            $testingEnabled = false;
                            $messages = array('Nota: PHPUnit es requerido para generar tests a los controladores');
                            $this->_util->output($messages);
                        }

                        $controllerResource = Zend_Tool_Project_Provider_Controller::createResource($this->_loadedProfile, $controllerName, $module);
                        $indexActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'index', $controllerName, $module);
                        $indexActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'index', $controllerName, $module);
                        if ($testingEnabled) {
                            $testActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'index', $module);
                        }
                        $controllerResource->create();
                        $indexActionResource->create();
                        $indexActionViewResource->create();
                        if ($testingEnabled) {
                            $testActionResource->getParentResource()->create();
                            $testActionResource->create();
                        }
                    }
                    // creamos la accion registro dentro del controlador creado
                    if (!Zend_Tool_Project_Provider_Action::hasResource($this->_loadedProfile, 'nuevo', $controllerName, $module)) {
                        $nuevoActionResource = Zend_Tool_Project_Provider_Action::createResource($this->_loadedProfile, 'nuevo', $controllerName, $module);
                        $nuevoActionViewResource = Zend_Tool_Project_Provider_View::createResource($this->_loadedProfile, 'nuevo', $controllerName, $module);
                        if ($testingEnabled) {
                            $testNuevoActionResource = Zend_Tool_Project_Provider_Test::createApplicationResource($this->_loadedProfile, $controllerName, 'nuevo', $module);
                        }
                        $nuevoActionResource->create();
                        $nuevoActionViewResource->create();
                        if ($testingEnabled) {
                            $testNuevoActionResource->getParentResource()->create();
                            $testNuevoActionResource->create();
                        }
                    }
                    //replace function $this->_storeProfile()
                    $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                    $projectProfileFile->getContext()->save();
                    $formName = $this->_util->format($tableName, 8);
                    $this->createFormFrontend($tableName, $formName, $module, $controllerName, 'nuevo', $inCaptcha);
                } else {
                    $formName = 'Nuevo';
                    $this->createFormFrontend($tableName, $formName, $module, null, 'index', $inCaptcha);
                }
            }

            // Salida
            $messages = array('CRUD FRONTEND para la entidad "' . $originalTableName . '" ha sido creada.');
            $this->_util->output($messages);

            // Verificamos si esta habilitado crear tablas relacionadas
            if ($this->_util->createRelationsTables()) {
                // Buscamos claves foraneas en la tabla
                $mapper = new Zrad_Mapper($originalTableName);
                $mapper->run();
                $relationships = $mapper->getResult('relationships');
                if (!empty($relationships['reference'])) {
                    foreach ($relationships['reference'] as $tableName) {
                        // Verificamos si la tabla esta activa para ser mapeada
                        if ($this->_util->isEnabled($tableName)) {
                            $messages = array(
                                'Se encontro un Tabla RELACIONADA',
                                '------------------------------------------'
                            );
                            $this->_util->output($messages);
                            // Creamos la tabla relacionada
                            $this->createCrudFrontend($tableName, null, 1, 1);
                        }
                    }
                }
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            $messages = array(self::DB_ERROR);
            $this->_util->output($messages);
        } catch (Exception $e) {
            $this->_messages = array($e->getMessage());
            $this->_util->output($this->_messages);
        }
    }

    public function test($tableName)
    {
        try {

            // Mapeamos la tabla
            $mapper = new Zrad_Mapper($tableName);
            $mapper->run();
            //$result = $mapper->getResult();


            $config = array(
                'originalTableName' => $originalTableName,
                'tableName' => $tableName,
                'moduleName' => $moduleName,
                'mapper' => $mapper->getResult()
            );

            //$config = $this->_initModel('create', $tableName, $moduleName);
            $model = new Zrad_Model_Mapper($config);
            $model->create();
            /* $controller = new Zrad_Crud_Controller($config);
              $controller->createBackend();

              //print_r($result['fields']);

              $config = array(
              'tableName' => $tableName,
              'moduleName' => 'default',
              'controllerName' => 'Index',
              'actionName' => 'index',
              'mapper' => $mapper->getResult(),
              'registry' => $this->_registry
              );

              $controller = new Zrad_Crud_Controller($config);
              $controller->createFrontend(); */

            /* $controller->createRaffle();
              $model = new Zrad_Model_Mapper($config);
              $model->createRaffle($config['tableName']); */

            // Creamos los Js
            //$js = new Zrad_Js_Build($config);
            //$view = new Zrad_View_Build($config);
            //$jscript = $js->createListRaffle($config['tableName']);
            //echo $jscript;
            // Vista list
            //$view->setJscript($jscript);
            //$view->createListRaffle();
        } catch (Exception $e) {
            $messages = array($e->getMessage());
            $this->_util->output($messages);
        }
    }

}