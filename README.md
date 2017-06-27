# emails
PHP sendmail dummy with web interface for reading emails

## php.ini config

```ini
sendmail_path = /path/to/emails/sendmail
```

## cron job

```
0 12 * * * /path/to/emails/cleaner > /dev/null 2>&1
```
