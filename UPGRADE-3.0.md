# UPGRADE from ^2.0 to ^3.0

## Do not use FSi\Bundle\AdminSecurityBundle\Security\FOS\FOSUser (rarely)

This class has been removed due to the bundle dropping integration with FOSUserBundle,
so if you want to use the two together, you will need to provide your own solution.
