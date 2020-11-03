<?php

/**
 
 */
// security - hide paths
if (!defined('ADODB_DIR')) die();

class ADODB2_db2 extends ADODB_DataDict {

    public $databaseType = 'db2';
    public $seqField = false;

    public function ActualType($meta)
    {
        switch($meta) {
        case 'C': return 'VARCHAR';
        case 'XL': return 'CLOB';
        case 'X': return 'VARCHAR(3600)';

        case 'C2': return 'VARCHAR'; // up to 32K
        case 'X2': return 'VARCHAR(3600)'; // up to 32000, but default page size too small

        case 'B': return 'BLOB';

        case 'D': return 'DATE';
        case 'TS':
        case 'T': return 'TIMESTAMP';

        case 'L': return 'SMALLINT';
        case 'I': return 'INTEGER';
        case 'I1': return 'SMALLINT';
        case 'I2': return 'SMALLINT';
        case 'I4': return 'INTEGER';
        case 'I8': return 'BIGINT';

        case 'F': return 'DOUBLE';
        case 'N': return 'DECIMAL';
        default:
            return $meta;
        }
    }

    // return string must begin with space
    public function _CreateSuffix($fname, &$ftype, $fnotnull, $fdefault, $fautoinc, $fconstraint, $funsigned)
    {
        $suffix = '';
        if ($fautoinc) return ' GENERATED ALWAYS AS IDENTITY'; # as identity start with 
        if (strlen($fdefault)) $suffix .= " DEFAULT $fdefault";
        if ($fnotnull) $suffix .= ' NOT NULL';
        if ($fconstraint) $suffix .= ' ' . $fconstraint;

        return $suffix;
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


    public function ChangeTableSQL($tablename, $flds, $tableoptions = false)
    {

        /**
         
         */
        $validTypes = array('CHAR', 'VARC');
        $invalidTypes = array('BIGI', 'BLOB', 'CLOB', 'DATE', 'DECI', 'DOUB', 'INTE', 'REAL', 'SMAL', 'TIME');
        // check table exists
        $cols = $this->MetaColumns($tablename);
        if (empty($cols)) {
            return $this->CreateTableSQL($tablename, $flds, $tableoptions);
        }

        // already exists, alter table instead
        list($lines, $pkey) = $this->_GenFields($flds);
        $alter = 'ALTER TABLE ' . $this->TableName($tablename);
        $sql = array();

        foreach ($lines as $id => $v) {
            if (isset($cols[$id]) && is_object($cols[$id])) {
                /**
                 fields, so account for that too
                 */
                $vargs = explode(' ', $v);
                // assume that $vargs[0] is the field name.
                $i=0;
                // Find the next non-blank value;
                for ($i=1;$i<sizeof($vargs);$i++)
                    if ($vargs[$i] != '')
                        break;

                // if $vargs[$i] is one of the following, we are trying to change the
                // size of the field, if not allowed, simply ignore the request.
                if (in_array(substr($vargs[$i], 0, 4), $invalidTypes))
                    continue;
                // insert the appropriate DB2 syntax
                if (in_array(substr($vargs[$i], 0, 4), $validTypes)) {
                    array_splice($vargs, $i, 0, array('SET', 'DATA', 'TYPE'));
                }

                // Now Look for the NOT NULL statement as this is not allowed in
                // the ALTER table statement. If it is in there, remove it
                if (in_array('NOT', $vargs) && in_array('NULL', $vargs)) {
                    for ($i=1;$i<sizeof($vargs);$i++)
                    if ($vargs[$i] == 'NOT')
                        break;
                    array_splice($vargs, $i, 2, '');
                }
                $v = implode(' ', $vargs);
                $sql[] = $alter . $this->alterCol . ' ' . $v;
            } else {
                $sql[] = $alter . $this->addCol . ' ' . $v;
            }
        }

        return $sql;
    }

}
