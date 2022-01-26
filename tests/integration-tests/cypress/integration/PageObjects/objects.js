require('cypress-xpath')

class Order
{
    visit()
    {   
        cy.clearCookies()
        cy.fixture('config').then((url)=>{
        cy.visit(url.shopURL)
         
        })   
    }
    
    addproduct(discount = ''){
        cy.get(':nth-child(1) > .product-thumb > .image > a > .img-responsive').click()
        cy.get('#button-cart').click()
        cy.get('.alert').contains('shopping cart').click()
        if(discount != ""){
            cy.contains('Use Coupon Code').click()
            cy.get('#input-coupon').type(discount)
            cy.get('#button-coupon').click().wait(2000)
        }
        cy.get('.pull-right > .btn').click()
        cy.get(':nth-child(4) > label > input').click()
        cy.get('#button-account').click()
        cy.get('#input-payment-firstname').type('Testperson-dk')
        cy.get('#input-payment-lastname').type('Approved')
        cy.get('#input-payment-email').type('demo@example.com')
        cy.get('#input-payment-address-1').type('Sæffleberggate 56,1 mf')
        cy.get('#input-payment-city').type('Varde')
        cy.get('#input-payment-postcode').type('6800')
        cy.get('#input-payment-telephone').type('20 12 34 56')
        cy.get('#input-payment-country').select('Denmark')
        cy.get('#input-payment-zone').select('Fyn')
        cy.get('#input-payment-zone').select('Fyn')
        cy.get('#button-guest').click().wait(3000)
        cy.get('body').then(($body) => {
            if ($body.text().includes('Flat Rate') ) {
                cy.get('#button-shipping-method').click().wait(3000)
            }
        })
        
    }

    cc_payment(CC_TERMINAL_NAME){
        
        cy.contains(CC_TERMINAL_NAME).click({force: true}).wait(2000)

        cy.get('.pull-right > [type="checkbox"]').click()
        cy.get('#button-payment-method').click()
        cy.get('#button-confirm').click()
        cy.get('[id=creditCardNumberInput]').type('4111111111111111')
        cy.get('#emonth').type('01')
        cy.get('#eyear').type('2023')
        cy.get('#cvcInput').type('123')
        cy.get('#cardholderNameInput').type('testname')
        cy.get('#pensioCreditCardPaymentSubmitButton').click().wait(2000)
        cy.get('#content > h1').should('have.text', 'Your order has been placed!')}
    
    klarna_payment(KLARNA_DKK_TERMINAL_NAME){

        cy.contains(KLARNA_DKK_TERMINAL_NAME).click({force: true})
        cy.get('.pull-right > [type="checkbox"]').click()
        cy.get('#button-payment-method').click()
        cy.get('#button-confirm').click()
        //Klarna Form
        cy.get('#submitbutton').click().wait(10000)

        cy.get('[id=klarna-pay-later-fullscreen]').wait(2000).then(function($iFrame){
            const mobileNum = $iFrame.contents().find('[id=invoice_kp-purchase-approval-form-phone-number]')
            cy.wrap(mobileNum).type('(452) 012-3456')
            const personalNum = $iFrame.contents().find('[id=invoice_kp-purchase-approval-form-national-identification-number]')
            cy.wrap(personalNum).type('1012201234')
            const submit = $iFrame.contents().find('[id=invoice_kp-purchase-approval-form-continue-button]')
            cy.wrap(submit).click()
            
        })
        
        cy.wait(3000)
        cy.get('#content > h1').should('have.text', 'Your order has been placed!')
    }

    admin(){
            cy.clearCookies()
            cy.fixture('config').then((admin)=>{
                cy.visit(admin.adminURL) 
                cy.get('#input-username').type(admin.adminUsername)
                cy.get('#input-password').type(admin.adminPass)
                cy.get('.btn').click()
                cy.get('.close').click()
                cy.get('h1').should('have.text', 'Dashboard')
            })      
    }
    capture(){
        cy.get('[href="#collapse4"]').click()
        cy.get('#collapse4 > :nth-child(1) > a').click()
        cy.get(':nth-child(1) > :nth-child(8) > [style="min-width: 120px;"] > .btn-group > a.btn > .fa').click()
        cy.get('.nav > :nth-child(3) > a').click()
        cy.get('#quantity').click().type('1')
        cy.get('#btn-capture').click()
        cy.get('#transaction-msg').should('have.text', 'Capture done')
    }

    refund(){
        cy.get('#quantity').click().clear().type('1')
        cy.get('#btn-refund').click()
        cy.get('#transaction-msg').should('have.text', 'Refund done')
    }

    change_currency_to_Euro(){
        cy.get('[href="#collapse7"]').click()
        cy.get('#collapse7 > :nth-child(1) > a').click()
        cy.get('.text-right > .btn').click()
        cy.get('.nav > :nth-child(3) > a').click()
        cy.get('#input-currency').select('Euro')
        cy.get('#button-save').click()
    }
    change_currency_to_DKK(){
        cy.get('[href="#collapse7"]').click()
        cy.get('#collapse7 > :nth-child(1) > a').click()
        cy.get('.text-right > .btn').click()
        cy.get('.nav > :nth-child(3) > a').click()
        cy.get('#input-currency').select('Danish Krone')
        cy.get('#button-save').click()
    }

    ideal_payment(iDEAL_EUR_TERMINAL){
        
        cy.contains(iDEAL_EUR_TERMINAL).click({force: true})
        cy.get('[type="checkbox"]').click()
        cy.get('#button-payment-method').click()
        cy.get('#button-confirm').click()
        cy.get('#idealIssuer').select('AltaPay test issuer 1')
        cy.get('#pensioPaymentIdealSubmitButton').click()
        cy.get('[type="text"]').type('shahbaz.anjum123-facilitator@gmail.com')
        cy.get('[type="password"]').type('Altapay@12345')
        cy.get('#SignInButton').click()
        cy.get(':nth-child(3) > #successSubmit').click().wait(1000)
    }

    ideal_refund(){
        cy.get('[href="#collapse4"]').click()
        cy.get('#collapse4 > :nth-child(1) > a').click()
        cy.get(':nth-child(1) > :nth-child(8) > [style="min-width: 120px;"] > .btn-group > a.btn > .fa').click()
        cy.get('.nav > :nth-child(3) > a').click()
        cy.get('#quantity').click().clear().type('1')
        cy.get('#btn-refund').click()
        cy.get('#transaction-msg').should('have.text', 'Refund done')
    }

    release_payment(){
        cy.get('[href="#collapse4"]').click()
        cy.get('#collapse4 > :nth-child(1) > a').click()
        cy.get(':nth-child(1) > :nth-child(8) > [style="min-width: 120px;"] > .btn-group > a.btn > .fa').click()
        cy.get('.nav > :nth-child(3) > a').click()
        cy.get('#btn-release').click()
        cy.get('#transaction-msg').should('have.text', 'Refund done')
    }
    add_partial_product(){
        cy.get(':nth-child(2) > .product-thumb > .image > a > .img-responsive').click()
        cy.get('#button-cart').click().wait(1000)
        cy.get('h1 > a').click()
    }
    partial_capture(){
        cy.get('[href="#collapse4"]').click()
        cy.get('#collapse4 > :nth-child(1) > a').click()
        cy.get(':nth-child(1) > :nth-child(8) > [style="min-width: 120px;"] > .btn-group > a.btn > .fa').click()
        cy.get('.nav > :nth-child(3) > a').click()
        cy.get('#quantity').click().clear().type('1')
        cy.get('#btn-capture').click()
    }

    create_discounts(){
        
            cy.get('[href="#collapse6"]').click()
            cy.contains('Coupons').click({force: true})
            if(cy.get('tbody > tr ').contains('Enabled')){
                cy.get('thead > tr > .text-center > input').click()
            cy.get('.btn-danger').click().wait(1000)
            }
            let discount_types = {'fixed_discount':'fixed', 'percentage_discount': 'percentage'}

        Object.entries(discount_types).forEach(([key, value]) => {
            cy.get('[href="#collapse6"]').click()
            cy.contains('Coupons').click({force: true})
            cy.get('.pull-right > .btn-primary > .fa').click()
                if(key =='fixed_discount'){
                    cy.get('#input-name').type('Fixed Discount')
                    cy.get('#input-code').type(value)
                    cy.get('#input-type').select('Fixed Amount')
                }
                else{
                    cy.get('#input-name').type('Percentage Discount')
                    cy.get('#input-code').type(value)
                    cy.get('#input-type').select('Percentage')
                }
            cy.get('#input-discount').type('10')
            cy.get('#input-uses-total').clear().type('9999')
            cy.get('#input-uses-customer').clear().type('9999')
            cy.get('.btn-primary').click()
        })
    }
}

export default Order