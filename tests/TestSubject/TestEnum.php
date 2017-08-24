<?php declare(strict_types=1);

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\Enum;

/**
 * @internal
 *
 * @method static TestEnum LOREM()
 * @method static TestEnum IPSUM()
 * @method static TestEnum DOLOR()
 */
class TestEnum extends Enum
{
    const LOREM = 'foo';
    const IPSUM = 123;
    const DOLOR = null;
}
