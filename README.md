# Dominant Color

WordPress plugin to save the dominant color of the image file you upload.

Also adds a metabox with a colorpicker in your Media library.


## Installation

1. Upload the plugin in your `wp-content/plugins` directory
2. Activate it on the plugin administration page


## Usage

Call the post meta (an hexadecimal code) in your template:

### By attachment ID

``` php
show_dominant($attachmentID)
```
### By post ID

``` php
show_dominant_by_post_id($postID)
```
