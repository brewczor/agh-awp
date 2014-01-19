<html>
	<body>
		<h2>Raspberry Pi OpenVPN Configurator</h2>
		<form action="openlite.php" method="post">
			<p>
			1<button type="submit" name="install" value="install">Install components</button>
			<br/>
			2<button type="submit" name="buildCert" value="buildCert">Build Certificates and Keys</button>(be patient it can take a while)
			<br/>
			3<button type="submit" name="startOpenVPN" value="startOpenVPN">Start OpenVPN</button>
			<input type="radio" name="connectionType" value="eth0">LAN (Ethernet cable)
			<input type="radio" name="connectionType" value="wlan0">WiFi (WiFi adapter)
			<br/>
			4<button type="submit" name="getKeys" value="getKeys">Download Configuration, Certificate and Keys</button>
			<input type="checkbox" name="ipmode" value="ipmode">External IP (select if you want to connect outside your network, don't forget - open port 1194 on your router)
			<br/>
			5<button type="submit" name="reboot" value="reboot">Reboot your device</button>Not necessary but recommended
			</p>
	</form>
	</body>
</html>

<?php
if (isset($_REQUEST['install'])) {
    install();
} elseif (isset($_REQUEST['buildCert'])) {
    buildCert();
} elseif (isset($_REQUEST['startOpenVPN'])) {
    startOpenVPN();
} elseif (isset($_REQUEST['getKeys'])) {
    getKeys();
} elseif (isset($_REQUEST['reboot'])) {
    reboot();
}
?>

<?php
//Short path
function install() {
	update();
	installVPN();
}

function buildCert() {
	copyVPNFiles();
	updateVARSFile();
	updateCleanAll();
	cleanAll();
	createLink();
	updatePKITool();
	buildCA();
	buildKeyServer();
	editPKITool();
	buildKeyClient();
	updateBuildDH();
	buildDH();
}

function startOpenVPN() {
if(isset($_REQUEST['connectionType'])) {
	createConfFile();
	updateIPForward();
	getIP();
	runIPTables();
	editSysctl();
	runOpenVPN();
} else {
	echo "Select connection type and repeat this step";
}
}

function getKeys() {
	createConfigurationFile();
	editRCLocal();
	copyKeys();
	downloadConfig();
}



////////////////////////
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
