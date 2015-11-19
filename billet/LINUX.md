#Status

Trash

## Preparing

**Arch:**

```sh
$ killall dhcpcd
$ dhcpcd
$ ping ya.ru -c 4
```

```sh
$ cfdisk /dev/sda
```

```sh
$ mkfs.ext4 /dev/sda
$ mkswap /dev/sda1
$ swapon /dev/sda1
$ mount /dev/sda2 /mnt
$ pacstrap /mnt base
$ genfstab -p /mnt >> /mnt/etc/fstab
```

```sh
$ arch-chroot /mnt
$ echo bacs_mdl > /etc/hostname
```

```sh
$ ln -sf /usr/share/zoneinfo/Europe/Samara /etc/localtime
$ locale-gen
$ echo LANG="ru_RU.UTF-8" > /etc/locale.conf
```

```sh
$ mcedit /etc/vconsole.conf
```
ADD:
>`KEYMAP=ru`
>`FONT=cyr-sun16`
>`CONSOLEFONT=cyr-sun16`
>`FONT_MAP=`

```sh
$ hwclock --systohc --utc
```

```sh
$ mcedit /etc/locale.gen
```
ADD:
>`ru_RU.UTF-8 UTF-8`
>`en_US.UTF-8 UTF-8`

```sh
$ locale-gen
```

```sh
$ passwd
```

```sh
$ pacman -Syu
$ pacman -S grub
$ grub-install --recheck /dev/sda
$ grub-mkconfig -o /boot/grub/grub.cfg
$ umount /mnt
$ reboot
```

```sh
$ dhcpcd eth0
$ systemctl start dhcpcd
$ systemctl enable dhcpcd
```

**Лешино зеркало**
```sh
$ nano /etc/pacman.conf
```
ADD:
>`[mirror.cs.istu.ru]`
>`SigLevel = Optional`
>`Server = http://mirror.cs.istu.ru/archlinux/$arch`

```sh
$ pacman -Syu
```

##Source
[wiki.archlinux.org]:https://wiki.archlinux.org/index.php/Installation_guide_(%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9)
[vse-ob-ustanovke-arch-linux]:http://nikita-petrov.com/articles/vse-ob-ustanovke-arch-linux#34