import Order from '../PageObjects/objects'

describe ('OpenCart3', function(){


    it('CC Payment', function(){

        const ord = new Order()
        ord.clrcookies()
        ord.visit()
        ord.addproduct()
        cy.fixture('config').then((admin)=>{
            if (admin.CC_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                if($a.find("label:contains('"+admin.CC_TERMINAL_NAME+"')").length){
                        ord.cc_payment(admin.CC_TERMINAL_NAME)
                        ord.admin()
                        ord.capture()
                        ord.refund()
                }else{
                    cy.log(admin.CC_TERMINAL_NAME + ' not found in page')
                }

                })
            
            }
            else {
                cy.log('CC_TERMINAL_NAME skipped')
            } 
        })
    })

    it('Klarna Payment', function(){

        const ord = new Order()
        ord.clrcookies()
        ord.visit()
        ord.addproduct()
        cy.fixture('config').then((admin)=>{
            if (admin.KLARNA_DKK_TERMINAL_NAME != "") {
                cy.get('body').then(($a) => {
                if($a.find("label:contains('"+admin.KLARNA_DKK_TERMINAL_NAME+"')").length){
                        ord.klarna_payment(admin.KLARNA_DKK_TERMINAL_NAME)
                        ord.admin()
                        ord.capture()
                        ord.refund()
                }else{
                    cy.log(admin.KLARNA_DKK_TERMINAL_NAME + ' not found in page')
                }

                })
            
            }
            else {
                cy.log('KLARNA_DKK_TERMINAL_NAME skipped')
            } 
        })
    })


})