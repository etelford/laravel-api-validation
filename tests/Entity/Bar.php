<?php

namespace tests\Entity;

class Bar extends \Etelford\LaravelValidation\BaseValidator
{
    public function rules() : array
    {
        return ['foo' => 'required_if:bar,1'];
    }
}
