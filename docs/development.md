# Corcoviewer

## Development

### Web

* Before you start working on the web frontend run: `docker exec -ti cw_node_web yarn run watch`
* Go to your browser and open http://localhost:80 (or whichever port you set in `.env` for the `NGINX_PORT_80` variable)

#### PhpMyAdmin

* Go to your browser and open http://localhost:81 (or whichever port you set in `.env` for the `PHPMYADMIN_PORT_80` variable)

### Game

* Before you start working on the game run: `docker exec -ti cw_node_game npm start`
* Go to your browser and open http://localhost:8080 (or whichever port you set in `.env` for the `GAME_CLIENT_PORT_8080` variable)
