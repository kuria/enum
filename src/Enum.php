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
     * Enums cannot be instantiated
     *
     * @see EnumObject
     * @codeCoverageIgnore
     */
    private function __construct()
    {
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

    /**
     * Get a list of all values
     */
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

    /**
     * Get total number of key-value pairs
     */
    static function count(): int
    {
        self::ensureKeyToValueMapLoaded();

        return count(self::$keyToValueMap[static::class]);
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

    protected static function dumpValue($value): string
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
