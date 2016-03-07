<?php

/**
 * @author Juan Minaya leon
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

/**
 * @see Zrad_Form_Biuld
 */
require_once 'zrad/Form/Element.php';

class Zrad_Form_Build extends Zrad_Abstract
{

    private $_formName = '';

    public function setFormName($formName)
    {
        $this->_formName = $formName;
    }

    /**
     * Init
     */
    protected function _init()
    {
        $this->_fileName = $this->_formName;
    }

    /**
     * Create Form
     */
    public function create()
    {
        $this->_fileName = $this->_formName;
        $className = $this->_appnamespace . '_Form_' . $this->_fileName;

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

        // Atributo ciudades
        $properties = array();

        if (isset($this->_config['isFacebook'])) {
            // Property dbTable
            $property = new Zend_CodeGenerator_Php_Property(array(
                    'name' => '_ciudades',
                    'visibility' => 'private',
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'tags' => array(
                            array(
                                'name' => 'var',
                                'description' => 'array'
                            )
                        )
                    ))
                ));
            $properties[] = $property;
        }

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setDocblock($docblock)
            ->setProperties($properties)
            ->setExtendedClass('Zend_Form');

        $body = '// Custom Validator and Elements' . "\n"
            . '$this->addPrefixPath(\'ZradAid_Form\', \'ZradAid/Form/\');' . "\n"
            . '$this->addElementPrefixPath(\'ZradAid_Validate\', \'ZradAid/Validate\', \'validate\');' . "\n\n"
            . '// Set the method for the display form to POST' . "\n"
            . '$this->setMethod(\'post\');' . "\n\n";

        $element = new Zrad_Form_Element();
        $element->setEntity($this->_mapper['table']);
        // Recorremos los campos depende si viene de facebook
        if (isset($this->_config['isFacebook'])) {
            foreach ($this->_mapper['fields'] as $field => $atributos) {
                // Obtenemos el elemento
                switch ($field) {
                    case 'paso': // no hacemos nada
                        break;
                    case 'es_activo': // no hacemos nada
                        break;
                    case 'edad': // no hacemos nada
                        break;
                    case 'ciudad':$body .= '// Add Ciudad element' . "\n"
                        . '$this->addElement(\'select\', \'ciudad\', array(' . "\n" 
                        . $this->getWhitespace(4) . '\'label\' => \'Ciudad\',' . "\n" 
                        . $this->getWhitespace(4) . '\'description\' => \'&nbsp;\',' . "\n" 
                        . $this->getWhitespace(4) . '\'required\' => true,' . "\n" 
                        . $this->getWhitespace(4) . '\'validators\' => array(' . "\n" 
                        . $this->getWhitespace(8) . '\'NotEmpty\',' . "\n" 
                        . $this->getWhitespace(4) . '),' . "\n" 
                        . $this->getWhitespace(4) . '\'multiOptions\' => $this->_ciudades,' . "\n" 
                        . $this->getWhitespace(4) . '\'decorators\' => array(' . "\n" 
                        . $this->getWhitespace(8) . 'array(\'ViewHelper\', array(\'tag\' => null))' . "\n" 
                        . $this->getWhitespace(4) . ')' . "\n" 
                        . '));' . "\n" . "\n" ;
                        break;
                    case 'fecha_nacimiento': $body .= '// Add Fecha Nacimiento element (showMonths => \'number\')' . "\n" 
                        . '$this->addElement(\'birth\', \'fechaNacimiento\', array(' . "\n" 
                        . $this->getWhitespace(4) . '\'label\' => \'Fecha Nac.\',' . "\n" 
                        . $this->getWhitespace(4) . '\'description\' => \'&nbsp;\',' . "\n" 
                        . $this->getWhitespace(4) . '\'required\' => true,' . "\n" 
                        . $this->getWhitespace(4) . '\'minAge\' => 18,' . "\n" 
                        . $this->getWhitespace(4) . '\'default\' => \'number\',' . "\n" 
                        . $this->getWhitespace(4) . '\'validators\' => array(' . "\n" 
                        . $this->getWhitespace(8) . '\'NotEmpty\',' . "\n" 
                        . $this->getWhitespace(8) . 'array(\'Date\', true, array(\'format\' => \'dd/MM/yyyy\')),' . "\n" 
                        . $this->getWhitespace(4) . '),' . "\n"
                        . $this->getWhitespace(4) . '\'decorators\' => array(' . "\n" 
                        . $this->getWhitespace(8) . 'array(\'ViewHelper\', array(\'tag\' => null))' . "\n" 
                        . $this->getWhitespace(4) . ')' . "\n"
                        . '));' . "\n" . "\n" ;
                        break;
                    default: $body .= $element->generate($field, $atributos, $this->_target);
                        break;
                }                                
            }
        } else {
            foreach ($this->_mapper['fields'] as $field => $atributos) {
                // Obtenemos el elemento
                $body .= $element->generate($field, $atributos, $this->_target);
            }
        }

        if ($this->_config['captcha']) {
            $body .= '// Add captcha element' . "\n"
                . '$this->addElement(\'text\', \'verificacion\', array(' . "\n"
                . '    \'label\' => \'Código de verificación\',' . "\n"
                . '    \'description\' => \'Ingrese los caracteres que logra ver en la imagen\',' . "\n"
                . '    \'required\' => true,' . "\n"
                . '    \'maxlength\' => 5,' . "\n"
                . '    \'style\' => \'width:102px\',' . "\n"
                . '    \'filters\' => array(' . "\n"
                . '        \'StringTrim\'' . "\n"
                . '    ),' . "\n"
                . '    \'validators\' => array(' . "\n"
                . '        \'NotEmpty\',' . "\n"
                . '        \'Alnum\',' . "\n"
                . '        \'Captcha\',' . "\n"
                . '        array(\'StringLength\', false, array(5))' . "\n"
                . '    ),' . "\n"
                . '    \'decorators\' => array(' . "\n"
                . '        array(\'ViewHelper\', array(\'tag\' => null))' . "\n"
                . '    )' . "\n"
                . '));' . "\n\n";
        }

        $body .= '// Add the submit button' . "\n"
            . '$this->addElement(\'submit\', \'submit\', array(' . "\n"
            . '    \'ignore\' => true,' . "\n"
            . '    \'label\' => \'Enviar\',' . "\n"
            . '));';

        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));

        $class->setMethod($method);

        if (isset($this->_config['isFacebook'])) {
            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'setCiudades',
                    'parameters' => array(
                        array('name' => 'ciudades')
                    ),
                    'body' => '$this->_ciudades = $ciudades;',
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Puebla el array con las ciudades',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'ciudades',
                                'datatype' => 'array'
                            ))
                        ),
                    ))
                ));
            $class->setMethod($method);
        }

        //echo $class->__toString();
        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);
        $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'forms' . DIRECTORY_SEPARATOR;
        file_put_contents($path . $this->_fileName . '.php', $file->generate());
    }

}

