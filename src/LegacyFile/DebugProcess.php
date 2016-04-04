<?php
/**
 * Debug process memory usage class file
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
 * Debug process memory usage class
 */
 class DebugProcess {
     
    /**
     * When check memory consumption
     *
     * @var bool
     */
    protected $_memDebug;

    /**
     * Sets if validation ImportExceptions are returned or thrown
     *
     * @var boolean
     */
    protected $_returnValidationExceptions = true;

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
 }
