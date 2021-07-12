/// <reference types="cypress" />

describe('Settings', () => {
  before(() => {
    cy.task('database:recreate');
  });

  it('Checking if general settings are saved correctly', () => {
    cy.login();

    cy.visit('/settings');

    cy.getInputByLabel('First name').clear().type('User2');

    cy.contains('Save').click();

    cy.getInputByLabel('First name').should('have.value', 'User2');
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

    cy.getInputByLabel('Old password').type('password');
    cy.getInputByLabel('New password').type('newpassword');
    cy.getInputByLabel('Repeat new password').type('newpassword');

    cy.contains('Save').click();

    cy.get('div.alert.alert-success').contains('successfully');
  });

  it('Checking if old password settings are saved correctly', () => {
    cy.task('database:recreate');

    cy.login();

    cy.visit('/settings/password');

    cy.getInputByLabel('Old password').type('superwrongpassword');
    cy.getInputByLabel('New password').type('newpassword');
    cy.getInputByLabel('Repeat new password').type('newpassword');

    cy.contains('Save').click();

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

      cy.on('window:confirm', (text) => {
        expect(text).to.contains('Are you sure?');
      });

    cy.url().should('include', '/auth/login');
  });

  it('Checking 2FA warning shows up', () => {
    cy.login();

    cy.visit('/settings/tfa');

    cy.get('select[name="settings_tfa[tfaEnabled]"]').select('Enabled', { force: true });

    cy.contains('Save').click();

    cy.get('.alert.alert-danger').should('exist').contains('You enabled 2FA, but do not have any methods enabled. Please activate at least one method for the functionality to work');
  });

  it('Checking if block users are shown', () => {
    // No blocked users
    cy.login();

    cy.visit('/settings/blocks');

    cy.contains('You are not blocking anyone yet');

    // With blocked user
    cy.visit('/users/ana')

    cy.get('a').contains(' Block ').click();

    cy.get('a').contains('user').click();

    cy.get('.dropdown-menu').contains('Settings').click();

    cy.get('li a').contains('Blocks').click();

    cy.get('.card .card-body small').contains('(ana)').should('exist');

    // Unblock blocked user
    cy.get('.card .card-body small').contains('(ana)').closest('.card').contains('View').click();

    cy.get('a').contains(' Unblock ').click();

    cy.contains('You have successfully unblocked the user').should('exist');

    cy.visit('/settings/blocks').contains('You are not blocking anyone yet');
  });

  it('Checking if export request works', () => {
    cy.login();

    cy.visit('/settings/export');

    cy.contains('Here you can request an export of all your data on this platform');

    cy.contains('Request export').click();

    cy.contains('Export successfully requested');

    cy.wait(2000);

    cy.reload();

    cy.get('a').contains('Download').should('exist');
  });

  it('Checking if alert message in deletion exist', () => {
    cy.login();

    cy.visit('/settings/deletion');

    cy.contains('This action is permanent. You will not be able to retrieve any of the content or information you shared with this platform. An email will be sent to you, so you can confirm your action').should('exist');
  });
});
