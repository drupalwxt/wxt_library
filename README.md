WxT Library
===========

This module leverages the native Drupal library system to load WxT relevant
assets.

## Installation

There are two installation methods `standalone` + `distro`  to leverage the
[WxT Bootstrap][wxt_bootstrap] theme in Drupal 8. The `standalone` install is
provided as an additional method for those who do not wish to have the full
weight of a distribution and its required dependencies.
### StandAlone Requirements

`Standalone` Install: [WxT Library][wxt_library] only requires the
[Bootstrap][bootstrap] base theme and the [WxT Library][wxt_library] module
at a minimum to function correctly.

You can easily retrieve these dependencies by running `composer install` which
will simply retrieve the following:

- [Bootstrap][bootstrap] (8.x-3.0+)
- [WxT Library][wxt_library] (8.x-1.x)

### Distribution Requirements

`Distro` Install: All dependencies are included as part of the
[Drupal WxT][drupal_wxt] distribution and come completely configured alongside
with additional integrations.


[bootstrap]:      http://drupal.org/project/bootstrap
[drupal_wxt]:     http://drupal.org/project/wxt
[wet_boew]:       http://wet-boew.github.io
[wxt_library]:    http://drupal.org/project/wxt_library
[wxt_bootstrap]:  http://drupal.org/project/wxt_bootstrap
