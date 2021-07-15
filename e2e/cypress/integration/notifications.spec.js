/// <reference types="cypress" />

describe('Notifications', () => {
  const sendFollowerRequestAndGetNotification = () => {
    cy.login();

    cy.visit('/settings/privacy');

    cy.getInputByLabel('Visibility').select('Private', { force: true });

    cy.contains('Save').click();

    cy.logout();

    // Follow user
    cy.login('ana');

    cy.visit('/users/user');

    cy.get('a').contains(' Follow ').click();

    cy.contains('You have successfully requested to follow this user');

    cy.logout();

    // Check if you got notification
    cy.login();

    cy.visit('/notifications');

    cy.contains(' has sent you a follower request');
  };

  beforeEach(() => {
    cy.task('database:recreate');
  });

  it('Checking if the notifications works', () => {
    sendFollowerRequestAndGetNotification();
  });

  it('Checking if you can view notification', () => {
    sendFollowerRequestAndGetNotification();

    cy.contains('View').first().click();

    cy.get('.card .card-body small').contains('(ana)');
  });

  it.only('Checking if you can view notification', () => {
    sendFollowerRequestAndGetNotification();

    cy.contains('Mark as read').first().click();

    cy.contains('You have successfully marked the notification as read');
  });
});
