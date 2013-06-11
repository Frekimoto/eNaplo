<?php
header("Content-Type: text/html; charset=UTF-8");
ob_start();
session_start();
if(file_exists("config.php"))
    {
    include_once("config.php");
    if(file_exists("install.php"))
        unlink("install.php");
    }else{
    if(!file_exists("install.php"))
        echo "A rendszer nincs telepítve és nem találom a telepítőfájlt.<br />Kérem másolja fel az \"index.php\" fájl mellé az \"install.php\" fájlt.";
            else
            header("Location: install.php");
    exit;
    }
if(!isset($_SESSION["ID"]))
    $_SESSION["ID"]=-1;
$_LE="";
if(isset($_POST["Login_Username"]) and isset($_POST["Login_Password"]) and $_SESSION["ID"]==-1)
    if($_POST["Login_Username"]=="" or $_POST["Login_Password"]=="")
        $_LE="Minden mező kitöltése kötelező!";
            else{
            $row=@mysql_fetch_array(@mysql_query("SELECT * FROM `$_SYSTEM_USERS_TABLE` WHERE `username` = '".mysql_real_escape_string($_POST['Login_Username'])."' AND `pass` = '".sha1(md5($_POST['Login_Password']))."'"));
            @mysql_free_result($row);
            if(!empty($row['id']))
                $_SESSION["ID"]=$row["id"];
                    else
                    $_LE="Hibás felhasználónév és/vagy jelszó.";
            }
if(isset($_GET["exit"]) and $_SESSION["ID"]!=-1)
    $_SESSION["ID"]=-1;
if($_SESSION["ID"]!=-1)
    {
    $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='". $_SESSION["ID"] ."' LIMIT 1");
    while($row=mysql_fetch_array($ADAT))
        {
        $_SESSION["USERNAME"]   = $row["username"];
        $_SESSION["RANK"]       = $row["rank"];
        $_SESSION["REAL_NAME"]  = $row["real_name"];
        $_SESSION["PARENT"]     = $row["parent"];
        $_SESSION["CLASS"]      = $row["class"];
        }
    }
?>
<!DOCTYPE html>
<html lang="hu">
    <head>
        <title>e-Napló</title>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <link href="favicon.ico" rel="icon" type="image/x-icon" />
        <script src="jquery-1.8.3.js"></script>
        <script src="jquery-ui-1.9.2.js"></script>
		<script src="jquery.printPage.js" type="text/javascript"></script>		
        <script type="text/javascript">
        $(document).ready(function()
            {
            <?php if($_SESSION["ID"]==-1) { ?>
            if($("#Login_Error").html()!="")
                $("#Login_Error").show('fast');
            <?php }else{ 
            if($_SESSION["RANK"]==1 or $_SESSION["RANK"]==2){
            ?>
            $.post('ajax.php',{TYPE: 2},function(data){$('#Content').html(data);});
            <?php }else{
            if(((mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE tid='".$_SESSION["ID"]."'"))>0 or mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".$_SESSION["CLASS"]."'"))==1) and $_SESSION["RANK"]==3) or ($_SESSION["RANK"])==4 and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE"))>0)
                echo '$.post(\'ajax.php\',{TYPE: 3, '.(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE, $_SYSTEM_USERS_TABLE WHERE $_SYSTEM_TIMETABLE_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND $_SYSTEM_USERS_TABLE.class='".$_SESSION["CLASS"]."'"))>0?'Type: '.mysql_result(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE".($_SESSION["RANK"]==3?" WHERE tid='".$_SESSION["ID"]."'":"")),0,"id").', ':'').'Id: '.($_SESSION["CLASS"]!=0?$_SESSION["CLASS"]:mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE ORDER BY name ASC"),0,"id")).'},function(data){$(\'#Gardes\').html(data);});';
            if($_SESSION["RANK"]==4) { ?>
            $.post('ajax.php',{TYPE: 9},function(data){$('#User_Content').html(data);});
            Timetable_Input();
            <?php } ?>
            NewUserTypes=new Array(1,"Student","Parent","Teacher","Administrator");
			$(".Print").printPage({
			      attr : "href",
				  url : false,
				  message: "Várj amíg létrehozzuk a nyomtatandó állományt." 
				});
            <?php } } ?>
            });
            <?php if($_SESSION["ID"]==-1) { ?>
            function Login()
                {
                Empty=0;
                $('input.required').each(
                    function()
                        {
                        if($(this).val()=='')
                            Empty=1;
                        });
                if(Empty)
                    {
                    $('#Login_Div').addClass('login-error')//.effect('shake', {times: 5}, 500);
					var options = {
										direction: 'left',
										distance: 20,
										times: 5
									};
									var left = $('.login-error').position().left > parseInt($('.login-error').css('margin-left')) ? $('.login-error').position().left : $('.login-error').css('margin-left');
									$('.login-error')
									.css({'margin-left': left})
									.effect('shake' , options , 500 , function(){
									$('.login-error').removeAttr('style');
									});
                    $('#Login_Error').html('Minden mező kitöltése kötelező!').show('fast');
                    }else{
                    $('#Login_Error').html('<div style="color: yellow;">Kis türelmet...</div>').show('fast');
					$('#Login_Username,#Login_Password').attr('disabled',true);
                    $.post('ajax.php',
                        {
                            TYPE: 1,
                            U: $('#Login_Username').val(),
                            P: $('#Login_Password').val()
                        },
                        function(data)
                            {
                            if(data=='1')
                                {
                                $('#Login_Error').html('<div style="color: green;">Átirányítás folyamatban...</div>');
                                location.href='index.php';
                                }else
                                if(data=='2')
                                    {
                                    $('#Login_Error').html('Minden mező kitöltése kötelező!').show('fast');
                                    $('#Login_Div').addClass('login-error')/*.effect('shake', {times: 5}, 500);*/
									var options = {
										direction: 'left',
										distance: 10,
										times: 5
									};
									var left = $('.login-error').position().left > parseInt($('.login-error').css('margin-left')) ? $('.login-error').position().left : $('.login-error').css('margin-left');
									$('.login-error')
									.css({'margin-left': left})
									.effect('shake' , options , 500 , function(){
									$('.login-error').removeAttr('style');
									});
                                        }else
                                        if(data=='3')
                                            {
                                            $('#Login_Error').html('Hibás felhasználónév és/vagy jelszó.').show('fast');
                                            $('#Login_Div').addClass('login-error')/*.effect('shake', {times: 5}, 500);*/
									var options = {
										direction: 'left',
										distance: 10,
										times: 5
									};
									var left = $('.login-error').position().left > parseInt($('.login-error').css('margin-left')) ? $('.login-error').position().left : $('.login-error').css('margin-left');
									$('.login-error')
									.css({'margin-left': left})
									.effect('shake' , options , 500 , function(){
									$('.login-error').removeAttr('style');
									});
                                            }
								$('#Login_Username,#Login_Password').attr('disabled',false);
                            });
                    }
                }
            <?php }else{ ?>
                function Message(Message,Delay,Div,Color) {
                    if(Delay<=0)
                        $("#"+Div).html('<p>'+(Color!=null?'<font color="'+Color+'">'+Message+'</font>':Message)+'</p>').fadeIn(1000);
                        else
                        $("#"+Div).html('<p>'+(Color!=null?'<font color="'+Color+'">'+Message+'</font>':Message)+'</p>').fadeIn(1000).delay(Delay-2000).fadeOut(1000,function(){$(this).empty();});
                }
            $.post('ajax.php',{TYPE: 12, Type: <?php $CLASS=mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".mysql_real_escape_string($_SESSION["CLASS"])."'")); echo $_SESSION["RANK"]==3?1:($CLASS!=1?2:3); ?>, Id: <?php  echo ($CLASS>0 or mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE"))==0)?$_SESSION["ID"]:mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE,$_SYSTEM_TIMETABLE_TABLE WHERE $_SYSTEM_CLASSES_TABLE.id=$_SYSTEM_TIMETABLE_TABLE.class AND enabled='1' ORDER BY $_SYSTEM_CLASSES_TABLE.name ASC"),0,"$_SYSTEM_CLASSES_TABLE.id"); ?>},function(data){$('#DTimetable').html(data);});
            <?php if($_SESSION["RANK"]==4){ ?>
            function Timetable_Input() {
                Message('',0,'Timetable_Error');
                $('.Bracket,#Timetable_Manager_Content,#TStudent,#TClass').show();
				$('.Bracket').hide();
                
                if($('#NewT_Class option').length==1) { //&& $('#NewT_Student option').length==1
                   $('#Timetable_Manager_Content').hide();
                   Message('Adjon meg egy osztályt',0,'Timetable_Error'); //, vagy egy tanulót.
                   return false;  
                }
                if($('#NewT_Class option').length==1)
                    $('.Bracket,#TClass').hide();

                if($('#NewT_Student option').length==1)
                    $('.Bracket,#TStudent').hide();

                if($('#NewT_Lesson option').length==1) {
                    $('#Timetable_Manager_Content').hide();
                        Message('Adjon meg egy tanórát.',0,'Timetable_Error');
                        return false;
                }
                if($('#NewT_Lesson option').length==1) {
                    $('#Timetable_Manager_Content').hide();
                        Message('Adjon meg egy tanórát.',0,'Timetable_Error');
                        return false;
                }

                if($('#NewT_Teacher option').length==1) {
                    $('#Timetable_Manager_Content').hide();
                        Message('Adjon meg egy tanárt.',0,'Timetable_Error');
                        return false;
                }
            return true;
            }
            <?php } } ?>
        </script>
    </head>
        <?php if($_SESSION["ID"]==-1) { ?>
            <h3>Azonosítás</h3>
			<div class="Menu">  </div>
			<div class="container">
				<div id="Login_Div" class="login">
					<form action="index.php" method="POST">
					<div id="Login_Error" class="Error"><?php echo $_LE; ?></div>
					<label for="Login_Username">Felhasználónév: </label><input type="Text" id="Login_Username" name="Login_Username" value="" autofocus="autofocus" autocomplete="off" placeholder="Felhasználónév" class="required"></input><br />
					<label for="Login_Password">Jelszó: </label><input type="Password" id="Login_Password" name="Login_Password" value="" autocomplete="off" placeholder="Jelszó" class="required"></input><br />
					<input type="Button" value="Bejelentkezés" onClick="Login(); return false;"/>
					</form>
				</div>
			</div>
        <?php }else{ ?>
            <h1>Üdvözlünk <i><?php echo $_SESSION["REAL_NAME"]; ?></i>!</h1>
            <div class="Menu"><a href="javascript:void(0);" onClick="<?php if($_SESSION["RANK"]==1 or $_SESSION["RANK"]==2)echo '$.post(\'ajax.php\',{TYPE: 2},function(data){$(\'#Content\').html(data);});'; ?> $('#Content').toggle('fast'); $('#Other_Manager,#Profile_Manager,#Timetable,#Notes,#Exit').hide('fast');">Napló</a>  <a href="javascript:void(0);" onClick="$('#Notes').toggle('fast'); $('#Timetable,#Content,#Other_Manager,#Profile_Manager,#Exit').hide('fast');">Jegyzőkönyv</a> <a href="javascript:void(0);" onClick="$('#Timetable').toggle('fast'); $('#Content,#Other_Manager,#Profile_Manager,#Notes,#Exit').hide('fast');">Órarend</a><?php if($_SESSION["RANK"]!=4){ ?> <a href="javascript:void(0);" onClick="$('#Profile_Manager').toggle('fast'); $('#Content,#Other_Manager,#Timetable,#Notes,#Exit').hide('fast');">Profil</a><?php }else{ ?> <a href="javascript:void(0);" onClick="$('#Other_Manager').toggle('fast'); $('#Content,#Profile_Manager,#Timetable,#Notes,#Exit').hide('fast');">Egyéb</a><?php } ?> <a href="javascript:void(0);" onClick="$('#Exit').toggle('fast'); $('#Content,#Other_Manager,#Timetable,#Notes,#Profile_Manager').hide('fast');">Kilépés</a></div><br />
			<div class="container">
            <div id="Notes" style="display: none;">
            <?php
            if(isset($_POST["ID"]) and  ($_SESSION["RANK"]==3 or $_SESSION["RANK"]==4))
                {
                mysql_query("INSERT `$_SYSTEM_DELAY_TABLE` (`id`, `uid`, `tid`, `typ`, `description`, `delay`, `fromc`, `toc`, `fromdate`, `todate`) VALUES ( NULL, '".mysql_real_escape_string($_POST["ID"])."', '".mysql_real_escape_string($_SESSION["ID"])."', '".mysql_real_escape_string($_POST["Type"])."', '".mysql_real_escape_string(isset($_POST["Desc"])?$_POST["Desc"]:0)."', '".mysql_real_escape_string($_POST["Value"])."', '".mysql_real_escape_string($_POST["From"])."', '".mysql_real_escape_string($_POST["To"])."', '".mysql_real_escape_string($_POST["Dat"])."', '".mysql_real_escape_string($_POST["Dat2"])."')");
                echo "<script>location.href='index.php';</script>";
                }
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_DELAY_TABLE WHERE uid='".$_SESSION["ID"]."'"))==0)
                echo "<h5>Még nincs egyetlen késés/hiányzás sem.</h5>";
                else{
                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_DELAY_TABLE WHERE uid='".$_SESSION["ID"]."' ORDER BY fromdate ASC");                
                while($row=mysql_fetch_array($ADAT))
                    echo "<p>".($row["description"]==""?"Nincs leírás.":$row["description"])." (".$row["fromdate"].($row["todate"]!="0000-00-00"?" - ".$row["todate"]:"")." - ".($row["fromc"]<$row["toc"]?$row["fromc"]:$row["toc"]).($row["fromc"]==$row["toc"]?"":"-".($row["fromc"]<$row["toc"]?$row["toc"]:$row["fromc"])).". óra - ".($row["typ"]==1?"Igazolatlan késés (".$row["delay"]." perc)":($row["typ"]==2?"Igazolt késés (".$row["delay"]." perc)":($row["typ"]==4?"Igazolatlan hiányzás":($row["typ"]==3?"Igazolt hiányzás":"Hiba!"))))." - ".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE id='".$row["tid"]."'"), 0, "real_name").")</p>";
                }
            if($_SESSION["RANK"]==3 or $_SESSION["RANK"]==4)
                {
                echo "<form id='SF' action='index.php' method='post'><select name='ID'>";
                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE rank='1' ORDER BY real_name ASC");
                while($row=mysql_fetch_array($ADAT))
                    echo "<option value='".$row["id"]."'>".$row["real_name"]."</option>\n";
                echo "</select>";
            ?>
            <select name='Type' onChange="if($(':selected',this).val()==1 || $(':selected',this).val()==2)$('#NewCertificate').show(); else $('#NewCertificate').hide();">
                <option value="1">Igazolatlan késés</option>
                <option value="2">Igazolt késés</option>
                <option value="3">Igazolt hiányzás</option>
                <option value="4">Igazolatlan hiányzás</option>
            </select>
            <input placeholder="Perc" size="2" id="NewCertificate" name='Value'/>
            <input placeholder="Egyéb leírás..." name='Desc'/>
            <input placeholder="Ettől az órától" name='From'/>
            <input placeholder="Eddig az óráig" name='To'/>
            <input type="Date" name="Dat" min="<?php echo date("Y-m-d",$_FROM_DATE); ?>" max="<?php echo date("Y-m-d",$_TO_DATE); ?>" placeholder="Dátum (ÉÉÉÉ-HH-NN)"/>
            <input type="Date" name="Dat2" min="<?php echo date("Y-m-d",$_FROM_DATE); ?>" max="<?php echo date("Y-m-d",$_TO_DATE); ?>" placeholder="Dátum (ÉÉÉÉ-HH-NN)"/>
            <input type="Button" value="Hozzáad" onClick="$('#SF').submit();" name="NewCertificate">
            </form>
            <?php } ?>
            </div>
            <div id="Timetable" style="display: none;">
            <?php
            if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE"))==0)
                 echo "<h5>Még nincs órarend megadva.</h5>";
                 else
                if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE"))==0)
                    echo "<h5>Hozzon létre osztályokat!</h5>";
            ?>
            <div id="DTimetable">Kis türelmet...</div>
            </div>
            <?php if($_SESSION["RANK"]!=4) { ?>
            <div id="Profile_Manager" style="display: none;">
                <div id="Profile_Error"></div>
                <label for="Profile_Username">Felhasználónév: </label><input id="Profile_Username" type="text" disabled="disabled" value="<?php echo $_SESSION["USERNAME"]; ?>"><br />
                <label for="Profile_RealName">Valódi név: </label><input id="Profile_RealName" type="text" disabled="disabled" value="<?php echo $_SESSION["REAL_NAME"]; ?>"><br />
                <label for="Profile_OldPassword">Régi jelszó: </label><input id="Profile_OldPassword" type="Password" placeholder="Régi jelszó" value=""><br />
                <label for="Profile_NewPassword">Új jelszó: </label><input id="Profile_NewPassword" type="Password" placeholder="Új jelszó" value=""><br />
                <label for="Profile_NewPasswordAgain">Új jelszó ismét: </label><input id="Profile_NewPasswordAgain" type="Password" placeholder="Új jelszó ismét" value=""><br />
                <input type="Button" value="Mentés" onClick="if($('#Profile_NewPassword').val()=='' || $('#Profile_NewPasswordAgain').val()==''|| $('#Profile_OldPassword').val()=='')Message('Nem töltötted ki a mezőket!',5000,'Profile_Error','red'); else if($('#Profile_NewPassword').val()!=$('#Profile_NewPasswordAgain').val())Message('A megadott jelszavak nem egyeznek.',5000,'Profile_Error','red'); else if($('#Profile_NewPassword').val()==$('#Profile_OldPassword').val())Message('A régi és új jelszó nem egyezhet.',5000,'Profile_Error','red'); else{$('#Profile_NewPasswordAgain,#Profile_NewPassword,#Profile_OldPassword').attr('disabled',true); $.post('ajax.php',{TYPE: 7, P: $('#Profile_OldPassword').val(), NP: $('#Profile_NewPassword').val(), NPA: $('#Profile_NewPasswordAgain').val()},function(data){$('#Profile_NewPasswordAgain,#Profile_NewPassword,#Profile_OldPassword').val(''); if(data=='1')Message('Profil módosítva',5000,'Profile_Error','green'); else if(data=='-2')Message('Nem töltötted ki a mezőket!',5000,'Profile_Error','red'); else if(data=='-3')Message('A régi jelszó helytelen',5000,'Profile_Error','red'); else if(data=='-4')Message('A régi és új jelszó nem egyezhet.',5000,'Profile_Error','red'); else if(data=='-5')Message('A jelszavak nem egyeznek.',5000,'Profile_Error','red'); else Message('Hiba!',5000,'Profile_Error','red'); $('#Profile_NewPasswordAgain,#Profile_NewPassword,#Profile_OldPassword').attr('disabled',false);});}"/>
            </div>
            <?php }else{ ?>
                <div id="Other_Manager" style="display: none;">
					<div class="Sub-Menu">
						<a href="javascript:void(0);" onClick="$('#Lesson_Manager').toggle('fast'); $('#User_Manager,#Class_Manager,#Timetable_Manager').hide('fast');">Tanórakezelő</a>
						<a href="javascript:void(0);" onClick="$('#User_Manager').toggle('fast'); $('#Lesson_Manager,#Class_Manager,#Timetable_Manager').hide('fast');">Felhasználókezelő</a>
						<a href="javascript:void(0);" onClick="$('#Class_Manager').toggle('fast'); $('#Lesson_Manager,#User_Manager,#Timetable_Manager').hide('fast');">Osztálykezelő</a>
						<a href="javascript:void(0);" onClick="$('#Timetable_Manager').toggle('fast'); $('#Lesson_Manager,#User_Manager,#Class_Manager').hide('fast');">Órarendkezelő</a>
					</div>
                    <div id="Class_Manager" style="display: none;">
                        <div id="Class_Error"></div>
                         <label for="New_Class">Osztály:</label> <input type="Text" placeholder="Osztály" id="New_Class" />
                         <label for="Edit_Class_Enabled">Látható</label><input type="radio" name="Edit_Class_Status" id="Edit_Class_Enabled" value="1" checked="checked" />
                         <label for="Edit_Class_Disabled">Rejtett</label><input type="radio" name="Edit_Class_Status" id="Edit_Class_Disabled" value="0" />
                         <input type="Button" value="Hozzáadás" onClick="if($('#New_Class').val()=='')Message('Nem töltötted ki a mezőt!',5000,'Class_Error','red'); else $.post('ajax.php',{TYPE: 5, Text: $('#New_Class').val(), Type: 1, Data: +$('input:radio[name=\'Edit_Class_Status\']').prop('checked')},function(data){if(data=='-1')Message('Hiba!',5000,'Class_Error','red'); else if(data=='-2')Message('Ilyen osztály már létezik.',5000,'Class_Error','red'); else if(data=='-3')Message('Nem töltötted ki a mezőt!',5000,'Class_Error','red'); else{Classes[$('#Edit_Class option').length]=+$('input:radio[name=\'Edit_Class_Status\']').prop('checked'); if(Classes[$('#Edit_Class option').length])$('.Class').show('fast'); $('#Edit_Class,#Delete_Classes,#NewT_Class'+(+$('input:radio[name=\'Edit_Class_Status\']').prop('checked')?',#Class_To_Teacher optgroup,#Class_To_Student optgroup':'')).append($('<option></option>').attr('value', data).text($('#New_Class').val())); $('#New_Class').val(''); if($('#Delete_Classes option').length==1)$('#Classes_Div').show('fast'); Message('Új osztály hozzáadva',5000,'Class_Error','green'); Timetable_Input();}});"/><br />
                         <div id="Classes_Div"<?php echo mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE"))==0?" style=\"display: none;\"":""; ?>>
                             <select id="Edit_Class" onChange="$('input:radio[name=\'Edit_Class_Status\'][value=\''+Classes[$(this).prop('selectedIndex')]+'\']').prop('checked', true); if($(':selected',this).val()!='-'){$('#New_Class').val($('#Edit_Class option:selected').text()); $('#New_Class_Button').attr('disabled', false);}else $('#New_Class_Button').attr('disabled', true);">
                                <option value="-"> - </option>
                                <?php
                                $Classes="";
                                $Default="";
                                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE ORDER BY name ASC");
                                while($row=mysql_fetch_array($ADAT)) {
                                   $Classes.="<option value='".$row["id"]."'>".$row["name"]."</option>\n";
                                    $Default.=",".$row["enabled"];
                                }
                                echo $Classes."<script>Classes = new Array(1".$Default.");</script>";
                                ?>
                            </select>
                            <input type="Button" value="Módosítás" id="New_Class_Button" onClick="if($('#New_Class').val().length==0)Message('Nem töltötted ki a mezőt!',5000,'Lesson_Error','red'); else if($('#New_Class').val()==$('#Edit_Class :selected').text() && Classes[$('#Edit_Class').prop('selectedIndex')]==+$('input:radio[name=\'Edit_Class_Status\']').prop('checked'))Message('Adj meg egy másik nevet, vagy változtasd meg a láthatóságot.',5000,'Class_Error','red'); else $.post('ajax.php',{TYPE: 6, Id: $('#Edit_Class :selected').val(), Text: $('#New_Class').val(), Type: 1, Data: +$('input:radio[name=\'Edit_Class_Status\']').prop('checked')},function(data){if(data=='-2')Message('Adj meg egy másik nevet, vagy változtasd meg a láthatóságot.',5000,'Class_Error','red'); else if(data=='-3')Message('Nem töltötted ki a mezőt!.',5000,'Class_Error','red'); else if(data=='1'){if(Classes[$('#Edit_Class').prop('selectedIndex')]==1 && +$('input:radio[name=\'Edit_Class_Status\']').prop('checked')==0){$('#Class_To_Student option[value=\''+($('#Edit_Class :selected').val())+'\'],#Class_To_Teacher option[value=\''+($('#Edit_Class :selected').val())+'\']').remove(); if($('#Class_To_Teacher option').length==1)$('.Class').hide();} else if(Classes[$('#Edit_Class').prop('selectedIndex')]==0 && +$('input:radio[name=\'Edit_Class_Status\']').prop('checked')==1){$('#Class_To_Teacher optgroup,#Class_To_Student optgroup').append($('<option></option>').attr('value', $('#Edit_Class :selected').val()).text($('#New_Class').val())); $('.Class').show();} $('#Edit_Class option[value=\''+$('#Edit_Class :selected').val()+'\'],#Delete_Classes option[value=\''+$('#Edit_Class :selected').val()+'\'],#NewT_Class option[value=\''+$('#Edit_Class :selected').val()+'\']').text($('#New_Class').val()); Classes[$('#Edit_Class').prop('selectedIndex')]=+$('input:radio[name=\'Edit_Class_Status\']').prop('checked'); Message('Módosítva!',5000,'Class_Error','green');}else Message('Hiba!',5000,'Class_Error','red');});" disabled="disabled"/><br />
                            <select id="Delete_Classes" onChange="if($('#Delete_Classes :selected').length==0)$('#Delete_Class').val('Válasszon elemet!').attr('disabled', true); else $('#Delete_Class').val($('#Delete_Classes :selected').length+' elem törlése').attr('disabled', false);" multiple="multiple">
                                <?php echo $Classes; ?>
                            </select><br />
                            <input type="Button" id="Delete_Class" value="Válasszon elemet!" onClick="if($('#Delete_Classes :selected').length!=0)if(confirm('Biztosan törölsz '+($('#Delete_Classes :selected').length)+' elemet?')){Text=''; $('#Delete_Classes :selected').each(function(index, value){if(Text!='')Text+=','; Text+=$(value).val();}); $.post('ajax.php',{TYPE: 4, Text: Text, Type: 1},function(data){if(data=='1'){$('#Delete_Class').val('Válasszon elemet!').attr('disabled', true); $('#Delete_Classes :selected').each(function(index, value){Classes.splice($('#Delete_Classes option[value=\''+($(value).val())+'\']').prop('index')+1,1); $('#Edit_Class option[value=\''+($(this).val())+'\'],#NewT_Class option[value=\''+($(this).val())+'\']').remove();}); $('#Delete_Classes :selected').remove(); if($('#Delete_Classes option').length==0)$('#Classes_Div, .Class').hide('fast'); Message('Osztály(ok) törölve',5000,'Class_Error','green'); Timetable_Input();}else Message('Hiba a folyamatban!',5000,'Class_Error','red');});}" disabled="disabled"/>
                         </div>
                    </div>
                    <div id="Lesson_Manager" style="display: none;">
                        <div id="Lesson_Error"></div>
                         <label for="New_Lesson">Tanóra:</label> <input type="Text" id="New_Lesson" placeholder="Tanóra"/>
                         <label for="Edit_Lesson_Enabled">Látható</label><input type="radio" name="Edit_Lesson_Status" id="Edit_Lesson_Enabled" value="1" checked="checked" />
                         <label for="Edit_Lesson_Disabled">Rejtett</label><input type="radio" name="Edit_Lesson_Status" id="Edit_Lesson_Disabled" value="0" />
                         <input type="Button" value="Hozzáadás" onClick="if($('#New_Lesson').val()=='')Message('Nem töltötted ki a mezőt!',5000,'Lesson_Error','red'); else $.post('ajax.php',{TYPE: 5, Type: 0, Data: +$('input:radio[name=\'Edit_Lesson_Status\']').prop('checked'), Text: $('#New_Lesson').val()},function(data){if(data=='-1')Message('Hiba!',5000,'Lesson_Error','red'); else if(data=='-2')Message('Ilyen óra már létezik.',5000,'Lesson_Error','red'); else if(data=='-3')Message('Nem töltötted ki a mezőt!',5000,'Lesson_Error','red'); else{$('#Edit_Lesson,#Delete_Lessons'+(+$('input:radio[name=\'Edit_Lesson_Status\']').prop('checked')?',#NewT_Lesson':'')).append($('<option></option>').attr('value', data).text($('#New_Lesson').val())); $('#New_Lesson').val(''); Lessons[$('#Edit_Lesson option').length-1]=+$('input:radio[name=\'Edit_Lesson_Status\']').prop('checked'); if($('#Delete_Lessons option').length==1)$('#Lessons_Div').show('fast'); Message('Új óra hozzáadva',5000,'Lesson_Error','green'); Timetable_Input();}});"/>
                         <div id="Lessons_Div"<?php echo mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE"))==0?" style=\"display: none;\"":""; ?>>
                             <select id="Edit_Lesson" onChange="$('input:radio[name=\'Edit_Lesson_Status\'][value=\''+Lessons[$(this).prop('selectedIndex')]+'\']').prop('checked', true); if($(':selected',this).val()!='-'){$('#New_Lesson').val($(':selected',this).text()); $('#New_Lesson_Update').attr('disabled', false);}else $('#New_Lesson_Update').attr('disabled', true);">
                                <option value="-"> - </option>
                                <?php
                                $DefaulT="";
                                $Default="";
                                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE ORDER BY name ASC");
                                while($row=mysql_fetch_array($ADAT)) {
                                    $DefaulT.="<option value='".$row["id"]."'>".$row["name"]."</option>\n";
                                    $Default.=",".$row["enabled"];
                                }
                                echo $DefaulT."<script>Lessons = new Array(1".$Default.");</script>";
                                ?>
                            </select>
                         <input type="Button" value="Módosítás" id="New_Lesson_Update" onClick="if($('#New_Lesson').val()=='')Message('Nem töltötted ki a mezőt!',5000,'Lesson_Error','red'); else if($('#New_Lesson').val()==$('#Edit_Lesson option:selected').text() && Lessons[$('#Edit_Lesson').prop('selectedIndex')]==+$('input:radio[name=\'Edit_Lesson_Status\']').prop('checked'))Message('Adj meg egy másik nevet, vagy változtasd meg a láthatóságot.',5000,'Lesson_Error','red'); else $.post('ajax.php',{TYPE: 6, Type: 0, Data: +$('input:radio[name=\'Edit_Lesson_Status\']').prop('checked'), Id: $('#Edit_Lesson :selected').val(), Text: $('#New_Lesson').val()},function(data){if(data=='-2')Message('Adj meg egy másik nevet, vagy változtasd meg a láthatóságot.',5000,'Lesson_Error','red'); else if(data=='-3')Message('Nem töltötted ki a mezőt!.',5000,'Lesson_Error','red'); else if(data=='1'){$('#Edit_Lesson option[value=\''+$('#Edit_Lesson :selected').val()+'\'],#Delete_Lessons option[value=\''+$('#Edit_Lesson :selected').val()+'\'],#NewT_Lesson option[value=\''+$('#Edit_Lesson :selected').val()+'\']').text($('#New_Lesson').val()); Lessons[$('#Edit_Lesson').prop('selectedIndex')]=+$('input:radio[name=\'Edit_Lesson_Status\']').prop('checked'); Message('Módosítva!',5000,'Lesson_Error','green'); Timetable_Input();}else Message('Hiba!',5000,'Lesson_Error','red');});" disabled="disabled"/><br />
                            <select id="Delete_Lessons" onChange="if($('#Delete_Lessons :selected').length==0){$('#Delete_Lesson').val('Válasszon elemet!').attr('disabled', true);}else{$('#Delete_Lesson').val($('#Delete_Lessons :selected').length+' elem törlése').attr('disabled', false);}" multiple="multiple">
                                <?php echo $DefaulT; ?>
                            </select><br />
                            <input type="Button" id="Delete_Lesson" value="Válasszon elemet!" onClick="if($('#Delete_Lessons :selected').length!=0)if(confirm('Biztosan törölsz '+($('#Delete_Lessons :selected').length)+' elemet?')){Text=''; $('#Delete_Lessons :selected').each(function(index, value){if(Text!='')Text+=','; Text+=$(value).val();}); $.post('ajax.php',{TYPE: 4, Text: Text},function(data){if(data=='1'){$('#Delete_Lessons :selected').each(function(index, value){Lessons.splice($('#Delete_Lessons option[value=\''+($(value).val())+'\']').prop('index')+1,1); $('#Edit_Lesson option[value=\''+($(value).val())+'\'],#NewT_Lesson option[value=\''+($(value).val())+'\'],#Delete_Lessons option[value=\''+($(value).val())+'\']').remove();}); $('#Delete_Lesson').val('Válasszon elemet!').attr('disabled', true); if($('#Delete_Lessons option').length==0)$('#Lessons_Div').hide('fast'); $('#New_Lesson').change(); Message('Órák törölve',5000,'Lesson_Error','green'); Timetable_Input();}else Message('Hiba a folyamatban!',5000,'Lesson_Error','red');});}" disabled="disabled"/>
                         </div>
                    </div>
                    <div id="User_Manager" style="display: none;">
                        <div id="User_Error"></div>
                        <div id="User_Content">Kis türelmet...</div>
                            <div id="Users_Div">
                                 <select id="Edit_User" onChange="if($(':selected',this).val()!='-'){$('#User_Form').find('input, textarea, button, select').attr('disabled',true); $.post('ajax.php',{TYPE: 9, Type: $(':selected',this).val()},function(data){$('#User_Content').html(data); $('#Change_User').attr('disabled', false);});}else $('#Change_User').attr('disabled', true);">
                                 <option value="-"> - </option>
                                 <?php
                                 $Users="";
                                 $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE ORDER BY real_name ASC");
                                 while($row=mysql_fetch_array($ADAT))
                                    {
                                    echo "<option value='".$row["id"]."'>(".$row["username"]." - ".($row["rank"]==4?"A":($row["rank"]==3?"T":($row["rank"]==2?"Sz":"D"))).") ".$row["real_name"]."</option>\n";
                                    $Users.="<option value='".$row["id"]."'".($_SESSION["ID"]==$row["id"]?" disabled":"").">(".$row["username"]." - ".($row["rank"]==4?"A":($row["rank"]==3?"T":($row["rank"]==2?"Sz":"D"))).") ".$row["real_name"]."</option>\n";
                                    if($Default=="")$Default=$row["name"];
                                    }                                    
                                 ?>
                                </select><input type="Button" value="Módosítás" id="Change_User" onClick="if($('#User_Username').val()=='' || $('#User_RealName').val()=='')Message('Nem töltötted ki az összes mezőt.',5000,'User_Error','red'); else{$('#User_Form').find('input, textarea, button, select').attr('disabled',false); a=$('#User_Form').serialize(); $('#User_Form').find('input, textarea, button, select').attr('disabled',true); $.post('ajax.php',{TYPE: 11, Type: $('#Edit_User :selected').val(), Data: a},function(a){if(a=='-2')Message('Nem töltötted ki az összes mezőt.',5000,'User_Error','red'); else if(a=='-4')Message('Rossz OM azonosító formátum.',5000,'User_Error','red'); else if(a=='-5')Message('Ez az OM azonosító már használatban van.',5000,'User_Error','red'); else if(a=='-3')Message('Ez a felhasználónév már foglalt.',5000,'User_Error','red'); else if(a=='1'){Message('Felhasználó módosítva!',5000,'User_Error','green'); User='('+$('#User_Username').val()+' - '; if($('#User_Rank :selected').val()=='2')User+='Sz';  else if($('#User_Rank :selected').val()=='3')User+='T'; else if($('#User_Rank :selected').val()=='4')User+='A'; else User+='D'; User+=') '+$('#User_RealName').val(); $('#User_Password').val(''); $('#Edit_User :selected,#Delete_Users option[value='+($('#Edit_User :selected').val())+']').html(User); $('#NewT_Student option[value='+($('#Edit_User :selected').val())+'],#NewT_Teacher option[value='+($('#Edit_User :selected').val())+'],#Parent_To_Student option[value='+($('#Edit_User :selected').val())+']').remove(); if($('#User_Rank :selected').val()=='2')$('#Parent_To_Student optgroup').append($('<option></option>').attr('value', $('#Edit_User :selected').val()).text('('+$('#User_Username').val()+') '+$('#User_RealName').val())); else if($('#User_Rank :selected').val()=='3')$('#NewT_Teacher').append($('<option></option>').attr('value', $('#Edit_User :selected').val()).text($('#User_RealName').val())); else if($('#User_Rank :selected').val()=='1')$('#NewT_Student').append($('<option></option>').attr('value', $('#Edit_User :selected').val()).text($('#User_RealName').val())); if($('#Parent_To_Student option').length>1)$('.Parent').show(); else $('.Parent').hide(); Timetable_Input();}else Message('Hiba!<br />'+a,5000,'User_Error','red'); $('#User_Form').find('input, textarea, button, select').attr('disabled',false);});}" disabled="disabled"/><br />
                                <select id="Delete_Users" onChange="if($('#Delete_Users :selected').length==0){$('#Delete_User').val('Válasszon felhasználót!').attr('disabled', true);}else{$('#Delete_User').val($('#Delete_Users :selected').length+' elem törlése').attr('disabled', false);}" multiple="multiple">
                                    <?php echo $Users; ?>
                                </select><br />
                                <input type="Button" id="Delete_User" value="Válasszon felhasználót!" onClick="if($('#Delete_Users :selected').length!=0)if(confirm('Biztosan törölsz '+($('#Delete_Users :selected').length)+' felhasználót?\nA hozzájuk tartozó adatok elvesznek.')){Text=''; $('#Delete_Users :selected').each(function(index, value){if(Text!='')Text+=','; Text+=$(value).val();}); $.post('ajax.php',{TYPE: 4, Text: Text, Type: 2},function(data){if(data=='1'){$('#Delete_User').val('Válasszon felhasználót!').attr('disabled', true); $('#Delete_Users :selected').each(function(index, value){$('#NewT_Student option[value='+($(value).val())+'],#NewT_Teacher option[value='+($(value).val())+'],#Parent_To_Student option[value='+($(value).val())+'],#Edit_User option[value='+($(value).val())+']').remove();}); $('#Delete_Users :selected').remove(); if($('#Parent_To_Student option').length==1)$('.Parent').hide(); Message('Felhasználó(k) törölve',5000,'User_Error','green'); Timetable_Input();}else Message('Hiba a folyamatban!',5000,'User_Error','red');});}" disabled="disabled"/>
                            </div>
                    </div>
                    <div id="Timetable_Manager" style="display: none;">
                    <div id="Timetable_Error"></div>
                    <div id="Timetable_Manager_Content">
                    <h5>Új órarendi pont hozzáadása</h5>
                    <label for="NewT_Day">Hét napja:</label> <select id="NewT_Day" onChange="$('#NewT_Lesson_Number').val(1);">
                        <?php
                            for($i=1; $i<=7; $i++)
                                echo "<option value='".$i."'>".iconv('iso-8859-2','utf-8',strftime("%A",strtotime(date("l",mktime(0,0,0,7,$i,2013)))))."</option>";
                        ?>
                    </select><br />
                    <label for="NewT_Lesson_Number">Óra száma:</label> <input type="number" id="NewT_Lesson_Number" maxlength="2" size="2" max="15" min="0" value="1" /><br />
                    <p class="Bracket" style="display: inline;">(</p>
                    <p id="TStudent" style="display: inline;">
                        <label for="NewT_Student" style="display: none;">Tanuló:</label> <select style="display: none;" id="NewT_Student" onChange="$('#NewT_Class').each(function(){$(this).val($('option:first',this).val());}); if($('#NewT_Teacher option:selected').val()!='' && $('#NewT_From').val()!='' && $('#NewT_Lesson option:selected').val()!='' && ($('#NewT_Student option:selected').val()!='' || $('#NewT_Class option:selected').val()!=''))$('#NewT_Button').attr('disabled',false); else $('#NewT_Button').attr('disabled',true);">
                            <option value="">-Válassz-</option>
                            <?php
                                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE rank='1' ORDER BY real_name ASC");
                                while($row=mysql_fetch_array($ADAT))
                                    echo "<option value='".$row["id"]."'>".$row["real_name"]."</option>\n";
                            ?>
                        </select>
                    </p><p class="Bracket" style="display: inline;"> vagy </p>
                    <p id="TClass" style="display: inline;">
                        <label for="NewT_Class">Osztály:</label> <select id="NewT_Class" onChange="$('#NewT_Student').each(function(){$(this).val($('option:first',this).val());}); if($('#NewT_Teacher option:selected').val()!='' && $('#NewT_From').val()!='' && $('#NewT_Lesson option:selected').val()!='' && ($('#NewT_Student option:selected').val()!='' || $('#NewT_Class option:selected').val()!=''))$('#NewT_Button').attr('disabled',false); else $('#NewT_Button').attr('disabled',true);">
                            <option value="">-Válassz-</option>
                            <?php echo $Classes; ?>
                        </select>
                    </p><p class="Bracket" style="display: inline;">)</p><br />
                    <label for="NewT_Lesson">Óra:</label> <select id="NewT_Lesson" onChange="if($('#NewT_Teacher option:selected').val()!='' && $('#NewT_From').val()!='' && $('#NewT_Lesson option:selected').val()!='' && ($('#NewT_Student option:selected').val()!='' || $('#NewT_Class option:selected').val()!=''))$('#NewT_Button').attr('disabled',false); else $('#NewT_Button').attr('disabled',true);">
                        <option value="">-Válassz-</option>
                        <?php
                        $ADAT=mysql_query("SELECT * FROM $_SYSTEM_LESSONS_TABLE WHERE enabled='1' ORDER BY name ASC");
                        while($row=mysql_fetch_array($ADAT))
                            echo "<option value='".$row["id"]."'>".$row["name"]."</option>\n";
                        ?>
                    </select><br />
                    <label for="NewT_Teacher">Tanár:</label> <select id="NewT_Teacher" onChange="if($('#NewT_Teacher option:selected').val()!='' && $('#NewT_From').val()!='' && $('#NewT_Lesson option:selected').val()!='' && ($('#NewT_Student option:selected').val()!='' || $('#NewT_Class option:selected').val()!=''))$('#NewT_Button').attr('disabled',false); else $('#NewT_Button').attr('disabled',true);">
                        <option value="">-Válassz-</option>
                        <?php
                            $ADAT=mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE rank='3' ORDER BY real_name ASC");
                            while($row=mysql_fetch_array($ADAT))
                                echo "<option value='".$row["id"]."'>".$row["real_name"]."</option>\n";
                        ?>
                    </select><br />
                    <label for="NewT_From">Kezdete:</label> <input type="Date" id="NewT_From" value="<?php echo date("Y-m-d"); ?>" min="<?php echo date("Y-m-d",$_FROM_DATE); ?>" max="<?php echo date("Y-m-d",$_TO_DATE); ?>" onChange="if($('#NewT_Teacher option:selected').val()!='' && $('#NewT_From').val()!='' && $('#NewT_Lesson option:selected').val()!='' && ($('#NewT_Student option:selected').val()!='' || $('#NewT_Class option:selected').val()!=''))$('#NewT_Button').attr('disabled',false); else $('#NewT_Button').attr('disabled',true);"/><br />
                    <label for="NewT_To">Vége:</label> <input type="Date" min="<?php echo date("Y-m-d",$_FROM_DATE); ?>" max="<?php echo date("Y-m-d",$_TO_DATE); ?>" id="NewT_To"/><font size="1"><i>(ürses ha nincs megadva)</i></font><br />
                    <input type="Button" disabled="disabled" id="NewT_Button" value="Hozzáad" onClick="if($('#NewT_Teacher option:selected').val()!='' && $('#NewT_Lesson option:selected').val()!='' && $('#NewT_From').val()!='' && ($('#NewT_Student option:selected').val()!='' || $('#NewT_Class option:selected').val()!=''))if($('#NewT_Class option:selected').val()=='')Type='1'; else Type='2'; $.post('ajax.php',{TYPE: 13, Type: Type, Data: $('#NewT_Student option:selected').val()+$('#NewT_Class option:selected').val(), Data2: $('#NewT_Lesson option:selected').val(), Data3: $('#NewT_Teacher option:selected').val(), Data4: $('#NewT_Day').val(), Data5: $('#NewT_Lesson_Number').val(), Data6: $('#NewT_From').val(), Data7: $('#NewT_To').val(), Data8: ($('#NewT_Student option:selected').val()==''?1:0)},function(data){if(data=='1'){if(parseInt($('#NewT_Lesson_Number').val())<parseInt($('#NewT_Lesson_Number').attr('max')))$('#NewT_Lesson_Number').val(+$('#NewT_Lesson_Number').val()+1); else{$('#NewT_Lesson_Number').val(1); if($('#NewT_Day :selected').val()<7)$('#NewT_Day option[value='+(+$('#NewT_Day :selected').val()+1)+']').attr('selected', 'selected');} Message('Hozzáadva',5000,'Timetable_Error','green'); }else Message('Hiba!'+data,5000,'Timetable_Error','red');});"/>
                    </div>
                </div>
                </div>
            <?php } ?>
            <div id="Content">
                <?php
                if($_SESSION["RANK"]!=3 and $_SESSION["RANK"]!=4)
                    echo "Kis türelmet...";
                        else{
                        if(((mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE WHERE tid='".$_SESSION["ID"]."'"))>0 or mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".$_SESSION["CLASS"]."'"))==1) and $_SESSION["RANK"]==3) or ($_SESSION["RANK"])==4 and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE"))>0)
                            {
                            echo '<select id="Edit_Gardes" onChange="$.post(\'ajax.php\',{TYPE: 3, Id: $(\'#Edit_Gardes :selected\').val()},function(data){$(\'#Gardes\').html(data);});"><optgroup label="Válassz osztályt!">';
                            if($_SESSION["RANK"]==4)
                                {
                                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1' ORDER BY name ASC");
                                while($row=mysql_fetch_array($ADAT))
                                    echo "<option value='".$row["id"]."'".(($_SESSION["CLASS"]==$row["id"])?" SELECTED":"").">".$row["name"]." (".mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE rank='1' AND class='".$row["id"]."'"))." fő)</option>\n";
                                }else{
                                $CLASSES=array();
                                $ADAT=mysql_query("SELECT * FROM $_SYSTEM_TIMETABLE_TABLE, $_SYSTEM_USERS_TABLE WHERE $_SYSTEM_TIMETABLE_TABLE.uid=$_SYSTEM_USERS_TABLE.id AND tid='".$_SESSION["ID"]."'");
                                while($row=mysql_fetch_array($ADAT))
                                    if(!in_array($row["class"],$CLASSES) and mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1' AND id='".$row["class"]."'"))==1)
                                        {
                                        echo "<option value='".$row["class"]."'".(($_SESSION["CLASS"]==$row["class"])?" SELECTED":"").">".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".$row["class"]."'"), 0, "name")." (".mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE rank='1' AND class='".$row["class"]."'"))." fő)</option>\n";
                                        $CLASSES[]=$row["class"];
                                        }
                                if(!in_array($_SESSION["CLASS"],$CLASSES))
                                    if(mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE enabled='1' AND id='".$_SESSION["CLASS"]."'")))
                                        echo "<option value='".$_SESSION["CLASS"]."' SELECTED>".mysql_result(mysql_query("SELECT * FROM $_SYSTEM_CLASSES_TABLE WHERE id='".$_SESSION["CLASS"]."'"), 0, "name")." (".mysql_num_rows(mysql_query("SELECT * FROM $_SYSTEM_USERS_TABLE WHERE rank='1' AND class='".$_SESSION["CLASS"]."'"))." fő)</option>\n";                                    
                                }
                            echo '</optgroup></select><div id="Gardes">Kis türelmet...</div>';
                            }else
                            echo "<h5>Hozzon létre osztályokat!</h5>";
                        }
                ?>
            </div>
			<div id="Exit" style="display: none;">
				<h3>Kilépés megerősítése.<br />Biztosan kilépsz?</h3>
				<input type="Button" value="Maradok" onClick="$('#Exit').hide('fast');" />
				<input type="Button" value="Kilépek" onClick="location.href='?exit';" />
			</div>
			</div>
        <?php } ?>
    <br /><?php echo base64_decode("PGRpdiBjbGFzcz0iZm9vdGVyIj48aDY+Q3JlYXRlZCBieTogPGEgaHJlZj0iaHR0cDovL3d3dy50LWJvbmQuaHUvIiB0YXJnZXQ9Il9ibGFuayI+VC1ib25kPC9hPiAtIDIwMTM8YnIgLz5EZXNpZ25lZCBieTogPGEgaHJlZj0iaHR0cDovL3VzZXJzLmF0dy5odS9ob3J2d2ViLyIgdGFyZ2V0PSJfYmxhbmsiPkhvcnY8L2E+PC9oNj48L2Rpdj4="); ?>
    </body>
</html>
<?php
ob_end_flush();
?>