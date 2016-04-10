editor_status = '';
tags = {};
(function($){
  $(window).resize(function() {
    $('.CodeMirror-scroll').height($('.CodeMirror-wrap').height() - $('#wp-editor-quicktags').height() - 3);
  });
  $(window).load(function() {
    setupPostEditor();
  });

  $(document).ready(function(){
    $('#content').attrchange({
      trackValues: true,
      /* enables tracking old and new values */
      callback: function(event) { //callback handler on DOM changes  
        if(event.attributeName == 'style') {
          if(event.oldValue !== event.newValue) {
            $('.CodeMirror').css('top', $('#content').css('margin-top'));
            $('.CodeMirror').css('margin-bottom', $('#content').css('margin-top'));
          }
        }
      }
    });
    /*
    $('#wpe_qt_content_fullscreen').live("click", function() {
      toggleFullscreenEditing();
      wp_editor.focus();
    });
    $('#wpe_qt_content_save').live("click", function() {
      wp_editor.save();
      $('#wp_mce_fullscreen').val($('#content').val());
      wp.editor.fullscreen.save();
      changeReset();
    });*/
    $('body').on('click', '#wp-link-submit', function() {
      wp_editor.toTextArea();
      wpLink.update();
      var element = document.getElementById('content');
      var cursor = window.get_content_cursor(element, element.selectionStart);
      postCodeMirror('content');
      wp_editor.setCursor(cursor.line, cursor.ch);
    });

    //$('#content-tmce').attr('onclick','').unbind('click');
    //$('#content-html').attr('onclick','').unbind('click');

    $('#content-tmce').click(function() {
      if(editor_status !== 'tmce') {
        var scrollPosition = wp_editor.getScrollInfo();
        document.cookie="scrollPositionX=" + scrollPosition.x;
        document.cookie="scrollPositionY=" + scrollPosition.y;
        wp_editor.toTextArea();
        id = $(this).attr( 'data-wp-editor-id' );
        switchEditors.go(id, 'tmce');
        editor_status = 'tmce';
        return false;
      }
    });
    $('#content-html').click(function() {
      if(editor_status !== 'html') {
        id = $(this).data( 'wp-editor-id' );
        switchEditors.go(id, 'html');
        setTimeout(function() {
          postCodeMirror('content');
          wp_editor.scrollTo(getCookie('scrollPositionX'), getCookie('scrollPositionY'));
        }, 0);
        editor_status = 'html';
        return false;
      }
      else {
        var scrollPosition = wp_editor.getScrollInfo();
        wp_editor.toTextArea();
        postCodeMirror('content');
        wp_editor.scrollTo(scrollPosition.x, scrollPosition.y);
        document.cookie="scrollPositionX=" + scrollPosition.x;
        document.cookie="scrollPositionY=" + scrollPosition.y;
        return false;
      }
    })
    $('#post').submit(function(e) {
      changeReset();
      if(editor_status == 'html') {
        var scrollPosition = wp_editor.getScrollInfo();
        document.cookie="scrollPositionX=" + scrollPosition.x;
        document.cookie="scrollPositionY=" + scrollPosition.y;
        wp_editor.save();
      }
    })
  })
  function getCookie(key, sub_key) {
    currentcookie = document.cookie;
    if(currentcookie.length > 0) {
      firstidx = currentcookie.indexOf(key + "=");
      if(firstidx != -1) {
        firstidx = firstidx + key.length + 1;
        lastidx = currentcookie.indexOf(";",firstidx);
        if(lastidx == -1) {
          lastidx = currentcookie.length;
        }
        if(sub_key) {
          var result = {};
          unescape(currentcookie.substring(firstidx, lastidx)).split("&").forEach(function(part) {
            var item = part.split("=");
            result[item[0]] = decodeURIComponent(item[1]);
          });
          return result[sub_key];
        }
        return unescape(currentcookie.substring(firstidx, lastidx));
      }
    }
    return "";
  }
  function setupPostEditor() {
    editor_status = getCookie('wp-settings-1', 'editor');
    if(editor_status == 'html') {
      postCodeMirror('content');
      //wp_editor.scrollTo(getCookie('scrollPositionX'), getCookie('scrollPositionY'));
    }
  }
  window.get_content_cursor = function(element, caret) {
    var lines = element.value.substr(0, this.selectionStart).split("\n");
    var newLength = 0, line = 0, lineArray = [];
    $.each(lines, function(key, value) {
      newLength = newLength + value.length + 1;
      lineArray[line] = newLength;
      if(caret > value.length) {
        caret -= value.length + 1
      }
      else {
        return false;
      }
      line++;
    });
    return {"line": line, "ch": caret};
  }
  window.set_content_cursor = function(element, cursor) {
    var lines = element.value.substr(0, this.selectionStart).split("\n");
    var newLength = 0, line = 1, lineArray = [];
    lineArray[0] = 0;
    $.each(lines, function(key, value) {
      newLength = newLength + value.length + 1;
      lineArray[line] = newLength;
      line++;
    });
    var start = lineArray[cursor.line] + cursor.ch, end = lineArray[cursor.line] + cursor.ch;
    return $.each(element, function() {
      if(element.setSelectionRange) {
        element.focus();
        element.setSelectionRange(start, end);
      }
      else if(element.createTextRange) {
        var range = element.createTextRange();
        range.collapse(true);
        range.moveEnd('character', end);
        range.moveStart('character', start);
        range.select();
      }
    });
  };
  window.wp_editor_send_to_editor = window.send_to_editor;
  window.send_to_editor = function(html){
    if(editor_status == 'html') {
      var cursor = wp_editor.getCursor(true);
      wp_editor.toTextArea();
      window.set_content_cursor($('#content'), cursor);
      window.wp_editor_send_to_editor(html);
      postCodeMirror('content');
      wp_editor.setCursor(cursor.line, cursor.ch + html.length)
      wp_editor.focus();
    }
    else {
      window.wp_editor_send_to_editor(html);
    }
  };
  function postCodeMirror(element) {
    var activeLine = WPEPosts.activeLine;
    wp_editor = CodeMirror.fromTextArea(document.getElementById(element), {
      mode: 'wp_shortcodes',
      theme: WPEPosts.theme,
      lineNumbers: WPEPosts.lineNumbers,
      lineWrapping: WPEPosts.lineWrapping,
      indentWithTabs: WPEPosts.indentWithTabs,
      indentSize: WPEPosts.indentSize,
      tabSize: WPEPosts.tabSize,
      onCursorActivity: function() {
        if(activeLine) {
          wp_editor.addLineClass(hlLine, null, null);
          hlLine = wp_editor.addLineClass(wp_editor.getCursor().line, null, activeLine);
        }
      },
      onChange: function() {
        changeTrue();
      },
      onKeyEvent: function(editor, event) {
        if(typeof(wpWordCount) != 'undefined') {
          wp_editor.save();
          last = 0, co = $('#content');
          $(document).triggerHandler('wpcountwords', [ co.val() ]);
          
          co.keyup(function(e) {
            var k = event.keyCode || event.charCode;
            
            if(k == last) {
              return true;
            }
            if(13 == k || 8 == last || 46 == last) {
              $(document).triggerHandler('wpcountwords', [ co.val() ]);
            }
            last = k;
            return true;
          });
        }
        
      },
      extraKeys: {
        'F11': toggleFullscreenEditing, 
        'Esc': toggleFullscreenEditing
      }
    });
    $('.CodeMirror').css('font-size', WPEPosts.fontSize);
    $('.CodeMirror').css('top', $('#content').css('margin-top'));
    $('.CodeMirror').css('margin-bottom', $('#content').css('margin-top'));
    if(activeLine) {
      var hlLine = wp_editor.addLineClass(0, activeLine);
    }
    if(WPEPosts.editorHeight) {
      $('.CodeMirror-scroll, .CodeMirror, .CodeMirror-gutter').height(WPEPosts.editorHeight + 'px');
      var scrollDivHeight = $('.CodeMirror-scroll div:first-child').height();
      var editorDivHeight = $('.CodeMirror').height();
      if(scrollDivHeight > editorDivHeight) {
        $('.CodeMirror-gutter').height(scrollDivHeight);
      }
    }
    /*if(!$('.CodeMirror .quicktags-toolbar').length) {
      $('.CodeMirror').prepend('<div id="wp-editor-quicktags" class="quicktags-toolbar">' + 
        '<div id="wp-fullscreen-save">' +
        '<input type="button" id="wpe_qt_content_save" class="button-primary" title="" value="' + WPEPosts.save + '" style="float:right;display:none;font-family: \'Open Sans\', sans-serif;"><span class="spinner"></span></div>' +
        '<input type="button" id="wpe_qt_content_fullscreen" class="ed_button" title="" value="fullscreen">' + 
        '</div>'
      ).height($('.CodeMirror').height() + 33);
      $('.CodeMirror-scroll').height($('.CodeMirror-wrap').height() - $('#wp-editor-quicktags').height() - 3);
      wp_editor.focus();
    }*/
  }
})(jQuery);