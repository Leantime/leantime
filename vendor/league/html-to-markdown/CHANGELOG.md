# Change Log
All notable changes to this project will be documented in this file.
Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [Unreleased][unreleased]

## [5.1.0] - 2022-03-02

### Changed

 - Changed horizontal rule style (#218, #219)

### Fixed

 - Fixed `Element::getValue()` not handling possible nulls

## [5.0.2] - 2021-11-06

### Fixed

 - Fixed missplaced comment nodes appearing at the start of the HTML input (#212)

## [5.0.1] - 2021-09-17

### Fixed

 - Fixed lists not using the correct amount of indentation (#211)

## [5.0.0] - 2021-03-28

### Added

 - Added support for tables (#203)
    - This feature is disable by default - see README for how to enable it
 - Added new `strip_placeholder_links` option to strip `<a>` tags without `href` attributes (#196)
 - Added new methods to `ElementInterface`:
    - `hasParent()`
    - `getNextSibling()`
    - `getPreviousSibling()`
    - `getListItemLevel()`
 - Added several parameter and return types across all classes
 - Added new `PreConverterInterface` to allow converters to perform any necessary pre-parsing

### Changed

 - Supported PHP versions increased to PHP 7.2 - 8.0
 - `HtmlConverter::convert()` may now throw a `\RuntimeException` when unexpected `DOMDocument`-related errors occur

### Fixed

 - Fixed complex nested lists containing heading and paragraphs (#198)
 - Fixed consecutive emphasis producing incorrect markdown (#202)

## [4.10.0] - 2020-06-30
### Added

 - Added the ability to disable autolinking with a configuration option (#187, #188)

## [4.9.1] - 2019-12-27
### Fixed
 - Fixed issue with HTML entity escaping in text (#184)

## [4.9.0] - 2019-11-02
### Added
 - Added new option to preserve comments (#177, #179)

## [4.8.3] - 2019-10-31
### Fixed
 - Fixed whitespace preservation around `<code>` tags (#174, #178)

## [4.8.2] - 2019-08-02
### Fixed
 - Fixed headers not being placed onto a new line in some cases (#172)
 - Fixed handling of links containing spaces (#175)

### Removed
 - Removed support for HHVM

## [4.8.1] - 2018-12-24
### Added
 - Added support for PHP 7.3

### Fixed
 - Fixed paragraphs following tables (#165, #166)
 - Fixed incorrect list item escaping (#168, #169)

## [4.8.0] - 2018-09-18
### Added
 - Added support for email auto-linking
 - Added a new interface (`HtmlConverterInterface`) for the main `HtmlConverter` class
 - Added additional test cases (#14)

### Changed
 - The `italic_style` option now defaults to `'*'` so that in-word emphasis is handled properly (#75)

### Fixed
 - Fixed several issues of `<code>` and `<pre>` tags not converting to blocks or inlines properly (#26, #70, #102, #140, #161, #162)
 - Fixed in-word emphasis using underscores as delimiter (#75)
 - Fixed character escaping inside of `<div>` elements
 - Fixed header edge cases

### Deprecated
 - The `bold_style` and `italic_style` options have been deprecated (#75)

## [4.7.0] - 2018-05-19
### Added
 - Added `setOptions()` function for chainable calling (#149)
 - Added new `list_item_style_alternate` option for converting every-other list with a different character (#155)

### Fixed
 - Fixed insufficient newlines after code blocks (#144, #148)
 - Fixed trailing spaces not being preserved in link anchors (#157)
 - Fixed list-like lines not being escaped inside of lists items (#159)

## [4.6.2]
### Fixed
 - Fixed issue with emphasized spaces (#146)

## [4.6.1]
### Fixed
 - Fixed conversion of `<pre>` tags (#145)

## [4.6.0]
### Added
 - Added support for ordered lists starting at numbers other than 1

### Fixed
 - Fixed overly-eager escaping of list-like text (#141)

## [4.5.0]
### Added
 - Added configuration option for list item style (#135, #136)

## [4.4.1]

### Fixed
 - Fixed autolinking of invalid URLs (#129)

## [4.4.0]

### Added
 - Added `hard_break` configuration option (#112, #115)
 - The `HtmlConverter` can now be instantiated with an `Environment` (#118)

### Fixed
 - Fixed handling of paragraphs in list item elements (#47, #110)
 - Fixed phantom spaces when newlines follow `br` elements (#116, #117)
 - Fixed link converter not sanitizing inner spaces properly (#119, #120)

## [4.3.1]
### Changed
 - Revised the sanitization implementation (#109)

### Fixed
 - Fixed tag-like content not being escaped (#67, #109)
 - Fixed thematic break-like content not being escaped (#65, #109)
 - Fixed codefence-like content not being escaped (#64, #109)

## [4.3.0]
### Added
 - Added full support for PHP 7.0 and 7.1

### Changed
 - Changed `<pre>` and `<pre><code>` conversions to use backticks instead of indendation (#102)

### Fixed
 - Fixed issue where specified code language was not preserved (#70, #102)
 - Fixed issue where `<code>` tags nested in `<pre>` was not converted properly (#70, #102)
 - Fixed header-like content not being escaped (#76, #105)
 - Fixed blockquote-like content not being escaped (#77, #103)
 - Fixed ordered list-like content not being escaped (#73, #106)
 - Fixed unordered list-like content not being escaped (#71, #107)

## [4.2.2]
### Fixed
 - Fixed sanitization bug which sometimes removes desired content (#63, #101)

## [4.2.1]
### Fixed
 - Fixed path to autoload.php when used as a library (#98)
 - Fixed edge case for tags containing only whitespace (#99)

### Removed
 - Removed double HTML entity decoding, as this is not desireable (#60)

## [4.2.0]

### Added
 - Added the ability to invoke HtmlConverter objects as functions (#85)

### Fixed
 - Fixed improper handling of nested list items (#19 and #84)
 - Fixed preceeding or trailing spaces within emphasis tags (#83)

## [4.1.1]

### Fixed
 - Fixed conversion of empty paragraphs (#78)
 - Fixed `preg_replace` so it wouldn't break UTF-8 characters (#79)

## [4.1.0]

### Added
 - Added `bin/html-to-markdown` script

### Changed
 - Changed default italic character to `_` (#58)

## [4.0.1]

### Fixed
 - Added escaping to avoid * and _ in a text being rendered as emphasis (#48)

### Removed
 - Removed the demo (#51)
 - `.styleci.yml` and `CONTRIBUTING.md` are no longer included in distributions (#50)

## [4.0.0]

This release changes the visibility of several methods/properties. #42 and #43 brought to light that some visiblities were
not ideally set, so this releases fixes that. Moving forwards this should reduce the chance of introducing BC-breaking changes.

### Added
 - Added new `HtmlConverter::getEnvironment()` method to expose the `Environment` (#42, #43)

### Changed
 - Changed `Environment::addConverter()` from `protected` to `public`, enabling custom converters to be added (#42, #43)
 - Changed `HtmlConverter::createDOMDocument()` from `protected` to `private`
 - Changed `Element::nextCached` from `protected` to `private`
 - Made the `Environment` class `final`

## [3.1.1]
### Fixed
 - Empty HTML strings now result in empty Markdown documents (#40, #41)

## [3.1.0]
### Added
 - Added new `equals` method to `Element` to check for equality

### Changes
 - Use Linux line endings consistently instead of plaform-specific line endings (#36)

### Fixed
 - Cleaned up code style

## [3.0.0]
### Changed
 - Changed namespace to `League\HTMLToMarkdown`
 - Changed packagist name to `league/html-to-markdown`
 - Re-organized code into several separate classes
 - `<a>` tags with identical href and inner text are now rendered using angular bracket syntax (#31)
 - `<div>` elements are now treated as block-level elements (#33)

## [2.2.2]
### Added
 - Added support for PHP 5.6 and HHVM
 - Enabled testing against PHP 7 nightlies
 - Added this CHANGELOG.md

### Fixed
 - Fixed whitespace preservation between inline elements (#9 and #10)

## [2.2.1]
### Fixed
 - Preserve placeholder links (#22)

## [2.2.0]
### Added
 - Added CircleCI config

### Changed
 - `<pre>` blocks are now treated as code elements

### Removed
 - Dropped support for PHP 5.2
 - Removed incorrect README comment regarding `#text` nodes (#17)

## [2.1.2]
### Added
 - Added the ability to blacklist/remove specific node types (#11)

### Changed
 - Line breaks are now placed after divs instead of before them
 - Newlines inside of link texts are now removed
 - Updated the minimum PHPUnit version to 4.*

## [2.1.1]
### Added
 - Added options to customize emphasis characters

## [2.1.0]
### Added
 - Added option to strip HTML tags without Markdown equivalents
 - Added `convert()` method for converter reuse
 - Added ability to set options after instance construction
 - Documented the required PHP extensions (#4)

### Changed
 - ATX style now used for h1 and h2 tags inside blockquotes

### Fixed
 - Newlines inside blockquotes are now started with a bracket
 - Fixed some incorrect docblocks
 - `__toString()` now returns an empty string if input is empty
 - Convert head tag if body tag is empty (#7)
 - Preserve special characters inside tags without md equivalents (#6)


## [2.0.1]
### Fixed
 - Fixed first line indentation for multi-line code blocks
 - Fixed consecutive anchors get separating spaces stripped (#3)

## [2.0.0]
### Added
 - Initial release

[unreleased]: https://github.com/thephpleague/html-to-markdown/compare/5.1.0...master
[5.1.0]: https://github.com/thephpleague/html-to-markdown/compare/5.0.2...5.1.0
[5.0.2]: https://github.com/thephpleague/html-to-markdown/compare/5.0.1...5.0.2
[5.0.1]: https://github.com/thephpleague/html-to-markdown/compare/5.0.0...5.0.1
[5.0.0]: https://github.com/thephpleague/html-to-markdown/compare/4.10.0...5.0.0
[4.10.0]: https://github.com/thephpleague/html-to-markdown/compare/4.9.1...4.10.0
[4.9.1]: https://github.com/thephpleague/html-to-markdown/compare/4.9.0...4.9.1
[4.9.0]: https://github.com/thephpleague/html-to-markdown/compare/4.8.3...4.9.0
[4.8.3]: https://github.com/thephpleague/html-to-markdown/compare/4.8.2...4.8.3
[4.8.2]: https://github.com/thephpleague/html-to-markdown/compare/4.8.1...4.8.2
[4.8.1]: https://github.com/thephpleague/html-to-markdown/compare/4.8.0...4.8.1
[4.8.0]: https://github.com/thephpleague/html-to-markdown/compare/4.7.0...4.8.0
[4.7.0]: https://github.com/thephpleague/html-to-markdown/compare/4.6.2...4.7.0
[4.6.2]: https://github.com/thephpleague/html-to-markdown/compare/4.6.1...4.6.2
[4.6.1]: https://github.com/thephpleague/html-to-markdown/compare/4.6.0...4.6.1
[4.6.0]: https://github.com/thephpleague/html-to-markdown/compare/4.5.0...4.6.0
[4.5.0]: https://github.com/thephpleague/html-to-markdown/compare/4.4.1...4.5.0
[4.4.1]: https://github.com/thephpleague/html-to-markdown/compare/4.4.0...4.4.1
[4.4.0]: https://github.com/thephpleague/html-to-markdown/compare/4.3.1...4.4.0
[4.3.1]: https://github.com/thephpleague/html-to-markdown/compare/4.3.0...4.3.1
[4.3.0]: https://github.com/thephpleague/html-to-markdown/compare/4.2.2...4.3.0
[4.2.2]: https://github.com/thephpleague/html-to-markdown/compare/4.2.1...4.2.2
[4.2.1]: https://github.com/thephpleague/html-to-markdown/compare/4.2.0...4.2.1
[4.2.0]: https://github.com/thephpleague/html-to-markdown/compare/4.1.1...4.2.0
[4.1.1]: https://github.com/thephpleague/html-to-markdown/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/thephpleague/html-to-markdown/compare/4.0.1...4.1.0
[4.0.1]: https://github.com/thephpleague/html-to-markdown/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/thephpleague/html-to-markdown/compare/3.1.1...4.0.0
[3.1.1]: https://github.com/thephpleague/html-to-markdown/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/thephpleague/html-to-markdown/compare/3.0.0...3.1.0
[3.0.0]: https://github.com/thephpleague/html-to-markdown/compare/2.2.2...3.0.0
[2.2.2]: https://github.com/thephpleague/html-to-markdown/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/thephpleague/html-to-markdown/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/thephpleague/html-to-markdown/compare/2.1.2...2.2.0
[2.1.2]: https://github.com/thephpleague/html-to-markdown/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/thephpleague/html-to-markdown/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/thephpleague/html-to-markdown/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/thephpleague/html-to-markdown/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/thephpleague/html-to-markdown/compare/775f91e...2.0.0

