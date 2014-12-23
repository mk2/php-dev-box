# Class: phpmyadmin::install
# 日本語化
#
class phpmyadmin::install {

  package { "phpmyadmin":
    ensure => installed,
  }

}