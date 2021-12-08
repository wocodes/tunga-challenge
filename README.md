### Large Data Set Upload: Tunga Challenge

### Installation Steps:

- Run ```composer install```
- Run php ```artisan migrate:fresh```

On the homepage, 
- Select file to import and Click 'Upload'
- The upload will automatically start in the background and insert records where necessary.
- Alternatively, this upload process checks every minutes to see if there are pending uploads to be done and performs them.
