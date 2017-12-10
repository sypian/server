#!/bin/bash

end="$((SECONDS+300))"
while true; do
    [[ "200" = "$(curl --silent --write-out "%{http_code}" --output /dev/null http://neo4j:7474)" ]] && echo "neo4j is up and running!" && break
    [[ "${SECONDS}" -ge "${end}" ]] && echo "Waiting for neo4j timeout of 300 seconds reached!" && exit 1
    sleep 1
done
