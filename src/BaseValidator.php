<?php

namespace Etelford\LaravelValidation;

abstract class BaseValidator implements ValidationRulesInterface
{
    /**
     * {@inheritDoc}
     */
    abstract public function rules() : array;

    /**
     * {@inheritDoc}
     */
    public function messages() : array
    {
        return [];
    }
}
