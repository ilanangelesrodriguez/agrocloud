<?php

namespace App\Services\Exports;

use DateTimeInterface;
use Phar;
use PharData;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class XlsxExporter
{
    /**
     * @param array<int, array{key:string,label:string,width?:float,type?:string,status?:bool}> $columns
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, scalar|null> $meta
     */
    public function download(
        string $filename,
        string $title,
        array $columns,
        array $rows,
        array $meta = [],
        string $sheetName = 'Datos',
    ): BinaryFileResponse {
        $path = $this->build($title, $columns, $rows, $meta, $sheetName);

        return response()
            ->download($path, $this->sanitizeFilename($filename).'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * Construye un XLSX OpenXML sin dependencias Composer adicionales.
     * PharData crea internamente el ZIP que compone un archivo .xlsx.
     *
     * @param array<int, array{key:string,label:string,width?:float,type?:string,status?:bool}> $columns
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, scalar|null> $meta
     */
    public function build(
        string $title,
        array $columns,
        array $rows,
        array $meta = [],
        string $sheetName = 'Datos',
    ): string {
        if ($columns === []) {
            throw new \InvalidArgumentException('El exportable necesita al menos una columna.');
        }

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'agrocloud_'.bin2hex(random_bytes(8)).'.xlsx';
        $archive = new PharData($path, 0, null, Phar::ZIP);

        $files = [
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->rootRelationshipsXml(),
            'docProps/app.xml' => $this->appPropertiesXml(),
            'docProps/core.xml' => $this->corePropertiesXml($title),
            'xl/workbook.xml' => $this->workbookXml($sheetName),
            'xl/_rels/workbook.xml.rels' => $this->workbookRelationshipsXml(),
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $this->worksheetXml($title, $columns, $rows, $meta),
        ];

        foreach ($files as $name => $content) {
            $archive[$name] = $content;
        }

        unset($archive);

        return $path;
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;
    }

    private function rootRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    private function workbookRelationshipsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(string $sheetName): string
    {
        $sheetName = substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $sheetName) ?: 'Datos', 0, 31);
        $sheetName = $this->xml($sheetName);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <bookViews><workbookView xWindow="0" yWindow="0" windowWidth="22000" windowHeight="14000"/></bookViews>
  <sheets><sheet name="{$sheetName}" sheetId="1" r:id="rId1"/></sheets>
</workbook>
XML;
    }

    private function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="11"/><name val="Aptos"/><family val="2"/></font>
    <font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Aptos"/><family val="2"/></font>
    <font><b/><color rgb="FF124932"/><sz val="16"/><name val="Aptos Display"/><family val="2"/></font>
  </fonts>
  <fills count="6">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF158A55"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEFFCF5"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEF2F2"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFE2E8F0"/></left>
      <right style="thin"><color rgb="FFE2E8F0"/></right>
      <top style="thin"><color rgb="FFE2E8F0"/></top>
      <bottom style="thin"><color rgb="FFE2E8F0"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="7">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1"><alignment vertical="center"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    /**
     * @param array<int, array{key:string,label:string,width?:float,type?:string,status?:bool}> $columns
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, scalar|null> $meta
     */
    private function worksheetXml(string $title, array $columns, array $rows, array $meta): string
    {
        $lastColumn = $this->columnName(count($columns));
        $lastRow = max(4 + count($rows), 4);
        $dimension = "A1:{$lastColumn}{$lastRow}";

        $colsXml = '';
        foreach ($columns as $index => $column) {
            $position = $index + 1;
            $width = max(8, min(60, (float) ($column['width'] ?? 18)));
            $colsXml .= '<col min="'.$position.'" max="'.$position.'" width="'.$width.'" customWidth="1"/>';
        }

        $metaText = 'Generado: '.now()->format('d/m/Y H:i');
        foreach ($meta as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $metaText .= '  ·  '.$label.': '.$this->stringValue($value);
        }

        $sheetRows = '<row r="1" ht="30" customHeight="1">'.$this->inlineCell('A1', $title, 1).'</row>';
        $sheetRows .= '<row r="2" ht="22" customHeight="1">'.$this->inlineCell('A2', $metaText, 0).'</row>';
        $sheetRows .= '<row r="3" ht="9" customHeight="1"/>';

        $headerCells = '';
        foreach ($columns as $index => $column) {
            $cell = $this->columnName($index + 1).'4';
            $headerCells .= $this->inlineCell($cell, $column['label'], 2);
        }
        $sheetRows .= '<row r="4" ht="28" customHeight="1">'.$headerCells.'</row>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 5;
            $cells = '';

            foreach ($columns as $columnIndex => $column) {
                $cell = $this->columnName($columnIndex + 1).$excelRow;
                $value = $row[$column['key']] ?? '';
                $style = $rowIndex % 2 === 0 ? 3 : 4;

                if (($column['status'] ?? false) === true) {
                    $normalized = strtolower(trim($this->stringValue($value)));
                    $style = in_array($normalized, ['activo', 'activa', 'sí', 'si'], true) ? 5 : 6;
                }

                if (($column['type'] ?? null) === 'number' && is_numeric($value)) {
                    $cells .= '<c r="'.$cell.'" s="'.$style.'" t="n"><v>'.(string) $value.'</v></c>';
                } else {
                    $cells .= $this->inlineCell($cell, $this->stringValue($value), $style);
                }
            }

            $sheetRows .= '<row r="'.$excelRow.'" ht="22" customHeight="1">'.$cells.'</row>';
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>
  <dimension ref="{$dimension}"/>
  <sheetViews>
    <sheetView workbookViewId="0" showGridLines="0">
      <pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/>
    </sheetView>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="18"/>
  <cols>{$colsXml}</cols>
  <sheetData>{$sheetRows}</sheetData>
  <autoFilter ref="A4:{$lastColumn}{$lastRow}"/>
  <mergeCells count="2"><mergeCell ref="A1:{$lastColumn}1"/><mergeCell ref="A2:{$lastColumn}2"/></mergeCells>
  <pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>
  <pageSetup orientation="landscape" fitToWidth="1" fitToHeight="0" paperSize="9"/>
</worksheet>
XML;
    }

    private function inlineCell(string $reference, string $value, int $style): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml($value).'</t></is></c>';
    }

    private function appPropertiesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>AgroCloud AI</Application>
  <AppVersion>1.0</AppVersion>
</Properties>
XML;
    }

    private function corePropertiesXml(string $title): string
    {
        $title = $this->xml($title);
        $created = now('UTC')->format('Y-m-d\TH:i:s\Z');

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>{$title}</dc:title>
  <dc:creator>AgroCloud AI</dc:creator>
  <cp:lastModifiedBy>AgroCloud AI</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">{$created}</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">{$created}</dcterms:modified>
</cp:coreProperties>
XML;
    }

    private function stringValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $filename) ?: 'export_agrocloud';

        return trim($filename, '_');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
