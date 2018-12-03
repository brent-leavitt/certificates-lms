<?php

//	Custom Template: Course Section
// 	This is not a standard Wordpress template, but is just being called via a php includes statement located in the doula-course plugin.

global $post, $nb_page_title;

?>
	
		<div class="course-nav top">
			<?php nb_course_nav_bar();?>
		</div><!-- end .course-nav top -->
		
		<div <?php post_class(); ?> > 
			<div class="post-entry">
				
				<?php 
				if( empty( $nb_page_title ) || strcmp( $post->post_title, $nb_page_title ) !== 0  ){
					echo"<h2>{$post->post_title}</h2>"; 
				}
				?>
				<?php 
				if( !empty( $post->post_content ) )	
					the_content(__('Read more &#8250;', 'responsive')); ?>
			
				<ul id="section-list">
					<?php wp_list_pages($list_args); ?>
				</ul>  
			</div><!-- end of .post-entry -->
		</div><!-- end of #post-<?php the_ID(); ?> -->   
		

		<div class="course-nav btm">
			<?php nb_course_nav_bar();?>
		</div><!-- end .course-nav btm -->
