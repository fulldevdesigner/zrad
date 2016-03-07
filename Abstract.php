<?php

/**
 * Class for create prototypes and performing common operations.
 *
 * @category   Zrad
 * @package    Zrad_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2010-2011 Zrad Technologies Perú Inc. (http://www.zend-rad.com)
 * @license    http://www.zend-rad.com/license/new-bsd     New BSD License
 */
abstract class Zrad_Abstract
{

    /**
     * Set Indentation 4 spaces per indent
     *
     * @var string
     */
    protected $_indentation = '    ';
    /**
     * What string to use as the indentation of output, this will typically be spaces. Eg: '    '
     * @var string
     */
    protected $_indent = '';
    /**
     * Get class complementary
     *
     * @var Zrad_Helper_Util
     */
    protected $_util = null;
    /**
     * Name of Namespace
     *
     * @var string
     */
    protected $_appnamespace = 'Application';
    /**
     * Name of Table
     *
     * @var string|null
     */
    protected $_originalTableName = null;
    /**
     * Name of Table
     *
     * @var string|null
     */
    protected $_tableName = null;
    /**
     * Name of Action
     *
     * @var string|null
     */
    protected $_actionName = null;
    /**
     * Name of Controller
     *
     * @var string|null
     */
    protected $_controllerName = null;
    /**
     * Name of module
     *
     * @var string|null
     */
    protected $_moduleName = null;
    /**
     * Path module resource
     *
     * @var string
     */
    protected $_pathModule = '';
    /**
     * Path module resource
     *
     * @var string
     */
    protected $_fileName = '';
    /**
     * Array describe metadata to table
     *
     * @var array
     */
    protected $_fields = array();
    /**
     * Array describe metadata to table
     *
     * @var array
     */
    protected $_fieldsModel = array();
    /**
     * Array que describe el mapper del objeto
     *
     * @var array
     */
    protected $_mapper;
    /**
     *
     * @var array
     */
    protected $_config;
    /**
     * @var string
     */
    protected $_target = 'frontend';

    /**
     *
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->_target = $target;
    }

    /**
     * Set Table Name
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;
    }

    /**
     * Get Module Name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }

    /**
     * Get File Name
     *
     * @return strin
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Set Controller Name
     *
     * @param string $controllerName
     */
    public function setControllerName($controllerName)
    {
        $this->_controllerName = $controllerName;
    }

    /**
     * Set Module Name
     *
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->_moduleName = $moduleName;
    }

    /**
     * This method should be implemented by the client implementation to
     * construct and set custom inflectors, request and response objects.
     */
    protected function _init()
    {

    }

    /**
     * Set the indentation string for __toString() serialization,
     * optionally, if a number is passed, it will be the number of spaces
     *
     * @param  string|int $indent
     * @return Zend_View_Helper_Placeholder_Container_Abstract
     */
    public function setIndent($indent)
    {
        $this->_indent = $this->getWhitespace($indent);
        return $this;
    }

    /**
     * Retrieve indentation
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->_indent;
    }

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param  int|string $indent
     * @return string
     */
    public function getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }

        return (string) $indent;
    }

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->_config = $config;
        $this->_util = new Zrad_Helper_Util();

        if (isset($config['moduleName'])) {
            $this->_moduleName = $config['moduleName'];
        }
        if (isset($config['actionName'])) {
            $this->_actionName = $config['actionName'];
        }
        if (isset($config['controllerName'])) {
            $this->_controllerName = $config['controllerName'];
        }
        if (isset($config['tableName'])) {
            $this->_tableName = $config['tableName'];
        }
        if (isset($config['originalTableName'])) {
            $this->_originalTableName = $config['originalTableName'];
        }
        if (isset($config['target'])) {
            $this->_target = $config['target'];
        }

        if ($this->_moduleName !== null) {
            $this->_appnamespace = $this->_util->format($this->_moduleName, 6);
            $this->_pathModule = 'modules' . DIRECTORY_SEPARATOR . $this->_moduleName . DIRECTORY_SEPARATOR;
        }

        $this->_mapper = $config['mapper'];
        $this->_fields = $this->_mapper['fields'];
        $this->_fieldsModel = $this->_mapper['fieldsModel'];

        $this->_init();
    }

}
