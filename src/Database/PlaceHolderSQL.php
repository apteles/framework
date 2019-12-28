<?php
namespace ApTeles\Database;

class PlaceHolderSQL extends StringSanitize
{
    public function transform(): string
    {
        return "{$this->value}";
    }
}
