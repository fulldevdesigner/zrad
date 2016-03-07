<?php

/**
 * Class for connecting to MySQL databases and performing common operations.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zrad_Db_Adapter_Pdo_Mysql
{

    /**
     * Specifies the case of column names retrieved in queries
     * Options
     * Zend_Db::CASE_NATURAL (default)
     * Zend_Db::CASE_LOWER
     * Zend_Db::CASE_UPPER
     *
     * @var integer
     */
    protected $_caseFolding = Zend_Db::CASE_NATURAL;

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName)
    {
        // @todo  use INFORMATION_SCHEMA someday when MySQL's
        // implementation has reasonably good performance and
        // the version with this improvement is in wide use.
        $db = Zrad_Db::getInstance()->getAdapter();
        $sql = "DESCRIBE $tableName";
        $stmt = $db->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $field = 0;
        $type = 1;
        $null = 2;
        $key = 3;
        $default = 4;
        $extra = 5;

        $desc = array();
        $i = 1;
        $p = 1;
        foreach ($result as $row) {
            list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity)
                = array(null, null, null, null, false, null, false);
            if (preg_match('/unsigned/', $row[$type])) {
                $unsigned = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'decimal';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^float\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'float';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = $matches[2];
                // The optional argument of a MySQL int type is not precision
                // or length; it is only a hint for display width.
            }
            if (strtoupper($row[$key]) == 'PRI') {
                $primary = true;
                $primaryPosition = $p;
                if ($row[$extra] == 'auto_increment') {
                    $identity = true;
                } else {
                    $identity = false;
                }
                ++$p;
            }
            $desc[$this->foldCase($row[$field])] = array(
                'SCHEMA_NAME' => null, // @todo
                'TABLE_NAME' => $this->foldCase($tableName),
                'COLUMN_NAME' => $this->foldCase($row[$field]),
                'COLUMN_POSITION' => $i,
                'DATA_TYPE' => $row[$type],
                'DEFAULT' => $row[$default],
                'NULLABLE' => (bool) ($row[$null] == 'YES'),
                'LENGTH' => $length,
                'SCALE' => $scale,
                'PRECISION' => $precision,
                'UNSIGNED' => $unsigned,
                'PRIMARY' => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY' => $identity
            );
            ++$i;
        }
        return $desc;
    }

    /**
     * Helper method to change the case of the strings used
     * when returning result sets in FETCH_ASSOC and FETCH_BOTH
     * modes.
     *
     * This is not intended to be used by application code,
     * but the method must be public so the Statement class
     * can invoke it.
     *
     * @param string $key
     * @return string
     */
    public function foldCase($key)
    {
        switch ($this->_caseFolding) {
            case Zend_Db::CASE_LOWER:
                $value = strtolower((string) $key);
                break;
            case Zend_Db::CASE_UPPER:
                $value = strtoupper((string) $key);
                break;
            case Zend_Db::CASE_NATURAL:
            default:
                $value = (string) $key;
        }
        return $value;
    }

}