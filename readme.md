docker-php-init
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

        }
