# WordPress Core functions for plugins PHP package

A PHP package with core functions for Hostinger WordPress plugins.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Support](#support)

## Installation

This is private package, adding it to composer is a bit different.

Add it to the composer.json:
```sh
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:hostinger/hostinger-wp-helper.git"
    }
  ],
```

and:
```sh
"require": {
  "hostinger/hostinger-wp-helper": "main",
}
```

## Usage

First thing boot up package in main plugin file:

```sh
use Hostinger\Helper;

$helepr = new Helper();
$helper->isPreviewDomain();
```

## Support

Package initially was written by Martynas U. (martynas.umbraziunas@hostinger.com). You can ping him in Slack for support.