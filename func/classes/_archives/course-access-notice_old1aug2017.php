<?php


//	Custom Template: Course Content
 
// 	This is not a standard Wordpress template, but is just being called via a php includes statement located in the doula-course plugin.


	 ?>    

			<div class="course-nav top">
				<?php nb_course_nav_bar();?>
			</div><!-- end .course-nav top -->
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>> 
				<div class="post-entry">
                   <h2>Limited Access</h2>
				   
				   <p>We don't blame you for wanting to look ahead, but your account doesn't have access to this part of your training yet. To gain access, you'll either have to complete all assignments from previous sections up to this point, or you'll have to have made the appropriate number of monthly payments.</p> 
				   
				   <h3>Feel Like You Should Already Have Access?</h3>
				   
				   <p>If you feel like there's been an error in our records and you should have access to this part of the course, send us a note to let us know and we'll take a look at it. Thanks!</p>
				   <hr>
				   
				   <h4>Want Full Course Access?</h4>
				   
				   <p>Of course, if you want to pay off the balance of your payments, you can request a payoff statement at any time and once that payment gets recorded, you'll gain full course access to the rest of the training. Request a payoff statement!</p>                 
                </div><!-- end of .post-entry -->
			</div><!-- end of #post-<?php the_ID(); ?> -->       
            
			<div class="course-nav btm">
				<?php nb_course_nav_bar();?>
			</div><!-- end .course-nav btm -->