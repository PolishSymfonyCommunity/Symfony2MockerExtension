Symfony2 Mocker Extension
=========================

[![Build Status](https://travis-ci.org/PolishSymfonyCommunity/Symfony2MockerExtension.svg)](https://travis-ci.org/PolishSymfonyCommunity/Symfony2MockerExtension)

Behat extension for mocking services defined in the Symfony2 dependency
injection container.

**This extension was always a hack. For a better approach try https://github.com/docteurklein/TestDoubleBundle.**

**Use it sparingly. Mocking a service container is not a good practice.
Most of the time if there's a need for this,
the problem can be better solved by improving the design instead.**

Internally it uses [Mockery](https://github.com/padraic/mockery) and
[SymfonyMockerContainer](https://github.com/PolishSymfonyCommunity/SymfonyMockerContainer).

## Documentation

[Official documentation](doc/index.rst)
