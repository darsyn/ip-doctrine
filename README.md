This library provides Doctrine types for [`darsyn/ip`][ip].

# Documentation

Please refer to the [`darsyn/ip`][ip] project for complete documentation.

## Compatibility

This library has extensive test coverage using PHPUnit on PHP versions: `8.1`,
`8.2`, `8.3` and `8.4`.

Static analysis is performed with PHPStan at `max` level on PHP `8.4`, using
core, bleeding edge, and deprecation rules.

Types are provided for `Ipv4`, `IPv6`, and `Multi` IP types from the parent
[`darsyn/ip`][ip] project.
- Versions `5.*.*` are for Doctrine DBAL `^2.3 || ^3.0` compatibility (PHP `5.6`
  and greater).
- Versions `6.*.*` are for Doctrine DBAL `^4` (PHP `8.1` and greater).

## Code of Conduct

This project includes and adheres to the [Contributor Covenant as a Code of
Conduct](CODE_OF_CONDUCT.md).

# License

Please see the [separate licence file](LICENSE.md) included in this repository
for a full copy of the MIT licence, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)

If you make a contribution (submit a pull request), don't forget to add your
name here!

[ip]: https://packagist.org/packages/darsyn/ip
