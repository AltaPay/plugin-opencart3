require('cypress-xpath')

class Order
{
    clrcookies(){
        cy.clearCookies()
        
    }

    visit()
    {
        cy.fixture('config').then((url)=>{
        cy.visit(url.shopURL)
         
        })   
     
    }
    
    addproduct(){
        cy.get(':nth-child(1) > .product-thumb > .image > a > .img-responsive').click()
        cy.get('#button-cart').click()
        cy.get('.alert > [href="http://54.246.153.166/opencart/index.php?route=checkout/cart"]').click()
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
        cy.get('#button-guest').click()
        
        
    }

    cc_payment(){
        
        cy.contains('EmbraceIT Test Terminal').click()
        cy.get('[type="checkbox"]').click()
        cy.get('[type="checkbox"]')
        cy.get('#button-payment-method').click()
        cy.get('#button-confirm').click()
        cy.get('[id=creditCardNumberInput]').type('4111111111111111')
        cy.get('#emonth').type('01')
        cy.get('#eyear').type('2023')
        cy.get('#cvcInput').type('123')
        cy.get('#cardholderNameInput').type('testname')
        cy.get('#pensioCreditCardPaymentSubmitButton').click().wait(2000)
        cy.get('#content > h1').should('have.text', 'Your order has been placed!')

    
    }
    
    klarna_payment(){

        cy.contains('EmbraceIT Klarna DKK Test Terminal').click()
        cy.get('[type="checkbox"]').click()
        cy.get('[type="checkbox"]')
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

    admin()
    {
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

  

}

export default Order