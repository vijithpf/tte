<?php

/**
 
 */

// security - hide paths
if (!defined('ADODB_DIR')) die();

class ADODB2_ibase extends ADODB_DataDict {

    public $databaseType = 'ibase';
    public $seqField = false;


    public function ActualType($meta)
    {
        switch($meta) {
        case 'C': return 'VARCHAR';
        case 'XL':
        case 'X': return 'VARCHAR(4000)';

        case 'C2': return 'VARCHAR'; // up to 32K
        case 'X2': return 'VARCHAR(4000)';

        case 'B': return 'BLOB';

        case 'D': return 'DATE';
        case 'TS':
        case 'T': return 'TIMESTAMP';

        case 'L': return 'SMALLINT';
        case 'I': return 'INTEGER';
        case 'I1': return 'SMALLINT';
        case 'I2': return 'SMALLINT';
        case 'I4': return 'INTEGER';
        case 'I8': return 'INTEGER';

        case 'F': return 'DOUBLE PRECISION';
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

}
