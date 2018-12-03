<?php
/*

//	Custom Template: Course Asssignments
 
// 	This is not a standard Wordpress template, but is just being called via a php includes statement located in the single-course.php file.
	
*/

global $wpdb, $current_user;
$course_permalink = get_permalink(); //Not sure what this is for?


?>  

			<div class="course-nav top">
				<?php nb_course_nav_bar();?>
			</div><!-- end .course-nav top -->
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>> 

				<div class="post-side-col">
					<h5>Unit Contents</h5>
					<ul>
						<?php wp_list_pages($list_args); ?>
					</ul>
				</div>
                <div class="post-entry">
                    <h2><?php the_title(); ?></h2>
					<hr>
                    
					<?php the_content(__('Read more &#8250;', 'responsive')); ?>
                    
				<?php 
				$asmt = new NB_Assignment( $current_user->ID );

				if( empty( $asmt->get_asmt_id( $post->ID ) ) && !empty( $asmt->asmt_exists( $post->ID ) ) ):
					$asmt_status_string = $asmt->get_asmt_status(  $post->ID  );
					
					echo "<hr>
					<div class='asmt_submitted'> 
						<h3>Assignment Submitted</h3>
						<p><em>This assignment is already marked as <strong>{$asmt_status_string}</strong>, but was submitted some other way, probably via email.</em></p>
					</div>";
				else: ?>
				
					<p><a class="button" href="#asmt-editor">Jump to Assignment Editor &darr;</a></p>
					<hr style="clear: both;">
					<h2 id="asmt-editor">Assignment Editor</h2>
					<?php // We may want to insert comments on the assignment here? Toggle Visibility. ?>
			
					<?php  include_once( plugin_dir_path( __FILE__ ).'/assignment-editor.php' );
				
					// Restore original Post Data //
					 wp_reset_postdata();
				 
				 endif;
				 
				?>
				
				</div><!-- end of .post-entry -->	               
				   
			</div><!-- end of #post-<?php the_ID(); ?> -->       

  
			<div class="course-nav btm">
				<?php nb_course_nav_bar();?>
			</div><!-- end .course-nav btm -->
           