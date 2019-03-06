<?php
	
	$msg2="";
	$err2="";
	$err = '';
	
	if(isset($_POST['send'])){		

        $email_addresses =   $_POST['email_addresses'];

        $f_name =   addslashes(strip_tags($_POST['f_name']));
		$f_email =   addslashes(strip_tags($_POST['f_email']));
		$e_subject =  addslashes(strip_tags($_POST['e_subject']));
		
		if($f_name=='' || $f_email=='' || $e_subject=='' || $email_addresses=='')
			$err2 .= "Please complete the required fields.";
			
		$all_email = explode(',',$email_addresses);
		
		foreach($all_email as $key=>$val){
			$val = trim($val);
			if ( $val !=  "" &&  !preg_match( '/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i' , $val ) )
			{
				 $err2 .= "$val  - Is Not A Correct Email Address!" . "<br/>";
			} 
		}
		
		if( $f_email !=  "" &&  !preg_match( '/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i' , $f_email ) )
		{
			$err2 .= "From email - $f_email  - Is Not A Correct Email Address!" . "<br/>";
		}
			
		if($err2==''){
			
			$body = "<h1>Testing jana Ojana mail service</h1>
			        <p>PHP mail is working correctly. Seems like mail server is already configured.</p>";
			
		foreach($all_email as $key=>$val){
			$val = trim($val);
			
			if($val !=''){

                if( mail($val, $e_subject, $body) )
				   $msg2 .= " $val, <br>";
				else
					$err .= "Unable to send the mail to $val. Please try again later. <br />";
			}
			
		}
			
		}
	}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Testing jana Ojana mail service</title>
</head>

<body>

<div class="box_blue_content">
    <h2>Testing jana Ojana mail service</h2>
    <?php  if ($msg2){ ?><p style="display:block; padding-bottom:10px; color:#006600; font-weight:bold; text-align:center;"><?php  echo 'Thanks! Your message has been sent successfully to '.$msg2 ; ?></p> <?php  } ?>
    <?php  if ($err2){ ?><p style="display:block; padding-bottom:10px; color:#990000; font-weight:bold; text-align:center;"><?php  echo $err2 ; ?></p> <?php  } ?>
    <?php  if ($err){ ?><p style="display:block; padding-bottom:10px; color:#990000; font-weight:bold; text-align:center;"><?php  echo $err ; ?></p> <?php  } ?>
    <div>
    <form action="php-mail.php" method="post">
        <span>*From Name:</span>
        <br />
        <input type="text" name="f_name" value="Jana Ojana" size="35"  />
        <br /><br />
        <span>*From Email:</span>
        <br />
        <input type="text" name="f_email" value="admin@janaojana.com" size="35"  />
        <br /><br />
        <span>*Subject:</span>
        <br />
        <textarea class="esubject" rows="2" cols="100" name="e_subject"><?php  if ($err2){ echo $_POST['e_subject'];}else{echo 'Testing jana Ojana mail service';} ?></textarea><br />	<br />
        <span>*Email Address (separated by comma):</span><br />
        <textarea class="comments" rows="10" cols="100" name="email_addresses"><?php  if ($err2){ echo $_POST['email_addresses'];} else echo "arif@mirtechbd.com, shaminul.islam@robi.com.bd"; ?></textarea>
        <br /><br />
        <input class="mail" type="submit" value="Send Test E-Mail" name="send" />
    </form>
    </div>
</div>

</body>
</html>
