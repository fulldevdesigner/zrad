<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_Crud_Form extends Zrad_Abstract
{
    /**
     * @var string
     */
    private $_typeElement = 'text';

    /**
     * @var string
     */
    private $_appnamespaceModel = '';

    /**
     * @var string
     */
    private $_relationTable = '';

    /**
     * @var array
     */
    private $_noValidColumns = array(
        'id',
        'created',
        'modified',
        'ip'
    );

    /**
     * Init
     */
    protected function _init()
    {
        $this->_fileName = ucfirst($this->_util->format(strtolower($this->_tableName), 1));        
    }

    /**
     * Create Form
     */
    public function create()
    {
        $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'forms' . DIRECTORY_SEPARATOR;

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

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setDocblock($docblock)
            ->setExtendedClass('Zend_Form');

        $body = '// Set the method for the display form to POST' . "\n"
            . '$this->setMethod(\'post\');' . "\n\n";

        foreach ($this->_mapper['fields'] as $field) {
            
                //type
                $type = 'text';
                if ($field['DATA_TYPE'] != 'int' && $field['DATA_TYPE'] != 'date'
                    && $columnName != 'email' && $columnName != 'dni') {
                    $validators = 'array(\'StringLength\', false, array(0, ' . $field['LENGTH'] . '))';
                } else {
                    $validators = '';
                }

                $filters = '';

                switch ($field['DATA_TYPE']) {
                    case 'text':
                        $type = 'textarea';
                        break;
                    case 'int':
                        $validators = '\'Digits\'';
                        break;
                    case 'tinyint':
                        $type = 'checkbox';
                        $validators = '\'Digits\'';
                        break;
                    case 'date':
                        $validators = 'array(\'Date\', true, array(\'format\'=>\'dd/mm/yyyy\')),' . "\n"
                            . $this->_indentation . $this->_indentation . 'array(\'StringLength\', false, array(10, 10))';
                        break;
                }

                switch ($columnName) {
                    case 'email':
                        if (!empty($validators))
                            $validators .= ',' . "\n" . $this->_indentation . $this->_indentation;
                        $validators .= '\'EmailAddress\',' . "\n"
                            . $this->_indentation . $this->_indentation . 'array(\'StringLength\', false, array(5, 50))';
                        break;
                    case 'dni':
                        if (!empty($validators))
                            $validators .= ',' . "\n" . $this->_indentation . $this->_indentation;
                        $validators .= 'array(\'StringLength\', false, array(8, 8))';
                        break;
                    case 'ruc':
                        if (!empty($validators))
                            $validators .= ',' . "\n" . $this->_indentation . $this->_indentation;
                        $validators .= 'array(\'StringLength\', false, array(11, 11))';
                        break;
                    case 'username':
                        if (!empty($validators))
                            $validators .= ',' . "\n" . $this->_indentation . $this->_indentation;
                        $validators .= '\'Alnum\',' . "\n"
                            . $this->_indentation . $this->_indentation .'array(\'Regex\', false, array(\'/^[a-z][a-z0-9]{2,}$/\'))';
                        $filters .= ', \'StringToLower\'';
                        break;
                    case 'password':
                        if (!empty($validators))
                            $validators .= ',' . "\n" . $this->_indentation . $this->_indentation;
                        //$validators .= 'array(\'StringLength\', false, array(6,15))';
                        $type = 'password';
                        break;
                }

                if (!empty($validators)) {
                    $validators = ',' . "\n" . $this->_indentation . $this->_indentation . $validators;
                }

                //verificando si es un elemento estandar
                //$this->_typeElement = 'text';

                if ($this->_isStandardElement($originalColumnName, $type)) {
                    $body .= $this->_getStandardElement($columnName, $type, $var, $filters, $validators);  
                }else{
                    $body .= $this->_getNoStandardElement($columnName, $var, $filters, $validators);
                }
            
        }

        $body .= '// Add the submit button' . "\n"
            . '$this->addElement(\'submit\', \'submit\', array(' . "\n"
            . '    \'ignore\' => true,' . "\n"
            . '    \'label\' => \'Sign\',' . "\n"
            . '));';

        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));

        $class->setMethod($method);

        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        //create file        
        file_put_contents($path . $this->_fileName . '.php', $file->generate());
    }

    /**
     * @param string $columnName
     * @return bool
     */
    private function _isStandardElement($columnName, $type)
    {
        //verificando si es imagen
        preg_match_all("/image/", $columnName, $output);
        if(!empty($output[0])){
            $image = $output[0][0];
            if ($image == 'image') {
                $this->_typeElement = 'image';
                return false;
            }
        }
        //verificando si es checkbox
        if($type == 'checkbox'){
            $this->_typeElement = 'checkbox';
            return false;
        }
        if($type == 'password'){
            $this->_typeElement = 'password';
            return false;
        }
        //verificando si es select        
        preg_match('/_id/i', $columnName, $output);
        if(!empty($output)){
            $lenght = strlen($columnName) - 3;
            $this->_relationTable = substr($columnName, 0, $lenght);
            $this->_typeElement = 'select';
            $this->_appnamespaceModel = $this->_appnamespace;
            if($this->_moduleName !== null){
                $this->_appnamespaceModel = ucfirst($this->_util->pluralize($this->_relationTable, 'es'));
            }
            return false;
        }
        //verificando si es checkbox
        return true;
        
    }

    /**
     * @param string $columnName
     * @return string
     */
    private function _getNoStandardElement($columnName, $var = null, $filters = null, $validators = null)
    {
        switch($this->_typeElement){
            case 'image' :
                 $body = '$imagen = new Zend_Form_Element_File(\'' . $var . '\');' . "\n"
                    // . '$imagen->setDisableTranslator(false);' . "\n"
                    . '$imagen->setLabel(\'' . $this->_util->format($columnName, 4) . '\');' . "\n"
                    . '$imagen->setRequired(true);' . "\n"
                    . '$imagen->setDescription(\'&nbsp;\');' . "\n"
                    . '$imagen->addValidator(\'Count\', false, 1);' . "\n"
                    . '$imagen->addValidator(\'Size\', false, array(\'min\' => 20, \'max\' => 1960837));' . "\n"
                    . '$imagen->addValidator(\'Extension\', false, \'jpg,png,gif\');' . "\n"
                    . '$imagen->setDecorators(array(\'File\', ' . "\n"
                    . $this->_indentation . 'array(\'ViewScript\', array(\'viewScript\' => \'decorators/input-image.phtml\', \'placement\' => false))' . "\n"
                    . '));' . "\n"
                    . '$this->addElement($imagen);' . "\n\n";
                break;
            case 'checkbox':
                 $body = '// Add ' . $var . ' element' . "\n"
                    . '$this->addElement(\'' . $this->_typeElement . '\', \'' . $var . '\', array(' . "\n"
                    . '    \'label\' => \'' . $this->_util->format($columnName, 4) . '\',' . "\n"
                    . '    \'required\' => true,' . "\n"
                    . '    \'filters\' => array(\'StringTrim\'' . $filters . '),' . "\n"
                    . '    \'validators\' => array(' . "\n"
                    . '        \'NotEmpty\'' . $validators . "\n"
                    . '    ),' . "\n"
                    . '    \'description\' => \'&nbsp;\',' . "\n"
                    . '    \'decorators\' => array(' . "\n"
                    . '        array(\'ViewScript\', array(\'viewScript\' => \'decorators/input-checkbox.phtml\' ))' . "\n"
                    . '    )' . "\n"
                    . '));' . "\n\n";
                break;
            case 'password':
                $body = '// Add ' . $columnName . ' element' . "\n"
                    . '$this->addElement(\'' . $this->_typeElement . '\', \'' . $var . '\', array(' . "\n"
                    . '    \'label\' => \'' . $this->_util->format($columnName, 4) . '\',' . "\n"
                    . '    \'required\' => true,' . "\n"
                    . '    \'filters\' => array(\'StringTrim\'' . $filters . '),' . "\n"
                    . '    \'validators\' => array(' . "\n"
                    . '        \'NotEmpty\'' . $validators . "\n"
                    . '    ),' . "\n"
                    . '    \'description\' => \'&nbsp;\',' . "\n"
                    . '    \'class\' => \'ui-widget-content ui-corner-all ui-input ui-upper\',' . "\n"
                    . '    \'decorators\' => array(' . "\n"
                    . '        array(\'ViewScript\', array(\'viewScript\' => \'decorators/input-password.phtml\' ))' . "\n"
                    . '    )' . "\n"
                    . '));' . "\n\n";
                break;
            case 'select':
                $tableEntity = $this->_util->format($this->_relationTable, 1);
                $body = '// Add ' . $columnName . ' element' . "\n"
                    . '$' . $var . ' = $this->createElement(\'select\', \'' . $var . '\')'  . "\n"
                    . '    ->setLabel(\'' . $this->_util->format($columnName, 4) . '\')' . "\n"
                    . '    ->addMultiOption(\'\', \'Seleccione...\')' . "\n"
                    . '    ->addValidator(\'Int\')' . "\n"
                    . '    ->setDescription(\'&nbsp;\')' . "\n"
                    . '    ->setRequired(true)' . "\n"
                    . '    ->setDecorators(array(array(\'ViewScript\', array(\'viewScript\' => \'decorators/input-select.phtml\' ))));' . "\n"
                    . '$this->addElement($' . $var . ');' . "\n\n"
                    . '$' . $tableEntity . 'Mapper = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . 'Mapper();' . "\n"
                    . '$' . $this->_util->pluralize($this->_relationTable, 'es') . ' = $' . $tableEntity . 'Mapper->fetchAll();' . "\n"
                    . 'foreach ($' .$this->_util->pluralize($this->_relationTable, 'es') . ' as $' . $this->_relationTable . ') {' . "\n"
                    . '    $' . $var . '->addMultiOption($' . $this->_relationTable . '->getId(), $' . $this->_relationTable . '->getNombre());' . "\n"
                    . '}' . "\n\n";
                break;
        }
        return $body;
    }


    /**
     * @param string $columnName
     * @param string $type
     * @param string $var
     * @param string $filters
     * @param string $validators
     * @return string
     */
    public function _getStandardElement($columnName, $type, $var, $filters, $validators)
    {
        $body = '// Add ' . $columnName . ' element' . "\n"
            . '$this->addElement(\'' . $type . '\', \'' . $var . '\', array(' . "\n"
            . '    \'label\' => \'' . $this->_util->format($columnName, 4) . '\',' . "\n"
            . '    \'required\' => true,' . "\n"
            . '    \'filters\' => array(\'StringTrim\'' . $filters . '),' . "\n"
            . '    \'validators\' => array(' . "\n"
            . '        \'NotEmpty\'' . $validators . "\n"
            . '    ),' . "\n"
            . '    \'description\' => \'&nbsp;\',' . "\n"
            . '    \'class\' => \'ui-widget-content ui-corner-all ui-input ui-upper\',' . "\n"
            . '    \'decorators\' => array(' . "\n"
            . '        array(\'ViewScript\', array(\'viewScript\' => \'decorators/input-text.phtml\' ))' . "\n"
            . '    )' . "\n"
            . '));' . "\n\n";
        return $body;
    }

}

