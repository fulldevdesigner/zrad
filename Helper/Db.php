<?php

/**
 *
 * @author Juan Minaya
 */
class Zrad_Helper_Db
{

    private $_db;
    
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $_adapter = null;

    public function __construct()
    {
       
    }

    public function getDbName()
    {
        return $this->_db->params->dbname;
    }

    public function getListTables()
    {

        //adapter
        $adapter = Zend_Db::factory($this->_db->adapter, array(
                'host' => $this->_db->params->host,
                'username' => $this->_db->params->username,
                'password' => $this->_db->params->password,
                'dbname' => $this->_db->params->dbname
            ));

        return $adapter->listTables();
    }

    /**
     * @param string $tableName nombre de la tabla
     * @return bool retorna si existe o no la tabla
     */
    public function existsTable($tableName)
    {
        $response = true;
        $tables = $this->_adapter->listTables();
        if (!in_array($tableName, $tables)) {
            $response = false;
        }
        return $response;
    }
}
