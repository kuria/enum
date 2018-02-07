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
abstract class Enum implements EnumInterface
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
    protected static $keyMap = [];

    /**
     * Key-value map (lazy)
     *
     * Format: class => [key => value, ...]
     *
     * @var array[]
     */
    protected static $keyToValueMap = [];

    /**
     * Value-key map (lazy)
     *
     * Format: class => [value => key, ...]
     * Caution: numeric string and null values are converted to integers and empty strings respectively by PHP
     *
     * @var array[]
     */
    protected static $valueToKeyMap = [];

    /**
     * Instance cache
     *
     * Format: class => [key => instance]
     *
     * @var array
     */
    protected static $instanceCache = [];

    /** @var string */
    protected $key;
    /** @var string|int|null */
    protected $value;

    /**
     * Internal constructor
     *
     * The key and value are assumed to be valid.
     *
     * @param string|int|null $value
     */
    protected function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
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
        static::ensureKeyMapLoaded();

        if (!isset(static::$keyMap[static::class][$name])) {
            throw new \BadMethodCallException(sprintf('Call to undefined static method %s::%s()', static::class, $name));
        }

        return static::$instanceCache[static::class][$name] ?? (static::$instanceCache[static::class][$name] = new static($name, static::$keyToValueMap[static::class][$name]));
    }

    /**
     * Get instance for the given key
     *
     * @throws InvalidKeyException
     * @return static
     */
    static function fromKey(string $key)
    {
        static::ensureKeyExists($key);

        return static::$instanceCache[static::class][$key] ?? (static::$instanceCache[static::class][$key] = new static($key, static::$keyToValueMap[static::class][$key]));
    }

    /**
     * Get instance for the given value
     *
     * @param string|int|null $value
     * @return static
     */
    static function fromValue($value)
    {
        $key = static::findKeyByValue($value);

        return static::$instanceCache[static::class][$key] ?? (static::$instanceCache[static::class][$key] = new static($key, static::$keyToValueMap[static::class][$key]));
    }

    static function hasKey(string $key): bool
    {
        static::ensureKeyMapLoaded();

        return isset(static::$keyMap[static::class][$key]);
    }

    /**
     * Check if the given value exists in this enum
     *
     * @param string|int|null $value
     */
    static function hasValue($value): bool
    {
        static::ensureValueToKeyMapLoaded();

        return isset(static::$valueToKeyMap[static::class][$value]);
    }

    /**
     * @throws InvalidKeyException
     * @return string|int|null
     */
    static function findValueByKey(string $key)
    {
        static::ensureKeyExists($key);

        return static::$keyToValueMap[static::class][$key];
    }

    /**
     * @param string|int|null $value
     * @throws InvalidValueException
     */
    static function findKeyByValue($value): string
    {
        static::ensureValueExists($value);

        return static::$valueToKeyMap[static::class][$value];
    }

    /**
     * @return string[]
     */
    static function getKeys(): array
    {
        static::ensureKeyToValueMapLoaded();

        return array_keys(static::$keyToValueMap[static::class]);
    }

    /**
     * @return string[]|int[]|null[]
     */
    static function getValues(): array
    {
        static::ensureKeyToValueMapLoaded();

        return array_values(static::$keyToValueMap[static::class]);
    }

    static function getKeyMap(): array
    {
        static::ensureKeyMapLoaded();

        return static::$keyMap[static::class];
    }

    static function getKeyToValueMap(): array
    {
        static::ensureKeyToValueMapLoaded();

        return static::$keyToValueMap[static::class];
    }

    static function getValueToKeyMap(): array
    {
        static::ensureValueToKeyMapLoaded();

        return static::$valueToKeyMap[static::class];
    }

    function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string|int|null
     */
    function getValue()
    {
        return $this->value;
    }

    function is(string $key): bool
    {
        return $this->key === $key;
    }

    /**
     * @param string|int|null $value
     */
    function equals($value): bool
    {
        static::ensureValueToKeyMapLoaded();

        return $this->key === (static::$valueToKeyMap[static::class][$value] ?? null);
    }

    /**
     * @throws InvalidKeyException if the key does not exist
     */
    static function ensureKeyExists(string $key)
    {
        if (!static::hasKey($key)) {
            throw new InvalidKeyException(sprintf(
                'The key "%s" is not defined in enum class "%s", known keys: %s',
                $key,
                static::class,
                implode(', ', static::getKeys())
            ));
        }
    }

    /**
     * @param string|int|null $value
     * @throws InvalidValueException if the value does not exist
     */
    static function ensureValueExists($value)
    {
        if (!static::hasValue($value)) {
            throw new InvalidValueException(sprintf(
                'The value %s is not defined in enum class "%s", known values: %s',
                static::dumpValue($value),
                static::class,
                implode(', ', array_map([static::class, 'dumpValue'], static::getValues()))
            ));
        }
    }

    protected static function ensureKeyMapLoaded()
    {
        isset(static::$keyMap[static::class]) or static::loadKeyMap();
    }

    protected static function ensureKeyToValueMapLoaded()
    {
        isset(static::$keyToValueMap[static::class]) or static::loadKeyToValueMap();
    }

    protected static function ensureValueToKeyMapLoaded()
    {
        isset(static::$valueToKeyMap[static::class]) or static::loadValueToKeyMap();
    }

    protected static function loadKeyMap()
    {
        static::ensureKeyToValueMapLoaded();

        static::$keyMap[static::class] = array_fill_keys(
            array_keys(static::$keyToValueMap[static::class]),
            true
        );
    }

    protected static function loadKeyToValueMap()
    {
        static::$keyToValueMap[static::class] = static::determineKeyToValueMap();
    }

    protected static function loadValueToKeyMap()
    {
        static::ensureKeyToValueMapLoaded();

        $valueToKeyMap = [];

        foreach (static::$keyToValueMap[static::class] as $key => $value) {
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
                    static::dumpValue($value),
                    $key,
                    static::class,
                    static::dumpValue(static::$keyToValueMap[static::class][$valueToKeyMap[$value]]),
                    $valueToKeyMap[$value]
                ));
            }

            $valueToKeyMap[$value] = $key;
        }

        static::$valueToKeyMap[static::class] = $valueToKeyMap;
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
        // use all public constants of current class
        $keyToValueMap = [];

        foreach ((new \ReflectionClass(static::class))->getReflectionConstants() as $constant) {
            if ($constant->isPublic()) {
                $keyToValueMap[$constant->name] = $constant->getValue();
            }
        }

        return $keyToValueMap;
    }

    /**
     * @param string|int|null $value
     */
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
