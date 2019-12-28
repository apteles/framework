<?php
namespace ApTeles\Database;

use ApTeles\Database\Contracts\SanitizeValueSQL;

class BooleanSanitize extends SanitizeValueSQL
{
    private $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function transform(): string
    {
        if ($this->value) {
            return 'TRUE';
        }

        return 'FALSE';
    }
}
