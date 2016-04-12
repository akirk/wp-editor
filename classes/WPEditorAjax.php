<?php
class WPEditorAjax {
  
  public static function save_settings() {
    $error = '';
    
    foreach ( $_REQUEST as $key => $value ) {
      if ( $key[0] != '_' && $key != 'action' && $key != 'submit' ) {
        if ( is_array( $value ) ) {
          $value = implode( '~', $value );
        }
        if ( $key == 'wpeditor_logging' && $value == '1' ) {
          try {
            WPEditorLog::create_log_file();
          }
          catch( WPEditorException $e ) {
            $error = $e->getMessage();
            WPEditorLog::log( '[' . basename( __FILE__ ) . ' - line ' . __LINE__ . "] Caught WPEditor exception: " . $e->getMessage() );
          }
        }
        WPEditorSetting::set_value( $key, trim( stripslashes( $value ) ) );
      }
    }
    
    if (isset( $_REQUEST['_tab'] ) ) {
      WPEditorSetting::set_value( 'settings_tab', $_REQUEST['_tab'] );
    }
    
    if ( $error ) {
      $result[0] = 'WPEditorAjaxError';
      $result[1] = '<h3>' . __( 'Warning','wpeditor' ) . "</h3><p>$error</p>";
    }
    else {
      $result[0] = 'WPEditorAjaxSuccess';
      $result[1] = '<h3>' . __( 'Success', 'wp-editor' ) . '</h3><p>' . $_REQUEST['_success'] . '</p>'; 
    }
    
    $out = wp_json_encode( $result );
    echo $out;
    die();
  }
  
  public static function upload_file() {
    $upload = '';
    if ( isset( $_POST['current_theme_root'] ) ) {
      $upload = WPEditorBrowser::upload_theme_files();
    }
    elseif ( isset( $_POST['current_plugin_root'] ) ) {
      $upload = WPEditorBrowser::upload_plugin_files();
    }
    echo wp_json_encode( $upload );
    die();
  }
  
  public static function save_file() {
    $error = '';
    try {
      if ( isset( $_POST['new_content'] ) && isset( $_POST['real_file'] ) ) {
        $real_file = $_POST['real_file'];
        
        //detect and handle unc paths
        if ( substr( $real_file, 0, 4) === '\\\\\\\\' ) {
          $real_file = str_replace( '\\\\', '\\', $real_file );	
        }

        if ( file_exists( $real_file ) ) {
          if ( is_writable( $real_file ) ) {
            $new_content = stripslashes( $_POST['new_content'] );
            if ( file_get_contents( $real_file ) === $new_content ) {
              WPEditorLog::log( '[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Contents are the same" );
            }
            else {
              $f = fopen( $real_file, 'w+' );
              fwrite( $f, $new_content );
              fclose( $f );
              WPEditorLog::log( '[' . basename(__FILE__) . ' - line ' . __LINE__ . "] just wrote to $real_file" );
            }
          }
          else {
            $error = __( 'This file is not writable', 'wp-editor' );
          }
        }
        else {
          $error = __( 'This file does not exist', 'wp-editor' );
        }
      }
      else {
        $error = __( 'Invalid Content', 'wp-editor' );
      }
    }
    catch( WPEditorException $e ) {
      $error = $e->getMessage();
      WPEditorLog::log( '[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught WPEditor exception: " . $e->getMessage() );
    }
    
    if ( $error ) {
      $result[0] = 'WPEditorAjaxError';
      $result[1] = '<h3>' . __( 'Warning','wpeditor' ) . "</h3><p>$error</p>";
    }
    else {
      $result[0] = 'WPEditorAjaxSuccess';
      $result[1] = '<h3>' . __( 'Success', 'wp-editor' ) . '</h3><p>' . $_REQUEST['_success'] . '</p>'; 
    }
    
    if (isset( $_POST['extension'] ) ) {
      $result[2] = $_POST['extension'];
    }
    
    $out = wp_json_encode( $result );
    echo $out;
    die();
  }  
  
  public static function ajax_folders() {
    
    $dir = urldecode( $_REQUEST['dir'] );
    
    if ( isset( $_REQUEST['contents'] ) ) {
      $contents = $_REQUEST['contents'];
    }
    else {
      $contents = 0;
    }
    $type = null;
    if ( isset( $_REQUEST['type'] ) ) {
      $type = $_REQUEST['type'];
    }
    $out = wp_json_encode( WPEditorBrowser::get_files_and_folders( $dir, $contents, $type ) );
    echo $out;
    die();
  }
  
}
