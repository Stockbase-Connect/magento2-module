# Documentation

This directory forms a Bookdown production project, with an eye toward publishing via Github Pages or similar.

## Generating the Docs

### Requirements:
* [Docker](https://www.docker.com/)

### Build
To build the project on Mac/Linux:

    ./build.sh

To build in other platforms, simply inspect the command inside that file, modify accordingly, and run it manually.

This will generate the HTML files inside the `./docs` folder.

To browse the built HTML, run the built-in PHP server. For example:

    php -S localhost:8080 -t ./docs/

For documentation on Bookdown visit [bookdown.io](http://bookdown.io)
