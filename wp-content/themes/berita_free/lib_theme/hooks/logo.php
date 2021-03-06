<?php 

add_action( 'bizz_logo', 'bizz_logo_area' );

add_action( 'bizz_logo_inside', 'bizz_logo_spot' );
add_action( 'bizz_search_form_logo_inside', 'bizz_search_form' );

function bizz_logo_area() { 

?>

<?php bizz_logo_before(); ?>

<div class="logo-area clearfix">
<div class="container_16">
<div class="grid_16">
	
	<div class="logo-spot fl">
    	<?php bizz_logo_inside(); ?>
	</div><!--/.logo-spot-->
	
	<div class="search-spot fr">
		<?php bizz_search_form_logo_inside(); ?>
	</div><!-- /.search-spot --> 
		
</div><!-- /.grid_16 -->	
</div><!--/.container_16 -->
</div><!-- /.logo-area -->

<?php bizz_logo_after(); ?>

<?php } ?>