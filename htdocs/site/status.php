<?php 
include ('config.inc.php');
function getinfo($host='localhost',$port=7171){
		// connects to server
        $socket = @fsockopen($host, $port, $errorCode, $errorString, 1);
		$data = '';
        // if connected then checking statistics
        if($socket)
        {
            // sets 1 second timeout for reading and writing
            stream_set_timeout($socket, 1);

            // sends packet with request
            // 06 - length of packet, 255, 255 is the comamnd identifier, 'info' is a request
            fwrite($socket, chr(6).chr(0).chr(255).chr(255).'info');

            // reads respond
			while (!feof($socket)){
				$data .= fread($socket, 128);
			}

			// closing connection to current server
			fclose($socket);
		}
	return $data;
}
if ($cfg['status_update_interval'] < 1) $cfg['status_update_interval'] = 1;
$modtime = filemtime('status.xml');
if (time() - $modtime > $cfg['status_update_interval']*60 || $modtime > time()){
	$info = getinfo($cfg['server_ip'], $cfg['server_port']);
	if (!empty($info)) file_put_contents('status.xml',$info);
}else $info = file_get_contents('status.xml');
if (!empty($info)) {
$infoXML = simplexml_load_string($info);

	$up = (int)$infoXML->serverinfo['uptime'];
	$online = (int)$infoXML->players['online'];
	$max = (int)$infoXML->players['max'];

	$h = floor($up/3600);
	$up = $up - $h*3600;
	$m = floor($up/60);
	$up = $up - $m*60;
	if ($h < 10) {$h = "0".$h;}
	if ($m < 10) {$m = "0".$m;}
	echo "<a href=\"playerson.php\"> <span class=\"f2\" style=\"color:white\">Servidor 1</span></font><br/>\n";
	echo "<font class=\"f1\" style=\"color:#0f0\"><span class=\"players\"><b>Players:</b> <b>$online/$max</b></span></font><br/>\n";
	echo "<br></a>";

	echo "<a href=\"playerson.php\"> <span class=\"f2\" style=\"color:white\">Monstros</span></font><br/>\n";
	echo "<font class=\"f1\" style=\"color:#0f0\"><span class=\"monsters\"><b>".$infoXML->monsters['total']."</b></span></font><br/>\n";
	//echo "<span class=\"uptime\">Uptime: <b>$h:$m</b></span><br/>\n";<>

	echo "<br><br></a>";

	echo "<font class=\"f3\" style=\"font-size: 18px;\"><center>Total</center>$online</font>";
} else {
	echo "<font color=\"red\"><span class=\"offline\"><b>Offline</b></font></span>\n";
}
?>