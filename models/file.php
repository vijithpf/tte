<?php

class File extends Concrete5_Model_File
{

    public static function getByTitle($fTitle)
    {

        $db = Loader::db();
        $row = $db->GetRow("SELECT FileVersions.fID
        FROM FileVersions
        WHERE FileVersions.fvFilename= ?", array($fTitle));

        return $row['fID'];
    }
}