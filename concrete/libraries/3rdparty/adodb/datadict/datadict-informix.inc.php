<?php

/**
 
 */

// security - hide paths
if (!defined('ADODB_DIR')) die();

class ADODB2_informix extends ADODB_DataDict {

    public $databaseType = 'informix';
    public $seqField = false;


    public function ActualType($meta)
    {
        switch($meta) {
        case 'C': return 'VARCHAR';// 255
        case 'XL':
        case 'X': return 'TEXT';

        case 'C2': return 'NVARCHAR';
        case 'X2': return 'TEXT';

        case 'B': return 'BLOB';

        case 'D': return 'DATE';
        case 'TS':
        case 'T': return 'DATETIME YEAR TO SECOND';

        case 'L': return 'SMALLINT';
        case 'I': return 'INTEGER';
        case 'I1': return 'SMALLINT';
        case 'I2': return 'SMALLINT';
        case 'I4': return 'INTEGER';
        case 'I8': return 'DECIMAL(20)';

        case 'F': return 'FLOAT';
        case 'N': return 'DECIMAL';
        default:
            return $meta;
        }
    }

    public function AlterColumnSQL($tabname, $flds)
    {
        if ($this->debug) ADOConnection::outp('AlterColumnSQL not supported');

        return array();
    }


    public function DropColumnSQL($tabname, $flds)
    {
        if ($this->debug) ADOConnection::outp('DropColumnSQL not supported');

        return array();
    }

    // return string must begin with space
    public function _CreateSuffix($fname, &$ftype, $fnotnull, $fdefault, $fautoinc, $fconstraint, $funsigned)
    {
        if ($fautoinc) {
            $ftype = 'SERIAL';

            return '';
        }
        $suffix = '';
        if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
        if ($fnotnull) $suffix .= ' NOT NULL';
        if ($fconstraint) $suffix .= ' ' . $fconstraint;

        return $suffix;
    }

}
