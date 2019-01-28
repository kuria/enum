Changelog
#########

5.1.0
*****

- added ``EnumObject::all()``


5.0.0
*****

- ``Enum`` is no longer instantiable by default
- added ``EnumObject`` (which extends from ``Enum``) that supports instantiation


4.0.0
*****

- simplified many method names
- added ``Enum::pair()``, ``Enum::getPair()``, ``Enum::getPairByKey()``
- removed ``EnumInterface``


3.0.0
*****

- enum instances can no longer be cloned
- ``Enum::determineKeyToValueMap()`` is now protected again so it can be overriden
- ``Enum::findValueByKey()`` and ``Enum::findKeyByValue()`` return ``NULL`` on failure
  instead of throwing exceptions
- added ``Enum::getValueByKey()`` and ``Enum::getKeyByValue()`` which throw exceptions
  on invalid key/value


2.0.1
*****

- fixed PHP version requirement to PHP 7.1+
- relaxed PHPDoc annotations of value-related ``Enum`` methods


2.0.0
*****

- changed most class members from protected to private
- cs fixes, added codestyle checks


1.1.0
*****

- added ``Enum::count()``


1.0.1
*****

- ignore private and protected constants


1.0.0
*****

Initial release
