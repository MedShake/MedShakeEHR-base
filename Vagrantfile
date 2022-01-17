# This file is part of MedShakeEHR.
#
# Copyright (c) 2021
# Michaël Val 
# MedShakeEHR is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# any later version.
#
# MedShakeEHR is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.

# MedshakeEHR Vagrantfile
#
# @author Michaël Val

Vagrant.configure("2") do |config|
  config.vm.box = "debian/bullseye64"

  # Pour personnaliser sa clef ssh
  #config.ssh.insert_key = false
  #config.ssh.private_key_path = ["~/.ssh/id_rsa", "~/.vagrant.d/insecure_private_key"]
  #config.vm.provision "file", source: "~/.ssh/id_rsa.pub", destination: "~/.ssh/authorized_keys"
  
  #Pour personnaliser les spécifications de la machine
  config.vm.provider "virtualbox" do |v|
      v.memory = 256
      v.cpus = 1
    end
  config.vm.define 'msehr' do |node|
      node.vm.hostname ='msehr.local'
      node.vm.provision "ansible" do |ansible|
          #ansible.verbose = "vvv"  
          ansible.playbook = "tools/vagrant/main.yml"
      end
  config.vm.network "private_network", ip: "55.55.55.5"    
  # Pour tester Dicom et Phonecapture sur son réseau local 
  #config.vm.network "public_network"   
  end
end