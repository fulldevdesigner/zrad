<?php
/**
 * Description of Menu
 *
 * @author Juan Minaya
 */
class Zrad_Menu
{
    private $_label = null;
    
    private $_moduleName = null;

    private $_controllerName = null;

    private $_actionName = null;

    private $_util = null;

    public function __construct()
    {
        $this->_util = new Zrad_Helper_Util();       
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function setModuleName($moduleName)
    {
        $this->_moduleName = $moduleName;
    }

    public function setControllerName($controllerName)
    {
        $this->_controllerName = $controllerName;
    }

    public function setActionName($actionName)
    {
        $this->_actionName = $actionName;
    }

    public function create($moduleName = null, $controllerName = null)
    {
        //controller
        if($controllerName !== null){
            $this->_controllerName = $controllerName;
        }

        //module
        if($moduleName !== null){
            $this->_moduleName = $moduleName;
        }

        //label
        if ($this->_moduleName != 'admin') {
            $this->_label = $this->_util->format($this->_moduleName, 9);
        } else {
            $this->_label = 'Inicio';
        }
        //nodo
        $nodo = $this->_util->format($this->_controllerName, 2);

        $moduleLink = '';
        if ($this->_moduleName !== null) {
             $nodo = $this->_util->format($this->_moduleName, 2);
             $moduleLink = 'admin';
             $moduleLink = '/' . $moduleLink;
        }

        $controllerLink = '';
        if($this->_controllerName !== null && $this->_controllerName != 'Index'){
             $controllerLink = $this->_util->format($this->_controllerName, 2);
             $controllerLink = '/' . $controllerLink;
        }

        $actionLink = '';
        if($this->_actionName !== null && $this->_actionName != 'index'){
            $actionLink = $this->_util->format($this->_actionName, 2);
            $actionLink = '/' . $actionLink;
        }       
        
        $url = $moduleLink . $controllerLink . $actionLink;

        //obtenemos el XML
        $path = 'application' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR
            . 'admin-navigation.xml';
        $xml = simplexml_load_file($path);
        $sxe = new SimpleXMLElement($xml->asXML());
        $section = $sxe->nav->addChild($nodo);
        $section->addChild('label', $this->_label);
        $section->addChild('uri', $url);
        file_put_contents($path, $sxe->asXML());
    }

}
