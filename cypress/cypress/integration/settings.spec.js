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

  it('Checking if old password settings are saved correctly', () => {
    cy.task('database:recreate');

    cy.login();

    cy.visit('/settings/password');

    cy.get('input[name="settings_password[oldPlainPassword]"]').type('superwrongpassword');
    cy.get('input[name="settings_password[plainPassword][first]"]').type('newpassword');
    cy.get('input[name="settings_password[plainPassword][second]"]').type('newpassword');

    cy.get('button[type="submit"]').click();

    cy.get('.form-error-message').contains('Wrong value for your current password');
  });

  it('Checking if device invalidation works', () => {
    cy.login();

    cy.visit('/settings/devices');

    cy.get('tr td b')
      .contains('(current)')
      .parent()
      .parent()
      .find('a.btn.btn-confirm')
      .click();

    cy.url().should('include', '/auth/login');
  });

  it('Checking 2FA warning shows up', () => {
    cy.login();

    cy.visit('/settings/tfa');

    cy.get('select[name="settings_tfa[tfaEnabled]"]').select('1', { force: true });

    cy.get('button[type="submit"]').click();

    cy.get('.alert.alert-danger').should('exist').contains('You enabled 2FA, but do not have any methods enabled. Please activate at least one method for the functionality to work');
  });
});
