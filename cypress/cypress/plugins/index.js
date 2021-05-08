/// <reference types="cypress" />

const axios = require('axios');

/**
 * @type {Cypress.PluginConfig}
 */
module.exports = (on, config) => {
  on('task', {
    async 'database:recreate'() {
      await axios.get(`${config.baseUrl}/dev?action=database_recreate`);

      return true;
    },
  });

  return config;
}
