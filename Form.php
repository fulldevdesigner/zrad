<?php
/**
 * @see Zrad_Form_Form
 */
require_once 'Zrad/Form/Build.php';

class Zrad_Form
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    /**
     * @var string|null
     */
    private $_moduleName = null;

    /**
     * @var string
     */
    private $_tableName = '';

    /**
     * @var Zrad_Helper_Util
     */
    private $_util = null;

    /**
     * @var Zrad_Crud_Form
     */
    private $_form = null;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        //parent::__construct();

        if (!isset($config['tableName'])) {
            throw new Exception('No se ha definido la tabla o nodo');
        }
        if (!isset($config['registry'])) {
            throw new Exception('No se ha encontrado instacia de Registry');
        }
        $registry = $config['registry'];
        if (!($registry instanceof Zend_Tool_Framework_Registry_Interface)) {
            throw new Exception('config[registry] debe ser instancia de Zend_Tool_Framework_Registry_Interface');
        }
        $this->_tableName = $config['tableName'];
        $this->setRegistry($registry);
        if (isset($config['moduleName'])) {
            $this->_moduleName = $config['moduleName'];
        }
        $this->_util = new Zrad_Helper_Util();
        $form = new Zrad_Form_Build($config);
        $form->setTarget($config['target']);
        $form->create();

    }
    
    public function create()
    {
        try {
            $this->_form->create();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
