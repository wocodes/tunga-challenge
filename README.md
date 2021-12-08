### Large Data Set Upload: Tunga Challenge

### Installation Steps 
#### (Run the following commands from bash (terminal):

- Pull project from repo using ```git clone https://github.com/wocodes/tunga-challenge.git```
- Run ```composer install```
- Setup your mysql database
- Run ```mv .env.example .env``` this is to copy the example .env file to an app ready env file
- Modify the copied .env file by adding the following
- -- Change ```QUEUE_CONNECTION``` value to ```redis```
- -- Change ```DB_DATABASE``` value to ```your created database```
- -- Change ```DB_USERNAME``` value to ```your database username```
- -- Change ```DB_PASSWORD``` value to ```your database password```
- -- Add ```REDIS_CLIENT=predis``` to the .env file
- Run ```php artisan key:generate```
- Run ```php artisan storage:link```
- Run ```php artisan migrate:fresh```

---
### Usage
On the homepage, 
- Select file to import and Click 'Upload'
- The upload will automatically start in the background and insert records where necessary.
- Alternatively, this upload process checks every minutes to see if there are pending uploads to be done and performs them.
