# Changelog

## 0.2.0 - 2013-xx-xx

* `KernelBuilder`: Removed `->run()`, pass the app to `resolve` instead
* `KernelBuilder`: Renamed `KernelBuilder` to `Stack`
* `UrlMap`: `UrlMap` now uses `preg_match` directly instead of relying
  on the `RequestMatcher` of HttpFoundation

