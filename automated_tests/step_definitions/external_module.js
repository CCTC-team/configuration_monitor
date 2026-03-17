//Add any of your own step definitions here
const { Given, defineParameterType } = require('@badeball/cypress-cucumber-preprocessor')

/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I trigger the cron job
 * @description Visits the cron.php endpoint to trigger the REDCap cron job and then returns to the previous page.
 */
Given("I trigger the cron job", () => {
    cy.url().then((currentUrl) => {
        cy.request({
            url: `${Cypress.config('baseUrl')}/cron.php`,
            failOnStatusCode: false
        }).then(() => {
            cy.visit(currentUrl)
        })
    })
})