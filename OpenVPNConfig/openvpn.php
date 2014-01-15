<html>
	<body>
		<h2>Raspberry Pi OpenVPN Configurator</h2>
		<form action="openvpn.php" method="post">
			<p>
			01<button type="submit" name="update" value="update">apt-get update</button>
			02<button type="submit" name="installVPN" value="installVPN">install openvpn openssl</button>
			</p>
			<p>
			03<button type="submit" name="copyVPNFiles" value="copyVPNFiles">Restore original VPN files</button>
			04<button type="submit" name="updateVARSFile" value="updateVARSFile">Edit "vars" file</button>
			</p>
			<p>
			05<button type="submit" name="updateCleanAll" value="updateCleanAll">Add vars variables to clean-all</button>
			06<button type="submit" name="cleanAll" value="cleanAll">cleann-all (remove old key and crt)</button>
			07<button type="submit" name="createLink" value="createLink">Create link to openssl</button>
			</p>
			<p>
			08<button type="submit" name="updatePKITool" value="updatePKITool">Add vars variables to pkitool</button>
			09<button type="submit" name="buildCA" value="buildCA">build-ca OpenVPN</button>
			10<button type="submit" name="buildKeyServer" value="buildKeyServer">build-key-server server</button>
			</p>
			<p>
			11<button type="submit" name="editPKITool" value="editPKITool">update vars variable from pkitool</button>
			12<button type="submit" name="buildKeyClient" value="buildKeyClient">build-key client1</button>
			</p>
			<p>
			13<button type="submit" name="updateBuildDH" value="updateBuildDH">add vars variables to build-dh</button>	
			14<button type="submit" name="buildDH" value="buildDH">build-dh</button>(be patient)
			</p>
			<p>
			15<button type="submit" name="createConfFile" value="createConfFile">Create openvpn.conf file</button>		
			16<button type="submit" name="updateIPForward" value="updateIPForward">Update IP Forward</button>
			</p>
			<p>
			17<button type="submit" name="getIP" value="getIP">Get local IP</button>
			<input type="radio" name="connectionType" value="eth0">LAN
			<input type="radio" name="connectionType" value="wlan0">WiFi
			</p>
			<p>
			18<button type="submit" name="runIPTables" value="runIPTables">iptables</button>
			19<button type="submit" name="editSysctl" value="editSysctl">Edit sysctl.conf file</button>
			</p>
			<p>
			20<button type="submit" name="runOpenVPN" value="runOpenVPN">Start openVPN</button>
			</p>
			<p>
			21<button type="submit" name="createConfigurationFile" value="createConfigurationFile">Create newvpn.ovpn file</button>
			<input type="checkbox" name="ipmode" value="ipmode">External IP
			</p>
			<p>
			22<button type="submit" name="editRCLocal" value="editRCLocal">Edit rc.local file</button>
			23<button type="submit" name="copyKeys" value="copyKeys">Copy keys</button>
			</p>
			<p>
			24<button type="submit" name="downloadConfig" value="downloadConfig">Download client configuration files</button>
			</p>
			<p>
			25<button type="submit" name="reboot" value="reboot">Reboot Raspberry Pi</button>
			</p>
	</form>
	</body>
</html>

<?php
  if (isset($_REQUEST['update'])) {
    update();
} elseif (isset($_REQUEST['installVPN'])) {
    installVPN();
} elseif (isset($_REQUEST['copyVPNFiles'])) {
    copyVPNFiles();
} elseif (isset($_REQUEST['updateVARSFile'])) {
    updateVARSFile();
} elseif (isset($_REQUEST['updateCleanAll'])) {
    updateCleanAll();
} elseif (isset($_REQUEST['cleanAll'])) {
    cleanAll();
} elseif (isset($_REQUEST['createLink'])) {
    createLink();
} elseif (isset($_REQUEST['updatePKITool'])) {
    updatePKITool();
} elseif (isset($_REQUEST['buildCA'])) {
    buildCA();
} elseif (isset($_REQUEST['buildKeyServer'])) {
    buildKeyServer();
} elseif (isset($_REQUEST['editPKITool'])) {
    editPKITool();
} elseif (isset($_REQUEST['buildKeyClient'])) {
    buildKeyClient();
}  elseif (isset($_REQUEST['updateBuildDH'])) {
    updateBuildDH();
} elseif (isset($_REQUEST['buildDH'])) {
    buildDH();
} elseif (isset($_REQUEST['createConfFile'])) {
    createConfFile();
} elseif (isset($_REQUEST['updateIPForward'])) {
    updateIPForward();
} elseif (isset($_REQUEST['getIP'])) {
    getIP();
} elseif (isset($_REQUEST['runIPTables'])) {
    runIPTables();
} elseif (isset($_REQUEST['editSysctl'])) {
    editSysctl();
} elseif (isset($_REQUEST['runOpenVPN'])) {
    runOpenVPN();
} elseif (isset($_REQUEST['createConfigurationFile'])) {
    createConfigurationFile();
} elseif (isset($_REQUEST['editRCLocal'])) {
    editRCLocal();
} elseif (isset($_REQUEST['copyKeys'])) {
    copyKeys();
} elseif (isset($_REQUEST['downloadConfig'])) {
    downloadConfig();
} elseif (isset($_REQUEST['reboot'])) {
    reboot();
}
?>

<?php
function update() {
	executeCMD("sudo apt-get update");
}


function installVPN() {
	executeCMD("sudo apt-get -y install openvpn openssl");
}


function copyVPNFiles() {
	executeCMD("sudo rm -rf /etc/openvpn/easy-rsa");
	executeCMD("sudo cp -r -v /usr/share/doc/openvpn/examples/easy-rsa/2.0 /etc/openvpn/easy-rsa");
}


function updateVARSFile() {
	$source='/etc/openvpn/easy-rsa/vars';
	$target='/etc/openvpn/easy-rsa/varsTMP';
	
	executeCMD("sudo chmod -v 777 /etc/openvpn/easy-rsa/");

	executeCMD("sudo chmod -v 777 $source");

	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	while (!feof($sh)) {
		$line=fgets($sh);
		if (strpos($line, 'export EASY_RSA')!==false) {
			$line='export EASY_RSA="/etc/openvpn/easy-rsa"' . PHP_EOL;
		}
		fwrite($th, $line);
	}

	fclose($sh);
	fclose($th);

	
	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);
	
	executeCMD("sudo chmod -v 755 /etc/openvpn/easy-rsa/");
	
	executeCMD("sudo chmod -v 644 $source");

	executeCMD("sudo chown -v root:root $source");
	
	echo "File $source updated";
}


function updateCleanAll() {
	addVarsToScript("clean-all");
	echo "<br/>";
}


function cleanAll() {
	executeCMD("sudo /etc/openvpn/easy-rsa/clean-all");
}


function createLink() {
	executeCMD("sudo ln -s /etc/openvpn/easy-rsa/openssl-1.0.0.cnf /etc/openvpn/easy-rsa/openssl.cnf");
}


function updatePKITool() {
	addVarsToScript("pkitool");
	echo "<br/>";
}


function buildCA() {
	executeCMD("sudo /etc/openvpn/easy-rsa/pkitool --initca OpenVPN");
}


function buildKeyServer() {
	executeCMD("sudo /etc/openvpn/easy-rsa/pkitool --server server");
}


function editPKITool() {
	$source='/etc/openvpn/easy-rsa/pkitool';
	$target='/etc/openvpn/easy-rsa/pkitoolTMP';
	
	executeCMD("sudo chmod -v 777 /etc/openvpn/easy-rsa/");
	
	executeCMD("sudo chmod -v 777 $source");
	
	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	while (!feof($sh)) {
		$line=fgets($sh);
		if (strpos($line, 'export KEY_CN')!==false) {
			$line='export KEY_CN=someuniqueclientcn' . PHP_EOL;
		}
		fwrite($th, $line);
	}

	fclose($sh);
	fclose($th);

	
	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);
	
	executeCMD("sudo chmod -v 755 /etc/openvpn/easy-rsa/");
	
	executeCMD("sudo chmod -v 755 $source");
	
	executeCMD("sudo chown -v root:root $source");
		
	echo "File $source edited";
}


function buildKeyClient() {
	executeCMD("sudo /etc/openvpn/easy-rsa/pkitool client1");
}


function updateBuildDH() {
	addVarsToScript("build-dh");
	echo "<br/>";
}


function buildDH() {
	executeCMD("sudo /etc/openvpn/easy-rsa/build-dh");
}


function createConfFile() {
	$target='/etc/openvpn/openvpn.conf';
	
	shell_exec("sudo rm $target");
	
	executeCMD("sudo chmod -v 777 /etc/openvpn/");
	
	$th=fopen($target, 'w');
	fwrite($th,"dev tun\n");
	fwrite($th,"proto udp\n");
	fwrite($th,"port 1194\n");
	fwrite($th,"ca /etc/openvpn/easy-rsa/keys/ca.crt\n");
	fwrite($th,"cert /etc/openvpn/easy-rsa/keys/server.crt\n");
	fwrite($th,"key /etc/openvpn/easy-rsa/keys/server.key\n");
	fwrite($th,"dh /etc/openvpn/easy-rsa/keys/dh1024.pem\n");
	fwrite($th,"user nobody\n");
	fwrite($th,"group nogroup\n");
	fwrite($th,"server 10.8.0.0 255.255.255.0\n");
	fwrite($th,"persist-key\n");
	fwrite($th,"persist-tun\n");
	fwrite($th,"status /var/log/openvpn-status.log\n");
	fwrite($th,"verb 3\n");
	fwrite($th,"client-to-client\n");
	fwrite($th,'push "redirect-gateway def1"'."\n");
	fwrite($th,"#set the dns servers\n");
	fwrite($th,'push "dhcp-option DNS 8.8.8.8"'."\n");
	fwrite($th,'push "dhcp-option DNS 8.8.4.4"'."\n");
	fwrite($th,"log-append /var/log/openvpn\n");
	fwrite($th,"comp-lzo\n");
	
	fclose($th);
	
	executeCMD("sudo chmod -v 755 /etc/openvpn/");
	executeCMD("sudo chmod -v 755 $target");
	executeCMD("sudo chown -v root:root $target");
}


function updateIPForward() {
	executeCMD("sudo echo 1 > /proc/sys/net/ipv4/ip_forward");
}


function getIP() {
	echo "getIP"."<br/>";
	
	if(isset($_REQUEST['connectionType'])) {
		$connType = $_POST["connectionType"];
		
		$expire=time()+60*60;
		setcookie("connType", $connType, $expire);
		
		$IPNum = shell_exec("/sbin/ifconfig $connType | grep 'inet addr:' | cut -d: -f2 | awk '{print $1}'");
		setcookie("IPNum", $IPNum, $expire);

		echo "Your IP: "."$connType "."$IPNum"."<br/>";
	} else {
		echo "Select connection type";
	}
}


function runIPTables() {
	$IPNum = $_COOKIE["IPNum"];
	$connType = $_COOKIE["connType"];
	
	executeCMD("sudo iptables -t nat -A POSTROUTING -s 10.8.0.0/24 -o $connType -j SNAT --to $IPNum");
}


function editSysctl() {
	$source='/etc/sysctl.conf';
	$target='/etc/sysctlTMP.conf';
	
	executeCMD("sudo chmod -v 777 /etc/");

	executeCMD("sudo chmod -v 777 $source");


	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	while (!feof($sh)) {
		$line=fgets($sh);
		if (strpos($line, '#net.ipv4.ip_forward=1')!==false) {
			$line='net.ipv4.ip_forward=1' . PHP_EOL;
		}
		fwrite($th, $line);
	}

	fclose($sh);
	fclose($th);

	
	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);
	
	executeCMD("sudo chmod -v 755 /etc/");

	executeCMD("sudo chmod -v 644 $source");

	executeCMD("sudo chown -v root:root $source");
	
	echo "File $source changed";
}


function runOpenVPN() {
	executeCMD("sudo /etc/init.d/openvpn start");
}


function createConfigurationFile() {
	if (isset($_REQUEST['ipmode'])) {
		$IPNum = shell_exec("wget http://ipecho.net/plain -O - -q");
	} else {
		$IPNum = $_COOKIE["IPNum"];
	}

	echo $IPNum."<br/>";

	$target='/etc/openvpn/newvpn.ovpn';
	
	shell_exec("sudo rm $target");
	
	executeCMD("sudo chmod -v 777 /etc/openvpn/");
	
	$th=fopen($target, 'w');

	fwrite($th,"dev tun\n");
	fwrite($th,"client\n");
	fwrite($th,"proto udp\n");
	fwrite($th,"remote $IPNum 1194\n");
	fwrite($th,"resolv-retry infinite\n");
	fwrite($th,"nobind\n");
	fwrite($th,"persist-key\n");
	fwrite($th,"persist-tun\n");
	fwrite($th,"ca ca.crt\n");
	fwrite($th,"cert client1.crt\n");
	fwrite($th,"key client1.key\n");
	fwrite($th,"comp-lzo\n");
	fwrite($th,"verb 3\n");

	fclose($th);
	
	executeCMD("sudo chmod -v 755 /etc/openvpn/");

	executeCMD("sudo chmod -v 755 $target");

	executeCMD("sudo chown -v root:root $target");
}


function editRCLocal() {
	$IPNum = $_COOKIE["IPNum"];
	$connType = $_COOKIE["connType"];
	
	$source='/etc/rc.local';
	$target='/etc/rc.localTMP';
	
	$backup='/etc/rc.localORG';
	
	if(file_exists($backup)) {
		executeCMD("sudo cp $backup $source");
	}
	
	executeCMD("sudo chmod -v 777 /etc/");

	executeCMD("sudo chmod -v 777 $source");

	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	$bc=fopen($backup, 'w');
	while (!feof($sh)) {
		$line=fgets($sh);
		if (strpos($line, 'exit 0')!==false) {
			fwrite($th, "iptables -t nat -A INPUT -i $connType -p udp -m udp --dport 1194 -j ACCEPT\n");
			fwrite($th, "iptables -t nat -A POSTROUTING -s 10.8.0.0/24 -o $connType -j SNAT --to-source $IPNum\n");
		}
		fwrite($th, $line);
		fwrite($bc, $line);
	}

	fclose($sh);
	fclose($th);
	fclose($bc);

	
	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);
	
	executeCMD("sudo chmod -v 755 /etc/");
	
	executeCMD("sudo chmod -v 755 $source");

	executeCMD("sudo chown -v root:root $source");
	
	echo "File $source updated";
}

function copyKeys() {
	executeCMD("sudo cp -rf -v /etc/openvpn/easy-rsa/keys /etc/openvpn");
}


function downloadConfig() {
	executeCMD("sudo cp -v /etc/openvpn/keys/ca.crt /tmp/ca.crt");
	executeCMD("sudo cp -v /etc/openvpn/keys/client1.crt /tmp/client1.crt");
	executeCMD("sudo cp -v /etc/openvpn/keys/client1.key /tmp/client1.key");
	executeCMD("sudo cp -v /etc/openvpn/newvpn.ovpn /tmp/newvpn.ovpn");
	
	executeCMD("sudo tar -zcvf /etc/openvpn/config.tar.gz /tmp/ca.crt /tmp/client1.crt /tmp/client1.key /tmp/newvpn.ovpn");

	$file = '/etc/openvpn/config.tar.gz';

	if (file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		readfile($file);
		exit;
	}
}

function reboot() {
	executeCMD("sudo reboot");
}


//===== UTILS
function executeCMD($cmd) {
	$output = shell_exec($cmd." 2>&1");
	echo $output;
	echo "<br/>";
}


function addVarsToScript($fileName) {
	$source='/etc/openvpn/easy-rsa/'.$fileName;
	$target=$source."TMP";
	
	$varsFile='/etc/openvpn/easy-rsa/vars';
	
	$output = shell_exec("sudo chmod -v 777 /etc/openvpn/easy-rsa/");
	echo $output;
	echo "<br/>";
	$output = shell_exec("sudo chmod -v 777 $source");
	echo $output;
	echo "<br/>";
	

	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	$vs=fopen($varsFile, 'r');
	while (!feof($sh)) {
		$line=fgets($sh);

		fwrite($th, $line);
		if (strpos($line, '#!/bin/sh')!==false) {
			while(!feof($vs)) {
				fwrite($th,fgets($vs));
			}
		}
	}

	fclose($sh);
	fclose($th);
	fclose($vs);

	
	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);
	
	$output = shell_exec("sudo chmod -v 755 /etc/openvpn/easy-rsa/");
	echo $output;
	echo "<br/>";
	$output = shell_exec("sudo chmod -v 755 $source");
	echo $output;
	echo "<br/>";
	$output = shell_exec("sudo chown -v root:root $source");
	echo $output;
	echo "<br/>";
	
	echo "File $source updated";
	echo "<br/>";
}
?>
