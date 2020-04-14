#!/bin/bash

docker-compose up -d --build
PORT=$(docker-compose port app 80 | awk '{print substr($1, 9)}')
echo "http://localhost:${PORT}"