# AltaPay for OpenCart

## Supported versions
OpenCart 3.x

## How to Build
Run the below command to create the zip file in the dist folder.

    ./docker/build-package.sh

## How to run cypress tests

### Prerequisites: 

* OpenCart 3.x should be installed with the default theme on publically accessible URL
* Cypress should be installed

### Steps 

* Install dependencies `npm i`
* Update "cypress/fixtures/config.json"
* Execute `./node_modules/.bin/cypress run` in the terminal to run all the tests

## Changelog

See [Changelog](CHANGELOG.md) for all the release notes.

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Documentation

For more details please see [docs](https://github.com/AltaPay/plugin-opencart3/wiki)
