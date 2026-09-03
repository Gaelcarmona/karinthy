<?php

namespace App\Services\Wikipedia;

use Generator;
use RuntimeException;

/**
 * Lit un dump SQL de Wikimedia (`.sql.gz`) et en restitue les lignes une à une.
 *
 * Ces dumps sont une suite de `INSERT INTO ... VALUES (...),(...),...;` où
 * chaque instruction pèse environ un mégaoctet. Le fichier est décompressé au
 * fil de l'eau et jamais chargé en entier : `page.sql.gz` fait 462 Mo compressé
 * et plusieurs gigaoctets une fois déplié.
 */
class SqlDumpReader
{
    private const CHUNK_BYTES = 1 << 20;

    /** Début d'une liste de valeurs, seul endroit où commencent les données. */
    private const MARKER = 'VALUES (';

    /**
     * Un n-uplet : tout ce qui n'est ni une parenthèse, ni une chaîne entre
     * apostrophes, ni un caractère échappé. Les parenthèses n'apparaissent donc
     * dans une valeur que protégées par des apostrophes.
     */
    private const TUPLE = "/\\(((?:[^)'\\\\]|\\\\.|'(?:[^'\\\\]|\\\\.)*')*)\\)/A";

    /** Une valeur : soit une chaîne entre apostrophes, soit une valeur nue. */
    private const VALUE = "/'((?:[^'\\\\]|\\\\.)*)'|([^,]+)/";

    private const UNESCAPE = [
        '\\0' => "\0",
        '\\b' => "\x08",
        '\\n' => "\n",
        '\\r' => "\r",
        '\\t' => "\t",
        '\\Z' => "\x1A",
        '\\\\' => '\\',
        "\\'" => "'",
        '\\"' => '"',
    ];

    public function __construct(private readonly string $path)
    {
    }

    /**
     * Les lignes du dump, dans l'ordre du fichier.
     *
     * @return Generator<int, array<int, string|null>>
     */
    public function rows(): Generator
    {
        $handle = gzopen($this->path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Dump illisible : {$this->path}");
        }

        $buffer = '';
        $reading = false;    // true entre le « VALUES ( » et le « ; » qui le clôt
        $exhausted = false;

        try {
            while (true) {
                if (! $exhausted) {
                    $chunk = gzread($handle, self::CHUNK_BYTES);

                    if ($chunk === false || $chunk === '') {
                        $exhausted = true;
                    } else {
                        $buffer .= $chunk;
                    }
                }

                $offset = 0;

                while (true) {
                    if (! $reading) {
                        // Le préambule contient des CREATE TABLE, donc des
                        // parenthèses qui ne sont pas des données.
                        $start = strpos($buffer, self::MARKER, $offset);

                        if ($start === false) {
                            break;
                        }

                        // Se placer sur la parenthèse ouvrante du marqueur.
                        $offset = $start + strlen(self::MARKER) - 1;
                        $reading = true;
                    }

                    if (! isset($buffer[$offset])) {
                        break;
                    }

                    // Séparateur laissé par le n-uplet précédent : virgule entre
                    // deux lignes, point-virgule à la fin de l'instruction.
                    if ($buffer[$offset] === ',' || $buffer[$offset] === ';') {
                        $reading = $buffer[$offset] !== ';';
                        $offset++;

                        continue;
                    }

                    if (preg_match(self::TUPLE, $buffer, $matches, 0, $offset) !== 1) {
                        break;
                    }

                    yield $this->values($matches[1]);

                    $offset += strlen($matches[0]);
                }

                if ($exhausted) {
                    break;
                }

                // Ce qui reste est un n-uplet coupé par la fin du morceau lu. Hors
                // instruction, on ne garde que de quoi reconstituer un marqueur
                // à cheval sur deux morceaux.
                $buffer = $reading
                    ? substr($buffer, $offset)
                    : substr($buffer, max($offset, strlen($buffer) - strlen(self::MARKER)));
            }
        } finally {
            gzclose($handle);
        }
    }

    /**
     * @return array<int, string|null>
     */
    private function values(string $tuple): array
    {
        preg_match_all(self::VALUE, $tuple, $matches, PREG_SET_ORDER);

        $values = [];

        foreach ($matches as $match) {
            if (count($match) > 2) {
                $bare = trim($match[2]);
                $values[] = $bare === 'NULL' ? null : $bare;

                continue;
            }

            $values[] = strtr($match[1], self::UNESCAPE);
        }

        return $values;
    }
}
