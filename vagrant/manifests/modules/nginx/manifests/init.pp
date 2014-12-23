# Class: nginx
#
#
class nginx (
  $docRoot = "/opt/htdocs",
) {

  class { 'nginx::install': }
  class { 'nginx::config':
    docRoot => $docRoot
  }
  class { 'nginx::service': }

  Class["nginx::install"]
  -> Class["nginx::config"]
  ~> Class["nginx::service"]
}
