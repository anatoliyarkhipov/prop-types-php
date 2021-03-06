<?php
namespace Prezly\PropTypes\Tests;

use PHPUnit\Framework\TestCase;
use Prezly\PropTypes\Checkers\ChainableTypeChecker;
use Prezly\PropTypes\Exceptions\PropTypeException;
use Prezly\PropTypes\PropTypes;

class PropTypesTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_provide_chainable_type_checkers()
    {
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::any());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::array());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::arrayOf(PropTypes::any()));
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::bool());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::instanceOf(self::class));
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::int());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::float());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::null());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::object());
        $this->assertInstanceOf(ChainableTypeChecker::class, PropTypes::string());
    }

    /**
     * @test
     * @dataProvider valid_data_examples
     * @param array $specs
     * @param array $values
     */
    public function it_should_silently_pass_valid_data(array $specs, array $values)
    {
        PropTypes::checkPropTypes($specs, $values);
        $this->assertTrue(true, "PropTypes::checkPropTypes didn't throw an exception");
    }

    /**
     * @test
     * @dataProvider invalid_data_examples
     * @param array $specs
     * @param array $values
     */
    public function it_should_throw_on_invalid_data(array $specs, array $values)
    {
        $this->expectException(PropTypeException::class);
        PropTypes::checkPropTypes($specs, $values);
    }

    /**
     * @test
     */
    public function it_should_throw_on_unexpected_extra_properties()
    {
        try {
            PropTypes::checkPropTypes([
                'name' => PropTypes::any(),
            ], [
                'name' => 'Elvis Presley',
                'job'  => 'The King',
            ]);
        } catch (PropTypeException $error) {
            $this->assertInstanceOf(PropTypeException::class, $error);
            $this->assertEquals('job', $error->getPropName());
            $this->assertEquals('unexpected_extra_property', $error->getErrorCode());
            $this->assertEquals('Unexpected extra property `job` supplied.', $error->getMessage());
        }
    }

    public function valid_data_examples(): iterable
    {
        yield 'any: string' => [
            ['name' => PropTypes::any()->isRequired()],
            ['name' => 'Elvis Presley'],
        ];
        yield 'any: null' => [
            ['name' => PropTypes::any()->isNullable()],
            ['name' => null],
        ];
        yield 'any: missing' => [
            ['name' => PropTypes::any()],
            [],
        ];

        yield 'arrayOf: string' => [
            ['friends' => PropTypes::arrayOf(PropTypes::string())],
            ['friends' => ['John Galt']]
        ];

        yield 'shape' => [
            ['name' => PropTypes::shape([
                'first' => PropTypes::string()->isRequired(),
                'last' => PropTypes::string()->isRequired(),
            ])],
            ['name' => [
                'first' => 'Elvis',
                'last' => 'Presley',
            ]]
        ];
    }

    public function invalid_data_examples(): iterable
    {
        yield 'any: null when not-nullable' => [
            ['name' => PropTypes::any()],
            ['name' => null],
        ];

        yield 'any: missing when required' => [
            ['name' => PropTypes::any()->isRequired()],
            [],
        ];

        yield 'extra property "occupation"' => [
            ['name' => PropTypes::any()],
            ['name' => 'Elvis Presley', 'occupation' => 'The King'],
        ];

        yield 'arrayOf: invalid type' => [
            ['friends' => PropTypes::arrayOf(PropTypes::string())],
            ['friends' => [42]]
        ];

        yield 'shape: not a shape' => [
            ['name' => PropTypes::shape([
                'first' => PropTypes::string()->isRequired(),
                'last' => PropTypes::string()->isRequired(),
            ])],
            ['name' => 'Elvis Presley'],
        ];
        yield 'shape: required prop missing' => [
            ['name' => PropTypes::shape([
                'first' => PropTypes::string()->isRequired(),
                'last' => PropTypes::string()->isRequired(),
            ])],
            ['name' => [
                'first' => 'Elvis',
            ]],
        ];
        yield 'shape: incorrect prop type' => [
            ['name' => PropTypes::shape([
                'first' => PropTypes::string()->isRequired(),
                'last' => PropTypes::string()->isRequired(),
            ])],
            ['name' => [
                'first' => 'Elvis',
                'last' => 42,
            ]],
        ];
    }
}
