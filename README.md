Dominant Color
=====

Save the dominant color of the image files you upload in Wordpress.


Usage
-----

```
$meta = get_post_meta( $post->ID, 'dominant_color', true );
$color = (empty($meta)) ? $color : $meta;
```
