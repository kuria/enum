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
- simple to use (just extend a class and define some class constants)
- many methods related to keys
- values and their enumeration, maps and checking
- enum instances
- detailed exception messages


Requirements
************

- PHP 7.1+


Usage
*****

.. _Enum:

``Enum``
========

The static ``Enum`` class provides access to the defined key-value pairs.


Defining an enum class
----------------------

.. code:: php

   <?php

   use Kuria\Enum\Enum;

   abstract class DayOfTheWeek extends Enum
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
^^^^^^^^^^^^^^^^^^^^^^^

To define key-value pairs using some other source than class constants, override the static
``determineKeyToValueMap()`` method:

.. code:: php

   <?php

   use Kuria\Enum\Enum;

   abstract class Example extends Enum
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
---------------------

Only string, integer and null values are supported.

Values must be unique when used as an array key. See `Value type coercion`_.

Values are looked up and compared with the same type-coercion rules as
PHP array keys. See `Value type coercion`_.


Method overview
---------------

Checking keys and values
^^^^^^^^^^^^^^^^^^^^^^^^

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
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Make sure a key or a value exists, otherwise throw an exception:

.. code:: php

   <?php

   DayOfTheWeek::ensureKey('MONDAY');
   DayOfTheWeek::ensureValue(0);

See `Error handling`_.


Getting keys for values or values for keys
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Keys and values can be looked up using their counterpart:

.. code:: php

   <?php

   var_dump(
       DayOfTheWeek::getValue('FRIDAY'),
       DayOfTheWeek::getKey(4)
   );

Output:

::

  int(4)
  string(6) "FRIDAY"

.. NOTE::

   If the key or value doesn't exist, an exception will be thrown. See `Error handling`_.

   To get ``NULL`` instead of an exception, use the ``findValue()`` or ``findKey()``
   method instead.


Getting key/value lists and maps
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: php

   <?php

   echo 'DayOfTheWeek::getKeys(): '; print_r(DayOfTheWeek::getKeys());
   echo 'DayOfTheWeek::getValues(): '; print_r(DayOfTheWeek::getValues());
   echo 'DayOfTheWeek::getMap(): '; print_r(DayOfTheWeek::getMap());
   echo 'DayOfTheWeek::getKeyMap(): '; print_r(DayOfTheWeek::getKeyMap());
   echo 'DayOfTheWeek::getValueMap(): '; print_r(DayOfTheWeek::getValueMap());

Output:

::

  DayOfTheWeek::getKeys(): Array
  (
      [0] => MONDAY
      [1] => TUESDAY
      [2] => WEDNESDAY
      [3] => THURSDAY
      [4] => FRIDAY
      [5] => SATURDAY
      [6] => SUNDAY
  )
  DayOfTheWeek::getValues(): Array
  (
      [0] => 0
      [1] => 1
      [2] => 2
      [3] => 3
      [4] => 4
      [5] => 5
      [6] => 6
  )
  DayOfTheWeek::getMap(): Array
  (
      [MONDAY] => 0
      [TUESDAY] => 1
      [WEDNESDAY] => 2
      [THURSDAY] => 3
      [FRIDAY] => 4
      [SATURDAY] => 5
      [SUNDAY] => 6
  )
  DayOfTheWeek::getKeyMap(): Array
  (
      [MONDAY] => 1
      [TUESDAY] => 1
      [WEDNESDAY] => 1
      [THURSDAY] => 1
      [FRIDAY] => 1
      [SATURDAY] => 1
      [SUNDAY] => 1
  )
  DayOfTheWeek::getValueMap(): Array
  (
      [0] => MONDAY
      [1] => TUESDAY
      [2] => WEDNESDAY
      [3] => THURSDAY
      [4] => FRIDAY
      [5] => SATURDAY
      [6] => SUNDAY
  )


Getting pairs
^^^^^^^^^^^^^

A pair is an array with a single key and the corresponding value. They can be retrieved using either
the key or the value:

.. code:: php

   <?php

   var_dump(DayOfTheWeek::getPair(DayOfTheWeek::MONDAY));
   var_dump(DayOfTheWeek::getPairByKey('FRIDAY'));

Output:

::

  array(1) {
    ["MONDAY"]=>
    int(0)
  }
  array(1) {
    ["FRIDAY"]=>
    int(4)
  }


Counting members
^^^^^^^^^^^^^^^^

.. code:: php

   <?php

   var_dump(DayOfTheWeek::count());

Output:

::

  int(7)


.. _EnumObject:

``EnumObject``
==============

The ``EnumObject`` class extends from Enum_ and adds factory methods to create instances.


Defining an enum object class
-----------------------------

.. code:: php

   <?php

   use Kuria\Enum\EnumObject;

   /**
    * @method static static RED()
    * @method static static GREEN()
    * @method static static BLUE()
    */
   class Color extends EnumObject
   {
       const RED = 'r';
       const GREEN = 'g';
       const BLUE = 'b';
   }

.. NOTE::

   The ``@method`` annotations are not required, but they will aid in code-completion and inspection.

   See `Magic static factory methods <Using the magic static factory method_>`_.


Creating instances
------------------

Instances can be created by one of the factory methods. Those instances are cached internally
and reused, so that multiple calls to the factory methods with the same key or value will yield
the same instance.

Enum instances cannot be cloned.


Using a value
^^^^^^^^^^^^^

.. code:: php

   <?php

   $color = Color::fromValue(Color::RED);

   var_dump($color);

Output:

::

  object(Foo\Color)#5 (2) {
    ["key"]=>
    string(3) "RED"
    ["value"]=>
    string(1) "r"
  }


Using a key
^^^^^^^^^^^

.. code:: php

   <?php

   $color = Color::fromKey('GREEN');

   var_dump($color);

Output:

::

  object(Foo\Color)#3 (2) {
    ["key"]=>
    string(5) "GREEN"
    ["value"]=>
    string(1) "g"
  }


Using the magic static factory method
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

For every key there is a static method with the same name, which returns an instance
for that key-value pair.

.. code:: php

   <?php

   $color = Color::BLUE();

   var_dump($color);


Output:

::

  object(Foo\Color)#5 (2) {
    ["key"]=>
    string(4) "BLUE"
    ["value"]=>
    string(1) "b"
  }

.. WARNING::

   Magic static factory method names are case-sensitive.


Method overview
---------------

Getting the key and value
^^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: php

   <?php

   $color = Color::RED();

   var_dump(
       $color->key(),
       $color->value()
   );

Output:

::

  string(3) "RED"
  string(1) "r"


Getting the pair
^^^^^^^^^^^^^^^^

.. code:: php

   <?php

   $color = Color::GREEN();

   var_dump($color->pair());

Output:

::

  array(1) {
    ["GREEN"]=>
    string(1) "g"
  }


Comparing the key and value
^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code:: php

  <?php

   $color = Color::RED();

   var_dump(
       $color->is('RED'),   // compare key
       $color->is('GREEN'), // compare key
       $color->equals('r'), // compare value
       $color->equals('g')  // compare value
   );

Output:

::

  bool(true)
  bool(false)
  bool(true)
  bool(false)


String conversion
^^^^^^^^^^^^^^^^^

Converting an instance to a string will yield its value (cast to a string):

.. code:: php

   <?php

   $color = Color::BLUE();

   echo $color;

Output:

::

  b


Error handling
==============

Most error states are handled by throwing an exception.

All exceptions thrown by the enum classes implement ``Kuria\Enum\Exception\ExceptionInterface``.

- ``Kuria\Enum\Exception\InvalidKeyException`` is thrown when a key doesn't exist
- ``Kuria\Enum\Exception\InvalidValueException`` is thrown when a value doesn't exist
- ``Kuria\Enum\Exception\InvalidMethodException`` is thrown when a magic factory method doesn't exist
- ``Kuria\Enum\Exception\DuplicateValueException`` is thrown when an enum class defines duplicate values

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

   The public API, e.g. ``Enum::getValue()`` and ``EnumObject::value()``,
   always returns the value as defined by the enum class.

.. NOTE::

   Array key type coercion is NOT the same as `loose comparison <http://php.net/manual/en/types.comparisons.php#types.comparisions-loose>`_ (`==`).


Examples
--------

.. code:: php

   <?php

   use Kuria\Enum\EnumObject;

   class IntAndNullEnum extends EnumObject
   {
       const INT_KEY = 123;
       const NULL_KEY = null;
   }

   class StringEnum extends EnumObject
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
       (IntAndNullEnum::fromValue('123'))->value(),
       (IntAndNullEnum::fromValue(''))->value(),
       (StringEnum::fromValue(123))->value(),
       (StringEnum::fromValue(null))->value()
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
