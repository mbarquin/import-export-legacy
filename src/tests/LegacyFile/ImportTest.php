<?php
use mbarquin\LegacyFile\Import;

class ImportTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $defArray = array (
            'name'    => 20,
            'surname' => 20,
            'phone'   => 10
        );
        $oImport = new Import('./files/contacts.csv', $defArray);
        $this->assertInstanceOf('mbarquin\LegacyFile\Import', $oImport);
    }

}
