# PHP開発用BOX
# 入れるもの一覧
# - taskjp         : 日本語化
# - nginx          : nginxインストール & 設定
# - memcache       : メムキャッシュ
# - PHP & FPM      : PHPとFPM Debianを想定するのでdotdeb経由で入れる
# - PHP Extensions : PHP-Memcache, runkit, timecop
# - MySQL          : MySQL
node default {

# 日本語化周り
  class { 'taskjp': }

# nginx周り
  class { 'nginx': }

# PHP周り
  class { 'phpfpm': }
}
