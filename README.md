# Eventum CLI Application

This is new Eventum CLI Application.

## Installing ##

1. clone this repository
2. [get composer](https://getcomposer.org/download/)
3. install composer dependencies: `php composer.phar install`
4. start using it: `php eventum.php`

## Configuring ##

You need to specify `--url` for the first time, url and optionally login credentials will be saved to `~/.eventum.json`

```txt
$ eventum.phar --url=http://eventum.example.org wr
    Authentication required (http://eventum.example.org/rpc/xmlrpc.php):
      Username: glen
      Password: 
Do you want to store credentials for http://eventum.example.org/rpc/xmlrpc.php ? [Yn] y
Elan Ruusamäe Weekly Report 2015.04.27 - 2015.05.03
```


## Commands ##

Available commands:
 - **add-attachment**   Add attachment to issue
 - **open-issues**      List open issues
 - **view-issue**       Display Issue details
 - **weekly-report**, **wr** Show weekly reports
