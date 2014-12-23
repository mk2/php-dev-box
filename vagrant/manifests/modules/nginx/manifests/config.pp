# Class: nginx::config
#
#
class nginx::config (
  $docRoot = "/opt/htdocs",
  $fpmListenLocation = "127.0.0.1:9000"
) {

  file { "nignx_doc_root":
    ensure => directory,
    path   => $docRoot,
    mode   => '0777',
  } ->
  file { "nginx_default_index_html":
    ensure  => present,
    path    => "$docRoot/index.html",
    content => template("nginx/index.html.erb"),
  } ->
  file { "nginx_config":
    ensure  => present,
    path    => "/etc/nginx/conf.d/default.conf",
    content => template("nginx/default.conf.erb"),
    require => [
      Package["nginx"],
    ],
  }

}