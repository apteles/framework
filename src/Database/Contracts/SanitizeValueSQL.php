<?php
declare(strict_types=1);
namespace ApTeles\Database\Contracts;

abstract class SanitizeValueSQL
{
    abstract public function transform(): string;
}
