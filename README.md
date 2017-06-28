# emails
PHP sendmail dummy with web interface for reading emails

## Install

```
composer create-project alexantr/emails
```

Set execution permissions: 

```
chmod u+x /path/to/emails/bin/sendmail
chmod u+x /path/to/emails/bin/cleaner
```

## php.ini config

```ini
sendmail_path = /path/to/emails/bin/sendmail
```

## cron job

If you want to delete old emails automatically, add cron job:

```
0 12 * * * /path/to/emails/bin/cleaner > /dev/null 2>&1
```

By default emails older than 2 days will be deleted.
