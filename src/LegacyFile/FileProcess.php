<?php
/**
 * Generic file processing class file
 *
 * PHP version 5.3
 *
 *
 * @category   LegacyFile
 * @package    src
 * @subpackage Exception
 * @author     Moises Barquin <moises.barquin@gmail.com>
 * @copyright  Moises Barquin 2016
 * @version    GIT: $Id$
 */

namespace mbarquin\LegacyFile;

const UTF8         = 'UTF-8';
const LATIN1       = 'ISO-8859-1';
const WINDOWS_OCCI = 'CP1252';

const EOL_WINDOWS  = "\r\n";
const EOL_LINUX    = "\n";

/**
 * Generic file processing class
 */
class FileProcess
{
    /**
     * Sets utf-8 encoding literal for ICONV
     */
    const UTF8   = 'UTF-8';

    /**
     * Sets latin1 encoding literal for ICONV
     */
    const LATIN1 = 'ISO-8859-1';

    /**
     * Sets windows encoding literal for ICONV
     */
    const WINDOWS_OCCI = 'CP1252';

    /**
     * Sets windows carriage returns literals to be used in files
     */
    const EOL_WINDOWS = "\r\n";

    /**
     * Sets linux carriage returns literals to be used in files
     */
    const EOL_LINUX = "\n";

    /**
     * When check memory consumption
     *
     * @var bool
     */
    protected $_memDebug;

    /**
     * Used to store max system memory usage in each iteration
     *
     * @var int
     */
    protected $_maxRealMemoryUsage;

    /**
     * Used to store max internal memory usage in each iteration
     *
     * @var int
     */
    protected $_maxInternalMemoryUsage;

    /**
     * @var string File Path as string
     */
    protected $_filePath;

    /**
     * @var array Array with fields and its lengths
     */
    protected $_fields;

    /**
     * @var type Sets transcodification type source
     */
    protected $_transcodeFrom;

    /**
     * @var type Sets transcodification type destination
     */
    protected $_transcodeTo;

    /**
     * @var resource PHP resource, file pointer
     */
    protected $_filePtr = null;

    /**
     * @var boolean When apply a trim function on returned array values
     */
    protected $_trimResult = true;

    /**
     * @var boolean When apply a trim function on returned array values
     */
    protected $_checkFields = true;

    /**
     * @var bool Sets if importation it's in non-standard PSA CSV
     */
    protected $_isPseudoCSV = false;

    /**
     * @var bool Sets if it's necessary to remove carriage returns from lines
     */
    protected $_removeReturns = true;


    /**
     * @var string Defines which EOL is in use
     */
    protected $_eolInUse = self::EOL_LINUX;


    /**
     * Sets which characters will be used on file line endings
     *
     * @param string $newEOL
     */
    public function setEndOfLine($newEOL)
    {
        $this->_eolInUse = $newEOL;
    }// End setEndOfLine()


    /**
     * Sets which characters will be used on file line endings
     *
     * @param string $newEOL
     */
    public function getEndOfLine()
    {
        return $this->_eolInUse;
    }// End getEndOfLine()


    /**
     * Sets when perform memory usage statistics
     *
     * @param boolean $memDebug
     */
    public function setMemDebug($memDebug=true)
    {
        $this->_memDebug = $memDebug;
    }// End setMemDebug()


    /**
     * Gets when perform memory usage statistics
     *
     * @return boolean
     */
    public function getMemDebug()
    {
        return $this->_memDebug;
    }// End getMemDebug()


    /**
     * Returns max real memory usage
     *
     * @return integer
     */
    public function getMaxRealMemoryUsage()
    {
        return $this->_maxRealMemoryUsage;
    }// End getMaxRealMemoryUsage()


    /**
     * Returns internal memory usage
     *
     * @return integer
     */
    public function getMaxInternalMemoryUsage()
    {
        return $this->_maxInternalMemoryUsage;
    }// End getMaxInternalMemoryUsage()


    /**
     * Main class constructor, sets file path
     *
     * @param string $filePath Absolute path to importation file
     * @param array $fieldsArray Array with fields name and length
     * @param string $mode Fopen mode a a+ r ...
     */
    public function __construct($filePath, $fieldsArray, $mode='r')
    {
        if (getenv('ENV') === 'development' || getenv('ENV') === 'local') {
            $this->_memDebug = true;
        }

        $this->setFilePath($filePath);

        if (is_array($fieldsArray) === true && empty($fieldsArray) === false) {
            $this->setFields($fieldsArray);
        }

        // Avoid \r\n double lines.
        ini_set("auto_detect_line_endings", true);

        $this->openFile($mode);

    }//end __construct


    /**
     * Sets removeReturns to true in order to avoid carriage returns
     *
     * @return bool
     */
    function getRemoveReturns()
    {
        return $this->_removeReturns;

    }//end getRemoveReturns()


    /**
     * Gets if it's necessary to avoid carriage returns
     *
     * @param bool $removeReturns
     */
    function setRemoveReturns($removeReturns)
    {
        $this->_removeReturns = $removeReturns;

    }//end setRemoveReturns()


    /**
     * Sets absolute file path
     *
     * @param string $filePath
     * @throws Exception
     *
     * @return void
     */
    public function setFilePath($filePath)
    {
        $this->_filePath = $filePath;

    }//end setFilePath()


    /**
     * _transcodeFrom and _transcodeTo setter
     *
     * @param string $from Source transcodification identifier
     * @param string $to   Target transcodification identifier
     */
    public function setTranscodification($from, $to)
    {
        if(empty($from) === true || empty($to) === true) {
            // Transcodification Source and Target must exist.
            $mess = _('Source and target codification '
                .'must be defined.');

            throw new \Exception($mess, '00002');

        } else {
            $this->_transcodeFrom = $from;
            $this->_transcodeTo   = $to;
        }

    }//end setTranscodification(


    /**
     * Sets fields configuration array ( 'nomField' => 'length' )
     *
     * @param array $fields Array with field names and lengths
     * @throws Exception
     *
     * return void
     */
    public function setFields($fields)
    {
        foreach ($fields as $key => $field) {
            if ((empty($field) === false
                && empty($key) === false
                && is_numeric($field) === true) || ($field === null)
            ) {
                $this->_fields[$key] = $field;
            } else {
                // if it's explicity null we check it as text
                // Array key must be the field name, and array value must be its length value as integer.
                $mess = _('Definition Array keys (%s) must be the field name'
                    .', and array values must be its length value as integer.');
                $mess = sprintf($mess, $key);
                throw new \Exception($mess, '0001');

            }
        }
    }//end setFields()


    /**
     * Sets configured fields array to empty
     */
    public function resetFields()
    {
        $this->_fields = array();

    }//end resetFields


    /**
     * Opens a PHP resource pointer to file
     *
     * @param string $mode Fopen mode to use
     */
    protected function openFile($mode)
    {
        $this->_filePtr = fopen($this->_filePath, $mode);

    }//end openFile()



    /**
     * Sets _isPseudoCSV flag
     * True if importation file is on PSA non-standard CSV format
     *
     * @return bool
     */
    public function getIsPseudoCSV()
    {
        return $this->_isPseudoCSV;

    }//end getIsPseudoCSV()


    /**
     * Returns _isPseudoCSV flag value
     * True if importation file is on PSA non-standard CSV format
     *
     * @param bool $pseudoCSV
     */
    public function setIsPseudoCSV($pseudoCSV)
    {
        $this->_isPseudoCSV = $pseudoCSV;

    }//end setIsPseudoCSV()


    /**
     * Gets the flag to order fields checks
     *
     * @return bool
     */
    public function getCheckFields()
    {
        return $this->_checkFields;

    }//end getCheckFields()


    /**
     * Sets the flag to implement checks per field
     *
     * @return bool
     */
    public function setCheckFields($_checkFields)
    {
        $this->_checkFields = $_checkFields;

    }//end setCheckFields()


    /**
     * _trimResult property getter
     * Use or no trim function on reg array values.
     *
     * @return boolean
     */
    public function getTrimResult()
    {
        return $this->_trimResult;

    }// End getTrimResult()


    /**
     * _trimResult property setter
     * Use or no trim function on reg array values.
     *
     * @param bool $_trimResult
     */
    public function setTrimResult($_trimResult)
    {
        $this->_trimResult = $_trimResult;

    }// End setTrimResult()


    /**
     * Checks possible file fields errors
     *
     * @param array $arrResult
     * @param bool $strictLength
     * @param bool $combine If true both arrays (data and keys) must be array_combined
     *
     * @return boolean
     * @throws ImportException
     */
    protected function checkFieldsErrors(&$arrResult, $strictLength = false, $combine = false)
    {
        // Check fields count.
        if(count($arrResult) !== count($this->_fields)) {
            // Read fields count is not equal to fields config array count.
            $mess = _('Read fields count is not equal to fields definition array count.').' '.count($arrResult).'!='.count($this->_fields);

            throw new ImportException($mess, $this->_position);
        }

        if($combine === true) {
            $keys           = array_keys($this->_fields);
            $this->_readReg = array_combine($keys, $this->_readReg);
        }

        foreach($arrResult as $fieldName => $fieldValue) {
            // Check fields length.
            if(mb_strlen($fieldValue) > (int)$this->_fields[$fieldName]
                    && $this->_fields[$fieldName] !== null && $strictLength === false) {

                // Read field length is greater than field length declared on config array
                $mess = _('Read field length is greater than'
                    .' the length declared for this field in definition array');

                throw new ImportException($mess.'('.$fieldName.', '.mb_strlen($fieldValue).')', $this->_position);
            }
            if(mb_strlen($fieldValue) <> (int)$this->_fields[$fieldName]
                    && $this->_fields[$fieldName] !== null  && $strictLength === true) {
                $mess = _('Read field length is different from '
                    .'the length declared for this field in definition array');

                throw new ImportException($mess.'('.$fieldName.', '.mb_strlen($fieldValue).')', $this->_position);
            }
            // Apply transco and trim on each value.
            $arrResult[$fieldName] = $this->parseResult($fieldValue);
        }// End foreach
        return true;

    }//end checkFieldsErrors()


    /**
     * Parses a result applying trim and iconv for Transcodification
     *
     * @param mixed $result Value to Parse
     *
     * @return mixed
     */
    protected function parseResult($result)
    {
        if ($this->getTrimResult() === true) {
            $result = trim($result);
        }

        if (empty($this->_transcodeFrom) === false
            && empty($this->_transcodeTo) === false) {
            $result = iconv($this->_transcodeFrom, $this->_transcodeTo, $result);
        }

        return $result;
    }//end parseResult()


    /**
     * Performs a str_pad on a multibyte string
     *
     * @param string $input
     * @param integer $pad_length
     * @param integer $pad_string
     * @param string $pad_type
     *
     * @return string
     */
    function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
    {
        $diff = strlen($input) - mb_strlen($input,$this->_transcodeTo);

        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);

    }// End mb_str_pad()


    /**
     * Checks memory in use to set up max used value
     */
    public function setMaxMemoryUsage()
    {
        $maxInt = memory_get_usage(false);
        if($this->_maxInternalMemoryUsage < $maxInt) {
            $this->_maxInternalMemoryUsage = $maxInt;
        }

        $maxReal = memory_get_usage(true);
        if($this->_maxRealMemoryUsage < $maxReal) {
            $this->_maxRealMemoryUsage = $maxReal;
        }

    }// End setMaxMemoryUsage()


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