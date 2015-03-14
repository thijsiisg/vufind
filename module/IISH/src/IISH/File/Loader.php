<?php
namespace IISH\File;

use Zend\Log\LoggerInterface;

/**
 * Asset handler for PDF files
 *
 * @package IISH\File
 */
class Loader implements \Zend\Log\LoggerAwareInterface
{

    private $cache = '/usr/local/vufind/local/cache/pdf/' ;

    /**
     * Logger (or false for none)
     *
     * @var LoggerInterface|bool
     */
    protected $logger = false;

    protected $ttl = 86400;

    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Property for storing raw file data; may be null if the file is unavailable
     *
     * @var string
     */
    protected $file = null;

    public function getFile()
    {
        return $this->file;
    }

    public function hasFile() {
        return ($this->file);
    }

    /**
     * Display the default "access closed" graphic and terminate execution.
     */
    public function loadFile($id)
    {
        $this->file = file_get_contents($this->cache . $id);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}