# Enlighten

Enlighten is a collection of Wordpress optimizations, useful template tags, and shortcodes. It includes a modified version of the cleanup optimizations from [Roots Theme](https://github.com/retlehs/roots/blob/master/doc/cleanup.md).

Hooks into __wp_enqueue_style__ allowing automatic compilation of SASS, SCSS, and LESS files. Compiled stylesheets are saved to the Wordpress uploads directory.

### Template Tags

```php 
the_content_from($page /* ID, slug or title */, $suppress_filters = false)
```

```php
get_template_part_for($slug, [$name = '',] $args)
```

The __faux_loop__ template tag emulates a WP_Query style loop. It has a couple advantages over using a WP_Query object directly though. Most noticeably the ability to easily place an existing array of posts into a loop. 
```php
// Possible arguments are: a WP_Query object, WP_Query args or an array of posts
$loop = faux_loop(array(
  'post_type' => 'events',
  'no_paging' => true,
  'meta_key'  => 'date',
  'orderby'   => 'meta_value',
  'order'     => 'DESC'
));

while ($loop->have_posts()) : $loop->the_post();
  the_content();
endwhile;
```

```php
add_post_thumbnail($name, $id, $post_types = array('page', 'post'))
```

```php
has_post_thumbnail_src($multi_post_thumbnail = '')
```

```php
the_post_thumbnail_src($size = 'full', $background_image = false, $multi_post_thumbnail = '')
```


~Current Version:0.1.2~
