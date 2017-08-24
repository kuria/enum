<?php declare(strict_types=1);

namespace Kuria\Enum;

interface EnumInterface
{
    function getKey(): string;
    function getValue();
    function is(string $key): bool;
    function equals($value): bool;
}
