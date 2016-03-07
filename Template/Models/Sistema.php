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
class Default_Model_Sistema
{

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $_db = null;

    /**
     * @var const Fecha de cierre     
     */
    const CIERRE = '2020-01-01';

    public function __construct()
    {
        $this->_db = Zend_Db_Table::getDefaultAdapter();
    }

    public function esFinConcurso()
    {
        $respuesta = false;
        $hoy = date('Y-m-d');
        if ($hoy >= self::CIERRE) {
            $respuesta = true;
        }
        
        return $respuesta;
    }

}

