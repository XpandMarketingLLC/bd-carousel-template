<?php /* Template Name: Home */

get_header(); 

	

			while ( have_posts() ) : the_post();
	
				get_template_part( 'template-parts/content', 'home' );
	
			endwhile;
		
		
		
get_footer();