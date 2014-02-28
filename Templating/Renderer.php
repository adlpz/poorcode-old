<?php

namespace Poorcode\Templating;

/**
 * Class Renderer
 * @package Poorcode\Templating
 *
 * This is a very basic but pretty funky renderer that allows substituting {{ variables }} in templates,
 * as well as looping through {% arrays %} and print {{ elements }} from them {% arrays %}.
 */
class Renderer {

    public function render($template, $arguments)
    {
        return $this->renderString(file_get_contents($template), $arguments);
    }

    private function renderString($template, $arguments, $parentArguments = [])
    {
        // Replace loops
        $loopRegex = '/\{\%\s*([a-zA-Z0-0\_\-]+)\s*\%\}(.*)\{\%\s*\1\s*\%\}/s';
        $self = $this;
        $template = preg_replace_callback($loopRegex, function($matches) use ($arguments, $self, $parentArguments) {
            $loopOn = $matches[1];
            $internalTemplate = $matches[2];
            $repeatedTemplate = "";
            $parentArguments[] = $arguments;
            $list = self::getValue($arguments, $loopOn, $parentArguments);
            foreach ($list as $index => $internalArguments) {
                if (!is_array($internalArguments) && !is_object($internalArguments)) {
                    $internalArguments = ['value' => $internalArguments];
                }
                $repeatedTemplate .= $self->renderString($internalTemplate, $internalArguments, $parentArguments);
            }
            return $repeatedTemplate;
        }, $template);

        // Replace variables
        $variableRegex = '/\{\{\s*([a-zA-Z0-0\_\-]+)\s*\}\}/';
        $template = preg_replace_callback($variableRegex, function($matches) use ($arguments, $parentArguments) {
            return $this->format(self::getValue($arguments, $matches[1], $parentArguments));
        }, $template);

        return $template;
    }

    private function format($input)
    {
        if ($input instanceof \DateTime) {
            return $input->format('Y/m/d H:i:s');
        }

        return $input;
    }

    private static function getValue($thing, $value, $parentArguments = null)
    {
        if (is_object($thing)) {
            // Try a getter
            if (method_exists($thing, 'get' . ucfirst($value))) {
                return call_user_func([$thing, 'get' . ucfirst($value)]);
            }
        }
        if (is_array($thing)) {
            // Try array access
            if (isset($thing[$value])) {
                return $thing[$value];
            }
        }

        if (!is_null($parentArguments)) {
            // Try the parents
            foreach ($parentArguments as $parentArgumentsElement) {
                try {
                    return self::getValue($parentArgumentsElement, $value);
                } catch (\Exception $e) {
                    // Pass
                }
            }
        }

        throw new \Exception("Could not find template variable '$value'");
    }

} 