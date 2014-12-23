# Class: PHPFPM
#
#
class phpfpm (
  $docRoot = "/opt/htdocs",
) inherits phpfpm::deps {

  class { "phpfpm::install": }
  class { "phpfpm::config": }
  class { "phpfpm::service": }

  Class["phpfpm::install"]
  -> Class["phpfpm::config"]
  ~> Class["phpfpm::service"]

}
