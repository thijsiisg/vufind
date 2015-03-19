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

    private $cache = '/usr/local/vufind/local/cache/pdf/';

    /**
     * Logger (or false for none)
     *
     * @var LoggerInterface|bool
     */
    protected $logger = false;

    /**
     * The filename, without the path.
     */
    protected $filename = null;

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }


    protected $ttl = 86400;

    public function getTtl()
    {
        return $this->ttl;
    }

    public function getFile()
    {
        return file_get_contents($this->cache . $this->filename);
    }

    public function hasFile()
    {
        return file_exists($this->cache . $this->filename);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}