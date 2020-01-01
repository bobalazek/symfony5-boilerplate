FROM node:12

WORKDIR /var/www/web

RUN npm install yarn -g

COPY start.sh /usr/local/bin/
RUN sed -i 's/\r$//' /usr/local/bin/start.sh
RUN chmod 755 /usr/local/bin/start.sh
