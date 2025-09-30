<?php

declare(strict_types=1);

namespace OpenFeature\implementation\multiprovider;

use InvalidArgumentException;
use OpenFeature\interfaces\provider\Provider;
use OpenFeature\interfaces\strategy\Strategy as StrategyInterface;
use Psr\Log\LoggerAwareTrait;

use function array_count_values;
use function array_diff;
use function array_filter;
use function array_keys;
use function array_map;
use function count;
use function implode;
use function strtolower;
use function trim;

class Multiprovider
{
    use LoggerAwareTrait;

    /**
     * List of supported keys in each provider data entry.
     *
     * @var array<int, string>
     */
    private static array $supportedProviderData = [
        'name', 'provider',
    ];

    public const NAME = 'Multiprovider';

    /**
     * @var array<string, Provider> Providers indexed by their names.
     */
    protected array $providersByName = [];

    /**
     * Multiprovider constructor.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     * @param StrategyInterface|null $strategy Optional strategy instance.
     */
    public function __construct(array $providerData = [], protected ?StrategyInterface $strategy = null)
    {
        $this->validateProviderData($providerData);
        $this->registerProviders($providerData);
    }

    /**
     * Validate the provider data array.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     *
     * @throws InvalidArgumentException If unsupported keys, invalid names, or duplicate names are found.
     */
    private function validateProviderData(array $providerData): void
    {
        foreach ($providerData as $index => $entry) {
            // check that entry contains only supported keys
            $unSupportedKeys = array_diff(array_keys($entry), self::$supportedProviderData);
            if (count($unSupportedKeys) !== 0) {
                throw new InvalidArgumentException(
                    'Unsupported keys in provider data entry at index ' . $index . ': ' . implode(', ', $unSupportedKeys),
                );
            }
            if (isset($entry['name']) && trim($entry['name']) === '') {
                throw new InvalidArgumentException(
                    'Each provider data entry must have a non-empty string "name" key at index ' . $index,
                );
            }
        }

        $names = array_map(fn ($entry) => $entry['name'] ?? null, $providerData);
        $nameCounts = array_count_values(array_filter($names)); // filter out nulls, count occurrences of each name
        $duplicateNames = array_keys(array_filter($nameCounts, fn ($count) => $count > 1)); // filter by count > 1 to get duplicates

        if ($duplicateNames !== []) {
            throw new InvalidArgumentException('Duplicate provider names found: ' . implode(', ', $duplicateNames));
        }
    }

    /**
     * Register providers by their names.
     *
     * @param array<int, array{name?: string, provider: Provider}> $providerData Array of provider data entries.
     *
     * @throws InvalidArgumentException If duplicate provider names are detected during assignment.
     */
    private function registerProviders(array $providerData): void
    {
        $counts = []; // track how many times a base name is used

        foreach ($providerData as $entry) {
            if (isset($entry['name']) && $entry['name'] !== '') {
                $this->providersByName[$entry['name']] = $entry['provider'];
            } else {
                $name = $this->uniqueProviderName($entry['provider']->getMetadata()->getName(), $counts);
                if (isset($this->providersByName[$name])) {
                    throw new InvalidArgumentException('Duplicate provider name detected during assignment: ' . $name);
                }
                $this->providersByName[$name] = $entry['provider'];
            }
        }
    }

    /**
     * Generate a unique provider name by appending a count suffix if necessary.
     * E.g., if "ProviderA" is used twice, the second instance becomes "ProviderA_2".
     *
     * @param string $name The base name of the provider.
     * @param array<string, int> $count Reference to an associative array tracking name counts.
     *
     * @return string A unique provider name.
     */
    private function uniqueProviderName(string $name, array &$count): string
    {
        $key = strtolower($name);
        $count[$key] = ($count[$key] ?? 0) + 1;

        return $count[$key] > 1 ? $name . '_' . $count[$key] : $name;
    }
}
