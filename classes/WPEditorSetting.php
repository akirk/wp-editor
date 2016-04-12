<?php
class WPEditorSetting {
  
  public static function set_value( $key, $value ) {
    global $wpdb;
    $settings_table = WPEditor::get_table_name( 'settings' );
    
    if ( ! empty( $key ) ) {
      $db_key = $wpdb->get_var( "SELECT `key` from $settings_table where `key`='$key'" );
      if ( $db_key ) {
        if ( ! empty( $value ) || $value !== 0 ) {
          $wpdb->update( $settings_table, 
            array( 'key'=>$key, 'value'=>$value ),
            array( 'key'=>$key ),
            array( '%s', '%s' ),
            array( '%s' )
          );
        }
        else {
          $wpdb->query( "DELETE from $settings_table where `key`='$key'" );
        }
      }
      else {
        if ( !empty( $value ) || $value !== 0 ) {
          $wpdb->insert( $settings_table, 
            array( 'key'=>$key, 'value'=>$value ),
            array( '%s', '%s' )
          );
        }
      }
    }
    
  }
  
  public static function get_value( $key, $entities=false ) {
    $value = false;
    global $wpdb;
    $settings_table = WPEditor::get_table_name( 'settings' );
    $value = $wpdb->get_var( "SELECT `value` from $settings_table where `key`='$key'" );
    
    if(!empty( $value ) && $entities ) {
      $value = htmlentities( $value );
    }
    
    return $value;
  }
  
}