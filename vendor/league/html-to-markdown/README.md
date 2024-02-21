HTML To Markdown for PHP
========================

[![Latest Version](https://img.shields.io/packagist/v/league/html-to-markdown.svg?style=flat-square)](https://packagist.org/packages/league/html-to-markdown)
[![Software License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/thephpleague/html-to-markdown/Tests/master.svg?style=flat-square)](https://github.com/thephpleague/html-to-markdown/actions?query=workflow%3ATests+branch%3Amaster)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/html-to-markdown.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/html-to-markdown/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/html-to-markdown.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/html-to-markdown)
[![Total Downloads](https://img.shields.io/packagist/dt/league/html-to-markdown.svg?style=flat-square)](https://packagist.org/packages/league/html-to-markdown)

Library which converts HTML to [Markdown](http://daringfireball.net/projects/markdown/) for your sanity and convenience.


**Requires**: PHP 7.2+

**Lead Developer**: [@colinodell](http://twitter.com/colinodell)

**Original Author**: [@nickcernis](http://twitter.com/nickcernis)


### Why convert HTML to Markdown?

*"What alchemy is this?"* you mutter. *"I can see why you'd convert [Markdown to HTML](https://github.com/thephpleague/commonmark),"* you continue, already labouring the question somewhat, *"but why go the other way?"*

Typically you would convert HTML to Markdown if:

1. You have an existing HTML document that needs to be edited by people with good taste.
2. You want to store new content in HTML format but edit it as Markdown.
3. You want to convert HTML email to plain text email.
4. You know a guy who's been converting HTML to Markdown for years, and now he can speak Elvish. You'd quite like to be able to speak Elvish.
5. You just really like Markdown.

### How to use it

Require the library by issuing this command:

```bash
composer require league/html-to-markdown
```

Add `require 'vendor/autoload.php';` to the top of your script.

Next, create a new HtmlConverter instance, passing in your valid HTML code to its `convert()` function:

```php
use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter();

$html = "<h3>Quick, to the Batpoles!</h3>";
$markdown = $converter->convert($html);
```

The `$markdown` variable now contains the Markdown version of your HTML as a string:

```php
echo $markdown; // ==> ### Quick, to the Batpoles!
```

The included `demo` directory contains an HTML->Markdown conversion form to try out.

### Conversion options

By default, HTML To Markdown preserves HTML tags without Markdown equivalents, like `<span>` and `<div>`.

To strip HTML tags that don't have a Markdown equivalent while preserving the content inside them, set `strip_tags` to true, like this:

```php
$converter = new HtmlConverter(array('strip_tags' => true));

$html = '<span>Turnips!</span>';
$markdown = $converter->convert($html); // $markdown now contains "Turnips!"
```

Or more explicitly, like this:

```php
$converter = new HtmlConverter();
$converter->getConfig()->setOption('strip_tags', true);

$html = '<span>Turnips!</span>';
$markdown = $converter->convert($html); // $markdown now contains "Turnips!"
```

Note that only the tags themselves are stripped, not the content they hold.

To strip tags and their content, pass a space-separated list of tags in `remove_nodes`, like this:

```php
$converter = new HtmlConverter(array('remove_nodes' => 'span div'));

$html = '<span>Turnips!</span><div>Monkeys!</div>';
$markdown = $converter->convert($html); // $markdown now contains ""
```

By default, all comments are stripped from the content. To preserve them, use the `preserve_comments` option, like this:

```php
$converter = new HtmlConverter(array('preserve_comments' => true));

$html = '<span>Turnips!</span><!-- Monkeys! -->';
$markdown = $converter->convert($html); // $markdown now contains "Turnips!<!-- Monkeys! -->"
```

To preserve only specific comments, set `preserve_comments` with an array of strings, like this:

```php
$converter = new HtmlConverter(array('preserve_comments' => array('Eggs!')));

$html = '<span>Turnips!</span><!-- Monkeys! --><!-- Eggs! -->';
$markdown = $converter->convert($html); // $markdown now contains "Turnips!<!-- Eggs! -->"
```

By default, placeholder links are preserved. To strip the placeholder links, use the `strip_placeholder_links` option, like this:

```php
$converter = new HtmlConverter(array('strip_placeholder_links' => true));

$html = '<a>Github</a>';
$markdown = $converter->convert($html); // $markdown now contains "Github"
```

### Style options

By default bold tags are converted using the asterisk syntax, and italic tags are converted using the underlined syntax. Change these by using the `bold_style` and `italic_style` options.

```php
$converter = new HtmlConverter();
$converter->getConfig()->setOption('italic_style', '*');
$converter->getConfig()->setOption('bold_style', '__');

$html = '<em>Italic</em> and a <strong>bold</strong>';
$markdown = $converter->convert($html); // $markdown now contains "*Italic* and a __bold__"
```

### Line break options

By default, `br` tags are converted to two spaces followed by a newline character as per [traditional Markdown](https://daringfireball.net/projects/markdown/syntax#p). Set `hard_break` to `true` to omit the two spaces, as per GitHub Flavored Markdown (GFM).

```php
$converter = new HtmlConverter();
$html = '<p>test<br>line break</p>';

$converter->getConfig()->setOption('hard_break', true);
$markdown = $converter->convert($html); // $markdown now contains "test\nline break"

$converter->getConfig()->setOption('hard_break', false); // default
$markdown = $converter->convert($html); // $markdown now contains "test  \nline break"
```

### Autolinking options

By default, `a` tags are converted to the easiest possible link syntax, i.e. if no text or title is available, then the `<url>` syntax will be used rather than the full `[url](url)` syntax. Set `use_autolinks` to `false` to change this behavior to always use the full link syntax.

```php
$converter = new HtmlConverter();
$html = '<p><a href="https://thephpleague.com">https://thephpleague.com</a></p>';

$converter->getConfig()->setOption('use_autolinks', true);
$markdown = $converter->convert($html); // $markdown now contains "<https://thephpleague.com>"

$converter->getConfig()->setOption('use_autolinks', false); // default
$markdown = $converter->convert($html); // $markdown now contains "[https://google.com](https://google.com)"
```

### Passing custom Environment object

You can pass current `Environment` object to customize i.e. which converters should be used.

```php
$environment = new Environment(array(
    // your configuration here
));
$environment->addConverter(new HeaderConverter()); // optionally - add converter manually

$converter = new HtmlConverter($environment);

$html = '<h3>Header</h3>
<img src="" />
';
$markdown = $converter->convert($html); // $markdown now contains "### Header" and "<img src="" />"
```

### Table support

Support for Markdown tables is not enabled by default because it is not part of the original Markdown syntax. To use tables add the converter explicitly:

```php
use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Converter\TableConverter;

$converter = new HtmlConverter();
$converter->getEnvironment()->addConverter(new TableConverter());

$html = "<table><tr><th>A</th></tr><tr><td>a</td></tr></table>";
$markdown = $converter->convert($html);
```

### Limitations

- Markdown Extra, MultiMarkdown and other variants aren't supported – just Markdown.

### Style notes

- Setext (underlined) headers are the default for H1 and H2. If you prefer the ATX style for H1 and H2 (# Header 1 and ## Header 2), set `header_style` to 'atx' in the options array when you instantiate the object:

    `$converter = new HtmlConverter(array('header_style'=>'atx'));`

     Headers of H3 priority and lower always use atx style.

- Links and images are referenced inline. Footnote references (where image src and anchor href attributes are listed in the footnotes) are not used.
- Blockquotes aren't line wrapped – it makes the converted Markdown easier to edit.

### Dependencies

HTML To Markdown requires PHP's [xml](http://www.php.net/manual/en/xml.installation.php), [lib-xml](http://www.php.net/manual/en/libxml.installation.php), and [dom](http://www.php.net/manual/en/dom.installation.php) extensions, all of which are enabled by default on most distributions.

Errors such as "Fatal error: Class 'DOMDocument' not found" on distributions such as CentOS that disable PHP's xml extension can be resolved by installing php-xml.

### Contributors

Many thanks to all [contributors](https://github.com/thephpleague/html-to-markdown/graphs/contributors) so far. Further improvements and feature suggestions are very welcome.

### How it works

HTML To Markdown creates a DOMDocument from the supplied HTML, walks through the tree, and converts each node to a text node containing the equivalent markdown, starting from the most deeply nested node and working inwards towards the root node.

### To-do

- Support for nested lists and lists inside blockquotes.
- Offer an option to preserve tags as HTML if they contain attributes that can't be represented with Markdown (e.g. `style`).

### Trying to convert Markdown to HTML?

Use one of these great libraries:

 - [league/commonmark](https://github.com/thephpleague/commonmark) (recommended)
 - [cebe/markdown](https://github.com/cebe/markdown)
 - [PHP Markdown](https://michelf.ca/projects/php-markdown/)
 - [Parsedown](https://github.com/erusev/parsedown)

No guarantees about the Elvish, though.
