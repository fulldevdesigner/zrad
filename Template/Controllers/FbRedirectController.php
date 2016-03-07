<?php

class Facebook_RedirectController extends Zend_Controller_Action
{

    /**
     * @var Zend_Session_Namespace
     */
    private $_fb = null;

    public function init()
    {
        $this->_fb = new Zend_Session_Namespace('fb');
    }

    public function indexAction()
    {
        if (empty($this->_fb->data['loginUrl'])) {
            $this->_redirect('/');
        }

        $this->_helper->layout->setLayout('web-fb');
        $this->view->loginUrl = $this->_fb->data['loginUrl'];
    }

}
