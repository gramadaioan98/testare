<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'tailpress' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

// /** Custom Blocks **/
require_once( 'inc/blocks.php' );


add_action('wp_head', 'function_filter');

add_action('wp_ajax_myfilter', 'inntech_filter_function'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_myfilter', 'inntech_filter_function');

function inntech_filter_function(){

    // We use the information sent with Ajax
    $materialsB = $_POST['materials'];
    // We create an array of taxonomies
    $taxQuerry = array();
    $taxQuerry[] = array('relation' => 'AND');

    // We check if there are taxonomies and add them to $taxQuerry

    if($materialsB){ 
        $taxQuerry[] = array(
            'taxonomy'        => 'material',
            'field'           => 'slug',
            'terms'           =>  $materialsB,
            'operator'        => 'IN',
        );
    }

    $args = array(
        'post_type'      => 'project',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query' => $taxQuerry,  
    );  
    
    $my_posts =  new WP_Query ( $args ); 
    $html_text = '<div id="grid-proiecte" class="grid grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 gap-5">';

    // Here we create the answer for display on the website
    while($my_posts->have_posts()){
        $my_posts->the_post();
        $html_text .= "<a href='";
        $html_text .= get_the_permalink();
        $html_text .= "' ";
        $html_text .= " class='group relative p-5 flex flex-row items-end h-[234px] bg-cover bg-no-repeat' style=background-image:url('";
        $html_text .= get_the_post_thumbnail_url( $my_posts->ID );
        $html_text .= "') >";
        $html_text .= "<div class='text-white relative z-[1] group-hover:opacity-0 duration-200'>";
        $html_text .= "<h3 class='text-2xl font-bold'>";
        $html_text .= get_the_title();
        $html_text .= "</h3>";
        $html_text .= "<span class='flex items-center gap-2 text-white'>
        <svg xmlns='http://www.w3.org/2000/svg' width='12.68' height='7.132' viewBox='0 0 12.68 7.132'>
        <path id='np_eye_93123_FFFFFF' d='M6.34,18.75A8.017,8.017,0,0,0,0,22.316a8.017,8.017,0,0,0,6.34,3.566,8.014,8.014,0,0,0,6.34-3.566A8.017,8.017,0,0,0,6.34,18.75Zm0,1.189a2.377,2.377,0,1,1-2.377,2.377A2.378,2.378,0,0,1,6.34,19.939Zm0,1.189a1.189,1.189,0,1,0,1.189,1.189A1.189,1.189,0,0,0,6.34,21.127Z' transform='translate(0 -18.75)' fill='#fff' />
        </svg>
        Project details
        </span>";
        $html_text .= "</div>";
        $html_text .= "<div class='group-hover:w-0 duration-200 absolute w-full h-full bg-black/50 top-0 left-0'></div>";
        $html_text .= "</a>";
    };
   
    $html_text .= "</div>";
    // And if we don't find anything, we send an information box
    if (!$my_posts->have_posts()) {
        $html_text = "<div class='flex justify-center flex-col items-center py-32 border-2 mt-10'><span class='font-primary font-bold text-center'>Your search has no results.</span>
      </div>";
    }
    echo $html_text;
	die();
}

function function_filter() {
?>
<script>
    
jQuery(document).ready(function($) {

    $('#reset').click(function(){
        window.history.pushState({}, document.title, "/" );
        $("input[name=set-material]:checked").prop( "checked", false );
        $.ajax({
			url:"/wp-admin/admin-ajax.php",
			dataType: "json", // form data
            data: {
                action: 'myfilter',
            },
			type:"POST", // POST
			complete:function(data){
				$('#response').html(data.responseText); // insert data
                
                $('.grid-projects-test').addClass('hidden');
			}
		});
		return false;
    });

 $('.set-filter').change(function(){  // If the value in the input or select changes
    $('.remove-result-search').remove(); // Here the component that displays the message that nothing was found will be deleted.

    var filters_materials = []; // At each input change, we initialize each array
   
    // We go through all the inputs, and if they are checked, we add them to the array
    $("input[name=set-material]:checked").each(function(index, element){
        filters_materials.push(this.value)
    }); 
       
		$.ajax({
			url:"/wp-admin/admin-ajax.php",
			dataType: "json", // form data

            // We add additional data on request and the desired action, for us it is "myfilter"
            data: {  
                action: 'myfilter',
                materials: filters_materials, 
            },
			type:"POST", // POST
			complete:function(data){
				$('#response').html(data.responseText); // insert data

                // We delete the grid with the information that was displayed at the first rendering of the page
                $('.grid-projects-test').remove(); 
			}
		});
        window.history.pushState({}, document.title, "/");

    // We create a new URL and add the parameters we selected using inputs and select

    const url = new URL(window.location);
    
    if (filters_materials  != 0){
        url.searchParams.set('materials[]', [filters_materials]);
    }

    window.history.pushState({}, '', url);

    return false;

	});

});

</script>
<?php 
}
?>

