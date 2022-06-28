
# HackerNews Api 

This api serves to filter data fetched from Hacker News Api.

It has api endpoints that filters the top most 10 occuring words in the titles of the last 25 stories and top 10 most occuring words in the titles of since last week.

# Getting started 
 Pools have been used to make parallel calls in order to improve wait time.


## Installation

## HackerNews Api
This api serves to filter data fetched from Open Hacker News Api.

It has api endpoints that filters the top most 10 occuring words in the titles of the last 25 stories and top 10 most occuring words in the titles of the post of last week.


Assuming you've already installed on your machine: PHP (>= 8.0.0), Laravel, Composer and Node.js.



```bash
# install dependencies
composer install
npm install

# create .env file and generate the application key
cp .env.example .env
php artisan key:generate

# build CSS and JS assets
npm run dev
# or, if you prefer minified files
npm run prod
```
Then launch the server:

```bash
php artisan serve

```
The Laravel sample project is now up and running! Access it at http://localhost:8000.

The endpoints currently served are:

http://localhost:8000/api/last-twenty-five

http://localhost:8000/api/last-week