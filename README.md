# Emails

Simple web interface for reading emails from /var/mail

## Install

```bash
composer create-project alexantr/emails
```

or

```bash
git clone git@github.com:alexantr/emails.git
```

Add `config.php` to the project's root folder:

```php
<?php
return [
    'mbox_file' => '/var/mail/www-data',
    'per_page' => 10,
    'site_title' => 'Emails on example.com',
];
```

Set up a new virtual host, specifying the project's "public" folder as the document root.

Don't forget to restrict access to the site using HTTP auth, IP whitelists etc.
