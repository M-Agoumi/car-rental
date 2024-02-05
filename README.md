# Car rental
## Description
this is an api for car rental, it has only few routes to manage cars, and reservations

## Installation
**1. Clone the repository**
**2. cp .env.example .env:**
> Update the .env file with your database credentials

**3. composer install**
> Install the dependencies of the project

**4. php bin/console lexik:jwt:generate-keypair**
> Generate the public and private keys for jwt

**5. php bin/console doctrine:database:create** (optional)
> Create the database if you haven't created it yet

**6. php bin/console doctrine:schema:update --complete --dump-sql**
> verify the sql to be executed, if it is correct, then run the command with --force instead of --dump-sql

**7. php bin/console doctrine:schema:update --complete --force --env=test**
> Create the database schema for test environment

**8. php bin/console doctrine:fixtures:load --env=test**
> Load the fixtures to the database of the test environment, so you can run the unitTests the api

**9. visit the api documentation at /api/doc**
> You can see the documentation of the api at /api/doc, explore the routes and test them