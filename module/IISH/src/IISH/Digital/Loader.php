<?php
namespace IISH\Digital;
use IISH\Cache\Cacheable;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 *
 * @package IISH\Digital
 */
class Loader extends Cacheable {
	private $type;
	private $ead;
	private $record;
	private $item;
	private $isAudio;
	private $isVideo;
	private $isHires;
	private $isArchivePdf;

    /**
     * Constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator) {
        parent::__construct($serviceLocator, 'Digital');
    }

	/**
	 * Set record code
	 */
	public function setRecord($record) {
		$this->record = $record;
	}

	/**
	 * Set type of document
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Set EAD document
	 */
	public function setEad($ead) {
		$this->ead = $ead;
	}

	/**
	 * Set item number
	 */
	public function setItem($item) {
		$this->item = $item;
	}

	/**
	 * Get item number
	 */
	public function getItem() {
		return $this->item;
	}

	/**
     * The key of the cached record.
     *
     * @return string The key.
     */
    protected function getKey() {
	    $key = $this->record . '_' . $this->item;
        return preg_replace('/[^a-z0-9_\+\-]/i', '_', $key);
	}

	/*
	 * Return array of daoloc hrefs
	 */
	protected function getDaolocHrefs() {
		$daoloc = array();

		$xml = simplexml_import_dom($this->ead);
		$xml->registerXPathNamespace('ead', 'urn:isbn:1-931666-22-9');

		//
		$match = $xml->xpath('//ead:did/ead:unitid[text()="' . $this->item . '"]/../ead:daogrp/ead:daoloc');
		foreach ($match as $key => $value) {
			$daoloc[(string)($value->attributes('http://www.w3.org/1999/xlink')->{'label'})] = (string)($value->attributes('http://www.w3.org/1999/xlink')->{'href'});
		}

		return $daoloc;
	}

	/*
	 * Is document of a specified type
	 */
	protected function isDocumentOfType($xmlDocument, $type) {
		// if type not an array, make it an array
		if ( !is_array($type)) {
			$type = array($type);
		}

		// try to find out if there is a fileGrp with the specified type
		foreach ($xmlDocument->fileSec->fileGrp as $fileGrp) {
			if ( in_array($fileGrp->attributes()->{'USE'}, $type)) {
				return true;
			}
		}

		return false;
	}

	/*
	 * Create array of all audio files of specified type found in the mets document
	 */
	private function createListOfAllAudioVideoFilesInMetsDocument($xmlDocument, $type) {
		$arr = array();

		// if type not an array, make it an array
		if ( !is_array($type)) {
			$type = array($type);
		}

		foreach ($xmlDocument->fileSec->fileGrp as $fileGrp) {
			if ( in_array((string)($fileGrp->attributes()->{'USE'}), $type)) {
				foreach ($fileGrp->file as $file) {
					foreach ($file->FLocat as $flocat) {

						// url to object
						$url = (string)($flocat->attributes('http://www.w3.org/1999/xlink')->{'href'});

						// audio
						if ($this->isAudio) {
							$arr[(string)($file->attributes()->{'ID'})] = array (
								'url' => $url
								, 'contentType' => (string)($file->attributes()->{'MIMETYPE'})
								);
						// video
						} elseif ($this->isVideo) {
							$baseUrl = $this->getUriBasename($url);

							$arr[(string)($file->attributes()->{'ID'})] = array (
								'url' => $url
								, 'contentType' => (string)($file->attributes()->{'MIMETYPE'})
								, 'stillsUrl' => $baseUrl . '?locatt=view:level2'
								, 'thumbnailUrl' => $baseUrl . '?locatt=view:level3'
								);
						}
					}
				}
			}
		}

		return $arr;
	}

	/*
	 * Get PDF link from Mets file
	 */
	protected function getPdfLinkFromMetsFile($xmlDocument) {
		$ret = '';

		// get the href from the archive pdf file group
		foreach ($xmlDocument->fileSec->fileGrp as $fileGrp) {
			if ($fileGrp->attributes()->{'USE'} == 'archive pdf' || $fileGrp->attributes()->{'USE'} == 'archive application' ) {
			    if ($fileGrp->file->attributes()->{'MIMETYPE'} == 'application/pdf'
                    || $fileGrp->file->attributes()->{'MIMETYPE'} == 'application/x-pdf') {
                    $ret = (string)($fileGrp->file->FLocat->attributes('http://www.w3.org/1999/xlink')->{'href'});
                }
			}
		}

		return $ret;
	}

	/*
	 * Get list of audio or video files from xml document
	 */
	protected function getAudioVideoFilesFromMetsFile($xmlDocument) {
		$arr = array();
		$arrOfFptrs = array();
		$physicalProccessed = false;

		// get fptrs
		foreach ($xmlDocument->structMap as $structMap) {
			if ($structMap->attributes()->{'TYPE'} == 'physical' && !$physicalProccessed) {
				foreach ($structMap->div->div as $div) {
					foreach ($div->fptr as $fptr) {
						$arrOfFptrs[(string)($div->attributes()->{'ORDER'})][] = (string)($fptr->attributes()->{'FILEID'});
					}
					$physicalProccessed = true;
				}
			}
		}

		// sort on key
		ksort($arrOfFptrs);

		// get list of all audio files with specified type
		$list = $this->createListOfAllAudioVideoFilesInMetsDocument($xmlDocument, array('reference audio', 'reference video'));

		// create array with urls
		foreach ($arrOfFptrs as $key => $fptrs) {
			foreach ($fptrs as $fptr) {
				if (isset($list[$fptr])) {

					// audio
					if ($this->isAudio) {
						$arr[] = array(
							'url' => $list[$fptr]['url']
							, 'contentType' => $list[$fptr]['contentType']
							, 'order' => $key
							);
					// video
					} elseif ($this->isVideo) {
						$arr[] = array(
							'url' => $list[$fptr]['url']
							, 'contentType' => $list[$fptr]['contentType']
							, 'stillsUrl' => $list[$fptr]['stillsUrl']
							, 'thumbnailUrl' => $list[$fptr]['thumbnailUrl']
							, 'order' => $key
							);
					}
				}
			}
		}

		return $arr;
	}

	/*
	 *
	 */
	protected function setIsAudioVideoHiresOrArchivePdf($xmlDocument) {
		$this->isAudio = $this->isDocumentOfType($xmlDocument, array('reference audio'));
		$this->isVideo = $this->isDocumentOfType($xmlDocument, array('reference video'));
		$this->isHires = $this->isDocumentOfType($xmlDocument, array('hires reference image'));
		$this->isArchivePdf = $this->isDocumentOfType($xmlDocument, array('archive pdf', 'archive application'));
	}

	/*
	 * Create dataset
	 */
	protected function createDataset($xmlDocument, $pdfUrl, $metsUrl) {
		// check wath type of document it is
		$this->setIsAudioVideoHiresOrArchivePdf($xmlDocument);

		// check if mets and pfd file exist
		if ( !$this->checkRemoteFileExists($metsUrl)) {
			$metsUrl = '';
		}
		if ( !$this->checkRemoteFileExists($pdfUrl)) {
			$pdfUrl = '';
		}

		if ( empty($metsUrl)) {
			// if empty, return null
            return null;
		} else {

            if ($this->isArchivePdf) {
                    // ARCHIVE PDF

                    // try to find pdf link in mets file
                $pdf = $this->getPdfLinkFromMetsFile($xmlDocument);
                // if no pdf link create link
                if ($pdf == '') {
                    $pdf = 'https://hdl.handle.net/10622/' . $this->item . '?locatt=view:pdf';
                }

                //
                return array(
                    'pdf' => ($this->checkRemoteFileExists($pdf) ? $pdf : null)
                , 'view' => null
                );
            } elseif ($this->isAudio || $this->isVideo) {
				// AUDIO VIDEO

				// get audio or video files
				$arr = $this->getAudioVideoFilesFromMetsFile($xmlDocument);

				// if audio return no pdf link
				if ($this->isAudio) {
					$pdfUrl = '';
				}

				return array(
					'pdf' => ($this->checkRemoteFileExists($pdfUrl) ? $pdfUrl : null)
					, 'view' => array(
						'mets' => ($this->checkRemoteFileExists($metsUrl) ? $metsUrl : null)
						, 'items' => $arr
					));
			} elseif ($this->isHires) {
				// HIRES SCANS
				return array(
					'pdf' => ($this->checkRemoteFileExists($pdfUrl) ? $pdfUrl : null)
					, 'view' => array(
						'mets' => ($this->checkRemoteFileExists($metsUrl) ? $metsUrl : null)
						, 'items' => null
					));
			} else {
				// ELSE
				return array();
			}
		}
	}

	/*
	 * Return output for EAD
	 */
	protected function createEad() {
		$xmlDocument = '';

		// load the daoloc hrefs
		$daoloc = $this->getDaolocHrefs();

		// check if there is a mets document
		if ( isset($daoloc['mets']) && !empty($daoloc['mets'])) {
			// load mets document
			$xmlDocument = simplexml_load_file($daoloc['mets']);
		}

		// return dataset
		return $this->createDataset($xmlDocument, $daoloc['pdf'], $daoloc['mets']);
	}

	/*
	 * Return output for Marc
	 */
	protected function createMarc() {
		// urls
		$mainUrl = "https://hdl.handle.net/10622/" . $this->item;
		$pdfUrl = $mainUrl . '?locatt=view:pdf';
		$metsUrl =  $mainUrl. '?locatt=view:mets';

		// load mets document
		$xmlDocument = simplexml_load_file($metsUrl);

		// return dataset
		return $this->createDataset($xmlDocument, $pdfUrl, $metsUrl);
	}

    /**
     * Loads the record.
     *
     * @return mixed
     */
    protected function create() {
	    // check type of document
	    if ( !in_array($this->type, array('ead', 'marc'))) {
	    	$this->type = 'marc';
	    }

	    // create EAD or Marc
		switch ($this->type) {
			case "ead":
				return $this->createEad();
				break;
			default:
				return $this->createMarc();
		}
    }

    /*
     * create List
     */
    public function getList() {
		return $this->get();
    }

    /*
     * Check if remote file exists
     * @source http://stackoverflow.com/questions/981954/how-can-one-check-to-see-if-a-remote-file-exists-using-php
     * modified version of function
     */
	protected function checkRemoteFileExists($url) {
		if ($url == '') {
			return false;
		}

		$ret = false;

		$curl = curl_init($url);

		// don't fetch the actual page, you only want to check the connection is ok
		curl_setopt($curl, CURLOPT_NOBODY, true);
		// timeout is not necessary !
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); // The maximum number of seconds to allow cURL functions to execute.

		// do request
		$result = curl_exec($curl);

		// if request did not fail
		if ($result !== false) {
			// if request was ok, check response code
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			// REMARK: check not only for 200 but also for 301, 302 and 303 (redirects)
			if ( in_array($statusCode, array('200', '301', '302', '303') )) {
				$ret = true;
			}
		}

		curl_close($curl);

		return $ret;
	}

	/*
	 * Get URI without query parameters
	 */
	protected function getUriBasename($url) {
		$parts = parse_url($url);
		$str = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];

		return $str;
	}
}