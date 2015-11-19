#Status

Trash

## Arch config

**Настройка sudo**
```sh
$ pacman -S sudo
$ groupadd admins
```

useradd -m -g [initial_group] -G [additional_groups] -s [login_shell] [username]

```sh
useradd -m -G admins -s /bin/bash myusername
passwd myusername
```

```sh
nano /etc/sudoers
```
ADD:
>`%admins   ALL=(ALL) ALL`
>`#%users  ALL= NOPASSWD: /sbin/poweroff,/sbin/reboot`

**Программы**
```sh
$ sudo pacman -S mc
$ sudo pacman -S fish
$ sudo pacman -S htop
```

**Настройка доступа по SSH**
```sh
$ sudo pacman -S openssh
$ sudo systemctl start sshd
$ sudo systemctl enable sshd
```

##Source
[wiki.archlinux.org]:https://wiki.archlinux.org/index.php/Installation_guide_(%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9)
[vse-ob-ustanovke-arch-linux]:http://nikita-petrov.com/articles/vse-ob-ustanovke-arch-linux#34