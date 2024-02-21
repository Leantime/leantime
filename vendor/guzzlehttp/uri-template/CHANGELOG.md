# Changelog

All notable changes to `uri-template` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## v1.0.2 - 2023-08-27

### Changed
- Officially support PHP 8.2 and 8.3

### Fixed
- Fixed using `0` as an expanded value

## v1.0.1 - 2021-10-07

### Changed
- Officially support PHP 8.1

## v1.0.0 - 2021-08-14

### Changed
- Dropped support for PHP 7.1

## v0.2.0 - 2020-07-21

### Added
- Support PHP 7.1 and 8.0

### Changed
- Renamed `GuzzleHttp\Utility\` to `GuzzleHttp\UriTemplate\`

### Fixed
- Delegate RFC 3986 query string encoding to PHP
- Fixed some bugs when parts ofs values are not strings

## v0.1.1 - 2020-06-30

### Fixed
- Fixed an error due to strict_types [d47d1b0a8e78a3fac1cd0f69d675fc9e06771ac8](https://github.com/guzzle/uri-template/commit/d47d1b0a8e78a3fac1cd0f69d675fc9e06771ac8)

## v0.1.0 - 2020-06-30

### Added
- Moved the `UriTemplate` class in this package
