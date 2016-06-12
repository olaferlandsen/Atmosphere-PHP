<?php
/**
 * Smarty Internal Plugin Resource File
 *
 * @package Smarty
 * @subpackage TemplateResources
 * @author Uwe Tews
 * @author Rodney Rehm
 */

/**
 * Smarty Internal Plugin Resource File
 *
 * Implements the file system as resource for Smarty templates
 *
 * @package Smarty
 * @subpackage TemplateResources
 */
class Smarty_Internal_Resource_File extends Smarty_Resource {

    /**
     * populate Source Object with meta data from Resource
     *
     * @param Smarty_Template_Source   $source    source object
     * @param Smarty_Internal_Template $_template template object
     */
    public function populate(Smarty_Template_Source $source, Smarty_Internal_Template $_template=null)
    {
        $source->filepath = $this->buildFilepath($source, $_template);

        if ($source->filepath !== false) {
            if (is_object($source->smarty->security_policy)) {
                $source->smarty->security_policy->isTrustedResourceDir($source->filepath);
            }

            $source->uid = sha1($source->filepath);
            if ($source->smarty->compile_check && !isset($source->timestamp)) {
                $source->timestamp = @filemtime($source->filepath);
                $source->exists = !!$source->timestamp;
            }
        }
    }

    /**
     * populate Source Object with timestamp and exists from Resource
     *
     * @param Smarty_Template_Source $source source object
     */
    public function populateTimestamp(Smarty_Template_Source $source)
    {
        $source->timestamp = @filemtime($source->filepath);
        $source->exists = !!$source->timestamp;
    }

    /**
     * Load template's source from file into current template object
     *
     * @param Smarty_Template_Source $source source object
     * @return string template source
     * @throws SmartyException if source cannot be loaded
     */
    public function getContent(Smarty_Template_Source $source)
    {
        if ($source->timestamp) {
            // original:
            // $source = file_get_contents($source->filepath);
            
            // nuevo
            $sourceFilepath = file_get_contents($source->filepath);
            /**
            *   Limpiamos llos tabuladores, espacios demas.. etc
            */
            $store = array();
            $_store = 0;
            $_offset = 0;
        
            // Unify Line-Breaks to \n
            $sourceFilepath = preg_replace("/\015\012|\015|\012/", "\n", $sourceFilepath);
        
            // capture Internet Explorer Conditional Comments
            if (preg_match_all('#<!--\[[^\]]+\]>.*?<!\[[^\]]+\]-->#is', $sourceFilepath, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $store[] = $match[0][0];
                    $_length = strlen($match[0][0]);
                    $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
                    $sourceFilepath = substr_replace($sourceFilepath, $replace, $match[0][1] - $_offset, $_length);
        
                    $_offset += $_length - strlen($replace);
                    $_store++;
                }
            }
        
            // Strip all HTML-Comments
            $sourceFilepath = preg_replace( '#<!--.*?-->#ms', '', $sourceFilepath );
        
            // capture html elements not to be messed with
            $_offset = 0;
            if (preg_match_all('#<(script|pre|textarea)[^>]*>.*?</\\1>#is', $sourceFilepath, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $store[] = $match[0][0];
                    $_length = strlen($match[0][0]);
                    $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
                    $sourceFilepath = substr_replace($sourceFilepath, $replace, $match[0][1] - $_offset, $_length);
        
                    $_offset += $_length - strlen($replace);
                    $_store++;
                }
            }
        
            $expressions = array(
                // replace multiple spaces between tags by a single space
                // can't remove them entirely, becaue that might break poorly implemented CSS display:inline-block elements
                '#(:SMARTY@!@|>)\s+(?=@!@SMARTY:|<)#s' => '\1\2',
                // remove spaces between attributes (but not in attribute values!)
                '#(([a-z0-9]\s*=\s*(["\'])[^\3]*?\3)|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \4',
                // note: for some very weird reason trim() seems to remove spaces inside attributes.
                // maybe a \0 byte or something is interfering?
                '#^\s+<#Ss' => '<',
                '#>\s+$#Ss' => '>',
            );
        
            $sourceFilepath = preg_replace( array_keys($expressions), array_values($expressions), $sourceFilepath );
            // note: for some very weird reason trim() seems to remove spaces inside attributes.
            // maybe a \0 byte or something is interfering?
            // $source = trim( $source );
        
            // capture html elements not to be messed with
            $_offset = 0;
            if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $sourceFilepath, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $store[] = $match[0][0];
                    $_length = strlen($match[0][0]);
                    $replace = array_shift($store);
                    $sourceFilepath = substr_replace($sourceFilepath, $replace, $match[0][1] + $_offset, $_length);
        
                    $_offset += strlen($replace) - $_length;
                    $_store++;
                }
            }
            
            
            #$sourceFilepath = preg_replace('/\t+/', '', $sourceFilepath);
            #$sourceFilepath = preg_replace('/( ){4}/', '', $sourceFilepath);
            #$sourceFilepath = preg_replace('/(\r+|\n+)/', '', $sourceFilepath);
            
            /**
            * Dejamos de limpiar y entregamos el recurso
            */
            
            return $sourceFilepath;
            // original:
            //return $source;
            
        }
        if ($source instanceof Smarty_Config_Source) {
            throw new SmartyException("Unable to read config {$source->type} '{$source->name}'");
        }
        throw new SmartyException("Unable to read template {$source->type} '{$source->name}'");
    }

    /**
     * Determine basename for compiled filename
     *
     * @param Smarty_Template_Source $source source object
     * @return string resource's basename
     */
    public function getBasename(Smarty_Template_Source $source)
    {
        $_file = $source->name;
        if (($_pos = strpos($_file, ']')) !== false) {
            $_file = substr($_file, $_pos + 1);
        }
        return basename($_file);
    }

}

?>