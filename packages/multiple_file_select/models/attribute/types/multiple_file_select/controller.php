<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class MultipleFileSelectAttributeTypeController extends AttributeTypeController
{

    public function getValue()
    {
        $db = Loader::db();
        $row = $db->GetRow("SELECT value, align, vAlign FROM atMultipleFiles WHERE avID = ?", array($this->getAttributeValueID()));
        $images = array();
        if ($row) {
            $fIDs = explode(',', $row['value']);
            $aligns = explode(',', $row['align']);
            $vAligns = explode(',', $row['vAlign']);
            foreach ($fIDs as $i => $fID) {
                $fID = intval($fID);
                if ($fID) {
                    $file = File::getByID($fID);
                    $images[] = array('file' => $file, 'align' => $aligns[$i], 'vAlign' => $vAligns[$i]);
                }
            }
        }
        return $images;
    }

    public function form()
    {
        $al = Loader::helper('concrete/asset_library');
    }

    public function saveValue($fIDs = array(), $align = array(), $vAlign = array())
    {
        $db = Loader::db();
        if (!is_array($fIDs)) $fIDs = array();
        if (!is_array($align)) $align = array();
        if (!is_array($vAlign)) $align = array();
        $cleanFIDs = array();
        foreach ($fIDs as $fID) $cleanFIDs[] = intval($fID);
        $cleanFIDs = array_unique($cleanFIDs);
        $cleanAlign = array();
        $cleanVAlign = array();
        foreach ($cleanFIDs as $fID) {
            if (!isset($align[$fID]) || !in_array($align[$fID], array('center', 'left', 'right'), true)) {
                $cleanAlign[] = 'center';
            } else {
                $cleanAlign[] = $align[$fID];
            }
            if (!isset($vAlign[$fID]) || !in_array($vAlign[$fID], array('top', 'center', 'bottom'), true)) {
                $cleanVAlign[] = 'center';
            } else {
                $cleanVAlign[] = $vAlign[$fID];
            }
        }
        $db->Replace('atMultipleFiles', array('avID' => $this->getAttributeValueID(), 'value' => join(',', $cleanFIDs), 'align' => join(',', $cleanAlign), 'vAlign' => join(',', $cleanVAlign)), 'avID', true);
    }

    public function deleteKey()
    {
        $db = Loader::db();
        $arr = $this->attributeKey->getAttributeValueIDList();
        foreach ($arr as $id) {
            $db->Execute('DELETE FROM atMultipleFiles WHERE avID = ?', array($id));
        }
    }

    public function saveForm($data)
    {
        $this->saveValue($data['fID'], $data['align'], $data['vAlign']);
    }

    public function deleteValue()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM atMultipleFiles WHERE avID = ?', array($this->getAttributeValueID()));
    }
}
