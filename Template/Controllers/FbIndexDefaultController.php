<?php

class Default_IndexController extends Zend_Controller_Action
{

    /**
     * @var bool
     */
    private $_irALRegistro = false;

    /**
     * @param array
     */
    private $_data = array();

    /**
     * @var Zend_Session_Namespace
     */
    private $_fb = null;

    /**
     * @param bool
     */
    private $_esFan = false;

    /**
     * Iniciamos la sesion fb ademas preparamos data de prueba
     */
    public function init()
    {
        // Facebook
        $this->_fb = new Zend_Session_Namespace('fb');

        // Development Data        
        $userProfile = array(
            'first_name' => 'Carlos',
            'last_name' => 'Andres Salas',
            'username' => 'Carlos',
            'email' => 'fbdeveloper@emediala.com'
        );

        $this->_esFan = false;

        $this->_data = array(
            'me' => $userProfile,
            'uid' => '100003346763245',
            'name' => 'Carlos',
            'loginUrl' => '',
            'logoutUrl' => '',
            'fanPage' => $this->view->fbFanPage,
            'accessToken' => ''
        );
    }

    public function preDispatch()
    {
        $facebook = ($this->view->fbEnvironment != 'development') ? $this->_initFb() : null;
        $user = $this->_data['uid'];

        $this->_fb->data = $this->_data;
        $this->_fb->uid = $user;
        $this->_fb->platform = $facebook;
    }

    private function _initFb()
    {
        $this->_esFan = false;
        $access = array(
            'appId' => $this->view->fbAppId,
            'secret' => $this->view->fbSecret
        );
        $facebook = new ZradAid_Facebook($access);

        $userProfile = null;
        $name = '';
        $user = $facebook->getUser();
        if ($user) {
            try {
                $this->_fb->me = $facebook->api('/me');
                // Enviamos el perfil
                $userProfile = $this->_fb->me;
                // Enviamos solo el nombre                    
                $name = ZradAid_Helper::shortName($this->_fb->me['name']);
            } catch (FacebookApiException $e) {
                // Token inactivo, guardar en Log
                $user = 0;
                // Log
                $log = Zend_Registry::get('log');
                $log->log('PHP Facebook: ' . $e->getMessage(), Zend_Log::ERR);
            }
        }

        // Verificamos si es fan
        $signedRequest = $facebook->getSignedRequest();
        if ($signedRequest) {
            if (isset($signedRequest['page']['liked']) && !empty($signedRequest['page']['liked'])) {
                $this->_esFan = true;
            }
        }

        // Verificamos si esta pasamdo parametro
        $appData = '';        
        //$appData = '&app_data=registro';
        if (isset($signedRequest['app_data'])) {
            if ($signedRequest['app_data'] == 'registro') {
                $this->_irALRegistro = true;
            }
        }

        $loginUrl = '';
        $logoutUrl = '';
        $viewHelper = new Zend_View_Helper_ServerUrl();
        $url = $viewHelper->serverUrl($this->getFrontController()->getBaseUrl()) . '/';
        $redirectUri = (empty($this->view->fbFanPage)) ? $url : $this->view->fbFanPage . $appData;

        if ($user) {
            $logoutUrl = $facebook->getLogoutUrl();
        } else {
            $params = array(
                'scope' => $this->view->fbScope,
                'redirect_uri' => $redirectUri
            );
            $loginUrl = $facebook->getLoginUrl($params);
        }

        $this->_data = array(
            'me' => $userProfile,
            'uid' => $user,
            'name' => $name,
            'loginUrl' => $loginUrl,
            'logoutUrl' => $logoutUrl,
            'fanPage' => $this->view->fbFanPage,
            'accessToken' => $facebook->getAccessToken()
        );

        return $facebook;
    }

    public function indexAction()
    {
        // Esta accion es cuando el usuario no es Fan de la pagina
        // Verificamos si es fan        
        if ($this->_esFan) {
            // Verificamos si la campana ya termino
            $sistema = new Default_Model_Sistema();
            if ($sistema->esFinConcurso()) {
                $this->_forward('fin');
            } else {
                $this->_redirect('concurso/launch');           
            }                       
        }
    }

    public function finAction()
    {
        // Esta accion se utiliza cuando es cierre del concurso
    }

}
