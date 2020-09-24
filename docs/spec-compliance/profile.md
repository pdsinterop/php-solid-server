# Identity

This document describes what the Solid specification demands from a Solid
Profile. Implementation details have been added describing if and how compliance
has been reached.

## Spec compliance

Defined by the [**Solid WebID Profiles Spec**][1]:

- [ ] _MUST have a `foaf:primaryTopic` predicate_<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _MUST have a primary topic be a valid `foaf:Agent` type, such as `foaf:Person`_<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _MUST identify the document as a `foaf:PersonalProfileDocument` instance_<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _MUST include a `foaf:name` MAY be a pseudonym_<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _MUST support separate public and private data in a user's profile._<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _SHOULD include a public avatar `foaf:img`_<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _SHOULD include `cert:key` public key certificate (for use with WebID+TLS/FOAF+SSL)_<br>
  ![][later] This will need to be implemented as part of the Authentication part

- [ ] _SHOULD link to Solid Storage container(s) using `pim:storage` (for applications data read/write)._<br>
  ![][later] This will need to be implemented as part of the Storage part.

- [ ] _SHOULD link to Type Registry Index resources_<br>
  ![][later] This will need to be implemented as part of the Storage part for auto-discovery.

- [ ] _If Type Registry Index links exist there MUST be only one link each to a private and a public type registry index file_<br>
  ![][later] Part of previous item.

- [ ] _MAY be split into multiple RDF resources linked together (via `owl:sameAs`, `rdfs:seeAlso`, or `space:preferencesFile`)_<br>
  ![][todo] This will be implemented as part of Profile.

- [ ] _MAY contain a link to the Solid Inbox container using `ldp:inbox`_<br>
  ![][later] This will need to be implemented as part of the Storage, as it should be created by default.

- [ ] _If an inbox link exists it MUST be only one Inbox for the profile_<br>
  ![][later] Part of previous item.

- [ ] _MAY provide a `foaf:nick` nickname_<br>
  ![][todo] This will be implemented as part of Profile.

Inherited from the [**WebID spec**][2]:

- [x] _MUST be available as `text/turtle`<br>_<br>
  ![][ready] Implemented through `Pdsinterop\Solid\Controller\Profile\CardController`.

- [ ] _MUST have a HTTP URI that dereferences to a document the user controls_<br>
  ![][later] This will need to be implemented as part of Storage.

- [x] _MAY be available as othe RDF formats if requested through content negotiation_<br>
  ![][ready] Implemented through `Pdsinterop\Solid\Controller\Profile\CardController`.


[1]: https://github.com/solid/solid-spec/blob/master/solid-webid-profiles.md
[2]: https://www.w3.org/2005/Incubator/webid/spec/identity/

[later]: https://img.shields.io/badge/resolution-later-important.svg
[maybe]: https://img.shields.io/badge/resolution-maybe%20later-yellow.svg
[ready]: https://img.shields.io/badge/resolution-done-success.svg
[todo]:  https://img.shields.io/badge/resolution-todo-critical.svg
