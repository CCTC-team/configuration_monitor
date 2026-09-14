//Add any of your own step definitions here
const { Given, defineParameterType } = require('@badeball/cypress-cucumber-preprocessor')

const ACCESS_DENIED = 'You do not have permission to access this page.'

// Builds the URL of one of this module's pages, carrying over the pid of the
// page currently open so project pages resolve to the same project.
function modulePageUrl(page, params = '') {
    return cy.url().then((currentUrl) => {
        const pid = new URL(currentUrl).searchParams.get('pid')
        const pidParam = pid ? `&pid=${pid}` : ''
        return `/redcap_v${Cypress.env('redcap_version')}/ExternalModules/?prefix=configuration_monitor&page=${page}${pidParam}${params}`
    })
}

function requestCsvExport(tableName) {
    return modulePageUrl('csv_export', `&tableName=${tableName}&export_type=everything`).then((url) => {
        return cy.request({ url: url, failOnStatusCode: false })
    })
}

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

/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I visit the Configuration Monitor page "systemChanges" by URL
 * @param {string} page - the module page name (systemChanges, projectChanges or userRoleChanges)
 * @description Opens a Configuration Monitor page directly by URL, without using its link. On a project page, the current project's pid is kept.
 */
Given("I visit the Configuration Monitor page {string} by URL", (page) => {
    modulePageUrl(page).then((url) => {
        cy.visit(url)
    })
})

/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example the Configuration Monitor CSV export of "system-changes" should be denied
 * @param {string} tableName - system-changes, project-changes or user-role-changes
 * @description Requests the full CSV export directly by URL and checks it is refused with HTTP 403. On a project page, the current project's pid is kept.
 */
Given("the Configuration Monitor CSV export of {string} should be denied", (tableName) => {
    requestCsvExport(tableName).then((response) => {
        expect(response.status).to.eq(403)
        expect(response.body).to.contain(ACCESS_DENIED)
    })
})

/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example the Configuration Monitor CSV export of "project-changes" should be allowed
 * @param {string} tableName - system-changes, project-changes or user-role-changes
 * @description Requests the full CSV export directly by URL and checks it is not refused. On a project page, the current project's pid is kept.
 */
Given("the Configuration Monitor CSV export of {string} should be allowed", (tableName) => {
    requestCsvExport(tableName).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).not.to.contain(ACCESS_DENIED)
    })
})
