<?php 
/**
 * FormitFastPack
 *
 * Copyright 2010-11 by Oleg Pryadko <oleg@websitezen.com>
 *
 * This file is part of FormitFastPack, a FormIt helper package for MODx Revolution.
 *
 * FormitFastPack is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * FormitFastPack is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * FormitFastPack; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 */
/**
 * @package FormitFastPack
 */
/*
 * Usage:
 * [[!fieldSetDefaults? &tpl=`myFieldsTemplate` &outer_tpl=`myOuterTpl` &chunks_path=`for/file/based/chunks/`]]
 * [[field? &type=`text` &name=`name` &req=`1` &label=`Full Name` &error_class=`notice`]]
 * [[field? &name=`email`]]
 * [[field? &type=`select` &name=`subject` &options=`General Inquiries==general||Quote Request==quote||Other==other`]]
 * [[field? &type=`checkbox` &array=`1` &name=`emails` &options=`Coupons==coupons||Newsletter==newsletter||Special Offers==spam`]]
 * [[field? &type=`textarea` &name=`message`]]
 *
 * *** Important note about caching and speed (for when half-seconds matter): ***
 *
 * I recommend that you call the field snippet cached, BUT ONLY if you don't need to have your options or options_html marked as checked or selected. 
 * - In other words, call only text, textarea, and other option-less field types cached.
 * - FieldSetDefaults and fields with types 'select','checkbox','radio' (and similar fields that need to set checked="checked" or selected="selected") should be called UNcached - [[!field]] - Smart caching will still kick in to cache as much as possible - see the &cache parameter.
 * - Leaving placeholders that do not have values slows MODx down. For the greatest speed gains, make sure you set empty defaults for all custom placeholders via fieldSetDefauls. Example: [[!fieldSetDefaults? &class=`` &label=`` &req=`` &anotherph=`` ...]]
 *
 *
 * Form Generators:
 *
 * I encourage the use of this component in making your own form generator snippet for you or your clients. Simply use $modx->runSnippet to execute the snippets based on a user imput, such as template variables.
 *
 *
 * General Parameters:
 *
 * debug - turn on debugging (default: false)
 * name - the name of the field (default: '')
 * type - the field type. Used to decide which subset of the tpl chunk to use. (default: 'text')
 * prefix - the prefix used by the FormIt call this field is for - may also work with EditProfile, Register, etc... snippet calls. (default: 'fi.')
 * delimiter_template - The template for the delimiter. (default: <!-- [[+type]] --> )
 * error_prefix - Override the calculated prefix for field errors. Example: 'error.' (default: '')
 * key_prefix - To use the same field names for different forms on the same page, specify a key prefix. (default: '')
 * outer_tpl - The outer template chunk, which can be used for any HTML that stays consistent between fields. This is a good place to put your <label> tags and any wrapping <li> or <div> elements that wrap each field in your form. (default: 'fieldWrapTpl')
 * outer_type - the outer wrapper type. Used to decide which subset of the outer_tpl chunk to use. (default: 'default')
 * tpl - The template chunk to use for templating all of the various fields. Each field is separated from the others by wrapping it - both above and below - with the following HTML comment: <!-- fieldtype -->, where fieldtype is the field type. For example, for a text field: <!-- text --> <input type="[[+type]]" name="[[+name]]" value="[[+current_value]]" /> <!-- text --> Use the fieldTypesTpl.chunk.tpl in the chunks directory as the starting point. (default: 'fieldTypesTpl')
 * chunks_path - Specify a path where file-based chunks are stored in the format lowercasechunkname.chunk.tpl, which will be used if the chunk is not found in the database.
 * inner_override - Specify your own HTML instead of using the field template. Useful if you want to use the outer_tpl and smart caching but specify your own HTML for the field. (default: '')
 * inner_element - Similar to inner_override, but accepts the name of an element (chunk, snippet...). All of the placeholders and parameters are passed to the element. Note: the inner_element override is not as useful as the options_element, which benefits much more from the smart caching. (default: '')
 * inner_element_class - Specify the classname of the element (such as modChunk, modSnippet, etc...). If using modChunk, you can specify an additional chunks_path parameter to allow file-based chunks. (default: 'modChunk')
 * inner_element_properties - A JSON array of properties to be passed to the element when it is processed. (default: '')
 * error_class - The name of the class to use for the [[+error_class]] placeholder. This placeholder is generated along with [[+error]] if a FormIt error is found for this field. (default: 'error')
 * to_placeholders - If set, will set all of the placeholders as global MODx placeholders as well. (default: false)
 * cache - Controls partial caching for when the snippet is called uncached. Make sure you leave this set to the default ('auto') if you are calling the snippet cached. Possible values: 0 (off - useful for troubleshooting), 1 (level 1 caching - actually calculates the dynamic values each time), 2 (level 2 caching - does not allow output filters), and 'auto' (default is 'auto'). 'auto' is the recommended cache level, as the other levels can never be used with cached snippet calls. It simply replaces the placeholders with the classic FormIt placeholders with the correct prefix. However, certain situations might benefit from level 1 or 2 caching, which use different processing methods.
 *
 *
 * Nested or Boolean Fields Parameters
 *
 * options - If your field is a nested or group type, such as checkbox, radio, or select, specify the options in tv-style format like so: Label One==value1||Label Two==value2||Label Three==value3 or Value1||Value2||Value3. The field snippet uses a sub-type (specified by option_type) to template the options. Setting this parameter causes smart caching to be enabled by default and "selected" or "checked" to be added to the currently selected option, as appropriate. See "mark_slected" and "cache" parameters. (default: '')
 * option_type - Specify the field type used for each option. If left blank, defaults to "bool" if &type is checkbox or radio and "option" if &type is select). (default: '')
 * options_override - same as inner_html, but for the options_html placeholder. Allows you to use your own custom elements while benefiting from the speed gains of the smart caching.  (default: '')
 * options_element - same as inner_element, but for the options_html placeholder (default: '')
 * options_element_class - same as inner_element_type, but for the options_html placeholder (default: 'modChunk')
 * options_element_properties - same as inner_element_properties, but for the options_html placeholder (default: '')
 * mark_selected - If left blank or set to zero, disables option marking. By default if "options" or an options override is specified, the field snippet will add a marker such as ' checked="checked"' or (if the field type is "select") ' selected="selected"' in the right place, assuming you are using HTML syntax for value (value="X"). This is a lot faster than using FormItIsSelected or FormItIsChecked.   (default: true)
 * selected_text - The text to mark selected options with (such as checked="checked" or selected="selected"). If left blank or set to false, defaults to checked="checked" unless the field type is "select", in which case it uses selected="selected". (default: '')
 *
 *
 * Custom Parameters
 *
 * You can add an infinite number of custom parameters, all of which will be set as placeholders in the template chunks.
 * Example parameters: class, req (required), note, help
 * Example parameter usage: 
 *  - class="[[+type]] [[+class]][[+error_class]][[+req:notempty=` required`]]"
 *  - label="[[+label:default=`[[+name:replace=`_== `:ucwords]]`]][[+req:notempty=` *`]]"
 *  - [[+note:notempty=`<span class="notice">[[+note]]</span>`]]
 *
 *
 * Placeholders:
 * 
 * The values of all the parameters listed above and any other parameters you pass to the snippet are automatically set as placeholders. 
 * This allows you to add custom placeholders such as "required", "class", etc.... (see custom parameters above)
 * In addition, the following special placeholders are generated:
 *
 * inner_html - Used in the outer_tpl to position the generated content, which will vary by field type. Simple example: <li>[[+inner_html]]</li>
 * options_html - Used in the tpl to position the options html (only when using &options or an options override). Example: <select name="[[+name]]">[[+options_html]]</select>
 * current_value - The value of the FormIt value for the field name. Exactly the same as writing [[!fi.fieldname]] for each fieldname (if the prefix is fi.). Never gets cached.
 * error - The value of the FormIt error message for the field name, if one is found. Exactly the same as writing [[!fi.error.fieldname]] for each fieldname (if the prefix is fi.). Never gets cached.
 * error_class - set to the value of the error_class parameter (default is " error") ONLY if a FormIt error for the field name is found. Exactly the same as using [[+error:notempty=` error`]].
 * error_class_name - same as error_class, but set every single time (not just when there's an error)
 * current_value_class - set to the value of the current_value_class parameter (default is " current_value") ONLY if a FormIt value for the field name is found. Exactly the same as using [[+current_value:notempty=` current_value`]].
 * current_value_class_name - same as current_value_class, but set every single time (not just when there's a value)
 * key - A unique but human-friendly identifier for each field or sub-field (useful for HTML id attributes). Generated from the key_prefix, prefix, field name, and (only if using an option field) value.
 *
 */
$debug = $modx->getOption('debug',$scriptProperties,false);
$ffp = $modx->getService('formitfastpack','FormitFastPack',$modx->getOption('ffp.core_path',null,$modx->getOption('core_path').'components/formitfastpack/').'model/formitfastpack/',$scriptProperties);
if (!($ffp instanceof FormitFastPack)) return 'Package not found.';

// load defaults
$global_defaults = $ffp->getConfig();
$config = array_merge($global_defaults,$scriptProperties);

// Important properties
$name = $modx->getOption('name',$config,'');
$type = $modx->getOption('type',$config,'text');
$outer_type = $modx->getOption('outer_type',$config,'default');
$prefix = $modx->getOption('prefix',$config,'fi.');
$error_prefix = $modx->getOption('error_prefix',$config,$prefix.'error.');
$options_html_prefix = $modx->getOption('options_html_prefix',$config,$prefix.'field_options_html.');     // used internally - only change if having problems
$error_class = $modx->getOption('error_class',$config,' error');
$current_value_class = $modx->getOption('current_value_class',$config,' current_value');
$key_prefix = $modx->getOption('key_prefix',$config,'');

// delimiter each field type is bordered by. 
// example: <!-- textarea --> <input type="textarea" name="[[+name]]">[[+current_value]]</input> <!-- textarea -->
$delimiter_template = $modx->getOption('delimiter_template',$config,'<!-- [[+type]] -->');
$delimiter = str_replace('[[+type]]',$type,$delimiter_template);
$outer_delimiter = str_replace('[[+type]]',$outer_type,$delimiter_template);

// The outer template 
$outer_tpl = $modx->getOption('outer_tpl',$config,'fieldWrapTpl');
// The main template (contains all field types separated by the delimiter)
$tpl = $modx->getOption('tpl',$config,'fieldTypesTpl');

// For checkboxes, radios, selects, etc... that require inner fields, parse options
$options = $modx->getOption('options',$config,'');

// Set defaults for the options of certain field types and allow to override from a system settings JSON array
$inner_static = $modx->fromJSON($modx->getOption('ffp.inner_options_static',null,'[]'));
if (empty($inner_static)) {
    $inner_static = array();
    $inner_static['bool'] = array('option_tpl' => 'bool','selected_text' => ' checked="checked"');
    $inner_static['checkbox'] = array('option_tpl' => 'bool','selected_text' => ' checked="checked"');
    $inner_static['radio'] = array('option_tpl' => 'bool','selected_text' => ' checked="checked"');
    $inner_static['select'] = array('option_tpl' => 'option','selected_text' => ' selected="selected"');
}
$inner_static['default'] = isset($inner_static['default']) ? $inner_static['default'] : array('option_tpl' => '','selected_text' => ' checked="checked" selected="selected"');
$default_option_tpl = isset($inner_static[$type]['option_tpl']) ? $inner_static[$type]['option_tpl'] : $inner_static['default']['option_tpl'];
$default_selected_text = isset($inner_static[$type]['selected_text']) ? $inner_static[$type]['selected_text'] : $inner_static['default']['selected_text'];
// Allow overriding the default settings for types from the script properties
$selected_text = $modx->getOption('selected_text',$config, '');
$selected_text = !empty($selected_text) ? $selected_text : $default_selected_text;

/*      CACHING         */
// See if caching is set system-wide or in the scriptProperties
$cache = $modx->getOption('cache',$config,$modx->getOption('ffp.field_default_cache',null,'auto'));
// By default, only cache elements that have options.

if ($cache == 'auto') {
    $cache = 3;
    // default cache to 3 for now. This seems to be the best in all auto-detectable situations, but the code below is left in just in case options benefit from a differet type of cache.
    // $cache = array_key_exists($type,$inner_static) || $modx->getOption('options',$config,false)  || $modx->getOption('options_element',$config,false) || $modx->getOption('inner_element',$config,false) ? 1 : 3; // set to 1 if has options or 3 otherwise
} else {
    $cache = !is_numeric($cache) && !empty($cache) ? 1 : $cache;
    $cache = (int) $cache;
}
$already_cached = false;
if ($cache) {
    if (empty($cacheKey)) $cacheKey = $modx->getOption('cache_resource_key', null, 'resource');
    if (empty($cacheHandler)) $cacheHandler = $modx->getOption('cache_resource_handler', null, $modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache'));
    if (!isset($cacheExpires)) $cacheExpires = (integer) $modx->getOption('cache_resource_expires', null, $modx->getOption(xPDO::OPT_CACHE_EXPIRES, null, 0));
    if (empty($cacheElementKey)) $cacheElementKey = $modx->resource->getCacheKey() . '/' . md5($modx->toJSON($config) . implode('', $modx->request->getParameters()));
    $cacheOptions = array(
        xPDO::OPT_CACHE_KEY => $cacheKey,
        xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        xPDO::OPT_CACHE_EXPIRES => $cacheExpires,
    );
    $cached = $modx->cacheManager->get($cacheElementKey, $cacheOptions);
    // Get the cached values and set them as necessary
    if (isset($cached['options_html']) && isset($cached['placeholders']) && isset($cached['inner_html'])) {
        $options_html = $cached['options_html'];
        $placeholders = $cached['placeholders'];
        $inner_html = $cached['inner_html'];
        $cached_output = $cached['cached_output'];
        $already_cached = true;
    }
}

// The following variables do not need to be set if cached content is found
if (!$cache || !$already_cached) {
    // Set placeholders
    $placeholders = $config;
    $placeholders['key'] = preg_replace("/[^a-zA-Z0-9\s]/", "", $key_prefix.$name);
    $placeholders['error_class_name'] = $error_class;
    $placeholders['current_value_class_name'] = $current_value_class;

    // Set overrides for options and inner_html
    $inner_html = isset($inner_html) ? $inner_html : $modx->getOption('inner_override',$config,'');

    // Process element overrides
    $possible_overrides = array('options','inner');
    foreach($possible_overrides as $level) {
        $level_html = $level.'_html';
        $level_element = $level.'_element';
        $level_element_class = $level.'_element_class';
        $level_element_properties = $level.'_element_properties';
        ${$level_html} = isset(${$level_html}) ? ${$level_html} : $modx->getOption($level_html,$config,'');
        ${$level_element} = $modx->getOption($level_element,$config,'');
        ${$level_element_class} = $modx->getOption($level_element_class,$config,'modChunk');
        ${$level_element_properties} = $modx->fromJSON($modx->getOption($level_element_properties,$config,'[]'));
        $properties = array_merge($placeholders,${$level_element_properties});
        if (${$level_element} && ${$level_element_class}) {
            if (${$level_element_class} === 'modChunk') {
                // Shortcut - use the cachable chunk method of FFP. Allows file-based chunks.
                ${$level_html} = $ffp->getChunk(${$level_element}, $properties, 'none');
            } else {
                // Full route for snippets & others
                $elementObj = $modx->getObject(${$level_element_class}, array('name' => ${$level_element}));
                if ($elementObj) {
                    ${$level_html} = $elementObj->process($properties);
                    $placeholders[$level.'_html'] = ${$level_html};
                }
            }
        }
    }
}
// Parse options for checkboxes, radios, etc... if &options is passed
// Note: if cached or overriden options_html has been found, this part will be skipped
if ($options && empty($options_html)) {
    $option_tpl = $modx->getOption('option_type',$config, '');
    $option_tpl = $option_tpl ? $option_tpl : $default_option_tpl;
    $options_delimiter = '||';
    $inner_delimiter = str_replace('[[+type]]',$option_tpl,$delimiter_template);
    $options_inner_delimiter = '==';
    $options_html = '';
    $options = explode($options_delimiter,$options);

    foreach ($options as $option) {
        $option_array =  explode($options_inner_delimiter,$option);
        foreach ($option_array as $key => $value) {
            $option_array[$key] = trim($value);
        }
        $inner_array = $placeholders;
        $inner_array['label'] = $option_array[0];
        $inner_array['value'] = isset($option_array[1]) ? $option_array[1] : $option_array[0];
        $inner_array['key'] = $placeholders['key'].'-'.preg_replace("/[^a-zA-Z0-9-\s]/", "", $inner_array['value']);
        $options_html .= $ffp->getChunk($tpl,$inner_array,$inner_delimiter);
    }
}

// cache everything up to this point if cache is enabled and not set to level 2
$cached = array('options_html' => $options_html,'inner_html' => $inner_html,'placeholders' => $placeholders, 'cached_output' => null);

// Set the error, current value, and options_html placeholders.
// these must be initialized to avoid errors even though they are only used by cache level 3.
$search = $replace = $dynamic_placeholders = $keys = array();
switch ($cache) {
    case 3 :
        // Add selected markers to options - much faster than FormItIsSelected and FormItIsChecked for large forms
        // Note: these must process every single time, so having options prevents you from calling the snippet cached
        if (!empty($options_html) && $selected_text && $modx->getOption('mark_selected',$config,true)) {
            $current_value = $modx->getPlaceholder($prefix.$name);
            $options_html = $ffp->markSelected($options_html,$current_value,$selected_text);
            // return "<strong>Warning: (field with name $name and type $type). Cache level is too high for marking checked or selected options. Please change the cache parameter to 2, 1, or 0 and call the snippet uncached or disable the mark_selected parameter.</strong>";
            $keys['options_html'] = $options_html_prefix.$name;
            $modx->setPlaceholder($keys['options_html'],$options_html);
        } else {
            // to avoid unset placholders - speed things up a bit
            $placeholders['options_html'] = $options_html;
        }
        // replace the dynamic placeholders with global MODx placeholders to allow snippet to be cached
        // the class placeholders must always be above the value placeholders
        $keys['error_class'] = $error_prefix.$name.':notempty=`'.$error_class.'`';
        $keys['error'] = $error_prefix.$name;
        $keys['current_value_class'] = $prefix.$name.':notempty=`'.$current_value_class.'`';
        $keys['current_value'] = $prefix.$name;
        foreach ($keys as $oldkey => $newkey) {
            $end_array= array(']]',':','?');
            foreach ($end_array as $end) {
                $search[] = '[[+'.$oldkey.$end;
                $search[] = '[[!+'.$oldkey.$end;
                $replace[] = $replace[] = '[[!+'.$newkey.$end;
            }
        }
        

        break;
    case 2:
        // obsolete? method of caching. Left in just in case.
        // Add selected markers to options - much faster than FormItIsSelected and FormItIsChecked for large forms
        if (!empty($options_html) && $selected_text && $modx->getOption('mark_selected',$config,true)) {
            $current_value = $modx->getPlaceholder($prefix.$name);
            $options_html = $ffp->markSelected($options_html,$current_value,$selected_text);
        }
        $dynamic_placeholders['error'] = '[[!+'.$error_prefix.$name.']]';
        $dynamic_placeholders['current_value'] = '[[!+'.$prefix.$name.']]';
        $dynamic_placeholders['options_html'] = $options_html;
        // $dynamic_placeholders['error_class'] =  '[[+'.$error_prefix.':notempty=`'.$error_class.'`]]';
        // $dynamic_placeholders['current_value_class'] =  empty($current_value) ? '' : $modx->getOption('current_value_class',$config,'current_value');
        break;
    case 1 :
    default :
        // The original way
        // Grab the error and current value from FormIt placeholders
        $error = $modx->getPlaceholder($error_prefix.$name);
        $current_value = $modx->getPlaceholder($prefix.$name);
        // Add selected markers to options - much faster than FormItIsSelected and FormItIsChecked for large forms
        if ($options_html && $selected_text && $modx->getOption('mark_selected',$config,true)) {
            $options_html = $ffp->markSelected($options_html,$current_value,$selected_text);
        }
        $dynamic_placeholders['error'] = empty($error) ? '' : $error;
        $dynamic_placeholders['error_class'] =  empty($error) ? '' : $error_class;
        $dynamic_placeholders['current_value'] = empty($current_value) ? '' : $current_value; // ToDo: add better caching and take this out to a str_replace function.
        $dynamic_placeholders['current_value_class'] =  empty($current_value) ? '' : $modx->getOption('current_value_class',$config,'current_value');
        $dynamic_placeholders['options_html'] = empty($options_html) ? '' : $options_html;
        break;
}

if ($cache == 2 && !$already_cached) {
    $temporary_placeholders = $ffp->createTemporaryPlaceholders($dynamic_placeholders,$ph_temporary_key_template);
    $placeholders = array_merge($placeholders,$temporary_placeholders);
}

$ph_temporary_key_template = $modx->getOption('ph_temporary_key_template',$config,'[+[[+key]]+]');
// only used for cache level 2
if ($cache == 1 || $cache == 0 || !$already_cached) {
    // set the dynamic placholders
    if ($cache == 1 || $cache == 0) {
        $placeholders = array_merge($placeholders,$dynamic_placeholders);
    }
    // Process inner_html
    if (empty($inner_html)) {
        $inner_html = $ffp->getChunk($tpl,$placeholders,$delimiter,$search,$replace);
    }
    $placeholders['inner_html'] = $inner_html;
    
    if ($modx->getOption('to_placeholders',$config,false)) {
        // Set all placeholders globally, not limited just to the template chunks
        $modx->toPlaceholders($placeholders,$key_prefix);
    }

    // If outer template is set, process it. Otherwise just use the $inner_html
    if ($outer_tpl) {
        $output = $ffp->getChunk($outer_tpl,$placeholders,$outer_delimiter,$search,$replace);
    } else {
        $output = $inner_html;
    }
    if ($cache > 1) {
        // prepare the output for processing
        $cached_output = $output;
        // also save it for later
        $cached['cached_output'] = $output;
    }
} else {
    $output = $cached_output;
}

if ($cache == 2) {
    $output = $ffp->processTemporaryPlaceholders($cached_output,$dynamic_placeholders,$ph_temporary_key_template);
}
// Put the cache array into the cache.
if ($cache && !$already_cached && $modx->getCacheManager()) {
    $modx->cacheManager->set($cacheElementKey, $cached, $cacheExpires, $cacheOptions);
}
return $output;