<?php

namespace Etelford\LaravelValidation;

use Illuminate\Http\Request;
use Validator as LaravelValidator;

trait HandlesApiRequests
{
    /**
     * The base namespace for Validation Classes
     *
     * @var string
     */
    private $rootNamespace = '\App\\Validation\\';

    /**
     * The Validator
     *
     * @var LaravelValidator
     */
    private $validator;

    /**
     * If the validator should throw an exception if it fails
     *
     * @var boolean
     */
    private $throwOnFailure;

    /**
     * Set the root namespace for validators
     *
     * @param void $namespace
     */
    public function setRootNamespace($namespace)
    {
        $this->rootNamespace = '\\' . str_replace('\\', '\\\\', $namespace) . '\\';
    }

    /**
     * Validate the request
     *
     * @param  Request $request
     * @param  string  $path
     * @return $this
     */
    public function validate($request, $path, $throwOnFailure = true)
    {
        $this->throwOnFailure = $throwOnFailure;

        $rules = is_array($path) ? $path : [$path];

        foreach ($rules as $rulePath) {
            list($rootPath, $subPaths) = $this->splitRulePath($rulePath);

            foreach ($subPaths as $subPath) {
                $currentPath = $this->makePath($rootPath, $subPath);
                $validatorClass = $this->parseValidatorClass($currentPath);
                $this->validator = LaravelValidator::make(
                    $this->resolveData($request),
                    $validatorClass->rules(),
                    $validatorClass->messages()
                );

                if ($this->validator->fails() && $this->throwOnFailure) {
                    throw new ApiValidationException($this->validator);
                }
            }
        }

        return $this;
    }

    /**
     * Attach rules to the validator
     *
     * @param  array $rules
     * @return $this
     */
    public function attachRules(...$rules)
    {
        foreach ($rules as $rule) {
            if (! $rule->passes()) {
                $this->validator->getMessageBag()->add(
                    $rule->messageBag()['key'], $rule->messageBag()['message']
                );

                if ($this->throwOnFailure) {
                    throw new ApiValidationException($this->validator);
                }
            }
        }

        return $this;
    }

    /**
     * Getter for the validator
     *
     * @return Validator
     */
    public function validator()
    {
        return $this->validator;
    }

    /**
     * Split out the root path and its subpaths
     *
     * @param  string $rulePath
     * @return array
     */
    protected function splitRulePath(string $rulePath) : array
    {
        $rulePath = explode('::', $rulePath);

        return [$rulePath[0], explode(',', $rulePath[1])];
    }

    /**
     * Construct a final rule path
     *
     * @param  args $parts
     * @return string
     */
    protected function makePath(...$parts) : string
    {
        return implode('::', $parts);
    }

    /**
     * Initializes the class to get the rules for
     *
     * @param  string $path
     * @return Object
     */
    protected function parseValidatorClass($path)
    {
        $parts = explode('::', $path);
        $entity = ucwords($parts[0]);
        $method = $this->parseRuleMethod($parts[1]);

        $class = "{$this->rootNamespace}$entity\\$method";

        if (class_exists($class)) {
            return new $class();
        }

        throw new ValidationRulesetException(
            "The ruleset for {$entity}\\{$method} doesn't exist."
        );
    }

    /**
     * Parse the method from the rule input
     *
     * 1. Create an array by splitting on '-'
     * 2. Now map over the array to make each part uppercase
     * 3. Finally put the pieces back together
     *
     * @param  string $input
     * @return string
     */
    protected function parseRuleMethod(string $input) : string
    {
        // 1.
        $output = explode('.', $input);

        // 2.
        $output = array_map(function($part) {
            return ucfirst($part);
        }, $output);

        // 3.
        return implode('', $output);
    }

    /**
     * Decide how to deal with the data.
     *
     * If it's a Laravel Request object, then just get all(), otherwise,
     * just us it as is.
     *
     * @param  mixed (Request | array) $request
     * @return array
     */
    protected function resolveData($request) : array
    {
        return ($request instanceof Request) ? $request->all() : $request;
    }
}
