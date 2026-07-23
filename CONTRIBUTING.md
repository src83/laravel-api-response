# Contributing

## Versioning

This package follows [Semantic Versioning](https://semver.org/).

### PATCH `1.0.X`

Backwards-compatible fixes — no public API changes:

- Behaviour fixes without signature changes
- Widening a dependency version range (e.g. allowing a new minor release)

### MINOR `1.X.0`

New functionality, backwards compatibility preserved:

- New artisan commands, middleware, or config options (with a default value)
- Adding support for a new Laravel or PHP version

### MAJOR `X.0.0`

Breaking changes:

- Any change to the JSON response contract (keys, structure)
- Renaming or removing config keys
- Renaming or removing artisan commands or public classes
- Dropping support for a Laravel or PHP version

### Tagging

- All development happens on the `main` branch
- A tag is applied to `main` after the final commit:
  ```bash
  git tag v1.0.0
  git push origin v1.0.0
  ```
- Update `CHANGELOG.md` before tagging
