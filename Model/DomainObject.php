<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_Model_DomainObject extends Zrad_Abstract
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
            $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'models' . DIRECTORY_SEPARATOR;
            $instance = $this->_util->format($this->_tableName, 1);
            $className = $this->_appnamespace . '_Model_' . $this->_fileName;
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

            $properties = array();
            foreach ($this->_fieldsModel as $field => $attributes) {
                $columName = strtolower($field);
                $columName = $this->_util->format($columName, 1);
                $property = array(
                    'name' => '_' . $this->_util->format($columName),
                    'visibility' => 'protected'
                );
                $properties[] = $property;
            }

            //class
            $class = new Zend_CodeGenerator_Php_Class();
            $class->setName($className)
                ->setDocblock($docblock)
                ->setProperties($properties)
                ->setMethods(array(
                    // Method passed as array
                    array(
                        'name' => '__construct',
                        'parameters' => array(
                            array(
                                'name' => 'options',
                                'type' => 'array',
                                'defaultValue' => null
                            )
                        ),
                        'body' => 'if (is_array($options)) {' . "\n"
                        . '    $this->setOptions($options);' . "\n"
                        . '}' . "\n"
                    ),
                    array(
                        'name' => '__set',
                        'parameters' => array(
                            array('name' => 'name'),
                            array('name' => 'value')
                        ),
                        'body' => '$method = \'set\' . $name;' . "\n"
                        . 'if ((\'mapper\' == $name) || !method_exists($this, $method)) {' . "\n"
                        . '    throw new Exception(\'Invalid ' . $instance . ' property\');' . "\n"
                        . '}' . "\n"
                        . '$this->$method($value);'
                    ),
                    array(
                        'name' => '__get',
                        'parameters' => array(
                            array('name' => 'name')
                        ),
                        'body' => '$method = \'get\' . $name;' . "\n"
                        . 'if ((\'mapper\' == $name) || !method_exists($this, $method)) {' . "\n"
                        . '    throw new Exception(\'Invalid ' . $instance . ' property\');' . "\n"
                        . '}' . "\n"
                        . 'return $this->$method();'
                    ),
                    array(
                        'name' => 'setOptions',
                        'parameters' => array(
                            array(
                                'name' => 'options',
                                'type' => 'array'
                            )
                        ),
                        'body' => '$methods = get_class_methods($this);' . "\n"
                        . 'foreach ($options as $key => $value) {' . "\n"
                        . '    $method = \'set\' . ucfirst($key);' . "\n"
                        . '    if (in_array($method, $methods)) {' . "\n"
                        . '        $this->$method($value);' . "\n"
                        . '    }' . "\n"
                        . '}' . "\n"
                        . 'return $this;'
                    ),
                    array(
                        'name' => 'toArray',
                        'body' => '$data = array();' . "\n"
                        . '$vars = get_class_vars(\'' . $className . '\');' . "\n"
                        . 'foreach ($vars as $key => $value) {' . "\n"
                        . '    $key = substr($key, 1);' . "\n"
                        . '    $method = \'get\' . ucfirst($key);' . "\n"
                        . '    $data[$key] = $this->$method();' . "\n"
                        . '}' . "\n"
                        . 'return $data;'
                    )
                ));

            //method
            $methods = array();
            foreach ($this->_fieldsModel as $field => $attributes) {
                $columName = strtolower($field);
                $columName = $this->_util->format($columName, 1);
                $var = $this->_util->format($field);
                $setMethod = new Zend_CodeGenerator_Php_Method(array(
                        'name' => $this->_util->format('set_' . $columName),
                        'parameters' => array(
                            array(
                                'name' => $columName
                            )
                        ),
                        'body' => '$this->_' . $columName . ' = $' . $columName . ';' . "\n"
                        . 'return $this;'
                    ));
                $getMethod = new Zend_CodeGenerator_Php_Method(array(
                        'name' => $this->_util->format('get_' . $columName),
                        'body' => 'return $this->_' . $columName . ';'
                    ));
                $methods[] = $setMethod;
                $methods[] = $getMethod;
            }

            $class->setMethods($methods);

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