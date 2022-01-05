#!/bin/bash

docker build . --file docker/Dockerfile-build-image -t plugin-opencart-package-build
docker run --rm --mount type=bind,source="$(pwd)",target=/app plugin-opencart-package-build ../bin/bash -c 'cd /app && bash build.sh'
