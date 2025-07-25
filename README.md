<!-- markdownlint-disable MD033 -->
<!-- x-hide-in-docs-start -->
<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/open-feature/community/0e23508c163a6a1ac8c0ced3e4bd78faafe627c7/assets/logo/horizontal/white/openfeature-horizontal-white.svg" />
    <img align="center" alt="OpenFeature Logo" src="https://raw.githubusercontent.com/open-feature/community/0e23508c163a6a1ac8c0ced3e4bd78faafe627c7/assets/logo/horizontal/black/openfeature-horizontal-black.svg" />
  </picture>
</p>

<h2 align="center">OpenFeature PHP SDK</h2>

<!-- x-hide-in-docs-end -->
<!-- The 'github-badges' class is used in the docs -->
<p align="center" class="github-badges">

  <a href="https://github.com/open-feature/spec/releases/tag/v0.5.1">
    <img alt="Specification" src="https://img.shields.io/static/v1?label=specification&message=v0.5.1&color=yellow&style=for-the-badge" />
  </a>
  <!-- x-release-please-start-version -->

  <a href="https://github.com/open-feature/php-sdk/releases/tag/2.0.11">
    <img alt="Release" src="https://img.shields.io/static/v1?label=release&message=v2.0.11&color=blue&style=for-the-badge" />
  </a>  

  <!-- x-release-please-end -->
  <br/>

  <a href="https://packagist.org/packages/open-feature/sdk">
    <img alt="Total Downloads" src="https://poser.pugx.org/open-feature/sdk/downloads" />
  </a>

  <a>
    <img alt="PHP 8.0+" src="https://img.shields.io/badge/php->=8.0-blue.svg" />
  </a>

  <a href="https://packagist.org/packages/open-feature/sdk">
    <img alt="License" src="https://poser.pugx.org/open-feature/sdk/license" />
  </a>
  
  <a href="https://bestpractices.coreinfrastructure.org/projects/6853">
    <img alt="OpenSSF Best Practices" src="https://bestpractices.coreinfrastructure.org/projects/6853/badge" />
  </a>

</p>
<!-- x-hide-in-docs-start -->

[OpenFeature](https://openfeature.dev) is an open specification that provides a vendor-agnostic, community-driven API for feature flagging that works with your favorite feature flag management tool.

<!-- x-hide-in-docs-end -->
## 🚀 Quick start

### Requirements

This library targets PHP version 8.0 and newer. As long as you have any compatible version of PHP on your system you should be able to utilize the OpenFeature SDK.

This package also has a `.tool-versions` file for use with PHP version managers like `asdf`.

### Install

```shell
composer require open-feature/sdk
```

### Usage

```php
use OpenFeature\OpenFeatureAPI;
use OpenFeature\Providers\Flagd\FlagdProvider;

function example()
{
    $api = OpenFeatureAPI::getInstance();
    
    // configure a provider
    $api->setProvider(new FlagdProvider());

    // create a client
    $client = $api->getClient();
    
    // get a bool flag value
    $client->getBooleanValue('v2_enabled', false);
}
```

#### Extended Example

```php
use OpenFeature\OpenFeatureClient;

class MyClass 
{
  private OpenFeatureClient $client;

  public function __construct() 
  {
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

## 🌟 Features

| Status | Features                        | Description                                                                                                                        |
| ------ | ------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| ✅      | [Providers](#providers)         | Integrate with a commercial, open source, or in-house feature management tool.                                                     |
| ✅      | [Targeting](#targeting)         | Contextually-aware flag evaluation using [evaluation context](https://openfeature.dev/docs/reference/concepts/evaluation-context). |
| ✅      | [Hooks](#hooks)                 | Add functionality to various stages of the flag evaluation life-cycle.                                                             |
| ✅      | [Logging](#logging)             | Integrate with popular logging packages.                                                                                           |
| ❌      | [Named clients](#named-clients) | Utilize multiple providers in a single application.                                                                                |
| ⚠️      | [Eventing](#eventing)           | React to state changes in the provider or flag management system.                                                                  |
| ❌      | [Shutdown](#shutdown)           | Gracefully clean up a provider during application shutdown.                                                                        |
| ✅      | [Extending](#extending)         | Extend OpenFeature with custom providers and hooks.                                                                                |

<sub>Implemented: ✅ | In-progress: ⚠️ | Not implemented yet: ❌</sub>

### Providers

[Providers](https://openfeature.dev/docs/reference/concepts/provider) are an abstraction between a flag management system and the OpenFeature SDK.
Look [here](https://openfeature.dev/ecosystem?instant_search%5BrefinementList%5D%5Btype%5D%5B0%5D=Provider&instant_search%5BrefinementList%5D%5Btechnology%5D%5B0%5D=PHP) for a complete list of available providers.
If the provider you're looking for hasn't been created yet, see the [develop a provider](#develop-a-provider) section to learn how to build it yourself.

Once you've added a provider as a dependency, it can be registered with OpenFeature like this:

```php
$api = OpenFeatureAPI::getInstance();
$api->setProvider(new MyProvider());
```

<!-- In some situations, it may be beneficial to register multiple providers in the same application.
This is possible using [named clients](#named-clients), which is covered in more detail below. -->

### Targeting

Sometimes, the value of a flag must consider some dynamic criteria about the application or user, such as the user's location, IP, email address, or the server's location.
In OpenFeature, we refer to this as [targeting](https://openfeature.dev/specification/glossary#targeting).
If the flag management system you're using supports targeting, you can provide the input data using the [evaluation context](https://openfeature.dev/docs/reference/concepts/evaluation-context).

```php
// add a value to the global context
$api = OpenFeatureAPI::getInstance();
$api->setEvaluationContext(new EvaluationContext('targetingKey', ['myGlobalKey' => 'myGlobalValue']));

// add a value to the client context
$client = $api->getClient();
$client->setEvaluationContext(new EvaluationContext('targetingKey', ['myClientKey' => 'myClientValue']));

// add a value to the invocation context
$context = new EvaluationContext('targetingKey', ['myInvocationKey' => 'myInvocationValue']);

$boolValue = $client->getBooleanValue('boolFlag', false, $context);
```

### Hooks

[Hooks](https://openfeature.dev/docs/reference/concepts/hooks) allow for custom logic to be added at well-defined points of the flag evaluation life-cycle.
Look [here](https://openfeature.dev/ecosystem/?instant_search%5BrefinementList%5D%5Btype%5D%5B0%5D=Hook&instant_search%5BrefinementList%5D%5Btechnology%5D%5B0%5D=php) for a complete list of available hooks.
If the hook you're looking for hasn't been created yet, see the [develop a hook](#develop-a-hook) section to learn how to build it yourself.

Once you've added a hook as a dependency, it can be registered at the global, client, or flag invocation level.

```php
// add a hook globally, to run on all evaluations
$api = OpenFeatureAPI::getInstance();
$api->addHook(new ExampleGlobalHook());

// add a hook on this client, to run on all evaluations made by this client
$client = $api->getClient();
$client->addHook(new ExampleClientHook());

// add a hook for this evaluation only
$value = $client->getBooleanValue("boolFlag", false, $context, new EvaluationOptions([new ExampleInvocationHook()]));
```

### Logging

The PHP SDK utilizes several of the PHP Standards Recommendation, one of those being [PSR-3](https://www.php-fig.org/psr/psr-3/) which provides a standard `LoggerInterface`.
The SDK makes use of a `LoggerAwareTrait` on several components, including the client for flag evaluation, the hook executor, and the global `OpenFeatureAPI` instance.
When an OpenFeature client is created by the API, it will automatically utilize the configured logger in the API for it. The logger set in the client is also automatically used for the hook execution.

> ⚠️ Once the client is instantiated, updates to the API's logger will not synchronize. This is done to support the separation of named clients. If you must update an existing client's logger, do so directly!

```php
$api = OpenFeatureAPI::getInstance();

$logger = new FancyLogger();

$defaultLoggerClient = $api->getClient('default-logger');

$api->setLogger(new CustomLogger());

$customLoggerClient = $api->getClient('custom-logger');

$overrideLoggerClient = $api->getClient('override-logger');
$overrideLoggerClient->setLogger($logger);

// now let's do some evaluations with these!

$defaultLoggerClient->getBooleanValue('A', false);
// uses default logger in the SDK

$customLoggerClient->getBooleanValue('B', false);
// uses the CustomLogger set in the API before the client was made

$overrideLoggerClient->getBooleanValue('C', false);
// uses the FancyLogger set directly on the client
```

### Named clients

Named clients are not yet available in the PHP SDK. Progress on this feature can be tracked [here](https://github.com/open-feature/php-sdk/issues/93).

### Eventing

Events are not yet available in the PHP SDK. Progress on this feature can be tracked [here](https://github.com/open-feature/php-sdk/issues/93).

### Shutdown

A shutdown method is not yet available in the PHP SDK. Progress on this feature can be tracked [here](https://github.com/open-feature/php-sdk/issues/93).

## Extending

### Develop a provider

To develop a provider, you need to create a new project and include the OpenFeature SDK as a dependency.
This can be a new repository or included in [the existing contrib repository](https://github.com/open-feature/php-sdk-contrib) available under the OpenFeature organization.
You’ll then need to write the provider by implementing the `Provider` interface exported by the OpenFeature SDK.

```php
declare(strict_types=1);

namespace OpenFeature\Example\Providers;

use OpenFeature\implementation\common\Metadata;
use OpenFeature\interfaces\common\Metadata as IMetadata;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\provider\ResolutionDetails;

class ExampleProviderImplementation implements Provider
{
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;

        // or, utilize the OpenFeature\interfaces\common\LoggerAwareTrait
    }

    /**
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return $this->hooks; // implement according to the OpenFeature specification
    }

    /**
     * Returns the metadata for the current resource
     */
    public function getMetadata(): IMetadata
    {
        return new Metadata(self::class);
    }

    public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        // resolve some ResolutionDetails
    }

    public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        // resolve some ResolutionDetails
    }

    public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        // resolve some ResolutionDetails
    }

    public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        // resolve some ResolutionDetails
    }

    /**
     * @inheritdoc
     */
    public function resolveObjectValue(string $flagKey, array $defaultValue, ?EvaluationContext $context = null): ResolutionDetails
    {
        // resolve some ResolutionDetails
    }
}
```

As you can see, this ends up requiring some boilerplate to fulfill all of the functionality that a Provider expects.
Another option for implementing a provider is to utilize the AbstractProvider base class.
This provides some internally wiring and simple scaffolding so you can skip some of it and focus on what's most important: resolving feature flags!

```php
declare(strict_types=1);

namespace OpenFeature\Example\Providers;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\provider\ResolutionDetails;

class ExampleProviderExtension extends AbstractProvider
{
    protected static string $NAME = self::class;

    public function resolveBooleanValue(string $flagKey, bool $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        // resolve some ResolutionDetails
    }

    public function resolveStringValue(string $flagKey, string $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        // resolve some ResolutionDetails
    }

    public function resolveIntegerValue(string $flagKey, int $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        // resolve some ResolutionDetails
    }

    public function resolveFloatValue(string $flagKey, float $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        // resolve some ResolutionDetails
    }

    /**
     * @inheritdoc
     */
    public function resolveObjectValue(string $flagKey, array $defaultValue, ?EvaluationContext $context = null): ResolutionDetailsInterface
    {
        // resolve some ResolutionDetails
    }
}
```

> Built a new provider? [Let us know](https://github.com/open-feature/openfeature.dev/issues/new?assignees=&labels=provider&projects=&template=document-provider.yaml&title=%5BProvider%5D%3A+) so we can add it to the docs!

### Develop a hook

To develop a hook, you need to create a new project and include the OpenFeature SDK as a dependency.
This can be a new repository or included in [the existing contrib repository](https://github.com/open-feature/php-sdk-contrib) available under the OpenFeature organization.
Implement your own hook by conforming to the `Hook interface`.
To satisfy the interface, all methods (`Before`/`After`/`Finally`/`Error`) need to be defined.
To avoid defining empty functions, make use of the `UnimplementedHook` struct (which already implements all the empty functions).

```php
declare(strict_types=1);

namespace OpenFeature\Example\Hooks;

use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints;
use OpenFeature\interfaces\provider\ResolutionDetails;


class ExampleStringHookImplementation implements Hook
{
    public function before(HookContext $context, HookHints $hints): ?EvaluationContext
    {
    }

    public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
    {
    }

    public function error(HookContext $context, Throwable $error, HookHints $hints): void
    {
    }

    public function finally(HookContext $context, HookHints $hints): void
    {
    }


    public function supportsFlagValueType(string $flagValueType): bool
    {
        return $flagValueType === FlagValueType::STRING;
    }
}
```

You can also make use of existing base classes for various types and behaviors.
Suppose you want to make this same hook, and have no limitation around extending a base class, you could do the following:

```php
declare(strict_types=1);

namespace OpenFeature\Example\Hooks;

use OpenFeature\implementation\hooks\StringHook;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\flags\FlagValueType;
use OpenFeature\interfaces\hooks\HookContext;
use OpenFeature\interfaces\hooks\HookHints;
use OpenFeature\interfaces\provider\ResolutionDetails;


class ExampleStringHookExtension extends StringHook
{
    public function before(HookContext $context, HookHints $hints): ?EvaluationContext
    {
    }

    public function after(HookContext $context, ResolutionDetails $details, HookHints $hints): void
    {
    }

    public function error(HookContext $context, Throwable $error, HookHints $hints): void
    {
    }

    public function finally(HookContext $context, HookHints $hints): void
    {
    }
}
```

> Built a new hook? [Let us know](https://github.com/open-feature/openfeature.dev/issues/new?assignees=&labels=hook&projects=&template=document-hook.yaml&title=%5BHook%5D%3A+) so we can add it to the docs!

<!-- x-hide-in-docs-start -->
## ⭐️ Support the project

- Give this repo a ⭐️!
- Follow us on social media:
  - Twitter: [@openfeature](https://twitter.com/openfeature)
  - LinkedIn: [OpenFeature](https://www.linkedin.com/company/openfeature/)
- Join us on [Slack](https://cloud-native.slack.com/archives/C0344AANLA1)
- For more, check out our [community page](https://openfeature.dev/community/)

## 🤝 Contributing

Interested in contributing? Great, we'd love your help! To get started, take a look at the [CONTRIBUTING](CONTRIBUTING.md) guide.

### Thanks to everyone who has already contributed

<a href="https://github.com/open-feature/php-sdk/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=open-feature/php-sdk" alt="Pictures of the folks who have contributed to the project" />
</a>



Made with [contrib.rocks](https://contrib.rocks).
<!-- x-hide-in-docs-end -->
