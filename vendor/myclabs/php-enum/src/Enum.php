<?php
/**
 * @link    http://github.com/myclabs/php-enum
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace MyCLabs\Enum;

/**
 * Base Enum class
 *
 * Create an enum by implementing this class and adding class constants.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Daniel Costa <danielcosta@gmail.com>
 * @author Mirosław Filip <mirfilip@gmail.com>
 *
 * @psalm-template T
 * @psalm-immutable
 * @psalm-consistent-constructor
 */
abstract class Enum implements \JsonSerializable, \Stringable
{
    /**
     * Enum value
     *
     * @var mixed
     * @psalm-var T
     */
    protected $value;

    /**
     * Enum key, the constant name
     *
     * @var string
     */
    private $key;

    /**
     * Store existing constants in a static cache per object.
     *
     *
     * @var array
     * @psalm-var array<class-string, array<string, mixed>>
     */
    protected static $cache = [];

    /**
     * Cache of instances of the Enum class
     *
     * @var array
     * @psalm-var array<class-string, array<string, static>>
     */
    protected static $instances = [];

    /**
     * Creates a new value of some type
     *
     * @psalm-pure
     * @param mixed $value
     *
     * @psalm-param T $value
     * @throws \UnexpectedValueException if incompatible type is given.
     */
    public function __construct($value)
    {
        if ($value instanceof static) {
           /** @psalm-var T */
            $value = $value->getValue();
        }

        /** @psalm-suppress ImplicitToStringCast assertValidValueReturningKey returns always a string but psalm has currently an issue here */
        $this->key = static::assertValidValueReturningKey($value);

        /** @psalm-var T */
        $this->value = $value;
    }

    /**
     * This method exists only for the compatibility reason when deserializing a previously serialized version
     * that didn't had the key property
     */
    public function __wakeup()
    {
        /** @psalm-suppress DocblockTypeContradiction key can be null when deserializing an enum without the key */
        if ($this->key === null) {
            /**
             * @psalm-suppress InaccessibleProperty key is not readonly as marked by psalm
             * @psalm-suppress PossiblyFalsePropertyAssignmentValue deserializing a case that was removed
             */
            $this->key = static::search($this->value);
        }
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function from($value): self
    {
        $key = static::assertValidValueReturningKey($value);

        return self::__callStatic($key, []);
    }

    /**
     * @psalm-pure
     * @return mixed
     * @psalm-return T
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the enum key (i.e. the constant name).
     *
     * @psalm-pure
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @psalm-pure
     * @psalm-suppress InvalidCast
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * Determines if Enum should be considered equal with the variable passed as a parameter.
     * Returns false if an argument is an object of different class or not an object.
     *
     * This method is final, for more information read https://github.com/myclabs/php-enum/issues/4
     *
     * @psalm-pure
     * @psalm-param mixed $variable
     * @return bool
     */
    final public function equals($variable = null): bool
    {
        return $variable instanceof self
            && $this->getValue() === $variable->getValue()
            && static::class === \get_class($variable);
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     */
    public static function keys()
    {
        return \array_keys(static::toArray());
    }

    /**
     * Returns instances of the Enum class of all Enum constants
     *
     * @psalm-pure
     * @psalm-return array<string, static>
     * @return static[] Constant name in key, Enum instance in value
     */
    public static function values()
    {
        $values = array();

        /** @psalm-var T $value */
        foreach (static::toArray() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    /**
     * Returns all possible values as an array
     *
     * @psalm-pure
     * @psalm-suppress ImpureStaticProperty
     *
     * @psalm-return array<string, mixed>
     * @return array Constant name in key, constant value in value
     */
    public static function toArray()
    {
        $class = static::class;

        if (!isset(static::$cache[$class])) {
            /** @psalm-suppress ImpureMethodCall this reflection API usage has no side-effects here */
            $reflection            = new \ReflectionClass($class);
            /** @psalm-suppress ImpureMethodCall this reflection API usage has no side-effects here */
            static::$cache[$class] = $reflection->getConstants();
        }

        return static::$cache[$class];
    }

    /**
     * Check if is valid enum value
     *
     * @param $value
     * @psalm-param mixed $value
     * @psalm-pure
     * @psalm-assert-if-true T $value
     * @return bool
     */
    public static function isValid($value)
    {
        return \in_array($value, static::toArray(), true);
    }

    /**
     * Asserts valid enum value
     *
     * @psalm-pure
     * @psalm-assert T $value
     * @param mixed $value
     */
    public static function assertValidValue($value): void
    {
        self::assertValidValueReturningKey($value);
    }

    /**
     * Asserts valid enum value
     *
     * @psalm-pure
     * @psalm-assert T $value
     * @param mixed $value
     * @return string
     */
    private static function assertValidValueReturningKey($value): string
    {
        if (false === ($key = static::search($value))) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }

        return $key;
    }

    /**
     * Check if is valid enum key
     *
     * @param $key
     * @psalm-param string $key
     * @psalm-pure
     * @return bool
     */
    public static function isValidKey($key)
    {
        $array = static::toArray();

        return isset($array[$key]) || \array_key_exists($key, $array);
    }

    /**
     * Return key for value
     *
     * @param mixed $value
     *
     * @psalm-param mixed $value
     * @psalm-pure
     * @return string|false
     */
    public static function search($value)
    {
        return \array_search($value, static::toArray(), true);
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     * @throws \BadMethodCallException
     *
     * @psalm-pure
     */
    public static function __callStatic($name, $arguments)
    {
        $class = static::class;
        if (!isset(self::$instances[$class][$name])) {
            $array = static::toArray();
            if (!isset($array[$name]) && !\array_key_exists($name, $array)) {
                $message = "No static method or enum constant '$name' in class " . static::class;
                throw new \BadMethodCallException($message);
            }
            return self::$instances[$class][$name] = new static($array[$name]);
        }
        return clone self::$instances[$class][$name];
    }

    /**
     * Specify data which should be serialized to JSON. This method returns data that can be serialized by json_encode()
     * natively.
     *
     * @return mixed
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @psalm-pure
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
