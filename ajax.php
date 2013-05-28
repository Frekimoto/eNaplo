<?php
header("Content-Type: text/html; charset=UTF-8");
ob_start();
session_start();
if(file_exists("config.php"))
    include_once("config.php");
        else{
        echo "Hiba a folyamatban!";
        exit;
        }
if(isset($_GET["print"])) {
if($_SESSION["ID"]==-1) {
	echo "Hiba!";
	exit;
}
if($_SESSION["RANK"]!=4 and $_SESSION["RANK"]!=3)die("Csalunk? Csalunk? Nincs hozzá jogod!");

if(!isset($_GET["id"]) or !isset($_GET["date"]))die("Paraméterhiba!");

$id=mysql_real_escape_string($_GET["id"]);

if(!mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='$id'")))die('Hiba! Nemlétező felhasználó.');

if(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$id."'"), 0, "status")!=1)die("Csak tanuló jogokkal rendelkező felhasználó nyomtatható!");

if(!strtotime($_GET["date"]."-1-1"))
	$date=strtotime(date("Y")."-1-1");
		else
		$date=strtotime($_GET["date"]."-1-1");
$LESSONS=array();
$ADAT=mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$id."'");
while($row=mysql_fetch_array($ADAT))
	if(!in_array($row["lesson"],$LESSONS))
		if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$id."' AND lesson='".$row["lesson"]."' AND type='".(isset($_GET["firsthalf"])?"8":"9")."'")))
			array_push($LESSONS,$row["lesson"]);
				else
				die('Hiányzó érdemjegy! (óra:diák|'.$row["lesson"].':'.$id.')');
sort($LESSONS);
$A=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_DELAY_TABLE WHERE uid='".$id."' AND (type='2' OR type='3')"));
$B=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_DELAY_TABLE WHERE uid='".$id."' AND (type='1' OR type='4')"));
if(isset($_GET["a"]))
	$A=(int)$_GET["a"];
if(isset($_GET["b"]))
	$B=(int)$_GET["b"];
?>
<html>
	<head>
		<title>Profil nyomtatása</title>
	</head>
	<body>
		<h3><?php echo (isset($_GET["firsthalf"])?"Félévi bizonyítvány":"Bizonyítvány")." ".date("Y",$date)."-".date('Y',strtotime("+1 year",$date)); ?></h3>
		<table>
			<tr>
				<td><b>Tanuló neve:</b></td>
				<td style="text-align:Right"><?php echo mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$id."'"), 0, "real_name"); ?></td>
			</tr>
			<tr>
				<td><b>Osztály:</b></td>
				<td style="text-align:Right"><?php echo !mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$id."'"), 0, "class")."'"))?"Nincs osztály megadva":mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$id."'"), 0, "class")."'"), 0, "name"); ?></td>
			</tr>
			<tr>
				<td><b>OM azonosító:</b></td>
				<td style="text-align:Right"><?php $OM=(string)mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$id."'"), 0, "om_id"); echo (strlen($OM)==11 and $OM[0]=="7")?$OM:"Nincs OM azonosító megadva"; ?></td>
			</tr>
		</table><br />
		<table>
			<tr>
				<td><b>Tantárgy</b></td>
				<td style="text-align:Right"><b>Érdemjegy</b></td>
				<?php
				foreach($LESSONS as $text)
					if($text!="")
						echo "<tr><td>".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".$text."'"), 0, "name")."</td><td style=\"text-align:Right\">".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$id."' AND lesson='".$text."' AND type='".(isset($_GET["firsthalf"])?"8":"9")."'"), 0, "description")."</td></tr>";
				?>
			</tr>
		</table><br />
		<table>
			<tr>
				<td colspan="3"><b>Hiányzások</b></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>Igazolt</td>
				<td>Igazolatlan</td>
				<td><b>Összesen</b></td>
			</tr>
			<tr>
				<td style="text-align:Center"><?php echo $A; ?></td>
				<td style="text-align:Center"><?php echo $B; ?></td>
				<td style="text-align:Center"><i><?php echo $A+$B; ?></i></td>
			</tr>
		</table>
		<?php echo date("Y-m-d"); ?>
		<br /><hr /><br />
		<table>
			<tr>
				<td width="50%">osztályfőnök</td>
				<td width="50%">szülő, gondviselő</td>
			</tr>
		</table>
	</body>
</html>
	<?php exit;
}
if(!isset($_POST["TYPE"]) or $_POST["TYPE"]=="")
    die("Paraméterhiba!");

switch((int)$_POST["TYPE"])
    {
    case 1://Login
    if(isset($_POST["U"]) and isset($_POST["P"]) and $_SESSION["ID"]==-1)
        if($_POST["U"]=="" or $_POST["P"]=="")
            echo 2;
                else{
                $row=@mysql_fetch_array(@mysql_query("SELECT * FROM `$_SYSTEM_USERS_TABLE` WHERE `username` = '".mysql_real_escape_string($_POST['U'])."' AND `password` = '".sha1(md5($_POST['P']))."'"));
                @mysql_free_result($row);
                if(!empty($row['id']))
                    {
                    $_SESSION["ID"]=$row["id"];
                    echo 1;
                        }else
                        echo 3;
                    }
        break;
    case 2: //Gardes table
        if($_SESSION["ID"]==-1)
            die("Hiba!");
        if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE parent='".$_SESSION["ID"]."'"))==0 and $_SESSION["RANK"]==2)
            die("<h5>Még nincs gyermek megadva.</h5>");
        if($_SESSION["RANK"]==2)
            {
            $ID=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE parent='".$_SESSION["ID"]."'"), 0, "id");
            if(isset($_POST["Type"]))
                if(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($_POST["Type"])."'"), 0, "parent"))
                    $ID=$_POST["Type"];
            }else
            $ID=$_SESSION["ID"];
        $HEAD=array("Órák");
        $LESSONS=array();
        $GARDES=array();
        $ADAT=mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$ID."' AND type!='8' AND type!='9' ORDER BY date ASC");
        while($row=mysql_fetch_array($ADAT))
            {
            $row["tid"]=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$row["tid"]."'"), 0, "real_name");
            array_push($GARDES,$row);
            if(!in_array($row["lesson"],$LESSONS))
                array_push($LESSONS,$row["lesson"]);
            $row["date"]=iconv('iso-8859-2','utf-8',strftime("%B",strtotime($row["date"])));
            if(!in_array($row["date"],$HEAD))
                array_push($HEAD,$row["date"]);
            }
        sort($LESSONS);
        if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE parent='".$_SESSION["ID"]."'"))>1)
            {
            echo '<select onChange="$.post(\'ajax.php\',{TYPE: 2, Type: $(\':selected\',this).val()},function(data){$(\'#Content\').html(data);});"><optgroup label="Válassz gyermeket!">';
            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE parent='".$_SESSION["ID"]."' ORDER BY real_name ASC");
            while($row=mysql_fetch_array($ADAT))
                echo '<option value="'.$row["id"].'"'.($row["id"]==$ID?" SELECTED":"").'>'.$row["real_name"].'</option>';
            echo '</optgroup></select>';
            }
        if(count($HEAD)==1)
            die("<h5>Nincs még érdemjegy.</h5>");
        $HALF=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$ID."' AND type='8'"));
        $END=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$ID."' AND type='9'"));
        ?>
        <table border="1" align="center">
            <tr>
                <?php
                foreach($HEAD as $text)
                    echo "<td>".ucfirst($text)."</td>";
                ?>
                <td>Átlag</td>
                <?php
                if($HALF!=0 or $END!=0)
                    echo ($HALF==0?"":"<td>Félév</td>").($END==0?"":"<td>Év vége</td>");
                ?>
            </tr>
        <?php
        foreach($LESSONS as $text)
            if($text!="")
                {
                echo "<tr><td>".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($text)."'"), 0, "name")."</td>";
                $k=array();
                $n=0;
                $s=0;
                for($i=1; $i<count($HEAD); $i++)
                    {
                    $w="";
                    echo "<td>";
                    foreach($GARDES as $text2)
                        {
                        if($w=="" and !in_array(date("m",strtotime($text2["date"])),$k))
                            {
                            $w=date("m",strtotime($text2["date"]));
                            array_push($k,$w);
                            }
                        if($text2["lesson"]==$text and date("m",strtotime($text2["date"]))==$w)
                            {
                            echo " <a href='javascript:void(0);' style='color: ".($text2["type"]==1?"green":($text2["type"]==3?"blue":($text2["type"]==4?"red":($text2["type"]==5?"gray":($text2["type"]==6?"yellow":($text2["type"]==7?"brown":"black"))))))."; text-decoration: none;' title='".$text2["date"]." (".$text2["tid"].")".($text2["description"]!=""?" - ":"").$text2["description"]."'>".$text2["value"]."</a>";
                            if($text2["value"]!="-")
                                {
                                $n+=$text2["value"];
                                $s++;
                                if($text2["type"]==4)
                                    {
                                    $n+=$text2["value"];
                                    $s++;
                                    }
                                }
                            }
                        }
                    echo "</td>";
                    }
                echo "<td>".sprintf("%01.2f", $n/$s)." ~(".round($n/$s).")</td>";
                echo ($HALF==0?"":"<td>".(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE lesson='".mysql_real_escape_string($text)."' AND uid='".$ID."' AND type='8'"))==1?mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE lesson='".mysql_real_escape_string($text)."' AND uid='".$ID."' AND type='8'"), 0, "description"):"-")."</td>").($END==0?"":"<td>".(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE lesson='".mysql_real_escape_string($text)."' AND uid='".$ID."' AND type='9'"))==1?mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE lesson='".mysql_real_escape_string($text)."' AND uid='".$ID."' AND type='9'"), 0, "description"):"-")."</td>");
                echo "</tr>";
                }
        ?>
        </table>
        <?php
        break;
    case 3: //Gardes editor
        if($_SESSION["ID"]==-1)
            die("Hiba!");
        if($_SESSION["RANK"]!=3 and $_SESSION["RANK"]!=4)
            die("Csalunk? Csalunk? Nincs hozzá jogod!");
        if(!isset($_POST["Id"]))
            echo -1;
            else{
            $ID=mysql_real_escape_string($_POST["Id"]);
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1' AND id='".$ID."'"))==0)
                echo -2;
                    else{
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE class='".$ID."' AND status='1'"))==0)
                        die("<h5>Ebbe az osztályba egy tanuló sem jár.</h5>");
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE, $_SYSTEM_USERS_TABLE WHERE $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND class='".$ID."'"))==0)
						die("<h5>Ebben az osztályban még senki nem tanul semmit.</h5>");
                    if(isset($_POST["Type"]) and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE enabled='1' AND id='".mysql_real_escape_string($_POST["Type"])."'"))>0)
                        $LESSON=$_POST["Type"];
                            else
                        $LESSON=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE, $_SYSTEM_USERS_TABLE WHERE $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND class='".$ID."'"), 0, "lesson");
                    $HEAD=array();
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE".($_SESSION["RANK"]==3?" WHERE tid='".$_SESSION["ID"]."'":""));
                    while($row=mysql_fetch_array($ADAT))
                        if(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".$row["lesson"]."'"), 0, "enabled")) {
                        $text=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".$row["lesson"]."'"), 0, "id");
                        $text2=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".$row["lesson"]."'"), 0, "name");
                        if(!in_array($text,$HEAD))
                            $HEAD[$text]=$text2;
                        }
                    if(count($HEAD)>1)
                        {
                        echo '<select onChange="$.post(\'ajax.php\',{TYPE: 3, Id: \''.$ID.'\', Type: $(\'option:selected\',this).val()},function(data){$(\'#Gardes\').html(data);});"><optgroup label="Válassz tantárgyat">';
                        foreach($HEAD as $text=>$text2)
                            echo "<option value='".$text."'".($text==$LESSON?" SELECTED":"").">".$text2." (".mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE, $_SYSTEM_USERS_TABLE WHERE $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND class='".$ID."' AND lesson='".mysql_real_escape_string($text)."'"))." fő)</option>";
                        echo "</optgroup></select>";
                        }
                    $HEAD=array();
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE lesson='".$LESSON."' AND type!='8' AND type!='9' AND date>'".date("Y-m-d",$_FROM_DATE)."' AND date<'".date("Y-m-d",$_TO_DATE)."' AND date!='0000-00-00' ORDER BY date ASC");
                    while($row=mysql_fetch_array($ADAT))
                        if(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$row["uid"]."'"), 0, "class")==$ID)
                            {
                            $row["date"]=iconv('iso-8859-2','utf-8',strftime("%B",strtotime($row["date"])));
                            if(!in_array($row["date"],$HEAD))
                                array_push($HEAD,$row["date"]);
                            }
					$date=date("Y",$_FROM_DATE);
                    ?>
                    <form id="NewGardesForm">
                    <table border="1" align="center">
                        <tr>
                            <td>Tanulók</td>
                            <?php
                            if(count($HEAD)==0)
                                echo "<td rowspan='100%'>Nincs érdemjegy</td>";
                                else
                                foreach($HEAD as $text)
                                    echo "<td>".ucfirst($text)."</td>";
							$HALF=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE, $_SYSTEM_USERS_TABLE, $_SYSTEM_GARDES_TABLE WHERE $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_GARDES_TABLE.uid AND class='".$ID."' AND $_SYSTEM_GARDES_TABLE.lesson='".$LESSON."' AND type='8'"));
                            $END=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE, $_SYSTEM_USERS_TABLE, $_SYSTEM_GARDES_TABLE WHERE $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_GARDES_TABLE.uid AND class='".$ID."' AND $_SYSTEM_GARDES_TABLE.lesson='".$LESSON."' AND class='".$ID."' AND type='9'"));
                            if($_SESSION["RANK"]==3 or $_SESSION["RANK"]==4) {
                                $HALF+=1;
                                $END+=1;
                            }
                            if($HALF!=0 or $END!=0)
                                echo '<td rowspan="100%" width="10px"></td>'.($HALF==0?"":"<td>Félév</td>").($END==0?"":"<td>Év vége</td>");
                            ?>
                            <td rowspan="100%" width="10px"></td>
                            <td class="EditTd">
                                <select onChange="$('.Type').val($(this).val());">
                                    <optgroup label="Tipus">
                                        <option value='1'>kis jegy</option>
                                        <option value='2'>normál jegy</option>
                                        <option value='3'>dolgozat</option>
                                        <option value='4'>témazáró</option>
                                        <option value='5'>vizsgajegy</option>
                                        <option value='6'>órai munka</option>
                                        <option value='7'>gyakorlati jegy</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="EditTd">
                                <select onChange="$('.Value').val($(this).val());">
                                    <optgroup label="Érték">
                                        <option value=''> </option>
                                        <option value='-'>-</option>
                                        <option value='1'>1</option>
                                        <option value='2'>2</option>
                                        <option value='3'>3</option>
                                        <option value='4'>4</option>
                                        <option value='5'>5</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="EditTd"><input type="Text" placeholder="Leírás" onKeyUp="$('.Description').val($(this).val());"/></td>
                            <td class="EditTd"><input type="Date" class="Date" min="<?php echo date("Y-m-d",$_FROM_DATE); ?>" max="<?php echo date("Y-m-d",$_TO_DATE); ?>" placeholder="Dátum (ÉÉÉÉ-HH-NN)" value="<?php echo date("Y-m-d"); ?>" onChange="$('.Date').val($(this).val());"/></td>
                            <td>Átlag</td>
                        </tr>
                    <?php
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE WHERE".($_SESSION["RANK"]==3?" tid='".$_SESSION["ID"]."' AND":"")." lesson='".$LESSON."'");
                    while($row=mysql_fetch_array($ADAT))
                        if(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$row["uid"]."'"), 0, "class")==$ID)
                            {
                            $s=0; $n=0;
                            echo "<tr><td>".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$row["uid"]."'"), 0, "real_name").(($_SESSION["RANK"]==3 or $_SESSION["RANK"]==4)?(($HALF-1>0?(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='8'"))?"<sup><a href='#' onClick='a=prompt(\"Igazolt órák száma:\"); b=prompt(\"Igazolatlan órák száma:\"); c=\"ajax.php?id=".$row["uid"]."&date=".$date."&firsthalf&print\"; if(a!=\"\")c+=\"&a=\"+a; if(b!=\"\")c+=\"&b=\"+b; $(this).attr(\"href\",c); return 0;' class='Print'>[1]</a></sup>":""):"").($END-1>0?(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='9'"))?"<sup><a href='ajax.php?id=".$row["uid"]."&date=".$date."&print' class='Print'>[2]</a></sup>":""):"")):"")."</td>";
                            $GARDES=array();
                            $ADAT2=mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type!='8' AND type!='9' ORDER BY date ASC");
                            while($row2=mysql_fetch_array($ADAT2))
                                {
                                $row2["tid"]=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$row2["tid"]."'"), 0, "real_name");
                                array_push($GARDES,$row2);
                                }
                            foreach($HEAD as $text)
                                {
                                echo "<td>";
                                foreach($GARDES as $text2)
                                if(iconv('iso-8859-2','utf-8',strftime("%B",strtotime($text2["date"])))==$text and $text2["type"]!=8 and $text2["type"]!=9)
                                        {
                                        echo " <a href='javascript:void(0);' onClick=".'"$.post(\'ajax.php\',{TYPE: 14, Id: \''.$text2["id"].'\'},function(data){$(\'#EditGarde\').html(data);});"'." style='color: ".($text2["type"]==1?"green":($text2["type"]==3?"blue":($text2["type"]==4?"red":($text2["type"]==5?"gray":($text2["type"]==6?"yellow":($text2["type"]==7?"brown":"black"))))))."; text-decoration: none;' title='".$text2["date"]." (".$text2["tid"].")".($text2["description"]!=""?" - ":"").$text2["description"]."'>".$text2["value"]."</a>";
                                        if($text2["value"]!="-")
                                            {
                                            $n+=$text2["value"];
                                            $s++;
                                            if($text2["type"]==4)
                                                {
                                                $n+=$text2["value"];
                                                $s++;
                                                }
                                            }
                                        }
                                if($s==0)
                                    echo "(-)";
                                echo "</td>";
                                }
                            $NUMBER=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type!='8' AND type!='9'"))>1?1:0;
                            echo ($HALF==0?"":"<td>".(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='8'"))==1?(($NUMBER?'<a href="javascript:void(0);" style="text-decoration: none;" onClick="$.post(\'ajax.php\',{TYPE: 14, Id: \''.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='8'"), 0, "id").'\'},function(data){$(\'#EditGarde\').html(data);});">':"").mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='8'"), 0, "description").($NUMBER?"</a>":"")):($NUMBER?'<a href="javascript:void(0);" style="text-decoration: none;" onClick="$.post(\'ajax.php\',{TYPE: 14, Id: \''.$row["uid"].'\', Type: \''.$LESSON.'\', Value: \'8\'},function(data){$(\'#EditGarde\').html(data);});">-</a>':"-"))."</td>").($END==0?"":"<td>".(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='9'"))==1?(($NUMBER?'<a href="javascript:void(0);" style="text-decoration: none;" onClick="$.post(\'ajax.php\',{TYPE: 14, Id: \''.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='9'"), 0, "id").'\'},function(data){$(\'#EditGarde\').html(data);});">':"").mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE uid='".$row["uid"]."' AND lesson='".$LESSON."' AND type='9'"), 0, "description").($NUMBER?"</a>":"")):($NUMBER?'<a href="javascript:void(0);" style="text-decoration: none;" onClick="$.post(\'ajax.php\',{TYPE: 14, Id: \''.$row["uid"].'\', Type: \''.$LESSON.'\', Value: \'9\'},function(data){$(\'#EditGarde\').html(data);});">-</a>':"-"))."</td>");
                            ?>
                            <td class="EditTd">
                                <select class="Type" name="<?php echo $row["uid"]; ?>_TYPE">
                                    <optgroup label="Tipus">
                                        <option value='1'>kis jegy</option>
                                        <option value='2'>normál jegy</option>
                                        <option value='3'>dolgozat</option>
                                        <option value='4'>témazáró</option>
                                        <option value='5'>vizsgajegy</option>
                                        <option value='6'>órai munka</option>
                                        <option value='7'>gyakorlati jegy</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td class="EditTd">
                                <select class="Value" name="<?php echo $row["uid"]; ?>_VALUE">
                                    <optgroup label="Érték">
                                        <option value=''> </option>
                                        <option value='-'>-</option>
                                        <option value='1'>1</option>
                                        <option value='2'>2</option>
                                        <option value='3'>3</option>
                                        <option value='4'>4</option>
                                        <option value='5'>5</option>
                                    </optgroup>
                                </select>
                            </td>                        
                            <td class="EditTd"><input type="Text" placeholder="Leírás" class="Description" name="<?php echo $row["uid"]; ?>_DES"/></td>
                            <td class="EditTd"><input type="Date" min="<?php echo date("Y-m-d",$_FROM_DATE); ?>" max="<?php echo date("Y-m-d",$_TO_DATE); ?>" class="Date" placeholder="Dátum (ÉÉÉÉ-HH-NN)" value="<?php echo date("Y-m-d"); ?>" name="<?php echo $row["uid"]; ?>_DAT"/></td>
                            <?php
                            echo "<td>".(($s==0)?"-":(sprintf("%01.2f", $n/$s)." ~(".round($n/$s).")"))."</td></tr>";
                            }
                    echo '<tr><td colspan="'.(count($HEAD)+1).'"></td>'.($HALF==0?($END!=0?'<td></td>':''):'<td'.($END!=0?' colspan="2"':'').'></td>').'<td colspan="4" class="EditTd"><input type="Button" value="Hozzáadás" onClick="$.post(\'ajax.php\',{TYPE: 8, Data: $(\'#NewGardesForm\').serialize(), Type: \''.$LESSON.'\'}); $.post(\'ajax.php\',{TYPE: 3, Id: \''.$ID.'\', Type: \''.$LESSON.'\', Data: 1},function(data){$(\'#Gardes\').html(data);});"/></td><td><a href="javascript:void(0);" onClick="$(\'.EditTd\').toggle();">Szerkesztő</a></td></tr></table></form><br /><div id="EditGarde"></div>';
                    if(isset($_POST["Data"]))
                        echo '<script>$(\'.EditTd\').show(\'fast\');</script>';
                    }
                }
        break;
    case 4: //Delete classes/lessons/users/timetable
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        $Type=(isset($_POST["Type"]))?$_POST["Type"]:0;
        switch($Type)
            {
			case 3:
                if(!isset($_POST["Text"]))die("-1");
				$id=mysql_real_escape_string($_POST["Text"]);
				if(!mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE id='".$id."'")))die("-1");
				$SQL=mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE id='".$id."'");
				$id=mysql_fetch_array($SQL);
				
				break;
            case 2:
                if(!isset($_POST["Text"]))
                        echo -1;
                            else{
                            $text=explode(",",$_POST["Text"]);
                            foreach($text as $text2)
                                if($text2!=$_SESSION["ID"])
                                    if(mysql_query("DELETE FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($text2)."'")==1)
                                        {
                                        mysql_query("DELETE FROM $_SYSTEM_GARDES_TABLE WHERE tid='".mysql_real_escape_string($text2)."' OR uid='".mysql_real_escape_string($text2)."'");
                                        mysql_query("DELETE FROM $_SYSTEM_TEACHES_TABLE WHERE tid='".mysql_real_escape_string($text2)."' OR uid='".mysql_real_escape_string($text2)."'");
                                        }
                            echo 1;
                            }
                break;
            case 1:
                if(!isset($_POST["Text"]))
                    echo -1;
                        else{
                        $text=explode(",",$_POST["Text"]);
                        foreach($text as $text2)
                            if(mysql_query("DELETE FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($text2)."'")==1)
                                mysql_query("UPDATE $_SYSTEM_USERS_TABLE SET `class`='0' WHERE class='".mysql_real_escape_string($text2)."'");
                        echo 1;
                        }
                break;
            default:
                if(!isset($_POST["Text"]))
                    echo -1;
                        else{
                        $text=explode(",",$_POST["Text"]);
                        foreach($text as $text2)
                            if(mysql_query("DELETE FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($text2)."'")==1)
                                {
                                mysql_query("DELETE FROM $_SYSTEM_GARDES_TABLE WHERE lesson='".mysql_real_escape_string($text2)."'");
                                mysql_query("DELETE FROM $_SYSTEM_TEACHES_TABLE WHERE lesson='".mysql_real_escape_string($text2)."'");
                                }
                        echo 1;
                        }
                break;
            }
        break;
    case 5: //Add class/lesson
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        $text=$_SYSTEM_LESSONS_TABLE;
        if(isset($_POST["Type"]))
            $text=($_POST["Type"]==1)?$_SYSTEM_CLASSES_TABLE:$text;
        if(!isset($_POST["Text"]) or !isset($_POST["Type"]))
            echo -1;
            else if($_POST["Text"]=="")
                    echo -3;
                        else
                        if(mysql_num_rows(mysql_query("SELECT * FROM $text WHERE name='".mysql_real_escape_string($_POST["Text"])."'"))==0)
                            {
                            mysql_query("INSERT `$text` (`id`, `name`, `enabled`) VALUES ( NULL, '".mysql_real_escape_string($_POST["Text"])."', '".mysql_real_escape_string($_POST["Data"])."')");
                            echo mysql_result(mysql_query("SELECT * FROM $text WHERE name='".mysql_real_escape_string($_POST["Text"])."'"), 0, "id");
                            }else
                            echo -2;
        break;
    case 6: //Update classes/lessons
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(!isset($_POST["Text"]) or !isset($_POST["Id"]) or !isset($_POST["Type"])) {
            echo -1;
            exit;
        }        
        switch($_POST["Type"]) {
            case 0:
                if(!isset($_POST["Data"]))
                    echo -1;
                    else
                    if($_POST["Text"]=="" or $_POST["Id"]=="")
                        echo -3;
                            else
                            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."' AND name='".mysql_real_escape_string($_POST["Text"])."' AND enabled='".mysql_real_escape_string($_POST["Data"])."'"))!=0)
                                echo -2;
                                    else
                                    echo mysql_query("UPDATE `$_SYSTEM_LESSONS_TABLE` SET `name`='".mysql_real_escape_string($_POST["Text"])."', `enabled`='".mysql_real_escape_string($_POST["Data"])."' WHERE id='".mysql_real_escape_string($_POST["Id"])."' LIMIT 1;");
                break;
            case 1:
                if(!isset($_POST["Data"]))
                    echo -1;
                    else
                    if($_POST["Text"]=="" or $_POST["Id"]=="")
                        echo -3;
                            else
                            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."' AND name='".mysql_real_escape_string($_POST["Text"])."' AND enabled='".mysql_real_escape_string($_POST["Data"])."'"))!=0)
                                echo -2;
                                    else
                                    echo mysql_query("UPDATE `$_SYSTEM_CLASSES_TABLE` SET `name`='".mysql_real_escape_string($_POST["Text"])."', `enabled`='".mysql_real_escape_string($_POST["Data"])."' WHERE id='".mysql_real_escape_string($_POST["Id"])."' LIMIT 1;");
                break;
            default:
                echo -1;
                break;
        }
        break;
    case 7: //Change Profile
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if(isset($_POST["P"]) and isset($_POST["NP"]) and isset($_POST["NPA"]))
            {
            if($_POST["P"]=="" or $_POST["NP"]=="" or $_POST["NPA"]=="")
                echo -2;
                    else
                    if($_POST["NP"]==$_POST["NPA"])
                        {
                        if($_POST["NP"]==$_POST["P"])
                            echo -4;
                                else{
                                $P=sha1(md5($_POST['P']));
                                $NP=sha1(md5($_POST['NP']));
                                if(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($_SESSION["ID"])."'"), 0, "password")==$P)
                                    echo mysql_query("UPDATE `$_SYSTEM_USERS_TABLE` SET `password`='".$NP."' WHERE id='".mysql_real_escape_string($_SESSION["ID"])."' AND password='".$P."' LIMIT 1;");
                                    else
                                    echo -3;
                                }
                        }else
                        echo -5;
            }else
            echo -1;
        break;
    case 8: //Add garde
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4 and $_SESSION["RANK"]!=3)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }        
        if(isset($_POST["Data"]) and isset($_POST["Type"]))
            if($_POST["Data"]!="" and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE (status='3' OR status='4') AND id='".mysql_real_escape_string($_SESSION["ID"])."'"))==1 and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($_POST["Type"])."'"))==1)
                {
                $Data=explode("&",$_POST["Data"]);
                foreach($Data as $text=>$text2)
                    if(strpos($text2,"_VALUE"))
                        {
                        $Type="";
                        $Des="";
                        $P=explode("=",$text2);
                        $Value=$P[1];
                        $P=explode("_",$P[0]);
                        $ID=$P[0];
                        unset($Data[$text]);
                        foreach($Data as $text=>$text2)
                            {
                            $text2=explode("=",$text2);
                            if($text2[0]==$ID."_TYPE")
                                {
                                $Type=$text2[1];
                                unset($Data[$text]);
                                }else
                            if($text2[0]==$ID."_DES")
                                {
                                $Des=$text2[1];
                                unset($Data[$text]);
                                }
                            if($text2[0]==$ID."_DAT")
                                {
                                $Dat=$text2[1];
                                if(!strtotime($Dat))
                                    $Dat=date("Y-m-d");
                                if((strtotime($Dat)>=$_FROM_DATE) && (strtotime($Dat)<=$_TO_DATE))
                                    $Dat=$Dat;
                                    else
                                    $Dat=date("Y-m-d");
                                unset($Data[$text]);
                                }
                            }
                        switch($Value)
                            {
                            case "-": case "1": case "2": case "3": case "4": case "5":
                                switch($Type)
                                    {
                                    case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
                                        if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($ID)."'"))==1)
                                            echo mysql_query("INSERT `$_SYSTEM_GARDES_TABLE` (`id`, `uid`, `tid`, `date`, `description`, `value`, `lesson`, `type`) VALUES ( NULL, '".mysql_real_escape_string($ID)."', '".mysql_real_escape_string($_SESSION["ID"])."', '".mysql_real_escape_string($Dat)."', '".mysql_real_escape_string(urldecode($Des))."', '".mysql_real_escape_string($Value)."', '".mysql_real_escape_string($_POST["Type"])."', '".mysql_real_escape_string($Type)."')");
                                        break;
                                    default:
                                        echo -1;
                                        break;
                                    }
                                break;
                            default:
                                echo -1;
                                break;
                            }
                        }
                }else echo -1;
        break;
    case 9: //User manager
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        $U=-1;
        if(isset($_POST["Type"]))
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($_POST["Type"])."'"))==1)
                {
                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($_POST["Type"])."'");
                while($row=mysql_fetch_array($ADAT))
                    {
                    $U  = $row["id"];
                    $UN = $row["username"];
                    $RN = $row["real_name"];
					$OM	= $row["om_id"];
                    $R  = $row["status"];
                    $P  = $row["parent"];
                    $C  = $row["class"];
                    }
                }
        ?>
        <form id="User_Form">
            <label for="User_Username">Felhasználónév: </label><input type="Text" id="User_Username" name="UN" placeholder="Felhasználónév" value="<?php echo ($U!=-1)?$UN:""; ?>"/><br />
                <label for="User_RealName">Valódi neve: </label><input type="Text" id="User_RealName" name="RN" placeholder="Valódi neve" value="<?php echo ($U!=-1)?$RN:""; ?>"/><br />
                <label for="User_Password">Jelszava: </label><input type="Password" id="User_Password" name="PAS" placeholder="Jelszava"/><br />
                <label for="User_Rank">Jogosultsága: </label>
                <select id="User_Rank" name="R" onChange="$('#'+NewUserTypes[NewUserTypes[0]]).hide('fast'); NewUserTypes[0]=parseInt(this.value); $('#'+NewUserTypes[NewUserTypes[0]]).show('fast');">
                    <optgroup label="Válasszon jogosultságot">
                        <option value="1"<?php echo ($U!=-1)?(($R==1)?" selected=\"selected\"":""):" selected=\"selected\""; ?>>Diák</option>
                        <option value="2"<?php echo ($U!=-1)?(($R==2)?" selected=\"selected\"":""):""; ?>>Szülő</option>
                        <option value="3"<?php echo ($U!=-1)?(($R==3)?" selected=\"selected\"":""):""; ?>>Tanár</option>
                        <option value="4"<?php echo ($U!=-1)?(($R==4)?" selected=\"selected\"":""):""; ?>>Adminisztrátor</option>
                        </optgroup>
                </select><br />
                <div id="Student"<?php echo ($U!=-1)?(($R==1)?"":" style=\"display: none;\""):""; ?>>
                    <div class="Parent"<?php echo mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='2'"))>0?'':' style="display: none"'; ?>>
                        <label for="Parent_To_Student">Szülő:</label> 
                        <select id="Parent_To_Student" name="PAR">
                            <optgroup label='Válasszon szülőt!'>
                                <option value="0"> - </option>
                                    <?php
                                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='2' ORDER BY real_name ASC");
                                    while($row=mysql_fetch_array($ADAT))
                                        echo "<option value='".$row["id"]."'".(($U!=-1)?(($P==$row["id"])?" selected=\"selected\"":""):"").">(".$row["username"].") ".$row["real_name"]."</option>\n";
                                    ?>
                            </optgroup>
                        </select>
                     </div>
                    <div class="Class"<?php echo mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1'"))>0?'':' style="display: none"'; ?>>
                        <label for="Class_To_Student">Osztály:</label> 
                        <select id="Class_To_Student" name="CS">
                            <optgroup label='Válasszon osztályt!'>
                                <option value="0"> - </option>
                                    <?php
                                    $CLASSES="";
                                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1' ORDER BY name ASC");
                                    while($row=mysql_fetch_array($ADAT))
                                        $CLASSES.="<option value='".$row["id"]."'".(($U!=-1)?(($C==$row["id"])?" SELECTED":""):"").">".$row["name"]."</option>\n";
                                    echo $CLASSES;
                                    ?>
                            </optgroup>
                        </select>
                </div>
				<label for="Student_OM">OM azonosítója:</label> <input id="Student_OM" name="OM" type="Number" size="11" maxlength="11" min="70000000000" max="79999999999" value="<?php echo $OM; ?>" onKeyUp="this.value = this.value.replace(/[^0-9\.]/g,'');" />
                </div>
                <div id="Parent"<?php echo ($U!=-1)?(($R==2)?"":" style=\"display: none;\"style=\"display: none;\""):" style=\"display: none;\""; ?>></div>
                <div id="Teacher"<?php echo ($U!=-1)?(($R==3)?"":" style=\"display: none;\"style=\"display: none;\""):" style=\"display: none;\""; ?>>
                    <div class="Class"<?php echo mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1'"))>0?'':' style="display: none"'; ?>>
                        <label for="Class_To_Teacher">Osztály:</label> 
                        <select id="Class_To_Teacher" name="CT">
                            <optgroup label='Válasszon osztályt!'>
                                <option value="0"> - </option>
                                <?php echo $CLASSES; ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div id="Administrator"<?php echo ($U!=-1)?(($R==4)?"":" style=\"display: none;\"style=\"display: none;\""):" style=\"display: none;\""; ?>></div><br/>
                <input type="Button" value="Hozzáad" onClick="if($('#User_Username').val()=='' || $('#User_RealName').val()=='' || $('#User_Password').val()=='')Message('Nem töltötted ki az összes mezőt.',5000,'User_Error','red'); else $.post('ajax.php',{TYPE: 10, Data: $('#User_Form').serialize()},function(a){if(a=='-2')Message('Nem töltötted ki az összes mezőt.',5000,'User_Error','red'); else if(a=='-3')Message('Ez a felhasználónév már foglalt.',5000,'User_Error','red'); else if(a=='-4')Message('Rossz OM azonosító formátum.',5000,'User_Error','red'); else if(a=='-5')Message('Ez az OM azonosító már használatban van.',5000,'User_Error','red'); else if(a=='-1')Message('Hiba!',5000,'User_Error','red'); else{Message('Felhasználó hozzáadva!',5000,'User_Error','green'); User='('+$('#User_Username').val()+' - '; if($('#User_Rank :selected').val()=='2')User+='Sz'; else if($('#User_Rank :selected').val()=='3')User+='T'; else if($('#User_Rank :selected').val()=='4')User+='A'; else User+='D'; User+=') '+$('#User_RealName').val(); $('#Edit_User,#Delete_Users').append($('<option></option>').attr('value', a).text(User)); if($('#User_Rank :selected').val()=='3')$('#NewT_Teacher').append($('<option></option>').attr('value',a).text($('#User_RealName').val())); else if($('#User_Rank :selected').val()=='1')$('#NewT_Student').append($('<option></option>').attr('value',a).text($('#User_RealName').val())); $('#Users_Div').show('fast'); $.post('ajax.php',{TYPE: 9},function(a){$('#User_Content').html(a); Timetable_Input();});}});"/>
                <input type="Button" value="Eldob" onClick="if(confirm('Biztosan kiüríted az űrlapot?')){$('#User_Username,#User_RealName,#User_Password').val(''); $('#User_Rank,#Parent_To_Student,#Class_To_Student,#Class_To_Teacher,#Edit_User').each(function(){$(this).val($('option:first',this).val()).trigger('change');});}"/>
            </form>
        <?php
        echo $U!=-1?'<script>NewUserTypes[0]=\''.$R.'\'</script>':'';
        break;
    case 10: //Add user
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(!isset($_POST["Data"]))
            echo -1;
            else if($_POST["Data"]=="")
                echo -1;
        $Data=explode("&",urldecode($_POST["Data"]));
        foreach($Data as $text)
            {
            $Type=explode("=",$text);
            $text2[$Type[0]]=$Type[1];
            }
        $Data=$text2;
        if(!isset($Data["UN"]) or !isset($Data["RN"]) or !isset($Data["PAS"]) or !isset($Data["OM"]))
            echo -1;
            else
            if($Data["UN"]=="" or $Data["RN"]=="" or $Data["PAS"]=="" or (string)$Data["OM"]=="")
                echo -2;
                else
				if(strlen($Data["OM"])!=11 or (string)$Data["OM"][0]!="7")
					echo -4;
						else
						if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE om_id='".mysql_real_escape_string($Data["OM"])."'"))==1)
						 echo -5;
							else
							if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE username='".mysql_real_escape_string($Data["UN"])."'"))==1)
								echo -3;
									else{
									$C=((string)$Data["R"]=="1")?(int)$Data["CS"]:((string)$Data["R"]=="3"?(int)$Data["CT"]:0);
									$C=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($C)."'"))==1?$C:0;
									$P=((string)$Data["R"]=="1")?((mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($Data["PAR"])."' AND status!='1'"))==1)?$Data["PAR"]:0):0;
									if(mysql_query("INSERT `$_SYSTEM_USERS_TABLE` (`id`, `username`, `real_name`, `password`, `om_id`, `date`, `class`, `parent`, `status`) VALUES ( NULL, '".mysql_real_escape_string($Data["UN"])."', '".mysql_real_escape_string($Data["RN"])."', '".sha1(md5($Data["PAS"]))."', '".mysql_real_escape_string($Data["OM"])."', '".mysql_real_escape_string(date("Y-m-d-G-i-s"))."', '".mysql_real_escape_string($C)."', '".mysql_real_escape_string($P)."', '".mysql_real_escape_string($Data["R"])."')")!=1)
										echo -1;
											else
											echo mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE username='".mysql_real_escape_string($Data["UN"])."'"), 0, "id");
									}
                
        break;
    case 11: //Change user data
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(!isset($_POST["Type"]))
            echo -1;
            else
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($_POST["Type"])."'"))!=1)
                echo -1;
                else{
                $Data=explode("&",urldecode($_POST["Data"]));
                foreach($Data as $text)
                    {
                    $Type=explode("=",$text);
                    $text2[$Type[0]]=$Type[1];
                    }
                $Data=$text2;
                if(!isset($Data["UN"]) or !isset($Data["RN"]) or !isset($Data["PAS"]) or !isset($Data["OM"]))
                    echo -1;
                    else
                    if($Data["UN"]=="" or $Data["RN"]=="" or (string)$Data["OM"]=="")
                        echo -2;
							else
						if(strlen($Data["OM"])!=11 or (string)$Data["OM"][0]!="7")
							echo -4;
								else
								if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE om_id='".mysql_real_escape_string($Data["OM"])."' AND id!='".mysql_real_escape_string($_POST["Type"])."'"))==1)
									echo -5;
										else
											if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE username='".mysql_real_escape_string($Data["UN"])."' AND id!='".mysql_real_escape_string($_POST["Type"])."'"))==0)
												{
												$C=((string)$Data["R"]=="1")?(int)$Data["CS"]:((string)$Data["R"]=="3"?(int)$Data["CT"]:0);
												$C=$C==0?0:(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($C)."'"))==1?$C:0);
												$P=((string)$Data["R"]=="1")?((mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($Data["PAR"])."' AND status!='1'"))==1)?$Data["PAR"]:0):0;
												echo mysql_query("UPDATE `$_SYSTEM_USERS_TABLE` SET `username`='".mysql_real_escape_string($Data["UN"])."', real_name='".mysql_real_escape_string($Data["RN"])."', class='".mysql_real_escape_string($C)."', parent='".mysql_real_escape_string($P)."', status='".mysql_real_escape_string($Data["R"])."'".($Data["PAS"]==""?"":", password='".sha1(md5($Data["PAS"]))."'").", om_id='".mysql_real_escape_string($Data["OM"])."' WHERE id='".mysql_real_escape_string($_POST["Type"])."' LIMIT 1;");
												}else
												echo -3;
                    }
        break;
        case 12: //Timetable
            if(!isset($_POST["Type"]) or !isset($_POST["Id"]))
                {
                echo "Hiba!";
                exit;
                }
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE"))==0 or mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE"))==0)
                exit;
            $TYPE=$_POST["Type"];
            $ID=$_POST["Id"];
            $DATE=strtotime(date("Y-m-d"));
            if(isset($_POST["Date"]))
                if(date("Y-m-d", strtotime($_POST["Date"]))==$_POST["Date"])
                    $DATE=strtotime($_POST["Date"]);
            if(date("N",$DATE)!=1)
                $DATE=strtotime("previous monday",$DATE);
			if(date("N")==1)
				$THIS=strtotime(date("Y-m-d"));
					else
					$THIS=strtotime("previous monday",strtotime(date("Y-m-d")));
            $DATEEND=strtotime("+6 day",$DATE);
            if($TYPE==1 or $TYPE==3)
                $CLASS=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($ID)."' LIMIT 1"), 0, "class");
                    else
                $CLASS=$ID;
            switch((int)$TYPE)
                {
                case 1:
                    $WHERE="tid='".mysql_real_escape_string($ID)."'";
                    break;
                case 2:
                    $WHERE="class='".mysql_real_escape_string($ID)."'";
                    break;
                case 3:
                    $WHERE="class='".$CLASS."'";
                    break;
                default:
                    echo "Hiba a helyválasztásban.";
                    break;
                }
            $i=$j="<option value=\"-\"> - </option>";
            $jj=0;
            $ii=0;
            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1' ORDER BY name ASC");
            while($row=mysql_fetch_array($ADAT))
                if(@mysql_num_rows(@mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE class='".$row["id"]."'"))) {
                    $j.="<option value='".$row["id"]."'".(($CLASS==$row["id"] and $TYPE!=1)?" SELECTED":"").">".$row["name"]."</option>\n";
                    $jj+=1;
                }
            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='3' ORDER BY real_name ASC");
            while($row=mysql_fetch_array($ADAT))
                if(@mysql_num_rows(@mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE tid='".$row["id"]."'"))!=0) {
                    $i.="<option value='".$row["id"]."'".(($ID==$row["id"] and $TYPE==1)?" SELECTED":"").">".$row["real_name"]."</option>\n";
                    $ii+=1;
                }
            echo '<table border="1" align="center"><tr><td><select id="Select_TimeTable_Teacher" onChange="$(\'#Select_TimeTable_Classes\').each(function(){$(this).val($(\'option:first\',this).val());}); if($(this).val()!=\'-\'){$.post(\'ajax.php\',{TYPE: 12, Type: 1, Id: $(this).val(), Date: $(\'#Timetable_Date :selected\').val()},function(data){$(\'#DTimetable\').html(data);});}">'.$i.'</select></td><td'.($jj>0?'':' style="display: none;"').'><label for="Select_TimeTable_Classes">Osztály:</label> <select id="Select_TimeTable_Classes" onChange="$(\'#Select_TimeTable_Teacher\').each(function(){$(this).val($(\'option:first\',this).val());}); if($(this).val()!=\'-\'){$.post(\'ajax.php\',{TYPE: 12, Type: 2, Id: $(this).val(), Date: $(\'#Timetable_Date :selected\').val()},function(data){$(\'#DTimetable\').html(data);});}">'.$j.'</select></td>';
            $j="";
            $jj=0;
            for($i=strtotime("Monday",$_FROM_DATE); $i<=$_TO_DATE; $i=strtotime("+1 week",$i))
                if(@mysql_num_rows(@mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE ".$WHERE." AND `from` <= '".date("Y-m-d",strtotime("+6 day",$i))."' AND (`to` >= '".date("Y-m-d",$i)."' OR `to`='0000-00-00')"))!=0) {
                    $j.="<option value='".date('Y-m-d', $i)."'".(date('Y-m-d', $i)==date('Y-m-d', $DATE)?" SELECTED":"").">".date('Y-m-d', $i)." - ".date('Y-m-d', strtotime("+6 day",$i))."</option>";
                    $jj+=1;
                }
            echo '<td'.($jj>1?'':' style="display: none;"').'><label for="Timetable_Date">Dátum:</label> <select id="Timetable_Date" onChange="if($(\'#Select_TimeTable_Classes :selected\').val()!=\'-\')$.post(\'ajax.php\',{TYPE: 12, Type: 2, Id: $(\'#Select_TimeTable_Classes :selected\').val(), Date: $(this).val()},function(data){$(\'#DTimetable\').html(data);}); else if($(\'#Select_TimeTable_Teacher :selected\').val()!=\'-\')$.post(\'ajax.php\',{TYPE: 12, Type: 1, Id: $(\'#Select_TimeTable_Teacher :selected\').val(), Date: $(this).val()},function(data){$(\'#DTimetable\').html(data);}); $(\'#Timetable_Button\').attr(\'disabled\', ($(this).val()==\''.date("Y-m-d",$THIS).'\'));">'.
                 $j."</select><input type=\"Button\" id=\"Timetable_Button\" onClick=\"\$('#Timetable_Date option[value=\'".date('Y-m-d',$THIS)."\']').attr('selected', 'selected').change();\" value=\"Ma\"".(date("Y-m-d",$DATE)==date('Y-m-d',$THIS)?' disabled="disabled"':'')."/></td></tr></table>";
            $SELECT="SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE ".$WHERE." AND `from` <= '".date("Y-m-d",$DATEEND)."' AND (`to` >= '".date("Y-m-d",$DATE)."' OR `to`='0000-00-00')";
            if(@mysql_num_rows(@mysql_query($SELECT))==0)
                {
                switch((int)$TYPE)
                    {
                    case 1:
                        echo "A kiválasztott felhasználónak (".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($ID)."' LIMIT 1"), 0, "real_name").") még nincs órarend megadva a következő időközre: ".date("Y-m-d",$DATE)." - ".date("Y-m-d",$DATEEND);
                        break;
                     case 2: case 3:
                        echo "A kiválasztott osztálynak (".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($ID)."' LIMIT 1"), 0, "name").") még nincs órarend megadva a következő időközre: ".date("Y-m-d",$DATE)." - ".date("Y-m-d",$DATEEND);
                        break;
                    default:
                        echo "Hiba a helyválasztásban.";
                        exit;
                        break;
                }
                echo "<h5>Itt még nincs órarend megadva.</h5>";
                exit;
                }
            if($TYPE==1 or $TYPE==3)
                echo "<h4><i>".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($ID)."' LIMIT 1"), 0, "real_name")."</i> órarendje</h4>";
                else
                if($TYPE==2)
                    echo "<h4><i>".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($ID)."' LIMIT 1"), 0, "name")."</i> órarendje</h4>";
            $DATES=array();
            $HEAD=array();
            $NMI="";
            $NMA="";
            $ADAT=mysql_query($SELECT);
            while($row=mysql_fetch_array($ADAT))
                {
                $WAS=array();
                while($row["parent"]!=0 and !in_array($row["parent"],$WAS))
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE")))
                        {
                        $WAS[]=$row["parent"];
                        for($i=0; $i<count($DATES); $i++)
                            if($DATES[$i]["id"]==$row["parent"] and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($row["lesson"])."'"))==1)
                                {
                                unset($DATES[$i]);
                                $row=mysql_fetch_array(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE id='".$row["id"]."' LIMIT 1"));
                                }
                        }else
                        $row["parent"]=0;
                if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($row["lesson"])."'"))!=1)
                    continue;
                if($TYPE==1)
                    $row["description"]=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($row["lesson"])."' LIMIT 1"), 0, "name")." (".($row["class"]?mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($row["class"])."' LIMIT 1"), 0, "name"):mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($row["uid"])."' LIMIT 1"), 0, "real_name")).")";
                    else
                    $row["description"]=mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($row["lesson"])."' LIMIT 1"), 0, "name")."<br />".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($row["tid"])."' LIMIT 1"), 0, "real_name");
                foreach($row as $key=>$value)
                    if(!is_string($key))
                        unset($row[$key]);
                $DATES[]=$row;
                if(!in_array($row["day"],$HEAD))
                array_push($HEAD,$row["day"]);
                if($NMI=="")$NMI=$row["number"]; else
                if($NMI>$row["number"])$NMI=$row["number"];
                if($NMA=="")$NMA=$row["number"]; else
                if($NMA<$row["number"])$NMA=$row["number"];
                }
            if(count($HEAD)==0 or count($DATES)==0)
                {
                echo "Hiba az órarendben!";
                exit;
                }
            sort($HEAD);
            ?>
            <table border="1" align="center">
                <tr><td>Óra/Nap</td><?php for($i=0; $i<count($HEAD); $i++)echo "<td>".ucfirst(iconv('iso-8859-2','utf-8',strftime("%A",strtotime('+'.$HEAD[$i].' day', strtotime('next Sunday')))))."</td>"; ?></tr>
                <?php
                for($i=$NMI; $i<=$NMA; $i++)
                    {
                    echo "<tr><td>".$i.".</td>";
                    for($a=0; $a<count($HEAD); $a++)
                        {
                        $Des="";
                        foreach($DATES as $array)
                            if($array["day"]==$HEAD[$a] and $array["number"]==$i)
                                $Des.=($_SESSION["RANK"]==4?"<a href=\"javascript:void(0);\" onClick=\"\$.post('ajax.php',{TYPE: 16, Id: '".$array["id"]."'},function(data){\$('#EditTimetable').html(data);});\" style=\"color: black; text-decoration: none;\">".$array["description"]."</a>":$array["description"]);
                        echo "<td".(($Des=="" and $_SESSION["RANK"]==4)?" onClick=\"\$('#Other_Manager').show('fast'); \$('#NewT_Day option[value=\'".($a+1)."\'],#NewT_Class option[value=\'".($TYPE==2?$ID:"")."\'],#NewT_Student option[value=\'".($TYPE==3?$ID:"")."\'],#NewT_Lesson option[value=\'\'],#NewT_Teacher option[value=\'".($TYPE==1?$ID:"")."\']').attr('selected', 'selected'); \$('#NewT_Lesson_Number').val('".$i."'); \$('#NewT_From').val('".date("Y-m-d")."'); \$('#NewT_To').val('0000-00-00'); \$('#Timetable_Manager').show('fast'); \$('#Timetable').hide('fast'); \$('#Lesson_Manager').hide('fast'); $('#User_Manager').hide('fast'); \$('#Class_Manager').hide('fast');\"":"").">".$Des."</td>";
                        }
                    echo"</tr>";
                    }
                ?>
            </table><br />
            <div id="EditTimetable"></div>
            <?php
            break;
    case 13: //Add timetable
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(isset($_POST["Type"]) and isset($_POST["Data"]) and isset($_POST["Data2"]) and isset($_POST["Data3"]) and isset($_POST["Data4"]) and isset($_POST["Data5"]) and isset($_POST["Data6"]) and isset($_POST["Data7"]) and isset($_POST["Data8"]))
            {
            if($_POST["Type"]!="" and $_POST["Data"]!="" and $_POST["Data2"]!="" and $_POST["Data3"]!="" and $_POST["Data4"]!="" and $_POST["Data5"]!="" and $_POST["Data6"]!="" and $_POST["Data8"]!="")
                {
                if($_POST["Data7"]=="")
                    $_POST["Data7"]="0000-00-00";
                if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($_POST["Data2"])."'"))!=1 or mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='3' AND id='".mysql_real_escape_string($_POST["Data3"])."'"))!=1)
                    {
                    echo -1;
                    exit;
                    }
                if($_POST["Type"]=="1")
                    {
                    $QUERY="INSERT `$_SYSTEM_TIMETABLE_TABLE` (`id`, `class`, `uid`, `tid`, `type`, `lesson`, `parent`, `day`, `number`, `from`, `to`) VALUES (NULL, '".((string)$_POST["Data8"]=="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".((string)$_POST["Data8"]!="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".mysql_real_escape_string($_POST["Data3"])."', '0', '".mysql_real_escape_string($_POST["Data2"])."', '0', '".mysql_real_escape_string($_POST["Data4"])."', '".mysql_real_escape_string($_POST["Data5"])."', '".mysql_real_escape_string($_POST["Data6"])."', '".(strtotime($_POST["Data6"])>strtotime($_POST["Data7"])?mysql_real_escape_string($_POST["Data6"]):mysql_real_escape_string($_POST["Data7"]))."');";
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='1' AND id='".mysql_real_escape_string($_POST["Data"])."'"))==1)
                        {
                        if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE WHERE uid='".mysql_real_escape_string($_POST["Data"])."' AND tid='".mysql_real_escape_string($_POST["Data3"])."' AND lesson='".mysql_real_escape_string($_POST["Data2"])."'"))!=1)
                            if(mysql_query($QUERY))
                                echo mysql_query("INSERT `$_SYSTEM_TEACHES_TABLE` (`id`, `tid`, `uid`, `lesson`) VALUES (NULL, '".mysql_real_escape_string($_POST["Data3"])."', '".mysql_real_escape_string($_POST["Data"])."', '".mysql_real_escape_string($_POST["Data2"])."')");
                                else
                                echo -1;
                            else
                            echo mysql_query($QUERY);
                        }else
                        echo -1;
                    }else{
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($_POST["Data"])."'"))==1) {
                        if(mysql_query("INSERT `$_SYSTEM_TIMETABLE_TABLE` (`id`, `class`, `uid`, `tid`, `type`, `lesson`, `parent`, `day`, `number`, `from`, `to`) VALUES (NULL, '".((string)$_POST["Data8"]=="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".((string)$_POST["Data8"]!="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".mysql_real_escape_string($_POST["Data3"])."', '0', '".mysql_real_escape_string($_POST["Data2"])."', '0', '".mysql_real_escape_string($_POST["Data4"])."', '".mysql_real_escape_string($_POST["Data5"])."', '".mysql_real_escape_string($_POST["Data6"])."', '".((strtotime($_POST["Data6"])<strtotime($_POST["Data7"]) or $_POST["Data7"]=="0000-00-00")?mysql_real_escape_string($_POST["Data7"]):mysql_real_escape_string($_POST["Data6"]))."');")) {
                            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='1' AND class='".mysql_real_escape_string($_POST["Data"])."'");
                            while($row=mysql_fetch_array($ADAT))
                                if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE WHERE uid='".mysql_real_escape_string($row["id"])."' AND tid='".mysql_real_escape_string($_POST["Data3"])."' AND lesson='".mysql_real_escape_string($_POST["Data2"])."'"))!=1)
                                    mysql_query("INSERT `$_SYSTEM_TEACHES_TABLE` (`id`, `tid`, `uid`, `lesson`) VALUES (NULL, '".mysql_real_escape_string($_POST["Data3"])."', '".mysql_real_escape_string($row["id"])."', '".mysql_real_escape_string($_POST["Data2"])."')");
                            echo 1;
                        }else
                        echo -1;
                    }else
                    echo -1;
                }
            }else
            echo -1;
            }else
            echo -1;
        break;
    case 14: //Load garde editor
     if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=3 and $_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(!isset($_POST["Id"]))
            echo "Hiba!";
            else{
            if(isset($_POST["Type"]) and isset($_POST["Value"]))
                $ID=mysql_real_escape_string(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."' LIMIT 1"), 0, "class"));
                else
                $ID=mysql_real_escape_string(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string(mysql_result(mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."' LIMIT 1"), 0, "uid"))."' LIMIT 1"), 0, "class"));
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".$ID."'"))==0)
                echo -2;
                    else{
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE class='".$ID."' AND status='1'"))==0)
                        {
                        echo "<h5>Ebbe az osztályba egy tanuló sem jár.</h5>";
                        exit;
                        }
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE, $_SYSTEM_USERS_TABLE WHERE $_SYSTEM_TEACHES_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND class='".$ID."'"))==0)
                        {
                        echo "<h5>Ebben az osztályban még senki nem tanul semmit.</h5>";
                        exit;
                        }
                    }
            if(isset($_POST["Type"]) and isset($_POST["Value"]))
                {
                $Type=(int)$_POST["Value"];
                $UN=$_POST["Id"];
                $RN="";
                $C=$_POST["Type"];
                }else{
                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_GARDES_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."'");
                while($row=mysql_fetch_array($ADAT))
                    {
                    $U      = $row["tid"];
                    $UN     = $row["uid"];
                    $RN     = $row["description"];
                    $Value  = $row["value"];
                    $P      = $row["date"];
                    $C      = $row["lesson"];
                    $Type   = $row["type"];
                    }
                }
            $TYPE=($Type==8 or $Type==9)?1:0;
            if($TYPE)
                $Typ=$Type==8?"Félév":"Év vége";
                else{
                $Typ="<select class='Value' id='EditType'>".
                    "<optgroup label='Tipus'>".
                        "<option value='1'".($Type==1?" SELECTED":"").">kis jegy</option>".
                        "<option value='2'".($Type==2?" SELECTED":"").">normál jegy</option>".
                        "<option value='3'".($Type==3?" SELECTED":"").">dolgozat</option>".
                        "<option value='4'".($Type==4?" SELECTED":"").">témazáró</option>".
                        "<option value='5'".($Type==5?" SELECTED":"").">vizsgajegy</option>".
                        "<option value='6'".($Type==6?" SELECTED":"").">órai munka</option>".
                        "<option value='7'".($Type==7?" SELECTED":"").">gyakorlati jegy</option>".
                    "</optgroup>".
                "</select>";
                }
            if(!$TYPE)$Valu="<select class='Value' id='EditValue'>".
                    "<optgroup label='Érték'>".
                        "<option value='-1'".($Value==''?" SELECTED":"").">Jegy törlése</option>".
                        "<option value='-'".($Value=='-'?" SELECTED":"").">-</option>".
                        "<option value='1'".($Value=='1'?" SELECTED":"").">1</option>".
                        "<option value='2'".($Value=='2'?" SELECTED":"").">2</option>".
                        "<option value='3'".($Value=='3'?" SELECTED":"").">3</option>".
                        "<option value='4'".($Value=='4'?" SELECTED":"").">4</option>".
                        "<option value='5'".($Value=='5'?" SELECTED":"").">5</option>".
                    "</optgroup>".
                "</select>";
            echo '<table border="1" align="Center"><tr><td>Diák</td><td>'.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($UN)."' LIMIT 1"), 0, "real_name").'</td></tr>'.
                (!$TYPE?'<tr><td>Tanár</td><td>'.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($U)."' LIMIT 1"), 0, "real_name").'</td></tr>':'').
                '<tr><td>Leírás</td><td><input id="EditDescription" placeholder="Leírás" value="'.$RN.'" /></td></tr>'.
                (!$TYPE?'<tr><td>Dátum</td><td><input id="EditDate" type="Date" min="'.date("Y-m-d",$_FROM_DATE).'" max="'.date("Y-m-d",$_TO_DATE).'" placeholder="Dátum (ÉÉÉÉ-HH-NN)" value="'.$P.'" /></td></tr>':'').
                '<tr><td>Tipus</td><td>'.$Typ.'</td></tr>'.
                (!$TYPE?'<tr><td>Érték</td><td>'.$Valu.'</td></tr>':'<tr><td>Óra</td><td>'.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($C)."' LIMIT 1"), 0, "name").'</td></tr>').
                '<tr><td><input type="Button" onClick="$(\'#EditGarde\').html(\'\');" value="Mégse"/></td><td><input type="Button" onClick="'.((isset($_POST["Type"]) and isset($_POST["Value"]))?'$.post(\'ajax.php\',{TYPE: 8, Data: \''.$UN.'_TYPE='.$Type.'&'.$UN.'_VALUE=-&'.$UN.'_DES=\'+($(\'#EditDescription\').val())+\'&'.$UN.'_DAT='.date("Y-m-d").'\', Type: \''.$C.'\'},function(data){if(data==1)$.post(\'ajax.php\',{TYPE: 3, Id: \''.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($UN)."' LIMIT 1"), 0, "class").'\', Type: \''.$C.'\'},function(data){$(\'#Gardes\').html(data);});});':(!$TYPE?'$.post(\'ajax.php\',{TYPE: 15, DESC: $(\'#EditDescription\').val(), Type: $(\'#EditType :selected\').val(), DAT: $(\'#EditDate\').val(), VALUE: $(\'#EditValue :selected\').val(), Id: '.$_POST["Id"].'},function(data){if(data==1)$.post(\'ajax.php\',{TYPE: 3, Id: \''.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($UN)."' LIMIT 1"), 0, "class").'\', Type: \''.$C.'\'},function(data){$(\'#Gardes\').html(data);});});':'$.post(\'ajax.php\',{TYPE: 15, DESC: $(\'#EditDescription\').val(), Id: '.$_POST["Id"].'},function(data){if(data==1)$.post(\'ajax.php\',{TYPE: 3, Id: \''.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".mysql_real_escape_string($UN)."' LIMIT 1"), 0, "class").'\', Type: \''.$C.'\'},function(data){$(\'#Gardes\').html(data);});});')).'" value="Módosít"/></td></tr>'.
                '</table>';
            }
        break;
    case 15: //Update garde
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4 and $_SESSION["RANK"]!=3)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        switch((isset($_POST["DAT"])?$_POST["VALUE"]:"-"))
            {
            case "-": case "1": case "2": case "3": case "4": case "5": case "-1":
                switch((isset($_POST["DAT"])?$_POST["Type"]:"8"))
                    {
                    case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
                        if(isset($_POST["DAT"]))
                            {
                            if($_POST["VALUE"]=="-1")
                                {
                                echo mysql_query("DELETE FROM $_SYSTEM_GARDES_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."'");
                                exit;
                                }
                            $Dat=$_POST["DAT"];
                            if(!strtotime($Dat))
                                $Dat=date("Y-m-d");
                            if((strtotime($Dat)>=$_FROM_DATE) && (strtotime($Dat)<=$_TO_DATE))
                                $Dat=$Dat;
                                else
                                $Dat=date("Y-m-d");
                            echo mysql_query("UPDATE $_SYSTEM_GARDES_TABLE SET `tid`='".mysql_real_escape_string($_SESSION["ID"])."', `value`='".mysql_real_escape_string($_POST["VALUE"])."', `date`='".mysql_real_escape_string($Dat)."', `type`='".mysql_real_escape_string($_POST["Type"])."', `description`='".mysql_real_escape_string($_POST["DESC"])."' WHERE id='".mysql_real_escape_string($_POST["Id"])."'");
                            }else
                            echo mysql_query("UPDATE $_SYSTEM_GARDES_TABLE SET `date`='".mysql_real_escape_string(date("Y-m-d"))."', `tid`='".mysql_real_escape_string($_SESSION["ID"])."', `description`='".mysql_real_escape_string($_POST["DESC"])."' WHERE id='".mysql_real_escape_string($_POST["Id"])."'");
                        break;
                    default:
                        echo -1;
                        break;
                    }
                    break;
                default:
                    echo -1;
                    break;
            }
        break;
    case 16: //Update timetable editor
    if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(!isset($_POST["Id"]))
            echo "Hiba!";
            else{
            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE id='".mysql_real_escape_string($_POST["Id"])."'");
            while($row2=mysql_fetch_array($ADAT)) {
                echo '<table border="1" align="Center">'.
                    '<tr><td>Hét napja</td><td><select id="EditT_Day" onChange="$(\'#EditT_Lesson_Number\').val(1);"><option value="-">-Törlés-</option>';
                    for($i=1; $i<=7; $i++)
                        echo '<option value="'.$i.'"'.($row2["day"]==$i?" SELECTED":"").'>'.iconv('iso-8859-2','utf-8',strftime("%A",strtotime(date("l",mktime(0,0,0,7,$i,2013))))).'</option>';
                echo '</select></td></tr>'.
                    '<tr><td>Óra száma</td><td><input type="number" id="EditT_Lesson_Number" maxlength="2" size="2" max="15" min="0" value="1" /></td></tr>'.
                    '<tr><td>Tanuló</td><td><select id="EditT_Student" onChange="$(\'#EditT_Class\').each(function(){$(this).val($(\'option:first\',this).val());}); if($(\'#EditT_Teacher option:selected\').val()!=\'\' && $(\'#EditT_From\').val()!=\'\' && $(\'#EditT_Lesson option:selected\').val()!=\'\' && ($(\'#EditT_Student option:selected\').val()!=\'\' || $(\'#EditT_Class option:selected\').val()!=\'\'))$(\'#EditT_Button\').attr(\'disabled\',false); else $(\'#EditT_Button\').attr(\'disabled\',true);"><option value="">-Válassz-</option>';
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='1' ORDER BY real_name ASC");
                    while($row=mysql_fetch_array($ADAT))
                        echo '<option value="'.$row["id"].'"'.($row2["uid"]==$row["id"]?" SELECTED":"").'>'.$row["real_name"].'</option>\n';
                echo '</select></td></tr>'.
                    '<tr><td>Osztály</td><td><select id="EditT_Class" onChange="$(\'#EditT_Student\').each(function(){$(this).val($(\'option:first\',this).val());}); if($(\'#EditT_Teacher option:selected\').val()!=\'\' && $(\'#EditT_From\').val()!=\'\' && $(\'#EditT_Lesson option:selected\').val()!=\'\' && ($(\'#EditT_Student option:selected\').val()!=\'\' || $(\'#EditT_Class option:selected\').val()!=\'\'))$(\'#EditT_Button\').attr(\'disabled\',false); else $(\'#EditT_Button\').attr(\'disabled\',true);"><option value="">-Válassz-</option>';
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE ORDER BY name ASC");
                    while($row=mysql_fetch_array($ADAT))
                        echo '<option value="'.$row["id"].'"'.($row2["class"]==$row["id"]?" SELECTED":"").'>'.$row["name"].'</option>\n';
                echo '</select></td></tr>'.
                    '<tr><td>Óra</td><td><select id="EditT_Lesson" onChange="if($(\'#EditT_Teacher option:selected\').val()!=\'\' && $(\'#EditT_From\').val()!=\'\' && $(\'#EditT_Lesson option:selected\').val()!=\'\' && ($(\'#EditT_Student option:selected\').val()!=\'\' || $(\'#EditT_Class option:selected\').val()!=\'\'))$(\'#EditT_Button\').attr(\'disabled\',false); else $(\'#EditT_Button\').attr(\'disabled\',true);"><option value="">-Válassz-</option>';
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE enabled='1' ORDER BY name ASC");
                    while($row=mysql_fetch_array($ADAT))
                        echo '<option value="'.$row["id"].'"'.($row2["lesson"]==$row["id"]?" SELECTED":"").'>'.$row["name"].'</option>\n';
                echo '</select></td></tr>'.
                    '<tr><td>Tanár</td><td><select id="EditT_Teacher" onChange="if($(\'#EditT_Teacher option:selected\').val()!=\'\' && $(\'#EditT_From\').val()!=\'\' && $(\'#EditT_Lesson option:selected\').val()!=\'\' && ($(\'#EditT_Student option:selected\').val()!=\'\' || $(\'#EditT_Class option:selected\').val()!=\'\'))$(\'#EditT_Button\').attr(\'disabled\',false); else $(\'#EditT_Button\').attr(\'disabled\',true);"><option value="">-Válassz-</option>';
                    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='3' ORDER BY real_name ASC");
                    while($row=mysql_fetch_array($ADAT))
                        echo '<option value="'.$row["id"].'"'.($row2["tid"]==$row["id"]?" SELECTED":"").'>'.$row["real_name"].'</option>\n';
                echo '</select></td></tr>'.
                    '<tr><td>Kezdete</td><td><input type="Date" id="EditT_From" value="'.$row2["from"].'" min="'.date("Y-m-d",$_FROM_DATE).'" max="'.date("Y-m-d",$_TO_DATE).'" onChange="if($(\'#EditT_Teacher option:selected\').val()!=\'\' && $(\'#EditT_From\').val()!=\'\' && $(\'#EditT_Lesson option:selected\').val()!=\'\' && ($(\'#EditT_Student option:selected\').val()!=\'\' || $(\'#EditT_Class option:selected\').val()!=\'\'))$(\'#EditT_Button\').attr(\'disabled\',false); else $(\'#EditT_Button\').attr(\'disabled\',true);"/></td></tr>'.
                    '<tr><td>Vége</td><td><input type="Date" id="EditT_To" value="'.$row2["to"].'" min="'.date("Y-m-d",$_FROM_DATE).'" max="'.date("Y-m-d",$_TO_DATE).'"/></td></tr>'.
                    '<tr><td><input type="Button" onClick="$(\'#EditTimetable\').html(\'\');" value="Mégse"/></td><td><input type="Button" id="EditT_Button" onClick="if($(\'#EditT_Day :selected\').val()==\'-\') alert(\'Törlés\'); else alert(\'Sajnos még nincs hatása!\');" value="Módosít"/></td></tr>'.
                    '</table>';
            }
            }
        break;
    case 13: //Update timetable
        if($_SESSION["ID"]==-1)
            {
            echo "Hiba!";
            exit;
            }
        if($_SESSION["RANK"]!=4)
            {
            echo "Csalunk? Csalunk? Nincs hozzá jogod!";
            exit;
            }
        if(isset($_POST["Type"]) and isset($_POST["Data"]) and isset($_POST["Data2"]) and isset($_POST["Data3"]) and isset($_POST["Data4"]) and isset($_POST["Data5"]) and isset($_POST["Data6"]) and isset($_POST["Data7"]) and isset($_POST["Data8"]))
            {
            if($_POST["Type"]!="" and $_POST["Data"]!="" and $_POST["Data2"]!="" and $_POST["Data3"]!="" and $_POST["Data4"]!="" and $_POST["Data5"]!="" and $_POST["Data6"]!="" and $_POST["Data8"]!="")
                {
                if($_POST["Data7"]=="")
                    $_POST["Data7"]="0000-00-00";
                if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE id='".mysql_real_escape_string($_POST["Data2"])."'"))!=1 or mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='3' AND id='".mysql_real_escape_string($_POST["Data3"])."'"))!=1)
                    {
                    echo -12;
                    exit;
                    }
                if($_POST["Type"]=="1")
                    {
                    $QUERY="INSERT `$_SYSTEM_TIMETABLE_TABLE` (`id`, `class`, `uid`, `tid`, `type`, `lesson`, `parent`, `day`, `number`, `from`, `to`) VALUES (NULL, '".((string)$_POST["Data8"]=="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".((string)$_POST["Data8"]!="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".mysql_real_escape_string($_POST["Data3"])."', '0', '".mysql_real_escape_string($_POST["Data2"])."', '0', '".mysql_real_escape_string($_POST["Data4"])."', '".mysql_real_escape_string($_POST["Data5"])."', '".mysql_real_escape_string($_POST["Data6"])."', '".(strtotime($_POST["Data6"])>strtotime($_POST["Data7"])?mysql_real_escape_string($_POST["Data6"]):mysql_real_escape_string($_POST["Data7"]))."');";
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='1' AND id='".mysql_real_escape_string($_POST["Data"])."'"))==1)
                        {
                        if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE WHERE uid='".mysql_real_escape_string($_POST["Data"])."' AND tid='".mysql_real_escape_string($_POST["Data3"])."' AND lesson='".mysql_real_escape_string($_POST["Data2"])."'"))!=1)
                            if(mysql_query($QUERY))
                                echo mysql_query("INSERT `$_SYSTEM_TEACHES_TABLE` (`id`, `tid`, `uid`, `lesson`) VALUES (NULL, '".mysql_real_escape_string($_POST["Data3"])."', '".mysql_real_escape_string($_POST["Data"])."', '".mysql_real_escape_string($_POST["Data2"])."')");
                                else
                                echo -1;
                            else
                            echo mysql_query($QUERY);
                        }else
                        echo -1;
                    }else{
                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($_POST["Data"])."'"))==1) {
                        if(mysql_query("INSERT `$_SYSTEM_TIMETABLE_TABLE` (`id`, `class`, `uid`, `tid`, `type`, `lesson`, `parent`, `day`, `number`, `from`, `to`) VALUES (NULL, '".((string)$_POST["Data8"]=="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".((string)$_POST["Data8"]!="1"?mysql_real_escape_string($_POST["Data"]):0)."', '".mysql_real_escape_string($_POST["Data3"])."', '0', '".mysql_real_escape_string($_POST["Data2"])."', '0', '".mysql_real_escape_string($_POST["Data4"])."', '".mysql_real_escape_string($_POST["Data5"])."', '".mysql_real_escape_string($_POST["Data6"])."', '".((strtotime($_POST["Data6"])<strtotime($_POST["Data7"]) or $_POST["Data7"]=="0000-00-00")?mysql_real_escape_string($_POST["Data7"]):mysql_real_escape_string($_POST["Data6"]))."');")) {
                            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE status='1' AND class='".mysql_real_escape_string($_POST["Data"])."'");
                            while($row=mysql_fetch_array($ADAT))
                                if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TEACHES_TABLE WHERE uid='".mysql_real_escape_string($row["id"])."' AND tid='".mysql_real_escape_string($_POST["Data3"])."' AND lesson='".mysql_real_escape_string($_POST["Data2"])."'"))!=1)
                                    mysql_query("INSERT `$_SYSTEM_TEACHES_TABLE` (`id`, `tid`, `uid`, `lesson`) VALUES (NULL, '".mysql_real_escape_string($_POST["Data3"])."', '".mysql_real_escape_string($row["id"])."', '".mysql_real_escape_string($_POST["Data2"])."')");
                            echo 1;
                        }else
                        echo -1;
                    }else
                    echo -1;
                }
            }else
            echo -1;
            }else
            echo -1;                
        break;
    default:
        echo -1;
        break;
    }
?>