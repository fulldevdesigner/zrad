<?php

/**
 * @see Zrad_Db_Singleton
 */
require_once 'Zrad/Db/Singleton.php';

/**
 *
 * @author Juan Minaya
 */
class Zrad_Db implements Zrad_Db_Singleton
{

    /**
     * @var Zend_Db
     */
    protected $_adapter = null;
    /**
     * @var string
     */
    protected $_dbName = '';
    /**
     * @var string
     */
    protected $_adapterName = '';
    /**
     * @var Zrad_Db
     */
    private static $_instance = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $config = new Zend_Config_Ini(
                'application' . DIRECTORY_SEPARATOR .
                'configs' . DIRECTORY_SEPARATOR . 'zrad.ini');

        $section = (isset($config->namespace->section)) ? $config->namespace->section : 'development';

        //config
        $config = new Zend_Config_Ini(
                'application' . DIRECTORY_SEPARATOR .
                'configs' . DIRECTORY_SEPARATOR . 'application.ini',
                $section);

        $db = $config->resources->db;

        //adapter
        $this->_adapter = Zend_Db::factory($db->adapter, array(
                'host' => $db->params->host,
                'username' => $db->params->username,
                'password' => $db->params->password,
                'dbname' => $db->params->dbname
            ));

        $this->_dbName = (string) $db->params->dbname;
        $this->_adapterName = strtoupper((string) $db->adapter);
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->_dbName;
    }

    /**
     * @return string
     */
    public function getAdapterName()
    {
        return $this->_adapterName;
    }

    /**
     * @return Zrad_Db
     */
    public static function getInstance()
    {
        try {
            if (self::$_instance == null) {
                self::$_instance = new self();
            }
            return self::$_instance;
        } catch (Exception $e) {
            throw $e;
        }
    }

}