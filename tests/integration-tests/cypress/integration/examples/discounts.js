import Order from '../PageObjects/objects'
if (Cypress.env('runDiscountsTests')) {
    describe('OpenCart3', function () {

        it('TC#09: Create Discounts', function () {
            const ord = new Order()
            ord.admin()
            ord.create_discounts()
        })

        it('TC#10: CC fixed discount', function () {
            cy.fixture('config').then((admin) => {
                if (admin.CC_TERMINAL_NAME != "") {
                    process(admin.CC_TERMINAL_NAME, 'cc', 'fixed')
                } else {
                    cy.log('CC_TERMINAL_NAME skipped')
                }
            })
        })
        it('TC#11: CC percentage discount', function () {
            cy.fixture('config').then((admin) => {
                if (admin.CC_TERMINAL_NAME != "") {
                    process(admin.CC_TERMINAL_NAME, 'cc', 'percentage')
                } else {
                    cy.log('CC_TERMINAL_NAME skipped')
                }
            })
        })
        it('TC#12: Klarna fixed discount', function () {
            cy.fixture('config').then((admin) => {
                if (admin.KLARNA_DKK_TERMINAL_NAME != "") {
                    process(admin.KLARNA_DKK_TERMINAL_NAME, 'klarna', 'fixed')
                } else {
                    cy.log('KLARNA_DKK_TERMINAL_NAME skipped')
                }
            })
        })
        it('TC#13: Klarna percentage discount', function () {
            cy.fixture('config').then((admin) => {
                if (admin.KLARNA_DKK_TERMINAL_NAME != "") {
                    process(admin.KLARNA_DKK_TERMINAL_NAME, 'klarna', 'percentage')
                } else {
                    cy.log('KLARNA_DKK_TERMINAL_NAME skipped')
                }
            })
        })
        it('TC#14: iDEAL fixed discount', function () {
            cy.fixture('config').then((admin) => {
                if (admin.iDEAL_EUR_TERMINAL != "") {
                    process(admin.iDEAL_EUR_TERMINAL, 'ideal', 'fixed')
                } else {
                    cy.log('iDEAL_EUR_TERMINAL skipped')
                }
            })
        })
        it('TC#15: iDEAL percentage discount', function () {
            cy.fixture('config').then((admin) => {
                if (admin.iDEAL_EUR_TERMINAL != "") {
                    process(admin.iDEAL_EUR_TERMINAL, 'ideal', 'percentage')
                } else {
                    cy.log('iDEAL_EUR_TERMINAL skipped')
                }
            })
        })


        function process(terminal_name, func_name, discount = '') {
            const ord = new Order()
            ord.visit()
            if (func_name == 'ideal') {
                cy.get('strong').then(($body) => {
                    if ($body.text().includes('€') === false) {
                        ord.admin()
                        ord.change_currency_to_Euro()
                        ord.visit()
                    }

                })
            } else {
                cy.get('strong').then(($body) => {
                    if ($body.text().includes('Kr') === false) {
                        ord.admin()
                        ord.change_currency_to_DKK()
                        ord.visit()
                    }
                })
            }
            ord.addproduct(discount)
            cy.get('body').then(($a) => {
                if ($a.find("label:contains('" + terminal_name + "')").length) {

                    if (func_name == 'cc') {
                        ord.cc_payment(terminal_name)
                    } else if (func_name == 'klarna') {
                        ord.klarna_payment(terminal_name)
                    } else if (func_name == 'ideal') {
                        ord.ideal_payment(terminal_name)
                    }
                    ord.admin()
                    if (func_name == 'ideal') {
                        ord.ideal_refund()
                    }
                    else {
                        ord.capture()
                        ord.refund()
                    }
                } else {
                    cy.log(terminal_name + ' not found in page')
                }
            })

        }
    })
}