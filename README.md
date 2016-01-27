PeoplePerHour/YiiLogExtension
=============================

[![Build Status](https://travis-ci.org/travis-ci/travis-web.svg?branch=master)](https://travis-ci.org/travis-ci/travis-web)


YiiLogExtension is a component that extends Yii::log functionality to handle arrays and exceptions and output in files in JSON format. Finally handles log rotation.


## Usage Example

### Register JsonLogger in your log route configuration

```php
array(
    'class'       =>'JsonLogger',
    'categories'  =>'category_key',
    'logFile'     =>'output.json', // File to be parsed by logstash
    'logPath'     =>dirname(__FILE__).'/../../logs',
    'maxFileSize' =>5120,
    'maxLogFiles' =>50,
)
```

Use LogWrapper, which extends Yii::log functionality
```php
LogWrapper::log('dummy message', 'error_level', 'category_key')
LogWrapper::logArray($array, 'error_level', 'category_key')
LogWrapper::logException($exc, 'error_level', 'category_key')
```

## Test repository
```bash
docker-compose run phpunit tests
```
