<?php declare(strict_types=1);

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\EnumObject;

/**
 * @internal
 *
 * @method static static LOREM()
 * @method static static IPSUM()
 * @method static static DOLOR()
 * @method static static _UNKNOWN_KEY_() doesn't actually exist
 */
class TestEnumObject extends EnumObject
{
    const LOREM = 'foo';
    const IPSUM = 123;
    const DOLOR = null;
}
