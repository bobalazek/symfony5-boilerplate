/// <reference types="cypress" />

describe('FollowerRequests', () => {
  const prepareAndSendFollowerRequest = () => {
    // Change privacy settings to private
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

    // Check if request was send
    cy.login();

    cy.visit('/follower-requests');

    cy.get('.card .card-body small').contains('(ana)');
  };

  beforeEach(() => {
    cy.task('database:recreate');
  });

  it('Checking if follower request work', () => {
    prepareAndSendFollowerRequest();
  });

  it('Checking if follower request approval works', () => {
    prepareAndSendFollowerRequest();

    cy.get('a').contains(' Approve ').click();

    cy.contains('No follower requests yet');

    // Check if user is followed
    cy.logout();

    cy.login('ana');

    cy.visit('/users/ana/following');

    cy.get('.card .card-body small').contains('(user)');
  });

  it('Checking if follower request ignore works', () => {
    prepareAndSendFollowerRequest();

    cy.get('a').contains(' Ignore ').click();

    cy.contains('You have successfully ignored the follower request');

    cy.get('a').contains('Ignored').click();

    cy.get('.card .card-body small').contains('(ana)');
  });

  it('Checking if follower request delete works', () => {
    prepareAndSendFollowerRequest();

    cy.get('a').contains(' Delete ').click();

    cy.contains('You have successfully deleted the follower request');
  });
});
