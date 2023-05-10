import Order from '../PageObjects/objects.cy'

describe('OpenCart3', function () {


    it('TC# 01: CC Payment', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('DKK') === false) {
                ord.admin()
                ord.change_currency_to_DKK()
                ord.visit()
            }
        })
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.CC_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.CC_TERMINAL_NAME + "')").length) {
                        ord.cc_payment(admin.CC_TERMINAL_NAME)
                        ord.admin()
                        ord.capture()
                        ord.refund()
                    } else {
                        cy.log(admin.CC_TERMINAL_NAME + ' not found in page')
                    }

                })

            }
            else {
                cy.log('CC_TERMINAL_NAME skipped')
            }
        })
    })

    it('TC# 02: Klarna Payment', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('DKK') === false) {
                ord.admin()
                ord.change_currency_to_DKK()
                ord.visit()
            }
        })
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.KLARNA_DKK_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.KLARNA_DKK_TERMINAL_NAME + "')").length) {
                        ord.klarna_payment(admin.KLARNA_DKK_TERMINAL_NAME)
                        ord.admin()
                        ord.capture()
                        ord.refund()
                    } else {
                        cy.log(admin.KLARNA_DKK_TERMINAL_NAME + ' not found in page')
                        this.skip()
                    }

                })

            }
            else {
                cy.log('KLARNA_DKK_TERMINAL_NAME skipped')
                this.skip()
            }
        })
    })
    it('TC# 03: iDEAL Payment', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('€') === false) {
                ord.admin()
                ord.change_currency_to_Euro()
                ord.visit()
            }
        })
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.iDEAL_EUR_TERMINAL != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.iDEAL_EUR_TERMINAL + "')").length) {
                        ord.ideal_payment(admin.iDEAL_EUR_TERMINAL)
                        ord.admin()
                        ord.ideal_refund()
                    } else {
                        cy.log(admin.iDEAL_EUR_TERMINAL + ' not found in page')
                        this.skip()
                    }

                })

            }
            else {
                cy.log('iDEAL_EUR_TERMINAL skipped')
                this.skip()
            }
        })
    })
    it('TC# 04: CC Release Payment', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('DKK') === false) {
                ord.admin()
                ord.change_currency_to_DKK()
                ord.visit()
            }
        })
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.CC_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.CC_TERMINAL_NAME + "')").length) {
                        ord.cc_payment(admin.CC_TERMINAL_NAME)
                        ord.admin()
                        ord.release_payment()
                    } else {
                        cy.log(admin.CC_TERMINAL_NAME + ' not found in page')
                    }

                })

            }
            else {
                cy.log('CC_TERMINAL_NAME skipped')
            }
        })
    })
    it('TC# 05: Klarna Release Payment', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('DKK') === false) {
                ord.admin()
                ord.change_currency_to_DKK()
                ord.visit()
            }
        })
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.KLARNA_DKK_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.KLARNA_DKK_TERMINAL_NAME + "')").length) {
                        ord.klarna_payment(admin.KLARNA_DKK_TERMINAL_NAME)
                        ord.admin()
                        ord.release_payment()
                    } else {
                        cy.log(admin.KLARNA_DKK_TERMINAL_NAME + ' not found in page')
                        this.skip()
                    }

                })

            }
            else {
                cy.log('KLARNA_DKK_TERMINAL_NAME skipped')
                this.skip()
            }
        })
    })
    it('TC# 06: iDEAL Release Payment', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('€') === false) {
                ord.admin()
                ord.change_currency_to_Euro()
                ord.visit()
            }
        })
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.iDEAL_EUR_TERMINAL != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.iDEAL_EUR_TERMINAL + "')").length) {
                        ord.ideal_payment(admin.iDEAL_EUR_TERMINAL)
                        ord.admin()
                        ord.release_payment()
                    } else {
                        cy.log(admin.iDEAL_EUR_TERMINAL + ' not found in page')
                        this.skip()
                    }

                })

            }
            else {
                cy.log('iDEAL_EUR_TERMINAL skipped')
                this.skip()
            }
        })
    })

    it('TC# 07: CC Partial Capture', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('DKK') === false) {
                ord.admin()
                ord.change_currency_to_DKK()
                ord.visit()
            }
        })
        ord.add_partial_product()
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.CC_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.CC_TERMINAL_NAME + "')").length) {
                        ord.cc_payment(admin.CC_TERMINAL_NAME)
                        ord.admin()
                        ord.partial_capture()
                        ord.refund()
                    } else {
                        cy.log(admin.CC_TERMINAL_NAME + ' not found in page')
                    }

                })

            }
            else {
                cy.log('CC_TERMINAL_NAME skipped')
            }
        })
    })
    it('TC# 08: Klarna Partial Capture', function () {

        const ord = new Order()
        ord.visit()
        cy.get('strong').then(($body) => {
            if ($body.text().includes('DKK') === false) {
                ord.admin()
                ord.change_currency_to_DKK()
                ord.visit()
            }
        })
        ord.add_partial_product()
        ord.addproduct()
        cy.fixture('config').then((admin) => {
            if (admin.KLARNA_DKK_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                    if ($a.find("label:contains('" + admin.KLARNA_DKK_TERMINAL_NAME + "')").length) {
                        ord.klarna_payment(admin.KLARNA_DKK_TERMINAL_NAME)
                        ord.admin()
                        ord.partial_capture()
                        ord.refund()
                    } else {
                        cy.log(admin.KLARNA_DKK_TERMINAL_NAME + ' not found in page')
                    }

                })

            }
            else {
                cy.log('CC_TERMINAL_NAME skipped')
            }
        })
    })

})