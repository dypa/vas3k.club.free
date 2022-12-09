if [ "$(docker container inspect vas3k-club-reader)" ]; then
    docker stop vas3k-club-reader -t 1
    docker rm vas3k-club-reader
fi

docker run -d --name vas3k-club-reader --restart unless-stopped -v "$(pwd):/app" --add-host=host.docker.internal:host-gateway vas3k.club.reader:v2
