<?php declare(strict_types=1);

namespace Kuria\Enum;

use Kuria\Enum\Exception\InvalidKeyException;
use Kuria\Enum\Exception\InvalidMethodException;
use Kuria\Enum\Exception\InvalidValueException;

/**
 * Base enumeration class that supports instantiation
 *
 * @see EnumObject::fromKey()
 * @see EnumObject::fromValue()
 * @see EnumObject::__callStatic()
 *
 * Instances are cached an reused, so there should be only one instance per enum pair.
 */
abstract class EnumObject extends Enum
{
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

    private function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Enum instance must not be cloned to ensure a single instance per key-value pair
     *
     * @codeCoverageIgnore
     */
    final private function __clone()
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
     * Magic factory method EnumObject::SOME_KEY()
     *
     * Arguments are ignored.
     *
     * @throws InvalidMethodException if method name does not correspond to a known key
     * @return static
     */
    static function __callStatic(string $name, array $arguments)
    {
        if (!static::hasKey($name)) {
            throw new InvalidMethodException(sprintf('Call to undefined static method %s::%s()', static::class, $name));
        }

        return self::$instanceCache[static::class][$name]
            ?? (self::$instanceCache[static::class][$name] = new static($name, static::getValue($name)));
    }

    /**
     * Get instance for the given key
     *
     * @throws InvalidKeyException if there is no such key
     * @return static
     */
    static function fromKey(string $key)
    {
        return self::$instanceCache[static::class][$key]
            ?? (self::$instanceCache[static::class][$key] = new static($key, static::getValue($key)));
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
            ?? (self::$instanceCache[static::class][$key] = new static($key, static::getValue($key)));
    }

    /**
     * Get key of this key-value pair
     */
    function key(): string
    {
        return $this->key;
    }

    /**
     * Get value of this key-value pair
     */
    function value()
    {
        return $this->value;
    }

    /**
     * Get this key-value pair as an array
     */
    function pair(): array
    {
        return [$this->key => $this->value];
    }

    /**
     * Compare key of this key-value pair with another key
     */
    function is(string $key): bool
    {
        return $this->key === $key;
    }

    /**
     * Compare value of this key-value pair with another value
     */
    function equals($value): bool
    {
        return $this->key === static::findKey($value);
    }
}
