# Contributing to Leantime
We love your input! We want to make contributing to Leantime as easy and transparent as possible. There are many ways to contribute:


## Development Process

We use `master` as our primary development branch. All changes should be made through feature branches and pull requests:

1. Fork the repository and create your branch from `master`
2. Create feature branches using the format: `feature/your-feature-name`
3. For bug fixes use: `fix/bug-description`
4. For documentation updates use: `docs/what-you-documented`
5. For translations use Crowdin instead of direct pull requests

## We Develop with GitHub
We use GitHub to host code, track issues and feature requests, and accept pull requests. Pull requests are the best way to propose changes.

### Pull Request Process

1. Ensure your code follows our coding standards (PSR-12)
2. Add/update tests for any new features
3. Update documentation as needed
4. Ensure all tests pass locally
5. Run code style checks:
   ```bash
   make codesniffer
   make phpstan
   ```
6. Include screenshots for user interface changes
7. Update the CHANGELOG.md if needed
8. Sign the Contributor License Agreement when prompted

### Quality Requirements


## Contributor License Agreements
Developers who wish to contribute code to be considered for inclusion in Leantime must first complete a Contributor License Agreement (CLA).
We use CLA assistant to manage signatures. You will be asked to sign the CLA with your first pull request. Subsequent pull requests will not require additional signatures. Please keep in mind that:

## Report bugs using GitHub's [issues](https://github.com/Leantime/leantime/issues)
We use GitHub issues to track public bugs. Report a bug by [opening a new issue](https://github.com/Leantime/leantime/issues); it's that easy!

## Translations
We use Crowdin to manage our translations. Please update translations in [this project](https://crowdin.com/project/leantime)
At this point we will stop accepting pull requests into the language files directly as this is causing issues with the Crowdin sync process. 

## Write bug reports with detail, background, and sample code
[This is an example](http://stackoverflow.com/q/12488905/180626) of a bug report, and we have modeled the issue templates after that. Here's [another example from Craig Hockenberry](http://www.openradar.me/11905408), an app developer whom I greatly respect.

**Great Bug Reports** tend to have:

  - Be specific!
  - Give sample code if you can. [My stackoverflow question](http://stackoverflow.com/q/12488905/180626) includes sample code that *anyone* with a base R setup can run to reproduce what I was seeing

People *love* thorough bug reports. I'm not even kidding.

## Development Setup

1. Fork and clone the repository
2. Install dependencies:
   ```bash
   make install-deps-dev
   ```
3. Set up your environment:
   - Copy `.env.example` to `.env`
   - Configure your database
   - Set up local development server

4. Run development build:
   ```bash
   make build-dev
   ```

5. Run tests:
   ```bash
   make unit-test
   make acceptance-test
   ```

## Use a Consistent Coding Style
We are using the [PSR-12 coding style](https://www.php-fig.org/psr/psr-12/) for PHP. 

### Naming conventions

As mentioned above we are following PSR-12 naming conventions. Some areas are not covered in the standard and will be covered in this section. For sake of completion we include all naming conventions in this section.

**Classes**
Pascal Case (Example: `Leantime\\Core\\Http\\Request`)

**Class Methods**
Camel Case (Example: `$object->getItem()`)

**Variable Names**
Camel Case (Example: `$variableName`)

**Array Index Keys**
Camel Case (Example: `$array['indexDefinition']`)

**Constants**
Macro Case (Example: `MY_CONSTANT`)

**Event Strings**
Camel Case with periods for the path (Example: `leantime.core.controller.frontController.someEvent`)

**HTML elements, classes, ids**
Kebab Case (Example: `<div class="my-class" id="my-id">`)

**Blade Components**
Kebab Case (Example: `<x-global::my-directive>`)

**Blade File Names**
Kebab Case + `blade.php` (Example: `my-directive.blade.php`)


## Facades

While facades are a valuable tool in Laravel's toolbox exessive usage can create problems around testing 
and modularity. For that purpose we limit the allowed facades to the following classes:

`Cache::`
`Log::`

All other classes should be injected via constructor.


## References
This document was adapted from the open-source contribution guidelines for [Facebook's Draft](https://github.com/facebookarchive/draft-js/blob/5dd99d327066f5f0b30b95ab95770822cff1ac65/CONTRIBUTING.md)
