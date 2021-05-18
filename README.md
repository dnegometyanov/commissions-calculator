# Docker / PHP 7.4 console / composer / phpunit 

Blank docker project for console php 7.4 projects with composer and phpunit.

## Prerequisites

Install Docker and optionally Make utility.

Commands from Makefile could be executed manually in case Make utility is not installed.

## Build container and install composer dependencies

    Make build

## Build container and install composer dependencies

If dist files are not copied to actual destination, then
    
    Make copy-dist-configs
        
## Run application

Runs container and executes console application.

    Make run

## Run unit tests

Runs container and executes unit tests.

    Make unit-tests

## Static analysis

Static analysis check

    Make static-analysis
    
## Fix code style

    Make cs-fix