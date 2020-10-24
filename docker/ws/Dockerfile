ARG NODE_VERSION=12

FROM node:${NODE_VERSION}

WORKDIR /usr/src/web

RUN mkdir -p /usr/src/web

RUN npm install pm2 -g

RUN npm install

EXPOSE 8080

CMD ["pm2-dev", "start", "ecosystem.config.js"]
