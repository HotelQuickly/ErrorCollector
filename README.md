ErrorCollector
==============

Collector of latte exceptions for project build on [Nette Framework](http://nette.org).

## Installation
Easiest way is to use [Composer](http://getcomposer.org/):

```sh
$ composer require hotel-quickly/error-collector:@dev
```

## Usage

Mandatory configuration in config.neon
```yml
errorCollector:
	s3:
		accessKeyId:
		secretAccessKeyId:
		region: 'ap-southeast-1'
```

Full configuration list
```yml
errorCollector:
	s3:
		accessKeyId:
		secretAccessKeyId:
		region: 'ap-southeast-1'
	logDirectory: %appDir%/../log/
	errorStorage: '\HQ\Storage\S3Storage'