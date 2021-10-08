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

## Loading and saving gateway configurations
Follow these steps to load and save the terminal configurations from the gateway.
* Move the file from `terminal-config/altapay_config.php` to the root directory of the OpenCart installation
* Edit the file and replace `~gatewayusername~`,`~gatewaypass~`, and `~gatewayurl~` with the actual credentials.
* Run the file with the below command

    $ php altapay_config.php

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Documentation

For more details please see [AltaPay docs](https://documentation.altapay.com/)

## Contact
Feel free to contact our support team (support@altapay.com) if you need any assistance.