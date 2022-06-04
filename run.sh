if [ "$(docker container inspect vas3k-club-free)" ]; then
    docker stop vas3k-club-free -t 1
    docker rm vas3k-club-free
fi

docker run -d --name vas3k-club-free --restart unless-stopped -v "$(pwd):/app" --add-host=host.docker.internal:host-gateway vas3k.club.free:v1
