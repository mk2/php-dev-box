# Class: PHPFPM
#
#
class phpfpm::deps(
  $docRoot = "/opt/htdocs",
) {

  include ::apt

  case $::osfamily {
    'Debian': {
      notice "Debian!"
    }
    default: {
      fail("Not support os.")
    }
  }

}
