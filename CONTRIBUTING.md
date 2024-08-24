# Contributing to Leantime
We love your input! We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Translating files
- Becoming a maintainer

## We Develop with GitHub
We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

## We Use [GitHub Flow](https://docs.github.com/en/get-started/quickstart/github-flow), So All Code Changes Happen Through Pull Requests
Pull requests are the best way to propose changes to the codebase (we use [GitHub Flow](https://docs.github.com/en/get-started/quickstart/github-flow)). We actively welcome your pull requests:

1. Fork the repo and create your branch from `master`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints.
6. Issue that pull request!

## Contributor License Agreements
Developers who wish to contribute code to be considered for inclusion in Leantime must first complete a Contributor License Agreement (CLA).
We use CLA assistant to manage signatures. You will be asked to sign the CLA with your first pull request. Subsequent pull requests will not require additional signatures. Please keep in mind that:
- If you are an individual writing the code on your own time and you're SURE you are the sole owner of any intellectual property you contribute, you can sign the license as an individual contributor
- If you are writing the code as part of your job, or if there is any possibility that your employers might think they own any intellectual property you create, then you should reach out to us at support@leantime.io for a Corporate Contributor License Agreement (this will allow any employee at your company to contribute without having to sign individual CLAs).

## Report bugs using GitHub's [issues]([https://github.com/Leantime/leantime/issues](https://github.com/Leantime/leantime/issues))
We use GitHub issues to track public bugs. Report a bug by [opening a new issue](https://github.com/Leantime/leantime/issues); it's that easy!

## Translations
We use Crowdin to manage our translations. Please update translations in [this project](https://crowdin.com/project/leantime)
At this point we will stop accepting PRs into the language files directly as this is causing issues with the Crowdin sync process. 

## Write bug reports with detail, background, and sample code
[This is an example](http://stackoverflow.com/q/12488905/180626) of a bug report, and we have modeled the issue templates after that. Here's [another example from Craig Hockenberry](http://www.openradar.me/11905408), an app developer whom I greatly respect.

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
  - Be specific!
  - Give sample code if you can. [My stackoverflow question](http://stackoverflow.com/q/12488905/180626) includes sample code that *anyone* with a base R setup can run to reproduce what I was seeing
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)

People *love* thorough bug reports. I'm not even kidding.

## Use a Consistent Coding Style
We are using the [PSR-12 coding style](https://www.php-fig.org/psr/psr-12/) for PHP. 

### Naming conventions

As mentioned above we are following PSR-12 naming conventions. Some areas are not covered in the standard and will be covered in this section. For sake of completion we include all naming conventions in this section.

**Classes**
Pascal Case (Example: `leantime.core.controller.frontcontroller`

**Class Methods**
Camel Case (Example: `$object->getItem()`

**Variable Names**
Camel Case (Example: `$variableName`)

**Array Index Keys**
Camel Case (Example: `$array['indexDefinition']`)

**Constants**
Macro Case (Example: `MY_CONSTANT`)

**Event Strings**
Pascal Case with periods for the path (Example: `Leantime.Core.Method.SomeEvent`)

**HTML elements, classes, ids**
Kebab Case (Example: `<div class="my-class" id="my-id">`

**Blade Components**
Kebab Case (Example: `<x-global::my-directive>`

**Blade File Names**
Kebab Case + `blade.php` (Example: my-directive.blade.php`)



## References
This document was adapted from the open-source contribution guidelines for [Facebook's Draft](https://github.com/facebookarchive/draft-js/blob/5dd99d327066f5f0b30b95ab95770822cff1ac65/CONTRIBUTING.md)
