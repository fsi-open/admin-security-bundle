# CHANGELOG FOR VERSION 3.*

## Dropped support for FOSUserBundle

The bundle no longer provides integration with FOSUserBundle and so the
`FSi\Bundle\AdminSecurityBundle\Security\FOS\FOSUser` class has been removed.

## Compatibility with Symfony 3.*

As of this version, this bundle is compatibile with Symfony in versions both 2.* and 3.*.

## Dropped support for PHP below 7.1

To be able to fully utilize new functionality introduced in 7.1, we have decided
to only support PHP versions equal or higher to it.
