<?php


//	Custom Template: Course Content
 
// 	This is not a standard Wordpress template, but is just being called via a php includes statement located in the doula-course plugin.


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
                                       
                </div><!-- end of .post-entry -->
			</div><!-- end of #post-<?php the_ID(); ?> -->       
            
			<div class="course-nav btm">
				<?php nb_course_nav_bar();?>
			</div><!-- end .course-nav btm -->