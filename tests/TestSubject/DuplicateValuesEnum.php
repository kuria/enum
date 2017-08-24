<?php

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\Enum;

/**
 * @internal
 */
class DuplicateValuesEnum extends Enum
{
    const VALUE = 'value';
    const DUPLICATE_VALUE = 'value';
}
