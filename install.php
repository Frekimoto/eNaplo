<?php
header("Content-Type: text/html; charset=UTF-8");
if(!file_exists("config"))
    {
    echo "Nem találom a beállítófájlt: config";
    exit;
    }
ob_start();
session_start();
if(isset($_POST["Type"]))
    {
    if($_POST["UN"]=="" or $_POST["RL"]=="" or $_POST["PA"]=="" or $_POST["PAA"]=="")
        {
        echo -3;
        exit;
        }
    if($_POST["PA"]!=$_POST["PAA"])
        {
        echo -4;
        exit;
        }
    $connection=@mysql_connect($_POST["H"],$_POST["U"],$_POST["PAS"]);
    if(!$connection)die("-1");
    $database=@mysql_select_db($_POST["D"],$connection);
    if(!$database)die("-2");
    mysql_query("SET NAMES 'utf8'");
    mysql_query("SET CHARACTER SET 'utf8'");
    if(file_exists("config.php"))
        unlink("config.php");
    rename("config","config.php");
    $Prefix=$_POST["P"];
    if($Prefix!="")
        if(substr($Prefix, -1)!="_")
            $Prefix.="_";
    $str=implode("",file('config.php'));
    $fp=fopen('config.php','w');
    $str=str_replace('{HOST}',$_POST["H"],$str);
    $str=str_replace('{USERNAME}',$_POST["U"],$str);
    $str=str_replace('{PASSWORD}',$_POST["PAS"],$str);
    $str=str_replace('{DATABASE}',$_POST["D"],$str);
    $str=str_replace('{PREFIX}',$Prefix,$str);
    fwrite($fp,$str,strlen($str));
    $SQL="DROP TABLE IF EXISTS `".$Prefix."certificates`;
CREATE TABLE IF NOT EXISTS `".$Prefix."certificates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `tid` bigint(20) NOT NULL,
  `type` enum('1','2','3','4') NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  `value` int(11) NOT NULL DEFAULT '1',
  `from` int(11) NOT NULL DEFAULT '1',
  `to` int(11) NOT NULL DEFAULT '1',
  `fromdate` date NOT NULL,
  `todate` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `".$Prefix."classes`;
CREATE TABLE IF NOT EXISTS `".$Prefix."classes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `".$Prefix."gardes`;
CREATE TABLE IF NOT EXISTS `".$Prefix."gardes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `tid` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(100) NOT NULL,
  `value` enum('-','1','2','3','4','5') NOT NULL,
  `lesson` bigint(20) NOT NULL,
  `type` enum('1','2','3','4','5','6','7','8','9') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `".$Prefix."lessons`;
CREATE TABLE IF NOT EXISTS `".$Prefix."lessons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `".$Prefix."teaches`;
CREATE TABLE IF NOT EXISTS `".$Prefix."teaches` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tid` bigint(20) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `lesson` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `".$Prefix."timetable`;
CREATE TABLE IF NOT EXISTS `".$Prefix."timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `description` varchar(32) NOT NULL,
  `day` enum('1','2','3','4','5','6','7') NOT NULL DEFAULT '1',
  `number` int(11) NOT NULL,
  `from` date NOT NULL,
  `to` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `".$Prefix."users`;
CREATE TABLE IF NOT EXISTS `".$Prefix."users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `real_name` varchar(32) NOT NULL,
  `password` varchar(100) NOT NULL,
  `om_id` bigint(11) NULL DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `class` bigint(20) NOT NULL,
  `parent` bigint(20) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;";
	$SQL=explode(';',$SQL);
	foreach($SQL as $index => $sql)
		mysql_query($sql);    
   echo mysql_query("INSERT `".$Prefix."users` (`id`, `username`, `real_name`, `password`, `om_id`, `date`, `class`, `parent`, `status`) VALUES ( NULL, '".mysql_real_escape_string($_POST["UN"])."', '".mysql_real_escape_string($_POST["RL"])."', '".sha1(md5($_POST["PA"]))."', NULL, '".mysql_real_escape_string(date("Y-m-d-G-i-s"))."', '0', '0', '4')");
    $_SESSION["ID"]=mysql_result(mysql_query("SELECT * FROM `".$Prefix."users` WHERE username='".mysql_real_escape_string($_POST["UN"])."'"), 0, "id");
    exit;
    }
?>
<!DOCTYPE html>
<html lang="hu">
    <head>
        <title>e-Napló telepítő</title>
        <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
        <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
        <script type="text/javascript">
        function Doit(ERROR_DIV,OTHER_ERROR_DIV,CONTENT_DIV,OTHER_CONTENT_DIV,OTHER_CONTENT_TITLE,FORM)
            {
            $('#'+ERROR_DIV+',#'+OTHER_ERROR_DIV).html('');
            if($('#Profile_Username').val()=='' || $('#Profile_RealName').val()=='' || $('#Profile_Password').val()=='' || $('#Profile_PasswordAgain').val()=='')
                $('#'+OTHER_ERROR_DIV).html('<font color="red">Az adminisztrátor adatait megadni kötelező!</font>');
                else
                if($('#Profile_Password').val()!=$('#Profile_PasswordAgain').val())
                    $('#'+OTHER_ERROR_DIV).html('<font color="red">A jelszavak nem eggyeznek.</font>');
                    else{
					$("#"+FORM+" :input").attr("disabled", true);
                    $.post('install.php',
                            {
                                Type: 1,
                                H: $('#MySql_Host').val(),
                                U: $('#MySql_Username').val(),
                                PAS: $('#MySql_Password').val(),
                                D: $('#MySql_Database').val(),
                                P: $('#MySql_Prefix').val(),
                                UN: $('#Profile_Username').val(),
                                RL: $('#Profile_RealName').val(),
                                PA: $('#Profile_Password').val(),
                                PAA: $('#Profile_PasswordAgain').val() 
                            },
                            function(ret)
                                {
                                if(ret=='1')
                                    {
									$('#'+OTHER_CONTENT_DIV+',#'+OTHER_CONTENT_TITLE).html('');
                                    $('#'+CONTENT_DIV).html('<font color="green">Adatbázis sikeresen telepítve.<br />Átirányítás folyamatban...<br />Ha nem történne semmi kattints <a href="index.php">ide</a>.</font>');
                                    setTimeout('location.href="index.php"',5000);
                                    }else
                                    if(ret=='-1')
                                        $('#'+ERROR_DIV).html('<font color="red">Nem sikerült kapcsolódni a MySql-hez.</font>');
                                            else
                                            if(ret=='-2')
                                                $('#'+ERROR_DIV).html('<font color="red">Nem találom az adatbázist.</font>');
                                                    else
                                                    if(ret=='-3')
                                                        $('#'+OTHER_ERROR_DIV).html('<font color="red">Az adminisztrátor adatait megadni kötelező!</font>');
                                                            else
                                                            if(ret=='-4')
                                                                $('#'+OTHER_ERROR_DIV).html('<font color="red">A jelszavak nem eggyeznek.</font>');
                                                                else
                                                                $('#'+ERROR_DIV).html('<font color="red">Beazonosíthatatlan hiba.<br />Hibakód: ,,'+ret+'"</font>');
								$("#"+FORM+" :input").attr("disabled", false);
                                });
					}
            }
        </script>
    </head>
    <body>
        <center>
			<form id="Form">
				<h1>Adatbázis beállítások</h1>
					<div id="MySql_Settings">
						<div id="MySql_Error"></div>
						<label for="MySql_Host">MySql host:</label> <input type="Text" id="MySql_Host" placeholder="MySql host" /><br />
						<label for="MySql_Username">MySql felhasználónév:</label> <input type="Text" id="MySql_Username" placeholder="MySql felhasználónév" /><br />
						<label for="MySql_Password">MySql jelszó:</label> <input type="Password" id="MySql_Password" placeholder="MySql jelszó" /><br />
						<label for="MySql_Database">MySql adatbázis:</label> <input type="Text" id="MySql_Database" placeholder="MySql adatbázis" /><br />
						<label for="MySql_Prefix">MySql tábla előtag:</label> <input type="Text" id="MySql_Prefix" placeholder="MySql előtag" /><br />
					</div>
				<br />
				<h1 id="Profile_Settings_Title">Felhasználói beállítások</h1>
				<div id="Profile_Settings">
						<div id="Profile_Error"></div>
						<label for="Profile_Username">Adminisztrátor felhasználóneve:</label> <input type="Text" id="Profile_Username" placeholder="Adminisztrátor felhasználóneve" /><br />
						<label for="Profile_RealName">Adminisztrátor valódi neve:</label> <input type="Text" id="Profile_RealName" placeholder="Adminisztrátor valódi neve" /><br />
						<label for="Profile_Password">Adminisztrátor jelszava:</label> <input type="Password" id="Profile_Password" placeholder="Adminisztrátor jelszava" /><br />
						<label for="Profile_PasswordAgain">Adminisztrátor jelszava ismét:</label> <input type="Password" id="Profile_PasswordAgain" placeholder="Adminisztrátor jelszava ismét" /><br />
						<input type="Submit" value="Mehet!" onClick="Doit('MySql_Error','Profile_Error','MySql_Settings','Profile_Settings','Profile_Settings_Title','Form'); return false;"/>
				</div>
			</form>
		<br />
		<h6>Created by: <a href="http://www.t-bond.hu/" target="_blank">T-bond</a> - 2013</h6>
        </center>
    </body>
</html>
<?php ob_end_flush(); ?>
