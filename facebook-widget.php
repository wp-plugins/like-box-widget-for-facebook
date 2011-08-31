<?php
/*
Plugin Name: Like Box Widget for Facebook
Plugin URI: //update with plugin website link
Description: Display facebook widget as a box in your WordPress blog.
Author: WebpageFX
Version: 1.0
Author URI: http://www.webpagefx.com/

Copyright 2011 (email : support@webpagefx.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class wp_fb_widget {
   
   protected $_name = "Facebook widget";
   protected $_folder;
   protected $_path;
   protected $_width = 300;
   protected $_height = 320;
   protected $_link = ''; //update with plugin website link
   
   /*
    * Constructor
    */
   function __construct() {
      $path = __FILE__;
      if (!$path) { $path = $_SERVER['PHP_SELF']; }
      $current_dir = dirname($path);
      $current_dir = str_replace('\\', '/', $current_dir);
      $current_dir = explode('/', $current_dir);
      $current_dir = end($current_dir);
      $this->_folder = $current_dir;
      $this->_path = '/wp-content/plugins/' . $this->_folder . '/';

      $this->init();
   }
   
   /*
    * Initialization function, called by plugin_loaded action.
    */
   function init() {
      add_filter("plugin_action_links_$plugin", array(&$this, 'link'));
      load_plugin_textdomain($this->_folder, false, $this->_folder);      
      
      if (!function_exists('register_sidebar_widget') || !function_exists('register_widget_control'))
         return;
      register_sidebar_widget($this->_name, array(&$this, "widget"));
      register_widget_control($this->_name, array(&$this, "form"), $this->_width, $this->_height); //admin form 
   }

   /*
    * Options validation function.
    */
   function validate_options(&$options) {
      if (!is_array($options)) {
         $options = array(
            'width' => "250", 
            'height' => "280", 
            'profile_id' => "",      
            'connections' => "4",
            'stream' => "",
            'header' => "",
            'locale' => "",);
      }
      
      // validations and defaults
      if (intval($options['width']) == 0) $options['width'] = '250';
      if (intval($options['height']) == 0) $options['height'] = '280';
      if (intval($options['connections']) == 0) $options['connections'] = '8';
	  if ($options['stream'] == "checked" && intval($options['height']) < 500) $options['height'] = '500';
   }
   
   /*
    * Called by register_sidebar_widget() function.
    * Widget rendered.
    */
   function widget($args) {
		extract($args);

		$options = get_option($this->_folder);
		//var_dump($options);
		$this->validate_options($options);
		
		?>
		<!-- Link Javascript and css files here or in the C# source  -->
		
		<?php
		try 
		{ 
			$client = new SoapClient("http://www.opensourceplugins.com/FacebookWidget/FacebookWidget.Service1.svc?wsdl");
			$params = array('height'=>$options['height'],'width'=>$options['width'],'id'=>$options['profile_id'],'src'=>"http://www.facebook.com/connect/connect.php",
			'connections'=>$options['connections'],'stream'=>$options['stream'],'header'=>$options['header'],'locale'=>$options['locale']);
			$response = $client->GetFacebookWidgetBody($params);
			echo $response->GetFacebookWidgetBodyResult; 	

		} 
		catch (SoapFault $E) 
		{  
			echo("Install SOAP"); 
		}
   }

   /*
    * Called by register_sidebar_control() function
	* Plugin form funtion, shown in the admin panel.
	* Form rendered
    */
   function form() {
      $options = get_option($this->_folder);
      $this->validate_options($options);
      if ($_POST[$this->_folder . '-submit']) {
         $options['width'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-width']));
         $options['height'] = htmlspecialchars($_POST[$this->_folder . '-height']);
         $options['profile_id'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-profile_id']));
         $options['connections'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-connections']));
         $options['stream'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-stream']));
         $options['header'] = htmlspecialchars($_POST[$this->_folder . '-header']);
         $options['locale'] = htmlspecialchars(stripslashes($_POST[$this->_folder . '-locale']));
         update_option($this->_folder, $options);
      }
?>
      <p>
         <label for="<?php echo($this->_folder) ?>-width"><?php _e('Width: ', $this->_folder); ?></label>
         <input type="text" id="<?php echo($this->_folder) ?>-width" name="<?php echo($this->_folder) ?>-width" value="<?php echo $options['width']; ?>" size="2"></input> (<a href="<?php echo $this->_link?>#width" target="_blank">?</a>)
      </p>
      <p>
         <label for="<?php echo($this->_folder) ?>-title"><?php _e('Height: ', $this->_folder); ?></label>
         <input type="text" id="<?php echo($this->_folder) ?>-height" name="<?php echo($this->_folder) ?>-height" value="<?php echo $options['height']; ?>" size="2"></input> (<a href="<?php echo $this->_link?>#height" target="_blank">?</a>)
      </p>
      <p>
         <label for="<?php echo($this->_folder) ?>-title"><?php _e('Profile Id: ', $this->_folder); ?></label>
         <input type="text" id="<?php echo($this->_folder) ?>-profile_id" name="<?php echo($this->_folder) ?>-profile_id" value="<?php echo $options['profile_id']; ?>" size="20"></input> (<a href="<?php echo $this->_link?>#profile-id" target="_blank">?</a>)
      </p>
      <p>
         <label for="<?php echo($this->_folder) ?>-connections"><?php _e('Connections: ', $this->_folder); ?></label>
         <input type="text" id="<?php echo($this->_folder) ?>-connections" name="<?php echo($this->_folder) ?>-connections" value="<?php echo $options['connections']; ?>" size="2"></input> (<a href="<?php echo $this->_link?>#connections" target="_blank">?</a>)
      </p>
      <p>
          <input type="checkbox" id="<?php echo($this->_folder) ?>-stream" name="<?php echo($this->_folder) ?>-stream" value="checked" <?php echo $options['stream'];?> /> <?php _e('Stream', $this->_folder) ?> (<a href="<?php echo $this->_link?>#stream" target="_blank">?</a>)       
      </p>
	  <!--
      <p>
          <input type="checkbox" id="<?php echo($this->_folder) ?>-stream" name="<?php echo($this->_folder) ?>-header" value="checked" <?php echo $options['header'];?> /> <?php _e('Header', $this->_folder) ?> (<a href="<?php echo $this->_link?>#wg-header" target="_blank">?</a>)
      </p> -->
 
      <p><?php printf(__('More details about these options, visit <a href="%s" target="_blank">Plugin Home</a>', $this->_folder), $this->_link) ?></p>
      <input type="hidden" id="<?php echo($this->_folder) ?>-submit" name="<?php echo($this->_folder) ?>-submit" value="1" />
<?php
   }
   
} // widget class ends

// initialization function, create a instance of the widget  
function wp_fb_widget_init() { $likebox = new wp_fb_widget();}
// wordpress plugin action hook
add_action('plugins_loaded', 'wp_fb_widget_init');