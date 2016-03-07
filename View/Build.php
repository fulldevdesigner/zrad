<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_View_Build extends Zrad_Abstract
{

    /**
     * string
     */
    private $_jscript = '';

    /**
     * string
     */
    private $_formName = '';

    /**
     * boolean
     */
    private $_captcha = false;

    /**
     * @param string $jscript
     */
    public function setJscript($jscript)
    {
        $this->_jscript = $jscript;
    }

    /**
     * @param string $formName
     */
    public function setFormName($formName)
    {
        $this->_formName = $formName;
    }

    /**
     * @param boolean $captcha
     */
    public function setCaptcha($captcha)
    {
        $this->_captcha = $captcha;
    }

    protected function _init()
    {
        $this->_fileName = ucfirst($this->_util->format(strtolower($this->_tableName), 1));
    }

    /**
     * Create Form
     * @param string $action define la accion editar o crear
     */
    public function createForm($action = null)
    {
        $phtml = '';
        $idRow = '';

        if ($action == 'edit' && $this->_target == 'backend') {
            $phtml .= $this->setIndent(12)->getIndent() . '<input type="hidden" name="id" value="<?php echo $this->id ?>" />' . "\n";
            $idRow = 'id/<?php echo $this->id ?>';
        }

        $i = 1;
        
        if (isset($this->_config['isFacebook'])) {
            // Removemos es_activo y paso de tabla participante
            unset($this->_mapper['fields']['es_activo']);
            //unset($this->_mapper['fields']['fecha_nacimiento']);
            unset($this->_mapper['fields']['paso']);
            unset($this->_mapper['fields']['edad']);
        }
        
        foreach ($this->_mapper['fields'] as $field => $atributos) {
            $salto = (count($this->_mapper['fields']) > $i) ? "\n" : '';
            $field = $this->_util->format($field, 1);

            if ($this->_target == 'frontend') {
                $phtml .= $this->setIndent(8)->getIndent() . '<div class="zrad-ui-form-element">' . "\n"
                    . $this->setIndent(10)->getIndent() . '<label class="zrad-label"><em><?php if ($this->form->getElement(\'' . $field . '\')->isRequired()): ?>*<?php endif ?></em> <?php echo $this->form->getElement(\'' . $field . '\')->getLabel() ?>' . "\n"
                    . $this->setIndent(12)->getIndent() . '<span class="zrad-label-small"><?php echo $this->form->getElement(\'' . $field . '\')->getDescription() ?></span>' . "\n"
                    . $this->setIndent(10)->getIndent() . '</label>' . "\n"
                    . $this->setIndent(10)->getIndent() . '<div class="zrad-input">' . "\n"
                    . $this->setIndent(12)->getIndent() . '<?php echo $this->form->getElement(\'' . $field . '\') ?>' . "\n"
                    . $this->setIndent(12)->getIndent() . '<?php if ($this->form->getElement(\'' . $field . '\')->hasErrors()): ?>' . "\n"
                    . $this->setIndent(12)->getIndent() . '<label class="zrad-error error" for="' . $field . '"><?php echo current($this->form->getElement(\'' . $field . '\')->getMessages()) ?></label>' . "\n"
                    . $this->setIndent(12)->getIndent() . '<?php else: ?>' . "\n"
                    . $this->setIndent(12)->getIndent() . '<label class="zrad-error error" for="' . $field . '">&nbsp;</label>' . "\n"
                    . $this->setIndent(12)->getIndent() . '<?php endif ?>' . "\n"
                    . $this->setIndent(10)->getIndent() . '</div>' . "\n"
                    . $this->setIndent(8)->getIndent() . '</div>' . $salto;
            } else {
                $columnName = $this->_util->format($field, 1);
                $phtml .= $this->setIndent(12)->getIndent() . '<?php echo $this->form->getElement(\'' . $columnName . '\')->render() ?>' . $salto;
            }
            $i++;
        }

        $path = $this->_util->getPathTemplate();
        $path .= DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'crud' . DIRECTORY_SEPARATOR;
        if ($this->_target == 'frontend') {
            $path .= (isset($this->_config['captcha']) && $this->_config['captcha']) ? 'form-captcha-frontend.phtml' : 'form-frontend.phtml';
        } else {
            $path .= (isset($this->_config['captcha']) && $this->_config['captcha']) ? 'form-captcha-backend.phtml' : 'form-backend.phtml';
        }        

        $idForm = 'form' . $this->_util->format($this->_mapper['table'], 1);
        // La funcion lcfirst solo esta disponible para la version 5.3.0 o superior
        $controller = $this->_util->format($this->_util->lcfirst($this->_controllerName), 2);

        if ($action === null) {
            if (empty($this->_actionName))
                throw new Exception('No se puede crear una vista sin definir un accion');
            $action = $this->_util->format($this->_actionName, 2);
        }
        
        $view = file_get_contents($path);
        $view = str_replace("%FORMJSCRIPT%", $this->_jscript, $view);
        $view = str_replace("%FORMCONTENT%", $phtml, $view);
        $view = str_replace("%IDFORM%", $idForm, $view);
        $view = str_replace("%IDROW%", $idRow, $view);
        $view = str_replace("%MODULE%", $this->_moduleName, $view);
        $view = str_replace("%CONTROLLER%", $controller, $view);
        $view = str_replace('%ACTION%', $action, $view);
        
        if (isset($this->_config['isFacebook'])) {
            $view = str_replace('%TYPEBTN%', 'button', $view);
        } else {
            $view = str_replace('%TYPEBTN%', 'submit', $view);
        }

        $path = 'application'
            . DIRECTORY_SEPARATOR . $this->_pathModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . $controller;

        if ($this->_target == 'backend') {
            $path .= DIRECTORY_SEPARATOR . 'entidad.phtml';
        } else {
            $path .= DIRECTORY_SEPARATOR . $action . '.phtml';
        }

        file_put_contents($path, $view);
    }

    public function createList()
    {
        // La funcion lcfirst solo esta disponible para la version 5.3.0 o superior
        $controller = $this->_util->format($this->_util->lcfirst($this->_controllerName), 2);
        $path = $this->_util->getPathTemplate();
        $path .= DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'crud'
            . DIRECTORY_SEPARATOR . 'list.phtml';        
        $view = str_replace('%FORMJSCRIPT%', $this->_jscript, file_get_contents($path));
        $path = 'application'
            . DIRECTORY_SEPARATOR . $this->_pathModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . $controller
            . DIRECTORY_SEPARATOR . 'list.phtml';
        file_put_contents($path, $view);
    }
    
    public function createListRaffle()
    {        
        // La funcion lcfirst solo esta disponible para la version 5.3.0 o superior
        $controller = $this->_util->format($this->_util->lcfirst($this->_controllerName), 2);
        $path = $this->_util->getPathTemplate();
        $path .= DIRECTORY_SEPARATOR . 'Views'
            . DIRECTORY_SEPARATOR . 'crud'
            . DIRECTORY_SEPARATOR . 'list-sorteo.phtml';        
        $view = str_replace('JSCRIPTLIST', $this->_jscript, file_get_contents($path));
        $path = 'application'
            . DIRECTORY_SEPARATOR . $this->_pathModule . 'views'
            . DIRECTORY_SEPARATOR . 'scripts'
            . DIRECTORY_SEPARATOR . $controller
            . DIRECTORY_SEPARATOR . 'index.phtml';
        file_put_contents($path, $view);        
    }

}

