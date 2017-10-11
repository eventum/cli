# Eventum CLI Application

This is new Eventum CLI Application.

## Installing ##

Download phar and start using it:

1. download `eventum.phar` from [releases page](https://github.com/eventum/cli/releases/latest)
2. start using it: `php eventum.phar`

## Updating ##

`php eventum.phar self-update`

## Developing ##

If you would like to use development code instead of pre-built PHAR file.

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

| Command               |                                       |
| --------------------- | ------------------------------------- |
| **attachment:upload** | Upload attachment to an issue         |
| **issue:close**       | Marks an issue as closed              |
| **issue:list**        | List open issues                      |
| **issue:status**      | Set Issue status                      |
| **issue:view**        | Display Issue details                 |
| **report:weekly**     | Show weekly reports                   |
| **time:spend**        | Add time-tracking entry to an issue   |
