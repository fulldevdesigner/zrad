<?php

/**
 * @see Zrad_Model_DomainObject
 */
require_once 'Zrad/Model/DomainObject.php';

/**
 * @see Zrad_Model_Mapper
 */
require_once 'Zrad/Model/Mapper.php';

/**
 * @see Zrad_Model_DbTable
 */
require_once 'Zrad/Model/DbTable.php';

class Zrad_Model 
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    /**
     * @var Zrad_Model_Mapper
     */
    private $_mapper = null;

    /**
     * @var Zrad_Model_DomainObject
     */
    private $_domainObject = null;

    /**
     * @var string|null
     */
    private $_moduleName = null;

    /**
     * @var Zrad_Model_DbTable
     */
    private $_dbTable = null;

    /**
     * @var string
     */
    private $_tableName = '';

    /**
     * @var string
     */
    private $_originalTableName = '';

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;

    /**
     * @var boolean
     */
    private $_isReflection = false;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        //parent::__construct(); only zf 1.10.8

        if (!isset($config['tableName'])) {
            throw new Exception('No se ha definido la tabla o nodo');
        }
        if(isset($config['originalTableName'])){
             $this->_originalTableName = $config['originalTableName'];
        }
        if (!isset($config['registry'])) {
            throw new Exception('No se ha encontrado instacia de Registry');
        }
        if (!isset($config['basic'])) {
            throw new Exception('¿basica o completa?');
        }
        $registry = $config['registry'];
        if (!($registry instanceof Zend_Tool_Framework_Registry_Interface)) {
            throw new Exception('config[registry] debe ser instancia de Zend_Tool_Framework_Registry_Interface');
        }
        $this->_tableName = $config['tableName'];
        $this->setRegistry($registry);
        if (isset($config['moduleName'])) {
            $this->_moduleName = $config['moduleName'];
        }        
        $this->_domainObject = new Zrad_Model_DomainObject($config);
        $this->_mapper = new Zrad_Model_Mapper($config);
        $this->_dbTable = new Zrad_Model_DbTable($config);
        $this->_util = new Zrad_Helper_Util();
    }

    public function create()
    {
        try {
            $this->createDomainObject();
            $this->createMapper();
            $this->createDbTable();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createDbTable()
    {
        try {           
            $dbTableName = $this->_dbTable->getFileName();
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            if(!Zend_Tool_Project_Provider_DbTable::hasResource($this->_loadedProfile, $dbTableName, $this->_moduleName)){
                $tableResource = Zend_Tool_Project_Provider_DbTable::createResource($this->_loadedProfile, $dbTableName, $this->_tableName, $this->_moduleName);
                $tableResource->create();
                //replace function $this->_storeProfile()
                $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                $projectProfileFile->getContext()->save();
            }
            //patch to fix , this cretae lowercase first letter of model
            //example: module usuario create class name: class usuario_Model_DbTable
            //correct name is: class Usuario_Model_DbTable
            $this->_dbTable->create();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createMapper()
    {
        try {
            $mapperName = $this->_mapper->getFileName();
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            if (!Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $mapperName, $this->_moduleName)) {
                Zend_Tool_Project_Provider_Model::createResource($this->_loadedProfile, $mapperName, $this->_moduleName);
                //replace function $this->_storeProfile()
                $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                $projectProfileFile->getContext()->save();
            } else {
                // Ruta del modelo
                $pathMapper = getcwd()
                . DIRECTORY_SEPARATOR . 'application'
                . DIRECTORY_SEPARATOR . 'modules'
                . DIRECTORY_SEPARATOR . $this->_moduleName
                . DIRECTORY_SEPARATOR . 'models';
                // Traemos el dominio
                require_once $pathMapper . DIRECTORY_SEPARATOR . $this->_domainObject->getFileName() . '.php';               
                $this->_isReflection = true;
                $this->_mapper->setIsReflection(true);
            }
            $this->_mapper->create();            
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createDomainObject()
    {
        try {
            $domainObjectName = $this->_domainObject->getFileName();
            $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            if (!Zend_Tool_Project_Provider_Model::hasResource($this->_loadedProfile, $domainObjectName, $this->_moduleName)) {
                Zend_Tool_Project_Provider_Model::createResource($this->_loadedProfile, $domainObjectName, $this->_moduleName);
                //replace function $this->_storeProfile()
                $projectProfileFile = $this->_loadedProfile->search('ProjectProfileFile');
                $projectProfileFile->getContext()->save();
            }
            $this->_domainObject->create();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return boolean
     */
    public function getIsReflection()
    {
        return $this->_isReflection;
    }
}