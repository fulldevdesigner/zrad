<?php

class Zrad_Helper_Util extends Zend_Tool_Project_Provider_Abstract
{

    /**
     * @param string $source
     * @param string $target
     */
    public function fullCopy($source, $target)
    {
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (false !== ( $entry = $d->read() )) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if ($entry != '.svn') {
                    $Entry = $source . '/' . $entry;
                    if (is_dir($Entry)) {
                        $this->fullCopy($Entry, $target . '/' . $entry);
                        continue;
                    }
                    copy($Entry, $target . '/' . $entry);
                }
            }
            $d->close();
        } else {
            copy($source, $target);
        }
    }

    public function output($messages)
    {
        foreach ($messages as $message) {
            echo 'zrad-> ' . $message . "\n";
        }
    }

    /**
     * @param String $field
     * @param Integer $case
     */
    public function format($field, $case = 1)
    {
        //$field = strtolower($field);
        $output = array();
        switch ($case) {
            case 1://input:producto_detalle , output: productoDetalle
                $fieldParts = explode('_', $field);
                if (count($fieldParts) > 1) {
                    $field = $fieldParts[0];
                    for ($i = 1; $i < count($fieldParts); $i++)
                        $field .= ucfirst($fieldParts[$i]);
                }
                break;
            case 2://input:productoDetalle , output: producto-detalle
                preg_match_all("/([A-Z]|[a-z])[a-z]+/", $field, $output);
                $output = $output[0];
                $controllerLink = '';
                foreach ($output as $result) {
                    $controllerLink .= strtolower($result) . '-';
                }
                $controllerLink = substr($controllerLink, 0, -1);
                $field = $controllerLink;
                break;
            case 3://input:productoDetalle , output: PRODUCTO DETALLE
                preg_match_all("/[A-Z][a-z]+/", $field, $output);
                $output = $output[0];
                $controllerTitle = '';
                foreach ($output as $result) {
                    $controllerTitle .= strtoupper($result) . ' ';
                }
                $controllerTitle = substr($controllerTitle, 0, -1);
                $field = $controllerTitle;
                break;
            case 4://input:producto_detalle , output: Producto Detalle
                $fieldParts = explode('_', $field);
                if (count($fieldParts) > 1) {
                    $field = ucfirst($fieldParts[0]);
                    for ($i = 1; $i < count($fieldParts); $i++)
                        if ($fieldParts[$i] != 'id') {
                            $field .= ' ' . ucfirst($fieldParts[$i]);
                        }
                } else {
                    $field = ucfirst($field);
                }
                break;
            case 5://input:productoDetalle , output: Producto Detalle
                preg_match_all("/[A-Z][a-z]+/", $field, $output);
                $output = $output[0];
                $label = '';
                foreach ($output as $result) {
                    $label .= ucfirst($result) . ' ';
                }
                $label = substr($label, 0, -1);
                $field = $label;
                break;
            case 6://input:producto-detalle , output: ProductoDetalle
                $fieldParts = explode('-', $field);
                if (count($fieldParts) > 1) {
                    $field = '';
                    for ($i = 0; $i < count($fieldParts); $i++)
                        $field .= ucfirst($fieldParts[$i]);
                } else {
                    $field = ucfirst($field);
                }
                break;
            case 7://input:producto_detalle , output: PRODUCTOS DETALLES
                /* $field = $this->pluralize($field, 'es');
                  preg_match_all("/[A-Z][a-z]+/", $field, $output);
                  $output = $output[0];
                  $controllerTitle = '';
                  foreach ($output as $result) {
                  $controllerTitle .= strtoupper($result) . ' ';
                  }
                  $controllerTitle = substr($controllerTitle, 0, -1);
                  $field = $controllerTitle; */                
                $parts = explode('_', $field);
                $field = '';
                foreach ($parts as $part) {
                    $field .= strtoupper($this->pluralize($part)) . ' ';
                }
                $field = trim($field);
                break;
            case 8://input:producto_detalle , output: ProductoDetalle
                $fieldParts = explode('_', $field);
                if (count($fieldParts) > 1) {
                    $field = '';
                    for ($i = 0; $i < count($fieldParts); $i++)
                        $field .= ucfirst($fieldParts[$i]);
                } else {
                    $field = ucfirst($field);
                }
                break;
            case 9://input:producto-detalle , output: Producto Detalle
                $fieldParts = explode('-', $field);
                if (count($fieldParts) > 1) {
                    $field = ucfirst($fieldParts[0]);
                    for ($i = 1; $i < count($fieldParts); $i++)
                        if ($fieldParts[$i] != 'id') {
                            $field .= ' ' . ucfirst($fieldParts[$i]);
                        }
                } else {
                    $field = ucfirst($field);
                }
                break;
            case 10://input:producto_detalle , output: producto-detalle
                $fieldParts = explode('_', $field);
                if (count($fieldParts) > 1) {
                    $field = '';
                    foreach ($fieldParts as $result) {
                        $field .= strtolower($result) . '-';
                    }
                    $field = substr($field, 0, -1);
                } else {
                    $field = strtolower($field);
                }
                break;
            case 11://input:producto-detalle , output: productoDetalle
                $fieldParts = explode('-', $field);
                if (count($fieldParts) > 1) {
                    $field = $fieldParts[0];
                    for ($i = 1; $i < count($fieldParts); $i++)
                        $field .= ucfirst($fieldParts[$i]);
                }
                break;
        }
        return $field;
    }

    public function formatFile($field)
    {
        $fieldParts = explode('_', $field);
        if (count($fieldParts) > 1) {
            $field = $fieldParts[0];
            for ($i = 1; $i < count($fieldParts); $i++) {
                $field .= '-' . $fieldParts[$i];
            }
        }
        return $field;
    }

    public function getPathTemplate()
    {
        $dir = dirname(__FILE__);
        $dir = str_replace('Helper', 'Template', $dir);
        return $dir;
    }

    public function pluralize($str, $lang = 'es')
    {
        $entidadesEn = array('post', 'blog', 'banner', 'tip', 'log', 'issue');

        if (!in_array($str, $entidadesEn)) {
            $rule1 = array('a', 'e', 'o');
            $rule2 = array('z');
            //obtenemos la ultima letra
            $last = substr($str, -1, 1);
            //terminacion a,e,o
            if (in_array($last, $rule1)) {
                return $str . 's';
            }
            //terminacion o
            if (in_array($last, $rule2)) {
                //eliminamos la ultima letra
                $str = substr($str, 0, strlen($str) - 1);
                return $str . 'ces';
            }
            return $str . 'es';
        } else {
            return $str . 's';
        }
    }

    public function generateNameModule($tableName)
    {
        $enModules = array('post', 'blog', 'banner');

        $moduleName = '';
        $tableName = strtolower($tableName);
        $pos = strpos($tableName, '_');
        if ($pos === false) {
            $moduleName = (!in_array($tableName, $enModules)) ? $this->pluralize($tableName) : $tableName . 's';
        } else {
            $tableName = str_replace('_', '-', $tableName);
            $parts = explode('-', $tableName);
            $nParts = count($parts);
            foreach ($parts as $key => $part) {
                $separator = ($key + 1 < $nParts) ? '-' : '';
                if (strlen($part) > 2) {
                    $part = $this->pluralize($part);
                    $moduleName .= $part . $separator;
                }
            }
            $pos = strpos($moduleName, '-');
            if ($pos === false) {
                $moduleName = $this->pluralize($moduleName);
            }
        }
        return $moduleName;
    }

    /**
     * formate el nombre de la tabla a un formato standar
     *
     * @param string $tableName , nombre original de la tabla
     * @return string
     * @example PP_PROMOCION_USUARIO -> promociones_usuario
     * @example PP_CLIENTE -> cliente
     */
    public function formatTableName($tableName)
    {
        $tableName = strtolower($tableName);
        $pos = strpos($tableName, '_');
        if ($pos) {
            $parts = explode('_', $tableName);
            $tableName = '';
            $nParts = count($parts);
            foreach ($parts as $key => $part) {
                $separator = ($key + 1 < $nParts) ? '_' : '';
                if (strlen($part) > 1)
                    $tableName .= $part . $separator;
            }
        }
        return $tableName;
    }

    public function deleteDirectory($dir)
    {
        if (!file_exists($dir))
            return true;
        if (!is_dir($dir) || is_link($dir))
            return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..')
                continue;
            if (!$this->deleteDirectory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!$this->deleteDirectory($dir . "/" . $item))
                    return false;
            };
        }
        return rmdir($dir);
    }

    /**
     * @return bool
     */
    public function isModular()
    {
        $response = false;
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $appConfigFileResource = $profile->search('ApplicationConfigFile');
        $appConfigFilePath = $appConfigFileResource->getPath();
        $config = new Zend_Config_Ini($appConfigFilePath, null, array('skipExtends' => true, 'allowModifications' => true));
        if (isset($config->{'production'}->resources->frontController->moduleDirectory))
            $response = true;
        return $response;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function existsModule($moduleName)
    {
        $response = false;
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        // find the actual modules directory we will use to house our module
        $modulesDirectory = $profile->search('modulesDirectory');
        // if there is a module directory already, except
        if ($modulesDirectory->search(array('moduleDirectory' => array('moduleName' => $moduleName)))) {
            $response = true;
        }
        return $response;
    }

    /**
     * 
     * @param string $formName
     * @return bool
     */
    public function existsForm($formName, $moduleName = null)
    {
        $response = false;
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        // find the actual modules directory we will use to house our module
        if (Zend_Tool_Project_Provider_Form::hasResource($profile, $formName, $moduleName)) {
            $response = true;
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function existsModel()
    {
        $modelsDirectory = Zend_Tool_Project_Provider_Model::_getModelsDirectoryResource($profile, $moduleName);
        return (($modelsDirectory->search(array('modelFile' => array('modelName' => $modelName)))) instanceof Zend_Tool_Project_Profile_Resource);
    }

    public function isEnabled($tableName)
    {
        $result = true;
        $config = new Zend_Config_Ini(
                'application' . DIRECTORY_SEPARATOR .
                'configs' . DIRECTORY_SEPARATOR . 'zrad.ini');
        if (isset($config->table->$tableName)) {
            $create = $config->table->$tableName->create;
            if ($create == 0) {
                $result = false;
            }
        }
        return $result;
    }

    public function createRelationsTables()
    {
        $result = true;
        $config = new Zend_Config_Ini(
                'application' . DIRECTORY_SEPARATOR .
                'configs' . DIRECTORY_SEPARATOR . 'zrad.ini');
        if (isset($config->table->relations)) {
            $create = (int) $config->table->relations->create;
            if ($create == 0) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Convierte la primera letra en minuscula
     * 
     * @param string $str
     * @return string
     */
    function lcfirst($str)
    {
        return (string) (strtolower(substr($str, 0, 1)) . substr($str, 1));
    }

    /**
     * @param string $entity nombre de la BD
     * @param string $field nombre del campo
     * @param string $section 
     */
    public function addInitForm($entity, $field, $section)
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $applicationConfigResource = $profile->search('ApplicationConfigForm');
        $appConfigFilePath = $applicationConfigResource->getPath();
        $config = new Zend_Config_Ini($appConfigFilePath, null, array('skipExtends' => true, 'allowModifications' => true));
        $object = $entity . '.' . $field . '.';

        switch ($section) {
            case 'image':
                if (!isset($config->{'images'}->thumb->$entity->$field)) {
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'rename.extension', '"jpg"', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'rename.prefix', '""', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'resize.type', '"auto"', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'resize.width', '800', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'resize.height', '600', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'validator.minWidth', '50', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'validator.minHeight', '50', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'validator.minWeight', '10', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'validator.maxWeight', '512000', 'images', false);
                    $applicationConfigResource->addStringItem('thumb.' . $object . 'validator.extension', '"jpg,png,gif"', 'images', false);
                    $applicationConfigResource->create();
                }
                break;
            case 'file':
                if (!isset($config->{'files'}->file->$entity->$field)) {
                    $applicationConfigResource->addStringItem('file.' . $object . 'rename.prefix', '0', 'files', false);
                    $applicationConfigResource->addStringItem('file.' . $object . 'validator.minWeight', '10', 'files', false);
                    $applicationConfigResource->addStringItem('file.' . $object . 'validator.maxWeight', '512000', 'files', false);
                    $applicationConfigResource->addStringItem('file.' . $object . 'validator.extension', '"pdf"', 'files', false);
                    $applicationConfigResource->create();
                }
                break;
            default:
                break;
        }
    }

}
