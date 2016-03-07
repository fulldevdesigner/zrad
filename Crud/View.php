<?php
/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_Crud_View
    extends Zrad_Abstract
{
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
     * Create view list
     *
     * @param string $jscript
     */
    public function createList($jscript)
    {
        $path = $this->_util->getPathTemplate();
        //update view list
        $path .= DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'crud'
            . DIRECTORY_SEPARATOR . 'list.phtml';
        $phtml = file_get_contents($path);
        $phtml = str_replace('%JSCRIPT%', $jscript, $phtml);
        $path = 'application'
            . DIRECTORY_SEPARATOR . $this->_pathModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . $this->_util->format($this->_controllerName, 2)
            . DIRECTORY_SEPARATOR . 'list.phtml';
        file_put_contents($path, $phtml);
    }

    /**
     * Create view new
     *
     * @param string $jscript
     */
    public function createNew($jscript)
    {
        $viewFile = $this->_controllerName;

        $form = '';
        $i = 1;
        foreach ($this->_fields as $field) {
            $columnName = strtolower($field['COLUMN_NAME']);
            if (!in_array($columnName, $this->_noValidColumns)) {
                $columnName = $this->_util->format($columnName,1);
                if ($i == 1) {
                    $form .= '<?php echo $this->form->getElement(\'' . $columnName . '\')->render() ?>';
                } else {
                    $form .= $this->_indentation . $this->_indentation . $this->_indentation . $this->_indentation . $this->_indentation .
                        '<?php echo $this->form->getElement(\'' . $columnName . '\')->render() ?>';
                }
                $form .= "\n";
            }
            $i++;
        }
        $form = substr($form, 0, -1);

        $module = '';
        if ($this->_moduleName !== null)
            $module = '/' . $this->_moduleName;

        //phtml
        $path = $this->_util->getPathTemplate();
        $path .= DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'crud'
            . DIRECTORY_SEPARATOR . 'new.phtml';
        $phtml = file_get_contents($path);
        $phtml = str_replace('%JSCRIPT%', $jscript, $phtml);
        $phtml = str_replace('%MODULE%', $module, $phtml);
        $phtml = str_replace('%CONTROLLER%', $this->_util->format($this->_controllerName, 2), $phtml);
        $phtml = str_replace("%FORMCONTENT%", $form, $phtml);
        $phtml = str_replace("%FORMNAME%", 'form-' . $this->_tableName, $phtml);
        $dir = 'application'
            . DIRECTORY_SEPARATOR . $this->_pathModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . $this->_util->format($this->_controllerName, 2)
            . DIRECTORY_SEPARATOR . 'new.phtml';
        file_put_contents($dir, $phtml);
    }

     /**
     * Create view edit
     *
     * @param string $jscript
     */
    public function createEdit($jscript)
    {
        $viewFile = $this->_controllerName;

        $form = '';
        $i = 1;
        foreach ($this->_fields as $field) {
            $columnName = strtolower($field['COLUMN_NAME']);
            if (!in_array($columnName, $this->_noValidColumns)) {
                $columnName = $this->_util->format($columnName,1);
                if ($i == 1) {
                    $form .= '<?php echo $this->form->getElement(\'' . $columnName . '\')->render() ?>';
                } else {
                    $form .= $this->_indentation . $this->_indentation . $this->_indentation . $this->_indentation . $this->_indentation .
                        '<?php echo $this->form->getElement(\'' . $columnName . '\')->render() ?>';
                }
                $form .= "\n";
            }
            $i++;
        }
        $form = substr($form, 0, -1);

        $module = '';
        if ($this->_moduleName !== null)
            $module = '/' . $this->_moduleName;

        //phtml
        $path = $this->_util->getPathTemplate();
        $path .= DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'crud'
            . DIRECTORY_SEPARATOR . 'edit.phtml';
        $phtml = file_get_contents($path);
        $phtml = str_replace('%JSCRIPT%', $jscript, $phtml);
        $phtml = str_replace('%MODULE%', $module, $phtml);
        $phtml = str_replace('%CONTROLLER%', $this->_util->format($this->_controllerName, 2), $phtml);
        $phtml = str_replace("%FORMCONTENT%", $form, $phtml);
        $phtml = str_replace("%FORMNAME%", 'form-' . $this->_tableName, $phtml);
        $dir = 'application'
            . DIRECTORY_SEPARATOR . $this->_pathModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . $this->_util->format($this->_controllerName, 2)
            . DIRECTORY_SEPARATOR . 'entidad.phtml';
            //. DIRECTORY_SEPARATOR . 'edit.phtml';
        file_put_contents($dir, $phtml);
    }
}
