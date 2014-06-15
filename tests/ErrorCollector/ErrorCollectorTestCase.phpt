<?php

/**
 * Test: ErrorCollector
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */

namespace Tests;

use Nette,
	Tester,
	Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../../src/ErrorCollector.php';
require __DIR__ . '/../../src/Storage/IErrorStorage.php';

class ErrorCollectorTestCase extends Tester\TestCase {

	/** @var \HQ\ErrorCollector\ErrorCollector */
	private $errorCollector;

	/** @var \HQ\Storage\IErrorStorage */
	private $errorStorage;

	/** @var string */
	private $directory;

	public function setUp()
	{
		$this->mockista = new \Mockista\Registry();

		$this->directory = TEMP_DIR;
		$this->errorStorage = $this->mockista->create('HQ\Storage\IErrorStorage', array(
			'save' => function($fileName, $filePath, $type) {
				@mkdir($this->directory . '/moved/');
				file_put_contents($this->directory . '/moved/' . $fileName, 'test');
				return true;
			}
		));

		$this->errorCollector = new \HQ\ErrorCollector\ErrorCollector($this->directory, $this->errorStorage);
	}

	public function tearDown()
	{
		$this->mockista->assertExpectations();
		// Tester\Helpers::purge($this->directory);
	}

	public function testFindFiles()
	{
		$files = array(
			'temp-file.html',
			'temp-log-file.log',
			'temp-txt-file.txt'
		);
		$this->prepareFiles($files);

		$finder = $this->errorCollector->findFiles('*', $this->directory);
		Assert::same(3, count($this->export($finder)));

		$finder = $this->errorCollector->findFiles('some-file.log', $this->directory);
		Assert::same(array(), $this->export($finder));

		Tester\Helpers::purge($this->directory);
	}


	public function testUploadFiles()
	{
		$files = array(
			'temp-file.log',
			'temp-log-file.log',
			'temp-exception-file.html',
			'unknown-file.txt'
		);
		$this->prepareFiles($files);

		$this->errorCollector->setErrorStorage($this->errorStorage);

		Assert::same(3, $this->errorCollector->uploadFiles());

		// log files should be renamed from original
		Assert::true(!is_file($this->directory . '/moved/' . 'temp-file.log'));

		// no exception or error files should be available to upload now
		Assert::same(0, $this->errorCollector->uploadFiles());
	}


	public function testGetFileType()
	{
		$file = new \SplFileInfo('test.html');
		Assert::same('exception', $this->errorCollector->getFileType($file));

		$file = new \SplFileInfo('test.log');
		Assert::same('log', $this->errorCollector->getFileType($file));

		$file = new \SplFileInfo('email-sent');
		Assert::exception(function() use ($file) {
			$this->errorCollector->getFileType($file);
		}, 'InvalidArgumentException');
	}


	private function prepareFiles(array $files)
	{
		foreach ($files as $file) {
			file_put_contents($this->directory . '/' . $file, 'test');
		}
	}

	private function export($iterator)
	{
		$arr = array();
		foreach ($iterator as $key => $value) $arr[] = strtr($key, '\\', '/');
		sort($arr);
		return $arr;
	}
}

\run(new ErrorCollectorTestCase());
