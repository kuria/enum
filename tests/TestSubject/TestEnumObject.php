<?php declare(strict_types=1);

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\EnumObject;

/**
 * @internal
 *
 * @method static TestEnum LOREM()
 * @method static TestEnum IPSUM()
 * @method static TestEnum DOLOR()
 * @method static TestEnum _UNKNOWN_KEY_() doesn't actually exist
 */
class TestEnumObject extends EnumObject
{
    const LOREM = 'foo';
    const IPSUM = 123;
    const DOLOR = null;
}
