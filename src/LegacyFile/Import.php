<?php
/**
 * Class file Import file
 *
 * PHP version 5.3
 *
 *
 * @category   LegacyFile
 * @package    src
 * @subpackage Import
 * @author     Moises Barquin <moises.barquin@gmail.com>
 * @copyright  Moises Barquin 2016
 * @version    GIT: $Id$
 */

namespace mbarquin\LegacyFile;

/**
 * Class to help on importation proccess
 */
class Import extends FileProcess implements \Iterator
{
    /**
     * @var string Keeps last read file line
     */
    protected $_readReg;


    /**
     * Main class constructor, sets file path
     *
     * @param string $filePath Absolute path to importation file
     * @param array $fieldsArray Array with fields name and length
     */
    public function __construct($filePath, $fieldsArray)
    {
        parent::__construct($filePath, $fieldsArray, 'r');

    }//end __construct


    /**
     * Read line and add to position while not eof
     *
     * @return boolean
     */
    private function readNextLine()
    {
        if(feof($this->_filePtr) === false) {
            if($this->getIsPseudoCSV() === true) {
                $csvConf = $this->getCsvDefaults();
                $this->_readReg = fgetcsv(
                    $this->_filePtr, 0, $csvConf['separator'], 
                        $csvConf['enclosure'], $csvConf['escape']
                );
            } else {
                $this->_readReg = fgets($this->_filePtr);
                // remove carriage returns
                if($this->_removeReturns === true) {
                    $this->_readReg = str_replace("\r", '', $this->_readReg);
                    $this->_readReg = str_replace("\n", '', $this->_readReg);
                }
            }
            ++$this->_position;
            return true;
        } else {
            return false;
        }

    }//end readNextLine()


    /**
     * Reset pointer
     *
     * @return boolean
     */
    public function rewind()
    {
        $this->_position = 0;
        if($this->_filePtr !== null) {
            rewind($this->_filePtr);
            return $this->readNextLine();
        } else {
            $this->openFile();
            return $this->readNextLine();
        }

    }//end rewind()


    /**
     * Returns a splitted _readReg line depending on import type
     *
     * @throws \Exception
     *
     * @return array
     */
    public function regToArray()
    {
        if((feof($this->_filePtr) === false && empty($this->_readReg) === true) || $this->_readReg === false) {
            $mess = _('Empty line.');
            throw new ImportException($mess, $this->_position);
        } elseif(feof($this->_filePtr) === false && is_array($this->_readReg) === true && count($this->_readReg) === 1 && empty($this->_readReg[0]) === true) {
            $mess = _('Empty line.');
            throw new ImportException($mess, $this->_position);
        }

        if($this->getIsPseudoCSV() === true) {
            return $this->readLineAsPseudoCSV();
        } else {
            return $this->readLineAsFixedPosition();
        }

    }//end regToArray()


    /**
     * Splits _readReg depending on length configuration array
     *
     * @throw \Exception Incongruent number of fields
     *
     * @return array
     */
    private function readLineAsPseudoCSV()
    {
        //$keys = array_keys($this->_fields);
        //$this->_readReg = array_combine($keys, $this->_readReg);

        // Check errors on read line
        if ( $this->_checkFields === true) {
            $this->checkFieldsErrors($this->_readReg, false, true);
        } else {
            $keys           = array_keys($this->_fields);
            $this->_readReg = array_combine($keys, $this->_readReg);
        }

        return $this->_readReg;
    }//end readLineAsPseudoCSV()


    /**
     * Splits _readReg depending on length configuration array
     *
     * @return array
     */
    private function readLineAsFixedPosition()
    {
        $actualPosition = 0;
        $auxResult      = array();
        if(strlen($this->_readReg) !== array_sum($this->_fields)) {
            $mess = _('Line length error').', '.strlen($this->_readReg).' Chars of '.array_sum($this->_fields);
            throw new ImportException($mess, $this->_position);
        }

        foreach($this->_fields as $name => $regLength) {
            if( ($actualPosition + $regLength) <= strlen($this->_readReg)) {
                $auxResult[$name] = substr($this->_readReg,
                    $actualPosition,
                    $regLength
                );

                $actualPosition = $actualPosition + $regLength;
            }
        }//end foreach

        if ($this->_checkFields === true ) {
            $this->checkFieldsErrors($auxResult, true);
        }

        return $auxResult;

    }//end readLineAsFixedPosition()


    /**
     * Gets actual row
     *
     * @return array
     */
    public function current()
    {
        // Line error control
        if($this->_memDebug === true) {
            $this->setMaxMemoryUsage();
        }

        try {
            return $this->regToArray($this->_readReg);
        } catch(ImportException $excpt) {
            return $this->returnThrowException($excpt);
        }

    }//end current()

    /**
     * Checks if validation ImportExceptions must be returned or thrown.
     *
     * @param mbarquin\LegacyFile\ImportException $excpt Validation exception
     *
     * @return mbarquin\LegacyFile\ImportException|void
     * @throws mbarquin\LegacyFile\ImportException
     */
    public function returnThrowException($excpt)
    {
        if($this->_returnValidationExceptions === true) {
            return $excpt;
        } else {
            throw $excpt;
        }

    }// End returnThrowException()

    /**
     * Gets actual pointer position
     *
     * @return mixed
     */
    public function key()
    {
        return $this->_position;

    }//end key()


    /**
     * Next row
     *
     * @return boolean
     */
    public function next()
    {
        if($this->_filePtr !== null) {
            if(feof($this->_filePtr) === false) {
                return $this->readNextLine();
            } else {
                $this->_readReg = '';
                return true;
            }
        } else {
            $this->openFile();
            return $this->readNextLine();
        }

    }//end next()


    /**
     * Returns true if actual position element is setted
     *
     * @return boolean
     */
    public function valid()
    {
        // Read is finished when we have returned the last read line.
        // 1 looop plus after feof.
        if (feof($this->_filePtr) === true && ($this->_readReg === '' || $this->_readReg === false) ) {
            return false;
        } else {
            return true;
        }

    }//end valid()


    /**
     * Returns actual read line number
     *
     * @return int
     */
    public function getActualLine()
    {
        return $this->_position;

    }//end getActualLine()


}//end class