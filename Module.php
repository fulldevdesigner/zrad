<?php
/**
 * @see Zrad_Directory
 */
require_once 'Zrad/Directory.php';

/**
 * @see Zrad_Directory
 */
require_once 'Zrad/Login.php';

/**
 * @see Zrad_Helper_Util
 */
require_once 'Zrad/Helper/Util.php';

class Zrad_Module
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    /**
     * @var Zrad_Directory
     */
    private $_directory = null;

    /**
     * @var Zrad_Login
     */
    private $_login = null;

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;

    /**
     * Construct
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
        $this->_directory = new Zrad_Directory();
        $this->_util = new Zrad_Helper_Util();
        $this->_login = new Zrad_Login($config);
    }
    
    

    /**
     * Crea el modulo admin si es un proyecto modular y copia los archivos
     */
    public function initBackend()
    {
        try {
            $isModular = $this->_util->isModular();
            $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            $moduleName = 'admin';
            $modulesDirectory = $profile->search('modulesDirectory');
            if ($isModular && !$modulesDirectory->search(array('moduleDirectory' => array('moduleName' => $moduleName)))) {
                $moduleResources = Zend_Tool_Project_Provider_Module::createResources($this->_loadedProfile, $moduleName);
                $enabledFilter = new Zend_Tool_Project_Profile_Iterator_EnabledResourceFilter($moduleResources);
                foreach (new RecursiveIteratorIterator($enabledFilter, RecursiveIteratorIterator::SELF_FIRST) as $resource) {
                    $resource->create();
                }
                
                //replace function $this->_storeProfile()
                $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                $projectProfileFile->getContext()->save();

                //actualizamos el bootstrap by reflection
                $path = 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
                $generator = Zend_CodeGenerator_Php_File::fromReflectedFileName($path);
                $class = $generator->getClass();
                $method = new Zend_CodeGenerator_Php_Method(array(
                        'name' => '_initAutoloader' . ucfirst($moduleName),
                        'visibility' => 'protected',
                        'body' => '$autoloader = new Zend_Application_Module_Autoloader(array(' . "\n"
                        . '    \'namespace\' => \'' . ucfirst($moduleName) . '_\',' . "\n"
                        . '    \'basePath\' => dirname(__FILE__) . \'/modules/' . $moduleName . '\'));' . "\n"
                        . 'return $autoloader;' . "\n"
                    ));
                $class->setMethod($method);
                $file = new Zend_CodeGenerator_Php_File();
                $file->setClass($class);
                file_put_contents('application' . DIRECTORY_SEPARATOR . 'Bootstrap.php', $file->generate());                
            }
            
            //copiamos lo directorios necesarios
            $this->_directory->enable($isModular);

            //creamos el login
            $this->_login->create();

            //actualizamos el bootstrap
            $this->_updateBootstrap();

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Actualizamos el BootStrap
     */
    protected function _updateBootstrap()
    {
        //update bootstrap by reflection
        $path = 'application' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
        $generator = Zend_CodeGenerator_Php_File::fromReflectedFileName($path);
        $class = $generator->getClass();
        
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => '_initNavigationAdmin',
                'visibility' => 'protected',
                'body' => '$this->bootstrap(\'layout\');' . "\n"
                . '$layout = $this->getResource(\'layout\');' . "\n"
                . '$view = $layout->getView();' . "\n"
                . '$config = new Zend_Config_Xml(APPLICATION_PATH . \'/configs/admin-navigation.xml\', \'nav\');' . "\n"
                . '$navigation = new Zend_Navigation($config);' . "\n"
                . '$view->navigation($navigation);' . "\n"
                . '$partial = array(\'elements/menu-admin.phtml\', \'default\');' . "\n"
                . '$view->navigation()->menu()->setPartial($partial);'
            ));
        $class->setMethod($method);
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);
        file_put_contents('application' . DIRECTORY_SEPARATOR . 'Bootstrap.php', $file->generate());
    }
}
