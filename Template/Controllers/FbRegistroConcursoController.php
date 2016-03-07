<?php

class Concurso_RegistroController extends Zend_Controller_Action
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

    /**
     * @var Default_Model_UbigeoMapper
     */
    private $_ubigeo = null;

    public function init()
    {
        $this->_participanteMapper = new Participantes_Model_ParticipanteMapper();
        $this->_ubigeo = new Default_Model_UbigeoMapper();

        $this->_fb = new Zend_Session_Namespace('fb');
        $this->_pasos = new Zend_Session_Namespace('Pasos');
    }

    public function preDispatch()
    {
        // Verificamos si la aplicación se ha instalado        
        if (empty($this->_fb->uid)) {
            // Si no se ha instalado solicitamos los permisos para instalarla
            $this->_redirect('facebook/index/preloading');
        }
        
        // Validacion cierre de la aplicacion
        $sistema = new Default_Model_Sistema();
        if ($sistema->esFinConcurso()) {
            $this->_redirect('/');
        }
    }

    public function indexAction()
    {
        // Web Form
        $this->_helper->layout->setLayout('web-form');
        
        if (!empty($this->_pasos->paso) && $this->_pasos->paso > 0) {
            $this->_redirect('concurso');
        }

        // Paso 1
        $ciudades = $this->_ubigeo->obtenerCiudades(false, '', true);

        $form = new Concurso_Form_Participante(array('ciudades' => $ciudades));
        $form->getElement('ciudad')->setValue('LIMA');
        $form->setAction('concurso/registro/procoesar');

        // Obtenemos los datos de Facebook
        $values = array(
            'nombres' => $this->_fb->data['me']['first_name'],
            'apellidos' => $this->_fb->data['me']['last_name'],
            'email' => $this->_fb->data['me']['email']
        );
        $form->populate($values);

        $this->view->form = $form;
    }

    public function procesarAction()
    {
        // Paso 1 Ajax response
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        // Varibles de estado
        $errors = array();
        $state = false;
        $info = '';

        $ciudades = $this->_ubigeo->obtenerCiudades(false, '', true);

        $form = new Concurso_Form_Participante(array('ciudades' => $ciudades));
        $request = $this->getRequest();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                // Verificamos la edad                                
                $edad = ZradAid_Date::getAge($form->getValue('fechaNacimiento'));
                $participante = new Participantes_Model_Participante($form->getValues());
                $participante->setFbUid($this->_fb->uid);
                // Verificamos si el usuario no se ha registrado anteriormente
                if (!$this->_participanteMapper->exists($participante)) {
                    $participante->setEsActivo(1);
                    $participante->setPaso(1);
                    $participante->setEdad($edad);
                    $participanteId = $this->_participanteMapper->save($participante);
                    // Persistimos los datos en pasos
                    $this->_pasos->participanteId = $participanteId;
                    $this->_pasos->participanteEmail = $participante->getEmail();
                    $this->_pasos->participanteNombreCompleto = $participante->getNombres() . ' ' . $participante->getApellidos();
                    $this->_pasos->participanteNombreCorto = ZradAid_Helper::shortName($participante->getNombres(), $participante->getApellidos());
                    $this->_pasos->paso = 1;
                    $state = true;
                } else {
                    $errors = current(array('Verifica tus datos' => array('ya_registrado' => 'tu DNI o email ya han sido registrados')));                    
                }
            } else {
                // Muestra solo un error
                //$errors = current($form->getMessages());                
                $errors = $form->getMessages();                
            }
        }        

        $resultado = array('state' => $state, 'info' => $info, 'errors' => $errors);
        echo Zend_Json::encode($resultado);
    }

    public function finAction()
    {
        // Esta accion es para mostrarle un landing page personalizado indicandole que ha terminado su registro
        // tambien se podria poner como una zona de usuario mostrando lo que ha cargado
    }

}
