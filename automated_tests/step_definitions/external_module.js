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

/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example the Configuration Monitor role filter should contain {string}
 * @param {string} text - expected option text
 * @description Verifies the text appears in the role filter dropdown
 */
Given("the Configuration Monitor role filter should contain {string}", (text) => {
    cy.get('#role_id').should('contain', text)
})

/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I set the Configuration Monitor filter {string} to {string}
 * @param {string} id - id of the dropdown
 * @param {string} value - option value
 * @description Selects an option in a filter dropdown, which reloads the page
 */
Given("I set the Configuration Monitor filter {string} to {string}", (id, value) => {
    cy.get(`#${id}`).should('not.be.disabled').select(value)
    cy.location('search').should('include', `${id}=${value}`)
})


/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I click the Configuration Monitor paging button {string}
 * @param {string} id - id of the button
 * @description Clicks the next or previous page button
 */
Given("I click the Configuration Monitor paging button {string}", (id) => {
    const pagenum = id === 'btnnextpage' ? 'pagenum=1' : 'pagenum=0'
    cy.document().its('readyState').should('eq', 'complete')
    cy.get('input[name="redcap_csrf_token"]').should('exist')
    cy.get(`#${id}`).should('not.be.disabled').then(($button) => $button[0].click())
    cy.location('search').should('include', pagenum)
})


/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I reset the Configuration Monitor filters
 * @description Clicks the reset button on the filter form
 */
Given("I reset the Configuration Monitor filters", () => {
    cy.document().its('readyState').should('eq', 'complete')
    cy.contains('button.clear-button', 'reset').then(($button) => $button[0].click())
    cy.location('search').should('not.include', 'defaulttimefilter=')
})


/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example the Configuration Monitor filter {string} should have the value {string}
 * @param {string} id - id of the filter
 * @param {string} value - expected value
 * @description Verifies the value of a filter on the change log page
 */
Given("the Configuration Monitor filter {string} should have the value {string}", (id, value) => {
    cy.get(`#${id}`).should('have.value', value)
})


/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I add {int} rows to the Configuration Monitor user role change log
 * @param {int} count - number of rows to add
 * @description Adds rows directly to the change log, so that the page has enough rows to test paging
 */
Given("I add {int} rows to the Configuration Monitor user role change log", (count) => {
    const mysql = Cypress.env('mysql')
    const container = mysql?.docker_container || 'CCTC_REDCap_Docker'
    cy.url().then((url) => {
        const pid = parseInt(new URL(url).searchParams.get('pid'), 10)
        const ts = new Date().toISOString().replace(/[-:T]/g, '').slice(0, 14)
        const rows = Array.from({ length: count }, (_, i) => `(${pid}, 99, NULL, 'Paging role ${i}', ${ts}, 'INSERT')`).join(',')
        const sql = `insert into user_role_changelog (project_id, role_id, old_value, new_value, ts, operation_type) values ${rows}`
        cy.exec(`docker exec ${container} mysql -u${mysql.db_user} -p${mysql.db_pass} ${mysql.db_name} -e "${sql}"`)
    })
})
