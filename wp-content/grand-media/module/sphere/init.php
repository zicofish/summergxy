<?php
/** @var $gmDB
 * @var  $gmCore
 * @var  $gmGallery
 * @var  $gallery
 * @var  $module
 * @var  $settings
 * @var  $terms
 * @var  $gmedia
 * @var  $is_bot
 **/
$content = array();
if(!isset($is_bot)){ $is_bot = false; }
if(!isset($shortcode_raw)){ $shortcode_raw = false; }
$tab = sanitize_title($gallery['name']);
foreach($terms as $term){

	$c = array(
		'cID' => "{$tab}_{$term->term_id}",
		'name' => $term->name,
		'description' => str_replace(array("\r\n", "\r", "\n"), '', wpautop($term->description)),
		'data' => array()
	);

	foreach($gmedia[$term->term_id] as $item){
		if('image' != substr($item->mime_type, 0, 5)){
			continue;
		}
		$meta['views'] = intval($gmDB->get_metadata('gmedia', $item->ID, 'views', true));
		$meta['likes'] = intval($gmDB->get_metadata('gmedia', $item->ID, 'likes', true));
		$_metadata = $gmDB->get_metadata('gmedia', $item->ID, '_metadata', true);
		if(!empty($item->link)){
			$item->title = '<a href="' . $item->link . '"><b>' . $item->title . '</b></a>';
		}
		$c['data'][] = array(
			'id' => $item->ID,
			'image' => "/{$gmGallery->options['folder']['image']}/{$item->gmuid}",
			'thumb' => "/{$gmGallery->options['folder']['image_thumb']}/{$item->gmuid}",
			'title' => $item->title,
			'description' => str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)),
			'date' => $item->date,
			'views' => $meta['views'],
			'likes' => $meta['likes'],
			'websize' => array_values($_metadata['web']),
			'thumbsize' => array_values($_metadata['thumb'])
		);
	}
	if(!count($c['data'])){
		continue;
	}
	$content[] = $c;
}

if(!empty($content)){
	$settings = array_merge($settings, array(
		'ID' => $gallery['term_id'],
		'galleryName' => $gallery['name'],
		'moduleUrl' => $module['url'],
		'pluginUrl' => $gmCore->gmedia_url,
		'libraryUrl' => $gmCore->upload['url'],
		'key' => $gmGallery->options['license_key2']
	));
	?>
	<div id="gmSphere_ID<?php echo $gallery['term_id']; ?>_Container" class="gmSphere_Container">
		<?php if($shortcode_raw){ echo '<pre style="display:none">'; }
		?><script type="text/javascript">
			var GmediaGallery_<?php echo $gallery['term_id']; ?>;
			jQuery(function(){
				var settings = <?php echo json_encode($settings); ?>;
				var content = <?php echo json_encode($content); ?>;
				GmediaGallery_<?php echo $gallery['term_id']; ?> = jQuery('#GmediaGallery_<?php echo $gallery['term_id'] ?>').gmSphere([content, settings]);
			});
		</script><?php if($shortcode_raw){ echo '</pre>'; } ?>
		<?php if(!$is_bot) { echo '<script type="text/html" id="flashmodule_alternative_'.$gallery['term_id'].'">'; }
		?><div class="flashmodule_alternative <?php if(!$is_bot) { echo 'delay'; } ?> noLightbox">
			<div class="gmcatlinks"><?php
				foreach($content as $cat){
					echo "<a class='gmcat' href='#{$cat['cID']}'>{$cat['name']}</a>";
				}
				?></div>
<?php foreach($content as $cat){ ?>
			<div class="gmcategory" id="<?php echo $cat['cID']; ?>"><div class="gmcatmeta"><h4><?php echo $cat['name']; ?></h4><?php echo $cat['description']; ?></div>
				<?php $i = 0;
				foreach($cat['data'] as $item){
					$orientation = (1 < $item['thumbsize'][0]/$item['thumbsize'][1])? 'landscape' : 'portrait';
					?><div class="gmcatimage gm_<?php echo $i; ?>" id="gmid_<?php echo $item['id']; ?>"><?php
					?><a class="photoswipe" href="<?php echo $settings['libraryUrl'].$item['image']; ?>" title="<?php echo esc_attr($item['title']); ?>" rel="<?php echo $cat['cID']; ?>" data-id="<?php echo $item['id']; ?>" data-width="<?php echo $item['websize'][0]; ?>" data-height="<?php echo $item['websize'][1]; ?>" data-date="<?php echo $item['date']; ?>"><?php
					?><img class="<?php echo $orientation; ?>" src="<?php echo $settings['libraryUrl'].$item['thumb']; ?>" alt="<?php echo esc_attr($item['title']); ?>" /><?php
					//$views = (intval($item['views']) < 10000) ? $item['views'] : round($item['views']/1000, 1).'k';
					//$likes = (intval($item['likes']) < 10000) ? $item['likes'] : round($item['likes']/1000, 1).'k';
					/*<span class="gmcatimage_counters"><i><?php echo $views; ?></i><b><?php echo $likes; ?></b></span>*/
					?></a><?php
					?><div class="gmcatimage_caption"><div class="gmcatimage_title"><?php echo $item['title']; ?></div><div class="gmcatimage_description"><?php echo $item['description']; ?></div></div><?php
					?></div><?php
					$i++;
				} ?></div>
<?php } ?>
		</div><?php
		if(!$is_bot) { echo '</script>'; }
		if(empty($settings['key'])){ echo "<a class='gmlove' target='_blank' href='{$module['info']['demo']}'>".__('built with GmediaGallery', 'grand-media')."</a>"; }
	?></div>
<?php
} else{
	echo GMEDIA_GALLERY_EMPTY;
}