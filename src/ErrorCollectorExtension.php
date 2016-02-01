<?php

namespace HQ\ErrorCollector;

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
		'logDirectory' => '%appDir%/../log/',
		'collectFileTypes' => array(
			'*.html',
		),
		'errorStorage' => '\HQ\ErrorCollector\Storage\S3Storage',
		's3' => array(
			'bucket' => 'hq-error-log'
		)
	);

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$storage = null;
		if ($config['errorStorage'] === '\HQ\ErrorCollector\Storage\S3Storage') {
			$builder->addDefinition($this->prefix('S3Proxy'))
				->setClass('HQ\AwsProxy\S3Proxy', array($config['s3']));

			$builder->addDefinition($this->prefix('storage'))
				->setClass('HQ\ErrorCollector\Storage\S3Storage', array(
					'projectName' => $config['projectName'],
					's3Bucket' => $config['s3']['bucket'],
					's3Proxy' => '@errorCollector.S3Proxy'
				));
		}

		$builder->addDefinition($this->prefix('errorCollector'))
			->setClass('HQ\ErrorCollector\ErrorCollector', array(
				'logDirectory' => $config['logDirectory'],
				'collectFileTypes' => $config['collectFileTypes'],
				'errorStorage' => '@errorCollector.storage'
			));
	}
}
