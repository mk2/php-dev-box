# Class: PHPFPM
#
#
class phpfpm::config (
  $phpModDir = "/etc/php5/mods-available",
  $fpmIniDir = "/etc/php5/fpm",
  $fpmListen = "127.0.0.1:9000"
) inherits phpfpm::deps {

###
# runkitのiniファイルを作成し、リンクも作る
  $runkitIniPath = "$phpModDir/runkit.ini"
  file { "make $runkitIniPath":
    ensure  => present,
    path    => $runkitIniPath,
    content => template("phpfpm/runkit.ini.erb"),
  } ->
  file { "$fpmIniDir/conf.d/20-runkit.ini":
    ensure => link,
    target => $runkitIniPath,
    notify => Service["php5-fpm"],
  }

###
# タイムコップのiniファイルを作成し、リンクも作る
  $timecopIniPath = "$phpModDir/timecop.ini"
  file { "make $timecopIniPath":
    ensure  => present,
    path    => $timecopIniPath,
    content => template("phpfpm/timecop.ini.erb"),
  } ->
  file { "$fpmIniDir/conf.d/20-timecop.ini":
    ensure => link,
    target => $timecopIniPath,
    notify => Service["php5-fpm"],
  }

###
# fpmを指定したポートで起動するようにする
  $fpmWWWConfPath = "$fpmIniDir/pool.d/www.conf"
  file_line { "change fpm listen":
    path    => $fpmWWWConfPath,
    line    => "listen = '$fpmListen'",
    match   => "^([\s]*listen)[\s]+=[\s]+.*",
    notify  => Service["php5-fpm"],
  }

}
