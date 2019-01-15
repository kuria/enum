<?php declare(strict_types=1);

namespace Kuria\Enum\TestSubject;

use Kuria\Enum\Enum;

/**
 * @internal
 */
abstract class TestEnum extends Enum
{
    const LOREM = 'foo';
    const IPSUM = 123;
    const DOLOR = null;

    protected const SIT = 'protected const should be ignored';
    private const AMET = 'private const should be ignored';
}
