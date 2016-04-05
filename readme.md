import-export-legacy
========

Introduction
------------
This is a library for importing large csv or fixed-line files, it just needs an
index array and a file path, the object itself is iterable, in each loop it returns
an index corresponding to the read line number and an array with data in the
form of the index array, if something goes wrong it returns an exception.

Installation
------------

You can install the component in the following ways:

* Use the official Github repository (https://github.com/mbarquin/import-export-legacy)

Usage
-----

        $defArray = array (
            'name'    => 20,
            'surname' => 20,
            'phone'   => 10
        );

        $oImport = new mbarquin\LegacyFile\Import('./files/contacts.csv', $defArray);

        $oImport->setIsPseudoCSV(TRUE);

        foreach ($oImport as $line => $data) {
            // This is also a valid check.
            // if(is_a($data, 'Exception') === true)

            if (is_a($data, 'mbarquin\LegacyFile\ImportException') === true) {
                // Error handling
            }

        }

First of all the class needs an index definition, it's an array with the fields name
as index, and in CSV case value is an integer which represents max data length, 
and in fixed-line cases it's the field length to be read. In this example is called defArray.

FileImport class is configured by default to read fixed-line importation files, 
if we want to change this behaviour we must use this method setIsPseudoCSV(TRUE|FALSE) 
to set CSV import file to true.

We also need a file to read, we can use an absolute or relative path, if the file 
not exists or something goes wrong an exception will be raised.

The object throws errors on many cases, but data size validations or fixed-line errors
will not be thrown, they are returned as ImportException objects, error handling is up to you, 
and interrupt or not the rest of the file import, This can 
be configured via setReturnValidationExceptions(TRUE|FALSE) method.

        $oImport = new mbarquin\LegacyFile\Import('./files/contacts.csv', $defArray);
        $oImport->setReturnValidationExceptions(FALSE);
        try {
            foreach ($oImport as $line => $data) {

            }
        } catch(\Exception $e) {
            // Error handling.
        }

If imported file is in another encoding, the class can transcode it via iconv. 
Class has three encoding literals as constants, Import::UTF8
Import::LATIN1 and Import::WINDOWS_OCCI, any encoding iconv literal will be accepted as 
the iconv command will be encoding the text. setTranscodification($from, $to)

        $oImport = new mbarquin\LegacyFile\Import('./files/contacts.csv', $defArray);
        $oImport->setTranscodification(
            mbarquin\LegacyFile\Import::WINDOWS_OCCI, mbarquin\LegacyFile\Import::UTF8
        );

