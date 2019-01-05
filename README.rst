Enum
####

Emulated enumeration objects in PHP.

The interface is similar to `SplEnum <http://php.net/manual/en/class.splenum.php>`_
but doesn't require any PHP extensions and provides more functionality.

.. image:: https://travis-ci.com/kuria/enum.svg?branch=master
   :target: https://travis-ci.com/kuria/enum

.. contents::


Features
********

- immutability
- ensured unique keys and values
- simple to use (just extend the ``Enum`` class and define some class constants)
- many methods related to keys, values and their enumeration, maps and checking
- detailed exception messages
- designed with performance in mind


Requirements
************

- PHP 7.1+

Usage
*****

Defining an enum class
======================

.. code:: php

   <?php

   use Kuria\Enum\Enum;

   class DayOfTheWeek extends Enum
   {
       const MONDAY = 0;
       const TUESDAY = 1;
       const WEDNESDAY = 2;
       const THURSDAY = 3;
       const FRIDAY = 4;
       const SATURDAY = 5;
       const SUNDAY = 6;
   }

.. NOTE::

   Private and protected constants are ignored.


Custom key-value source
-----------------------

To define key-value pairs using some other source than class constants, override the static
``determineKeyToValueMap()`` method:

.. code:: php

   <?php

   class Example extends Enum
   {
      protected static function determineKeyToValueMap(): array
      {
           return [
               'FOO' => 'bar',
               'BAZ' => 'qux',
               'QUUX' => 'quuz',
           ];
      }
   }


Supported value types
=====================

Only string, integer and null values are supported.

Values must be unique when used as an array key. See `Duplicate values`_.

Values are looked up and compared with the same type-coercion rules as
PHP array keys. See `Value type coercion`_.


Static method overview
======================

Checking keys and values
------------------------

Verify the existence of a key or a value:

.. code:: php

   <?php

   var_dump(
       DayOfTheWeek::hasKey('MONDAY'),
       DayOfTheWeek::hasValue(0)
   );

Output:

::

  bool(true)
  bool(true)


Ensuring existence of keys and values
-------------------------------------

Make sure a key or a value exists, otherwise throw an exception:

.. code:: php

   <?php

   DayOfTheWeek::ensureKeyExists('MONDAY');
   DayOfTheWeek::ensureValueExists(0);

Also see `error handling`_.


Getting keys for values or values for keys
------------------------------------------

Keys and values can be looked up using their counterpart:

.. code:: php

   <?php

   var_dump(
       DayOfTheWeek::getValueByKey('FRIDAY'),
       DayOfTheWeek::getKeyByValue(4)
   );

Output:

::

  int(4)
  string(6) "FRIDAY"

.. NOTE::

   If the key or value doesn't exist, an exception will be thrown. See `Error handling`_.

   To get ``NULL`` instead of an exception, use the ``findValueByKey()`` or ``findKeyByValue()``
   method instead.


Getting key/value lists and maps
---------------------------------

.. code:: php

   <?php

   echo 'DayOfTheWeek::getKeys(): '; print_r(DayOfTheWeek::getKeys());
   echo 'DayOfTheWeek::getValues(): '; print_r(DayOfTheWeek::getValues());
   echo 'DayOfTheWeek::getKeyMap(): '; print_r(DayOfTheWeek::getKeyMap());
   echo 'DayOfTheWeek::getKeyToValueMap(): '; print_r(DayOfTheWeek::getKeyToValueMap());
   echo 'DayOfTheWeek::getValueToKeyMap(): '; print_r(DayOfTheWeek::getValueToKeyMap());

Output:

::

  DayOfTheWeek::keys(): Array
  (
      [0] => MONDAY
      [1] => TUESDAY
      [2] => WEDNESDAY
      [3] => THURSDAY
      [4] => FRIDAY
      [5] => SATURDAY
      [6] => SUNDAY
  )
  DayOfTheWeek::values(): Array
  (
      [0] => 0
      [1] => 1
      [2] => 2
      [3] => 3
      [4] => 4
      [5] => 5
      [6] => 6
  )
  DayOfTheWeek::keyMap(): Array
  (
      [MONDAY] => 1
      [TUESDAY] => 1
      [WEDNESDAY] => 1
      [THURSDAY] => 1
      [FRIDAY] => 1
      [SATURDAY] => 1
      [SUNDAY] => 1
  )
  DayOfTheWeek::keyToValueMap(): Array
  (
      [MONDAY] => 0
      [TUESDAY] => 1
      [WEDNESDAY] => 2
      [THURSDAY] => 3
      [FRIDAY] => 4
      [SATURDAY] => 5
      [SUNDAY] => 6
  )
  DayOfTheWeek::valueToKeyMap(): Array
  (
      [0] => MONDAY
      [1] => TUESDAY
      [2] => WEDNESDAY
      [3] => THURSDAY
      [4] => FRIDAY
      [5] => SATURDAY
      [6] => SUNDAY
  )


Counting members
----------------

.. code:: php

   <?php

   var_dump(DayOfTheWeek::count());

Output:

::

  int(7)


Creating enum instances
=======================

Instances created by ``fromValue()``, ``fromKey()`` and the magic factory methods
are cached internally and reused.

Multiple calls to the factory methods with the same value or key will yield
the same instance.

Enum instances cannot be cloned.


Using a value
-------------

.. code:: php

   <?php

   $day = DayOfTheWeek::fromValue(DayOfTheWeek::MONDAY);

   var_dump($day);

Output:

::

  object(DayOfTheWeek)#3 (2) {
    ["key"]=>
    string(6) "MONDAY"
    ["value"]=>
    int(0)
  }


Using a key
-----------

.. code:: php

   <?php

   $day = DayOfTheWeek::fromKey('FRIDAY');

   var_dump($day);

Output:

::

  object(DayOfTheWeek)#3 (2) {
    ["key"]=>
    string(6) "FRIDAY"
    ["value"]=>
    int(4)
  }


Using the magic static factory method
-------------------------------------

Magic static factory methods may be used instead of passing constants
to the constructor.

For every key there is a static method with the same name. Calling it will
yield an instance with value of the given key.


.. code:: php

   <?php

   /**
    * @method static self MONDAY()
    * @method static self TUESDAY()
    * @method static self WEDNESDAY()
    * @method static self THURSDAY()
    * @method static self FRIDAY()
    * @method static self SATURDAY()
    * @method static self SUNDAY()
    */
   class DayOfTheWeek extends Enum
   {
       const MONDAY = 0;
       const TUESDAY = 1;
       const WEDNESDAY = 2;
       const THURSDAY = 3;
       const FRIDAY = 4;
       const SATURDAY = 5;
       const SUNDAY = 6;
   }

   $day = DayOfTheWeek::SUNDAY();

   var_dump($day);

Output:

::

  object(DayOfTheWeek)#3 (2) {
    ["key"]=>
    string(6) "SUNDAY"
    ["value"]=>
    int(6)
  }


.. WARNING::

   Magic static factory method names are case-sensitive.

.. NOTE::

   The ``@method`` annotations are not required.

   They aid IDE code-completion and inspection.


Enum instance method overview
=============================

Getting the key and value
-------------------------

.. code:: php

   <?php

   $day = DayOfTheWeek::fromValue(1);

   var_dump(
       $day->getKey(),
       $day->getValue()
   );

Output:

::

  string(7) "TUESDAY"
  int(1)


Comparing the key and value
---------------------------

.. code:: php

  <?php

   $day = DayOfTheWeek::fromValue(DayOfTheWeek::TUESDAY);

   var_dump(
       $day->is('TUESDAY'),   // compare key
       $day->is('WEDNESDAY'), // compare key
       $day->equals(1),       // compare value
       $day->equals(2)        // compare value
   );

Output:

::

  bool(true)
  bool(false)
  bool(true)
  bool(false)


String conversion
-----------------

Converting an instance to a string will yield its value (cast to a string):

.. code:: php

   <?php

   $day = DayOfTheWeek::fromValue(DayOfTheWeek::THURSDAY);

   echo $day;

Output:

::

  3


Error handling
==============

Most error states are handled by throwing an exception.

All exceptions thrown by the ``Enum`` class implement ``Kuria\Enum\Exception\ExceptionInterface``.


Invalid value
-------------

.. code:: php

   <?php

   $day = DayOfTheWeek::fromValue(123456);

   // or

   DayOfTheWeek::getKeyByValue(123456);

Result:

``Kuria\Enum\Exception\InvalidValueException`` will be thrown with the following message:

  The value 123456 is not defined in enum class "DayOfTheWeek", known values: 0, 1, 2, 3, 4, 5, 6


Invalid key
-----------

.. code:: php

   <?php

    DayOfTheWeek::fromKey('NONEXISTENT');

    // or

    DayOfTheWeek::getValueByKey('NONEXISTENT');

Result:

``Kuria\Enum\Exception\InvalidKeyException`` will be thrown with the following message:

  The key "NONEXISTENT" is not defined in enum class "DayOfTheWeek", known keys: MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY, SUNDAY


Duplicate values
----------------

.. code:: php

   <?php

   use Kuria\Enum\Enum;

   class EnumWithDuplicateValues extends Enum
   {
       const FOO = 'foo';
       const BAR = 'foo';
   }

   EnumWithDuplicateValues::getKeyByValue('foo');

Result:

``Kuria\Enum\Exception\DuplicateValueException`` will be thrown with the following message:

  Duplicate value "foo" for key "BAR" in enum class "EnumWithDuplicateValues". Value "foo" is already defined for key "FOO".


.. NOTE::

   Values are used as array keys internally. This means that ``null`` and ``""``
   (empty string) and also ``123`` and ``"123"`` (numeric string) are considered
   the same value when verifying uniqueness.

   See `Value type coercion`_.


Value type coercion
===================

Values are looked up and compared with the same type-coercion rules as PHP array
keys. See `PHP manual <http://php.net/manual/en/language.types.array.php>`_ for
a detailed explanation.

With string, integer and null being the supported value types, this means that
the following values are equal:

- ``null`` and ``""`` (an empty string)
- ``123`` and ``"123"`` (a numeric string)

.. NOTE::

   The public API, e.g. ``Enum::getValueByKey()`` and ``$enum->value()``,
   always returns the value as defined by the enum class.

.. NOTE::

   Array key type coercion is NOT the same as `loose comparison <http://php.net/manual/en/types.comparisons.php#types.comparisions-loose>`_ (`==`).


Examples
--------

.. code:: php

   <?php

   use Kuria\Enum\Enum;

   class IntAndNullEnum extends Enum
   {
       const INT_KEY = 123;
       const NULL_KEY = null;
   }

   class StringEnum extends Enum
   {
       const NUMERIC_STRING_KEY = '123';
       const EMPTY_STRING_KEY = '';
   }

   // value checks
   var_dump(
       IntAndNullEnum::hasValue('123'),
       IntAndNullEnum::hasValue('0123'),
       IntAndNullEnum::hasValue(''),
       IntAndNullEnum::hasValue(' '),
       StringEnum::hasValue(123),
       StringEnum::hasValue('0123'),
       StringEnum::hasValue(null),
       StringEnum::hasValue(' ')
   );

   // value retrieval
   var_dump(
       (IntAndNullEnum::fromValue('123'))->getValue(),
       (IntAndNullEnum::fromValue(''))->getValue(),
       (StringEnum::fromValue(123))->getValue(),
       (StringEnum::fromValue(null))->getValue()
   );

Output for value checks:

::

  bool(true)    // '123' matches 123
  bool(false)   // '0123' does not match 123
  bool(true)    // '' matches NULL
  bool(false)   // ' ' does not match NULL
  bool(true)    // 123 matches '123'
  bool(false)   // '0123' does not match '123'
  bool(true)    // NULL matches ''
  bool(false)   // ' ' does not match ''

Output for value retrieval:

::

  int(123)          // enum created with '123' but 123 is returned
  NULL              // enum created with '' but NULL is returned
  string(3) "123"   // enum created with 123 but '123' is returned
  string(0) ""      // enum created with NULL but '' is returned
