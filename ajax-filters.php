<?php
// We take the parameters from the url when someone wants to send us the link with the selected filters
$materialsB = $_GET['materials'][0];

// Create the array of taxonomies
$taxQuerry = array();
$taxQuerry[] = array('relation' => 'AND');
if($materialsB != null){
  $taxQuerry[] = array(
    'taxonomy'        => 'material',
		'field'           => 'slug',
		'terms'           =>  explode(",",$materialsB),
    'operator'        => 'IN',
  );
}

// If we have the parameters in the url

if($_GET){
  $args = array(
    'post_type'      => 'project',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'tax_query' => $taxQuerry,
  );
} else {
  $args=array(
  'post_type'      => 'project',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'orderby'        => 'title',
  'order'          => 'ASC',
  );
}

$my_posts =  new WP_Query ( $args ); 
$materials = get_terms( array(
  'taxonomy' => 'material',
  'hide_empty' => false,
));
?>

<section id="proiecte" class="min-h-[calc(100vh-156px)]">
  <div class="container sm:grid grid-cols-8 xl:grid-cols-5 pt-10 pb-10 gap-5">
    <form  method="POST" id="filter"  class="col-span-4 sm:col-span-3 lg:col-span-2 xl:col-span-1 grid grid-cols-2 sm:block gap-x-5">
      <div class="col-span-2 flex flex-col items-start">
        <button type="button" name="action" value="myfilter" id="reset" id="set-filter" class="set-filter flex items-center font-primary text-sm sm:text-lg text-[#606060] mb-5 gap-2">
          <span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
          <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
        </svg>  
      </span>
        Reset Search
        </button>
      </div>
        <div class="text-xs sm:text-sm parent-filtru ">
          <div class="cursor-pointer sm:cursor-default open-modal-filtru bg-primary text-center text-secondary  font-bold px-2 py-1 mb-5">Materials</div>
          <div id="set-filter" class="modal-filtru hidden sm:flex flex-col pb-5 shadow-lg px-5">
          <?php foreach($materials as $material):?>
            <div class="flex gap-2 uppercase">
              <input type="checkbox"  class="set-filter" name="set-material" id="<?php echo $material->slug?>" value="<?php echo $material->slug?>"
              <?php
                  if($_GET["materials"]){  // if we find this taxonomy in the url parameters
                    $materials_array = explode(",",$_GET["materials"][0]);

                    //We check if the taxonomies in the url are found among our inputs

                    for($i=0; $i< count($materials_array); $i++){
                      if( $material->slug == $materials_array[$i]) {
                        echo 'checked';
                      }
                    }
                  }
                ?>
              >
              <label class="cursor-pointer font-primary text-textGray" for="<?php echo $material->slug?>"><?php echo $material->name?></label>
            </div>
          <?php endforeach?>
          </div>
        </div>
    </form>
      <div class="col-span-5 sm:col-span-5 lg:col-span-6 xl:col-span-4">
        <div class="flex items-center justify-start sm:justify-end text-sm sm:text-lg text-[#606060] mb-5 font-primary">
        </div>
        <div id="response"></div>
        <?php if( $my_posts->have_posts() ): ?>
          <div id="grid-proiecte" class="grid-projects-test grid grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php while ( $my_posts->have_posts() ):?>
              <?php $my_posts->the_post();?>
              <a href="<?php echo get_the_permalink()?>" class="group relative p-5 flex flex-row items-end h-[234px] bg-cover bg-no-repeat" style="background-image:url('<?php echo get_the_post_thumbnail_url( $my_posts->ID ); ?>');">
              
                <div class="text-white relative z-[1] group-hover:opacity-0 duration-200">
                  <h3 class="text-2xl font-bold"><?php echo  get_the_title()?></h3>
                  <span class="flex items-center gap-2 text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12.68" height="7.132" viewBox="0 0 12.68 7.132">
                    <path id="np_eye_93123_FFFFFF" d="M6.34,18.75A8.017,8.017,0,0,0,0,22.316a8.017,8.017,0,0,0,6.34,3.566,8.014,8.014,0,0,0,6.34-3.566A8.017,8.017,0,0,0,6.34,18.75Zm0,1.189a2.377,2.377,0,1,1-2.377,2.377A2.378,2.378,0,0,1,6.34,19.939Zm0,1.189a1.189,1.189,0,1,0,1.189,1.189A1.189,1.189,0,0,0,6.34,21.127Z" transform="translate(0 -18.75)" fill="#fff" />
                  </svg>
                  Project details
                  </span>
                </div>
                <div class="group-hover:w-0 duration-200 absolute w-full h-full bg-black/50 top-0 left-0"></div>
              </a>
            <?php endwhile;?>
          </div>
            <?php else :?>
            <div class='remove-result-search flex justify-center flex-col items-center py-32 border-2 mt-10'><span class='font-primary font-bold text-center'>Your search has no results.</span>
            </div>
        <?php endif?>
      </div>
  </div>
</section>