<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SplFileInfo;

final class AcceptAllContentFilter
{
    public static function filter(SplFileInfo $file, string $filename): bool
    {
        return true;
    }
}
