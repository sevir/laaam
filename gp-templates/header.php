<?php
wp_enqueue_style( 'base' );
wp_enqueue_script( 'jquery' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title><?php echo gp_title(); ?></title>
		<link rel="stylesheet" type="text/css" href="cmanager/public/assets/css/bootstrap.min.css">
		
		<?php gp_head(); ?>
		<script type="text/javascript" src="cmanager/public/assets/js/libs//bootstrap/bootstrap.min.js"></script>
	</head>
	<body <?php body_class(); ?>>
	<script type="text/javascript">document.body.className = document.body.className.replace('no-js','js');</script>
		<div class="gp-content">
	    <div id="gp-js-message"></div>
		<h1>
			<a class="logo" href="<?php echo gp_url( '/' ); ?>" rel="home">
				<img alt="laaam" class="logoimg" src="<?php echo gp_url_img( 'glotpress-logo.png' ); ?>" />
			</a>			
			<span id="hello">
			<?php
			if (GP::$user->logged_in()):
				$user = GP::$user->current();

				printf( __('Hi, %s.'), '<a href="'.gp_url( '/profile' ).'">'.$user->user_login.'</a>' );
				?>
				<?php if ( GP::$user->current()->can( 'write', 'project' ) ): ?>
					<a href="<?php echo gp_url('/cmanager')?>"><?php _e('Backend'); ?></a>
				<?php endif; ?>
				<a href="<?php echo gp_url('/logout')?>"><?php _e('Log out'); ?></a>
			<?php else: ?>
				<strong><a href="<?php echo gp_url_login(); ?>"><?php _e('Log in'); ?></a></strong>
			<?php endif; ?>
			<?php do_action( 'after_hello' ); ?>
			</span>
			<?php echo gp_breadcrumb(); ?>
			<div class="clearfix"></div>
		</h1>
		<div class="clear after-h1"></div>
		<?php if (gp_notice('error')): ?>
			<div class="error">
				<?php echo gp_notice( 'error' ); //TODO: run kses on notices ?>
			</div>
		<?php endif; ?>
		<?php if (gp_notice()): ?>
			<div class="notice">
				<?php echo gp_notice(); ?>
			</div>
		<?php endif; ?>
		<?php do_action( 'after_notices' ); ?>
