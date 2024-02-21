metasyntactical / composer-plugin-license-check
===============================================

This composer plugin allows to define a white- and/or blacklist of licenses
packages which will be installed in a project will be validated against.
If a forbidden license is found in a package the installation of the particular
package will be failed.

Additionally a new composer command ``check-licenses`` is provided to list all
packages in the dependencies including their license and if it is allowed to
use.

## How to install

Installation procedure follows the general installation process of packages with
composer.

Run ``composer require metasyntactical/composer-plugin-license-check`` to add the
package to the ``composer.json`` and install the package.


## How to use

The composer plugin reacts on extra variables in the extra-section of the
composer.json.

```json
{
  "extra": {
    "metasyntactical/composer-plugin-license-check": {
      "allow-list": [],
      "deny-list": [],
      "allowed-packages": []
    }
  }
}
```

Just specify the allowed or forbidden licenses as array.
Use the license identifiers allowed/used in the version-property of the composer.json
to be compatible with the general usage.

One may specify additional packages which are allowed despite of license violations.

**Important Note**: This plugin is licensed under MIT license. Even if you forbid
to use MIT licensed packages in your project the plugin itself is the only package
it would not complain about (otherwise further checking would not work obviously).
