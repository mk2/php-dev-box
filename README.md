# Basic PHP Dev Box with Vagrant & Puppet on Debian

## OS
Debian 7.6 Wheezy

## Puppet install modules

- PHP5.5 (via dotdeb)
- PHP5.5 devtools (via dotdeb)
- PHP5-FPM (via dotdeb)
    + run on '127.0.0.1:9000'
- Memcached
    + run on '127.0.0.1:11211'
- PHP-Memcache (via dotdeb)
- PHP-Runkit (via Github)
- PHP-Timecop (via Github)
- phpMyAdmin
    + Port: 8081
- Nginx (via Official)
- Task-Japanese (Change locale to Japanese)
- MySQL5.5
    + run on 'localhost'
    + RootUserName: root
    + RootUserPassword: <?= RootUserName ?>!

## Document Root
- /opt/htdocs
