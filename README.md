PHP ADE
========

A PHP 5.3 library for scraping AdultDvdEmpire, strongly inspired by [php-imdb](https://github.com/redpanda/php-imdb).

## Installation

### Install vendors

    wget http://getcomposer.org/composer.phar
    php composer.phar install

### Update vendors

    php composer.phar update

## Usage

### Get DVD

```php
<?php
$i = new ADE\Movie("http://www.adultdvdempire.com/1692533/hands-on-training-hardcore-massage-porn-movies.html");
$i->getTitle();
```

## License

MIT, see LICENSE
