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

/**
 * Generic file processing class
 */
abstract class FileProcess extends DebugProcess
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
     * Sets if validation ImportExceptions are returned or thrown
     *
     * @var boolean
     */
    protected $_returnValidationExceptions = true;

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
     * @var bool Sets if importation it's in non-standard CSV
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
     * @var string Csv fields separator
     */
    protected $_csvSeparator = ';';

    /**
     * @var string Csv fields separator
     */
    protected $_csvEnclosure = '"';
    
    /**
     * @var string Csv fields separator
     */
    protected $_csvEscape = '\\';
    
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
     * Ends write file process closing file pointer.
     * 
     * @return void
     */
    public function end() {
        fclose($this->_filePtr);
    }// End end()
    
    
    /**
     * Main class constructor, sets file path
     *
     * @param string $filePath Absolute path to importation file
     * @param array $fieldsArray Array with fields name and length
     * @param string $mode Fopen mode a a+ r ...
     */
    public function __construct($filePath, $fieldsArray, $mode='r')
    {
        parent::__construct();
        
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
    public function getRemoveReturns()
    {
        return $this->_removeReturns;

    }//end getRemoveReturns()


    /**
     * Gets if it's necessary to avoid carriage returns
     *
     * @param bool $removeReturns
     */
    public function setRemoveReturns($removeReturns)
    {
        $this->_removeReturns = $removeReturns;

    }//end setRemoveReturns()

    /**
     * Sets fgetCSV defaults
     * 
     * @param string $csvSeparator CSV field separator
     * @param string $enclosure    Fields with special chars are encapsulated in.
     * @param string $escape       Sets the escape character (one character only)
     */
    public function setCsvDefaults($csvSeparator = ";",  $enclosure = '"', $escape = '\\') 
    {
        $this->_csvSeparator = $csvSeparator;
        $this->_csvEnclosure = $enclosure;
        $this->_csvEscape    = $escape;
    }

    /**
     * Returns fgetCSV defaults as array
     * 
     * @return array
     */
    public function getCsvDefaults() 
    {
        return array ( 
            "separator" => $this->_csvSeparator,
            "enclosure" => $this->_csvEnclosure,
            "escape"    => $this->_csvEscape
        );
    }
    
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
            $mess = _('Source and target codification must be defined.');

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
     * True if importation file is on non-standard CSV format
     *
     * @return bool
     */
    public function getIsPseudoCSV()
    {
        return $this->_isPseudoCSV;

    }//end getIsPseudoCSV()


    /**
     * Returns _isPseudoCSV flag value
     * True if importation file is on non-standard CSV format
     *
     * @param bool $pseudoCSV
     */
    public function setIsPseudoCSV($pseudoCSV)
    {
        $this->_isPseudoCSV = $pseudoCSV;

    }//end setIsPseudoCSV()

    /**
     * Gets if validation ImportExceptions are returned or thrown
     *
     * @return boolean
     */
    public function getReturnValidationExceptions()
    {
        return $this->_returnValidationExceptions;
    }

    /**
     * Sets if validation ImportExceptions are returned or thrown
     *
     * @param boolean $returnValidationExceptions
     */
    public function setReturnValidationExceptions($returnValidationExceptions)
    {
        $this->_returnValidationExceptions = $returnValidationExceptions;
    }

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
     * Returns actual read line number
     *
     * @return int
     */
    public function getActualLine()
    {
        return $this->_position;

    }//end getActualLine()


}//end class