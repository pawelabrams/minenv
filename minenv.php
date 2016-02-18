<?php
/**
 * Minenv by Paweł Abramowicz
 * Based on PHP Dotenv by Vance Lucas
 * to use, just include and loadenv(__DIR__);
 */

function loadenv($path, $file = '.env', $opts = array()) {
    # get the path
    if (!is_string($file))
        $file = '.env';
    $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;

    # if file's readable
    if (is_file($path) && is_readable($path)) {

        # set mutability
        $immutable = isset($opts['immutable']) ? $opts['immutable'] : in_array('immutable', (array)$opts);

        # get lines with line endings autodetection
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        # process lines; set values accordingly
        foreach ($lines as $line) {
            # if it looks like a setter
            if (strpos(trim($line), '#') !== 0 && strpos($line, '=') !== false) {
                if (strpos($line, '=') !== false)
                    list($name, $value) = explode('=', $line, 2);
                else
                    $name = $line;

                # sanitize name
                $name = trim(str_replace(array('export ', '\'', '"'), '', $name));

                # sanitize value
                $value = trim($value);
                if ($value && strpbrk($value[0], '"\'') !== false) { # value starts with a quote
                    $quote = $value[0];
                    # regex copied verbatim from vlucas/phpdotenv
                    $regexPattern = sprintf(
                        '/^
                        %1$s          # match a quote at the start of the value
                        (             # capturing sub-pattern used
                         (?:          # we do not need to capture this
                          [^%1$s\\\\] # any character other than a quote or backslash
                          |\\\\\\\\   # or two backslashes together
                          |\\\\%1$s   # or an escaped quote e.g \"
                         )*           # as many characters that match the previous rules
                        )             # end of the capturing sub-pattern
                        %1$s          # and the closing quote
                        .*$           # and discard any string after the closing quote
                        /mx',
                        $quote
                    );
                    $value = preg_replace($regexPattern, '$1', $value);
                    $value = str_replace("\\$quote", $quote, $value);
                    $value = str_replace('\\\\', '\\', $value);
                } elseif ($value) {
                    $parts = explode(' #', $value, 2); #TODO: tab before # breaks this, think of a workaround!
                    $value = rtrim($parts[0]);

                    # Unquoted values cannot contain whitespace
                    if (preg_match('/\s+/', $value) > 0) {
                        throw new Exception('The values containing spaces must be surrounded by quotes.');
                    }
                }

                #TODO: resolve nested vars

                # Don't overwrite existing environment variables if we're immutable
                if ($immutable && (array_key_exists($name, $_ENV) || array_key_exists($name, $_SERVER) || getenv($name))) {
                    continue;
                }

                # set variables
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}