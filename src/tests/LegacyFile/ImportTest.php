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

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testBadPathConstruct()
    {
        $defArray = array (
            'name'    => 20,
            'surname' => 20,
            'phone'   => 10
        );
        $oImport = new Import('./files/contactsFileNotExists.csv', $defArray);
    }

    public function testBadDefinitionConstruct()
    {
        $oImport = new Import('./files/contacts.csv', 'Test');
    }

    public function testReadOkReturnAnArrayConstruct()
    {
        $defArray = array (
            'name'    => 20,
            'surname' => 20,
            'phone'   => 10
        );
        $oImport = new Import('./files/contacts.csv', $defArray);
        $oImport->setIsPseudoCSV(TRUE);
        foreach ($oImport as $line => $data) {
            $this->assertTrue(is_array($data));
        }
    }

    public function testBadSizeExceptionReturned()
    {
        $defArray = array (
            'name'    => 3,
            'surname' => 20,
            'phone'   => 10
        );
        $oImport = new Import('./files/contacts.csv', $defArray);
        $oImport->setIsPseudoCSV(TRUE);
        foreach ($oImport as $line => $data) {
            $this->assertInstanceOf('mbarquin\LegacyFile\ImportException', $data);
        }
    }

    /**
     * @expectedException mbarquin\LegacyFile\ImportException
     */
    public function testBadSizeExceptionThrown()
    {
        $defArray = array (
            'name'    => 3,
            'surname' => 20,
            'phone'   => 10
        );
        $oImport = new Import('./files/contacts.csv', $defArray);
        $oImport->setIsPseudoCSV(TRUE);
        $oImport->setReturnValidationExceptions(false);
        foreach ($oImport as $line => $data) {

        }
    }
}
