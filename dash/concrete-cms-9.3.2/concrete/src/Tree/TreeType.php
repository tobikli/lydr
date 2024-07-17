<?php
namespace Concrete\Core\Tree;

use Concrete\Core\Foundation\ConcreteObject;
use Concrete\Core\Package\PackageList;
use Core;
use Database;

class TreeType extends ConcreteObject
{
    public function getTreeTypeID()
    {
        return $this->treeTypeID;
    }

    public function getTreeTypeHandle()
    {
        return $this->treeTypeHandle;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    public function getPackageHandle()
    {
        return PackageList::getHandle($this->pkgID);
    }

    public static function add($treeTypeHandle, $pkg = false)
    {
        $pkgID = 0;
        $db = Database::connection();
        if (is_object($pkg)) {
            $pkgID = $pkg->getPackageID();
        }

        $r = $db->query("insert into TreeTypes (treeTypeHandle, pkgID) values (?, ?)", array(
            $treeTypeHandle, $pkgID,
        ));

        $treeTypeID = $db->Insert_ID();

        return self::getByID($treeTypeID);
    }

    public function delete()
    {
        $db = Database::connection();
        $db->Execute('delete from TreeTypes where treeTypeID = ?', array($this->treeTypeID));
    }

    public static function getByID($treeTypeID)
    {
        $db = Database::connection();
        $row = $db->GetRow('select * from TreeTypes where treeTypeID = ?', array($treeTypeID));
        if (is_array($row) && $row['treeTypeID']) {
            $type = new static();
            $type->setPropertiesFromArray($row);

            return $type;
        }
    }

    public static function getByHandle($treeTypeHandle)
    {
        $db = Database::connection();
        $row = $db->GetRow('select * from TreeTypes where treeTypeHandle = ?', array($treeTypeHandle));
        if (is_array($row) && isset($row['treeTypeHandle'])) {
            $type = new static();
            $type->setPropertiesFromArray($row);

            return $type;
        }
    }

    public function getTreeTypeClass()
    {
        $txt = Core::make('helper/text');
        $className = '\\Concrete\\Core\\Tree\\Type\\' . $txt->camelcase($this->treeTypeHandle);

        return $className;
    }

    public static function getListByPackage($pkg)
    {
        $db = Database::connection();
        $list = array();
        $r = $db->Execute('select treeTypeID from TreeTypes where pkgID = ? order by treeTypeID asc', array($pkg->getPackageID()));
        while ($row = $r->fetch()) {
            $list[] = self::getByID($row['treeTypeID']);
        }

        $r->free();

        return $list;
    }

    public static function getList()
    {
        $db = Database::connection();
        $list = array();
        $r = $db->Execute('select treeTypeID from TreeTypes order by treeTypeID asc');
        while ($row = $r->fetch()) {
            $list[] = self::getByID($row['treeTypeID']);
        }

        return $list;
    }

    public function export(\SimpleXMLElement $node)
    {
        $node = $node->addChild('treetype');
        $node->addAttribute('handle', $this->getTreeTypeHandle());
        $node->addAttribute('package', $this->getPackageHandle());
        return $node;
    }
}
