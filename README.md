# Commissions Calculator

Given:

 - There is a list of transactions in CSV file, see [example](https://github.com/dnegometyanov/commissions-calculator/blob/master/src/InputData/input.csv)
 - And exchanges rates are provided by [api.exchangeratesapi.io](https://api.exchangeratesapi.io/latest)
 - And there are following types of user: `private` and `business`
 - And there are following types of transactions `deposit` and `witdrawal`
 - And `withdrawal` + `business` transactions commissions are calculated with flat percentage rate of `0.5%`
 - And `deposit` + (both `private` and  `business`)  transactions commissions are calculated with flat percentage rate of `0.03%`
 - And `withdrawal` + (`private`) are free if weekly (Mon-Sun) total amount of these type of transactions is less then `EUR 1000`
    according to `api.exchangeratesapi.io` and at the same time less or equal then `3` transactions of this type per week. 
    In other case, the part of transaction that exceeds the above threshold conditions has commission of `0.3%`

Expected:
 - Console command should take the csv filename as input and output commission amount without currency as output.   

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