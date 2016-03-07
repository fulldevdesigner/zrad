<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_Model_Mapper extends Zrad_Abstract
{
    /**
     * @var int
     */

    const MAX_COLUMN = 7;

    /**
     * @var array
     */
    private $_defaultMethods = array(
        'setDbTable',
        'getDbTable',
        'save',
        'find',
        'fetchAll',
        'toSelect',
        'delete',
        'pagination',
        'addFilter',
        '_processFilters',
        'getMedia'
    );

    /**
     * @var array
     */
    private $_basicMethods = array(
        'setDbTable',
        'getDbTable',
        'save',
        'find',
        'fetchAll',
        'delete',
    );

    /**
     * @var bool
     */
    private $_isReflection = false;

    /**
     * @var string
     */
    private $_dbTable = '';

    /**
     * Init
     */
    protected function _init()
    {
        $this->_fileName = ucfirst($this->_util->format(strtolower($this->_tableName), 1));
        $this->_dbTable = $this->_fileName;
        $this->_fileName .= 'Mapper';
    }

    /**
     * @param boolean $isReflection
     */
    public function setIsReflection($isReflection)
    {
        $this->_isReflection = (bool) $isReflection;
    }

    /**
     * @return boolean
     */
    public function getIsReflection()
    {
        return $this->_isReflection;
    }

    /**
     * Obtenemos los metodos no crud de la clase
     *
     * @param string $path
     * @return array methods instace for Zend_CodeGenerator_Php_Method
     */
    private function _getMethodsforReflection($path)
    {
        //Invoke the class
        require_once $path;

        $file = new Zend_Reflection_File($path);
        $class = $file->getClass();
        $methods = $class->getMethods();
        $reflectionMethods = array();
        foreach ($methods as $key => $method) {
            if (!in_array($method->name, $this->_defaultMethods)) {
                $reflectionMethods[] = Zend_CodeGenerator_Php_Method::fromReflection($method);
            }
        }
        return $reflectionMethods;
    }

    /**
     * create DataMapper    
     */
    public function create()
    {
        try {
            $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'models' . DIRECTORY_SEPARATOR;

            // Obtenemos los metodos por reflection
            if ($this->_isReflection) {
                $methods = $this->_getMethodsforReflection($path . $this->_fileName . '.php');
            }

            $instance = $this->_util->format(strtolower($this->_tableName), 1);
            $className = $this->_appnamespace . '_Model_' . $this->_fileName;

            //phpdoc
            // 'shortDescription' => $this->_fileName . '.php',
            $docblock = new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Zend Rad' . "\n" . "\n" . 'LICENCIA',
                    'longDescription' => 'Este archivo está sujeta a la licencia CC(Creative Commons) que se incluye' . "\n"
                    . 'en docs/LICENCIA.txt.' . "\n"
                    . 'Tambien esta disponible a traves de la Web en la siguiente direccion' . "\n"
                    . 'http://www.zend-rad.com/licencia/' . "\n"
                    . 'Si usted no recibio una copia de la licencia por favor envie un correo' . "\n"
                    . 'electronico a <licencia@zend-rad.com> para que podamos enviarle una copia' . "\n"
                    . 'inmediatamente.',
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
            // Property dbTable
            $property = new Zend_CodeGenerator_Php_Property(array(
                    'name' => '_dbTable',
                    'visibility' => 'protected',
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'tags' => array(
                            array(
                                'name' => 'var',
                                'description' => $this->_appnamespace . "_Model_DbTable_" . ucfirst($this->_dbTable)
                            )
                        )
                    ))
                ));
            $properties[] = $property;
            if ($this->_config['basic'] == false) {
                // Property filters
                $property = new Zend_CodeGenerator_Php_Property(array(
                        'name' => '_filters',
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
                ->setMethods(array(
                    // Method passed as array
                    array(
                        'name' => 'setDbTable',
                        'parameters' => array(
                            array('name' => 'dbTable')
                        ),
                        'body' => 'if (is_string($dbTable)) {' . "\n"
                        . '    $dbTable = new $dbTable();' . "\n"
                        . '}' . "\n"
                        . 'if (!$dbTable instanceof Zend_Db_Table_Abstract) {' . "\n"
                        . "    throw new Exception('Invalid table data gateway provided');" . "\n"
                        . '}' . "\n"
                        . '$this->_dbTable = $dbTable;' . "\n"
                        . 'return $this;',
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'shortDescription' => 'Inicializa la Zend_Db_Table',
                            'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'dbTable',
                                    'datatype' => 'string'
                                ))
                            ),
                        ))
                    ),
                    array(
                        'name' => 'getDbTable',
                        'body' => 'if (null === $this->_dbTable) {' . "\n"
                        . "    \$this->setDbTable('" . $this->_appnamespace . "_Model_DbTable_" . ucfirst($this->_dbTable) . "');" . "\n"
                        . '}' . "\n"
                        . 'return $this->_dbTable;',
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'shortDescription' => 'Devuelve una instacia de Zend_Db_Table',
                            'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                    'datatype' => $this->_appnamespace . "_Model_DbTable_" . ucfirst($this->_dbTable)
                                ))
                            ),
                        ))
                    )
                ));

            // Metodo save
            $body = '$data = array(' . "\n";
            $i = 1;
            foreach ($this->_fieldsModel as $field => $attributes) {
                switch ($attributes['type']) {
                    case 'date':
                        $value = 'ZradAid_Date::invert($' . $instance . '->' . $this->_util->format('get_' . strtolower($field)) . '(),1)';
                        break;
                    default:
                        $value = '$' . $instance . '->' . $this->_util->format('get_' . strtolower($field)) . '()';
                        break;
                }
                $coma = (count($this->_fieldsModel) > $i) ? ',' : '';
                $notArray = array('created');
                if (!in_array($field, $notArray)) {
                    $body .= '    \'' . $field . '\' => ' . $value . $coma . "\n";
                }
                $i++;
            }

            $body .= ');' . "\n\n";

            // Verificamos si tiene ID
            if (isset($this->_fieldsModel['id'])) {
                // Tienes ID
                $body .= 'if (null === ($id = $' . $instance . '->getId())) {' . "\n";
                $body .= $this->getWhitespace(4) . 'unset($data[\'id\']);' . "\n";

                foreach ($this->_fieldsModel as $field => $attributes) {
                    $columName = strtolower($field);
                    switch ($columName) {
                        case 'ip':
                            $body .= $this->getWhitespace(4) . '$data[\'' . $field . '\'] = ZradAid_Helper::getIP();' . "\n";
                            break;
                        case 'created':
                            //$body .= '    $data[\'' . $field . '\'] = Zrad_Date::getDateTime();' . "\n";
                            break;
                    }
                }

                $body .= $this->getWhitespace(4) . 'return $this->getDbTable()->insert($data);' . "\n";
                $body .= '} else {' . "\n";
                foreach ($this->_fieldsModel as $field => $attributes) {
                    $columName = strtolower($field);
                    if ($columName == 'modified') {
                        $body .= $this->getWhitespace(4) . '$data[\'' . $field . '\'] = new Zend_Db_Expr(\'NOW()\');' . "\n";
                    }
                }

                $body .= $this->getWhitespace(4) . '$this->getDbTable()->update($data, array(\'id = ?\' => $id));' . "\n";
                $body .= '}';
            } else {
                // No tiene ID
                foreach ($this->_fieldsModel as $field => $attributes) {
                    $columName = strtolower($field);
                    switch ($columName) {
                        case 'ip':
                            $body .= '$data[\'' . $field . '\'] = ZradAid_Helper::getIP();' . "\n";
                            break;
                    }
                }

                $body .= '$this->getDbTable()->insert($data);' . "\n";
            }

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'save',
                    'parameters' => array(
                        array(
                            'name' => $instance,
                            'type' => $this->_appnamespace . '_Model_' . $this->_dbTable
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Inserta un nuevo objeto a la tabla',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => $instance,
                                'datatype' => $this->_appnamespace . '_Model_' . $this->_dbTable
                            ))
                        )
                    )),
                ));

            $class->setMethod($method);

            //method find
            $body = '$result = $this->getDbTable()->find($id);' . "\n"
                . 'if (0 == count($result)) {' . "\n"
                . '    return;' . "\n"
                . '}' . "\n"
                . '$row = $result->current();' . "\n";
            foreach ($this->_fieldsModel as $field => $attributes) {
                switch ($attributes['type']) {
                    case 'date':
                        $value = 'ZradAid_Date::invert($row->' . $field . ',2)';
                        break;
                    default:
                        $value = '$row->' . $field;
                        break;
                }
                $body .= '$' . $instance . '->' . $this->_util->format('set_' . strtolower($field)) . '(' . $value . ');' . "\n";
            }

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'find',
                    'parameters' => array(
                        array(
                            'name' => 'id',
                        ),
                        array(
                            'name' => $instance,
                            'type' => $this->_appnamespace . '_Model_' . $this->_dbTable
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Obtiene una instancia de la clase ' . $this->_appnamespace . '_Model_' . $this->_dbTable,
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'id',
                                'datatype' => 'int',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => $instance,
                                'datatype' => $this->_appnamespace . '_Model_' . $this->_dbTable
                            ))
                        ),
                    ))
                ));

            $class->setMethod($method);

            // Metodo fetchAll
            $body = '$resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);' . "\n"
                . '$entries   = array();' . "\n"
                . 'foreach ($resultSet as $row) {' . "\n"
                . '    $entry = new ' . $this->_appnamespace . '_Model_' . $this->_dbTable . '();' . "\n";
            foreach ($this->_fieldsModel as $field => $attributes) {
                switch ($attributes['type']) {
                    case 'date':
                        $value = 'ZradAid_Date::invert($row->' . $field . ',2)';
                        break;
                    default:
                        $value = '$row->' . $field;
                        break;
                }
                $body .= '    $entry->' . $this->_util->format('set_' . strtolower($field)) . '(' . $value . ');' . "\n";
            }
            $body .= '    $entries[] = $entry;' . "\n"
                . '}' . "\n"
                . 'return $entries;';

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'fetchAll',
                    'parameters' => array(
                        array(
                            'name' => 'where',
                            'defaultValue' => null
                        ),
                        array(
                            'name' => 'order',
                            'defaultValue' => null
                        ),
                        array(
                            'name' => 'count',
                            'defaultValue' => null
                        ),
                        array(
                            'name' => 'offset',
                            'defaultValue' => null
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Obtiene un array de instancias de la clase ' . $this->_appnamespace . '_Model_' . $this->_dbTable . "\n" . 'fetchAll(array(\'id = ?\' => 1), array(\'id DESC\'))',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'where',
                                'datatype' => 'array',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'order',
                                'datatype' => 'array',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'count',
                                'datatype' => 'int',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'offset',
                                'datatype' => 'int',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                'datatype' => 'array'
                            ))
                        ),
                    )),
                ));

            $class->setMethod($method);

            // Metodo delete
            $body = $this->_indentation . '$where = $this->getDbTable()->getAdapter()->quoteInto(\'id = ?\', $id);' . "\n";
            $body .= '$this->getDbTable()->delete($where);';

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'delete',
                    'parameters' => array(
                        array(
                            'name' => 'id',
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Elimina un registro de la tabla',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'id',
                                'datatype' => 'int',
                            ))
                        )
                    ))
                ));
            $class->setMethod($method);

            // Solo si no es basico
            if ($this->_config['basic'] == false) {

                // Verificamos si se puede renderizar el metodo
                if (isset($this->_mapper['model']['method']['toSelect'])) {
                    $body = '$resultSet = $this->getDbTable()->fetchAll();' . "\n"
                        . '$options = ($default) ? array(\'\' => $optionDefault) : array();' . "\n"
                        . 'foreach ($resultSet as $row) {' . "\n"
                        . '    $options[$row->' . $this->_mapper['model']['method']['toSelect']['value'] . '] = $row->' . $this->_mapper['model']['method']['toSelect']['option'] . ';' . "\n"
                        . '}' . "\n"
                        . 'return $options;' . "\n";

                    $method = new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'toSelect',
                            'parameters' => array(
                                array(
                                    'name' => 'default',
                                    'defaultValue' => true
                                ),
                                array(
                                    'name' => 'optionDefault',
                                    'defaultValue' => 'Todas'
                                )
                            ),
                            'body' => $body,
                            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                                'shortDescription' => 'Puebla los combos HTMls',
                                'tags' => array(
                                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                        'paramName' => 'default',
                                        'datatype' => 'bool',
                                    )),
                                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                        'paramName' => 'optionDefault',
                                        'datatype' => 'string',
                                    )),
                                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                        'datatype' => 'array'
                                    ))
                                ),
                            )),
                        ));
                    $class->setMethod($method);
                }

                // Metodo pagination
                $nFields = count($this->_fieldsModel);
                $nValid = 1;
                // colName
                $columns = $this->_mapper['grid']['colmodels'];
                $colNames = '';
                $i = 1;

                $alias = substr($this->_tableName, 0, 1);
                $thumbs = array();

                foreach ($columns as $field => $attributes) {
                    switch ($attributes['type']) {
                        case 'date':
                            $column = 'DATE_FORMAT(' . $alias . '.' . $field . ', "%d/%m/%Y") AS ' . $field;
                            break;
                        case 'tinyint':
                            $esBool = array('publicado', 'es_activo', 'is_active');
                            $column = (in_array($field, $esBool)) ? 'IF(' . $alias . '.' . $field . ' = 0,"NO","SI") AS .' . $field : $alias . '.' . $field;
                            break;
                        case 'image':
                            $column = $alias . '.' . $field;
                            array_push($thumbs, $field);
                            break;
                        default:
                            $column = $alias . '.' . $field;
                    }
                    $coma = (count($columns) > $i) ? ',' : '';
                    $colNames .= '\'' . $column . '\'' . $coma;
                    $i++;
                }

                $body = '// Adapter Db' . "\n";
                $body .= '$db = Zend_Db_Table::getDefaultAdapter();' . "\n" . "\n";
                $body .= '$selectA = $this->_processFilters()->from(array(\'' . $alias . '\' => \'' . $this->_originalTableName . '\'), array(\'COUNT(*) AS total\'));' . "\n";
                $body .= '$count = $db->query($selectA)->fetchColumn();' . "\n" . "\n";
                $body .= '$totalPages = ($count > 0) ? ceil($count / $limit) : 0;' . "\n";
                $body .= '$start = $limit * $page - $limit;' . "\n" . "\n";
                $body .= '// Orders' . "\n";
                $body .= '$orders = array();' . "\n";
                $body .= '$order = \'' . $alias . '.\' . $sidx . \' \' . strtoupper($sort);' . "\n";
                $body .= 'array_push($orders, $order);' . "\n" . "\n";

                $body .= '$selectB = $this->_processFilters()->from(array(\'' . $alias . '\' => \'' . $this->_originalTableName . '\'), array(' . $colNames . '))' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '->order($orders)' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '->limit($limit, $start);' . "\n" . "\n";
                $body .= '$data = $db->query($selectB)->fetchAll();' . "\n";
                $body .= '//$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();' . "\n";
                $body .= '$rows = array();' . "\n";
                $body .= 'foreach ($data as $indice => $value) {' . "\n";

                // Verificamos si existe datos tipo imagen
                foreach ($thumbs as $thumb) {
                    $body .= $this->setIndent(4)->getIndent() . '$data[$indice][\'' . $thumb . '\'] = \'<img src="\'. $baseUrl . \'/admin/multimedia/render/image/\' . $data[$indice][\'' . $thumb . '\'] . \'/width/113/height/75/entity/' . $this->_util->format($this->_tableName, 10) . '" border="0" alt="foto" />\';' . "\n";
                }

                $body .= $this->setIndent(4)->getIndent() . '$cell = array_values($data[$indice]);' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '$numeration = ($page - 1) * $limit;' . "\n";
                $body .= $this->setIndent(4)->getIndent() . 'array_unshift($cell, $indice + 1 + $numeration);' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '$temp = array(\'id\' => $value[\'id\'], \'cell\' => $cell);' . "\n";
                $body .= $this->setIndent(4)->getIndent() . 'array_push($rows, $temp);' . "\n";
                $body .= '}' . "\n";
                $body .= '$request = array(\'page\' => $page, \'total\' => $totalPages, \'records\' => $count, \'rows\' => $rows);' . "\n";
                $body .= 'return $request;' . "\n";

                $method = new Zend_CodeGenerator_Php_Method(array(
                        'name' => 'pagination',
                        'parameters' => array(
                            array(
                                'name' => 'page',
                            ),
                            array(
                                'name' => 'limit',
                            ),
                            array(
                                'name' => 'sidx',
                            ),
                            array(
                                'name' => 'sort',
                            )
                        ),
                        'body' => $body,
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'shortDescription' => 'Pagina los datos segun el modelo Jqgrid',
                            'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'page',
                                    'datatype' => 'int',
                                )),
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'limit',
                                    'datatype' => 'int',
                                )),
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'sidx',
                                    'datatype' => 'int',
                                )),
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'sort',
                                    'datatype' => 'int',
                                ))
                            ),
                        ))
                    ));
                $class->setMethod($method);

                // Metodo addFilter
                $body = '$this->_filters[$attribute] = $value;';
                $method = new Zend_CodeGenerator_Php_Method(array(
                        'name' => 'addFilter',
                        'parameters' => array(
                            array(
                                'name' => 'attribute'
                            ),
                            array(
                                'name' => 'value'
                            )
                        ),
                        'body' => $body,
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'shortDescription' => 'Agrega un nuevo filtro',
                            'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'attribute',
                                    'datatype' => 'string',
                                )),
                                new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                    'paramName' => 'value',
                                    'datatype' => 'string',
                                ))
                            ),
                        ))
                    ));
                $class->setMethod($method);

                // Metodo _processFilters
                $namespace = $this->_util->format($this->_tableName, 1);
                $body = '// Adapter Db' . "\n";
                $body .= '$db = Zend_Db_Table::getDefaultAdapter();' . "\n";
                $body .= '$select = $db->select();' . "\n" . "\n";
                $body .= '// Filters' . "\n";
                $body .= '$search = false;' . "\n";
                $body .= 'if (is_array($this->_filters)) {' . "\n";
                $body .= $this->setIndent(4)->getIndent() . 'foreach ($this->_filters as $indice => $value) {' . "\n";
                $body .= $this->setIndent(8)->getIndent() . 'switch ($indice) {' . "\n";
                $body .= $this->setIndent(12)->getIndent() . 'case \'_search\':' . "\n";
                $body .= $this->setIndent(16)->getIndent() . '$search = ($value == \'true\') ? true : false;' . "\n";
                $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
                $columns = $this->_mapper['grid']['fieldnames'];
                //$columns = $this->_mapper['grid']['colmodels'];
                foreach ($this->_fieldsModel as $field => $attributes) {
                    if (in_array($field, $columns)) {
                        $body .= $this->setIndent(12)->getIndent() . 'case \'' . $field . '\':' . "\n";
                        //echo '--------------------------------' . print_r($attributes) . "\n";
                        if ($attributes['type'] == 'varchar') {
                            $body .= $this->setIndent(16)->getIndent() . '$like = $db->quoteInto(\'' . $alias . '.' . $field . ' LIKE ?\', \'%\' . $value . \'%\');' . "\n";
                            $body .= $this->setIndent(16)->getIndent() . '$select->where($like);' . "\n";
                        } else {
                            $body .= $this->setIndent(16)->getIndent() . '$select->where(\'' . $alias . '.' . $field . ' = ?\', $value);' . "\n";
                        }
                        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
                    }
                }
                $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
                $body .= '}' . "\n" . "\n";
                $body .= '$filter = new Zend_Session_Namespace(\'filter\');' . "\n";
                $body .= 'if ($search) {' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '// $filter->' . $namespace . ' = $select;' . "\n";
                $body .= '} else if (isset($filter->' . $namespace . ')) {' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '$select = $filter->' . $namespace . ';' . "\n";
                $body .= '}' . "\n";
                $body .= 'return $select;';

                $method = new Zend_CodeGenerator_Php_Method(array(
                        'name' => '_processFilters',
                        'visibility' => 'private',
                        'body' => $body,
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'shortDescription' => 'Concatena todos los filtros',
                            'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                    'datatype' => 'Zend_Db_Select',
                                )),
                            ),
                        )),
                    ));
                $class->setMethod($method);

                // Verificamos si la entidad tiene campos tipo imagen o file
                if (isset($this->_mapper['controller']['type']['image']) ||
                    isset($this->_mapper['controller']['type']['youtube']) ||
                    isset($this->_mapper['controller']['type']['file'])) {

                    // Method getMedia
                    $body = '$select = $this->getDbTable()->select()' . "\n";
                    $body .= $this->setIndent(4)->getIndent() . '->from(\'' . $this->_originalTableName . '\', $field)' . "\n";
                    $body .= $this->setIndent(4)->getIndent() . '->where(\'id = ?\', $id);' . "\n";
                    $body .= 'return $this->getDbTable()->fetchRow($select)->$field;' . "\n";

                    $method = new Zend_CodeGenerator_Php_Method(array(
                            'name' => 'getMedia',
                            'parameters' => array(
                                array('name' => 'id'),
                                array('name' => 'field'),
                            ),
                            'visibility' => 'public',
                            'body' => $body,
                            'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                                'shortDescription' => 'Obtiene el nombre del archivo fisico',
                                'tags' => array(
                                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                        'paramName' => 'id',
                                        'datatype' => 'int',
                                    )),
                                    new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                        'paramName' => 'field',
                                        'datatype' => 'string',
                                    )),
                                    new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                        'datatype' => 'string'
                                    ))
                                ),
                            ))
                        ));
                    $class->setMethod($method);
                }
            }

            // Metodos por reflection
            if ($this->_isReflection && !empty($methods)) {
                $class->setMethods($methods);
            }

            // File
            $file = new Zend_CodeGenerator_Php_File();
            $file->setClass($class);

            // Create file
            file_put_contents($path . $this->_fileName . '.php', $file->generate());
            //echo $class->__toString();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createRaffle($workTable = null)
    {
        try {
            $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'models' . DIRECTORY_SEPARATOR;

            //$instance = $this->_util->format(strtolower($this->_tableName), 1);
            $className = $this->_appnamespace . '_Model_SorteoMapper';

            //phpdoc
            // 'shortDescription' => $this->_fileName . '.php',
            $docblock = new Zend_CodeGenerator_Php_Docblock(array(
                    'shortDescription' => 'Zend Rad' . "\n" . "\n" . 'LICENCIA',
                    'longDescription' => 'Este archivo está sujeta a la licencia CC(Creative Commons) que se incluye' . "\n"
                    . 'en docs/LICENCIA.txt.' . "\n"
                    . 'Tambien esta disponible a traves de la Web en la siguiente direccion' . "\n"
                    . 'http://www.zend-rad.com/licencia/' . "\n"
                    . 'Si usted no recibio una copia de la licencia por favor envie un correo' . "\n"
                    . 'electronico a <licencia@zend-rad.com> para que podamos enviarle una copia' . "\n"
                    . 'inmediatamente.',
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
                            'description' => 'http://www.zend-rad.com/licencia/  CC licencia Creative Commons'
                        )
                    )
                ));

            $properties = array(
                new Zend_CodeGenerator_Php_Property(array(
                    'name' => '_db',
                    'visibility' => 'protected',
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'tags' => array(
                            array(
                                'name' => 'var',
                                'description' => 'Zend_Db_Adapter_Abstract'
                            )
                        )
                    ))
                )),
                new Zend_CodeGenerator_Php_Property(array(
                    'name' => '_collection',
                    'visibility' => 'private',
                    'defaultValue' => 'array()',
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'tags' => array(
                            array(
                                'name' => 'var',
                                'description' => 'array'
                            )
                        )
                    ))
                ))
            );

            //class
            $class = new Zend_CodeGenerator_Php_Class();
            $class->setName($className)
                ->setDocblock($docblock)
                ->setProperties($properties)
                ->setMethods(array(
                    // Method passed as array
                    array(
                        'name' => '__construct',
                        'body' => '$this->_db = Zend_Db_Table::getDefaultAdapter();',
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'shortDescription' => 'Inicializa el Adapter',
                        ))
                    ),
                    array(
                        'name' => 'getCollection',
                        'body' => 'return $this->_collection;',
                        'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                            'tags' => array(
                                new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                    'datatype' => 'array'
                                ))
                            ))
                        )
                    )
                ));

            // Verificamos si existe dni
            $esIternacional = false;
            $columnasInternacional = array('tipo_documento', 'nro_documento');

            $model = $this->_model = new Zrad_Db_Model();
            $participanteCampos = $model->describeTable('participante');

            foreach ($participanteCampos as $indice => $column) {
                $columnT = strtolower($column['COLUMN_NAME']);
                if (in_array($columnT, $columnasInternacional)) {
                    $esIternacional = true;
                    break;
                }
            }

            // Method obtenerParticipante            
            $body = '$select = $this->_db->select()' . "\n";
            $body .= $this->setIndent(4)->getIndent() . '->from(array(\'a\' => \'participante\'), array(' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'CONCAT(a.nombres," ",a.apellidos) AS participante\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.id AS participante_id\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.dni\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.fb_uid\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'DATE_FORMAT(a.fecha_nacimiento, "%d/%m/%Y") AS nacimiento\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.email\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.ciudad\'';

            // Verificamos si tiene un tabla relacionada
            if (null !== $workTable) {
                $body .= ',' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '\'b.id AS ' . $workTable . '_id\',' . "\n";
                $columnas = array('detalle', 'imagen', 'votos');
                // Obtenemos todas las columnas de la tabla work
                foreach ($this->_fieldsModel as $field => $attributes) {
                    $column = strtolower($field);
                    if (in_array($column, $columnas)) {
                        $body .= $this->setIndent(8)->getIndent() . '\'b.' . $column . '\',' . "\n";
                    }
                }
                $body .= $this->setIndent(8)->getIndent() . '\'IF(b.es_activo = 0,"NO","SI") AS es_activo\'))' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '->joinInner(array(\'b\' => \'' . $workTable . '\'), \'b.participante_id = a.id\', array())' . "\n";
            } else {
                $body .= '))' . "\n";
            }

            $body .= $this->setIndent(4)->getIndent() . '->where(\'a.id = ?\', $participanteId);' . "\n\n";
            $body .= 'return $this->_db->query($select)->fetch();';

            $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'obtenerParticipante',
                    'parameters' => array(
                        array(
                            'name' => 'participanteId'
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Busca los datos de un participante por Id',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'participanteId',
                                'datatype' => 'int'
                            ))
                        )
                    ))
                    )
            ));

            // Method Count
            $body = $this->setIndent(4)->getIndent() . '$select = $this->_db->select()->from(\'participante\', \'COUNT(id)\');' . "\n";
            $body .= 'return $this->_db->query($select)->fetchColumn();';
            $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'getCount',
                    'body' => $body
                    )
            ));

            // Method pagination   
            $thumbs = array();
            $body = '// Orders' . "\n";
            $body .= '$orders = array();' . "\n";
            $body .= '$order = $sidx . \' \' . strtoupper($sort);' . "\n";
            $body .= 'array_push($orders, $order);' . "\n" . "\n";
            $body .= '$select = $this->_processFilters()' . "\n";
            $body .= $this->setIndent(4)->getIndent() . '->from(array(\'a\' => \'participante\'), array(' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.id AS participante_id\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'CONCAT(a.nombres," ",a.apellidos) AS participante_nombres\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . 'new Zend_Db_Expr(\'CASE c.tipo WHEN 1 THEN "GANADOR" WHEN 2 THEN "SUPLENTE" ELSE "REGULAR" END AS tipo\'),' . "\n";

            if ($esIternacional) {
                $body .= $this->setIndent(8)->getIndent() . 'new Zend_Db_Expr(\'CASE a.tipo_documento WHEN 1 THEN "DNI" WHEN 2 THEN "DOCUMENTO DE EXTRANJERIA" ELSE "OTRO" END AS tipo_documento\'),' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '\'a.nro_documento\',' . "\n";
            } else {
                $body .= $this->setIndent(8)->getIndent() . '\'a.dni\',' . "\n";
            }

            $body .= $this->setIndent(8)->getIndent() . '\'a.fb_uid\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'DATE_FORMAT(a.fecha_nacimiento, "%d/%m/%Y") AS nacimiento\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.email\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.ciudad\',' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '\'a.paso\'';

            // Verificamos si tiene un tabla relacionada
            if (null !== $workTable) {
                $body .= ',' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '\'b.id AS ' . $workTable . '_id\',' . "\n";
                $columnas = array('detalle', 'imagen', 'votos', 'es_activo');
                // Obtenemos todas las columnas de la tabla work
                foreach ($this->_mapper['grid']['colmodels'] as $field => $attributes) {
                    $column = strtolower($field);
                    if (in_array($column, $columnas)) {
                        switch ($column) {
                            case 'es_activo':
                                $body .= $this->setIndent(8)->getIndent() . '\'IF(b.es_activo = 0,"NO","SI") AS es_activo\'))' . "\n";
                                break;
                            default:$body .= $this->setIndent(8)->getIndent() . '\'b.' . $column . '\',' . "\n";
                                break;
                        }
                    }
                    if ($attributes['type'] == 'image') {
                        array_push($thumbs, $field);
                    }
                }

                $body .= $this->setIndent(4)->getIndent() . '->joinInner(array(\'b\' => \'' . $workTable . '\'), \'b.participante_id = a.id\', array())' . "\n";
            } else {
                $body .= '))' . "\n";
            }

            $body .= $this->setIndent(4)->getIndent() . '->joinLeft(array(\'c\' => \'ganador\'), \'c.participante_id = a.id\', array())' . "\n";

            $body .= $this->setIndent(4)->getIndent() . '->order($orders);' . "\n\n";
            $body .= '$data = $this->_db->query($select)->fetchAll();' . "\n";
            if (count($thumbs) > 0) {
                $body .= '$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();' . "\n";
            } else {
                $body .= '//$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();' . "\n";
            }

            $body .= '$rows = array();' . "\n";
            $body .= '$participantes = array();' . "\n";
            $body .= 'foreach ($data as $indice => $value) {' . "\n";

            // Verificamos si existe datos tipo imagen
            foreach ($thumbs as $thumb) {
                $body .= $this->setIndent(4)->getIndent() . '$data[$indice][\'' . $thumb . '\'] = \'<img src="\'. $baseUrl . \'/admin/multimedia/render/image/\' . $data[$indice][\'' . $thumb . '\'] . \'/width/113/height/75/entity/' . $this->_util->format($this->_tableName, 10) . '" border="0" alt="foto" />\';' . "\n";
            }

            $body .= $this->setIndent(4)->getIndent() . '$cell = array_values($data[$indice]);' . "\n";
            $body .= $this->setIndent(4)->getIndent() . 'array_unshift($cell, $indice + 1);' . "\n";
            $body .= $this->setIndent(4)->getIndent() . '$temp = array(\'id\' => $value[\'participante_id\'], \'cell\' => $cell);' . "\n";
            $body .= $this->setIndent(4)->getIndent() . 'array_push($rows, $temp);' . "\n";
            $body .= $this->setIndent(4)->getIndent() . 'array_push($participantes, $value[\'participante_id\']);' . "\n";
            $body .= '}' . "\n\n";
            $body .= '$count = count($data);' . "\n\n";
            $body .= '// Guardamos para el sorteo' . "\n";
            $body .= '$sorteo = new Zend_Session_Namespace(\'sorteo\');' . "\n";
            $body .= '$sorteo->participantes = $participantes;' . "\n\n";
            $body .= '$request = array(\'page\' => 1, \'total\' => 1, \'records\' => $count, \'rows\' => $rows);' . "\n";
            $body .= 'return $request;' . "\n";

            $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'pagination',
                    'parameters' => array(
                        array(
                            'name' => 'sidx',
                        ),
                        array(
                            'name' => 'sort',
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Todos los participantes activos',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'sidx',
                                'datatype' => 'int',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'sort',
                                'datatype' => 'int',
                            ))
                        ),
                    ))
                )));

            // Method addFilter
            $body = '$this->_filters[$attribute] = $value;';
            $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                    'name' => 'addFilter',
                    'parameters' => array(
                        array(
                            'name' => 'attribute'
                        ),
                        array(
                            'name' => 'value'
                        )
                    ),
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Agrega un nuevo filtro',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'attribute',
                                'datatype' => 'string',
                            )),
                            new Zend_CodeGenerator_Php_Docblock_Tag_Param(array(
                                'paramName' => 'value',
                                'datatype' => 'string',
                            ))
                        ),
                    ))
                )));

            // Method _processFilters
            $body = '// Adapter Db' . "\n";
            $body .= '$db = Zend_Db_Table::getDefaultAdapter();' . "\n";
            $body .= '$select = $db->select();' . "\n" . "\n";
            $body .= '// Filters' . "\n";
            $body .= '$search = false;' . "\n";
            $body .= 'if (is_array($this->_filters)) {' . "\n";
            $body .= $this->setIndent(4)->getIndent() . 'foreach ($this->_filters as $indice => $value) {' . "\n";
            $body .= $this->setIndent(8)->getIndent() . 'switch ($indice) {' . "\n";
            $body .= $this->setIndent(12)->getIndent() . 'case \'_search\':' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$search = ($value == \'true\') ? true : false;' . "\n";
            $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
            $body .= $this->setIndent(12)->getIndent() . 'case \'id\':' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$select->where(\'a.id = ?\', $value);' . "\n";
            $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
            $body .= $this->setIndent(12)->getIndent() . 'case \'esActivo\':' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$select->where(\'a.es_activo = ?\', $value);' . "\n";
            $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
            $body .= $this->setIndent(12)->getIndent() . 'case \'participante\':' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$like1 = $this->_db->quoteInto(\'a.nombres LIKE ?\', \'%\' . $value . \'%\');' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$like2 = $this->_db->quoteInto(\'a.apellidos LIKE ?\', \'%\' . $value . \'%\');' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$select->where($like1);' . "\n";
            $body .= $this->setIndent(16)->getIndent() . '$select->orWhere($like2);' . "\n";
            $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
            $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
            $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
            $body .= '}' . "\n" . "\n";
            $body .= 'return $select;';

            $method = new Zend_CodeGenerator_Php_Method(array(
                    'name' => '_processFilters',
                    'visibility' => 'private',
                    'body' => $body,
                    'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                        'shortDescription' => 'Concatena todos los filtros',
                        'tags' => array(
                            new Zend_CodeGenerator_Php_Docblock_Tag_Return(array(
                                'datatype' => 'Zend_Db_Select',
                            )),
                        ),
                    )),
                ));
            $class->setMethod($method);

            // File
            $file = new Zend_CodeGenerator_Php_File();
            $file->setClass($class);

            // Create file
            file_put_contents($path . 'SorteoMapper.php', $file->generate());
            //echo $class->__toString();
        } catch (Exception $e) {
            throw $e;
        }
    }

}