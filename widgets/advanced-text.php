<?php

class Advanced_Text_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(false, 'HTML', array('classname' => 'widget_advanced_text', 'description' => 'Arbitrary text or HTML'), array('width' => 800, 'height' => 450));
    }

    function widget( $args, $instance ) {
        extract($args);
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        $text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
        echo $before_widget;
        //if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
            <div class="htmlwidget"><?php echo !empty( $instance['filter'] ) ? wpautop( $text ) : $text; ?></div>
        <?php
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        if ( current_user_can('unfiltered_html') )
            $instance['text'] =  $new_instance['text'];
        else
            $instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
        $instance['filter'] = isset($new_instance['filter']);
        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
        $title = strip_tags($instance['title']);
        $text = esc_textarea($instance['text']);
?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" placeholder="Title" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <input type="hidden" class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" />
        <div style="position: relative; height: 264px; margin-bottom: 3px;">
            <div id="<?php echo $this->get_field_id('text'); ?>_ace" style="right: 0; left: 0; top: 0; bottom: 0; background: #fff;"><?php echo strtr($text, '<', '&lt;'); ?></div>
        </div>
        <script src="http://d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
        <script>
        (function() {
            var editor = ace.edit("<?php echo $this->get_field_id('text'); ?>_ace");
            editor.setTheme("ace/theme/clouds");
            editor.getSession().setMode("ace/mode/html");
            editor.on("change", function(e) {
                jQuery('#<?php echo $this->get_field_id('text'); ?>').val(editor.getValue());
            });
        })();
        </script>

        <p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
<?php
    }
}
