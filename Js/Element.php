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
class Zrad_Js_Element extends Zrad_Render
{
    /**
     * @var Zrad_Helper_Util
     */
    private $_util;
    /**
     * @var string
     */
    private $_action;        
    
    public function __construct()
    {
        $this->_util = new Zrad_Helper_Util();
    }        
    
    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }
    
    public function generate($field, $attributes, $target)
    {
        $element = $attributes['element'];
        $script = '';
        $script .= $this->setIndent(16)->getIndent() . $this->_util->format($field, 1) . ': {' . "\n";

        $i = 1;
        foreach ($attributes['validators'] as $validator => $value) {
            $coma = (count($attributes['validators']) > $i) ? ',' : '';
            switch ($validator) {
                case 'NotEmpty':
                    if ($field != 'publicado') {                        
                        if(($attributes['type'] == 'image' || $attributes['type'] == 'file') && $this->_action == 'edit'){
                            // None
                        } else {
                            $script .= $this->setIndent(20)->getIndent() . 'required: true' . $coma . "\n";
                        }                        
                    }
                    break;
                case 'Digits':
                    $script .= $this->setIndent(20)->getIndent() . 'number: true' . $coma . "\n";
                    break;
                case 'StringLength':
                    if (isset($value['type'])) {
                        $type =  $value['type'];
                        switch ($type) {
                            case 'exact':
                                $script .= $this->setIndent(20)->getIndent() . 'rangelength:[' . $value['length'] . ',' . $value['length'] . ']' . $coma . "\n";
                                break;                            
                            case 'interval':
                                $script .= $this->setIndent(20)->getIndent() . 'rangelength:[' . $value['min'] . ',' . $value['length'] . ']' . $coma . "\n";                                
                                break;
                        }                                                
                    } else if ($field != 'ciudad') {
                        $script .= $this->setIndent(20)->getIndent() . 'rangelength:[3,' . $value['length'] . ']' . $coma . "\n";
                    }
                    break;
                case 'Alpha':
                    if (is_array($value) && isset($value['allowWhiteSpace'])) {
                        $script .= $this->setIndent(20)->getIndent() . 'letterswithbasicpunc : true' . $coma . "\n";
                    } else if ($attributes['type'] != 'email' ) {
                        $script .= $this->setIndent(20)->getIndent() . 'lettersonly: true' . $coma . "\n";
                    }
                    break;
                case 'Alnum':
                    if (is_array($value) && isset($value['allowWhiteSpace'])) {
                        $script .= $this->setIndent(20)->getIndent() . 'letterswithbasicpunc : true' . $coma . "\n";
                    } else {
                        $script .= $this->setIndent(20)->getIndent() . 'alphanumeric: true' . $coma . "\n";
                    }
                    break;
                case "Image":
                    $script .= $this->setIndent(20)->getIndent() . 'accept: "jpg"' . $coma . "\n";
                    break;
                case "Date":
                    $script .= $this->setIndent(20)->getIndent() . 'date: true' . $coma . "\n";
                    break;
                case "EmailAddress":
                    $script .= $this->setIndent(20)->getIndent() . 'email: true' . $coma . "\n";
                    break;
                case "File":
                    $script .= $this->setIndent(20)->getIndent() . 'accept: "pdf"' . $coma . "\n";
                    break;
                case "Uri":
                    $script .= $this->setIndent(20)->getIndent() . 'url: true' . $coma . "\n";
                    break;
            }
            $i++;
        }        
        $script .= $this->setIndent(16)->getIndent() . '}';
        return $script;
    }

}