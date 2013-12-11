<?php
    /**
     * Add paths that you would like to add to the auto loading system here.
     * to add a path you would put it within the 'load_paths' array inside the
     * $autoload array...
     * i.e. $autoload['load_paths'] = array('application/widgets', 'application/path2');
     * The 'exclusions' section of the array is a special place to add classes that will
     * ignore the underscore_to_camel_case setting in the config array. In other words
     * if you add a class name to this section the autoloader will always switch underscores
     * to camel case...
     * i.e. $autoload['exclusions'] = array('TipsTricks'); the url would show tips_tricks
     * but with the TipsTricks set in the inclusions array regardless of the config setting
     * the auto loader will look for class TipsTricks, or TipsTricksController as it were.
     */


    $autoload = array();

//$autoload['load_paths'] = array('application/widgets');
$autoload['exclusions'] = array('TipsTricks');