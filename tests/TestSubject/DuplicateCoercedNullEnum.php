<?php declare(strict_types=1);

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\Enum;

/**
 * @internal
 */
abstract class DuplicateCoercedNullEnum extends Enum
{
    const VALUE = null;
    const DUPLICATE_VALUE = '';
}
