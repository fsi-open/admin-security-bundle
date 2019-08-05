# CHANGELOG FOR VERSION 3.0

## Dropped support for FOSUserBundle

The bundle no longer provides integration with FOSUserBundle and so the
`FSi\Bundle\AdminSecurityBundle\Security\FOS\FOSUser` class has been removed.

## Compatibility with Symfony 3.* and 4.*

As of this version, this bundle is compatibile with Symfony in versions both 2.* and 3.*.

## Dropped support for PHP below 7.1

To be able to fully utilize new functionality introduced in 7.1, we have decided
to only support PHP versions equal or higher to it.

## Users admin element requires form class as constructor parameter

To allow proper overriding of the form used by the element (for Symfony 3+), it
is now required to pass it's class name to the constructor. Keep that in mind if
you plan on extending the element's class.
