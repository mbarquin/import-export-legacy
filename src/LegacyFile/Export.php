<?php
/**
 * Class file export file
 *
 * PHP version 5.3
 *
 *
 * @category   LegacyFile
 * @package    src
 * @subpackage Export
 * @author     Moises Barquin <moises.barquin@gmail.com>
 * @copyright  Moises Barquin 2016
 * @version    GIT: $Id$
 */

namespace mbarquin\LegacyFile;


/**
 * Class to help on importation proccess
 */
class Export extends FileProcess
{


    /**
     * Main class constructor, sets file path
     *
     * @param string $filePath Absolute path to importation file
     * @param array $fieldsArray Array with fields name and length
     */
    public function __construct($filePath, $fieldsArray)
    {
        parent::__construct($filePath, $fieldsArray, 'w+');

    }//end __construct


    /**
     * Returns a splitted _readReg line depending on import type
     *
     * @throws \Exception
     *
     * @return array
     */
    public function regToFixedString($regArray)
    {
        $strSend = '';
        foreach($this->_fields as $fieldK => $fieldData) {
            if(isset($regArray[$fieldK]) === true) {
                $procData = $this->parseResult($regArray[$fieldK]);
                $procData = $this->mb_str_pad($procData, $fieldData);
                $strSend .= $procData;
            } else {
                $procData = $this->mb_str_pad('', $fieldData);
                $strSend .= $procData;
            }
        }
        return $strSend;
    }//end regToArray()


    /**
     * Writes a line to the file from an Array
     *
     * @param array $regArray
     *
     * @return boolean
     */
    public function writeRow($regArray)
    {
        if($this->_isPseudoCSV === true) {
            if(fputcsv($this->_filePtr, $regArray, $this->_csvSeparator) !== false) {
                return true;
            }
        } else {
            $strToWrite = $this->regToFixedString($regArray);
            if($strToWrite !== false) {
                $strToWrite .= $this->_eolInUse;
                if(fwrite($this->_filePtr, $strToWrite) !== false) {
                    return true;
                }
            }
        }

        return false;
    }// End writeRow()


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