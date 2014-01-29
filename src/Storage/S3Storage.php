<?php

namespace HQ\Storage;

/**
 * Storage for exception files implemented on S3
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class S3Storage {

	/** @var HQ\AWSProxy\S3Proxy */
	private $s3Client;

	public function __construct(
		$projectName,
		$s3Bucket,
		S3Client $s3Client
	) {
		$this->s3Client = $s3Client;
		$this->s3Client->setBucket($s3Bucket);

		$this->projectName = $projectName;
	}


	public function save($fileName, $localFilePath, $type)
	{
		$targetKey = $this->projectName . '/ ' . $type . '/' . $fileName;
		return $this->s3Client->uploadFile($localFilePath, $targetKey);
	}
}