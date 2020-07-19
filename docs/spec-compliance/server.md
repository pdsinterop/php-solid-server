# Server

This document describes what the Solid specification demands from a server in
general. Implementation details have been added describing if and how compliance
has been reached.

## Spec compliance

- [x] _All http URIs MUST redirect to their https counterparts using a response with a 301 status code and a Location header._<br>
  ![][ready] Implemented through the `Pdsinterop\Solid\Controller\HttpToHttpsController`.

- [ ] _It SHOULD additionally implement the server part of HTTP/1.1 Caching to improve performance_<br>
  ![][maybe] As caching can be added "in-front" of the server this is deemed low-priority.

- [ ] _When a client does not provide valid credentials when requesting a resource that requires it, the data pod MUST send a response with a 401 status code (unless 404 is preferred for security reasons)._<br>
  ![][later] This will need to be implemented as part of the OAuth, ACL, and protected documents parts.

- [ ] _A Solid server MUST reject PUT, POST and PATCH requests without the Content-Type header with a status code of 400._<br>
  ![][todo] This should be added in a similar fashion as the HTTPtheHTTPS mechanism. No need to continue routing if this criteria is not met.

- [x] _Paths ending with a slash denote a container resource. the server MAY respond to requests for the latter URI with a 301 redirect to the former._<br>
  ![][ready] Implemented through the `Pdsinterop\Solid\Controller\AddSlashToPathController`

[later]: https://img.shields.io/badge/resolution-later-important.svg
[maybe]: https://img.shields.io/badge/resolution-maybe%20later-yellow.svg
[ready]: https://img.shields.io/badge/resolution-done-success.svg
[todo]:  https://img.shields.io/badge/resolution-todo-critical.svg
