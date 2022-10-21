# OpenFeature SDK for PHP

[![Project Status: WIP – Initial development is in progress, but there has not yet been a stable, usable release suitable for the public.](https://www.repostatus.org/badges/latest/wip.svg)](https://www.repostatus.org/#wip)
[![Specification](https://img.shields.io/static/v1?label=Specification&message=v0.5.0&color=yellow)](https://github.com/open-feature/spec/tree/v0.5.0)
[![Latest Stable Version](http://poser.pugx.org/open-feature/sdk/v)](https://packagist.org/packages/open-feature/sdk)
[![Total Downloads](http://poser.pugx.org/open-feature/sdk/downloads)](https://packagist.org/packages/open-feature/sdk)
[![License](http://poser.pugx.org/open-feature/sdk/license)](https://packagist.org/packages/open-feature/sdk)

## Overview

This package provides a functional SDK for an OpenFeature API and client. It also builds on various PSRs (PHP Standards Recommendations) such as the Logger interfaces (PSR-3) and the Basic and Extended Coding Standards (PSR-1 and PSR-12).

Future development may aim to allow this library to be auto-loaded by filepath (PSR-4) and optionally integrate with the container standards (PSR-11) over global `$_SESSION` access.

## Installation

```
$ composer require open-feature/sdk   // installs the latest version
```

## Usage

While `Boolean` provides the simplest introduction, we offer a variety of flag types.

```php
use OpenFeature\OpenFeatureClient;

class MyClass {
  private OpenFeatureClient $client;

  public function __construct() {
    $this->client = OpenFeatureAPI::getInstance()->getClient('MyClass');
  }

  public function booleanExample(): UI
  {
      // Should we render the redesign? Or the default webpage? 
      if ($this->client->getBooleanValue('redesign_enabled', false)) {
          return render_redesign();
      }
      return render_normal();
  }

  public function stringExample(): Template
  {
      // Get the template to load for the custom new homepage
      $template = $this->client->getStringValue('homepage_template', 'default-homepage.html');

      return render_template($template);
  }

  public function numberExample(): array
  {
      // How many modules should we be fetching?
      $count = $this->client->getIntegerValue('module-fetch-count', 4);

      return fetch_modules($count);
  }

  public function structureExample(): HomepageModule
  {
      $obj = $this->client->getObjectValue('hero-module', $previouslyDefinedDefaultStructure);

      return HomepageModuleBuilder::new()
              ->title($obj->getValue('title'))
              ->body($obj->getValue('description'))
              ->build();
  }
}
```

### Configuration

To configure OpenFeature, you'll need to add a provider to the global singleton `OpenFeatureAPI`. From there, you can generate a `Client` which is usable by your code. If you do not set a provider, then the `NoOpProvider`, which simply returns the default passed in, will be used.

```php

use OpenFeature\OpenFeatureAPI;
use OpenFeature\Providers\Flagd\FlagdProvider;

class MyApp {
    public function bootstrap(){
        $api = OpenFeatureAPI.getInstance();
        $api->setProvider(new FlagdProvider());
        $client = $api->getClient();

        // Now use your `$client` instance to evaluate some feature flags!
    }
}
```

## Development

### PHP Versioning

This library targets PHP version 7.4 and newer. As long as you have any compatible version of PHP on your system you should be able to utilize the OpenFeature SDK.

This package also has a `.tool-versions` file for use with PHP version managers like `asdf`.

### Installation and Dependencies

Install dependencies with `composer install`. `composer install` will update the `composer.lock` with the most recent compatible versions.

We value having as few runtime dependencies as possible. The addition of any dependencies requires careful consideration and review.

### Testing

Run tests with `composer run test`.
