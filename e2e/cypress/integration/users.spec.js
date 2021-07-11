/// <reference types="cypress" />

describe('Users', () => {
    before(() => {
        cy.task('database:recreate');
    });

    it('Checking if my profile is correct', () => {
        cy.visit('/users/user');

        cy.contains('Followers').parent().get('.h3 a').contains('0');
        cy.contains('Following').parent().get('.h3 a').contains('0');
    });

    it('Checking if I can see action buttons', () => {
        cy.login();

        cy.visit('/users/ana');

        cy.get('a').contains(' Follow ');
        cy.get('a').contains(' Block ');
        cy.get('a').contains(' Message ');
    });

    it('Checking if I can follow and unfollow another user', () => {
        cy.login();

        // Follow
        cy.visit('/users/ana');

        cy.get('a').contains(' Follow ').click();

        cy.contains('You have successfully followed this user');
        cy.contains('Followers').parent().find('a').contains('1');
        cy.contains('Followers').parent().find('a').click();
        cy.get('.card .card-body small').contains('(user)');

        cy.visit('/users/user');

        cy.contains('Following').parent().find('a').contains('1');

        // Unfollow
        cy.visit('/users/ana');

        cy.get('a').contains(' Unfollow ').click();

        cy.contains('You have successfully unfollowed this user');
        cy.contains('Followers').parent().find('a').contains('0');
        cy.contains('Followers').parent().find('a').click();
        cy.contains('No followers yet');
    });

    it('Checking if I can block and unblock another user', () => {
        cy.login();

        // Block
        cy.visit('/users/ana');

        cy.get('a').contains(' Block ').click();

        cy.contains('You have successfully blocked the user');

        cy.logout();
        cy.login('ana');

        cy.visit('/users/user');

        cy.contains('You are blocked by this user');

        cy.logout();

        // Unblock
        cy.login();

        cy.visit('/users/ana');

        cy.get('a').contains(' Unblock ').click();

        cy.contains('You have successfully unblocked the user');

        cy.logout();
        cy.login('ana');

        cy.visit('/users/user');

        cy.contains('You are blocked by this user').should('not.exist');
    });

    it.only('Checking if I can message another user', () => {
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
