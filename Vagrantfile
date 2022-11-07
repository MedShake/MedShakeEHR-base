Vagrant.configure("2") do |config|
  config.vm.box = "generic/debian11"
  config.vm.define "msehr"
  config.vm.hostname = "msehr.local"
  config.vm.network "private_network", ip: "192.168.56.4"
  config.vm.synced_folder ".", "/vagrant", type: "rsync"

  # Pour tester Phonecapture sur son réseau local 
  # config.vm.network "public_network"
  config.vm.provision "ansible" do |ansible|
      ansible.playbook = "tools/vagrant/main.yml"
  end
  # Pour personnaliser les spécifications de la machine
  config.vm.provider "virtualbox" do |v|
      v.memory = 512
      v.cpus = 2
    end
  config.vm.provider "libvirt" do |lb|
      # for ubuntu2204 1024
      lb.memory = 512
      lb.cpus = 2
    end
  # Pour personnaliser sa clef ssh
  # config.ssh.insert_key = false
  # config.ssh.private_key_path = ["~/.ssh/id_rsa", "~/.vagrant.d/insecure_private_key"]
  # config.vm.provision "file", source: "~/.ssh/id_rsa.pub", destination: "~/.ssh/authorized_keys"
  
end