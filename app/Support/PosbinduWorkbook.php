<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

final class PosbinduWorkbook
{
    private const SHEET_PATH = 'xl/worksheets/sheet1.xml';
    private const FIRST_DATA_ROW = 6;
    private const LAST_TEMPLATE_ROW = 234;

    public static function create(string $templatePath, string $targetPath, array $rows): void
    {
        if (! is_file($templatePath)) {
            throw new RuntimeException('Template laporan Posbindu tidak ditemukan.');
        }
        if (count($rows) > self::LAST_TEMPLATE_ROW - self::FIRST_DATA_ROW + 1) {
            throw new RuntimeException('Jumlah peserta melebihi kapasitas template laporan.');
        }
        if (! copy($templatePath, $targetPath)) {
            throw new RuntimeException('Salinan laporan Posbindu gagal dibuat.');
        }

        $zip = new ZipArchive();
        if ($zip->open($targetPath) !== true) {
            throw new RuntimeException('File laporan Posbindu tidak dapat dibuka.');
        }

        try {
            $xml = $zip->getFromName(self::SHEET_PATH);
            if (! is_string($xml) || $xml === '') {
                throw new RuntimeException('Sheet laporan Posbindu tidak ditemukan.');
            }

            $document = new DOMDocument('1.0', 'UTF-8');
            $document->preserveWhiteSpace = false;
            if (! $document->loadXML($xml, LIBXML_NONET)) {
                throw new RuntimeException('Struktur sheet laporan Posbindu tidak valid.');
            }

            $xpath = new DOMXPath($document);
            $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $xpath->registerNamespace('x', $namespace);
            $styleMap = self::styleMap($xpath);

            for ($rowNumber = self::FIRST_DATA_ROW; $rowNumber <= self::LAST_TEMPLATE_ROW; $rowNumber++) {
                $rowElement = $xpath->query('//x:sheetData/x:row[@r="' . $rowNumber . '"]')->item(0);
                if (! $rowElement instanceof DOMElement) {
                    continue;
                }
                while ($rowElement->firstChild) {
                    $rowElement->removeChild($rowElement->firstChild);
                }
                if (isset($rows[$rowNumber - self::FIRST_DATA_ROW])) {
                    self::writeRow($document, $rowElement, $namespace, $styleMap, $rows[$rowNumber - self::FIRST_DATA_ROW], $rowNumber);
                } else {
                    self::writeRow($document, $rowElement, $namespace, $styleMap, array_fill(0, 61, ''), $rowNumber);
                }
            }

            $zip->addFromString(self::SHEET_PATH, $document->saveXML());
        } finally {
            $zip->close();
        }
    }

    private static function styleMap(DOMXPath $xpath): array
    {
        $styles = [];
        foreach ($xpath->query('//x:sheetData/x:row[@r="6"]/x:c') as $cell) {
            if ($cell instanceof DOMElement) {
                $styles[preg_replace('/\d+$/', '', $cell->getAttribute('r'))] = $cell->getAttribute('s');
            }
        }

        return $styles;
    }

    private static function writeRow(DOMDocument $document, DOMElement $row, string $namespace, array $styles, array $values, int $rowNumber): void
    {
        $values = array_pad(array_slice($values, 0, 61), 61, '');
        foreach ($values as $index => $value) {
            $column = self::columnName($index + 2);
            $cell = $document->createElementNS($namespace, 'c');
            $cell->setAttribute('r', $column . $rowNumber);
            if (($styles[$column] ?? '') !== '') {
                $cell->setAttribute('s', $styles[$column]);
            }

            if ($value !== '' && $value !== null) {
                if (is_int($value) || is_float($value)) {
                    $cell->appendChild($document->createElementNS($namespace, 'v', (string) $value));
                } else {
                    $cell->setAttribute('t', 'inlineStr');
                    $inline = $document->createElementNS($namespace, 'is');
                    $text = $document->createElementNS($namespace, 't');
                    $text->setAttribute('xml:space', 'preserve');
                    $text->appendChild($document->createTextNode((string) $value));
                    $inline->appendChild($text);
                    $cell->appendChild($inline);
                }
            }
            $row->appendChild($cell);
        }
    }

    private static function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}
