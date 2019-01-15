<?php declare(strict_types=1);

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\Enum;

/**
 * @internal
 */
abstract class DuplicateCoercedIntEnum extends Enum
{
    const VALUE = 123;
    const DUPLICATE_VALUE = '123';
}
