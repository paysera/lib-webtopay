#!/bin/bash

container='lib_webtopay_tests'

if [ "$(docker ps -a -q -f name=$container)" ]; then
    docker stop $container
    docker rm $container
fi
docker build -t $container -f $PWD/Dockerfile --build-arg PHP_VER=7.4 $PWD

docker run -d --name $container -v $PWD:/var/www -w /var/www $container
#docker exec $container bash -c "composer i"

docker exec -it $container bash
docker exec $container bash -c "composer run phpunit"
docker container stop $container
#docker container rm --force $container
#docker image rm $container
