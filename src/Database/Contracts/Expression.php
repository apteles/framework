<?php
namespace ApTeles\Database\Contracts;

abstract class Expression
{
    public const AND_OPERATION = 'AND';
    public const OR_OPERATION = 'OR';

    abstract protected function dump(): string;
}
