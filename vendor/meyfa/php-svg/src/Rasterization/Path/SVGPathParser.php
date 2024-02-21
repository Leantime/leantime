<?php

namespace SVG\Rasterization\Path;

/**
 * This class can convert SVG path description strings into arrays of distinct
 * commands + their arguments.
 */
class SVGPathParser
{
    /**
     * @var int[] $commandLengths A map of command ids to their argument counts.
     */
    private static $commandLengths = array(
        'M' => 2,   'm' => 2,   // MoveTo
        'L' => 2,   'l' => 2,   // LineTo
        'H' => 1,   'h' => 1,   // LineToHorizontal
        'V' => 1,   'v' => 1,   // LineToVertical
        'C' => 6,   'c' => 6,   // CurveToCubic
        'S' => 4,   's' => 4,   // CurveToCubicSmooth
        'Q' => 4,   'q' => 4,   // CurveToQuadratic
        'T' => 2,   't' => 2,   // CurveToQuadraticSmooth
        'A' => 7,   'a' => 7,   // ArcTo
        'Z' => 0,   'z' => 0,   // ClosePath
    );

    /**
     * Parses a path description into a consecutive array of commands.
     *
     * The commands themselves are associative arrays with 2 keys:
     * - string 'id': the command's id, e.g. 'M' or 'c' or ...
     * - float[] 'args': consecutive array of command arguments
     *
     * @param string $description The SVG path description to parse.
     *
     * @return array[] An array of commands (structure: see above).
     */
    public function parse($description)
    {
        $commands = array();

        $matches  = array();
        $idString = implode('', array_keys(self::$commandLengths));
        preg_match_all('/(['.$idString.'])([^'.$idString.']*)/', $description, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $id   = $match[1];
            $args = $this->splitArguments($match[2]);

            $success = $this->parseCommandChain($id, $args, $commands);
            if (!$success) {
                break;
            }
        }

        return $commands;
    }

    /**
     * Splits the given arguments string into an array of strings.
     *
     * @param string $str The string to split.
     *
     * @return string[] The splitted arguments.
     */
    private function splitArguments($str)
    {
        $str = trim($str);

        $args = array();
        if (!empty($str)) {
            preg_match_all('/[+-]?(\d*\.\d+|\d+)(e[+-]?\d+)?/', $str, $args);
            $args = $args[0];
        }

        return $args;
    }

    /**
     * Groups the given argument chain into sets and constructs a command array
     * for every set, which is then pushed to the given array reference.
     *
     * @param string   $id       The command id that the arguments belong to.
     * @param string[] $args     All of the arguments following the command.
     * @param array[]  $commands The array to add all parsed commands to.
     *
     * @return bool Whether the command is known AND the arg count is correct.
     */
    private function parseCommandChain($id, array $args, array &$commands)
    {
        if (!isset(self::$commandLengths[$id])) {
            // unknown command
            return false;
        }

        $length = self::$commandLengths[$id];

        if ($length === 0) {
            if (count($args) > 0) {
                return false;
            }
            $commands[] = array(
                'id'    => $id,
                'args'  => $args,
            );
            return true;
        }

        foreach (array_chunk($args, $length) as $subArgs) {
            if (count($subArgs) !== $length) {
                return false;
            }
            $commands[] = array(
                'id'    => $id,
                'args'  => array_map('floatval', $subArgs),
            );
            // If a MoveTo command is followed by additional coordinate pairs,
            // those are interpreted as implicit LineTo commands
            if ($id === 'M') {
                $id = 'L';
            } elseif ($id === 'm') {
                $id = 'l';
            }
        }

        return true;
    }
}
