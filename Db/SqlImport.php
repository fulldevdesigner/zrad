<?php

// SQLImporter
// Version 1.1
// V1.0 Author: Ruben Crespo Alvarez - rumailster@gmail.com
// Updated by: Yannick Luescher, hivemail.com
// Updated by: Juan Minaya Leon

/* * ****************************************************************************************
 * Possibility to select dbase when creating an object instance:
 * -------------------------------------------------------------
 * $db = new sqlImport('dump.sql', false, 'localhost', 'testuser', 'testpass', 'testdbase');
 * $db->import();
 * if ($db->error) exit($db->error);
 * else echo "<b>Data written successfully</b>";
 * -------------------------------------------------------------
 * Now working with both /r/n resp. /n line endings (to make it work with /r see php.net)
 * Now working when using ; inside SQL statements
 * Check parameter added to output what would be written into dbase.
 * If host isn't set the active connection will be used (if any) as always.
  /***************************************************************************************** */

class Zrad_Db_SqlImport
{

    // param $check bool: echo the sql statements instead of writing them into dbase
    public $_error = '';
    private $_sqlArchive = '';

    // Constructor
    function __construct($SqlArchive, $check = false)
    {
        $this->_sqlArchive = $SqlArchive;
        $this->check = $check;
    }

    // Import Data
    function import()
    {
        // To avoid problems we're reading line by line ...
        $lines = file($this->_sqlArchive, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $buffer = '';
        foreach ($lines as $line) {
            // Skip lines containing EOL only
            if (($line = trim($line)) == '')
                continue;

            // skipping SQL comments
            if (substr(ltrim($line), 0, 2) == '--')
                continue;

            // An SQL statement could span over multiple lines ...
            if (substr($line, -1) != ';') {
                // Add to buffer
                $buffer .= $line;
                // Next line
                continue;
            } else
            if ($buffer) {
                $line = $buffer . $line;
                // Ok, reset the buffer
                $buffer = '';
            }

            // strip the trailing ;
            $line = utf8_decode(substr($line, 0, -1));

            // Write the data
            if (!$this->check){
                $model = new Zrad_Db_Model();
                $db = $model->getDb()->getAdapter();
                $db->getConnection();
                $db->query($line);                
            // or print it out
            }else {
                echo substr($line, 0, 180) . ((strlen($line) > 180) ? "...<br>" : "<br>");
                $this->_error = "<b>No data has been written (check = true)</b>";
            }

            if ($this->check) {
                $this->_error = "<b>Error (mysql_query): </b>";
                return;
            }
        }
    }

}