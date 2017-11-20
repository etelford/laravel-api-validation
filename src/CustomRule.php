<?php

namespace Etelford\LaravelValidation;

abstract class CustomRule
{
    /**
     * Init
     *
     * @param array $attributes
     */
    public function __construct($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * If the validation passes
     *
     * @return bool
     */
    public abstract function passes() : bool;

    /**
     * The message bag
     *
     * @return array
     */
    public abstract function messageBag() : array;
}
