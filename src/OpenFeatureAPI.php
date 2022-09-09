<?php

declare(strict_types=1);

namespace OpenFeature;

use OpenFeature\implementation\flags\NoOpClient;
use OpenFeature\implementation\provider\NoOpProvider;
use OpenFeature\interfaces\common\LoggerAwareTrait;
use OpenFeature\interfaces\common\Metadata;
use OpenFeature\interfaces\flags\API;
use OpenFeature\interfaces\flags\Client;
use OpenFeature\interfaces\flags\EvaluationContext;
use OpenFeature\interfaces\hooks\Hook;
use OpenFeature\interfaces\provider\Provider;
use Psr\Log\LoggerAwareInterface;
use Throwable;

use function array_merge;
use function is_null;

class OpenFeatureAPI implements API, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private static ?OpenFeatureAPI $instance = null;

    //// TODO: Support global using $_SESSION?
    // private const GLOBAL_OPEN_FEATURE_KEY = '__OPENFEATURE_INSTANCE_ID__';

    private ?Provider $provider = null;

    /** @var Hook[] $hooks */
    private array $hooks = [];
    private ?EvaluationContext $evaluationContext = null;

    /**
     * -----------------
     * Requirement 1.1.1
     * -----------------
     * The API, and any state it maintains SHOULD exist as a global singleton, even
     * in cases wherein multiple versions of the API are present at runtime.
     */
    public static function getInstance(): API
    {
        //// TODO: Support global using $_SESSION?
        // if (isset($_SESSION)) {
        //     if (is_null($_SESSION[self::GLOBAL_OPEN_FEATURE_KEY])) {
        //         $_SESSION[self::GLOBAL_OPEN_FEATURE_KEY] = new self();
        //     }

        //     return $_SESSION[self::GLOBAL_OPEN_FEATURE_KEY];
        // }

        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Requirement 1.1.1
     *
     * The API, and any state it maintains SHOULD exist as a global singleton, even in cases wherein multiple versions of the API are present at runtime.
     *
     * It's important that multiple instances of the API not be active, so that state stored therein, such as the registered provider, static global
     * evaluation context, and globally configured hooks allow the API to behave predictably. This can be difficult in some runtimes or languages, but
     * implementors should make their best effort to ensure that only a single instance of the API is used.
     */
    private function __construct()
    {
       // no-op
    }

    public function getProvider(): Provider
    {
        if (!$this->provider) {
            return new NoOpProvider();
        }

        return $this->provider;
    }

    /**
     * -----------------
     * Requirement 1.1.2
     * -----------------
     * The API MUST provide a function to set the global provider singleton, which
     * accepts an API-conformant provider implementation.
     */
    public function setProvider(Provider $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * -----------------
     * Requirement 1.1.4
     * -----------------
     * The API MUST provide a function for retrieving the metadata field of the
     * configured provider.
     */
    public function getProviderMetadata(): Metadata
    {
        return $this->getProvider()->getMetadata();
    }

    /**
     * -----------------
     * Requirement 1.1.4
     * -----------------
     * The API MUST provide a function for creating a client which accepts the following options:
     *   name (optional): A logical string identifier for the client.
     */
    public function getClient(?string $name = null, ?string $version = null): Client
    {
        $name = $name ?? 'OpenFeature';
        $version = $version ?? 'OpenFeature';

        try {
            $client = new OpenFeatureClient($this, $name, $version);
            $client->setLogger($this->getLogger());

            return $client;
        } catch (Throwable $err) {
            return new NoOpClient();
        }
    }

    /**
     * @return Hook[]
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * -----------------
     * Requirement 1.1.3
     * -----------------
     * The API MUST provide a function to add hooks which accepts one or more API-conformant
     * hooks, and appends them to the collection of any previously added hooks. When new
     * hooks are added, previously added hooks are not removed.
     */
    public function addHooks(Hook ...$hooks): void
    {
        $this->hooks = array_merge($this->hooks, $hooks);
    }

    public function clearHooks(): void
    {
        $this->hooks = [];
    }

    public function getEvaluationContext(): ?EvaluationContext
    {
        return $this->evaluationContext;
    }

    public function setEvaluationContext(EvaluationContext $context): void
    {
        $this->evaluationContext = $context;
    }
}
