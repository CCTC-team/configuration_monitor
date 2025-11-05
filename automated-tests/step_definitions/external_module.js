const { Given, defineParameterType } = require('@badeball/cypress-cucumber-preprocessor')


defineParameterType({
    name: 'emTableName',
    regexp: /monitoring logging|data entry log|system changes|project changes|user role changes/
})


emTableName = {
    'monitoring logging' : '#monitor-query-data-log',
    'data entry log' : '#log-data-entry-event',
    'system changes' : '#system_changes_table',
    'project changes' : '#project_changes_table',
    'user role changes' : '#user_role_changes_table',
}


/**
 * @module ExternalModule
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I {externalOption} the external module named {string}
 * @param {string} externalOption - available options - 'Enable', 'Delete Version'
 * @param {string} label - name of external module
 * @description Enable/Disable external module
 */
Given("I click on the button labeled {externalOption} for the external module named {string}", (option, label) => {
    cy.get('#external-modules-disabled-table').find('td').contains(label).parents('tr').within(() => {
        cy.get('button').contains(option).click()
    })
})


/**
 * @module ExternalModule
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I click on the button labeled {string} for the field labeled {string} in the external module configuration
 * @param {string} buttonLabel - Label on button
 * @param {string} field - Field Label
 * @description Clicks on the button for the field in the external module configuration
 */
Given("I click on the button labeled {string} for the field labeled {string} in the external module configuration", (buttonLabel, field) => {
    cy.get('.table-no-top-row-border').find('td').contains(field).parents('tr').within(() => {
        cy.get('button').contains(buttonLabel).click()
    })
})


/**
 * @module ExternalModule
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I close the dialog box for the external module {string}
 * @param {string} name - Name of external module
 * @description Close the dialog box for the external module
 */
Given("I close the dialog box for the external module {string}", (name) => {
    cy.get('.modal-dialog').contains(name).parents('div[class="modal-header"]').within(() => {
        cy.get('button[class=close]').click()
    })
})


/**
 * @module ControlCenter
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I click on the {toDoTableIcons} icon for the {string} request created for the project named {string} within the {toDoTableTypes} table
 * @param {string} toDoTableIcons - available options: 'process request', 'get more information', 'add or edit a comment', 'Move to low priority section', 'archive request notification'
 * @param {string} request_name - Name of request
 * @param {string} project_name - the text value of project name you want to target
 * @param {string} toDoTableTypes - available options: 'Pending Requests', 'Low Priority Pending Requests', 'Completed & Archived Requests'
 * @description Clicks on an icon within the To-Do-List page based upon Icon, Request Name, Project Name, and Table Name specified.
 */
Given('I click on the {toDoTableIcons} icon for the {string} request created for the project named {string} within the {toDoTableTypes} table', (icon, request_name, project_name, table_name) => {
    cy.get(`.${window.toDoListTables[table_name]}`).within(() => {
        cy.get(`.request-container:contains("${project_name}"):has(.type:contains("${request_name}"))`).within(() => {
            cy.get(`button[data-tooltip="${icon}"]`).click()
        })
    })
})


/**
 * @module ControlCenter
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I (should )see the {string} request created for the project named {string} within the {toDoTableTypes} table
 * @param {string} request_name - Name of request
 * @param {string} project_name - the text value of project name you want to target
 * @param {string} toDoTableTypes - available options: 'Pending Requests', 'Low Priority Pending Requests', 'Completed & Archived Requests'
 * @description Identifies Request Name within the To-Do-List page based upon Project Name, and Table Name specified.
 */
Given('I (should )see the {string} request created for the project named {string} within the {toDoTableTypes} table', (request_name, project_name, table_name) => {
    cy.get(`.${window.toDoListTables[table_name]}`).within(() => {
        cy.get(`.request-container:contains("${project_name}"):has(.type:contains("${request_name}"))`)
    })
})


/**
 * @module ConfigurationMonitor
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I should see {int} row(s) in the {emTableName} table
 * @param {int} num - number of row(s)
 * @param {string} emTableName - available options: 'monitoring logging', 'data entry log', 'system changes', 'project changes', 'user role changes'
 * @description verifies the table contains the specified number of row(s)
 */
Given('I should see {int} row(s) in the {emTableName} table', (num, tableName) => {
    element = emTableName[tableName] + ' tbody tr'
    cy.get(element).its('length').then ((rowCount) => {
        // Subtracting 1 for header
        if (tableName === 'data entry log') {
            rowCount = rowCount-1
        }

        if (tableName === 'monitoring logging') {
             // Subtracting 1 for header and dividing by 2 as each entry has 2 rows
            rowCount = (rowCount-1)/2
        }

        expect(rowCount).to.be.equal(num)
    })
})


/**
 * @module MailHog
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I should see an email table with the following rows:
 * @param {DataTable} dataTable - Gherkin DataTable with headers and rows
 * @description Verifies a plain HTML table in emails (e.g., MailHog) contains the specified headers and rows.
 * Supports date/time patterns like mm/dd/yyyy hh:mm.
 */
Given('I should see an email table with the following rows:', (dataTable) => {
    const rows = dataTable.rawTable

    // First row contains headers
    const expectedHeaders = rows[0]
    const expectedRows = rows.slice(1)

    // MailHog displays email content in an iframe - switch to it first
    cy.get('iframe#preview-html').then(($iframe) => {
        const iframeBody = $iframe.contents().find('body')

        // Find table in the iframe body
        cy.wrap(iframeBody).find('table').then(($table) => {

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
})