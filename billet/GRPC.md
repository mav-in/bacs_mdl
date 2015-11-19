#Status

Trash

## Install grpc

**Arch:**

```sh
$ sudo pacman -S git
$ sudo pacman -S ruby
$ PATH="$PATH":~/.gem/ruby/2.2.0/bin
$ PATH="$PATH":/usr/bin/ruby
$ gem
$ mkdir ~/git
$ cd git
$ git clone https://github.com/grpc/grpc.git

$ sudo pacman -S gcc make autoconf automake
$ sudo pacman -S php php-fpm php-gd php-intl php-pear
$ sudo pacman -S re2c

$ sudo php --ini
$ sudo php -m
```

Edit `/etc/php/php.ini` uncomment so.

```sh
$ cd ~/git/grpc/src/php
$ curl -sS https://getcomposer.org/installer | php
$ sudo cp composer.phar /usr/local/bin/composer
```

Add `open_basedir = $open_basedir:/usr/local/bin/composer`

```sh
$ sudo composer install
$ PATH="$PATH":/usr/local/bin/composer
$ cd ~/git/grpc/src/php/ext/grpc

$ phpize
$ ./configure
$ make
$ sudo make install
$ cd ~/git/grpc/src/php/vendor/datto/protobuf-php
$ gem install rake ronn
```

//Bad decision:
//`pear channel-discover pear.pollinimini.net`
//`pear install drslump/Protobuf-beta`

```sh
$ rake pear:package version=1.0
$ sudo pear install Protobuf-1.0.tgz
```

##Source
[grpc/ /php]:https://github.com/grpc/grpc/tree/master/src/php