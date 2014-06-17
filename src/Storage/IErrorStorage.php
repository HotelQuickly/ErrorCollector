<?php

namespace HQ\ErrorCollector\Storage;

/**
 * Interface for saving files to storage
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */

interface IErrorStorage {

	public function save($fileName, $localFilePath, $type);
}