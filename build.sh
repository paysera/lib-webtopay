#!/bin/bash

container='lib_webtopay_build'

if [ "$(docker ps -a -q -f name=$container)" ]; then
    docker stop $container
    docker rm $container
fi
docker build -t $container -f $PWD/Dockerfile_build $PWD

docker run -d --name $container -v $PWD:/var/www -w /var/www $container
docker exec $container bash -c "php build/phing-latest.phar -f build/build.xml"
docker container stop $container
docker container rm --force $container
docker image rm $container
