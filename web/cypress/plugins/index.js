/// <reference types="cypress" />

const axios = require('axios');

/**
 * @type {Cypress.PluginConfig}
 */
// eslint-disable-next-line no-unused-vars
module.exports = (on, config) => {
  on('task', {
    async 'database:recreate'() {
      const response = await axios.get(`${config.baseUrl}/dev?action=database_recreate`);
      return true;
    },
  });

  return config;
}
