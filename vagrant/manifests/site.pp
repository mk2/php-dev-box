# PHP開発用BOX
# 入れるもの一覧
# - taskjp         : 日本語化
# - nginx          : nginxインストール & 設定
# - memcache       : メムキャッシュ
# - PHP & FPM      : PHPとFPM Debianを想定するのでdotdeb経由で入れる
# - PHP Extensions : PHP-Memcache, runkit, timecop
# - MySQL          : MySQL
node default {

  stage { "last": }
  Stage["main"] -> Stage["last"]

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

  $override_options = {
    "mysqld" => {
      "bind-address" => "0.0.0.0",
    }
  }

  class { "::mysql::server":
    root_password    => "root!",
    override_options => $override_options,
    restart          => true,
  }  ->
  mysql_user { "root@%":
    ensure                   => "present",
    max_connections_per_hour => "0",
    max_queries_per_hour     => "0",
    max_updates_per_hour     => "0",
    max_user_connections     => "0",
    password_hash            => '*3CC11D4B8A2DCD31D660959772068DE9943DA12D', # root!
  } ->
  mysql_grant { "root@%/*.*":
    ensure                   => "present",
    options                  => ["GRANT"],
    privileges               => ["ALL"],
    table                    => "*.*",
    user                     => "root@%",
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
  }

###
# phpmyadmin。mysqlやphpに依存するので、最後にインストールさせる
  class { "phpmyadmin":
    stage => "last",
  }
}
