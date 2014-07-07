<?php

namespace HQ\ErrorCollector\Storage;

use HQ\AWSProxy\S3Proxy;

/**
 * Storage for exception files implemented on S3
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class S3Storage implements IErrorStorage {

	/** @var \HQ\AWSProxy\S3Proxy */
	private $s3proxy;

	public function __construct(
		$projectName,
		$s3Bucket,
		S3Proxy $s3Proxy
	) {
		$this->s3proxy = $s3Proxy;
		$this->s3proxy->setBucket($s3Bucket);

		$this->projectName = $projectName;
	}


	public function save($fileName, $localFilePath, $type)
	{
		$targetKey = $this->projectName . '/' . $type . '/' . $fileName;
		return $this->s3proxy->uploadFile($localFilePath, $targetKey);
	}
}
