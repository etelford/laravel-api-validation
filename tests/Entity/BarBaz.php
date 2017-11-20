<?php

namespace tests\Entity;

class BarBaz extends \Etelford\LaravelValidation\BaseValidator
{
    public function rules() : array
    {
        return ['foobar' => 'required_if:baz,1'];
    }
}
