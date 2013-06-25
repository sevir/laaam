<?php
gp_title( sprintf( __('%s &lt; Laaam'), __('Login') ) );
gp_breadcrumb( array(
	__('Login'),
) );
gp_tmpl_header();
?>
	<h2><? _e('Login'); ?></h2>
	<?php do_action( 'before_login_form' ); ?>
	<form action="<?php echo gp_url_ssl( gp_url_current() ); ?>" method="post" class="loginform" id="loginform">
	<h3><? _e('Internal users'); ?></h3>
	<dl>
		<dt><label for="user_login"><?php _e('Username'); ?></label></dt>
		<dd><input type="text" value="" id="user_login" name="user_login" /></dd>

		<dt><label for="user_pass"><?php _e('Password'); ?></label></dt>
		<dd><input type="password" value="" id="user_pass" name="user_pass" /></dd>
	</dl>
	<p><input class="btn" type="submit" name="submit" value="<?php _e('Login'); ?>" id="submit"></p>
	<input type="hidden" value="<?php echo esc_attr( gp_get( 'redirect_to' ) ); ?>" id="redirect_to" name="redirect_to" />
	</form>
	

	<form class="loginform_internal" id="loginform_internal" action="cmanager/public/index.php/laaam/login">
		<h3><? _e('Users from external platform'); ?></h3>
		<dl>
			<dt><label for="user_login"><?php _e('Username'); ?></label></dt>
			<dd><input type="text" value="" id="user_login_internal" name="user_login" /></dd>

			<dt><label for="user_pass"><?php _e('Password'); ?></label></dt>
			<dd><input type="password" value="" id="user_pass_internal" name="user_pass" /></dd>
		</dl>
		<p><input class="btn" type="submit" name="submit" value="<?php _e('Login'); ?>" id="submit_internal"></p>
	</form>
<script type="text/javascript">
	$(function(){
		$("#loginform_internal input").keyup(function(e){
			var code = e.which;
			if(code==13) $("#loginform_internal").submit();
		});
		$("#loginform_internal").submit(function(){
			var user = $("#user_login_internal").val();
			var password = $("#user_pass_internal").val();
			
			$.post($(this).attr("action"),{
				'username':user,
				'password':password
			}, function (json){
				if(json.stat == 'OK'){
					$("#user_login").val(json.user_login);
					$("#user_pass").val(json.user_password);
					$("#submit").click();
				}
			});
			return false;
		});
	});
</script>
<?php do_action( 'after_login_form' ); ?>
<script type="text/javascript" charset="utf-8">
	document.getElementById('user_login').focus();
</script>
<?php gp_tmpl_footer();
