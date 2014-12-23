# PHP開発用BOX
# 入れるもの一覧
# - taskjp         : 日本語化
# - nginx          : nginxインストール & 設定
# - memcache       : メムキャッシュ
# - PHP & FPM      : PHPとFPM Debianを想定するのでdotdeb経由で入れる
# - PHP Extensions : PHP-Memcache, runkit, timecop
# - MySQL          : MySQL
node default {

  $mysqlRootPassword = "root!"

###
# 日本語化周り
  class { "taskjp": }

###
# nginx周り
  class { "nginx": }

###
# PHP周り
  class { "phpfpm": }

###
# MySQL周り
  class { "::mysql::server":
    root_password    => $mysqlRootPassword,
  } ->
  mysql_user { "kitaro@localhost":
    ensure                   => "present",
    max_connections_per_hour => "0",
    max_queries_per_hour     => "0",
    max_updates_per_hour     => "0",
    max_user_connections     => "0",
    password_hash            => '*746EF78849114C26119970A7F2CF67414FEDE68C', # kitaro!
  } ->
  mysql_grant { "kitaro@localhost/*.*":
    ensure                   => "present",
    options                  => ["GRANT"],
    privileges               => ["ALL"],
    table                    => "*.*",
    user                     => "kitaro@localhost",
  } ->
  mysql_user { "phpmyadmin@localhost":
    ensure                   => "present",
    max_connections_per_hour => "0",
    max_queries_per_hour     => "0",
    max_updates_per_hour     => "0",
    max_user_connections     => "0",
    password_hash            => '*FD582D1FDA9F93178D9966161461C204CF61BCBC', # phpmyadmin!
  } ->
  mysql_grant { "phpmyadmin@localhost/*.*":
    ensure                   => "present",
    options                  => ["GRANT"],
    privileges               => ["ALL"],
    table                    => "*.*",
    user                     => "phpmyadmin@localhost",
  } ->
  class { "phpmyadmin": }
}
