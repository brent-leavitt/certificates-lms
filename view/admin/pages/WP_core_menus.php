<?php

/*
	Education Page
		
*/


$core_menus = array(
	'posts' => array(
		'edit.php' => 'All Posts',
		'post-new.php' => 'Add New Post',
		'edit-tags.php?taxonomy=category' => 'Categories',
		'edit-tags.php?taxonomy=post_tag' => 'Tags',
	),
	'Media' => array(
		'upload.php' => 'Library',
		'media-new.php' => 'Add New',
	),
	'Pages' => array(
		'edit.php?post_type=page' => 'All Pages',
		'post-new.php?post_type=page' => 'Add New Page',
	),
	'Appearance' => array(
		'themes.php' => 'Themes',
		'customize.php' => 'Customize',
		'widgets.php' => 'Widgets',
		'nav-menus.php' => 'Menus',
		'themes.php?page=custom-header' => 'Headers',
		'themes.php?page=ktoptions' => 'Theme Options',
		'themes.php?page=install_recommended_plugins' => 'Theme Plugins',
		'/wp-admin/theme-editor.php' => 'Editor',
	),
	'Plugins' => array(
		'plugins.php' => 'Installed Plugins',
		'plugin-install.php' => 'Add New Plugin',
		'plugin-editor.php' => 'Editor',
	),
	'Users' => array(
		'user.php' => 'All Users',
		'profile.php' => 'Your Profile',
	),
	'Tools' => array(
		'tools.php' => 'Available Tools',
		'import.php' => 'Import',
		'export.php' => 'Export',
		'tools.php?page=export_personal_data' => 'Export Personal Data',
		'tools.php?page=remove_personal_data' => 'Erase Personal Data',
	),
	'Settings' => array(
		'options-general.php' => 'General',
		'options-writing.php' => 'Writing',
		'options-reading.php' => 'Reading',
		'options-discussion.php' => 'Discussion',
		'options-media.php' => 'Media',
		'options-permalink.php' => 'Permalinks',
		'privacy.php' => 'Privacy',
	),
);
?>
<p>Below are the core menues from WordPress.</p>
<hr />
<div id="dashboard-widgets" class="metabox-holder">

<?php 
	foreach( $core_menus as $core_key => $submenu ):
?>
	
	<div class="postbox-container">
		<div class="meta-box-sortables">
			<div class="postbox">
				<h2 class="hndle ui-sortable-handle"><a href="/wp-admin/<?php echo key($submenu); ?>"><?php echo $core_key; ?></a></h2>
				<div class="inside">
					<div class="main">
						<ul>
						
							<?php foreach( $submenu as $subkey => $subval ): ?>
							
								<li><a href="/wp-admin/<?php echo $subkey; ?>"><?php echo $subval; ?></a></li>
					
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	
<?
	endforeach;
?>
</div>

