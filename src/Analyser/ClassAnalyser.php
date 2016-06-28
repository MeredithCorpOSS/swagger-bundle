<?php

namespace TimeInc\SwaggerBundle\Analyser;

use Symfony\Component\Finder\Finder;

/**
 * Class ClassAnalyser.
 *
 * @author Andy Thorne <andy.thorne@timeinc.com>
 */
class ClassAnalyser
{
    /**
     * Given a file name, search and extract all class names in the file.
     *
     * @param string $file    File to search in
     * @param array  $classes Array to append classes to
     *
     * @return array Array of all class names
     */
    public function analyse($file, array &$classes = [])
    {
        $tokens = token_get_all(file_get_contents($file));

        $namespace = '';
        $buildingNs = false;
        $buildingClass = false;
        foreach ($tokens as $token) {
            if ($token[0] == T_NAMESPACE) {
                $buildingNs = true;
                $buildingClass = false;
                $namespace = '';
            }

            if ($buildingNs) {
                if (is_numeric($token[0]) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $namespace .= $token[1];
                }

                if (!is_numeric($token[0]) && in_array($token, [';', '{'])) {
                    $buildingNs = false;
                }
            } elseif ($buildingClass) {
                if ($token[0] === T_STRING) {
                    $classes[] = $namespace.'\\'.$token[1];
                    $buildingClass = false;
                    $buildingNs = false;
                }
            } else {
                if ($token[0] === T_CLASS) {
                    $buildingClass = true;
                    $buildingNs = false;
                }
            }
        }

        return $classes;
    }

    /**
     * Find all class names by a finder instance.
     *
     * @param Finder $finder
     *
     * @return array
     */
    public function analysePaths(Finder $finder)
    {
        $classes = [];
        foreach ($finder as $file) {
            $this->analyse($file, $classes);
        }

        return $classes;
    }
}
