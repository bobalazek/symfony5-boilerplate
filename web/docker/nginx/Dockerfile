FROM nginx:alpine

EXPOSE 80
EXPOSE 443

# Copy config
COPY config/nginx.conf /etc/nginx/nginx.conf
COPY sites/default.conf /etc/nginx/sites-available/default.conf
COPY conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY certs/nginx-selfsigned.crt /etc/ssl/certs/nginx-selfsigned.crt
COPY certs/nginx-selfsigned.key /etc/ssl/certs/nginx-selfsigned.key

CMD ["nginx", "-g", "daemon off;"]
