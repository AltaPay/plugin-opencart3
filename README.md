# AltaPay for OpenCart

## Supported versions
OpenCart 3.x

## Supported Payment Methods & Functionalities
<table>
<tr><td>

| Functionalities	    | Support       |
| :------------------------ | :-----------: |
| Reservation               | &check;       |
| Capture                   | &check;       |
| Instant Capture           | &cross;       |
| Multi Capture             | &check;       |
| Recurring / Unscheduled   | &cross;       |
| Release                   | &check;       |
| Refund                    | &check;       |
| Multi Refund              | &check;       |
| 3D Secure                 | &check;       |
| Fraud prevention (other)  | &check;       |
| Reconciliation            | &check;       |
| MO/TO                     | &cross;       |

</td><td valign="top">

| Payment Method      | Support       |
| ------------------- | :-----------: |
| Card                | &check;       |
| Invoice             | &check;       |
| ePayments           | &check;       |
| Bank-to-bank        | &check;       |
| Interbank           | &cross;       |
| Cash Wallet         | &check;       |
| Mobile Wallet       | &check;       |

</td></tr> </table>

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
