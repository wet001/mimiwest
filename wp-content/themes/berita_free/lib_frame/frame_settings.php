<?php

function bizzthemes_add_admin() {

    global $themename;
	
	if ( isset($_GET['page']) && $_GET['page'] == 'bizzthemes' || isset($_GET['activated']) ) {
        // REDIRECT to theme options on activation
		if ( isset($_GET['activated']) ) { 
            header("Location: admin.php?page=bizzthemes");
	    }
    }
	
	// RESET theme options
	if( isset($_REQUEST['bizz_save']) && $_REQUEST['bizz_save'] == 'reset' && $_GET['page'] == 'bizzthemes' ) {
		    global $wpdb;
			$query = "DELETE FROM $wpdb->options WHERE option_name LIKE 'bizzthemes_options' OR option_name LIKE '%pag_exclude%' OR option_name LIKE '%pst_exclude%' ";
			$wpdb->query($query);
			header("Location: admin.php?page=bizzthemes&reset=true");
			die;
	// RESET design options
	} elseif( isset($_REQUEST['bizz_save']) && $_REQUEST['bizz_save'] == 'reset' && $_GET['page'] == 'bizz-design' ) {
		    global $wpdb;
			$query = "DELETE FROM $wpdb->options WHERE option_name LIKE 'bizzthemes_design' ";
			$wpdb->query($query);
			// clear layout.css file: START
			if (is_writable(BIZZ_LAYOUT_CSS)) {
				$lid = @fopen(BIZZ_LAYOUT_CSS, 'w');
				$def = '/*
File:			layout.css
Description:	Custom layout styles for Your Theme
Author:         You ;)

        IMPORTANT NOTE:
	
	    If you wish to make custom changes to your theme, DO NOT EDIT THIS FILE. 
	    Instead, use the Theme Design Options in your theme administration to 
	    define custom layout styles.
*/

/*--------- GENERAL STYLING Options --------- */

				';
				@fwrite($lid, $def);
				@fclose($lid);
			}
			// clear layout.css file: END
			header("Location: admin.php?page=bizz-design&reset=true");
			die;
	}
	
	// Theme Options Icon
	if ( 
	    isset($GLOBALS['opt']['bizzthemes_branding_back']) && 
		isset($GLOBALS['opt']['bizzthemes_branding_back_icon']) &&
		$GLOBALS['opt']['bizzthemes_branding_back'] == true && 
		!empty($GLOBALS['opt']['bizzthemes_branding_back_icon'])
	)
	    $themeicon = $GLOBALS['opt']['bizzthemes_branding_back_icon'];
	else
	    $themeicon = BIZZ_FRAME_IMAGES. '/bizzthemes-icon.png';
	
	// Theme Options Menu
	add_menu_page('Theme Options', $themename, 'edit_themes', 'bizzthemes', 'bizzthemes_options', $themeicon, 31);
	
} // end function bizzthemes_add_admin()

/**
 * ADMIN SUBMENUS SEPARATOR
 *
 * output admin options panel separator
 * @since 6.6.3
 */
function bizzthemes_add_menu_sep() {
    global $menu;
	
	if (version_compare(get_bloginfo('version'), '2.9', '>='))
		$menu[30] = array('', 'read', 'separator-bizzthemes', '', 'wp-menu-separator');
	
}
add_action('init', 'bizzthemes_add_menu_sep');

add_action('admin_menu', 'bizzthemes_add_admin');

function bizzthemes_add_submenus() {

    global $themename;
	
	add_submenu_page('bizzthemes', $themename, 'Theme Options', 'manage_options', 'bizzthemes','bizzthemes_options');
	add_submenu_page('bizzthemes', $themename, 'Design Control', 'manage_options', 'bizz-design','bizzthemes_options');
	add_submenu_page('bizzthemes', $themename, 'Updates Control', 'manage_options', 'bizz-update','bizzthemes_updates');
	add_submenu_page('bizzthemes', $themename, 'Custom Editor', 'manage_options', 'bizz-editor', array('bizz_custom_editor', 'bizzthemes_editor'));
	
} // end function bizzthemes_add_admin()

add_action('admin_menu', 'bizzthemes_add_submenus');


// AJAX SAVE ACTION - bizz_ajax_callback
// Original author of Ajax script: WooThemes
// License: GPL

function bizz_ajax_callback() {
	    
	global $options, $design, $themeid;
		
	if($_POST['type'] == 'bizz-design'){ $options = $design; } else { $options = $options; } // choose which options to load (design or general)
					
	$opts = array();
	$opts['themeid'] = $themeid;
			
	if($_POST['type'] == 'upload'){
	    $clickedID = $_POST['data']; // Acts as the name
		$filename = $_FILES[$clickedID];
		$override['test_form'] = false;
		$override['action'] = 'wp_handle_upload';
		$uploaded_file = wp_handle_upload($filename,$override);
		$opts[$clickedID] = $uploaded_file['url'];
		echo $uploaded_file['url']; 
				
	} else {
		
		$data = $_POST['data'];
		parse_str($data,$output);
		print_r( $output );
							
		foreach ($output as $key => $value) {
			
			if ($value != ''){
				if ( is_string( $value ) ) 
				    $opts[$key] = mysql_real_escape_string( $value );
				else
				    $opts[$key] = $value;
			}
				
        }
			
	}
	
		
	if($_POST['type'] == 'bizz-design'){ // save design options
		update_option('bizzthemes_design', $opts);
		bizz_generate_css(); #updates layout.css file
		die;
		
	} elseif($_POST['type'] == 'bizz-all') {
		update_option('bizzthemes_options', $opts);
		die;
		
	}
	die; // prevent overlooping

}

add_action('wp_ajax_bizz_ajax_post_action', 'bizz_ajax_callback');

function bizzthemes_options() {
    global $themeid, $themename, $themeurl, $bloghomeurl, $shortname, $options, $design, $frame;
	
	if ( $_GET['page'] == 'bizzthemes' )
	    $options = $options;	
	elseif ( $_GET['page'] == 'bizzthemes&reset=true' )
	    $options = $options;
	elseif ( $_GET['page'] == 'bizz-design' )
	    $options = $design;
		
	echo '<div class="clear"><!----></div>'."\n";
	echo '<div class="bizzadmin">'."\n";
	    echo '<h2>'."\n";
		    echo '<span class="theme-name">'. $themename .' Theme Options</span>'."\n";
			echo '<a id="master_switch" href="" title="Show/Hide All Options"><span class="pos">&nbsp;</span><span class="neg">&nbsp;</span> Show/Hide All Options</a>'."\n";
	        if ( isset($GLOBALS['opt']['bizzthemes_branding_back']) && $GLOBALS['opt']['bizzthemes_branding_back'] == 'true' ){
			    if (isset($GLOBALS['opt']['bizzthemes_branding_back_link']) && $GLOBALS['opt']['bizzthemes_branding_back_link'] <> '' && $GLOBALS['opt']['bizzthemes_branding_back_link_dest'] <> '' )
	                echo '&rarr; <a class="optional" href="'.$GLOBALS['opt']['bizzthemes_branding_back_link_dest'].'" title="'.$GLOBALS['opt']['bizzthemes_branding_back_link'].'">'.$GLOBALS['opt']['bizzthemes_branding_back_link'].'</a>'."\n";
	        } else {
	            echo '&rarr; <a class="optional" href="http://bizzthemes.com/support/" title="Read Theme Documentation & Installation Guide">Documentation</a>'."\n";
			    echo '<a class="optional" href="http://bizzthemes.com/forums/" title="Visit Support Forums">Support Forums</a>'."\n";
				echo '<a href="#" class="feedback"  onclick="window.open(\'http://bizzthemes.wufoo.com/forms/m7x4a3/\',  null, \'height=560, width=670, toolbar=0, location=0, status=1, scrollbars=1,resizable=1\'); return false" title="Give your Feedback"><img src="'. BIZZ_FRAME_IMAGES . '/bug.png" width="22" height="23" alt="Feedback" />&nbsp;&nbsp;Report a Bug</a>'."\n";
			} 
	    echo '</h2>'."\n";
	echo '<div class="clear"><!----></div>'."\n";
	
	echo '<div id="saved"></div>'."\n";
	    
    if ( isset($_REQUEST['reset']) )
	    echo '<div id="message" class="updated fade themeinfo"><p><strong>'.$themename.' settings reset!&nbsp; <a href="'.$bloghomeurl.'">Check out your blog &rarr;</a></strong></p></div>'."\n";
	if (!is_writable(BIZZ_LAYOUT_CSS))
	    echo '<div id="message" class="updated fade themeinfo"><p>Warning: Your <code>custom</code> folder is not writeable. Please <a href="http://codex.wordpress.org/Changing_File_Permissions">CHMOD</a> all custom folder content to 777 restrictions, otherwise theme will not work properly.</p></div>'."\n";
	echo '<div class="clear"><!----></div>'."\n";
	echo '<div id="bizz-popup-save" class="save-popup">Changes Saved</div>'."\n";
	echo '<div class="ajax-loading"><!----></div>'."\n";
	
echo '<form action="" enctype="multipart/form-data" id="bizz_form">'."\n";

foreach ($options as $value) {
	
	switch ( $value['type'] ) {
	
		case 'text':
		    option_wrapper_header($value);
			
			$std = $value['std'];
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
		        <input class="text_input" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php echo stripslashes(stripslashes($val)); ?>" />
<?php
		    option_wrapper_footer($value);
		break;
		
		case 'select':
		    option_wrapper_header($value);
			
			$std = $value['std'];			
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	            <select class="select_input" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
				    <?php 
					    if ($value['show_option_none'] == true) {
						    echo "<option value=''>-- None --</option>\n";
						}
					    foreach ($value['options'] as $option) {
						    if($val == $option){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $option . "\">" . $option . "</option>\n";
						}
					?>
	            </select>
<?php
		    option_wrapper_footer($value);
		break;
		
		case 'select_by_id':
		    option_wrapper_header($value);
			
			$std = $value['std'];			
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	            <select class="select_input" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
				    <?php 
					    if ($value['show_option_none'] == true) {
						    echo "<option value=''>-- None --</option>\n";
						} elseif ($value['show_option_all'] == true) {
						    echo "<option value=''>-- All --</option>\n";
						}
					    foreach ($value['options'] as $key=>$option) {
						    if($val == $option){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $option . "\">" . $key . "</option>\n";
						}
					?>
	            </select>
<?php
		    option_wrapper_footer($value);
		break;
		
		case 'menu_select':
		    option_wrapper_header($value);
						
			$nav_menus = wp_get_nav_menus();	
			$nav_menu_selected_id = $nav_menus[0]->term_id;
						
			$std = $value['std'];			
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	            <select class="select_input" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
				    <?php foreach( (array) $nav_menus as $key => $_nav_menu ) : ?>
					<?php 
					$_nav_menu->truncated_name = trim( wp_html_excerpt( $_nav_menu->name, 40 ) );
					if ( $_nav_menu->truncated_name != $_nav_menu->name )
						$_nav_menu->truncated_name .= '&hellip;';
					$nav_menus[$key]->truncated_name = $_nav_menu->truncated_name; 
					if($val == esc_attr($_nav_menu->term_id)){ $selected = 'selected="selected"'; } else { $selected = ''; }
					?>
							<option value="<?php echo esc_attr($_nav_menu->term_id); ?>" <?php echo $selected; ?>>
								<?php echo esc_html( $_nav_menu->truncated_name ); ?>
							</option>
					<?php endforeach; ?>
	            </select>
<?php
		    option_wrapper_footer($value);
		break;
		
		case 'upload':
		    option_wrapper_header($value);
				
			$id = $value['id'];
			$std = $value['std'];	
			$uploader = '';		
			$val = '';
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
				<div id="upload-wrap">
				    <div class="upload_button" id="<?php echo $id; ?>">Choose File</div>
				    <input class="upload_text_input" name="<?php echo $id; ?>" id="<?php echo $id; ?>" type="text" value="<?php echo $val; ?>" />
					<?php if (!empty($val)) { ?>
                        <a class="img-preview" href="<?php echo $val; ?>">
						<img id="image_<?php echo $id; ?>" src="<?php echo $val; ?>" width="20" height="20" title="Image Preview" alt="Image Preview" />
                        </a>
                    <?php } ?>
				    <div class="clear"><!----></div>
				</div>
<?php
			option_wrapper_footer($value);
		break;
		
		case 'textarea':
		    option_wrapper_header($value);
			
		    if (isset($value['wysiwyg']) && $value['wysiwyg'] <> ''){ $wysiwyg=$value['wysiwyg']; } else { $wysiwyg=$value['id']; }
			if (isset($value['cols']) && $value['cols'] <> ''){ $cols=$value['cols']; } else { $cols='50'; }
			if (isset($value['rows']) && $value['rows'] <> ''){ $rows=$value['rows']; } else { $rows='8'; }
			$std = $value['std'];			
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			    <textarea name="<?php echo $value['id']; ?>" class="<?php echo $wysiwyg; ?>" id="<?php echo $wysiwyg; ?>" cols="<?php echo $cols; ?>" rows="<?php echo $rows; ?>"><?php echo stripslashes($val); ?></textarea>
<?php
		    option_wrapper_footer($value);
		break;

		case "radio":
		    option_wrapper_header($value);
			
			$std = $value['std'];	
			
			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
			
			    $counting = 0;
			    foreach ($value['options'] as $key=>$option) { 
				
				    $counting++;
					$checked = '';
					if($val == $key){ $checked = ' checked'; } else { $checked = ''; }
?>
			    <input class="input_checkbox" type="radio" name="<?php echo $value['id']; ?>" id="<?php echo $value['id'].'_'.$counting; ?>" value="<?php echo $key; ?>" <?php echo $checked; ?> />&nbsp;
			    <label for="<?php echo $value['id'].'_'.$counting; ?>"><?php echo $option; ?></label><br />
<?php 
		        }
		    option_wrapper_footer($value);
		break;
		
		case "checkbox":
		    option_wrapper_header($value);
			
			if( 
			(isset($GLOBALS['opt'][$value['id']]) && $GLOBALS['opt'][$value['id']]) || 
			(isset($GLOBALS['optd'][$value['id']]) && $GLOBALS['optd'][$value['id']]) || 
			(isset($value['std']) && $value['std'] && isset($GLOBALS['opt'][$value['id']])) 
			)
				$val = 'true';
			else
				$val = '';
			
			$checked = '';
			$checked = ($val == 'true') ? ' checked' : '' ;
			$disabled = ( isset($value['disabled']) ) ? $value['disabled'] : '' ;
?>
		        <input <?php echo $disabled; ?> class="input_checkbox" type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />&nbsp;
			    <label for="<?php echo $value['id']; ?>"><?php echo $value['label']; ?></label><br />
<?php
		    option_wrapper_footer($value);
		break;
		
		case "checkbox2": 
		    option_wrapper_header($value);
			
				if( isset($GLOBALS['opt'][$value['id']]) ){
					$checked = "checked=\"checked\"";
				} elseif ( isset($GLOBALS['optd'][$value['id']]) ){
					$checked = "checked=\"checked\"";
				} else {
					$checked = "";
				}
				if ( isset($value['disabled']) )
				    $disabled = $value['disabled'];
				else
				    $disabled = '';
?>
		        <input <?php echo $disabled; ?> class="input_checkbox" type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />&nbsp;
				<label for="<?php echo $value['id']; ?>"><?php echo $value['label']; ?></label><br />
<?php
		    option_wrapper_footer($value);
		break;
						
		case "multisort":
			    $vid = $value['id'];
				
				if ( $GLOBALS['opt'][$value['id']] <> '' ) {
				
				    $array1 = $GLOBALS['opt'][$value['id']];
				    $array2 = $value['options'];
					
					$sort_array1 = array_intersect_key($array1, $array2);
					$sort_array2 = array_diff_key($array1, $array2);
					
					$count_a1 = count($sort_array1); // count same array keys
					$count_a2 = count($array2); // count std arrays
					
					if ( $count_a1==$count_a2 ){
					    $sort_array = $sort_array1;
						$opto = 'false';
					} else {
					    $sort_array = $array2;
						$opto = 'true';
					}
				
				} else {
				
				    $sort_array = $value['options'];
					$opto = 'true';
					
				}

				foreach ($sort_array as $key=>$value) { 
				    $pn_key = $vid . '_' . $key;
					$chk_std = ( isset( $value['show'] ) ) ? $value['show'] : '';
					
					if( isset($GLOBALS['opt'][$vid . '_' . $key]) )
					    $chk_sav = true;
					else
					    $chk_sav = '';
					if( isset($GLOBALS['opt'][$vid . '_' . $key]) )
					    $chk_sav2 = true;
					else
					    $chk_sav2 = '';

					$checked = '';
					if(!empty($chk_sav)) {
					    if($chk_sav == 'true') { $checked = "checked=\"checked\""; } else { $checked = ''; }
					} elseif(!empty($chk_sav2)) {
					    if($chk_sav == 'true') { $checked = "checked=\"checked\""; } else { $checked = ''; }
					} elseif ( $chk_std == 'true') {
					    $checked = "checked=\"checked\"";
					} else {
				        $checked = '';
					}
					$opt_name = $value; // get option full name
					if ($opto == 'true') { $opt_name = $value['name']; } else { $opt_name = $value; } // get option full name
?>
			    <div class="list_item">
				<input class="input_checkbox" type="checkbox" name="<?php echo $pn_key; ?>" id="<?php echo $pn_key; ?>" value="true" <?php echo $checked; ?> />&nbsp;
			    <label for="<?php echo $pn_key; ?>"><?php echo $opt_name; ?>&nbsp;&nbsp;<small style='color:#aaaaaa'>id=<?php echo $key; ?></small></label><br />
				<input type="hidden" name="<?php echo $vid; ?>[<?php echo $key; ?>]" value="<?php echo $opt_name; ?>" />
				</div>
<?php 
		        } // end foreach
		break;
		
		case "typography":
		    option_wrapper_header($value);
		
			// font-size
			$std = $value['std']['size'];
			
			if( isset($GLOBALS['opt'][$value['id']]['size']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['size'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['size']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['size'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	        <select class="select_q q1" name="<?php echo $value['id']; ?>[size]" id="<?php echo $value['id']; ?>_size">
<?php 
					    for ($i = 7; $i < 71; $i++){
						    if($val == $i){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $i . "px\">" . $i . "px</option>\n";
						} 
?>
	        </select>
<?php
					
			// font-family
			$font_stacks = bizz_get_fonts();
			$std = $value['std']['face'];
			
			if( isset($GLOBALS['opt'][$value['id']]['face']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['face'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['face']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['face'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			<select class="select_q q2" name="<?php echo $value['id']; ?>[face]" id="<?php echo $value['id']; ?>_face">
<?php
						foreach ($font_stacks as $font_key => $font) {
							if($val == $font_key){ $selected = 'selected="selected"'; } else { $selected = ''; }
							$web_safe = ($font['web_safe']) ? ' *' : '';
							$goog_font = ($font['google']) ? ' <small>+</small>' : '';
							echo "<option ".$selected." value=\"" . $font_key . "\">" . $font['name'] . $web_safe .''. $goog_font . "</option>\n";	
						}
?>
			</select>
<?php
			
			// font-style
			$options_style = array("normal","italic","bold","bold italic");
			$std = $value['std']['style'];

			if( isset($GLOBALS['opt'][$value['id']]['style']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['style'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['style']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['style'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			<select class="select_q q3" name="<?php echo $value['id']; ?>[style]" id="<?php echo $value['id']; ?>_style">
<?php
						foreach ($options_style as $style) {
							if($val == $style){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $style . "\">" . $style . "</option>\n";	
						}
?>
			</select>
<?php
			
			// font-color
			$std = $value['std']['color'];
			
			if( isset($GLOBALS['opt'][$value['id']]['color']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['color'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['color']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['color'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			<input class="text_q color {hash:true,caps:false,required:false}" name="<?php echo $value['id']; ?>[color]" id="<?php echo $value['id']; ?>" type="text" value="<?php echo $val; ?>" />
<?php
						
			option_wrapper_footer($value);
		break;
		
		case "border":
		    option_wrapper_header($value);

			// border-width
			$std = $value['std']['width'];
			
			if( isset($GLOBALS['opt'][$value['id']]['width']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['width'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['width']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['width'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	        <select class="select_q q1" name="<?php echo $value['id']; ?>[width]" id="<?php echo $value['id']; ?>_width">
<?php 
					    for ($i = 0; $i < 30; $i++){
						    if($val == $i){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $i . "px\">" . $i . "px</option>\n";
						} 
?>
	        </select>
<?php
			
			// border-style
			$options_style = array("solid","dashed","dotted","double");
			$std = $value['std']['style'];
			
			if( isset($GLOBALS['opt'][$value['id']]['style']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['style'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['style']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['style'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	        <select class="select_q q4" name="<?php echo $value['id']; ?>[style]" id="<?php echo $value['id']; ?>_style">
<?php 
					    foreach ($options_style as $style) {
							if($val == $style){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $style . "\">" . $style . "</option>\n";	
						}
?>
	        </select>
<?php
			
			// border-color
			$std = $value['std']['color'];
			
			if( isset($GLOBALS['opt'][$value['id']]['color']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['color'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['color']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['color'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			<input class="text_q color {hash:true,caps:false,required:false}" name="<?php echo $value['id']; ?>[color]" id="<?php echo $value['id']; ?>" type="text" value="<?php echo $val; ?>" />
<?php
						
			option_wrapper_footer($value);
		break;
		
		case "bgproperties":
		    option_wrapper_header($value);

			// bg-color
			$std = $value['std']['color'];
			
			if( isset($GLOBALS['opt'][$value['id']]['color']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['color'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['color']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['color'];
			else
			    $sav2 = '';
			
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			<input class="text_q color {hash:true,caps:false,required:false}" name="<?php echo $value['id']; ?>[color]" id="<?php echo $value['id']; ?>" type="text" value="<?php echo $val; ?>" />
<?php
			
			// bg-repeat
			$options_style = array("repeat","repeat-x","repeat-y","no-repeat");
			$std = $value['std']['repeat'];
			
			if( isset($GLOBALS['opt'][$value['id']]['repeat']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['repeat'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['repeat']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['repeat'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	        <select class="select_q q5" name="<?php echo $value['id']; ?>[repeat]" id="<?php echo $value['id']; ?>_repeat">
<?php 
					    foreach ($options_style as $repeat) {
							if($val == $repeat){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $repeat . "\">" . $repeat . "</option>\n";	
						}
?>
	        </select>
<?php
			
			// bg-position
			$options_style = array("top left", "top center", "top right", "center left", "center center", "center right", "bottom left", "bottom center", "bottom right");
			$std = $value['std']['position'];
			
			if( isset($GLOBALS['opt'][$value['id']]['position']) )			
			    $sav = $GLOBALS['opt'][$value['id']]['position'];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]['position']) )
			    $sav2 = $GLOBALS['optd'][$value['id']]['position'];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
	        <select class="select_q q5" name="<?php echo $value['id']; ?>[position]" id="<?php echo $value['id']; ?>_position">
<?php 
					    foreach ($options_style as $position) {
							if($val == $position){ $selected = 'selected="selected"'; } else { $selected = ''; }
							echo "<option ".$selected." value=\"" . $position . "\">" . $position . "</option>\n";	
						}
?>
	        </select>
<?php
						
			option_wrapper_footer($value);
		break;
		
		case "color":
		    option_wrapper_header($value);
			
			$std = $value['std'];			

			if( isset($GLOBALS['opt'][$value['id']]) )			
			    $sav = $GLOBALS['opt'][$value['id']];
			else
			    $sav = '';
			if( isset($GLOBALS['optd'][$value['id']]) )
			    $sav2 = $GLOBALS['optd'][$value['id']];
			else
			    $sav2 = '';
				
			if ( $sav != "") { $val = $sav; } elseif ( $sav2 != "") { $val = $sav2; } else { $val = $std; }
?>
			<input class="text_q color {hash:true,caps:false,required:false}" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="text" value="<?php echo $val; ?>" />
<?php
						
			option_wrapper_footer($value);
		break;
		
		case 'help':
		    option_wrapper_header3($value);
		        echo '<div><!----></div>'."\n";
		    option_wrapper_footer3($value);
		break;
		
		case "heading":
		    if ( isset($value['where']) ) { 
			    $where = '<a href="#" class="where"  onclick="window.open(\''.$value['where'].'\',  null, \'height=700, width=500, toolbar=0, location=0, status=1, scrollbars=1,resizable=1\'); return false">where?</a>'; 
			} else { 
			    $where = ''; 
			}
		    echo '<div class="box-title">'. $value['name'] .' '. $where .'</div>'."\n";
			echo '<div class="fr submit submit-title">'."\n";
			    echo '<input name="save" type="submit" value="Save changes" />'."\n";
				// echo '<input type="hidden" name="bizz_save" value="save" />'."\n";
			echo '</div>'."\n";
		break;
		
		case "subheadingtop":
		    echo '<div class="feature-box">'."\n";
			echo '<div class="subheading">'."\n";
			    if ($value['toggle'] <> "") {
			        echo '<a class="toggle" href="" title="Show/hide additional information"><span class="pos">&nbsp;</span><span class="neg">&nbsp;</span>'. $value['name'] .'</a>';
			    } 
			echo '</div>'."\n";
			echo '<div class="options-box">'."\n";
		break;
		
		case "subheadingbottom":
		    echo '</div>'."\n"; // end options-box
			echo '</div>'."\n"; // end feature-box
		break;
		
		case "wraptop":
		    echo '<div class="table-row"><div class="text"><div class="wrap-dropdown">'."\n";
		break;
		case "wrapbottom":
		    echo '</div></div></div>'."\n";
		break;
		
		case "upc_top":
		    echo '<div class="table-row upc-top"><div class="text"><div class="upc-wrap">'."\n";
		break;
		case "upc_bottom":
		    echo '</div></div></div>'."\n";
		break;
		case "upc_addremove":
		    echo '<div class="addremove"><span class="add" title="Add new item">Add [&#43;]</span> <span class="remove" title="Remove this item">Remove [&#45;]</span></div>'."\n";
		break;
		
		case "sorttop":
		    echo '<div class="table-row"><div class="text"><div id="sortme" class="wrap-dropdown sortable">'."\n";
		break;
		case "sortbottom":
		    echo '</div></div></div>'."\n";
		break;

		
		case "maintabletop":
		    echo '<div class="maintable">'."\n";
		break;
		case "maintablebottom":
		    echo '</div>'."\n";
		break;
		case "maintablebreak":
		    echo '<div class="break"><!----></div>'."\n";
		break;
		
		default:
		break;
		
	} // end switch			
} // foreach

    echo '<div class="clear"></div>'."\n";
    echo '<p class="reset_save">'."\n";
	    echo '<input name="save" type="submit" class="save_button" value="Save changes" />'."\n";
		// echo '<input type="hidden" name="bizz_save" value="save" />'."\n";
	echo '</p>'."\n";
echo '</form>'."\n";

echo '<form method="post">'."\n";
    echo '<p class="reset_save reset">'."\n";
	    echo '<input name="reset" type="submit" class="save_button" value="Reset" onclick="return confirm(\'All theme settings will be lost! Click OK to reset.\');" />'."\n";
		echo '<input type="hidden" name="bizz_save" value="reset" />'."\n";
	echo '</p>'."\n";
echo '</form>'."\n";

} // end function bizzthemes_admin

function option_wrapper_header2($values){
    echo '<div class="table-row">'."\n";
	
} // end function option_wrapper_header2

function option_wrapper_header3($values){
    echo '<div class="table-row">'."\n";
	    if ($values['name'] <> '') {
		echo '<div class="top-container">'."\n";
		    echo '<span class="name fl">'. $values['name'] .'</span>'."\n";
			if ( isset($values['desc']) ) {
		        // echo '<p class="description">'. $values['desc'] .'</p>'."\n";
			    echo '<div class="bubbleInfo fr">'."\n";
			    echo '<span class="trigger">more info [&#43;]</span>'."\n";
			    echo '<div class="popup">'. $values['desc'] .'</div>'."\n";
			    echo '</div>'."\n";
			}
		echo '<div class="clear"><!----></div'."\n";
		echo '</div'."\n";
		}
		echo '<div class="bottom-container">'."\n";
		
} // end function option_wrapper_header3

function option_wrapper_header($values){
    echo '<div class="table-row">'."\n";
	    if (isset($values['name']) && $values['name'] <> '') {
		echo '<div class="top-container">'."\n";
		    echo '<span class="name fl">'. $values['name'] .'</span>'."\n";
			if (isset($values['desc']) && $values['desc'] <> '') {
		        // echo '<p class="description">'. $values['desc'] .'</p>'."\n";
			    echo '<div class="bubbleInfo fr">'."\n";
			    echo '<span class="trigger">more info [&#43;]</span>'."\n";
			    echo '<div class="popup">'. $values['desc'] .'</div>'."\n";
			    echo '</div>'."\n";
			}
		echo '<div class="clear"><!----></div'."\n";
		echo '</div'."\n";
		}
		echo '<div class="bottom-container">'."\n";
		echo '<div class="text">'."\n";
		
} // end function option_wrapper_header

function option_wrapper_footer($values){
        echo '</div>'."\n";
		echo '</div>'."\n";
	echo '</div>'."\n";
	
} // end function option_wrapper_footer

function option_wrapper_footer3($values){
        echo '</div>'."\n";
	echo '</div>'."\n";
	
} // end function option_wrapper_footer3

if (!function_exists('array_intersect_key')) {
    function array_intersect_key ($isec, $arr2) {
        $argc = func_num_args();
        for ($i = 1; !empty($isec) && $i < $argc; $i++){
            $arr = func_get_arg($i);
            foreach ($isec as $k => $v)
                if (!isset($arr[$k]))
                    unset($isec[$k]);
        }
        return $isec;
    }
}

if (!function_exists('array_diff_key')) {
    function array_diff_key() {
        $arrs = func_get_args();
        $result = array_shift($arrs);
        foreach ($arrs as $array) {
            foreach ($result as $key => $v) {
                if (array_key_exists($key, $array)) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
   }
}

?>