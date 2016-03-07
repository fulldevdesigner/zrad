<?php

class Concurso_IndexController extends Zend_Controller_Action
{

    /**
     * @var Participantes_Model_ParticipanteMapper
     */
    private $_participanteMapper = null;

    /**
     * @var Zend_Session_Namespace
     */
    private $_fb = null;

    /**
     * @var Zend_Session_Namespace
     */
    private $_pasos = null;

    public function init()
    {
        $this->_participanteMapper = new Participantes_Model_ParticipanteMapper();
        $this->_fb = new Zend_Session_Namespace('fb');
        $this->_pasos = new Zend_Session_Namespace('Pasos');
    }

    public function preDispatch()
    {
        // Validacion cierre de la aplicacion
        $sistema = new Default_Model_Sistema();
        if ($sistema->esFinConcurso()) {
            $this->_redirect('/');
        }

        // Dispacher        
        if (isset($this->_pasos->paso)) {
            $url = '';
            // Redirecciona al siguiente paso p.e si esta en el paso 1 la url se redirecciona
            // al siguiente paso
            switch ($this->_pasos->paso) {
                case 1: $url = 'concurso/registro/fin';
                    break;
                case 2: $url = '';
                    break;
                case 3: $url = '';
                    break;
            }

            // Adiciona otros pasos p.e si tienes validacion de carga de foto o si es un 
            // concurso donde exista un ganador cada semana, para mayor informacion visite 
            // www.zend-rad.com/guia-de-programacion

            if (!empty($url)) {
                $this->_redirect($url);
            }
        }
    }

    public function indexAction()
    {
        // Esta accion es cuando el usuario ya es Fan se puede hacer un redirect al Registro
        // o podria usarse como pantalla de bienvenida
    }

}

