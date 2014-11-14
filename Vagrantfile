VAGRANTFILE_API_VERSION = "2"

require './.vagrant/reboot-plugin.rb'

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "archlinux-x86_64"
  config.vm.box_url = "http://cloud.terry.im/vagrant/archlinux-x86_64.box"

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--name", "symfony-demo"]
    vb.customize ["modifyvm", :id, "--memory", "1024"]
    vb.customize ["modifyvm", :id, "--cpus", "1"]
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
  end

  config.vm.synced_folder ".", "/vagrant", disabled: true
  config.vm.synced_folder "application", "/srv/http";

  config.vm.network "forwarded_port", guest: 80, host: 8080

  config.vm.provision "shell",
    path: ".vagrant/provision/shell/install-pre-reboot.sh",
    keep_color: true

  config.vm.provision "unix_reboot"

  config.vm.provision "shell",
    path: ".vagrant/provision/shell/install-post-reboot.sh",
    keep_color: true

  config.vm.provision "file",
    source: ".vagrant/provision/files/nginx.conf",
    destination: "/home/vagrant/nginx.conf"

  config.vm.provision "shell",
    path: ".vagrant/provision/shell/config.sh",
    keep_color: true

end
