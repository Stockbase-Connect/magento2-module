# Documentation

This directory forms a Bookdown production project, with an eye toward publishing via Github Pages or similar.

## Generating the Docs

### Requirements:
* [Docker](https://www.docker.com/)

### Build
To build the project on Mac/Linux:

    cd docs
    ./bin/build

To build in other platforms, simply inspect the docker command inside that file, modify accordingly, and run it manually.

This will generate the HTML files inside the `../docs` folder (`./docs` relative to the repository root).

See `./bin/build -h` for additional options.

##### Local Preview

To both build and locally preview the docs run `./bin/build --preview`

You can also preview manually using the built-in PHP server. For example:

    # from the root of the repository
    php -S localhost:8080 -t ./docs/

For documentation on Bookdown visit [bookdown.io](http://bookdown.io)
