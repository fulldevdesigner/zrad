<?php

/**
 * @see Zrad_Helper_Util
 */
require_once 'Zrad/Helper/Util.php';

class Zrad_Project extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{

    /**
     * @var string
     */
    private $_name = '';

    /**
     * @var string
     */
    private $_originalName = '';

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;
    protected $_appConfigFilePath = null;
    protected $_config = null;
    protected $_sectionName = 'production';

    /**
     * Constructor
     */
    public function __construct($config)
    {
        //parent::__construct(); only zf 1.8
        $this->_util = new Zrad_Helper_Util();

        if (!isset($config['registry'])) {
            throw new Exception('No se ha encontrado instacia de Registry');
        }
        $registry = $config['registry'];
        if (!($registry instanceof Zend_Tool_Framework_Registry_Interface)) {
            throw new Exception('config[registry] debe ser instancia de Zend_Tool_Framework_Registry_Interface');
        }
        if (!isset($config['name'])) {
            throw new Exception('No se ha definido el nombre del proyecto');
        }
        $this->setRegistry($registry);
        $this->_name = $config['name'];
        $this->_originalName = $config['name'];

        if ($this->_name == null) {
            $this->_name = getcwd();
        } else {
            $this->_name = trim($this->_name);
            if (!file_exists($this->_name)) {
                $created = mkdir($this->_name);
                if (!$created) {
                    require_once 'Zend/Tool/Framework/Client/Exception.php';
                    throw new Zend_Tool_Framework_Client_Exception('Could not create requested project directory \'' . $this->_name . '\'');
                }
            }
            $this->_name = str_replace('\\', '/', realpath($this->_name));
        }

        $profile = $this->_loadProfile(self::NO_PROFILE_RETURN_FALSE, $this->_name);

        if ($profile !== false) {
            require_once 'Zend/Tool/Framework/Client/Exception.php';
            throw new Zend_Tool_Framework_Client_Exception('A project already exists here');
        }
    }

    private function _getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }
        return (string) $indent;
    }

    /**
     * @param string $type Tipo de proyecto
     */
    private function _postCreate($type)
    {
        // Update bootstrap by reflection
        $path = $this->_name . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
        $generator = Zend_CodeGenerator_Php_File::fromReflectedFileName($path);
        $class = $generator->getClass();

        // Views
        /* $methodA = new Zend_CodeGenerator_Php_Method(array(
          'name' => '_initViewHelpers',
          'visibility' => 'protected',
          'body' => '$this->bootstrap(\'layout\');' . "\n"
          . '$layout = $this->getResource(\'layout\');' . "\n"
          . '$view = $layout->getView();' . "\n"
          . '$view->doctype(\'XHTML1_STRICT\');' . "\n"
          . '$view->headMeta()->appendHttpEquiv(\'Content-type\', \'text/html; charset=UTF-8\');' . "\n"
          . '$view->headTitle(\'' . $this->_util->format($this->_originalName, 9) . '\');'
          ));
          $class->setMethod($methodA); */

        // Create GA
        $methodF = new Zend_CodeGenerator_Php_Method(array(
                'name' => '_initGA',
                'visibility' => 'protected',
                'body' => '// Iniciamos la vista' . "\n"
                . '$this->bootstrap(\'view\');' . "\n"
                . '$view = $this->getResource(\'view\');' . "\n" . "\n"
                . '$configs = new Zend_Config_Ini(APPLICATION_PATH . \'/configs/application.ini\', APPLICATION_ENV);' . "\n" . "\n"
                . '$view->gaWebId = $configs->ga->webId;' . "\n"
                . '$view->environment = APPLICATION_ENV;' . "\n"
            ));
        $class->setMethod($methodF);

        // Init Log
        $methodB = new Zend_CodeGenerator_Php_Method(array(
                'name' => '_initLog',
                'visibility' => 'protected',
                'body' => '// Log' . "\n"
                . 'if ($this->hasPluginResource(\'log\')) { ' . "\n"
                . '    $r = $this->getPluginResource(\'log\');' . "\n"
                . '    $log = $r->getLog();' . "\n"
                . '    Zend_Registry::set(\'log\', $log);' . "\n"
                . '}' . "\n"
            ));
        $class->setMethod($methodB);

        // Init forms
        $methodC = new Zend_CodeGenerator_Php_Method(array(
                'name' => '_initForms',
                'visibility' => 'protected',
                'body' => '// Images' . "\n"
                . '$images = new Zend_Config_Ini(APPLICATION_PATH . \'/configs/admin-form.ini\',\'images\');' . "\n"
                . 'Zend_Registry::set(\'configImages\', $images);' . "\n"
                . '// Files' . "\n"
                . '$files = new Zend_Config_Ini(APPLICATION_PATH . \'/configs/admin-form.ini\',\'files\');' . "\n"
                . 'Zend_Registry::set(\'configFiles\', $files);' . "\n"
            ));
        $class->setMethod($methodC);

        // Init Translate
        $methodD = new Zend_CodeGenerator_Php_Method(array(
                'name' => '_initTranslate',
                'visibility' => 'protected',
                'body' => '$translator = new Zend_Translate(' . "\n"
                . '    array(' . "\n"
                . '        \'adapter\' => \'array\',' . "\n"
                . '        \'content\' => APPLICATION_PATH . \'/../resources/languages\',' . "\n"
                . '        \'locale\' => \'es\',' . "\n"
                . '        \'scan\' => Zend_Translate::LOCALE_DIRECTORY' . "\n"
                . '    )' . "\n"
                . ');' . "\n"
                . 'Zend_Validate_Abstract::setDefaultTranslator($translator);' . "\n"
            ));
        $class->setMethod($methodD);

        // Create module default
        $methodE = new Zend_CodeGenerator_Php_Method(array(
                'name' => '_initAutoloaderDefault',
                'visibility' => 'protected',
                'body' => '$autoloader = new Zend_Application_Module_Autoloader(array(' . "\n"
                . $this->_getWhitespace(4) . '\'namespace\' => \'Default_\',' . "\n"
                . $this->_getWhitespace(4) . '\'basePath\' => dirname(__FILE__) . \'/modules/default\'));' . "\n"
                . 'return $autoloader;' . "\n"
            ));
        $class->setMethod($methodE);

        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);
        file_put_contents($this->_name . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php', $file->generate());

        $destination = $this->_name . DIRECTORY_SEPARATOR . 'application';
        $source = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Template';

        //traslate
        $sourceZrad = $source
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'default'
            . DIRECTORY_SEPARATOR . 'languages';
        $destinationZrad = $this->_name . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'languages';

        $this->_util->fullCopy($sourceZrad, $destinationZrad);
        //ini
        $sourceZrad = $source
            . DIRECTORY_SEPARATOR . 'Configs';
        $destinationZrad = $destination
            . DIRECTORY_SEPARATOR . 'configs';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);

        $sourceIndexController = $source
            . DIRECTORY_SEPARATOR . 'Controllers'
            . DIRECTORY_SEPARATOR . 'IndexControllerSimple.php';
        $destinationIndexController = $destination;
        $sourceErrorController = $source
            . DIRECTORY_SEPARATOR . 'Controllers'
            . DIRECTORY_SEPARATOR . 'ErrorControllerSimple.php';
        $destinationErrorController = $destination;

        $destinationViewDefault = $destination;
        if ($type == 'Modular') {
            $sourceIndexController = $source
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'IndexControllerModular.php';
            $sourceErrorController = $source
                . DIRECTORY_SEPARATOR . 'Controllers'
                . DIRECTORY_SEPARATOR . 'ErrorControllerModular.php';
            $destinationErrorController .= DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default';
            $destinationIndexController .= DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default';
            $destinationViewDefault .= DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . 'default';
        }

        $destinationIndexController .= DIRECTORY_SEPARATOR . 'controllers'
            . DIRECTORY_SEPARATOR . 'IndexController.php';
        $destinationErrorController .= DIRECTORY_SEPARATOR . 'controllers'
            . DIRECTORY_SEPARATOR . 'ErrorController.php';

        //index
        $this->_util->fullCopy($sourceIndexController, $destinationIndexController);
        //error
        $this->_util->fullCopy($sourceErrorController, $destinationErrorController);

        //update application.ini
        //layout
        $layoutDefault = '"web-default"';
        $layoutPath = 'APPLICATION_PATH "/layouts/scripts/"';
        $projectDirectory = $this->_name;
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION, $projectDirectory);
        $applicationConfigResource = $profile->search('ApplicationConfigFile');
        $applicationConfigResource->addStringItem('autoloadernamespaces.zradAid', '"ZradAid_"', 'production', false);
        $applicationConfigResource->addStringItem('resources.view.helperPath.ZradAid_View_Helper', '"ZradAid/View/Helper"', 'production', false);
        $applicationConfigResource->addStringItem('resources.layout.layout', $layoutDefault, 'production', false);
        $applicationConfigResource->addStringItem('resources.layout.layoutPath', $layoutPath, 'production', false);
        $applicationConfigResource->create();
        //module
        if ($type == 'Modular') {
            $applicationConfigResource = $profile->search('ApplicationConfigFile');
            $applicationConfigResource->removeStringItem('resources.frontController.controllerDirectory', 'production');
            $applicationConfigResource->addStringItem('resources.frontController.params.prefixDefaultModule', '1', 'production');
            $applicationConfigResource->addStringItem('resources.frontController.moduleDirectory', 'APPLICATION_PATH "/modules"', 'production', false);
            $applicationConfigResource->create();
        }

        $applicationConfigResource->addStringItem('; Lenguaje', 'true', 'production', false);
        $applicationConfigResource->create();
        $applicationConfigResource->addStringItem('resources.locale.default', '"es_PE"', 'production', false);
        $applicationConfigResource->addStringItem('resources.locale.force', 'true', 'production', false);
        $applicationConfigResource->create();

        // Testing
        $applicationConfigResource->addStringItem('resources.frontController.params.displayExceptions', '1', 'testing', false);
        $applicationConfigResource->addStringItem('resources.frontController.params.displayExceptions', '1', 'staging', false);
        $applicationConfigResource->addStringItem('phpSettings.display_startup_errors', '1', 'staging', false);
        $applicationConfigResource->addStringItem('phpSettings.display_errors', '1', 'staging', false);
        $applicationConfigResource->create();

        $environments = array('development', 'testing', 'staging');
        foreach ($environments as $environment) {
            // Db
            $applicationConfigResource->addStringItem('; Base de Datos', 'true', $environment, false);
            $applicationConfigResource->addStringItem('resources.db.params.dbname', '""', $environment, false);
            $applicationConfigResource->addStringItem('resources.db.params.username', '""', $environment, false);
            $applicationConfigResource->addStringItem('resources.db.params.password', '""', $environment, false);
            $applicationConfigResource->addStringItem('resources.db.params.host', '""', $environment, false);
            $applicationConfigResource->create();
        }

        // Sesiones
        $applicationConfigResource->addStringItem('; Sesiones', 'true', 'production', false);
        $applicationConfigResource->addStringItem('resources.session.save_path', 'APPLICATION_PATH "/../data/sessions"', 'production', false);
        $applicationConfigResource->addStringItem('resources.session.use_only_cookies', 'true', 'production', false);
        $applicationConfigResource->addStringItem('resources.session.remember_me_seconds', 240, 'production', false);
        $applicationConfigResource->create();

        // Logs
        $applicationConfigResource->addStringItem('; Log', 'true', 'production', false);
        $applicationConfigResource->addStringItem('resources.log.stream.writerName', 'Stream');
        $applicationConfigResource->addStringItem('resources.log.stream.writerParams.stream', 'APPLICATION_PATH "/../data/logs/application.log"', 'production', false);
        $applicationConfigResource->addStringItem('resources.log.stream.writerParams.mode', 'a');
        $applicationConfigResource->addStringItem('resources.log.stream.filterName', 'Priority');
        $applicationConfigResource->addStringItem('resources.log.stream.filterParams.priority', '4', 'production', false);
        $applicationConfigResource->create();

        // Firebug
        $applicationConfigResource->addStringItem('; Firebug', 'true', 'production', false);
        $applicationConfigResource->addStringItem('resources.log.firebug.writerName', 'Firebug');
        $applicationConfigResource->addStringItem('resources.log.firebug.filterName', 'Priority');
        $applicationConfigResource->addStringItem('resources.log.firebug.filterParams.priority', '7', 'production', false);
        $applicationConfigResource->create();

        // GA
        $applicationConfigResource->addStringItem('; Google Analytics', 'true', 'production', false);
        $applicationConfigResource->addStringItem('ga.webId', 'UA-XXXXXXXX-X');
        $applicationConfigResource->create();

        // Bd
        $applicationConfigResource->addStringItem('; Base de Datos', 'true', 'production', false);
        $applicationConfigResource->create();

        $sourceViewDefault = $source
            . DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'default'
            . DIRECTORY_SEPARATOR . 'default.phtml';

        $destinationViewDefault .= DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'index'
            . DIRECTORY_SEPARATOR . 'index.phtml';

        //view default
        $this->_util->fullCopy($sourceViewDefault, $destinationViewDefault);

        // Layout Basico
        $sourceZrad = $source
            . DIRECTORY_SEPARATOR . 'Layouts'
            . DIRECTORY_SEPARATOR . 'Basic';
        $destinationZrad = $destination
            . DIRECTORY_SEPARATOR . 'layouts'
            . DIRECTORY_SEPARATOR . 'scripts';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);

        //delete layout layout.phtml
        $destinationLayout = $destination
            . DIRECTORY_SEPARATOR . 'layouts'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'layout.phtml';
        unlink($destinationLayout);

        //update .htaccess
        $sourceHtaccess = $source
            . DIRECTORY_SEPARATOR . 'Profiles'
            . DIRECTORY_SEPARATOR . '.htaccess';
        $destinationHtaccess = $this->_name
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . '.htaccess';
        $this->_util->fullCopy($sourceHtaccess, $destinationHtaccess);
        // favicon
        $sourceZrad = $source
            . DIRECTORY_SEPARATOR . 'Public'
            . DIRECTORY_SEPARATOR . 'favicon.ico';
        $destinationZrad = $this->_name
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'favicon.ico';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);
        //update README.txt
        $sourceTxt = $source
            . DIRECTORY_SEPARATOR . 'Profiles'
            . DIRECTORY_SEPARATOR . 'README.txt';
        $destinationTxt = $this->_name
            . DIRECTORY_SEPARATOR . 'docs'
            . DIRECTORY_SEPARATOR . 'README.txt';
        //$this->_util->fullCopy($sourceTxt, $destinationTxt);
        //update PROJECT.txt
        $sourceTxt = $source
            . DIRECTORY_SEPARATOR . 'Profiles'
            . DIRECTORY_SEPARATOR . 'LICENCIA.txt';
        $destinationTxt = $this->_name
            . DIRECTORY_SEPARATOR . 'docs'
            . DIRECTORY_SEPARATOR . 'LICENCIA.txt';
        $this->_util->fullCopy($sourceTxt, $destinationTxt);

        $sourceZrad = $source . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'js';
        $destinationZrad = $this->_name . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);
        // styles
        $sourceZrad = $source . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'css';
        $destinationZrad = $this->_name . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);
        // images
        $sourceZrad = $source . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR .
            'img' . DIRECTORY_SEPARATOR . 'zrad';
        $destinationZrad = $this->_name . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'img' .
            DIRECTORY_SEPARATOR . 'zrad';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);
        // humans
        $sourceZrad = $source . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'humans.txt';
        $destinationZrad = $this->_name . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'humans.txt';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);
        // robots
        $sourceZrad = $source . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'robots.txt';
        $destinationZrad = $this->_name . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'robots.txt';
        $this->_util->fullCopy($sourceZrad, $destinationZrad);

        //solo para la presentacion
        $this->_aloneTest();

        //creamos el proyecto
        $this->_createNbProject();
    }

    private function _createNbProject()
    {
        $source = $this->_util->getPathTemplate();
        $sourceNb = $source
            . DIRECTORY_SEPARATOR . 'Project'
            . DIRECTORY_SEPARATOR . 'nbproject';
        $destinationNb = $this->_name
            . DIRECTORY_SEPARATOR . 'nbproject';
        $this->_util->fullCopy($sourceNb, $destinationNb);
        // Actualizamos el proyecto
        $projectPath = $this->_name;
        $path = $this->_name
            . DIRECTORY_SEPARATOR . 'nbproject'
            . DIRECTORY_SEPARATOR . 'project.xml';
        $pxml = file_get_contents($path);
        $pxml = str_replace('%NAMEPROJECT%', $this->_util->format($this->_originalName, 6), $pxml);
        file_put_contents($path, $pxml);
        $path = $this->_name
            . DIRECTORY_SEPARATOR . 'nbproject'
            . DIRECTORY_SEPARATOR . 'private'
            . DIRECTORY_SEPARATOR . 'private.properties';
        $prop = file_get_contents($path);
        $prop = str_replace('%NAME%', $this->_originalName, $prop);
        file_put_contents($path, $prop);

        $path = $this->_name
            . DIRECTORY_SEPARATOR . 'nbproject'
            . DIRECTORY_SEPARATOR . 'private'
            . DIRECTORY_SEPARATOR . 'private.xml';
        $prop = file_get_contents($path);
        $prop = str_replace('%PROJECTPATH%', $projectPath, $prop);
        file_put_contents($path, $prop);
    }

    private function _aloneTest()
    {
        $dsn = 'adapter=Pdo_Mysql&host=127.0.0.1&charset=utf8&username=&password=&dbname=';
        $sectionName = 'production';

        $projectDirectory = $this->_name;
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION, $projectDirectory);

        $appConfigFileResource = $profile->search('applicationConfigFile');

        if ($appConfigFileResource == false) {
            throw new Zend_Tool_Project_Exception('A project with an application config file is required to use this provider.');
        }

        $this->_appConfigFilePath = $appConfigFileResource->getPath();

        $this->_config = new Zend_Config_Ini($this->_appConfigFilePath, null, array('skipExtends' => true, 'allowModifications' => true));

        if ($sectionName != 'production') {
            $this->_sectionName = $sectionName;
        }

        if (!isset($this->_config->{$this->_sectionName})) {
            throw new Zend_Tool_Project_Exception('The config does not have a ' . $this->_sectionName . ' section.');
        }

        if (isset($this->_config->{$this->_sectionName}->resources->db)) {
            throw new Zend_Tool_Project_Exception('The config already has a db resource configured in section ' . $this->_sectionName . '.');
        }

        $this->_configureViaDSN($dsn);
        //actualizamos el index

        $source = $this->_util->getPathTemplate()
            . DIRECTORY_SEPARATOR . 'Public'
            . DIRECTORY_SEPARATOR . 'index.php';
        $destination = $this->_name
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'index.php';
        $this->_util->fullCopy($source, $destination);
    }

    protected function _configureViaDSN($dsn)
    {
        $dsnVars = array();

        if (strpos($dsn, '=') === false) {
            throw new Zend_Tool_Project_Provider_Exception('At least one name value pair is expected, typcially '
                . 'in the format of "adapter=Mysqli&username=uname&password=mypass&dbname=mydb"'
            );
        }

        parse_str($dsn, $dsnVars);

        // parse_str suffers when magic_quotes is enabled
        if (get_magic_quotes_gpc()) {
            array_walk_recursive($dsnVars, array($this, '_cleanMagicQuotesInValues'));
        }

        $dbConfigValues = array('resources' => array('db' => null));

        if (isset($dsnVars['adapter'])) {
            $dbConfigValues['resources']['db']['adapter'] = $dsnVars['adapter'];
            unset($dsnVars['adapter']);
        }

        $dbConfigValues['resources']['db']['params'] = $dsnVars;
        $this->_registry->getRequest()->isPretend();

        // get the config resource
        $applicationConfig = $this->_loadedProfile->search('ApplicationConfigFile');
        $applicationConfig->addItem($dbConfigValues, $this->_sectionName, null);

        $this->_registry->getResponse();
        $applicationConfig->create();
    }

    protected function _cleanMagicQuotesInValues(&$value, $key)
    {
        $value = stripslashes($value);
    }

    public function create()
    {
        $this->createProjectModular();
    }

    /**
     * Create Simple Project
     */
    public function createSimple()
    {
        $source = dirname(__FILE__)
            . DIRECTORY_SEPARATOR . 'Template'
            . DIRECTORY_SEPARATOR . 'Profiles'
            . DIRECTORY_SEPARATOR . 'simple.xml';

        $profileData = file_get_contents($source);
        $newProfile = new Zend_Tool_Project_Profile(array(
                'projectDirectory' => $this->_name,
                'profileData' => $profileData
            ));

        $newProfile->loadFromData();
        $response = $this->_registry->getResponse();
        $response->appendContent('zrad-> Creating project at ' . $this->_name);
        $response->appendContent('zrad-> Note: ', array('separator' => false, 'color' => 'yellow'));
        $response->appendContent('for more information setting up your VHOST, please see docs/README');

        // Update view default
        foreach ($newProfile->getIterator() as $resource) {
            $resource->create();
        }

        // Update view
        $this->_postCreate('Simple');
    }

    public function createModular($unit)
    {                
        $source = dirname(__FILE__)
            . DIRECTORY_SEPARATOR . 'Template'
            . DIRECTORY_SEPARATOR . 'Profiles';            
        
        if (!$unit) {
            $source .= DIRECTORY_SEPARATOR . 'modular.xml';
        } else {
            $source .= DIRECTORY_SEPARATOR . 'modular-unit.xml';         
        }        

        $profileData = file_get_contents($source);
        $newProfile = new Zend_Tool_Project_Profile(array(
                'projectDirectory' => $this->_name,
                'profileData' => $profileData
            ));

        $newProfile->loadFromData();

        //update view default
        foreach ($newProfile->getIterator() as $resource) {
            $resource->create();
        }

        //update view
        $this->_postCreate('Modular');

        $response = $this->_registry->getResponse();
        $response->appendContent('zrad-> Proyecto creado en ' . $this->_name);
        $response->appendContent('zrad-> Nota: ', array('separator' => false, 'color' => 'yellow'));
        $response->appendContent('Para mayor informacion del uso de un VHOST, por favor lea docs/README.');
        $response->appendContent('zrad-> Nota: ', array('separator' => false, 'color' => 'yellow'));
        $response->appendContent('Para el uso de Zrad, por favor lea docs/LICENCIA.');
        $response->appendContent('zrad-> Nota: ', array('separator' => false, 'color' => 'yellow'));
        $response->appendContent('Crea un codigo GA para tu proyecto.');

        if (!Zend_Tool_Project_Provider_Test::isPHPUnitAvailable()) {
            $response->appendContent('zrad-> Nota: ', array('separator' => false, 'color' => 'yellow'));
            $response->appendContent('PHPUnit no se encuentra en tu include_path.');
        }
    }

}
