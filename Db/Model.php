<?php

/**
 * @see Zrad/Db.php
 */
require_once 'Zrad/Db.php';

/**
 * @see Zrad/Helper/Util.php
 */
require_once 'Zrad/Helper/Util.php';

require_once 'Zrad/Db/Adapter/Pdo/Mysql.php';

/**
 *
 * @author Juan Minaya León
 */
class Zrad_Db_Model
{

    /**
     * @var Zrad_Db
     */
    private $_db = null;

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_db = Zrad_Db::getInstance();
        $this->_util = new Zrad_Helper_Util();
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function describeTable($tableName)
    {
        try {
            $db = $this->_db->getAdapter();
            $tables = $db->listTables();
            if (!in_array($tableName, $tables)) {
                throw new Exception('La tabla "' . $tableName . '" no existe en "' . $this->_db->getDbName() . '"');
            }
            // hack to bitint, int, smallint LENGHT
            $adapterName = $this->_db->getAdapterName();
            if ($adapterName == "PDO_MYSQL") {
                $pdoMysql = new Zrad_Db_Adapter_Pdo_Mysql();
                return $pdoMysql->describeTable($tableName);
            } else {
                return $db->describeTable($tableName);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 
     * Obtiene una lista de las tablas relacionadas
     * 
     * @param string $tableName
     * @return array
     */
    public function getForeignKeys($tableName)
    {
        $dbName = $this->_db->getDbName();
        $db = $this->_db->getAdapter();

        $select = $db->select()
            ->from(array('i' => 'information_schema.KEY_COLUMN_USAGE'), array('TABLE_NAME', 'CONSTRAINT_NAME', 'COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME'))
            ->where('CONSTRAINT_SCHEMA = ?', $dbName)
            ->where('REFERENCED_TABLE_NAME IS NOT NULL')
            ->where('TABLE_NAME = ?', $tableName);
        $results = $db->query($select)->fetchAll(Zend_Db::FETCH_ASSOC);

        $tables = array();
        foreach ($results as $result) {
            $tables['referenced_field'][] = $result['REFERENCED_COLUMN_NAME'];
            $tables['name'][] = $result['REFERENCED_TABLE_NAME'];
            $tables['field'][] = $result['COLUMN_NAME'];
        }
        return $tables;
    }

    /**
     * @param string $tableName nombre de la tabla
     * @return bool retorna si existe o no la tabla
     */
    public function existsTable($tableName)
    {
        $response = true;
        $tables = $this->_db->getAdapter()->listTables();
        if (!in_array($tableName, $tables)) {
            $response = false;
        }
        return $response;
    }

    public function getRelationships($tableName)
    {
        $relationships = array(
            'name' => $tableName,
            'reference' => array(),
            'dependentTables' => array(),
            'referenceMap' => array()
        );
        $dbName = $this->_db->getDbName();
        $db = $this->_db->getAdapter();

        $select = $db->select()
            ->from(array('i' => 'information_schema.KEY_COLUMN_USAGE'), array('TABLE_NAME', 'CONSTRAINT_NAME', 'COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME'))
            ->where('CONSTRAINT_SCHEMA = ?', $dbName)
            ->where('TABLE_NAME = ?', $tableName);
        $resultA = $db->query($select)->fetchAll(Zend_Db::FETCH_ASSOC);

        $select = $db->select()
            ->from(array('i' => 'information_schema.KEY_COLUMN_USAGE'), array('TABLE_NAME', 'CONSTRAINT_NAME', 'COLUMN_NAME', 'REFERENCED_TABLE_NAME', 'REFERENCED_COLUMN_NAME'))
            ->where('CONSTRAINT_SCHEMA = ?', $dbName)
            ->where('REFERENCED_TABLE_NAME IS NOT NULL');
        $resultB = $db->query($select)->fetchAll(Zend_Db::FETCH_ASSOC);

        //buscando tablas dependientes
        foreach ($resultB as $result) {
            if ($result['REFERENCED_TABLE_NAME'] == $tableName) {
                $relationships['dependentTables'][] = $this->_util->format($result['TABLE_NAME'], 8);
            }
        }

        foreach ($resultA as $result) {
            if ($result['CONSTRAINT_NAME'] != 'PRIMARY') {
                //$relationships['referenceMap'][] =
                $relationships['reference'][] = $result['REFERENCED_TABLE_NAME'];
                $relation = $this->_util->format($result['REFERENCED_TABLE_NAME'], 8);
                $namespace = $this->_util->pluralize($relation) . '_Model_DbTable_';
                $reference = array(
                    'columns' => $result['COLUMN_NAME'],
                    'refTableClass' => $namespace . $relation,
                    'refColumns' => $result['REFERENCED_COLUMN_NAME']
                );
                $relationships['referenceMap'][$relation] = $reference;
            } else {
                $relationships['primary'][] = $result['COLUMN_NAME'];
            }
        }

        return $relationships;
    }

}
