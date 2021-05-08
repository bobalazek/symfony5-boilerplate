/// <reference types="cypress" />

describe('Settings', () => {
  before(() => {
    cy.task('database:recreate');
  });

  it('Checking if settings are saved correctly', () => {
    cy.login();

    cy.visit('/settings');

    cy.get('input[name="settings[firstName]"]').clear().type('User2');

    cy.get('button[type="submit"]').click();

    cy.get('input[name="settings[firstName]"]').should('have.value', 'User2');
  });
});
