<?php

/**
 * Zend Rad
 *
 * LICENCIA
 *
 * Este archivo está sujeta a la licencia CC(Creative Commons) que se incluye
 * en docs/LICENCIA.txt.
 * Tambien esta disponible a traves de la Web en la siguiente direccion
 * http://www.zend-rad.com/licencia/
 * Si usted no recibio una copia de la licencia por favor envie un correo
 * electronico a <licencia@zend-rad.com> para que podamos enviarle una copia
 * inmediatamente.
 *
 * @author Juan Minaya Leon <info@juanminaya.com>
 * @copyright Copyright (c) 2011-2012 , Juan Minaya Leon
 * (http://www.zend-rad.com)
 * @licencia http://www.zend-rad.com/licencia/   CC licencia Creative Commons
 */
class Default_Model_UbigeoMapper
{

    /**
     * Instancia de Zend_Db_Table
     *
     * @var Default_Model_DbTable_Ubigeo
     */
    protected $_dbTable = null;

    /**
     * Inicializa la Zend_Db_Table
     *
     * @param string $dbTable
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * Devuelve una instacia de Zend_Db_Table
     *
     * @return Default_Model_DbTable_Ubigeo
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_Ubigeo');
        }
        return $this->_dbTable;
    }

    /**
     * Obtiene una instancia de la clase Default_Model_Ubigeo
     *
     * @param int $id
     * @param Default_Model_Ubigeo $ubigeo
     */
    public function find($id, Default_Model_Ubigeo $ubigeo)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $ubigeo->setId($row->id);
        $ubigeo->setCodigoDepartamento($row->codigo_departamento);
        $ubigeo->setCodigoProvincia($row->codigo_provincia);
        $ubigeo->setCodigoDistrito($row->codigo_distrito);
        $ubigeo->setNombre($row->nombre);
    }

    /**
     * Obtiene un array de instancias de la clase Default_Model_Ubigeo
     *
     * @param array $where
     * @return array
     */
    public function fetchAll($where = null)
    {
        $resultSet = (is_array($where)) ? $this->getDbTable()->fetchAll($where) : $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Default_Model_Ubigeo();
            $entry->setId($row->id);
            $entry->setCodigoDepartamento($row->codigo_departamento);
            $entry->setCodigoProvincia($row->codigo_provincia);
            $entry->setCodigoDistrito($row->codigo_distrito);
            $entry->setNombre($row->nombre);
            $entries[] = $entry;
        }
        return $entries;
    }

    /**
     * Puebla los combos HTMls
     *
     * @param bool $default
     * @param string $optionDefault
     * @return array
     */
    public function obtenerCiudades($default = true, $optionDefault = 'Todas', $simple = false)
    {
        $resultSet = $this->getDbTable()->fetchAll(array(
            'codigo_provincia = ?' => 0,
            'codigo_distrito = ?'  => 0
            ));
        $options = ($default) ? array('' => $optionDefault) : array();
        foreach ($resultSet as $row) {
            $key = ($simple) ? $row->nombre : $row->id;
            $options[$key] = $row->nombre;
        }
        return $options;
    }
    
    /**
     * Busca las provincias de un departamento
     * 
     * @param int $codigoDepartamento
     * @return array
     */
    public function obtenerProvinciasPorDepartamento($codigoDepartamento)
    {
        $resultSet = $this->getDbTable()->fetchAll(array(
            'codigo_departamento = ?' => $codigoDepartamento,
            'codigo_provincia <> ?'   => 0,
            'codigo_distrito = ?'     => 0,
            ));

        return $resultSet->toArray();
    }
    
    /**
     * Busca las provincias de un departamento
     * 
     * @param int $codigoDepartamento
     * @param int $codigoProvincia
     * @return array
     */
    public function obtenerDistritosPorProvincia($codigoDepartamento, $codigoProvincia)
    {
        $resultSet = $this->getDbTable()->fetchAll(array(
            'codigo_departamento = ?' => $codigoDepartamento,
            'codigo_provincia = ?'   => $codigoProvincia,
            'codigo_distrito <> ?'     => 0,
            ));

        return $resultSet->toArray();
    }


}

