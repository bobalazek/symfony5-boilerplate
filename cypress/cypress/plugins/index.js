/// <reference types="cypress" />

const axios = require('axios');

/**
 * @type {Cypress.PluginConfig}
 */
module.exports = (on, config) => {
  on('task', {
    async 'database:recreate'() {
      const response = await axios.get(`${config.baseUrl}/dev?action=database_recreate`);

      console.log(response);

      return true;
    },
  });

  return config;
}
