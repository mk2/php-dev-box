# Class: phpmyadmin
#
#
class phpmyadmin {

  include phpmyadmin::install
  include phpmyadmin::config

  Class["phpmyadmin::install"]
  -> Class["phpmyadmin::config"]
}
