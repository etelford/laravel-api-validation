<?php

namespace tests;

use ReflectionClass;
use Orchestra\Testbench\TestCase;

class ApiValidationTest extends TestCase
{
    /** @test */
    public function it_validates_a_set_of_rules()
    {
        $class = new TestableClass();
        $request = new \Illuminate\Http\Request(['bar' => 1, 'foo' => 1]);
        $validation = $class->validate($request, 'entity::bar');

        $this->assertTrue($validation->validator()->passes());
    }

    /** @test */
    public function it_allows_a_custom_rule()
    {
        $class = new TestableClass();
        $request = new \Illuminate\Http\Request(['bar' => 1, 'foo' => 1]);
        $customRule = new TestableRule(['amount' => 100000, 'minimum' => 50000]);
        $validation = $class->validate($request, 'entity::bar')->attachRules($customRule);

        $this->assertTrue($validation->validator()->passes());
    }

    /** @test */
    public function it_allows_to_bypass_exception_throwing()
    {
        $class = new TestableClass();
        $request = new \Illuminate\Http\Request(['bar' => 1]);
        $validation = $class->validate($request, 'entity::bar', $throwOnFailure = false);

        $this->assertInstanceOf('tests\TestableClass', $validation);
        $this->assertFalse($validation->validator()->passes());
    }

    /**
     * @test
     * @expectedException \Etelford\LaravelValidation\ApiValidationException
     */
    public function it_throws_an_exception_when_validation_fails()
    {
        $class = new TestableClass();
        $request = new \Illuminate\Http\Request(['bar' => 1]);
        $class->validate($request, 'entity::bar');
    }

    /** @test */
    public function it_instantiates_a_single_word_found_validator_class_and_returns_its_rules()
    {
        $class = new TestableClass();
        $reflection = new ReflectionClass($class);
        $method = $this->getMethod($reflection, 'parseValidatorClass');

        $appliedRuleClass = $method->invokeArgs($class, ['entity::bar']);
        $ruleClass = new Entity\Bar;

        $this->assertEquals($appliedRuleClass->rules(), $ruleClass->rules());
    }

    /** @test */
    public function it_instantiates_a_multi_word_found_validator_class_and_returns_its_rules()
    {
        $class = new TestableClass();
        $reflection = new ReflectionClass($class);
        $method = $this->getMethod($reflection, 'parseValidatorClass');

        $appliedRuleClass = $method->invokeArgs($class, ['entity::bar.baz']);
        $ruleClass = new Entity\BarBaz;

        $this->assertEquals($appliedRuleClass->rules(), $ruleClass->rules());
    }

    /**
     * @test
     * @expectedException \Etelford\LaravelValidation\ValidationRulesetException
     */
    public function it_throws_an_exception_when_a_validator_class_does_not_exist()
    {
        $class = new TestableClass();
        $reflection = new ReflectionClass($class);
        $method = $this->getMethod($reflection, 'parseValidatorClass');

        $appliedRuleClass = $method->invokeArgs($class, ['entity::fake.class']);
    }

    /** @test */
    public function it_correctly_parses_a_rule_with_a_single_argument()
    {
        $class = new TestableClass();
        $reflection = new ReflectionClass($class);
        $method = $this->getMethod($reflection, 'parseRuleMethod');
        $methodClass = $method->invokeArgs($class, ['update']);

        $this->assertEquals('Update', $methodClass);
    }

    /** @test */
    public function it_correctly_parses_a_rule_with_a_multiple_argument()
    {
        $class = new TestableClass();
        $reflection = new ReflectionClass($class);
        $method = $this->getMethod($reflection, 'parseRuleMethod');
        $methodClass = $method->invokeArgs($class, ['update.foo']);

        $this->assertEquals('UpdateFoo', $methodClass);
    }

    /**
     * Helper to set a protected/private method to be accessible
     *
     * @param  ReflectionClass $class
     * @param  string $name
     * @return ReflectionMethod
     */
    private function getMethod($reflection, $name)
    {
        $method = $reflection->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}

/** Dummy test classes */
class TestableClass
{
    use \Etelford\LaravelValidation\HandlesApiRequests;

    public function __construct()
    {
        $this->setRootNamespace('tests');
    }
}

class TestableRule extends \Etelford\LaravelValidation\CustomRule
{
    public function passes() : bool
    {
        return $this->amount >= $this->minimum;
    }

    public function messageBag() : array
    {
        return ['foo' => 'Bar'];
    }
}
