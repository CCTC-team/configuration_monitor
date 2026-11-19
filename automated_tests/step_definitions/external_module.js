//Add any of your own step definitions here
const { Given, defineParameterType } = require('@badeball/cypress-cucumber-preprocessor')

defineParameterType({
    name: 'emTableName',
    regexp: /system changes|project changes|user role changes/
})

const emTableName = {
    'system changes' : '#system-changes-table',
    'project changes' : '#project-changes-table',
    'user role changes' : '#user-role-changes-table'
}

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


/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I should see {int} row(s) in the {emTableName} table
 * @param {int} num - number of row(s)
 * @param {string} emTableName - available options: 'system changes', 'project changes', 'user role changes'
 * @description verifies the table contains the specified number of row(s)
 */
Given('I should see {int} row(s) in the {emTableName} table', (num, tableName) => {
    const element = emTableName[tableName] + ' tbody tr'
    cy.get(element).its('length').then ((rowCount) => {
        expect(rowCount).to.be.equal(num)
    })
})


/**
 * @module Visibility
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I should see a table header and rows with rowspan containing the following values in a table:
 * @param {dataTable} options the Data Table of values specified
 * @description Verifies a table contains the specified headers and rows, handling columns with rowspan that spans across multiple rows.
 * This step definition is designed for tables where columns have variable positions due to rowspan attributes.
 * It expands cells with rowspan to cover all affected rows during verification.
 */
Given('I should see a table header and rows with rowspan containing the following values in a table:', (dataTable) => {
    const rows = dataTable.rawTable
    const expectedHeaders = rows[0]
    const expectedRows = rows.slice(1)

    cy.get('table').should('be.visible').then(($table) => {
        // Find all rows in the table
        const $allRows = $table.find('tr')

        // Find header row
        let $headerCells
        const $theadHeaders = $table.find('thead tr:first th, thead tr:first td')

        if ($theadHeaders.length > 0) {
            $headerCells = $theadHeaders
        } else {
            // Headers might be in first tbody row
            const $firstRow = Cypress.$($allRows[0])
            $headerCells = $firstRow.find('th, td')
        }

        // Verify headers
        expectedHeaders.forEach((expectedHeader, index) => {
            const headerText = Cypress.$($headerCells[index]).text().trim()
            expect(headerText, `Header at position ${index}`).to.include(expectedHeader.trim())
        })

        // Get data rows (skip header row)
        let $dataRows = $allRows.slice(1)
        if ($theadHeaders.length > 0) {
            $dataRows = $table.find('tbody tr')
        }

        // Build a normalized grid that expands rowspan cells
        const normalizedGrid = []
        const rowspanTracker = [] // Tracks which columns are occupied by rowspan from previous rows

        $dataRows.each((rowIndex, htmlRow) => {
            const $row = Cypress.$(htmlRow)
            const $cells = $row.find('td, th')

            // Initialize tracker for this row if needed
            if (!rowspanTracker[rowIndex]) {
                rowspanTracker[rowIndex] = []
            }

            const normalizedRow = []
            let cellIndex = 0

            // Fill in cells, accounting for rowspan from previous rows
            for (let colIndex = 0; colIndex < expectedHeaders.length; colIndex++) {
                // Check if this column is occupied by a rowspan from a previous row
                if (rowspanTracker[rowIndex][colIndex]) {
                    normalizedRow[colIndex] = rowspanTracker[rowIndex][colIndex]
                } else {
                    // Use the current cell
                    const $cell = Cypress.$($cells[cellIndex])
                    if ($cell.length > 0) {
                        const cellText = $cell.text().trim()
                        const rowspan = parseInt($cell.attr('rowspan') || '1')

                        normalizedRow[colIndex] = cellText

                        // If this cell has rowspan > 1, mark it for subsequent rows
                        if (rowspan > 1) {
                            for (let r = 1; r < rowspan; r++) {
                                const targetRowIndex = rowIndex + r
                                if (!rowspanTracker[targetRowIndex]) {
                                    rowspanTracker[targetRowIndex] = []
                                }
                                rowspanTracker[targetRowIndex][colIndex] = cellText
                            }
                        }

                        cellIndex++
                    } else {
                        normalizedRow[colIndex] = ''
                    }
                }
            }

            normalizedGrid.push(normalizedRow)
        })

        // Verify each expected row exists in the normalized grid
        expectedRows.forEach((expectedRow) => {
            let rowFound = false

            normalizedGrid.forEach((normalizedRow) => {
                // Only check rows that have the same number of cells
                if (normalizedRow.length !== expectedRow.length) {
                    return
                }

                let cellsMatch = true

                expectedRow.forEach((expectedCell, cellIndex) => {
                    if (cellIndex >= normalizedRow.length) {
                        cellsMatch = false
                        return
                    }

                    const cellText = normalizedRow[cellIndex]
                    const expectedText = expectedCell.trim()

                    // Handle date/time pattern matching
                    if (expectedText === 'mm/dd/yyyy hh:mm' || expectedText.match(/^mm\/dd\/yyyy/)) {
                        const dateTimePattern = /\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2}\s*(am|pm)?/i
                        if (!dateTimePattern.test(cellText)) {
                            cellsMatch = false
                        }
                    } else if (expectedText === '') {
                        // Empty cell - allow any whitespace or truly empty
                        if (cellText !== '' && cellText !== ' ') {
                            cellsMatch = false
                        }
                    } else {
                        // Exact match or contains match
                        if (!cellText.includes(expectedText) && cellText !== expectedText) {
                            cellsMatch = false
                        }
                    }
                })

                if (cellsMatch) {
                    rowFound = true
                }
            })

            expect(rowFound, `Expected row not found: ${expectedRow.join(' | ')}`).to.be.true
        })
    })
})


/**
 * @module MailHog
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I should see a {emTableName} table in the email with the following rows:
 * @param {string} emTableName - available options: 'system changes', 'project changes', 'user role changes'
 * @param {DataTable} dataTable - Gherkin DataTable with headers and rows
 * @description Verifies a plain HTML table in emails (e.g., MailHog) contains the specified headers and rows.
 * Supports date/time patterns like mm/dd/yyyy hh:mm.
 */
Given('I should see a {emTableName} table in the email with the following rows:', (tableName, dataTable) => {
    const rows = dataTable.rawTable

    // First row contains headers
    const expectedHeaders = rows[0]
    const expectedRows = rows.slice(1)

    // MailHog displays email content in an iframe - use retriable approach to avoid DOM detachment
    cy.get('iframe#preview-html')
        .its('0.contentDocument.body')
        .should('not.be.empty')
        .then(cy.wrap)
        // Email HTML may not have the same IDs as the web page - try specific ID first, fall back to table
        .then(($body) => {
            const specificTable = $body.find(emTableName[tableName])
            if (specificTable.length > 0) {
                return cy.wrap(specificTable)
            }
            // Fall back to finding any table in the email
            return cy.wrap($body).find('table').should('exist')
        })
        .then(($table) => {
            cy.wrap($table).find('tr').then(($allRows) => {
                // Find header row - could be in thead or first row with th/td elements
                let $headerCells
                const $theadHeaders = $table.find('thead tr:first th, thead tr:first td')

                if ($theadHeaders.length > 0) {
                    // Headers are in thead
                    $headerCells = $theadHeaders
                } else {
                    // Headers are likely in first row (styled differently)
                    // Look for first row with style attribute or just use first row
                    const $firstRow = Cypress.$($allRows[0])
                    $headerCells = $firstRow.find('th, td')
                }

                // Verify headers exist
                expectedHeaders.forEach((expectedHeader, index) => {
                    const headerText = Cypress.$($headerCells[index]).text().trim()
                    expect(headerText, `Header at position ${index}`).to.include(expectedHeader.trim())
                })

                // Verify each expected row exists in the table
                // Skip first row if it contains headers
                let $dataRows = $allRows
                if ($headerCells.length > 0) {
                    $dataRows = $allRows.slice(1)
                }

                expectedRows.forEach((expectedRow) => {
                    let rowFound = false

                    $dataRows.each((_, htmlRow) => {
                        const $cells = Cypress.$(htmlRow).find('td, th')

                        // Only check rows that have the same number of cells
                        if ($cells.length !== expectedRow.length) {
                            return
                        }

                        let cellsMatch = true

                        expectedRow.forEach((expectedCell, cellIndex) => {
                            if (cellIndex >= $cells.length) {
                                cellsMatch = false
                                return
                            }

                            const cellText = Cypress.$($cells[cellIndex]).text().trim()
                            const expectedText = expectedCell.trim()

                            // Handle date/time pattern matching (mm/dd/yyyy hh:mm)
                            if (expectedText === 'mm/dd/yyyy hh:mm' || expectedText.match(/^mm\/dd\/yyyy/)) {
                                // Match date pattern: digits/digits/digits and optional time
                                const dateTimePattern = /\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2}\s*(am|pm)?/i
                                if (!dateTimePattern.test(cellText)) {
                                    cellsMatch = false
                                }
                            } else if (expectedText === '') {
                                // Empty cell - allow any whitespace or truly empty
                                if (cellText !== '' && cellText !== ' ') {
                                    cellsMatch = false
                                }
                            } else {
                                // Exact match or contains match
                                if (!cellText.includes(expectedText) && cellText !== expectedText) {
                                    cellsMatch = false
                                }
                            }
                        })

                        if (cellsMatch) {
                            rowFound = true
                        }
                    })

                    expect(rowFound, `Expected row not found: ${expectedRow.join(' | ')}`).to.be.true
                })
            })
        })
})
