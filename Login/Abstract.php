<?php

/**
 * Template Method
 *
 * @author Juan Minaya León
 *
 */
abstract class Zrad_Login_Abstract extends Zend_Tool_Project_Provider_Abstract implements Zend_Tool_Framework_Provider_Pretendable
{

    protected $_indentation = '    ';
    protected $_path = '';
    protected $_util = null;
    protected $_moduleName = null;
    protected $_appnamespace = 'Application';

    /**
     * Crea el proceso completo
     */
    public abstract function create();

    /**
     * Authenticate method
     */
    protected function _getAuthenticateMethod()
    {
        $body = $this->_indentation . '$this->_helper->viewRenderer->setNoRender();' . "\n";
        $body .= '$this->_helper->layout->disableLayout();' . "\n" . "\n";

        $body .= '$request = $this->getRequest();' . "\n";
        $body .= '$form = new ' . $this->_appnamespace . '_Form_Login();' . "\n" . "\n";

        $body .= 'if (!$this->getRequest()->isPost()) {' . "\n";
        $body .= $this->_indentation . '$result = array(\'state\' => \'deny\', \'response\' => \'Debe registrar sus credenciales\');' . "\n";
        $body .= $this->_indentation . 'echo json_encode($result);' . "\n";
        $body .= $this->_indentation . 'exit();' . "\n";
        $body .= '}' . "\n" . "\n";

        $body .= 'if (!$form->isValid($request->getPost())) {' . "\n";
        $body .= $this->_indentation . '$result = array(\'state\' => \'deny\', \'response\' => \'Revise sus credenciales\');' . "\n";
        $body .= $this->_indentation . 'echo json_encode($result);' . "\n";
        $body .= $this->_indentation . 'exit();' . "\n";
        $body .= '}' . "\n" . "\n";

        $body .= '$username = $form->getValue(\'username\');' . "\n";
        $body .= '$password = $form->getValue(\'password\');' . "\n";
        $body .= '$save = (int) $request->getParam(\'save\', 0);' . "\n" . "\n";

        $body .= 'if ($username == \'admin\' && $password == \'admin\') {' . "\n". "\n";
        $body .= $this->_indentation . 'if ($save == 1) {' . "\n";
        $body .= $this->_indentation . $this->_indentation . 'setcookie(\'usu\', $username, time() + 12 * 30 * 24 * 60 * 60, \'/\');' . "\n";
        $body .= $this->_indentation . $this->_indentation . 'setcookie(\'cla\', $password, time() + 12 * 30 * 24 * 60 * 60, \'/\');' . "\n";
        $body .= $this->_indentation . '} else {' . "\n";
        $body .= $this->_indentation . $this->_indentation . 'setcookie(\'usu\', \'\', 0, \'/\');' . "\n";
        $body .= $this->_indentation . $this->_indentation . 'setcookie(\'cla\', \'\', 0, \'/\');' . "\n";
        $body .= $this->_indentation . '}' . "\n". "\n";        
        $body .= $this->_indentation . '$this->_logged->success = true;' . "\n";
        $body .= $this->_indentation . '$r = array(\'state\' => true, \'info\' => \'Administrador\');' . "\n";
        $body .= '} else {' . "\n";
        $body .= $this->_indentation . '$r = array(\'state\' => false, \'info\' => \'usuario o clave incorrecta\');' . "\n";
        $body .= '}' . "\n" . "\n";

        $body .= 'echo Zend_Json::encode($r);';
        return $body;
    }

    /**
     * Creamos el formulario de logueo
     */
    protected function _generateForm()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $formName = 'Login';
        $formResource = Zend_Tool_Project_Provider_Form::createResource($this->_loadedProfile, $formName, $this->_moduleName);
        $formResource->create();

        $fileName = 'Login';
        $className = $this->_appnamespace . '_Form_Login';

        //phpdoc
        $docblock = new Zend_CodeGenerator_Php_Docblock(array(
                'shortDescription' => 'Zend Rad' . "\n" . "\n" . 'LICENCIA',
                'longDescription' => 'Este archivo está sujeta a la licencia CC(Creative Commons) que se incluye' . "\n"
                . 'en docs/LICENCIA.txt.' . "\n"
                . 'Tambien esta disponible a traves de la Web en la siguiente direccion' . "\n"
                . 'http://www.zend-rad.com/licencia/' . "\n"
                . 'Si usted no recibio una copia de la licencia por favor envie un correo' . "\n"
                . 'electronico a <licencia@zend-rad.com> para que podamos enviarle una copia' . "\n"
                . 'inmediatamente.',
                'tags' => array(
                    array(
                        'name' => 'author',
                        'description' => 'Juan Minaya Leon <info@juanminaya.com>'
                    ),
                    array(
                        'name' => '@copyright',
                        'description' => 'Copyright (c) 2011-' . date('Y') . ' , Juan Minaya Leon (http://www.zend-rad.com)'
                    ),
                    array(
                        'name' => 'licencia',
                        'description' => 'http://www.zend-rad.com/licencia/   CC licencia Creative Commons'
                    )
                )
            ));

        $class = new Zend_CodeGenerator_Php_Class();
        $class->setName($className)
            ->setDocblock($docblock)
            ->setExtendedClass('Zend_Form');

        //method init
        //$body = $this->_indentation . '//translator' . "\n";
        //$body .= '$translator = new Zend_Translate(\'array\',APPLICATION_PATH.\'/langs/es.php\',\'es_EN\');' . "\n";
        //$body .= '$this->setTranslator($translator);' . "\n" . "\n";

        $body = '// Set the method for the display form to POST' . "\n";
        $body .= '$this->setMethod(\'post\');' . "\n" . "\n";

        $body .= '// Add username element' . "\n";
        $body .= '$this->addElement(\'text\', \'username\', array(' . "\n";
        $body .= $this->_indentation . '\'label\' => \'Username\',' . "\n";
        $body .= $this->_indentation . '\'required\' => true,' . "\n";
        $body .= $this->_indentation . '\'filters\' => array(\'StringTrim\', \'StringToLower\'), ' . "\n";
        $body .= $this->_indentation . '\'validators\' => array(' . "\n";
        $body .= $this->_indentation . '\'NotEmpty\',' . "\n";
        $body .= $this->_indentation . '\'Alnum\',' . "\n";
        $body .= $this->_indentation . 'array(\'Regex\',' . "\n";
        $body .= $this->_indentation . $this->_indentation . 'false,' . "\n";
        $body .= $this->_indentation . $this->_indentation . "array('/^[a-z][a-z0-9]{2,}$/'))" . "\n";
        $body .= $this->_indentation . '),' . "\n";
        $body .= $this->_indentation . '\'description\' => \'&nbsp;\'' . "\n";
        $body .= '));' . "\n" . "\n";

        $body .= '// Add password element' . "\n";
        $body .= '$this->addElement(\'password\', \'password\', array(' . "\n";
        $body .= $this->_indentation . '\'label\' => \'Password\',' . "\n";
        $body .= $this->_indentation . '\'required\' => true,' . "\n";
        $body .= $this->_indentation . '\'filters\' => array(\'StringTrim\', \'StringToLower\'),' . "\n";
        $body .= $this->_indentation . '\'validators\' => array(' . "\n";
        $body .= $this->_indentation . $this->_indentation . '\'NotEmpty\',' . "\n";
        $body .= $this->_indentation . $this->_indentation . 'array(\'StringLength\', false, array(4,20))' . "\n";
        $body .= $this->_indentation . '),' . "\n";
        $body .= $this->_indentation . '\'description\' => \'&nbsp;\'' . "\n";
        $body .= '));';
        $method = new Zend_CodeGenerator_Php_Method(array(
                'name' => 'init',
                'body' => $body
            ));
        $class->setMethod($method);

        //file
        $file = new Zend_CodeGenerator_Php_File();
        $file->setClass($class);

        $dirModule = '';
        if ($this->_moduleName !== null) {
            $dirModule = 'modules' . DIRECTORY_SEPARATOR . $this->_moduleName . DIRECTORY_SEPARATOR;
        }
        $dir = 'application' . DIRECTORY_SEPARATOR . $dirModule . 'forms' . DIRECTORY_SEPARATOR;

        //eliminamos el archivos creado pro defecto
        unlink($dir . $fileName . '.php');
        //create file
        file_put_contents($dir . $fileName . '.php', $file->generate());
    }

}
