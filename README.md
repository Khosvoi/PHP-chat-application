# PHP-chat-application
A chat application built for recruiting process at Bunq.
I've followed and completed all asked features for the application plus it has an interactive UI and
is built and run via Docker.

## Features
- Create and join public chat groups
- Send messages within groups
- List all messages in a group
- Persistent SQLite database using Docker volumes
- RESTful JSON API with Slim framework
- Interactive UI for testing endpoints
- Complete test suite (Unit & Integration)

## Running the Application

To run it with new volume and cleaning everything the following command is recommended:

```sh
docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)
docker rmi $(docker images -q)
docker volume rm $(docker volume ls -q)
docker network prune -f
docker system prune -a --volumes -f
sudo lsof -t -i:8000 | xargs kill -9 2>/dev/null || true
```
For simply creating volume and running it the following command is recommended: 

```sh
docker volume create chat-db-volume
docker build -t chat-app .
docker run -p 8000:8000 -v chat-db-volume:/var/lib/chat-db chat-app
```

Then when the current session is interrupted only the last line 
can be repeated and all data remains. 

The application will be available at:
- API: http://localhost:8000
- UI: http://localhost:8000/ui.html

## Running Tests

To run all tests the following command is recommended: 

```sh
./vendor/bin/phpunit 
```

A big thanks to the Bunq recruiting team for encouraging me to learn many new technologies to me
and assigning me this fun project to code :)