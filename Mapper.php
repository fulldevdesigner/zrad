<?php

/**
 * @see Zrad_Helper_Db
 */
require_once 'Zrad/Model.php';

/**
 * @see
 */
require_once 'Zrad/Db.php';

class Zrad_Mapper
{

    /**
     * @var Util
     */
    private $_util = null;

    /**
     * @var Util
     */
    private $_model = null;

    /**
     * Fields
     * @var array
     */
    private $_fields = null;

    /**
     * Fields Model
     * @var array
     */
    private $_fieldsModel = null;

    /**
     * Relation Fields
     */
    private $_relationTables = array();

    /**
     * Table Name
     * @var string
     */
    private $_tableName = null;

    /**
     * Resultado final
     * @var array
     */
    private $_result = array();

    /**
     * @var array
     */
    private $_noValidColumns = array(
        'id',
        'fb_uid',
        'created',
        'modified',
        'ip'
    );

    /**
     * construct
     */
    public function __construct($tableName)
    {
        $this->_util = new Zrad_Helper_Util();
        $this->_model = new Zrad_Db_Model();
        $this->_tableName = $tableName;
        $this->_fields = $this->_model->describeTable($tableName);
        $this->_result['table'] = $this->_tableName;
    }

    /**
     * inicia el proceso de mapeado
     */
    public function run()
    {
        // Verificamos si es necesario verificar las claves foraneas            
        $config = new Zend_Config_Ini(
                'application' . DIRECTORY_SEPARATOR .
                'configs' . DIRECTORY_SEPARATOR . 'zrad.ini');
        $relation = (isset($config->table->relations->create) && $config->table->relations->create == 1) ? true : false;

        if ($relation) {
            //verificamos las claves foraneas
            $this->_processForeignKeys();
        }
        //mapeamos el controlador
        $this->_result['controller'] = $this->_processController();
        $this->_result['model'] = $this->_processModel();
        $this->_result['fields'] = $this->_processFields();
        $this->_result['fieldsModel'] = $this->_fieldsModel;
        $this->_result['grid'] = $this->_processColumnsGrid();
        $this->_result['relationships'] = $this->_model->getRelationships($this->_tableName);
        //eliminamos el campo grid
        unset($this->_result['fields']['grid']);
    }

    /**
     * @param string $option
     * @return array
     */
    public function getResult($option = '')
    {
        $result = array();
        switch ($option) {
            case 'relationships':
                $result = $this->_result['relationships'];
                break;
            default:
                $result = $this->_result;
                break;
        }
        return $result;
    }

    /**
     *
     */
    public function getResultController()
    {
        
    }

    /**
     * @return array
     */
    public function getResultFields()
    {
        return $this->_result['fields'];
    }

    /**
     * procesa las claves foraneas
     */
    private function _processForeignKeys()
    {
        $relationTables = $this->_model->getForeignKeys($this->_tableName);

        if (count($relationTables) > 0) {

            $this->_relationTables = $relationTables;
            $tables = Zrad_Db::getInstance()->getAdapter()->listTables();
            $error = false;

            foreach ($relationTables['name'] as $table) {
                if (!in_array($table, $tables)) {
                    $message = array('La tabla "' . $table . '" no existe en la bd "' . $this->_model->getDb()->getDbName() . '"');
                    $this->_util->output($message);
                    $error = true;
                    break;
                }
            }

            if ($error) {
                throw new Exception('Error al encontrar tablas relacionadas');
            }
        }
    }

    /**
     * procesa el tipo de controlador
     * @return array
     */
    private function _processController()
    {
        $type = array();
        //$fieldsController = array();
        foreach ($this->_fields as $field) {
            $columnName = strtolower($field['COLUMN_NAME']);
            //verificamos si es tipo imagen
            if ($this->_isImage($columnName)) {
                $type['image'][] = $columnName;
            }
            //verificamos si es tipo youtube
            if ($this->_isYoutube($columnName)) {
                $type['youtube'][] = $columnName;
            }
            //verificamos si es tipo file
            if ($this->_isFile($columnName)) {
                $type['file'][] = $columnName;
            }
        }

        if (empty($type))
            $type['standard'] = true;

        return array('type' => $type);
    }

    /**
     * @return array
     */
    private function _processModel()
    {
        $model = array();

        //toSelect
        $validOptions = array('nombre', 'titulo', 'nombres', 'apellidos', 'name');
        $option = '';
        foreach ($this->_fields as $field) {
            $columnName = strtolower($field['COLUMN_NAME']);
            if (in_array($columnName, $validOptions)) {
                $option = $columnName;
                break;
            }
        }

        if (!empty($option)) {

            foreach ($this->_fields as $field) {
                if ($field['PRIMARY']) {
                    $value = strtolower($field['COLUMN_NAME']);
                    break;
                }
            }

            $model['method']['toSelect']['option'] = $option;
            $model['method']['toSelect']['value'] = $value;
        }
        //end toSelect

        return $model;
    }

    /**
     * verifica si el campo es de tipo nombre
     * @return bool
     */
    private function _isTitulo($columnName)
    {
        /**
         * @uses nombres|image para archivos de tipo foto
         * @uses foto|photo para archivos de tipo foto
         * @uses fotografia|photograpy para archivos de tipo foto
         * @var array
         */
        $checkMeIn = array('titulo');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * verifica si el campo es de tipo email
     * @return bool
     */
    private function _isEmail($columnName)
    {
        /**
         * @uses email|correo para archivos de tipo email
         * @var array
         */
        $checkMeIn = array('email', 'correo');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * verifica si el campo es de tipo nombre
     * @return bool
     */
    private function _isNombres($columnName)
    {
        /**
         * @uses nombres|image para archivos de tipo foto
         * @uses foto|photo para archivos de tipo foto
         * @uses fotografia|photograpy para archivos de tipo foto
         * @var array
         */
        $checkMeIn = array('nombres', 'nombre', 'apellido');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * verifica si el campo es de tipo imagen
     * @return bool
     */
    private function _isImage($columnName)
    {
        /**
         * @uses imagen|image para archivos de tipo foto
         * @uses foto|photo para archivos de tipo foto
         * @uses fotografia|photograpy para archivos de tipo foto
         * @var array
         */
        $checkMeIn = array('imagen', 'foto', 'fotografia');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * verifica si el campo es de tipo youtube
     * @return bool
     */
    private function _isYoutube($columnName)
    {
        $checkMeIn = array('youtube', 'url', 'link');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * Verifica si el campo es de tipo url
     *
     * @param string $columnName
     * @return bool
     */
    private function _isURL($columnName)
    {
        $checkMeIn = array('youtube', 'url', 'link');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * verifica si el campo es de tipo archivo
     * @return bool
     */
    private function _isFile($columnName)
    {
        /**
         * @uses pdf|pdf para archivos con extension pdf
         * @uses documento|document para archivos de word
         * @uses excel|excel para archivos tipo excel
         * @uses archivo|file para los demas tipos (rar,zip,etc.)
         * @var array
         */
        $checkMeIn = array('pdf', 'documento', 'excel', 'archivo');

        foreach ($checkMeIn as $findme) {
            $pos = strpos($columnName, $findme);
            if ($pos !== false) {
                break;
            }
        }

        if ($pos === false)
            return false;
        return true;
    }

    /**
     * verifica si es un campo con clave foranea
     * @return bool
     */
    private function _isForeignKey($name)
    {
        if (count($this->_relationTables) > 0) {
            if (in_array($name, $this->_relationTables['field'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * retorna la tabla relaciona
     * @return string
     */
    private function _getRelationTable($name)
    {

        if (count($this->_relationTables) > 0) {
            $clave = array_search($name, $this->_relationTables['field']);
            if ($clave !== false) {
                return $this->_relationTables['name'][$clave];
            }
        }
        return '';
    }

    public function test()
    {
        $this->_processFields();
    }

    private function _processColumnsGrid()
    {

        // Set width del contenedor , colocar 100 si es en porcentaje
        // El contenedor es de 1000px de ancho menos 20px para el scroll
        // Menos 40 para los botones edicion y eliminacion y 20 del index
        $width = 980 - 40 - 20 - 20 - 10;

        $grid = array();
        $gridImg = array();
        $checkMeNotIn = array(
            'detalle',
            'descripcion',
            'excel',
            'archivo',
            'direccion',
            'username',
            'password',
            'usuario',
            'clave',
            'text',
            'video',
            'archivo',
            'image'
        );

        // Procesamos las imagenes
        $nImages = 0;
        foreach ($this->_result['fields']['grid'] as $field => $atributos) {
            if (isset($atributos['type']) && $atributos['type'] == 'image') {
                $gridImg['colnames'][] = $atributos['colnames'];
                $gridImg['fieldnames'][] = $field;
                $gridImg['colmodels'][$field]['name'] = $field;
                $gridImg['colmodels'][$field]['width'] = $atributos['width'];
                $gridImg['colmodels'][$field]['type'] = $atributos['type'];
                $width -= $atributos['width'];
                $nImages++;
            }
        }

        $nColumnsLimit = 6 - $nImages;

        // Procesamos los demas campos
        $i = 1;
        $nColValid = 0;
        $endField = '';
        foreach ($this->_result['fields']['grid'] as $field => $atributos) {
            if (isset($atributos['type']) && !in_array($atributos['type'], $checkMeNotIn) && !in_array($atributos['type'], $this->_noValidColumns) && $i < $nColumnsLimit) {
                $grid['colnames'][] = $atributos['colnames'];
                $grid['fieldnames'][] = $field;
                $grid['colmodels'][$field]['name'] = $field;
                $grid['colmodels'][$field]['width'] = $atributos['width'];
                $grid['colmodels'][$field]['type'] = $atributos['type'];
                $grid['colmodels'][$field]['index'] = $i - 1;
                $endField = $field;
                if (isset($atributos['width']) && $atributos['width'] !== null) {
                    $width -= $atributos['width'];
                    $nColValid++;
                }
                $i++;
            }
        }

        if ($width > 0) {
            $nColumns = count($grid['colmodels']) - $nColValid;
            if ($nColumns > 0) {
                $widthStand = floor($width / $nColumns);
                foreach ($grid['colmodels'] as $field => $atributos) {
                    if ($atributos['width'] == null) {
                        $grid['colmodels'][$field]['width'] = $widthStand;
                        $width -= $widthStand;
                    }
                }
            }
        }

        if ($width > 0) {
            $grid['colmodels'][$endField]['width'] += $width;
        }

        // Indeces de las imagenes
        if (isset($gridImg['colnames'])) {
            $initIndex = $i - 1;
            foreach ($gridImg['colmodels'] as $key => $value) {
                $gridImg['colmodels'][$key]['index'] = $initIndex;
                $initIndex++;
            }
        }

        // Unimos los resultados
        if (isset($gridImg['colnames'])) {
            $grid['colnames'] = array_merge($grid['colnames'], $gridImg['colnames']);
        }

        if (isset($gridImg['fieldnames'])) {
            $grid['fieldnames'] = array_merge($grid['fieldnames'], $gridImg['fieldnames']);
        }

        if (isset($gridImg['colmodels'])) {
            $grid['colmodels'] = array_merge($grid['colmodels'], $gridImg['colmodels']);
        }
        return $grid;
    }

    private function _processFields()
    {
        $fields = array();
        $fieldsModel = array();
        // Grid                
        $tieneId = false;
        foreach ($this->_fields as $field) {
            $name = strtolower($field['COLUMN_NAME']);
            $type = $field['DATA_TYPE'];

            $fieldsModel[$name]['type'] = $type;

            if ($name == 'id') {
                $tieneId = true;
            }

            if (!in_array($name, $this->_noValidColumns)) {

                // Set colnames
                $fields['grid'][$name]['colnames'] = $this->_util->format(strtoupper($name), 4);

                if ((count($this->_relationTables) > 0) && in_array($name, $this->_relationTables['field'])) {
                    $key = array_search($name, $this->_relationTables['field']);
                    $fields[$name]['relation']['table'] = $this->_relationTables['name'][$key];
                    $fields[$name]['relation']['value'] = 'titulo';
                }

                $fields[$name]['length'] = $field['LENGTH'];
                $fields[$name]['type'] = $type;
                $fields[$name]['filters']['StringTrim'] = true;
                $fields[$name]['validators']['NotEmpty'] = true;
                $fields[$name]['validators']['StringLength']['length'] = $field['LENGTH'];
                //type
                switch ($type) {
                    case 'varchar':
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-text';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['Alpha'] = true;
                        $fields['grid'][$name]['type'] = $name;
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'char':
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-text';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['Alpha'] = true;
                        $fields['grid'][$name]['type'] = $name;
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'text':
                        $fields[$name]['element'] = 'textarea';
                        $fields[$name]['class'] = 'ui-zrad-input-textarea';
                        $fields[$name]['decorator'] = 'zrad-input-textarea.phtml';
                        //$fields[$name]['validators']['Alnum'] = true;
                        unset($fields[$name]['validators']['StringLength']);
                        $fields[$name]['attributes']['cols'] = 30;
                        $fields[$name]['attributes']['rows'] = 3;
                        $fields['grid'][$name]['type'] = 'text';
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'int':
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-number';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['Digits'] = true;
                        $fields['grid'][$name]['type'] = $name;
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'bigint':
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-number';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['Digits'] = true;
                        $fields['grid'][$name]['type'] = $name;
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'tinyint':
                        unset($fields[$name]['validators']['StringLength']);
                        $fields[$name]['element'] = 'checkbox';
                        $fields[$name]['attributes']['style'] = '\'width:25px\'';
                        $fields[$name]['class'] = '';
                        $fields[$name]['decorator'] = 'zrad-input-checkbox.phtml';
                        $fields[$name]['validators']['Digits'] = true;
                        $fields['grid'][$name]['type'] = 'tinyint';
                        $fields['grid'][$name]['width'] = 80;
                        break;
                    case 'smallint':
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['attributes']['style'] = '\'width:25px\'';
                        $fields[$name]['class'] = '';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['Digits'] = true;
                        $fields['grid'][$name]['type'] = $name;
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'date':
                        //unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['length'] = 10;
                        $fields[$name]['validators']['StringLength']['length'] = 10;
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-text';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['StringLength']['type'] = 'exact';
                        $fields[$name]['validators']['Date']['format'] = 'dd/mm/yyyy';
                        $fields[$name]['attributes']['maxlength'] = 10;
                        $fields['grid'][$name]['type'] = 'date';
                        $fields['grid'][$name]['width'] = 80;
                        break;
                    case 'datetime':
                        //unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['length'] = 10;
                        $fields[$name]['validators']['StringLength']['length'] = 10;
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-text';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields[$name]['validators']['StringLength']['type'] = 'exact';
                        $fields[$name]['validators']['Date']['format'] = 'dd/mm/yyyy';
                        $fields[$name]['attributes']['maxlength'] = 10;
                        $fields['grid'][$name]['type'] = 'date';
                        $fields['grid'][$name]['width'] = 80;
                        break;
                    default :
                        $fields[$name]['element'] = 'text';
                        $fields[$name]['class'] = 'ui-zrad-input-text';
                        $fields[$name]['decorator'] = 'zrad-input-text.phtml';
                        $fields['grid'][$name]['type'] = $name;
                        $fields['grid'][$name]['width'] = 20;
                        break;
                }

                //name
                switch ($name) {
                    case 'telefono':
                        unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['validators']['Alnum'] = true;
                        $fields[$name]['validators']['StringLength']['type'] = 'interval';
                        $fields[$name]['validators']['StringLength']['min'] = 7;
                        break;
                    case 'celular':
                        unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['validators']['Alnum'] = true;
                        $fields[$name]['validators']['StringLength']['type'] = 'interval';
                        $fields[$name]['validators']['StringLength']['min'] = 9;
                        break;
                    case 'direccion':
                        unset($fields[$name]['validators']['Alpha']);
                        //$fields[$name]['validators']['Alnum'] = true;                        
                        break;
                    case 'dni':
                        unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['validators']['Digits'] = true;
                        $fields[$name]['validators']['StringLength']['type'] = 'exact';
                        $fields[$name]['attributes']['maxlength'] = 8;
                        $fields['grid'][$name]['type'] = 'dni';
                        $fields['grid'][$name]['width'] = 80;
                        break;
                    case 'ruc':
                        unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['validators']['Digits'] = true;
                        $fields[$name]['validators']['StringLength']['type'] = 'exact';
                        $fields[$name]['attributes']['maxlength'] = 11;
                        $fields['grid'][$name]['type'] = 'ruc';
                        $fields['grid'][$name]['width'] = 100;
                        break;
                    case 'username':
                        unset($fields[$name]['validators']['Alpha']);
                        $fields[$name]['validators']['Alnum'] = true;
                        $fields[$name]['validators']['Regex']['exp'] = "/^[a-z][a-z0-9]{2,}$/";
                        $fields[$name]['filters']['StringToLower'] = true;
                        $fields['grid'][$name]['type'] = 'username';
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'password':
                        $fields[$name]['element'] = 'password';
                        $fields[$name]['attributes']['maxlength'] = 8;
                        $fields['grid'][$name]['type'] = 'password';
                        $fields['grid'][$name]['width'] = null;
                        break;
                    case 'sexo':                        
                        $fields[$name]['validators']['StringLength']['type'] = 'exact';
                        $fields[$name]['attributes']['maxlength'] = 1;
                        $fields['grid'][$name]['width'] = 10;
                        break;
                }

                // Verificamos si es de tipo email
                if ($this->_isEmail($name)) {
                    unset($fields[$name]['validators']['Alpha']);
                    $fields[$name]['validators']['EmailAddress'] = true;
                    $fields['grid'][$name]['type'] = 'email';
                    $fields['grid'][$name]['width'] = null;
                    $fields[$name]['type'] = 'email';
                }

                //verificando si son nombres
                if ($this->_isTitulo($name)) {
                    unset($fields[$name]['validators']['Alpha']);
                    $fields[$name]['validators']['Alnum']['allowWhiteSpace'] = true;
                    $fields['grid'][$name]['type'] = 'titulo';
                    $fields['grid'][$name]['width'] = null;
                }

                //verificando si son titulos
                if ($this->_isNombres($name)) {
                    unset($fields[$name]['validators']['Alpha']);
                    $fields[$name]['validators']['Alpha']['allowWhiteSpace'] = true;
                    $fields['grid'][$name]['type'] = 'nombres';
                    $fields['grid'][$name]['width'] = null;
                }

                //verificando si es imagen
                if ($this->_isImage($name)) {
                    $fields[$name]['validators']['Image']['Size'] = true;
                    $fields[$name]['validators']['Image']['Extension'] = true;
                    $fields[$name]['validators']['Image']['ImageSize'] = true;
                    $fields[$name]['decorator'] = 'zrad-input-image.phtml';
                    unset($fields[$name]['validators']['StringLength']);
                    unset($fields[$name]['validators']['Alpha']);
                    unset($fields[$name]['class']);
                    $fields[$name]['element'] = 'image';
                    $fields[$name]['type'] = 'image';
                    $fields[$name]['upload'] = $this->_util->format($this->_tableName, 10);
                    $fields[$name]['filters']['Rename'] = true;
                    $fields['grid'][$name]['type'] = 'image';
                    $fields['grid'][$name]['width'] = 118;
                }
                //verificando si es file
                if ($this->_isFile($name)) {
                    $fields[$name]['validators']['File']['Size'] = true;
                    $fields[$name]['validators']['File']['Extension'] = true;
                    $fields[$name]['decorator'] = 'zrad-input-file.phtml';
                    unset($fields[$name]['validators']['StringLength']);
                    unset($fields[$name]['validators']['Alpha']);
                    unset($fields[$name]['class']);
                    $fields[$name]['element'] = 'file';
                    $fields[$name]['type'] = 'file';
                    $fields[$name]['upload'] = $this->_util->format($this->_tableName, 10);
                    $fields[$name]['filters']['Rename'] = true;
                    $fields['grid'][$name]['type'] = 'archivo';
                    $fields['grid'][$name]['width'] = null;
                }
                // Verificando si es youtube
                /* if ($this->_isYoutube($name)) {
                  unset($fields[$name]['validators']['Alpha']);
                  $fields[$name]['validators']['Uri'] = true;
                  $fields['grid'][$name]['type'] = 'video';
                  $fields['grid'][$name]['width'] = null;
                  } */
                // Verificamos si es URL
                if ($this->_isURL($name)) {
                    $fields[$name]['validators']['Uri'] = true;
                    unset($fields[$name]['validators']['Alpha']);
                    $fields['grid'][$name]['width'] = null;
                }
                //verificando si es clave foranea
                if ($this->_isForeignKey($name)) {
                    unset($fields[$name]['validators']['StringLength']);
                    unset($fields[$name]['class']);
                    $fields[$name]['decorator'] = 'zrad-input-select.phtml';
                    $fields[$name]['element'] = 'select';
                    $fields[$name]['relationTable'] = $this->_getRelationTable($name);
                    $lenght = strlen($name) - 3;
                    $sname = substr($name, 0, $lenght);
                    $fields['grid'][$name]['colnames'] = $this->_util->format(strtoupper($sname), 4);
                    $fields['grid'][$name]['type'] = 'foreign';
                    $fields['grid'][$name]['width'] = null;
                }
            }
        }

        if ($tieneId) {
            $fields['grid']['id']['colnames'] = 'ID';
            $fields['grid']['id']['type'] = 'int';
            $fields['grid']['id']['width'] = 20;
        }


        $this->_fieldsModel = $fieldsModel;
        return $fields;
    }

}