<?php

/**
 * SimpleView.
 *
 * @link      https://github.com/lroot/SimpleView
 *
 * @copyright Copyright (c) 2015 Larry Root
 * @license   https://github.com/lroot/SimpleView/blob/master/LICENSE.md (MIT License)
 */
namespace Lroot\Views;

use Exception;

/**
 * SimpleView.
 *
 * A single class implementing the core features of a view system including
 * layouts, partials, placeholders and caching.
 */
class SimpleView
{
    /**
     * Path to view directory.
     *
     * @var string
     */
    const CONFIG_VIEW_DIR = 'view_directory';

    /**
     * Path to view directory.
     *
     * @var string
     */
    const CONFIG_LAYOUT_DIR = 'layout_directory';

    /**
     * Path to partials directory.
     *
     * @var string
     */
    const CONFIG_PARTS_DIR = 'partials_directory';

    /**
     * Default view configuration.
     *
     * @var array
     */
    private static $config = array(
        self::CONFIG_VIEW_DIR => './views/',
        self::CONFIG_LAYOUT_DIR => './layouts/',
        self::CONFIG_PARTS_DIR => './parts/',
    );

    /**
     * Sets configuration based on the array of config properties.
     *
     * @param array $properties
     */
    public static function setConfigProperties(array $properties)
    {
        self::$config = array_merge(self::$config, $properties);
    }

    /**
     * Sets a view configuration value.
     *
     * @param string $name  Name of configuration property to set
     * @param mixed  $value Value to set
     */
    public static function setConfigProperty($name, $value)
    {
        self::$config[$name] = $value;
    }

    /**
     * Returns the current configuration.
     *
     * @return array
     */
    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * Renders the provided data with the given template and returns the
     * resulting content.
     *
     * @param string $script Name of the template file to use. Expected file
     *                       name and location is "{CONFIG_VIEW_DIR}{$script}.php"
     * @param array  $data   Associative array of data that will be injected into
     *                       the template environment as variables Ex:
     *                       array('foo'=>1) will become $view_foo within the
     *                       template script
     * @param string $layout Name of the layout to use. If NULL is passed, then
     *                       just the result of the template file will be
     *                       returned. Expected file name and location is
     *                       "{CONFIG_LAYOUT_DIR}{$layout}.php"
     *
     * @return string Rendered content
     */
    public static function render($script, array $data = array(), $layout = 'default')
    {
        extract($data, EXTR_PREFIX_ALL, 'view');

        // Render template and capture code
        ob_start();
        require_once self::$config[self::CONFIG_VIEW_DIR].$script.'.php';
        self::placeholderSetContent(self::PLACEHOLDER_TMPL_CONTENT, ob_get_clean());

        // Render template code within layout and return result
        if (is_string($layout) && !empty($layout)) {
            ob_start();
            require_once self::$config[self::CONFIG_LAYOUT_DIR].$layout.'.php';
            $result = ob_get_clean();
            // If no layout specified, user rendered template code
        } else {
            $result = self::placeholderGetContent(self::PLACEHOLDER_TMPL_CONTENT);
        }

        return $result;
    }

    /**
     * Renders the provided data with the given partial and returns the
     * resulting content.
     *
     * @param string $script Name of the partial file to use. Expected file
     *                       name and location is
     *                       '{CONFIG_PARTS_DIR}{$script}'
     *                       You can reference sub directiors within the
     *                       '/parts/' folder
     * @param array  $data   Associative array of data that will be injected into
     *                       the partials environment as variables Ex:
     *                       array('foo'=>1) will become $parts_foo within the
     *                       partial script
     *
     * @return string Rendered content
     */
    public static function partial($script, array $data = array())
    {
        extract($data, EXTR_PREFIX_ALL, 'parts');

        // Render partial
        ob_start();
        require self::$config[self::CONFIG_PARTS_DIR].$script;

        return ob_get_clean();
    }

    /**
     * Inline scripts that will be included in the bottom of the
     * page. YOU MAY WRAP JAVASCRIPT IN SCRIPT TAGS (with no attributes)!
     *
     * example:
     * <code>
     *   <script>
     *     console.log(...);
     *   </script>
     * </code>
     *
     * @var string
     */
    const PLACEHOLDER_INLINE_SCRIPTS = 'inline-scripts';

    /**
     * Content that will be placed in the HEAD of the page.
     *
     * @var string
     */
    const PLACEHOLDER_HEAD_CONTENT = 'head-content';

    /**
     * Content that will be placed at the bottom of the page, after all html and before JavaScript.
     *
     * @var string
     */
    const PLACEHOLDER_FOOTER_CONTENT = 'footer-content';

    /**
     * The main content of a rendered template that will be inserted into a layout.
     *
     * @var string
     */
    const PLACEHOLDER_TMPL_CONTENT = 'tmpl-content';

    /**
     * The ID value to apply to the body tag.
     *
     * @var string
     */
    const PLACEHOLDER_PAGE_ID = 'page-id';

    /**
     * The class value to apply to the body tag.
     *
     * @var string
     */
    const PLACEHOLDER_PAGE_CLASS = 'page-class';

    /**
     * Title tag for the page.
     *
     * @var string
     */
    const PLACEHOLDER_META_TITLE = 'meta-title';

    /**
     * Meta description for page.
     *
     * @var string
     */
    const PLACEHOLDER_META_DESCRIPTION = 'meta-description';

    /**
     * Meta keywords for the page.
     *
     * @var string
     */
    const PLACEHOLDER_META_KEYWORDS = 'meta-keywords';

    /**
     * Meta image for the page. Used by 3rd party social and sharing sites.
     *
     * @var string
     */
    const PLACEHOLDER_META_IMAGE = 'meta-image';

    /**
     * Array of content by placeholder name.
     *
     * @var array
     */
    private static $placeholderContent = array();

    /**
     * Name of the placeholder that is currently being captured.
     *
     * @var string
     */
    private static $activeCaptureName = null;

    /**
     * Starts capturing data for the given placeholder name. Captures are
     * additive. To replace captured data you must first clear it.
     *
     * @param string $name The name of the placeholder to store the captured
     *                     data under. Use the AG_View::PLACEHOLDER_* constants
     *
     * @throws Exception
     */
    public static function placeholderCaptureStart($name)
    {
        if (!is_string($name) || strlen($name) == 0) {
            throw new Exception('You must provide a valid placeholder name');
        } elseif (!is_null(self::$activeCaptureName)) {
            throw new Exception("You must end the current capture before starting a new one ('"
                                .self::$activeCaptureName."' was still active when you tried to start '".$name."')");
        }
        self::$activeCaptureName = $name;
        ob_start();
    }

    /**
     * Stops the currently active capture and stores the resulting data.
     *
     * @throws Exception
     */
    public static function placeholderCaptureEnd()
    {
        if (is_null(self::$activeCaptureName)) {
            throw new Exception('Tried to end a placeholder capture that was never started');
        }

        // If this is the first time we have seen this placehoder, initialize it
        if (!array_key_exists(self::$activeCaptureName, self::$placeholderContent)) {
            self::$placeholderContent[self::$activeCaptureName] = '';
        }

        self::$placeholderContent[self::$activeCaptureName] .= (string) ob_get_clean();
        self::$activeCaptureName = null;
    }

    /**
     * Returns the content of the given placeholder name.
     *
     * @param string $name The name of the placeholder to store the captured
     *                     data under. Use the AG_View::PLACEHOLDER_* constants
     *
     * @return string
     *
     * @throws Exception
     */
    public static function placeholderGetContent($name)
    {
        if (!is_string($name) || strlen($name) == 0) {
            throw new Exception('You must provide a valid placeholder name');
        }

        return array_key_exists($name, self::$placeholderContent)
            ? self::$placeholderContent[$name]
            : null;
    }

    /**
     * Rather than capturing content for a placeholder, here you can pass the
     * content in as a string.
     *
     * @param string $name    The name of the placeholder to store the captured
     *                        data under. Use the AG_View::PLACEHOLDER_* constants
     * @param string $content the content to store
     *
     * @throws Exception
     */
    public static function placeholderSetContent($name, $content)
    {
        if (!is_string($name) || strlen($name) == 0) {
            throw new Exception('You must provide a valid placeholder name');
        } elseif (!is_string($name)) {
            throw new Exception('You must provide valid string content');
        }
        self::$placeholderContent[$name] .= $content;
    }

    /**
     * Removes the content for the given placeholder name.
     *
     * @param string $name The name of the placeholder to store the captured
     *                     data under. Use the AG_View::PLACEHOLDER_* constants
     *
     * @throws Exception
     */
    public static function placeholderDeleteContent($name)
    {
        if (!is_string($name) || strlen($name) == 0) {
            throw new Exception('You must provide a valid placeholder name');
        }
        unset(self::$placeholderContent[$name]);
    }

    /**
     * Returns a URL based on the provided data. Builds from the current URL and
     * overrides parts based on data provided.
     *
     * @param array  $query optional Associative array of name => value pairs
     * @param string $path  optional The URL path
     * @param string $host  optional The hostname to use
     *
     * @return string
     */
    public static function link(array $query = array(), $path = null, $host = null)
    {

        // Breakdown existing URL
        $urlParts = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));

        // Parse current query string and merge in changes;
        $queryParts = array();
        parse_str($urlParts['query'], $queryParts);
        $queryParts = array_merge($queryParts, $query);

        // Update URL parts with any new data
        $urlParts['path'] = $path ? $path : $urlParts['path'];
        $urlParts['query'] = http_build_query($queryParts);
        $urlParts['host'] = $host ? $host : null;

        // Return newly constructed URL
        return $urlParts['host'].
               $urlParts['path'].
               ($urlParts['query'] ? '?'.$urlParts['query'] : '');
    }
}
