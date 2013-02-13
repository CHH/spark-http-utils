# Changelog

## 0.3.0 - 2013-xx-xx

* Move `Middleware` and `Filter` classes to `Spark\HttpUtils\Middleware`
  namespace
* Add `Config` middleware

## 0.2.0 - 2013-02-12

* `KernelBuilder`: Removed `->run()`, pass the app to `resolve` instead
* `KernelBuilder`: Renamed `KernelBuilder` to `Stack`
* `UrlMap`: `UrlMap` now uses `preg_match` directly instead of relying
  on the `RequestMatcher` of HttpFoundation

