# http://stackoverflow.com/questions/10845864
exec { 'apt-update':
  command => "/usr/bin/apt-get update",
  onlyif => "/bin/bash -c 'exit $(( $(( $(date +%s) - $(stat -c %Y /var/lib/apt/lists/$( ls /var/lib/apt/lists/ -tr1|tail -1 )) )) <= 604800 ))'"
}
Exec['apt-update'] -> Package <| |>

package { 'libapache2-mod-php5': ensure => 'installed' }
package { 'php-apc': ensure => 'installed' }
package { 'default-jdk': ensure => 'installed' }
package { 'php5': ensure => 'installed' }
package { 'php5-curl': ensure => 'installed'} # needed for CAS
package { 'php5-dev': ensure => 'installed' }
package { 'php-pear': ensure => 'installed' }
package { 'php5-json': ensure => 'installed' }
package { 'php5-ldap': ensure => 'installed' }
package { 'php5-mcrypt': ensure => 'installed' }
package { 'php5-mysql': ensure => 'installed' }
package { 'php5-xsl': ensure => 'installed' }
package { 'php5-intl': ensure => 'installed' }
package { 'php5-gd': ensure => 'installed' }

class { '::mysql::server': root_password => 'UNSET', }

service { 'apache2':
  ensure => 'running',
  enable => 'true',
  require => Package['libapache2-mod-php5']
}

file { '/etc/apache2/conf-enabled/vufind.conf':
  notify => Service['apache2'],
  ensure => 'link',
  target => '/usr/local/vufind/local/httpd-vufind.conf',
}

file { '/etc/apache2/mods-enabled/rewrite.load':
  notify => Service['apache2'],
  ensure => 'link',
  target => '/etc/apache2/mods-available/rewrite.load',
}

file { '/usr/local/vufind2':
  ensure => 'link',
  target => '/usr/local/vufind',
}

file { '/etc/init.d/vufind':
  ensure => 'link',
  target => '/usr/local/vufind/vufind.sh',
}

service { 'vufind':
  ensure => 'running',
  enable => 'true',
  require => [
    File['/etc/init.d/vufind'],
    File['/usr/local/vufind2'],
  ],
  hasstatus => false,
  status => 'sudo /etc/init.d/vufind check'
}

exec { '/usr/sbin/php5enmod mcrypt':
  creates => '/etc/php5/apache2/conf.d/20-mcrypt.ini',
  notify => Service['apache2'],
  require => Package['php5-mcrypt'],
}

# This is a development setup, so we want xdebug available to be enabled,
# but the module can have a significant impact on performance when enabled,
# so disable it.
package { 'php5-xdebug': ensure => 'installed' }
exec { '/usr/sbin/php5dismod xdebug':
  require => Package['php5-xdebug'],
  notify => Service['apache2'],
  onlyif => "/usr/sbin/php5query -s apache2 -m xdebug ||\
             /usr/sbin/php5query -s cli -m xdebug",
}

file { '/etc/php5/mods-available/xdebug.ini':
  ensure => present,
  require => Package['php5-xdebug'],
  notify => Service['apache2']
} ->
file_line { 'xdebug.remote_enable':
  path => '/etc/php5/mods-available/xdebug.ini',
  line => 'xdebug.remote_enable=1',
  match => '^xdebug.remote_enable*$'
} ->
file_line { 'xdebug.remote_connect_back':
  path => '/etc/php5/mods-available/xdebug.ini',
  line => 'xdebug.remote_connect_back=1',
  match => '^xdebug.remote_connect_back*$'
}