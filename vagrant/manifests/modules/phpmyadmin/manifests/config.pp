# Class: phpmyadmin::config
#
#
class phpmyadmin::config {

  exec { "gunzip create_tables.sql":
    command => "gunzip /usr/share/doc/phpmyadmin/examples/create_tables.sql.gz",
    path => "/usr/bin:/bin",
  } ->
  exec { "create phpmyadmin config db":
    command => "mysql -u root -proot! < /usr/share/doc/phpmyadmin/examples/create_tables.sql",
    path    => "/usr/bin:/bin",
  }

  file { "make config-db.php":
    ensure  => file,
    path    => "/etc/phpmyadmin/config-db.php",
    content => template("phpmyadmin/config-db.php.erb"),
  }

}