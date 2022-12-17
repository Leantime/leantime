# Contributing to Leantime
We love your input! We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## We Develop with Github
We use github to host code, to track issues and feature requests, as well as accept pull requests.

## We Use [Github Flow](https://guides.github.com/introduction/flow/index.html), So All Code Changes Happen Through Pull Requests
Pull requests are the best way to propose changes to the codebase (we use [Github Flow](https://guides.github.com/introduction/flow/index.html)). We actively welcome your pull requests:

1. Fork the repo and create your branch from `master`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints.
6. Issue that pull request!

## Any contributions you make will be under the GPL-2.0 Software License
In short, when you submit code changes, your submissions are understood to be under the same [GPL 2.0 License](https://choosealicense.com/licenses/gpl-2.0/) that covers the project. Feel free to contact the maintainers if that's a concern.

## Report bugs using Github's [issues]([https://github.com/briandk/transcriptase-atom/issues](https://github.com/Leantime/leantime/issues))
We use GitHub issues to track public bugs. Report a bug by [opening a new issue](); it's that easy!

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

## Directory Structure in Leantime

This isn't a complete list, but points out the things of note to most contributors.

```yaml
├── bin/leantime # leantime cli
├── config/
│   ├── .env OR config.yaml OR configuration.php # different configuration file options
│   └── appSettings.php # application settings, including current version
├── logs/error.log # error and debug logging
├── vendor/ # composer dependencies
├── public/ # directory root
│   ├── css/
│   ├── fonts/
│   ├── images/
│   ├── js/
│   ├── less/
│   ├── theme/ # different themes for leantime
│   │   └── {themename}/
│   │       ├── css/
│   │       ├── js/
│   │       ├── language/
│   │       ├── layout/ # layout files of the applicaiton
│   │       │   ├── app.twig
│   │       │   ├── {layoutname}.twig
│   │       │   └── partials/ # twig partials for layouts
│   │       │       ├── header.twig
│   │       │       └── {partialname}.twig
│   │       └── theme.ini # config file for themes
│   ├── userfiles/
│   ├── backup.php
│   ├── cron.php
│   ├── download.php
│   └── index.php # index file of the application
└── src/
    ├── command/ # each of these files are a command in leantime-cli
    │   └── class.{name}Command.php 
    ├── core/ # this folder contains the core functionality of the application
    │   ├── abstract.{name}.php # abstract classes for the application
    │   ├── trait.{name}.php # traits for the application
    │   └── class.{name}.php # classes for the application
    ├── custom/... # overwrites the src/ folder, instance specific
    ├── domain/ # contains all the different modules (routes) of the application
    │   └── {modulename}/
    │       ├── pages/
    │       │   ├── controllers/class.{actionname}.php
    │       │   └── templates/{actionname}.twig
    │       ├── components/
    │       │   ├── controllers/class.{componentname}.php
    │       │   └── templates/{componentname}.twig
    │       ├── events/register.php # contains events for the module
    │       ├── repositories/class.{modulename}.php # module repository
    │       └── services/class.{modulename}.php # module service
    ├── language/
    │   ├── {language_code}.ini
    │   └── ...
    ├── plugins/
    │   ├── {pluginname}/...
    │   └── motivationalQuotes/ # example plugin
    │       └── register.php # only required file for plugins, used to hook into events
    └── macros/ # small reusable templates, used throughout the application
        ├── icon.twig
        ├── searchabledropdown.twig
        ├── passwordfield.twig
        └── {macroname}.twig
```

## Templates in Leantime
| Type       | Description                                      | Location                                                      |
| ---------- | ------------------------------------------------ | ------------------------------------------------------------- |
| Macros     | Small building blocks, very reusable             | `src/macros/{macroname}.twig`                                 |
| Components | HTML partials generated and sent from the server | `src/domain/{modulename}/components/`                         |
| Layouts    | Specific page structures, uses partials          | `public/theme/{themename}/layout/{layoutname}.twig`           |
| Partials   | Templates for pieces of specific page structure  | `public/theme/{themename}/layout/partials/{partialname}.twig` |
| Pages      | Templates for specific routes, extends layouts   | `src/domain/{modulename}/pages/`                              |

## References
This document was adapted from the open-source contribution guidelines for [Facebook's Draft](https://github.com/facebook/draft-js/blob/a9316a723f9e918afde44dea68b5f9f39b7d9b00/CONTRIBUTING.md)
