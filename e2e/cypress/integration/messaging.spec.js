/// <reference types="cypress" />

describe('Messaging', () => {
    before(() => {
        cy.task('database:recreate');
    });

    it('Checking if I can message another user', () => {
        // Send message
        cy.login();

        cy.visit('/users/ana');

        cy.get('a').contains(' Message ').click();

        cy.get('#messaging-threads a.messaging-thread:first').click();

        cy.contains('This thread has no messages yet').should('exist');

        cy.get('#messaging-thread-messages-wrapper .form-control').type('Hello World');

        cy.get('#messaging-thread-messages-wrapper').contains('Send').click();

        cy.contains('This thread has no messages yet').should('not.exist');

        // Recieve message
        cy.logout();

        cy.login('ana');

        cy.get('header .dropdown .dropdown-toggle').click();

        cy.get('header .dropdown-menu').contains('Messaging').click();

        cy.contains('No thread selected').should('exist');

        cy.get('#messaging-threads a.messaging-thread:first').click();

        cy.get('#messaging-thread-messages-inner').contains('Hello World').should('exist');
    });
  });
