# SimpleView

A single class implementing the core features of a view system including layouts, partials, placeholders and caching.

## Configuration

You can configure SimpleView to work with your directory structure:
```php
// Setup Configuration
SimpleView::setConfigProperties(array(
    SimpleView::CONFIG_VIEW_DIR   => YOUR_APP_PATH.'/app/views/',
    SimpleView::CONFIG_LAYOUT_DIR => YOUR_APP_PATH.'/app/views/_layouts/',
    SimpleView::CONFIG_PARTS_DIR  => YOUR_APP_PATH.'/app/views/_partials/'
));
```

## Rendering

Rendering a view is simple:
```php
// Output your view script. Parameters: view script, data & layout
echo SimpleView::render('your-view-script', array('name'=>'Amanda'), 'your-layout');
```

You specify your view script, pass an associative array of data and indicate which layout you would like to wrap the view script in. You can access this passed in data as standard variables within the view script. For example `array('name'=>'amanda')` will become `$view_name`.

## Views

View scripts are the primary template file. They contain the core layout for the view you are rendering. Within the layout you can include Partials content and define placeholder content for use within the enclosing layout.

```HTML+PHP
<?php SimpleView::placeholderCaptureStart(SimpleView::PLACEHOLDER_HEAD_CONTENT); ?>
<style>
    /* CSS content... */
    /* consider outputting this placeholder content in the head of your layout */
</style>
<?php SimpleView::placeholderCaptureEnd(); ?>

<?php echo SimpleView::partial('title.php',array('name'=>$view_name)); ?>
<p>Example conetent lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>

<?php echo SimpleView::placeholderCaptureStart(SimpleView::PLACEHOLDER_INLINE_SCRIPTS);?>
<script>
    // ... mind blowing js code ...
    // consider outputting this placeholder content at the bottom of your layout
</script>
<?php SimpleView::placeholderCaptureEnd(); ?>
```

## Placeholders

Placeholders allow you to associate content with a name that you can reference later.

```php
SimpleView::placeholderSetContent('placeholder_name','placeholder content');
```

You can also "capture" placeholder content.
```php
<?php SimpleView::placeholderCaptureStart('placeholder_name'); ?>
All content will be captured until we call placeholderCaptureEnd()
<?php SimpleView::placeholderCaptureEnd(); ?>
```

Getting placeholder content is simple.
```php
echo SimpleView::placeholderGetContent('placeholder_name');
```

Naming things is hard so there is a set of common placeholder names if you wish.
```HTML+PHP
<?php SimpleView::placeholderCaptureStart(SimpleView::PLACEHOLDER_INLINE_SCRIPTS);?>
<script>
    // ... mind blowing js code ...
    // consider outputting this at the bottom of your layout
</script>
<?php SimpleView::placeholderCaptureEnd(); ?>
```

## Partials

Partials are like small, self contained view scripts. You can pass an associative array of data to partial templates too.
```php
// Execute a partial script and echo its content
echo SimpleView::partial('title.php',array('name'=>'Amanda'));
```

Partial templates are simple php files. The array of data will be available as variables within the partial template.
```HTML+PHP
<!--Super simple partial template-->
<h1>Hello <?=$parts_name?></h1>
```

## Layouts

Layouts are your "master" templates pulling together all your content.
```HTML+PHP
<!DOCTYPE html>
<html>
<head>
    <?php
        // This content could be set from anywhere including the view script or a
        // partial included by the view script
        echo SimpleView::placeholderGetContent(SimpleView::PLACEHOLDER_HEAD_CONTENT);
    ?>
</head>
<body>
    <?php
        // This placeholder name is special. It will be populated with the contents
        // of your rendered layout.
        echo SimpleView::placeholderGetContent(SimpleView::PLACEHOLDER_TMPL_CONTENT);
    ?>
    <?php
        // This content could be set from anywhere as well
        echo SimpleView::placeholderGetContent(SimpleView::PLACEHOLDER_FOOTER_CONTENT);
    ?>
</body>
</html>
```

## Further Documentation

SimpleView has extensive code level documentation and examples. To learn more about how SimpleView works just read through the code.


