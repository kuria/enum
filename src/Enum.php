<?php declare(strict_types=1);

namespace Kuria\Enum;

use Kuria\Enum\Exception\DuplicateValueException;
use Kuria\Enum\Exception\InvalidKeyException;
use Kuria\Enum\Exception\InvalidValueException;

/**
 * Base enumeration class
 *
 * Subclasses should define class constants which will be used as the source of enumeration keys and values.
 *
 * @see Enum::determineKeyToValueMap() to extend or replace this behavior
 *
 * - only string, integer and null values are supported
 * - values are looked up and compared with the same type-coercion rules as PHP array keys
 * - values must be unique when used as an array key
 * - instances are cached and should be immutable
 */
abstract class Enum
{
    /**
     * Key map (lazy)
     *
     * Used for quick key existence checks even if the key maps to a NULL value.
     *
     * Format: class => [key => true]
     *
     * @var array
     */
    private static $keyMap = [];

    /**
     * Key-value map (lazy)
     *
     * Format: class => [key => value, ...]
     *
     * @var array[]
     */
    private static $keyToValueMap = [];

    /**
     * Value-key map (lazy)
     *
     * Format: class => [value => key, ...]
     * Caution: numeric string and null values are converted to integers and empty strings respectively by PHP
     *
     * @var array[]
     */
    private static $valueToKeyMap = [];

    /**
     * Instance cache
     *
     * Format: class => [key => instance]
     *
     * @var array
     */
    private static $instanceCache = [];

    /** @var string */
    private $key;

    /** @var mixed */
    private $value;

    /**
     * Internal constructor
     *
     * The key and value are assumed to be valid.
     */
    protected function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Enum instance must not be cloned to ensure a single instance per key-value pair
     *
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    function __toString(): string
    {
        return (string) $this->value;
    }

    function __debugInfo(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }

    /**
     * Magic factory method Enum::SOME_KEY()
     *
     * Arguments are ignored.
     *
     * @throws \BadMethodCallException if method name does not correspond to a known key
     * @return static
     */
    static function __callStatic(string $name, array $arguments)
    {
        self::ensureKeyMapLoaded();

        if (!isset(self::$keyMap[static::class][$name])) {
            throw new \BadMethodCallException(sprintf('Call to undefined static method %s::%s()', static::class, $name));
        }

        return self::$instanceCache[static::class][$name]
            ?? (self::$instanceCache[static::class][$name] = new static($name, self::$keyToValueMap[static::class][$name]));
    }

    /**
     * Get instance for the given key
     *
     * @throws InvalidKeyException if there is no such key
     * @return static
     */
    static function fromKey(string $key)
    {
        self::ensureKey($key);

        return self::$instanceCache[static::class][$key]
            ?? (self::$instanceCache[static::class][$key] = new static($key, self::$keyToValueMap[static::class][$key]));
    }

    /**
     * Get instance for the given value
     *
     * @throws InvalidValueException if there is no such value
     * @return static
     */
    static function fromValue($value)
    {
        $key = self::getKey($value);

        return self::$instanceCache[static::class][$key]
            ?? (self::$instanceCache[static::class][$key] = new static($key, self::$keyToValueMap[static::class][$key]));
    }

    /**
     * Check if the given key exists in this enum
     */
    static function hasKey(string $key): bool
    {
        self::ensureKeyMapLoaded();

        return isset(self::$keyMap[static::class][$key]);
    }

    /**
     * Check if the given value exists in this enum
     */
    static function hasValue($value): bool
    {
        self::ensureValueToKeyMapLoaded();

        return isset(self::$valueToKeyMap[static::class][$value]);
    }

    /**
     * @throws InvalidKeyException if there is no such key
     */
    static function getValue(string $key)
    {
        self::ensureKey($key);

        return self::$keyToValueMap[static::class][$key];
    }

    /**
     * Attempt to find a value by its key. Returns NULL on failure.
     */
    static function findValue(string $key)
    {
        return self::hasKey($key)
            ? self::$keyToValueMap[static::class][$key]
            : null;
    }

    /**
     * @throws InvalidValueException if there is no such value
     */
    static function getKey($value): string
    {
        self::ensureValue($value);

        return self::$valueToKeyMap[static::class][$value];
    }

    /**
     * Attempt to find a key by its value. Returns NULL on failure.
     */
    static function findKey($value): ?string
    {
        return self::hasValue($value)
            ? self::$valueToKeyMap[static::class][$value]
            : null;
    }

    /**
     * @return string[]
     */
    static function getKeys(): array
    {
        self::ensureKeyToValueMapLoaded();

        return array_keys(self::$keyToValueMap[static::class]);
    }

    static function getValues(): array
    {
        self::ensureKeyToValueMapLoaded();

        return array_values(self::$keyToValueMap[static::class]);
    }

    /**
     * Get a key => value map
     */
    static function getMap(): array
    {
        self::ensureKeyToValueMapLoaded();

        return self::$keyToValueMap[static::class];
    }

    /**
     * Get a key => true map
     */
    static function getKeyMap(): array
    {
        self::ensureKeyMapLoaded();

        return self::$keyMap[static::class];
    }

    /**
     * Get a value => key map
     */
    static function getValueMap(): array
    {
        self::ensureValueToKeyMapLoaded();

        return self::$valueToKeyMap[static::class];
    }

    /**
     * @throws InvalidValueException if there is no such value
     */
    static function getPair($value): array
    {
        $key = self::getKey($value);

        return [$key => self::$keyToValueMap[static::class][$key]];
    }

    /**
     * @throws InvalidKeyException if there is no such key
     */
    static function getPairByKey(string $key): array
    {
        self::ensureKey($key);

        return [$key => self::$keyToValueMap[static::class][$key]];
    }

    static function count(): int
    {
        self::ensureKeyToValueMapLoaded();

        return count(self::$keyToValueMap[static::class]);
    }

    function key(): string
    {
        return $this->key;
    }

    function value()
    {
        return $this->value;
    }

    function pair(): array
    {
        return [$this->key => $this->value];
    }

    function is(string $key): bool
    {
        return $this->key === $key;
    }

    function equals($value): bool
    {
        self::ensureValueToKeyMapLoaded();

        return $this->key === (self::$valueToKeyMap[static::class][$value] ?? null);
    }

    /**
     * Ensure that a key exists
     *
     * @throws InvalidKeyException if there is no such key
     */
    static function ensureKey(string $key)
    {
        if (!self::hasKey($key)) {
            throw new InvalidKeyException(sprintf(
                'The key "%s" is not defined in enum class "%s", known keys: %s',
                $key,
                static::class,
                implode(', ', self::getKeys())
            ));
        }
    }

    /**
     * Ensure that a value exists
     *
     * @throws InvalidValueException if there is no such value
     */
    static function ensureValue($value)
    {
        if (!self::hasValue($value)) {
            throw new InvalidValueException(sprintf(
                'The value %s is not defined in enum class "%s", known values: %s',
                self::dumpValue($value),
                static::class,
                implode(', ', array_map([static::class, 'dumpValue'], self::getValues()))
            ));
        }
    }

    private static function ensureKeyMapLoaded()
    {
        isset(self::$keyMap[static::class]) or self::loadKeyMap();
    }

    private static function ensureKeyToValueMapLoaded()
    {
        isset(self::$keyToValueMap[static::class]) or self::loadKeyToValueMap();
    }

    private static function ensureValueToKeyMapLoaded()
    {
        isset(self::$valueToKeyMap[static::class]) or self::loadValueToKeyMap();
    }

    private static function loadKeyMap()
    {
        self::ensureKeyToValueMapLoaded();

        self::$keyMap[static::class] = array_fill_keys(
            array_keys(self::$keyToValueMap[static::class]),
            true
        );
    }

    private static function loadKeyToValueMap()
    {
        self::$keyToValueMap[static::class] = static::determineKeyToValueMap();
    }

    private static function loadValueToKeyMap()
    {
        self::ensureKeyToValueMapLoaded();

        $valueToKeyMap = [];

        foreach (self::$keyToValueMap[static::class] as $key => $value) {
            assert(
                is_int($value) || is_string($value) || is_null($value),
                new InvalidValueException(sprintf(
                    'Only integer, string and null values are allowed, but found %s value for key "%s" in enum class "%s"',
                    gettype($value),
                    $key,
                    static::class
                ))
            );

            if (isset($valueToKeyMap[$value])) {
                throw new DuplicateValueException(sprintf(
                    'Duplicate value %s for key "%s" in enum class "%s". Value %s is already defined for key "%s".',
                    self::dumpValue($value),
                    $key,
                    static::class,
                    self::dumpValue(self::$keyToValueMap[static::class][$valueToKeyMap[$value]]),
                    $valueToKeyMap[$value]
                ));
            }

            $valueToKeyMap[$value] = $key;
        }

        self::$valueToKeyMap[static::class] = $valueToKeyMap;
    }

    /**
     * Determine keys and values containing in this enum
     *
     * The returned array must have string keys and string|int|null values.
     *
     * @return array
     */
    protected static function determineKeyToValueMap(): array
    {
        // default behavior is to use all public constants of the current class
        $keyToValueMap = [];

        foreach ((new \ReflectionClass(static::class))->getReflectionConstants() as $constant) {
            if ($constant->isPublic()) {
                $keyToValueMap[$constant->name] = $constant->getValue();
            }
        }

        return $keyToValueMap;
    }

    private static function dumpValue($value): string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        } elseif (is_null($value)) {
            return 'NULL';
        } else {
            return (string) $value;
        }
    }
}
