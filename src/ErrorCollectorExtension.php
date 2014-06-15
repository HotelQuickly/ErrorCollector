<?php

namespace HQ;

use Nette;

// compatibility for nette 2.0.x and 2.1.x
if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
}

/**
 *
 * @author Josef Nevoral <josef.nevoral@hotelquickly.com>
 */
class ErrorCollectorExtension extends Nette\DI\CompilerExtension {

	private $defaults = array(
		'logDirectory' => '%appDir%/../log/errorCollector.log',
		'errorStorage' => '\HQ\Storage\S3Storage',
		'bucket' => 'hq-error-log'
	);

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$storage = null;
		if ($config['errorStorage'] === '\HQ\ErrorStorage\S3Storage') {
			$builder->addDefinition($this->prefix('S3Proxy'))
				->setClass('HQ\Storage\S3Storage', array(
					'accessKeyId' => $config['accessKeyId'],
					'secretAccessKeyId' => $config['secretAccessKeyId'],
					'region' => $config['region']
				));

			$builder->addDefinition($this->prefix('storage'))
				->setClass('HQ\Storage\S3Storage', array(
					'projectName' => $config['projectName'],
					's3Bucket' => $config['bucket'],
					$this->prefix('@S3Proxy')
				));
		}

		$builder->addDefinition($this->prefix('errorCollector'))
			->setClass('HQ\ErrorCollector', array(
				'logDirectory' => $config['logDirectory'],
				$this->prefix('storage')
			));
	}
}