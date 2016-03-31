<?php
/**
 * Class Import exception object file
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
 * Expecific importacions exception
 */
class ImportException extends \Exception
{

    /**
     * @var integer Number of actual line
     */
    private $_importFileLine;

    /**
     * Main class constructor
     *
     * @param string $message
     * @param string $importFileLine
     *
     * @throws exception
     */
    public function __construct($message = null, $importFileLine=0)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }

        $this->setImportFileLine($importFileLine);

        parent::__construct($message, 0);

    }//end __construct()


    /**
     * Returns actual read fileline
     *
     * @return string
     */
    function getImportFileLine()
    {
        return $this->_importFileLine;

    }//end getImportFileLine()


    /**
     * Sets actual read fileline
     *
     * @param integer $importFileLine
     */
    function setImportFileLine($importFileLine)
    {
        $this->_importFileLine = $importFileLine;

    }//end setImportFileLine()


}//end class
