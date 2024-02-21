# AWS Common Runtime PHP bindings

## Requirements

* PHP 5.5+ on UNIX platforms, 7.2+ on Windows
* CMake 3.x
* GCC 4.4+, clang 3.8+ on UNIX, Visual Studio build tools on Windows
* Tests require [Composer](https://getcomposer.org)

## Installing with Composer and PECL

The package has two different package published to [composer](https://packagist.org/packages/aws/aws-crt-php) and [PECL](https://pecl.php.net/package/awscrt).

On UNIX, you can get the package from package manager or build from source:

```
pecl install awscrt
composer require aws/aws-crt-php
```

On Windows, you need to build from source as instruction written below for the native extension `php_awscrt.dll` . And, follow https://www.php.net/manual/en/install.pecl.windows.php#install.pecl.windows.loading to load extension. After that:

```
composer require aws/aws-crt-php
```

## Building from Github source

```sh
$ git clone --recursive https://github.com/awslabs/aws-crt-php.git
$ cd aws-crt-php
$ phpize
$ ./configure
$ make
$ ./dev-scripts/run_tests.sh
```

## Building on Windows

### Requirements for Windows

* Ensure you have the [windows PHP SDK](https://github.com/microsoft/php-sdk-binary-tools) (this example assumes installation of the SDK to C:\php-sdk and that you've checked out the PHP source to php-src within the build directory) and it works well on your machine.

* Ensure you have "Development package (SDK to develop PHP extensions)" and PHP available from your system path. You can download them from https://windows.php.net/download/. You can check if they are available by running `phpize -v` and `php -v`

### Instructions

From Command Prompt (not powershell). The instruction is based on Visual Studio 2019 on 64bit Windows.

```bat
> git clone --recursive https://github.com/awslabs/aws-crt-php.git
> git clone https://github.com/microsoft/php-sdk-binary-tools.git C:\php-sdk
> C:\php-sdk\phpsdk-vs16-x64.bat

C:\php-sdk\
$ cd <your-path-to-aws-crt-php>

<your-path-to-aws-crt-php>\
$ phpize

# --with-prefix only required when your php runtime in system path is different than the runtime you wish to use.
<your-path-to-aws-crt-php>\
$ configure --enable-awscrt=shared --with-prefix=<your-path-to-php-prefix>

<your-path-to-aws-crt-php>\
$ nmake

<your-path-to-aws-crt-php>\
$ nmake generate-php-ini

# check .\php-win.ini, it now has the full path to php_awscrt.dll that you can manually load to your php runtime, or you can run the following command to run tests and load the required native extension for awscrt.
<your-path-to-aws-crt-php>\
$ .\dev-scripts\run_tests.bat <your-path-to-php-binary>
```

Note: for VS2017, Cmake will default to build for Win32, refer to [here](https://cmake.org/cmake/help/latest/generator/Visual%20Studio%2015%202017.html). If you are building for x64 php, you can set environment variable as follow to let cmake pick x64 compiler.

```bat
set CMAKE_GENERATOR=Visual Studio 15 2017
set CMAKE_GENERATOR_PLATFORM=x64
```

## Debugging

Using [PHPBrew](https://github.com/phpbrew/phpbrew) to build/manage multiple versions of PHP is helpful.

Note: You must use a debug build of PHP to debug native extensions.
See the [PHP Internals Book](https://www.phpinternalsbook.com/php7/build_system/building_php.html) for more info

```shell
# PHP 8 example
$ phpbrew install --stdout -j 8 8.0 +default -- CFLAGS=-Wno-error --disable-cgi --enable-debug
# PHP 5.5 example
$ phpbrew install --stdout -j 8 5.5 +default -openssl -mbstring -- CFLAGS="-w -Wno-error" --enable-debug --with-zlib=/usr/local/opt/zlib
$ phpbrew switch php-8.0.6 # or whatever version is current, it'll be at the end of the build output
$ phpize
$ ./configure
$ make CMAKE_BUILD_TYPE=Debug
```

Ensure that the php you launch from your debugger is the result of `which php` , not just
the system default php.

## Security

See [CONTRIBUTING](CONTRIBUTING.md#security-issue-notifications) for more information.

## Known OpenSSL related issue (Unix only)

* When your php loads a different version of openssl than your system openssl version, awscrt may fail to load or weirdly crash. You can find the openssl version php linked via: `php -i | grep 'OpenSSL'`, and awscrt linked from the build log, which will be `Found OpenSSL: * (found version *)`

The easiest workaround to those issue is to build from source and get aws-lc for awscrt to depend on instead.
TO do that, same instructions as [here](#building-from-github-source), but use `USE_OPENSSL=OFF make` instead of `make`

## License

This project is licensed under the Apache-2.0 License.
