# Class: PHPFPM
#
#
class phpfpm::service (
) inherits phpfpm::deps {

###
# php5-fpmサービス
  service { "php5-fpm":
    enable      => true,
    ensure      => running,
    hasrestart  => true,
  }

###
# memcachedサービス
  service { "memcached":
    enable      => true,
    ensure      => running,
    hasrestart  => true,
  }

}
