<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsHelper
 *
 * @author Juan Minaya
 */
class Zend_View_Helper_HeadPostScript extends Zend_View_Helper_Abstract
{
     public function headPostScript() {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $fileUri = 'js/scripts/'            
            . $request->getControllerName() . '/'
            . $request->getActionName()
            . '.js';

        if(file_exists($fileUri)){
            $this->view->headScript()->appendFile($this->view->baseUrl('/' . $fileUri));
        }                
    }

}

