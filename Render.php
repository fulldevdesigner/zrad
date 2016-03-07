<?php

/**
 * Class for create prototypes and performing common operations.
 *
 * @category   Zrad
 * @package    Zrad_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2010-2011 Zrad Technologies Perú Inc. (http://www.zend-rad.com)
 * @license    http://www.zend-rad.com/license/new-bsd     New BSD License
 */
abstract class Zrad_Render
{
    /**
     * What string to use as the indentation of output, this will typically be spaces. Eg: '    '
     * @var string
     */
    protected $_indent = '';
    
    /**
     * Set the indentation string for __toString() serialization,
     * optionally, if a number is passed, it will be the number of spaces
     *
     * @param  string|int $indent
     * @return Zrad_Render
     */
    public function setIndent($indent)
    {
        $this->_indent = $this->getWhitespace($indent);
        return $this;
    }

    /**
     * Retrieve indentation
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->_indent;
    }

    /**
     * Retrieve whitespace representation of $indent
     *
     * @param  int|string $indent
     * @return string
     */
    public function getWhitespace($indent)
    {
        if (is_int($indent)) {
            $indent = str_repeat(' ', $indent);
        }
        return (string) $indent;
    }    

}
