<?php

/**
 * Zend Rad
 *
 * LICENCIA
 *
 * Este archivo está sujeta a la licencia CC(Creative Commons) que se incluye
 * en docs/LICENCIA.txt.
 * Tambien esta disponible a traves de la Web en la siguiente direccion
 * http://www.zend-rad.com/licencia/
 * Si usted no recibio una copia de la licencia por favor envie un correo
 * electronico a <licencia@zend-rad.com> para que podamos enviarle una copia
 * inmediatamente.
 *
 * @author Juan Minaya Leon <info@juanminaya.com>
 * @copyright Copyright (c) 2011-2012 , Juan Minaya Leon
 * (http://www.zend-rad.com)
 * @licencia http://www.zend-rad.com/licencia/   CC licencia Creative Commons
 */
class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add username element
        $this->addElement('text', 'username', array(
            'label' => 'Username',
            'required' => true,
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array(
                'NotEmpty',
                'Alnum',
                array('Regex',
                    false,
                    array('/^[a-z][a-z0-9]{2,}$/'))
            ),
            'description' => '&nbsp;'            
        ));

        // Add password element
        $this->addElement('password', 'password', array(
            'label' => 'Password',
            'required' => true,
            'filters' => array('StringTrim', 'StringToLower'),
            'validators' => array(
                'NotEmpty',
                array('StringLength', false, array(4,8))
            ),
            'description' => '&nbsp;'
        ));
    }

}

