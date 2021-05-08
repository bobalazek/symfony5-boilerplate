/// <reference types="cypress" />

describe('Settings', () => {
  before(() => {
    cy.task('database:recreate');
  });

  it('Checking if general settings are saved correctly', () => {
    cy.login();

    cy.visit('/settings');

    cy.get('input[name="settings[firstName]"]').clear().type('User2');

    cy.get('button[type="submit"]').click();

    cy.get('input[name="settings[firstName]"]').should('have.value', 'User2');
  });

  it('Checking if image settings are saved correctly', () => {
    cy.login();

    cy.visit('/settings/image');

    cy.get('.single-avatar-image[data-name="02.png"]').click();

    cy.get('button[type="submit"]').click();

    cy.get('.single-avatar-image[data-name="02.png"]').should('have.class', 'selected');
  });

  it('Checking if password settings are saved correctly', () => {
    cy.login();

    cy.visit('/settings/password');

    cy.get('input[name="settings_password[oldPlainPassword]"]').type('password');
    cy.get('input[name="settings_password[plainPassword][first]"]').type('newpassword');
    cy.get('input[name="settings_password[plainPassword][second]"]').type('newpassword');

    cy.get('button[type="submit"]').click();

    cy.get('div.alert.alert-success').contains('successfully');
  });
});
