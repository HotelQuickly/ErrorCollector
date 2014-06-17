ErrorCollector
==============
[![Build Status](https://travis-ci.org/HotelQuickly/ErrorCollector.svg?branch=master)](https://travis-ci.org/HotelQuickly/ErrorCollector)

Collector of latte exceptions for project build on [Nette Framework](http://nette.org).

Currently only Amazon AWS S3 is supported as shared storage for exception files.

## Installation in your project
Easiest way is to use [Composer](http://getcomposer.org/):

```sh
$ composer require hotel-quickly/error-collector:v1.0.1
```

## Usage in your project

Add mandatory configuration to config.neon. It's recommended to set access key to s3 in config.local.neon

Mandatory configuration in config.neon
```yml
errorCollector:
	projectName: hotelquickly
	s3:
		accessKeyId:
		secretAccessKey:
		region: 'ap-southeast-1'
```

Full configuration list
```yml
errorCollector:
	s3:
		accessKeyId:
		secretAccessKey:
		region: 'ap-southeast-1'
		bucket: hq-error-log
	logDirectory: %appDir%/../log/
	errorStorage: '\HQ\ErrorCollector\Storage\S3Storage'
```

### Add extension to your bootstrap.php

```php
$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('errorCollector', new \HQ\ErrorCollector\ErrorCollectorExtension);
};
```

### Add presenter with action for cron

```php
<?php

class ErrorCollectorPresenter extends BasePresenter {

	/** @autowire
	 * @var \HQ\ErrorCollector\ErrorCollector */
	protected $errorCollector;

	public function actionUploadErrors()
	{
		$exceptionCnt = $this->errorCollector->uploadFiles();
	}

}
```

And setup a cron

```
*/1 * * * * root /usr/bin/wget --no-check-certificate -t 1 -q -O /dev/null http://vanilla.hotelquickly.com/cron/error-collector/upload-to-s3 >> /dev/null
```


## The MIT License (MIT)

Copyright (c) 2014 Hotel Quickly Ltd.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
