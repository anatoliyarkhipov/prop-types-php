<?php
namespace Prezly\PropTypes;

use InvalidArgumentException;
use Prezly\PropTypes\Checkers\AnyTypeChecker;
use Prezly\PropTypes\Checkers\TypeChecker;

final class PropTypes
{
    /**
     * @param \Prezly\PropTypes\Checkers\TypeChecker[] $specs
     * @param array $values
     * @throws \Prezly\PropTypes\Exceptions\PropTypeException When a prop-type validation fails.
     * @throws \InvalidArgumentException When invalid specs configuration was given.
     */
    public static function checkPropTypes(array $specs, array $values): void
    {
        foreach ($specs as $key => $checker) {
            if (! $checker instanceof TypeChecker) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid argument supplied to %s(). Expected an associative array of `%s` instances, but received `%s` at key `%s`.',
                    __FUNCTION__,
                    TypeChecker::class,
                    is_object($checker) ? get_class($checker) : gettype($checker),
                    $key
                ));
            }
        }

        foreach ($specs as $prop_name => $checker) {
            $error = $checker->validate($values, $prop_name, $prop_name);
            if ($error !== null) {
                throw $error;
            }
        }
    }

    public static function any(): TypeChecker
    {
        return new AnyTypeChecker();
    }
}
