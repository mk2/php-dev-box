# Class: nginx::service
#
#
class nginx::service {

  service { "apache2":
    enable => false,
    ensure => stopped,
  } ~>
  service { "nginx":
    enable      => true,
    ensure      => running,
    hasrestart  => true,
  }

}