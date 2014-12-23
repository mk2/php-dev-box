# Class: taskjp
# 日本語化
#
class taskjp {

  include taskjp::install
  include taskjp::config

  Class['taskjp::install']
  -> Class['taskjp::config']
}
