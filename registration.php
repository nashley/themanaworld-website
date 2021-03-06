<?php
	require_once('includes/libs/libmysql.php');
	require_once('includes/models/account.php');
	require_once('recaptcha-php/recaptchalib.php');

	require_once('recaptcha-php/keys.php');

	$enable_captcha = true;  // modify this in production

	$showform = true;

	if (isset($_POST['register']) && $_POST['register'] == "true")
	{
		$showform = false;
		$err = "";
		$acc = new TMWAccount();
		$acc->setUsername($_POST['username']);
		$acc->setPassword($_POST['password1']);
		$acc->setEMail($_POST['email']);
		$acc->setGender($_POST['gender']);

		$val = $acc->validate();
		if (is_array($val))
		{
			foreach( $val as $error)
			{
				$err .= $error . "<br />";
			}
			$showform = true;
		}

		if ($_POST['password2'] != $_POST['password1'])
		{
			$err .= "The given passwords don't match!"; $showform = true;
		}

		if (TMWAccount::existsUsername( $_POST['username'] ))
		{
			$err .= "The username is in use!"; $showform = true;
		}

		if ($enable_captcha)
		{
			// check captcha
			$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

			if (!$resp->is_valid)
			{
				$err .= "The captcha was incorrect!"; $showform = true;
			}
		}

		if (!$showform)
		{
			// create the account
			if (!$acc->storeAccount())
			{
				$err = "The was an unknown error while storing your new account";
				$showform = true;
			}
		}
	}

	include("includes/common.php");
	placeHeader("Registration");


	if ($showform)
	{

?>
<p>With this form you can register for a new account. <i>We will never give your email to someone else or send you spam! Its only purpose is to be able to send you back whether account creation succeeded.</i></p>

<p style="background-color: #ede2da; padding: 5px; border: 1px solid
#9f9894; border-radius: 10px;"><i>Security warning:</i> Do not use the same username and password on
two different servers. The server admins can read all of them in clear text and
nothing stops them from trying them on other servers. It happened a lot in the
past that users of the official server got "hacked" because they ignored this
important precaution.</p>

<form action="registration.php" method="post">

	<input type="hidden" name="register" value="true" />
	<table>
		<?php if (isset($err))
		{
			echo "<tr><td colspan=\"2\" style=\"border: 1px solid red; color: red;\">" .
				$err . "</td></tr>";
		}
		?>
		<tr>
			<td>Username:</td>
			<td><input type="text" size="20" name="username" /></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" size="20" name="password1" /></td>
		</tr>
		<tr>
			<td>Retype password:</td>
			<td><input type="password" size="20" name="password2" /></td>
		</tr>
		<tr>
			<td>EMail:</td>
			<td><input type="text" size="30" name="email" /></td>
		</tr>
		<tr>
			<td>Gender:</td>
      <td>
       <select name="gender">
         <option value="0" selected></option>
         <option value="1">Male</option>
         <option value="2">Female</option>
       </select>
      </td>
		</tr>
		<tr>
			<td colspan="2">
				<?php echo recaptcha_get_html($publickey); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right">
				<input type="submit" value="Register" />
			</td>
		</tr>
	</table>
</form>


<?php

	} // end of showform
	else
	{
	?>
		<p>Your account was scheduled for creation! In a few minutes you should receive an email with verification of your new account.</p>

		<p><em>If the account doesn't work after five minutes, please ask for help on the forums or IRC. We can make it for you.</em></p>

	<?php }
	placeFooter();
?>

