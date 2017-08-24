<?php declare(strict_types=1);

namespace Kuria\Enum;

use Kuria\Enum\Exception\DuplicateValueException;
use Kuria\Enum\Exception\InvalidKeyException;
use Kuria\Enum\Exception\InvalidValueException;
use Kuria\Enum\TestSubject\DuplicateCoercedIntEnum;
use Kuria\Enum\TestSubject\DuplicateCoercedNullEnum;
use Kuria\Enum\TestSubject\DuplicateCoercedValuesEnum;
use Kuria\Enum\TestSubject\DuplicateValuesEnum;
use Kuria\Enum\TestSubject\IntNullEnum;
use Kuria\Enum\TestSubject\StringEnum;
use Kuria\Enum\TestSubject\TestEnum;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    /**
     * @dataProvider provideKeyValue
     */
    function testStaticInterface(string $key, $value)
    {
        /** @var TestEnum $enum */

        // new instance from key
        $enum = TestEnum::fromKey($key);
        $this->assertInstanceOf(TestEnum::class, $enum);
        $this->assertSame($key, $enum->getKey());
        $this->assertSame($value, $enum->getValue());

        // new instance from value
        $enum = TestEnum::fromValue($value);
        $this->assertInstanceOf(TestEnum::class, $enum);
        $this->assertSame($key, $enum->getKey());
        $this->assertSame($value, $enum->getValue());

        // new instance via magic factory method
        $enum = TestEnum::$key();
        $this->assertInstanceOf(TestEnum::class, $enum);
        $this->assertSame($key, $enum->getKey());
        $this->assertSame($value, $enum->getValue());

        $this->assertTrue(TestEnum::hasKey($key));
        $this->assertFalse(TestEnum::hasKey('__NONEXISTENT_KEY__'));
        $this->assertTrue(TestEnum::hasValue($value));
        $this->assertFalse(TestEnum::hasValue('__NONEXISTENT_VALUE__'));
        $this->assertSame($value, TestEnum::findValueByKey($key));
        $this->assertSame($key, TestEnum::findKeyByValue($value));
        $this->assertSame(['LOREM', 'IPSUM', 'DOLOR'], TestEnum::getKeys());
        $this->assertSame(['foo', 123, null], TestEnum::getValues());
        $this->assertSame(['LOREM' => true, 'IPSUM' => true, 'DOLOR' => true], TestEnum::getKeyMap());
        $this->assertSame(['LOREM' => 'foo', 'IPSUM' => 123, 'DOLOR' => null], TestEnum::getKeyToValueMap());
        $this->assertSame(['foo' => 'LOREM', 123 => 'IPSUM', null => 'DOLOR'], TestEnum::getValueToKeyMap());
    }

    function testExceptionWhenCreatingFromInvalidKey()
    {
        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('The key "__NONEXISTENT_KEY__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum", known keys: LOREM, IPSUM, DOLOR');

        TestEnum::fromKey('__NONEXISTENT_KEY__');
    }

    function testExceptionWhenCreatingFromInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The value "__NONEXISTENT_VALUE__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum", known values: "foo", 123, NULL');

        TestEnum::fromValue('__NONEXISTENT_VALUE__');
    }

    function testExceptionWhenCallingUnknownFactoryMethod()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined static method Kuria\Enum\TestSubject\TestEnum::_UNKNOWN_KEY_()');

        /** @noinspection PhpUndefinedMethodInspection */
        TestEnum::_UNKNOWN_KEY_();
    }

    function testExceptionOnDuplicateValues()
    {
        $this->expectException(DuplicateValueException::class);
        $this->expectExceptionMessageRegExp('{^Duplicate value "value" for key "DUPLICATE_VALUE" in enum class ".+"\. Value "value" is already defined for key "VALUE"\.$}');

        DuplicateValuesEnum::fromValue('value');
    }

    function testExceptionOnDuplicateValueBecauseNumericStringArrayKeyIsCoercedToInteger()
    {
        $this->expectException(DuplicateValueException::class);
        $this->expectExceptionMessageRegExp('{^Duplicate value "123" for key "DUPLICATE_VALUE" in enum class ".+"\. Value 123 is already defined for key "VALUE"\.$}');

        DuplicateCoercedIntEnum::fromValue(123);
    }

    function testExceptionOnDuplicateValueBecauseNullArrayKeyIsCoercedToEmptyString()
    {
        $this->expectException(DuplicateValueException::class);
        $this->expectExceptionMessageRegExp('{^Duplicate value "" for key "DUPLICATE_VALUE" in enum class ".+"\. Value NULL is already defined for key "VALUE"\.$}');

        DuplicateCoercedNullEnum::fromValue(null);
    }

    /**
     * @dataProvider provideKeyValue
     */
    function testInstanceCache(string $key, $value)
    {
        $instance = TestEnum::fromKey($key);

        $this->assertSame($instance, TestEnum::fromKey($key));
        $this->assertSame($instance, TestEnum::fromValue($value));
        $this->assertSame($instance, TestEnum::$key());
    }

    /**
     * @dataProvider provideKeyValue
     */
    function testObjectInterface(string $key, $value)
    {
        $enum = TestEnum::fromValue($value);

        $this->assertSame($key, $enum->getKey());
        $this->assertSame($value, $enum->getValue());
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
    function testValueTypeCoercion(string $enumClass, $actualValue, $coercibleValue)
    {
        /** @var Enum $enumClass */
        $enum = $enumClass::fromValue($actualValue);
        $this->assertTrue($enum->equals($coercibleValue));
        $this->assertSame($actualValue, $enum->getValue()); // constructor should normalize the value

        /** @var Enum $enumClass */
        $this->assertTrue($enumClass::hasValue($actualValue));
        $this->assertTrue($enumClass::hasValue($coercibleValue));
        $this->assertSame($actualValue, $enumClass::findValueByKey($enumClass::findKeyByValue($coercibleValue)));
    }

    /**
     * @dataProvider provideNoncoercibleValues
     */
    function testNoncoercibleValueTypes(string $enumClass, $actualValue, $noncoercibleValue)
    {
        /** @var Enum $enumClass */
        $enum = $enumClass::fromValue($actualValue);
        $this->assertFalse($enum->equals($noncoercibleValue));
        $this->assertNotSame($noncoercibleValue, $enum->getValue());

        /** @var Enum $enumClass */
        $this->assertTrue($enumClass::hasValue($actualValue));
        $this->assertFalse($enumClass::hasValue($noncoercibleValue));

        $this->expectException(InvalidValueException::class);

        TestEnum::findKeyByValue($noncoercibleValue);
    }

    function testEnsureKeyExists()
    {
        TestEnum::ensureKeyExists('LOREM');

        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('The key "__NONEXISTENT_KEY__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum", known keys: LOREM, IPSUM, DOLOR');

        TestEnum::ensureKeyExists('__NONEXISTENT_KEY__');
    }

    function testEnsureValueExists()
    {
        TestEnum::ensureValueExists('foo');

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The value "__NONEXISTENT_VALUE__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum", known values: "foo", 123, NULL');

        TestEnum::ensureValueExists('__NONEXISTENT_VALUE__');
    }

    function provideKeyValue(): array
    {
        return [
            ['LOREM', 'foo'],
            ['IPSUM', 123],
            ['DOLOR', null],
        ];
    }

    function provideCoercibleValues(): array
    {
        return [
            // enum class, actual value, coercible value
            [IntNullEnum::class, IntNullEnum::INT_KEY, '123'],
            [IntNullEnum::class, IntNullEnum::NULL_KEY, ''],
            [StringEnum::class, StringEnum::NUMERIC_STRING_KEY, 123],
            [StringEnum::class, StringEnum::EMPTY_STRING_KEY, null],
        ];
    }

    function provideNoncoercibleValues(): array
    {
        return [
            // enum class, actual value, noncoercible value
            [IntNullEnum::class, IntNullEnum::INT_KEY, '0123'],
            [IntNullEnum::class, IntNullEnum::NULL_KEY, ' '],
            [StringEnum::class, StringEnum::NUMERIC_STRING_KEY, '0123'],
            [StringEnum::class, StringEnum::EMPTY_STRING_KEY, ' '],
        ];
    }
}
