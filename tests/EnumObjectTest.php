<?php declare(strict_types=1);

namespace Kuria\Enum;

use Kuria\DevMeta\Test;
use Kuria\Enum\Exception\InvalidKeyException;
use Kuria\Enum\Exception\InvalidMethodException;
use Kuria\Enum\Exception\InvalidValueException;
use Kuria\Enum\TestSubject\IntNullEnumObject;
use Kuria\Enum\TestSubject\StringEnumObject;
use Kuria\Enum\TestSubject\TestEnumObject;

class EnumObjectTest extends Test
{
    /**
     * @dataProvider provideKeyValue
     */
    function testShouldCreateInstance(string $key, $value)
    {
        /** @var TestEnumObject $enum */

        $enum = TestEnumObject::fromKey($key);
        $this->assertInstanceOf(TestEnumObject::class, $enum);
        $this->assertSame($key, $enum->key());
        $this->assertSame($value, $enum->value());

        $enum = TestEnumObject::fromValue($value);
        $this->assertInstanceOf(TestEnumObject::class, $enum);
        $this->assertSame($key, $enum->key());
        $this->assertSame($value, $enum->value());

        $enum = TestEnumObject::$key();
        $this->assertInstanceOf(TestEnumObject::class, $enum);
        $this->assertSame($key, $enum->key());
        $this->assertSame($value, $enum->value());
    }

    /**
     * @dataProvider provideKeyValue
     */
    function testShouldCacheInstances(string $key, $value)
    {
        $instance = TestEnumObject::fromKey($key);

        $this->assertSame($instance, TestEnumObject::fromKey($key));
        $this->assertSame($instance, TestEnumObject::fromValue($value));
        $this->assertSame($instance, TestEnumObject::$key());
    }

    function testShouldNotBeCloneaeble()
    {
        $this->assertFalse((new \ReflectionClass(TestEnumObject::class))->isCloneable());
    }

    function testShouldThrowExceptionWhenCreatingFromInvalidKey()
    {
        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage(
            'The key "__NONEXISTENT_KEY__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnumObject"'
            . ', known keys: LOREM, IPSUM, DOLOR'
        );

        TestEnumObject::fromKey('__NONEXISTENT_KEY__');
    }

    function testShouldThrowExceptionWhenCreatingFromInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(
            'The value "__NONEXISTENT_VALUE__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnumObject"'
            . ', known values: "foo", 123, NULL'
        );

        TestEnumObject::fromValue('__NONEXISTENT_VALUE__');
    }

    function testShouldThrowExceptionWhenCallingUnknownFactoryMethod()
    {
        $this->expectException(InvalidMethodException::class);
        $this->expectExceptionMessage('Call to undefined static method Kuria\Enum\TestSubject\TestEnumObject::_UNKNOWN_KEY_()');

        TestEnumObject::_UNKNOWN_KEY_();
    }

    /**
     * @dataProvider provideKeyValue
     */
    function testShouldPerformObjectOperations(string $key, $value)
    {
        $enum = TestEnumObject::fromValue($value);

        $this->assertSame($key, $enum->key());
        $this->assertSame($value, $enum->value());
        $this->assertSame([$key => $value], $enum->pair());
        $this->assertTrue($enum->is($key));
        $this->assertFalse($enum->is('__NOT_A_CURRENT_KEY__'));
        $this->assertTrue($enum->equals($value));
        $this->assertFalse($enum->equals('__NOT_A_CURRENT_VALUE__'));
        $this->assertSame((string) $value, (string) $enum);
        $this->assertSame(['key' => $key, 'value' => $value], $enum->__debugInfo());
    }

    /**
     * @dataProvider provideCoercibleValues
     */
    function testShouldPerformValueTypeCoercion(string $enumClass, $actualValue, $coercibleValue)
    {
        /** @var EnumObject $enumClass */
        $enum = $enumClass::fromValue($actualValue);
        $this->assertTrue($enum->equals($coercibleValue));
        $this->assertSame($actualValue, $enum->value()); // constructor should normalize the value
    }

    /**
     * @dataProvider provideNoncoercibleValues
     */
    function testShouldNotCoerceIncompatibleValueTypes(string $enumClass, $actualValue, $noncoercibleValue)
    {
        /** @var EnumObject $enumClass */
        $enum = $enumClass::fromValue($actualValue);
        $this->assertFalse($enum->equals($noncoercibleValue));
        $this->assertNotSame($noncoercibleValue, $enum->value());

        $this->expectException(InvalidValueException::class);

        TestEnumObject::fromValue($noncoercibleValue);
    }

    function provideKeyValue()
    {
        return [
            ['LOREM', 'foo'],
            ['IPSUM', 123],
            ['DOLOR', null],
        ];
    }

    function provideCoercibleValues()
    {
        return [
            // enum class, actual value, coercible value
            [IntNullEnumObject::class, IntNullEnumObject::INT_KEY, '123'],
            [IntNullEnumObject::class, IntNullEnumObject::NULL_KEY, ''],
            [StringEnumObject::class, StringEnumObject::NUMERIC_STRING_KEY, 123],
            [StringEnumObject::class, StringEnumObject::EMPTY_STRING_KEY, null],
        ];
    }

    function provideNoncoercibleValues()
    {
        return [
            // enum class, actual value, noncoercible value
            [IntNullEnumObject::class, IntNullEnumObject::INT_KEY, '0123'],
            [IntNullEnumObject::class, IntNullEnumObject::NULL_KEY, ' '],
            [StringEnumObject::class, StringEnumObject::NUMERIC_STRING_KEY, '0123'],
            [StringEnumObject::class, StringEnumObject::EMPTY_STRING_KEY, ' '],
        ];
    }
}
