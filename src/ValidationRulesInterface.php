<?php

namespace Etelford\LaravelValidation;

interface ValidationRulesInterface
{
    /**
     * Validation rules
     *
     * @return array
     */
    public function rules() : array;

    /**
     * Custom error validation messages
     *
     * @return array
     */
    public function messages() : array;
}
