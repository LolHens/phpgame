# phpgame
This is a top-down shooter that is implemented with close to no javascript. It is basically built like your typical php webshop. It was a nice challange but as you would expect the gameplay is pretty crappy.

## Docker Stack
```yml
version: '3.7'

services:
  phpgame:
    image: ghcr.io/lolhens/phpgame
    environment:
      DB_HOST: 'mariadb'
      DB_USERNAME: 'root'
      DB_PASSWORD: 'root'
      DB_NAME: 'game'
    networks:
      - db
    ports:
      - 1234:80
    deploy:
      update_config:
        order: start-first
  mariadb:
    image: mariadb
    environment:
      TZ: 'Europe/Berlin'
      MYSQL_ROOT_PASSWORD: 'root'
      MYSQL_DATABASE: 'game'
    volumes:
      - phpgame/db:/var/lib/mysql
      - phpgame/initdb:/docker-entrypoint-initdb.d
    networks:
      - db
    healthcheck:
      test: ['CMD', 'mysqladmin', '--password=root', 'ping']
      timeout: 10s
      retries: 5

networks:
  db:
    driver: overlay
```
