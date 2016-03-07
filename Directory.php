<?php

/**
 * @see Zrad_Helper_Util
 */
require_once 'Zrad/Helper/Util.php';

class Zrad_Directory
{

    /**
     * @var Zrad_Helpe_Util
     */
    private $_util = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_util = new Zrad_Helper_Util();
    }

    /**
     * @param bool $moduleIncluded
     */
    public function enable($moduleIncluded = false)
    {
        $dir = $this->_util->getPathTemplate();

        //navigation.xml
        $source = $dir . DIRECTORY_SEPARATOR . 'Configs' . DIRECTORY_SEPARATOR .
            'admin-navigation.xml';
        $destination = 'application' . DIRECTORY_SEPARATOR . 'configs' .
            DIRECTORY_SEPARATOR . 'admin-navigation.xml';
        $this->_util->fullCopy($source, $destination);

        $dirModule = '';
        if ($moduleIncluded) {
            $moduleName = 'admin';
            $dirModule = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR;
        }

        //view helper input render
        $source = $dir . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR .
            'decorators';
        $destination = 'application' . DIRECTORY_SEPARATOR . $dirModule . 'views' .
            DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR .
            'decorators';
        $this->_util->fullCopy($source, $destination);

        // Thumb Controller
        if ($moduleIncluded) {
            $source = $dir . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'MultimediaControllerModular.php';
        } else {
            $source = $dir . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'MultimediaControllerSimple.php';
        }
        $destination = 'application' . DIRECTORY_SEPARATOR . $dirModule . 'controllers' . DIRECTORY_SEPARATOR . 'MultimediaController.php';
        $this->_util->fullCopy($source, $destination);

    }

    public function createLogin($moduleName = null)
    {

        $dirModule = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR;
        $dir = $this->_util->getPathTemplate();
        
        // Layout
        $source = $dir
            . DIRECTORY_SEPARATOR . 'Layouts'
            . DIRECTORY_SEPARATOR . 'Admin';
        $destination = 'application'
            . DIRECTORY_SEPARATOR . 'layouts'
            . DIRECTORY_SEPARATOR . 'scripts';
        $this->_util->fullCopy($source, $destination);

        // Login Index
        $source = $dir
            . DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'Login'
            . DIRECTORY_SEPARATOR . 'index.phtml';
        $destination = 'application'
            . DIRECTORY_SEPARATOR . $dirModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'login'
            . DIRECTORY_SEPARATOR . 'index.phtml';
        $this->_util->fullCopy($source, $destination);
        
        // Login preloading
        $source = $dir
            . DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'Login'
            . DIRECTORY_SEPARATOR . 'preloading.phtml';
        $destination = 'application'
            . DIRECTORY_SEPARATOR . $dirModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'login'
            . DIRECTORY_SEPARATOR . 'preloading.phtml';
        $this->_util->fullCopy($source, $destination);

        // Index
        $source = $dir
            . DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'Default'
            . DIRECTORY_SEPARATOR . 'home.phtml';
        $destination = 'application'
            . DIRECTORY_SEPARATOR . $dirModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'index'
            . DIRECTORY_SEPARATOR . 'index.phtml';
        $this->_util->fullCopy($source, $destination);
        
        // Dashboard Index
        $source = $dir
            . DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'Dashboard'
            . DIRECTORY_SEPARATOR . 'index.phtml';
        $destination = 'application'
            . DIRECTORY_SEPARATOR . $dirModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . 'dashboard'
            . DIRECTORY_SEPARATOR . 'index.phtml';
        $this->_util->fullCopy($source, $destination);
        
    }

    public function enableControllers($moduleIncluded = false)
    {
        $dir = dirname(__FILE__);
        $dir = str_replace('Tool\Project\Provider', 'Template', $dir);
        if ($moduleIncluded) {
            $source = $dir . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'ErrorController.php';
            $destination = 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'ErrorController.php';
            $this->_util->fullCopy($source, $destination);
            $source = $dir . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'error.phtml';
            $destination = 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'default'
                . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'error'
                . DIRECTORY_SEPARATOR . 'error.phtml';
            $this->_util->fullCopy($source, $destination);
            //deleted files
            $this->_util->deleteDirectory('application' . DIRECTORY_SEPARATOR . 'controllers');
            $this->_util->deleteDirectory('application' . DIRECTORY_SEPARATOR . 'models');
            $this->_util->deleteDirectory('application' . DIRECTORY_SEPARATOR . 'views');
        }
        $source = $dir . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'default.phtml';
        $destination = 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'default'
            . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'index'
            . DIRECTORY_SEPARATOR . 'index.phtml';
        $this->_util->fullCopy($source, $destination);
    }

}

