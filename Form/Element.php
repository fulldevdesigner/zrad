<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Zrad/Render.php';

/**
 * Description of Element
 *
 * @author Juan Minaya
 */
class Zrad_Form_Element extends Zrad_Render
{

    /**
     * @var array
     */
    private $_notStandard = array('file', 'image', 'select');

    /**
     * @var Zrad_Helper_Util
     */
    private $_util;

    /**
     * @var string
     */
    private $_entity;

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_util = new Zrad_Helper_Util();
    }

    public function generate($field, $attributes, $target)
    {
        $element = $attributes['element'];
        if (in_array($element, $this->_notStandard)) {
            return $this->_getNoStandardElement($field, $attributes, $target);
        } else {
            return $this->_getStandardElement($field, $attributes, $target);
        }
    }

    /**
     * @param string $field
     * @param array $attributes
     * @param string $target frontend|backend
     * @return string
     */
    private function _getStandardElement($field, $attributes, $target)
    {
        $body = '// Add ' . $this->_util->format($field, 4) . ' element' . "\n"
            . '$this->addElement(\'' . $attributes['element'] . '\', \'' . $this->_util->format($field, 1) . '\', array(' . "\n"
            . '    \'label\' => \'' . $this->_util->format($field, 4) . '\',' . "\n"
            . '    \'description\' => \'&nbsp;\',' . "\n";

        $nameField = $this->_util->format($field, 1);
        if ($nameField == 'publicado') {
            $body .= '    \'required\' => false,' . "\n";
        } else {
            $body .= '    \'required\' => true,' . "\n";
        }

        $body .= '    \'filters\' => array(' . "\n";

        $i = 1;
        foreach ($attributes['filters'] as $filter => $value) {
            $coma = (count($attributes['filters']) > $i) ? ',' : '';
            $body .= $this->setIndent(8)->getIndent() . '\'' . $filter . '\'' . $coma . "\n";
            $i++;
        }

        $body .=
            '    ),' . "\n"
            . '    \'validators\' => array(' . "\n";

        $i = 1;
        foreach ($attributes['validators'] as $validator => $value) {
            $coma = (count($attributes['validators']) > $i) ? ',' : '';
            switch ($validator) {
                case 'Alpha':
                    $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', false, array(\'allowWhiteSpace\' => true))' . $coma . "\n";
                    break;
                case 'Digits':
                    $body .= $this->setIndent(8)->getIndent() . '\'' . $validator . '\'' . $coma . "\n";
                    break;
                case 'StringLength':
                    if (isset($value['type'])) {
                        $type =  $value['type'];
                        switch ($type) {
                            case 'exact':
                                $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', false, array(' . $value['length'] . ',' . $value['length'] . '))' . $coma . "\n";
                                break;                            
                            case 'interval':
                                $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', false, array(' . $value['min'] . ',' . $value['length'] . '))' . $coma . "\n";
                                break;
                            default:
                                break;
                        }                                                
                    } else {
                        $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', false, array(3,' . $value['length'] . '))' . $coma . "\n";
                    }
                    break;
                case 'Alnum':
                    $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', false, array(\'allowWhiteSpace\' => true))' . $coma . "\n";
                    break;
                case 'Date':
                    $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', true, array(\'format\' => \'dd/mm/yyyy\'))' . $coma . "\n";
                    break;
                case 'Regex':
                    $body .= $this->setIndent(8)->getIndent() . 'array(\'' . $validator . '\', false, array(\'' . $value['exp'] . '\'))' . $coma . "\n";
                    break;
                case 'Uri':
                    $body .= $this->setIndent(8)->getIndent() . 'new Zrad_Validate_Uri()' . $coma . "\n";
                    break;
                default:
                    $body .= $this->setIndent(8)->getIndent() . '\'' . $validator . '\'' . $coma . "\n";
                    break;
            }
            $i++;
        }

        $body .= '    ),' . "\n";

        if (isset($attributes['attributes'])) {
            $i = 0;
            foreach ($attributes['attributes'] as $attribute => $value) {
                $coma = (count($attributes['attributes']) > $i) ? ',' : '';
                $body .= '    \'' . $attribute . '\' => ' . $value . $coma . "\n";
                $i++;
            }
        }

        if ($target == 'frontend') {
            $body .= '    \'decorators\' => array(' . "\n"
                . '        array(\'ViewHelper\', array(\'tag\' => null))' . "\n"
                . '    )' . "\n";
        } else {
            $body .= '    \'class\' => \'ui-widget-content ui-corner-all ui-input ' . $attributes['class'] . '\',' . "\n"
                . '    \'decorators\' => array(' . "\n"
                . '        array(\'ViewScript\', array(\'viewScript\' => \'decorators/' . $attributes['decorator'] . '\'))' . "\n"
                . '    )' . "\n";
        }
        $body .= '));' . "\n\n";

        return $body;
    }

    /**
     * @param string $field
     * @param array $attributes
     * @param string $target frontend|backend
     * @return string
     */
    private function _getNoStandardElement($field, $attributes, $target)
    {
        $newName = $this->_util->format($field, 1);
        switch ($attributes['element']) {
            case 'image':
                $body = '// Add ' . $this->_util->format($field, 4) . ' element' . "\n"
                    . '$' . $newName . ' = new ZradAid_Form_Element_File(\'' . $this->_util->format($field, 1) . '\');' . "\n"
                    . '$' . $newName . '->setLabel(\'' . $this->_util->format($field, 4) . '\');' . "\n"
                    . '$' . $newName . '->setRequired(true);' . "\n"
                    . '$' . $newName . '->setInitialization(array(' . "\n"
                    . $this->setIndent(4)->getIndent() . '\'type\' => \'image\',' . "\n"
                    . $this->setIndent(4)->getIndent() . '\'entity\' => \'' . $this->_util->format($this->_entity, 1) . '\',' . "\n"
                    . $this->setIndent(4)->getIndent() . '\'field\' => \'' . $this->_util->format($field, 1) . '\',' . "\n"
                    . '));' . "\n";

                // Creamos carpeta de la entidad
                $dir = getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $attributes['upload'];
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }

                // Creamos la carpeta images
                $dir = getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $attributes['upload'] . DIRECTORY_SEPARATOR . 'images';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }

                // Quitamos los decoradores si es frontend
                if ($target == 'frontend') {
                    $body .= '$' . $newName . '->setDecorators(array(\'File\', array()));' . "\n"
                        . '$this->addElement($' . $newName . ');' . "\n\n";
                } else {
                    $body .= '$' . $newName . '->setDecorators(array(\'File\', ' . "\n"
                        . $this->setIndent(4)->getIndent() . 'array(\'ViewScript\', array(\'viewScript\' => \'decorators/zrad-input-image.phtml\', \'placement\' => false))' . "\n"
                        . '));' . "\n"
                        . '$this->addElement($' . $newName . ');' . "\n\n";
                }

                // Actualizamos el archivo admin-form.ini   
                $this->_util->addInitForm($this->_util->format($this->_entity), $this->_util->format($field), 'image');

                break;
            case 'file':
                $body = '// Add ' . $this->_util->format($field, 4) . ' element' . "\n"
                    . '$' . $newName . ' = new ZradAid_Form_Element_File(\'' . $this->_util->format($field, 1) . '\');' . "\n"
                    . '$' . $newName . '->setLabel(\'' . $this->_util->format($field, 4) . '\');' . "\n"
                    . '$' . $newName . '->setRequired(true);' . "\n"
                    . '$' . $newName . '->setInitialization(array(' . "\n"
                    . $this->setIndent(4)->getIndent() . '\'type\' => \'file\',' . "\n"
                    . $this->setIndent(4)->getIndent() . '\'entity\' => \'' . $this->_util->format($this->_entity, 1) . '\',' . "\n"
                    . $this->setIndent(4)->getIndent() . '\'field\' => \'' . $this->_util->format($field, 1) . '\',' . "\n"
                    . '));' . "\n";

                // Creamos carpeta de la entidad
                $dir = getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $attributes['upload'];
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }

                // Creamos la carpeta images
                $dir = getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $attributes['upload'] . DIRECTORY_SEPARATOR . 'files';
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }

                if ($target == 'frontend') {
                    $body .= '$' . $newName . '->setDecorators(array(\'File\', array()));' . "\n"
                        . '$this->addElement($' . $newName . ');' . "\n\n";
                } else {
                    $body .= '$' . $newName . '->setDecorators(array(\'File\', ' . "\n"
                        . $this->setIndent(4)->getIndent() . 'array(\'ViewScript\', array(\'viewScript\' => \'decorators/zrad-input-file.phtml\', \'placement\' => false))' . "\n"
                        . '));' . "\n"
                        . '$this->addElement($' . $newName . ');' . "\n\n";
                }

                // Actualizamos el archivo admin-form.ini   
                $this->_util->addInitForm($this->_util->format($this->_entity, 1), $this->_util->format($field, 1), 'file');

                break;
            case 'select':
                $body = '';
                if (isset($attributes['relation']['table'])) {
                    $table = $attributes['relation']['table'];
                    $body = '// Populate ' . $this->_util->format($field, 4) . ' element' . "\n";
                    $body .= '$' . $this->_util->format($table, 1) . 'Mapper = new ' . $this->_util->format($this->_util->generateNameModule($table),6) . '_Model_' . ucfirst($this->_util->format($table, 1)) . 'Mapper();' . "\n"
                        . '$' . $this->_util->format($this->_util->generateNameModule($table),11) . ' = $' . $this->_util->format($table, 1) . 'Mapper->toSelect(true);' . "\n\n";
                }

                $body .= '// Add ' . $this->_util->format($field, 4) . ' element' . "\n"
                    . '$this->addElement(\'' . $attributes['element'] . '\', \'' . $this->_util->format($field, 1) . '\', array(' . "\n"
                    . '    \'label\' => \'' . $this->_util->format($field, 4) . '\',' . "\n"
                    . '    \'description\' => \'&nbsp;\',' . "\n"
                    . '    \'required\' => true,' . "\n"
                    . '    \'validators\' => array(' . "\n"
                    . $this->setIndent(8)->getIndent() . '\'Int\'' . "\n"
                    . '    ),' . "\n";

                if (isset($attributes['relation']['table'])) {
                    $body .= '    \'multiOptions\' => $' . $this->_util->pluralize($table, 'es') . ',' . "\n";
                } else {
                    $body .= '    \'multiOptions\' => array(' . "\n"
                        . $this->setIndent(8)->getIndent() . '0 => \'Selecciones\'' . "\n"
                        . $this->setIndent(4)->getIndent() . '),' . "\n";
                }

                if ($target == 'frontend') {
                    $body .= '    \'decorators\' => array(' . "\n"
                        . '        array(\'ViewHelper\', array(\'tag\' => null))' . "\n"
                        . '    )' . "\n"
                        . '));' . "\n\n";
                } else {
                    $body .= '    \'decorators\' => array(' . "\n"
                        . '        array(\'ViewScript\', array(\'viewScript\' => \'decorators/' . $attributes['decorator'] . '\' ))' . "\n"
                        . '    )' . "\n"
                        . '));' . "\n\n";
                }
                break;
        }
        return $body;
    }

}
