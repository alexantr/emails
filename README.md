# emails
PHP sendmail dummy with web interface for reading emails

## php.ini config

```ini
sendmail_path = /path/to/emails/bin/sendmail
```

## cron job

```
0 12 * * * /path/to/emails/bin/cleaner > /dev/null 2>&1
```
