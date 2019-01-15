<?php declare(strict_types=1);

namespace Kuria\Enum;

use Kuria\DevMeta\Test;
use Kuria\Enum\Exception\DuplicateValueException;
use Kuria\Enum\Exception\InvalidKeyException;
use Kuria\Enum\Exception\InvalidValueException;
use Kuria\Enum\TestSubject\DuplicateCoercedIntEnum;
use Kuria\Enum\TestSubject\DuplicateCoercedNullEnum;
use Kuria\Enum\TestSubject\DuplicateValuesEnum;
use Kuria\Enum\TestSubject\IntNullEnum;
use Kuria\Enum\TestSubject\StringEnum;
use Kuria\Enum\TestSubject\TestEnum;

class EnumTest extends Test
{
    /**
     * @dataProvider provideKeyValue
     */
    function testShouldPerformStaticKeyValueOperations(string $key, $value)
    {
        $this->assertTrue(TestEnum::hasKey($key));
        $this->assertTrue(TestEnum::hasValue($value));
        $this->assertSame($value, TestEnum::getValue($key));
        $this->assertSame($key, TestEnum::getKey($value));
        $this->assertSame($value, TestEnum::findValue($key));
        $this->assertSame($key, TestEnum::findKey($value));
        $this->assertSame([$key => $value], TestEnum::getPair($value));
        $this->assertSame([$key => $value], TestEnum::getPairByKey($key));
    }

    function testShouldPerformStaticOperations()
    {
        $this->assertFalse(TestEnum::hasKey('__NONEXISTENT_KEY__'));
        $this->assertFalse(TestEnum::hasValue('__NONEXISTENT_VALUE__'));
        $this->assertNull(TestEnum::findValue('__NONEXISTENT_KEY__'));
        $this->assertNull(TestEnum::findKey('__NONEXISTENT_VALUE__'));
        $this->assertSame(['LOREM', 'IPSUM', 'DOLOR'], TestEnum::getKeys());
        $this->assertSame(['foo', 123, null], TestEnum::getValues());
        $this->assertSame(['LOREM' => true, 'IPSUM' => true, 'DOLOR' => true], TestEnum::getKeyMap());
        $this->assertSame(['LOREM' => 'foo', 'IPSUM' => 123, 'DOLOR' => null], TestEnum::getMap());
        $this->assertSame(['foo' => 'LOREM', 123 => 'IPSUM', null => 'DOLOR'], TestEnum::getValueMap());
        $this->assertSame(3, TestEnum::count());
    }

    function testShouldThrowExceptionWhenGettingValueForInvalidKey()
    {
        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage(
            'The key "__NONEXISTENT_KEY__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum"'
            . ', known keys: LOREM, IPSUM, DOLOR'
        );

        TestEnum::getValue('__NONEXISTENT_KEY__');
    }

    function testShouldThrowExceptionWhenGettingKeyForInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(
            'The value "__NONEXISTENT_VALUE__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum"'
            . ', known values: "foo", 123, NULL'
        );

        TestEnum::getKey('__NONEXISTENT_VALUE__');
    }

    function testShouldThrowExceptionOnDuplicateValues()
    {
        $this->expectException(DuplicateValueException::class);
        $this->expectExceptionMessageRegExp(
            '{^Duplicate value "value" for key "DUPLICATE_VALUE" in enum class ".+"\. Value "value" is already defined for key "VALUE"\.$}'
        );

        DuplicateValuesEnum::getKey('value');
    }

    function testShouldThrowExceptionOnDuplicateValueBecauseNumericStringArrayKeyIsCoercedToInteger()
    {
        $this->expectException(DuplicateValueException::class);
        $this->expectExceptionMessageRegExp(
            '{^Duplicate value "123" for key "DUPLICATE_VALUE" in enum class ".+"\. Value 123 is already defined for key "VALUE"\.$}'
        );

        DuplicateCoercedIntEnum::getKey(123);
    }

    function testShouldThrowExceptionOnDuplicateValueBecauseNullArrayKeyIsCoercedToEmptyString()
    {
        $this->expectException(DuplicateValueException::class);
        $this->expectExceptionMessageRegExp(
            '{^Duplicate value "" for key "DUPLICATE_VALUE" in enum class ".+"\. Value NULL is already defined for key "VALUE"\.$}'
        );

        DuplicateCoercedNullEnum::getKey(null);
    }

    /**
     * @dataProvider provideCoercibleValues
     */
    function testShouldPerformValueTypeCoercion(string $enumClass, $actualValue, $coercibleValue)
    {
        /** @var Enum $enumClass */
        $this->assertTrue($enumClass::hasValue($actualValue));
        $this->assertTrue($enumClass::hasValue($coercibleValue));
        $this->assertSame($actualValue, $enumClass::getValue($enumClass::getKey($coercibleValue)));
    }

    /**
     * @dataProvider provideNoncoercibleValues
     */
    function testShouldNotCoerceIncompatibleValueTypes(string $enumClass, $actualValue, $noncoercibleValue)
    {
        /** @var Enum $enumClass */
        $this->assertTrue($enumClass::hasValue($actualValue));
        $this->assertFalse($enumClass::hasValue($noncoercibleValue));

        $this->expectException(InvalidValueException::class);

        TestEnum::getKey($noncoercibleValue);
    }

    function testShouldEnsureKeyExists()
    {
        TestEnum::ensureKey('LOREM');

        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage(
            'The key "__NONEXISTENT_KEY__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum"'
                . ', known keys: LOREM, IPSUM, DOLOR'
        );

        TestEnum::ensureKey('__NONEXISTENT_KEY__');
    }

    function testShouldEnsureValueExists()
    {
        TestEnum::ensureValue('foo');

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(
            'The value "__NONEXISTENT_VALUE__" is not defined in enum class "Kuria\Enum\TestSubject\TestEnum"'
                . ', known values: "foo", 123, NULL'
        );

        TestEnum::ensureValue('__NONEXISTENT_VALUE__');
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
            [IntNullEnum::class, IntNullEnum::INT_KEY, '123'],
            [IntNullEnum::class, IntNullEnum::NULL_KEY, ''],
            [StringEnum::class, StringEnum::NUMERIC_STRING_KEY, 123],
            [StringEnum::class, StringEnum::EMPTY_STRING_KEY, null],
        ];
    }

    function provideNoncoercibleValues()
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
