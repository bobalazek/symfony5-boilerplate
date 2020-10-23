ARG NODE_VERSION=12

FROM node:${NODE_VERSION}

RUN npm install pm2 -g

RUN npm install ws

WORKDIR /usr/src/web

RUN mkdir -p /usr/src/web

CMD ["pm2-runtime", "start", "ecosystem.config.js"]
