<?php

/**
 * @see Zrad_Abstract
 */
require_once 'zrad/Abstract.php';

class Zrad_Crud_Controller extends Zrad_Abstract
{

    /**
     * @var string
     */
    private $_appnamespaceModel = null;

    /**
     * @var boolean
     */
    private $_isImage = false;

    /**
     * @var string
     */
    private $_formName = '';

    /**
     * 
     */
    public function setFormName($formName)
    {
        $this->_formName = $formName;
    }

    /**
     * _init
     */
    protected function _init()
    {
        $this->_fileName = ucfirst($this->_controllerName) . 'Controller';
        $this->_appnamespaceModel = $this->_appnamespace;
        if ($this->_moduleName !== null) {
            $tempName = $this->_util->generateNameModule($this->_tableName);
            $this->_appnamespaceModel = $this->_util->format($tempName, 6);
        }

        $this->_isImage();
    }

    private function _isImage()
    {
        $type = $this->_mapper['controller']['type'];
        if (array_key_exists('image', $type)) {
            $this->_isImage = true;
        }
    }

    public function createFrontend()
    {

        $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'controllers' . DIRECTORY_SEPARATOR . $this->_controllerName . 'Controller.php';
        require_once $path;

        $file = new Zend_Reflection_File($path);
        $classR = $file->getClass();

        $noFilter = array($this->_actionName . 'Action', 'init');
        $tableEntity = $this->_util->format($this->_tableName, 1);
        $redirect = $this->_util->format($this->_moduleName, 2) . '/' . $this->_util->format($this->_controllerName, 2) . '/' . $this->_util->format($this->_actionName, 2);

        $results = $classR->getMethods();
        $methods = array();
        $initBody = $classR->getMethod('init')->getBody();

        $body = '';
        if (isset($this->_config['captcha'])) {
            $body .= '$this->_captcha = new ZradAid_Captcha();' . "\n";
        }

        //method init
        $body .= '// Habilitelo si necesita guardar en la Bd ' . "\n";
        $body .= '//$this->_' . $tableEntity . 'Mapper = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . 'Mapper();' . "\n";
        $body .= $initBody;
        $method = new Zend_CodeGenerator_Php_Method(
                array(
                    'name' => 'init',
                    'body' => $body
                )
        );
        array_push($methods, $method);

        foreach ($results as $result) {
            $pos = strpos($result->getName(), 'Action');
            if ($pos !== false && !in_array($result->getName(), $noFilter)) {
                array_push($methods, Zend_CodeGenerator_Php_Method::fromReflection($result));
            }
        }

        $results = $classR->getProperties();
        $properties = array();
        foreach ($results as $result) {
            $prop = Zend_CodeGenerator_Php_Property::fromReflection($result);
            if ($prop->getVisibility() != 'protected' && $prop->getVisibility() != 'public') {
                array_push($properties, $prop);
            }
        }

        //properties
        $property = array(
            'name' => '_' . $tableEntity . 'Mapper',
            'visibility' => 'private',
            'defaultValue' => null,
        );
        array_push($properties, $property);

        if (isset($this->_config['captcha'])) {
            $property = array(
                'name' => '_captcha',
                'visibility' => 'private',
                'defaultValue' => null,
            );
            array_push($properties, $property);
        }

        //method
        $body = $this->setIndent(4)->getIndent() . '$request = $this->getRequest();' . "\n";
        $body .= '$form = new ' . $this->_appnamespace . '_Form_' . $this->_formName . '();' . "\n";
        $body .= 'if ($this->getRequest()->isPost()) {' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'if ($form->isValid($request->getPost())) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '// Habilitelo si necesita guardar en la Bd ' . "\n\n";
        $body .= $this->setIndent(8)->getIndent() . '// Transaccion' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '$db = Zend_Db_Table::getDefaultAdapter();' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '$db->beginTransaction();' . "\n\n";
        $body .= $this->setIndent(8)->getIndent() . 'try {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '//$' . $tableEntity . ' = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . '($form->getValues());' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '//$' . $tableEntity . 'Id = $this->_' . $tableEntity . 'Mapper->save($' . $tableEntity . ');' . "\n";        
        $body .= $this->setIndent(12)->getIndent() . '//$form->reset();' . "\n";        
        $body .= $this->setIndent(12)->getIndent() . '$db->commit();' . "\n\n";
        $body .= $this->setIndent(12)->getIndent() . '// Redireccionamos ' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '//$this->_redirect(\'/' . $redirect . '\', array(\'exit\' => true));' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '} catch (Exception $e) {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '// Deshacemos' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$db->rollBack();' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '// Log' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'Zend_Registry::get(\'log\')->log($e->getMessage(), Zend_Log::ERR);' . "\n";     
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'Zend_Registry::get(\'log\')->log( \'Datos obtenidos, procesando...\', Zend_Log::DEBUG);' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
        $body .= '}' . "\n";
        $body .= '$this->view->form = $form;' . "\n";
        if (isset($this->_config['captcha'])) {
            $body .= '$this->view->captcha = $this->_captcha->generate(5, 126, 50);' . "\n";
        }

        $method = new Zend_CodeGenerator_Php_Method(
                array(
                    'name' => $this->_actionName . 'Action',
                    'body' => $body
                )
        );
        array_push($methods, $method);

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($classR->getName());
        $class->setExtendedClass('Zend_Controller_Action');
        $class->setProperties($properties);
        $class->setMethods($methods);

        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'controllers' . DIRECTORY_SEPARATOR;
        file_put_contents($path . $this->_fileName . '.php', $file->generate());
        //echo $class->__toString();
    }

    public function createBackend()
    {
        $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'controllers' . DIRECTORY_SEPARATOR;

        $className = $this->_controllerName . 'Controller';
        if ($this->_moduleName !== null) {
            $className = $this->_appnamespace . '_' . $this->_controllerName . 'Controller';
        }
        $tableEntity = $this->_util->format($this->_tableName, 1);

        //properties
        $properties = array(
            array(
                'name' => '_' . $tableEntity . 'Mapper',
                'visibility' => 'private',
                'defaultValue' => null,
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . 'Mapper'
                        )
                    )
                ))
            ),
            array(
                'name' => '_baseUrl',
                'visibility' => 'private',
                'defaultValue' => null,
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'string'
                        )
                    )
                ))
            )
        );

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setExtendedClass('Zend_Controller_Action')
            ->setProperties($properties);

        //seteando PHPTHumb
        /* if ($this->_isImage) {
          $class->setProperty(array(
          'name' => '_thumb',
          'visibility' => 'private',
          'defaultValue' => null,
          ));
          } */

        //method init
        $body = $this->_indentation . '$this->_baseUrl = $this->getFrontController()->getBaseUrl();' . "\n";
        $body .= '$this->_helper->layout->setLayout(\'admin-default\');' . "\n";
        $body .= '$this->_' . $tableEntity . 'Mapper = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . 'Mapper();';

        // Verificando si tiene campos tipo imagen
        // Aún en fase prueba
        if ($this->_isImage) {
            /* $body .= '$this->_thumb = new Jminaya_Image_PHPThumb(\'' . $this->_tableName . '\');' . "\n";
              $body .= '$this->_thumb->setBaseUrl($this->_baseUrl);' . "\n";
              //fields
              $fields = $this->_mapper['controller']['type']['image'];
              foreach ($fields as $field) {
              $body .= '$this->_thumb->addImage(\'' . $this->_util->format($field, 1) . '\');' . "\n";
              } */
        }
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));
        $class->setMethod($method);

        //method preDispatch
        $body = $this->_indentation . '$logged = new Zend_Session_Namespace(\'loggedInBakend\');' . "\n";
        $body .= 'if ($logged->success === null)' . "\n";
        $body .= $this->_indentation . '$this->_redirect(\'/admin/\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'preDispatch',
                'body' => $body
            ));
        $class->setMethod($method);

        //method index
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'indexAction',
                'body' => $this->_indentation . '$this->_forward(\'list\');'
            ));
        $class->setMethod($method);

        //method list
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'listAction',
                'body' => $this->_indentation . '$this->view->title = \'LISTA DE ' . $this->_util->format($this->_tableName, 7) . '\';'
            ));
        $class->setMethod($method);

        $nRows = 19;
        if ($this->_isImage) {
            $nRows = 5;
        }

        //method pagination
        $body = '// Xhr' . "\n";
        $body .= '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n" . "\n";
        $body .= '$page = (int) $this->_request->getParam(\'page\', 1);' . "\n";
        $body .= '$limit = (int) $this->_request->getParam(\'rows\', ' . $nRows . ');' . "\n";
        $body .= '$sidx = $this->_request->getParam(\'sidx\', \'id\');' . "\n";
        $body .= '$sord = $this->_request->getParam(\'sord\', \'desc\');' . "\n" . "\n";
        $body .= '// Filtros dinamicos' . "\n";
        $body .= '$params = $this->_request->getParams();' . "\n";
        $body .= 'foreach ($params as $attribute => $value) {' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'if (!empty($value)) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'switch ($attribute) {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'case \'id\': $this->_' . $tableEntity . 'Mapper->addFilter(\'id\', $value);' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'default : $this->_' . $tableEntity . 'Mapper->addFilter($attribute, $value);' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
        $body .= '}' . "\n" . "\n";
        $body .= '// Filtros estaticos' . "\n";
        $body .= '$' . $tableEntity . ' = $this->_' . $tableEntity . 'Mapper->pagination($page, $limit, $sidx, $sord);' . "\n\n";
        $body .= 'header(\'Content-Type: application/json\');' . "\n";
        $body .= 'echo Zend_Json($' . $tableEntity . ');' . "\n";

        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'paginationAction',
                'body' => $body
            ));
        $class->setMethod($method);

        $redirect = $this->_util->format($this->_moduleName, 2) . '/' . $this->_util->format($this->_controllerName, 2);

        // Metodo new        
        $body = '$form = new ' . $this->_appnamespace . '_Form_' . ucfirst($tableEntity) . '();' . "\n";
        $body .= '$form->setAction(\'admin/' . $this->_util->format($this->_tableName, 10) . '/new\');' . "\n\n";
        $body .= '// Procesar formulario' . "\n";
        $body .= '$this->_runForm($form);' . "\n\n";
        $body .= '$this->view->id = 0;' . "\n";
        $body .= '$this->view->form = $form;' . "\n";
        $body .= '$this->view->title = \'NUEVO ' . $this->_util->format($this->_controllerName, 3) . '\';' . "\n";
        ;
        $body .= '$this->render(\'entidad\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'newAction',
                'body' => $body
            ));
        $class->setMethod($method);

        // Metodo edit
        $body = $this->_indentation . '$id = (int) $this->_request->getParam(\'id\');' . "\n";
        $body .= '$' . $tableEntity . ' = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . '();' . "\n";
        $body .= '$this->_' . $tableEntity . 'Mapper->find($id, $' . $tableEntity . ');' . "\n";
        //$body .= $this->_setterFieldHardEdit() . "\n";
        $body .= '$form = new ' . $this->_appnamespace . '_Form_' . ucfirst($tableEntity) . '();' . "\n";
        $body .= '$form->setAction(\'admin/' . $this->_util->format($this->_tableName, 10) . '/edit/id/\' . $id);' . "\n";
        $body .= '$form->populate($' . $tableEntity . '->toArray());' . "\n\n";
        $body .= '// Procesar formulario' . "\n";
        $body .= '$this->_runForm($form);' . "\n\n";        
        $body .= '$this->view->id = $id;' . "\n";
        $body .= '$this->view->form = $form;' . "\n";
        $body .= '$this->view->title = \'EDITAR ' . $this->_util->format($this->_controllerName, 3) . '\';' . "\n";
        ;
        $body .= '$this->render(\'entidad\');';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'editAction',
                'body' => $body
            ));
        $class->setMethod($method);

        // Metodo _runForm
        $body = $this->setIndent(0)->getIndent() . '$request = $this->getRequest();' . "\n";
        $body .= $this->setIndent(0)->getIndent() . 'if ($this->getRequest()->isPost()) {' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'if ($form->isValid($request->getPost())) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '$id = $this->_request->getParam(\'id\');' . "\n\n";
        $body .= $this->setIndent(8)->getIndent() . '// Transaccion' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '$db = Zend_Db_Table::getDefaultAdapter();' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '$db->beginTransaction();' . "\n\n";
        $body .= $this->setIndent(8)->getIndent() . 'try {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$' . $tableEntity . ' = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . '();' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$' . $tableEntity . '->setId($id);' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$' . $tableEntity . 'Id = $this->_' . $tableEntity . 'Mapper->save($' . $tableEntity . ');' . "\n\n";
        $body .= $this->setIndent(12)->getIndent() . '// Action' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'if (!empty($id)) {' . "\n";
        $body .= $this->setIndent(16)->getIndent() . '$productoId = $id;' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '//throw new Exception(\'Prueba\');' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$db->commit();' . "\n\n";
        $body .= $this->setIndent(12)->getIndent() . '// Redireccionamos ' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$this->_redirect(\'/' . $redirect . '/list\', array(\'exit\' => true));' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '} catch (Exception $e) {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '// Deshacemos' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '$db->rollBack();' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '// Guardamos el error' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'Zend_Registry::get(\'log\')->log($e->getMessage(), Zend_Log::ERR);' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '// Asignamos el error' . "\n";
        $body .= $this->setIndent(12)->getIndent() . '// $form->setErrors(array(\'transaccion\' => $e->getMessage()));' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(0)->getIndent() . '}' . "\n";
        $method = new Zend_CodeGenerator_Php_Method(array(
                'visibility' => 'private',
                'name' => '_runForm',
                'body' => $body,
                'parameters' => array(
                    array(
                        'name' => 'form',
                        'type' => $this->_appnamespace . '_Form_' . ucfirst($tableEntity)
                    )
                ),
            ));
        $class->setMethod($method);

        // Method delete
        $body = '// Xhr' . "\n";
        $body .= '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n\n";
        $body .= '// Response' . "\n";       
        $body .= '$r = array(\'state\' => false, \'info\' => \'\', \'error\' => \'\');' . "\n\n";        
        
        $body .= 'try {' . "\n\n";  
        $body .= $this->setIndent(4)->getIndent() . '// Transaccion' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '$db = Zend_Db_Table::getDefaultAdapter();' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '$db->beginTransaction();' . "\n\n";
        $body .= $this->setIndent(4)->getIndent() . '$r[\'info\'] = \'Se ha eliminado el registro de la BD\';' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'if ($this->getRequest()->isPost()) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'throw new Exception(\'No se han enviado los datos\');' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}' . "\n\n";
        $body .= $this->setIndent(4)->getIndent() . '$id = $this->getRequest()->getPost(\'id\');' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'if (empty($id)) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'throw new Exception(\'ID nulo\');' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}' . "\n\n";
        $body .= $this->setIndent(4)->getIndent() . '$' . $tableEntity . ' = new ' . $this->_appnamespaceModel . '_Model_' . ucfirst($tableEntity) . '();' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '$this->_' . $tableEntity . 'Mapper->find($id, $' . $tableEntity . ');' . "\n\n";
        // Verificamos si tiene imagen
        if (isset($this->_mapper['controller']['type']['image'])) {
            foreach ($this->_mapper['controller']['type']['image'] as $field) {
                $body .= $this->setIndent(4)->getIndent() . '// Eliminamos la imagen del campo: ' . $field . "\n";
                $body .= $this->setIndent(4)->getIndent() . '$imagen = $' . $tableEntity . '->get' . ucfirst($this->_util->format($field, 1)) . '();' . "\n";
                $body .= $this->setIndent(4)->getIndent() . 'if (!empty($imagen)) {' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '$filename = UPLOAD_PATH . \'/' . $tableEntity . '/images/\' . $imagen;' . "\n";
                $body .= $this->setIndent(8)->getIndent() . 'if (file_exists($filename)) {' . "\n";
                $body .= $this->setIndent(12)->getIndent() . 'unlink($filename);' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '} else {' . "\n";
                $body .= $this->setIndent(12)->getIndent() . '$r[\'info\'] .= \' , pero no se econtro el archivo físico: \' . $imagen;' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
            }
        }

        // Verificamos si tiene docs        
        if (isset($this->_mapper['controller']['type']['file'])) {
            foreach ($this->_mapper['controller']['type']['file'] as $field) {
                $body .= $this->setIndent(4)->getIndent() . '// Eliminamos el archivo del campo: ' . $field . "\n";
                $body .= $this->setIndent(4)->getIndent() . '$file = $' . $tableEntity . '->get' . ucfirst($this->_util->format($field, 1)) . '();' . "\n";
                $body .= $this->setIndent(4)->getIndent() . 'if (!empty($file)) {' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '$filename = UPLOAD_PATH . \'/' . $tableEntity . '/files/\' . $file;' . "\n";
                $body .= $this->setIndent(8)->getIndent() . 'if (file_exists($filename)) {' . "\n";
                $body .= $this->setIndent(12)->getIndent() . 'unlink($filename);' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '} else {' . "\n";
                $body .= $this->setIndent(12)->getIndent() . '$r[\'info\'] .= \' , pero no se econtro el archivo físico: \' . $file;' . "\n";
                $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
                $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
            }
        }
        
        $body .= $this->setIndent(4)->getIndent() . '// Eliminamos el registro ' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '$this->_' . $tableEntity . 'Mapper->delete($id);' . "\n\n";        
        $body .= $this->setIndent(4)->getIndent() . '$db->commit();' . "\n";        
        $body .= $this->setIndent(4)->getIndent() . '$r[\'state\'] = true;' . "\n\n";        
        $body .= '} catch (Exception $e) {' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '// Deshacemos' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '$db->rollBack();' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '$r[\'info\'] = $e->getMessage();' . "\n";        
        $body .= $this->setIndent(4)->getIndent() . '// Guardamos el error' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '// Zend_Registry::get(\'log\')->log($r[\'info\'], Zend_Log::ERR);' . "\n";        
        $body .= '}' . "\n\n";       
        $body .= 'header(\'Content-Type: application/json\');' . "\n";
        $body .= 'echo Zend_Json::encode($r);';
        
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'deleteAction',
                'body' => $body
            ));
        $class->setMethod($method);

        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        file_put_contents($path . $this->_fileName . '.php', $file->generate());
        //echo $class->__toString();
    }

    public function createRaffle()
    {
        $path = 'application' . DIRECTORY_SEPARATOR . $this->_pathModule . 'controllers' . DIRECTORY_SEPARATOR;

        $className = $this->_controllerName . 'Controller';
        if ($this->_moduleName !== null) {
            $className = $this->_appnamespace . '_' . $this->_controllerName . 'Controller';
        }

        // Properties
        $properties = array(
            array(
                'name' => '_sorteoMapper',
                'visibility' => 'private',
                'defaultValue' => null,
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => $this->_appnamespace . '_Model_SorteoMapper'
                        )
                    )
                ))
            ),
            array(
                'name' => '_baseUrl',
                'visibility' => 'private',
                'defaultValue' => null,
                'docblock' => new Zend_CodeGenerator_Php_Docblock(array(
                    'tags' => array(
                        array(
                            'name' => 'var',
                            'description' => 'string'
                        )
                    )
                ))
            )
        );

        //class
        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setExtendedClass('Zend_Controller_Action')
            ->setProperties($properties);

        //method init
        $body = $this->_indentation . '$this->_baseUrl = $this->getFrontController()->getBaseUrl();' . "\n";
        $body .= '$this->_helper->layout->setLayout(\'admin-default\');' . "\n";
        $body .= '$this->_sorteoMapper = new ' . $this->_appnamespace . '_Model_SorteoMapper();';

        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            )));

        // Method preDispatch
        $body = $this->_indentation . '$logged = new Zend_Session_Namespace(\'loggedInBakend\');' . "\n";
        $body .= 'if ($logged->success === null)' . "\n";
        $body .= $this->_indentation . '$this->_redirect(\'/admin/\');';

        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'preDispatch',
                'body' => $body
            )));

        // Method index
        $body = $this->getWhitespace(4) . '// Total' . "\n";
        $body .= '$total = $this->_sorteoMapper->getCount();' . "\n";
        $body .= '$this->view->title = \'LISTA DE PARTICIPANTES\'; ' . "\n";
        $body .= '$this->view->tipos = array(\'\' => \'TODOS\', \'1\' => \'GANADORES\', \'2\' => \'SUPLENTES\');' . "\n";
        $body .= '$this->view->total = $total;';
        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'indexAction',
                'body' => $body
            )));

        // Method pagination
        $body = $this->_indentation . '// Xhr' . "\n";
        $body .= '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n" . "\n";
        $body .= '$sidx = $this->_request->getParam(\'sidx\', \'id\');' . "\n";
        $body .= '$sord = $this->_request->getParam(\'sord\', \'desc\');' . "\n" . "\n";
        $body .= '// Filtros dinamicos' . "\n";
        $body .= '$params = $this->_request->getParams();' . "\n";
        $body .= 'foreach ($params as $attribute => $value) {' . "\n";
        $body .= $this->setIndent(4)->getIndent() . 'if (!empty($value) || ($attribute == \'esActivo\' && $value == \'0\')) {' . "\n";
        $body .= $this->setIndent(8)->getIndent() . 'switch ($attribute) {' . "\n";
        $body .= $this->setIndent(12)->getIndent() . 'default : $this->_sorteoMapper->addFilter($attribute, $value);' . "\n";
        $body .= $this->setIndent(16)->getIndent() . 'break;' . "\n";
        $body .= $this->setIndent(8)->getIndent() . '}' . "\n";
        $body .= $this->setIndent(4)->getIndent() . '}' . "\n";
        $body .= '}' . "\n" . "\n";
        $body .= '$result = $this->_sorteoMapper->pagination($sidx, $sord);' . "\n";
        $body .= 'header(\'Content-Type: application/json\');' . "\n";
        $body .= 'echo Zend_Json::encode($result);' . "\n";

        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'paginationAction',
                'body' => $body
            )));

        // Method guardarGanador
        $body = $this->_indentation . '// Xhr' . "\n";
        $body .= '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n" . "\n";
        $body .= '$sorteo = new Zend_Session_Namespace(\'sorteo\');' . "\n";
        $body .= '$participantes = $sorteo->participantes;' . "\n" . "\n";
        $body .= '// Ganador aleatorio <http://www.php.net/manual/es/function.array-rand.php>' . "\n";
        $body .= '$key = array_rand($participantes, 1);' . "\n";
        $body .= '$participanteId = $participantes[$key];' . "\n";
        $body .= '$ganadorInfo = $this->_sorteoMapper->obtenerDatosDelGanador($participanteId);' . "\n";
        $body .= '$sorteo->ganador = $ganadorInfo;' . "\n\n";
        $body .= 'header(\'Content-Type: application/json\');' . "\n";
        $body .= 'echo Zend_Json::encode($ganadorInfo);' . "\n";

        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'obtenerGanadorAction',
                'body' => $body
            )));

        // Method guardarGanador
        $body = $this->_indentation . '// Xhr' . "\n";
        $body .= '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n\n";
        $body .= '// Response' . "\n";
        $body .= '$r = array(\'state\' => false, \'info\' => \'\', \'error\' => \'\');' . "\n\n";
        $body .= '$sorteo = new Zend_Session_Namespace(\'sorteo\');' . "\n";
        $body .= '$ganadorInfo = $sorteo->ganador;' . "\n";
        $body .= '// Tipo 1:Ganador, 2:Suplente, 3:Ausente' . "\n";
        $body .= '$esGanador = $this->_request->getParam(\'esSuplente\', 1);' . "\n";
        $body .= '$ganador = new Ganadores_Model_Ganador();' . "\n";
        $body .= '$ganador->setParticipanteId($ganadorInfo[\'id\']);' . "\n";
        $body .= '$ganador->setTipo($esGanador);' . "\n";
        $body .= '$this->_ganadorMapper->save($ganador);' . "\n";
        $body .= '$r[\'state\'] = true;' . "\n\n";
        $body .= 'header(\'Content-Type: application/json\');' . "\n";
        $body .= 'echo Zend_Json::encode($r);' . "\n";

        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'guardarGanadorAction',
                'body' => $body
            )));

        $body = $this->_indentation . '// Descargar Lista de Ganadores' . "\n";
        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'descargarGanadoresAction',
                'body' => $body
            )));

        $body = $this->_indentation . '// Descargar Lista de Suplentes' . "\n";
        $class->setMethod(new Zend_CodeGenerator_Php_Method(array(
                'name' => 'descargarSuplementesAction',
                'body' => $body
            )));

        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        //file_put_contents($path . $this->_fileName . '.php', $file->generate());
        echo $class->__toString();
    }

}