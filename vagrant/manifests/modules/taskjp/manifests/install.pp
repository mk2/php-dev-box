# Class: taskjp::install
# 日本語化
#
class taskjp::install {

  package { "task-japanese":
    ensure => installed,
  }

}