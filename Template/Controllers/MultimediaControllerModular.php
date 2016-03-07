<?php

/**
 * Libreria para manejo de imagenes
 * 
 * @see Zrad_Image_PHPThumb_ThumbLib.inc
 */
require_once 'Zrad/Image/PHPThumb/ThumbLib.inc.php';

class Admin_MultimediaController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    public function indexAction()
    {
        // action body
    }

    public function renderAction()
    {        
        $image = $this->getRequest()->getParam('image');
        $width = $this->getRequest()->getParam('width');
        $height = $this->getRequest()->getParam('height');
        $entity = $this->getRequest()->getParam('entity');        

        $thumb = PhpThumbFactory::create(UPLOAD_PATH . '/' . $entity . '/images/' . $image);
        $thumb->adaptiveResize($width, $height);
        $thumb->show();
    }
    
    public function getDimensionAction()
    {
        $entity = $this->getRequest()->getPost('nameEntity');
        $field = $this->getRequest()->getPost('nameImage');
        $id = $this->getRequest()->getPost('idEntity');
        
        $mapperClass = ucfirst(Zrad_String::parseString($entity)->toPlural()) . '_Model_' . ucfirst($entity) . 'Mapper';
        $mapper = new $mapperClass();
        $image = $mapper->getMedia($id, $field);

        $path = UPLOAD_PATH . '/' . $entity . '/images/' . $image;
        $thumb = PhpThumbFactory::create($path);
        $result = $thumb->getCurrentDimensions();
        $result['path'] = $this->getFrontController()->getBaseUrl() . '/uploads/' . $entity . '/images/' . $image;
        echo Zend_Json::encode($result);
    }
    
    public function downloadAction()
    {
        $entity = $this->getRequest()->getParam('entity');
        $file = $this->getRequest()->getParam('file');        
        $path = UPLOAD_PATH  . '/' . $entity . '/files/' . $file;        
        // Descargamos el archivo
        Zrad_Helper::download($path);
    }

}