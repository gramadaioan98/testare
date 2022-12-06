## Filtering some posts by taxonomies with Ajax

If you too have spent hours thinking about how to filter some custom posts without refreshing the page, you can consult our solution.

## Have you ever entered a website and had the problem that when you tick some inputs, the page refreshes and then those products/articles appear?

It is not a mistake, but it depends on the display speed of the products and is not a visual solution for the eye.

## Our solution

After several searches on different sites, we put together different solutions and "packaged" them so that it will be easy to build a custom post filter from scratch.

With the help of these ACF (Advanced Custom Fields) and CPT (Custom Post Type) modules, but also with Ajax and jQuery, we arrived at the best solution to create this type of filtering

## Below, we'll go step-by-step and find out everything we set out to do to make this filter

## Step 1. We create the CPT with the necessary taxonomies and then drag them where we need them on the page.

a. We created CPT for Projects.

b. Taxonomies: material, applications and county

```php
<?php
$materiale = get_terms( array(
  'taxonomy' => 'material',
  'hide_empty' => false,
));
?>
```

## Step 2. I created a form where we find the reset button, checkbox type inputs and a selection with different options with counties

## In ajax-filters.php, this is a block of ACF

```php

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

```
<p align="center">
<img src="https://github.com/gramadaioan98/testare/blob/main/filters.jpg?raw=true"/>
</p>
## Step 3. jQuery script to Send a Request and to Receive Result Data

In this step, we learn how to take the value from an input/select and send the request to the form submission.

I wrote comments in the code to follow each line step by step.

```js
$(".set-filter")
	.add("select[name=county]")
	.change(function () {
		//If the value in the input or select changes
		$(".remove-result-search").remove(); //Here the component that displays the message that nothing was found will be deleted.

		var filters_materials = []; //At each input change, we initialize each array

		// We go through all the inputs, and if they are checked, we add them to the array
		$("input[name=set-material]:checked").each(function (index, element) {
			filters_materials.push(this.value);
		});

		var filters_aplications = [];
		$("input[name=set-aplicatie]:checked").each(function (index, element) {
			filters_aplications.push(this.value);
		});
		var filters_county = "";

		if ($("select[name=county]").val()) {
			filters_county = $("select[name=county]").val();
		}
		$.ajax({
			url: "/wp-admin/admin-ajax.php",
			dataType: "json", // form data

			//We add additional data on request and the desired action, for us it is "myfilter"
			data: {
				action: "myfilter",
				materials: filters_materials,
				applicationsSet: filters_aplications,
				countySet: filters_county
			},
			type: "POST", // POST
			complete: function (data) {
				$("#response").html(data.responseText); // insert data

				//We delete the grid with the information that was displayed at the first rendering of the page
				$(".grid-projects-test").remove();
			}
		});
		window.history.pushState({}, document.title, "/");

		//We create a new URL and add the parameters we selected using inputs and select
		const url = new URL(window.location);

		if (filters_materials != 0) {
			url.searchParams.set("materials[]", [filters_materials]);
		}

		if (filters_aplications.length != 0) {
			url.searchParams.set("applicationsSet[]", [filters_aplications]);
		}

		if (filters_county.length != 0) {
			url.searchParams.set("countySet", filters_county);
		}

		window.history.pushState({}, "", url);

		return false;
	});
```

## Step 4. PHP code to Process the Request

In this part, we decide how to filter posts. This code is fully based on WP_Query.

```php


add_action('wp_ajax_myfilter', 'misha_filter_function'); // wp_ajax_{ACTION HERE}
add_action('wp_ajax_nopriv_myfilter', 'misha_filter_function');

function misha_filter_function(){

    // We use the information sent with Ajax
    $materialsB = $_POST['materials'];
    $aplicationB = $_POST['applicationsSet'];
    $countyB = $_POST['countySet'];

    //We create an array of taxonomies
    $taxQuerry = array();
    $taxQuerry[] = array('relation' => 'AND');

    //We check if there are taxonomies and add them to $taxQuerry
    if($materialsB){
        $taxQuerry[] = array(
          'taxonomy'        => 'material',
              'field'           => 'slug',
              'terms'           =>  $materialsB,
          'operator'        => 'IN',
        );
      }
      if($aplicationB){
        $taxQuerry[] = array(
          'taxonomy'        => 'applications',
              'field'           => 'slug',
              'terms'           =>  $aplicationB,
          'operator'        => 'IN',
        );
      }
      if($countyB ){
        $taxQuerry[] = array(
          'taxonomy'        => 'county',
              'field'           => 'slug',
              'terms'           =>  $countyB,
          'operator'        => 'IN',
        );
      }

        $args=array(
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
    while ( $my_posts->have_posts() ){
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
    if ( !$my_posts->have_posts() ) {
        $html_text = "<div class='flex justify-center flex-col items-center py-32 border-2 mt-10'><span class='font-primary font-bold text-center'>Your search has no results.</span>
      </div>";
    }
    echo $html_text;
	die();
}


```

## And at the end, we reset the filters and the ajax response to return to the initial state

```js
$("#reset").click(function () {
	window.history.pushState({}, document.title, "/");
	$("input[name=set-material]:checked").prop("checked", false);
	$("input[name=set-aplicatie]:checked").prop("checked", false);
	$("select[name=county]").prop("selectedIndex", 0);
	$.ajax({
		url: "/wp-admin/admin-ajax.php",
		dataType: "json", // form data
		data: {
			action: "myfilter"
		},
		type: "POST", // POST
		complete: function (data) {
			$("#response").html(data.responseText); // insert data

			$(".grid-projects-test").addClass("hidden");
		}
	});
	return false;
});
```
