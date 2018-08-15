<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
  exit();

delete_option('purechat_widget_code');
delete_option('purechat_widget_name');
delete_option('purechat_plugin_ver');

?>
