#!/bin/bash

docker run -it --rm -e CSS_BOOTSWATCH=flatly -e CSS_PRISM=coy -v "$(pwd)/..":/app sandrokeil/bookdown docs/book/bookdown.json @a