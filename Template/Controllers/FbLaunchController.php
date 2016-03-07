<?php

class Concurso_LaunchController extends Zend_Controller_Action
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
        // Verificamos si la aplicación se ha instalado
        $fbUid = $this->_fb->uid;
        if (empty($fbUid)) {
            // Si no se ha instalado solicitamos los permisos para instalarla
            $this->_redirect('facebook/redirect');
        }

        // Verificamos si el Id de facebook ya existe 
        $consultarSiempre = true;
        if (empty($this->_pasos->participanteId) || $consultarSiempre) {
            $participante = $this->_participanteMapper->findByIdFacebook($fbUid);                       
            
            if ($participante !== null) {
                $this->_pasos->participanteId = $participante->id;
                $this->_pasos->participanteEmail = $participante->email;
                $this->_pasos->participanteNombreCompleto = $participante->nombres . ' ' . $participante->apellidos;
                $this->_pasos->participanteNombreCorto = ZradAid_Helper::shortName($participante->nombres, $participante->apellidos);
                $this->_pasos->paso = (int) $participante->paso;
            } else {
                // Limpamos solo para pruebas 
                unset($this->_pasos->participanteId);
                unset($this->_pasos->paso);
            }
        }

        $this->_redirect('concurso');
    }

    public function indexAction()
    {
        // action body
    }

}

