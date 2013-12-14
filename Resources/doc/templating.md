# Templating

One of the goals of this bundle is to overwrite ``FSiAdminBundle::base.html.twig`` layout file.
This can be done only through ``fsi_admin_bundle`` configuration prepend.
So basically when FSiAdminSecureBundle is registered it prepend FSiAdminBundle configuration with
following options:

```
fsi_admin:
    templates:
        base: FSiAdminSecurityBundle:Admin:base.html.twig
```
If you need to modify base admin template remember to extend from
``FSiAdminSecurityBundle:Admin:base.html.twig`` in your twig file instead of
``FSiAdminBundle::base.html.twig``.