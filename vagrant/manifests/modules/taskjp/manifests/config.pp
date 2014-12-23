# Class: taskjp::config
# 日本語化
#
class taskjp::config {

  file_line { "ja_jp":
    path    => "/etc/locale.gen",
    line    => "ja_JP.UTF-8 UTF-8",
    match   => "#*(\s)*ja_JP(\\.)UTF-8(\s)*UTF-8",
  } ->
  exec { 'locale-gen':
    command     => 'locale-gen',
    path        => '/usr/bin:/usr/sbin:/bin:/usr/local/bin',
  } ->
  exec { "update-locale":
    command => "update-locale LANG=ja_JP.UTF-8 LANGUAGE='ja_JP:ja'",
    path    => "/usr/bin:/usr/sbin:/bin:/usr/local/bin",
  }

}