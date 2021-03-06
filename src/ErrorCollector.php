<?php

namespace HQ\ErrorCollector;

use HQ\ErrorCollector\Storage\IErrorStorage;
use Nette\Object;

/**
 * Error collector to S3
 *
 * @author Jetsada Machom <jetsada.machom@hotelquickly.com>
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 *
 */
class ErrorCollector extends Object {

	const EXCEPTION_FILE_TYPE = 'exception';
	const LOG_FILE_TYPE = 'log';

	/** @var IErrorStorage */
	private $errorStorage;

	/** @var string */
	private $logDirectory;

	/** @var  array */
	private $collectFileTypes;

	public function __construct(
		$logDirectory,
		array $collectFileTypes,
		IErrorStorage $errorStorage = null
	) {
		$this->logDirectory = $logDirectory;
		$this->collectFileTypes = $collectFileTypes;
		$this->errorStorage = $errorStorage;
	}

	public function setErrorStorage(IErrorStorage $errorStorage)
	{
		$this->errorStorage = $errorStorage;
	}

	public function uploadFiles()
	{
		if (!is_dir($this->logDirectory)) {
			throw new \Nette\InvalidStateException('Uknown log directory: "' . $this->logDirectory . '"');
		}

		if (!$this->errorStorage) {
			throw new \Nette\InvalidStateException('No error storage set.');
		}

		$files = $this->findFiles($this->collectFileTypes, $this->logDirectory);
		$cnt = 0;
		/** @var \SplFileInfo $file */
		foreach ($files as $file) {

			$filePath = realpath($this->logDirectory . '/' . $file->getFilename());

			$fileType = $this->getFileType($file);

			$fileName = $file->getFilename();
			if ($fileType === self::LOG_FILE_TYPE) {
				$now = new \DateTime();
				// add time extension to filename
				$fileName = $file->getBasename('.log') . '-' . $now->format('Y-m-d-H-i-s') . '.log';
			}

			if (!is_file($filePath)) {
				// some other process already collected it?
				continue;
			}

			try {
				if ($this->errorStorage->save($fileName, $filePath, $fileType)) {
					@unlink($filePath);
				}
			} catch (\Aws\Common\Exception\InvalidArgumentException $e) {
				if ($e->getMessage() == 'You must specify a non-null value for the Body or SourceFile parameters.') {
					// trying to upload empty file. Just ignore
					@unlink($filePath);
				}
				throw $e;
			}


			$cnt++;
		}

		return $cnt;
	}


	public function findFiles($pattern, $path)
	{
		return \Nette\Utils\Finder::findFiles($pattern)->in($path);
	}


	public function getFileType(\SplFileInfo $file)
	{
		switch ($file->getExtension()) {
			case 'log':
				return self::LOG_FILE_TYPE;
				break;
			case 'html':
				return self::EXCEPTION_FILE_TYPE;
				break;
			default:
				throw new \InvalidArgumentException('Error collector does not handle files with extension: ' . $file->getExtension());
		}
	}
}
