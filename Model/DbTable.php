<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_Model_DbTable extends Zrad_Abstract
{

    /**
     * Init
     */
    protected function _init()
    {
        $this->_fileName = ucfirst($this->_util->format(strtolower($this->_tableName), 1));
    }

    /**
     * Create Domain Object
     */
    public function create()
    {
        try {
            $path = 'application'
                . DIRECTORY_SEPARATOR . $this->_pathModule . 'models'
                . DIRECTORY_SEPARATOR . 'DbTable'
                . DIRECTORY_SEPARATOR;
            $className = $this->_appnamespace . '_Model_DbTable_' . $this->_fileName;
            //phpdoc
            $docblock = new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Zend Rad' . "\n" . "\n" . 'LICENCIA',
                    'longDescription' => 'Este archivo está sujeta a la licencia CC(Creative Commons) que se incluye' . "\n"
                    . 'en docs/LICENCIA.txt.' . "\n"
                    . 'Tambien esta disponible a traves de la Web en la siguiente direccion' . "\n"
                    . 'http://www.zend-rad.com/licencia/' . "\n"
                    . 'Si usted no recibio una copia de la licencia por favor envie un correo' . "\n"
                    . 'electronico a <licencia@zend-rad.com> para que podamos enviarle una copia' . "\n"
                    . 'inmediatamente.'
                    ,
                    'tags' => array(
                        array(
                            'name' => 'author',
                            'description' => 'Juan Minaya Leon <info@juanminaya.com>'
                        ),
                        array(
                            'name' => '@copyright',
                            'description' => 'Copyright (c) 2011-' . date('Y') . ' , Juan Minaya Leon (http://www.zend-rad.com)'
                        ),
                        array(
                            'name' => 'licencia',
                            'description' => 'http://www.zend-rad.com/licencia/   CC licencia Creative Commons'
                        )
                    )
                ));

            $relationships = $this->_mapper['relationships'];
            $properties = array();

            //name
            $property = new Zend_CodeGenerator_Php_Property(
                    array(
                        'name' => '_name',
                        'visibility' => 'protected',
                        'defaultValue' => $relationships['name']
                    )
            );

            array_push($properties, $property);


            //dependentTables
            if (isset($relationships['dependentTables'])) {

                $property = new Zend_CodeGenerator_Php_Property(
                        array(
                            'name' => '_dependentTables',
                            'visibility' => 'protected',
                            'defaultValue' => $relationships['dependentTables']
                        )
                );
                array_push($properties, $property);
            }

            //referenceMap

            if (isset($relationships['referenceMap'])) {
                $property = new Zend_CodeGenerator_Php_Property(
                        array(
                            'name' => '_referenceMap',
                            'visibility' => 'protected',
                            'defaultValue' => $relationships['referenceMap']
                        )
                );
                array_push($properties, $property);
            }

            //class
            $class = new Zend_CodeGenerator_Php_Class();
            $class->setName($className)
                ->setDocblock($docblock)
                ->setProperties($properties)
                ->setExtendedClass('Zend_Db_Table_Abstract');

            //file
            $file = new Zend_CodeGenerator_Php_File();
            $file->setClass($class);

            //create file            
            file_put_contents($path . $this->_fileName . '.php', $file->generate());
        } catch (Exception $e) {
            throw $e;
        }
    }

}