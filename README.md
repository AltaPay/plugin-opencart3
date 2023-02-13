# AltaPay for OpenCart

## Supported versions
OpenCart 3.x

## How to Build
Run the below command to create the zip file in the dist folder.

    ./docker/build-package.sh

## How to run cypress tests

As a prerequisite install OpenCart 3.x with the default theme and follow below steps:

* Navigate to `tests/integration-tests`
* Install cypress by executing 

        npm i
        
* Update `cypress/fixtures/config.json`
* Run cypress

        ./node_modules/.bin/cypress open
   
## Changelog

See [Changelog](CHANGELOG.md) for all the release notes.

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Documentation

For more details please see [docs](https://github.com/AltaPay/plugin-opencart3/wiki)
