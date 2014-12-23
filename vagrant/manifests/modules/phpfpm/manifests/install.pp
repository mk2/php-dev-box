# Class: PHPFPM
#
#
class phpfpm::install (
  $buildDir = "/tmp/build",
  $runkitRepositoryUrl = "https://github.com/zenovich/runkit.git",
  $timecopRepositoryUrl = "https://github.com/hnw/php-timecop.git",
) inherits phpfpm::deps {

  include apt


  apt::source { "dotdeb_repository":
    comment     => "Dotdeb repository.",
    location    => "http://packages.dotdeb.org",
    repos       => "all",
    release     => "wheezy-php55",
    include_deb => true,
    key         => "89DF5277",
    key_server  => "keyserver.ubuntu.com",
  } ->
  package { "php5":
    ensure  => installed,
  }

###
# MySQL周り
  package { "php5-mysql":
    ensure  => installed,
    require => Package["php5"],
  }

###
# FPM周り
  package { "php5-fpm":
    ensure  => installed,
    require => Package["php5"],
  }

###
# memcache周り
  package { "memcached":
    ensure  => installed,
    require => Package["php5"],
  } ->
  package { "php5-memcache":
    ensure => installed,
  }

###
# devパッケージ周り。runkit,timecopも含む

  $runkitBuildDir = "$buildDir/runkit"
  $timecopBuildDir = "$buildDir/timecop"

  package { "php5-dev":
    ensure  => installed,
    require => Package["php5"],
  } ->
  file { "build dir":
    ensure => directory,
    path   => $buildDir,
    mode   => '0777',
  } ->
  vcsrepo { $runkitBuildDir:
    ensure   => latest,
    provider => git,
    source   => $runkitRepositoryUrl,
  } ->
  exec { "exec phpize to runkit":
    command => "phpize",
    path    => "/usr/bin:/bin:/sbin",
    cwd     => $runkitBuildDir,
  } ->
  exec { "configure runkit":
    command => "$runkitBuildDir/configure",
    cwd     => $runkitBuildDir,
  } ->
  exec { "make runkit":
    command => "make",
    path    => "/usr/bin:/bin:/sbin",
    cwd     => $runkitBuildDir,
  } ->
  exec { "make install runkit":
    command => "make install",
    path    => "/usr/bin:/bin:/sbin",
    cwd     => $runkitBuildDir,
  } ->
  vcsrepo { $timecopBuildDir:
    ensure   => latest,
    provider => git,
    source   => $timecopRepositoryUrl,
  } ->
  exec { "exec phpize to timecop":
    command => "phpize",
    path    => "/usr/bin:/bin:/sbin",
    cwd     => $timecopBuildDir,
  } ->
  exec { "configure timecop":
    command => "$timecopBuildDir/configure",
    cwd     => $timecopBuildDir,
  } ->
  exec { "make timecop":
    command => "make",
    path    => "/usr/bin:/bin:/sbin",
    cwd     => $timecopBuildDir,
  } ->
  exec { "make install timecop":
    command => "make install",
    path    => "/usr/bin:/bin:/sbin",
    cwd     => $timecopBuildDir,
  }

}
