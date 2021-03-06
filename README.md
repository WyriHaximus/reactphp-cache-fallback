# Fallback cache decorator

[![Build Status](https://travis-ci.com/WyriHaximus/reactphp-cache-fallback.svg?branch=master)](https://travis-ci.com/WyriHaximus/reactphp-cache-fallback)
[![Latest Stable Version](https://poser.pugx.org/WyriHaximus/react-cache-fallback/v/stable.png)](https://packagist.org/packages/WyriHaximus/react-cache-fallback)
[![Total Downloads](https://poser.pugx.org/WyriHaximus/react-cache-fallback/downloads.png)](https://packagist.org/packages/WyriHaximus/react-cache-fallback)
[![Code Coverage](https://scrutinizer-ci.com/g/WyriHaximus/reactphp-cache-fallback/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WyriHaximus/reactphp-cache-fallback/?branch=master)
[![License](https://poser.pugx.org/WyriHaximus/react-cache-fallback/license.png)](https://packagist.org/packages/WyriHaximus/react-cache-fallback)
[![PHP 7 ready](http://php7ready.timesplinter.ch/WyriHaximus/reactphp-cache-fallback/badge.svg)](https://travis-ci.org/WyriHaximus/reactphp-cache-fallback)

# Installation

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/react-cache-fallback 
```

# Usage

The following example will first try to get he item from the `ArrayCache` before 
falling back to `$fallback` on `get`. When it gets something from `$fallback` it will 
also set it to `ArrayCache`. All `set` and `delete` calls will go to both.

```php
$cache = new Fallback(
    new ArrayCache(5),
    $fallback
);
```

# License

The MIT License (MIT)

Copyright (c) 2018 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
