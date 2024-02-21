CHANGELOG
=========

6.4
---

 * Deprecate per-property lazy-initializers

6.2
---

 * Add support for lazy ghost objects and virtual proxies
 * Add `Hydrator::hydrate()`
 * Preserve PHP references also when using `Hydrator::hydrate()` or `Instantiator::instantiate()`
 * Add support for hydrating from native (array) casts

5.1.0
-----

 * added argument `array &$foundClasses` to `VarExporter::export()` to ease with preloading exported values

4.2.0
-----

 * added the component
