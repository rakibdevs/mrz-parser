<?php

declare(strict_types=1);

namespace Rakibdevs\MrzParser\Traits;

trait NameFormatter
{
    /**
     * Name components split on the '<<' surname / given-name separator.
     *
     * @var array<int, string>
     */
    protected array $nameString = [];

    /**
     * Given name(s): the segment after '<<', with the single '<' separator
     * turned into spaces and trailing filler removed.
     */
    protected function firstNameFromNameString(): ?string
    {
        $given = $this->nameString[1] ?? '';
        $given = trim(str_replace('<', ' ', rtrim($given, '<')));

        return $given === '' ? null : $given;
    }

    /**
     * Surname: the segment before '<<', trailing filler removed.
     */
    protected function lastNameFromNameString(): ?string
    {
        $last = rtrim($this->nameString[0] ?? '', '<');

        return $last === '' ? null : $last;
    }
}
