<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class DesignedXlsxWorkbook
{
    /**
     * @param  array<int, array<string, mixed>>  $sheets
     */
    public function __construct(private readonly array $sheets) {}

    public function toBinary(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pd-xlsx-');

        if ($path === false) {
            throw new RuntimeException('Unable to create temporary XLSX file.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);

            throw new RuntimeException('Unable to open temporary XLSX file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('docProps/app.xml', $this->appPropertiesXml());
        $zip->addFromString('docProps/core.xml', $this->corePropertiesXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());

        foreach ($this->sheets as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet'.($index + 1).'.xml', $this->worksheetXml($sheet));
        }

        $zip->close();

        $contents = file_get_contents($path);
        @unlink($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read temporary XLSX file.');
        }

        return $contents;
    }

    private function contentTypesXml(): string
    {
        $sheetOverrides = collect($this->sheets)
            ->keys()
            ->map(fn (int $index): string => '<Override PartName="/xl/worksheets/sheet'.($index + 1).'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>')
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$sheetOverrides
            .'</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function appPropertiesXml(): string
    {
        $sheetCount = count($this->sheets);
        $sheetNames = collect($this->sheets)
            ->map(fn (array $sheet): string => '<vt:lpstr>'.$this->xml((string) $sheet['name']).'</vt:lpstr>')
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>Paradise Dolls</Application><DocSecurity>0</DocSecurity><ScaleCrop>false</ScaleCrop>'
            .'<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>'.$sheetCount.'</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            .'<TitlesOfParts><vt:vector size="'.$sheetCount.'" baseType="lpstr">'.$sheetNames.'</vt:vector></TitlesOfParts>'
            .'</Properties>';
    }

    private function corePropertiesXml(): string
    {
        $timestamp = now()->toIso8601ZuluString();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:creator>Paradise Dolls</dc:creator><cp:lastModifiedBy>Paradise Dolls</cp:lastModifiedBy>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$timestamp.'</dcterms:created>'
            .'<dcterms:modified xsi:type="dcterms:W3CDTF">'.$timestamp.'</dcterms:modified>'
            .'</cp:coreProperties>';
    }

    private function workbookXml(): string
    {
        $sheets = collect($this->sheets)
            ->map(fn (array $sheet, int $index): string => '<sheet name="'.$this->xml((string) $sheet['name']).'" sheetId="'.($index + 1).'" r:id="rId'.($index + 1).'"/>')
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<bookViews><workbookView showHorizontalScroll="1" showVerticalScroll="1" showSheetTabs="1" tabRatio="600"/></bookViews>'
            .'<sheets>'.$sheets.'</sheets><calcPr calcId="124519" fullCalcOnLoad="1"/></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        $sheetRelationships = collect($this->sheets)
            ->keys()
            ->map(fn (int $index): string => '<Relationship Id="rId'.($index + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.($index + 1).'.xml"/>')
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$sheetRelationships
            .'<Relationship Id="rId'.(count($this->sheets) + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    /**
     * @param  array<string, mixed>  $sheet
     */
    private function worksheetXml(array $sheet): string
    {
        $rows = $sheet['rows'] ?? [];
        $merges = $sheet['merges'] ?? [];
        $columns = $sheet['columns'] ?? [];
        $maxRow = max(1, ...collect($rows)->pluck('r')->all());
        $maxCol = max(
            1,
            ...collect($rows)->flatMap(fn (array $row) => collect($row['cells'] ?? [])->pluck('col'))->all(),
            ...collect($merges)->map(fn (string $range): int => $this->rangeEndColumnNumber($range))->all(),
        );
        $dimension = 'A1:'.$this->columnName($maxCol).$maxRow;

        $colsXml = collect($columns)
            ->map(fn (int|float $width, int $index): string => '<col min="'.($index + 1).'" max="'.($index + 1).'" width="'.$width.'" customWidth="1"/>')
            ->implode('');

        $rowsXml = collect($rows)
            ->map(fn (array $row): string => $this->rowXml($row))
            ->implode('');

        $mergeXml = '';

        if ($merges !== []) {
            $mergeXml = '<mergeCells count="'.count($merges).'">'
                .collect($merges)->map(fn (string $range): string => '<mergeCell ref="'.$this->xml($range).'"/>')->implode('')
                .'</mergeCells>';
        }

        $freezeRow = (int) ($sheet['freezeRow'] ?? 0);
        $paneXml = $freezeRow > 0
            ? '<pane ySplit="'.$freezeRow.'" topLeftCell="A'.($freezeRow + 1).'" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A1" sqref="A1"/>'
            : '<selection activeCell="A1" sqref="A1"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetPr><outlinePr summaryBelow="1" summaryRight="1"/><pageSetUpPr/></sheetPr>'
            .'<dimension ref="'.$dimension.'"/>'
            .'<sheetViews><sheetView showGridLines="0" workbookViewId="0">'.$paneXml.'</sheetView></sheetViews>'
            .'<sheetFormatPr baseColWidth="8" defaultRowHeight="15"/>'
            .($colsXml !== '' ? '<cols>'.$colsXml.'</cols>' : '')
            .'<sheetData>'.$rowsXml.'</sheetData>'
            .$mergeXml
            .'<pageMargins left="0.75" right="0.75" top="1" bottom="1" header="0.5" footer="0.5"/>'
            .'</worksheet>';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowXml(array $row): string
    {
        $height = isset($row['height']) ? ' ht="'.$row['height'].'" customHeight="1"' : '';
        $cells = collect($row['cells'] ?? [])
            ->map(fn (array $cell): string => $this->cellXml($cell, (int) $row['r']))
            ->implode('');

        return '<row r="'.$row['r'].'"'.$height.'>'.$cells.'</row>';
    }

    /**
     * @param  array<string, mixed>  $cell
     */
    private function cellXml(array $cell, int $row): string
    {
        $reference = $this->columnName((int) $cell['col']).$row;
        $style = isset($cell['style']) ? ' s="'.((int) $cell['style']).'"' : '';
        $value = $cell['value'] ?? null;

        if ($value === null || $value === '') {
            return '<c r="'.$reference.'"'.$style.'/>';
        }

        if (is_int($value) || is_float($value)) {
            return '<c r="'.$reference.'"'.$style.' t="n"><v>'.$value.'</v></c>';
        }

        $value = $this->cleanString((string) $value);
        $space = preg_match('/^\s|\s$/u', $value) ? ' xml:space="preserve"' : '';

        return '<c r="'.$reference.'"'.$style.' t="inlineStr"><is><t'.$space.'>'.$this->xml($value).'</t></is></c>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .$this->fontsXml()
            .$this->fillsXml()
            .$this->bordersXml()
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .$this->cellFormatsXml()
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'<tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleLight16"/>'
            .'</styleSheet>';
    }

    private function fontsXml(): string
    {
        $fonts = [
            ['Calibri', 11, '000000', false, false],
            ['Arial', 16, 'FFFFFF', true, false],
            ['Arial', 10, '880E4F', false, true],
            ['Arial', 11, 'FFFFFF', true, false],
            ['Arial', 10, '880E4F', true, false],
            ['Arial', 10, '2D1B2E', false, false],
            ['Arial', 11, 'C2185B', true, false],
            ['Arial', 10, '388E3C', true, false],
            ['Arial', 10, 'E65100', true, false],
            ['Arial', 10, 'C62828', true, false],
            ['Arial', 14, '1565C0', true, false],
            ['Arial', 14, '2E7D32', true, false],
            ['Arial', 14, '6A1B9A', true, false],
            ['Arial', 14, 'E65100', true, false],
        ];

        return '<fonts count="'.count($fonts).'">'
            .collect($fonts)->map(function (array $font): string {
                [$name, $size, $color, $bold, $italic] = $font;

                return '<font><name val="'.$name.'"/>'
                    .($bold ? '<b/>' : '')
                    .($italic ? '<i/>' : '')
                    .'<color rgb="00'.$color.'"/><sz val="'.$size.'"/></font>';
            })->implode('')
            .'</fonts>';
    }

    private function fillsXml(): string
    {
        $fills = [
            null,
            'gray125',
            '880E4F',
            'F3E5F0',
            '2E7D32',
            '1565C0',
            'FFB6C1',
            'F8E8F0',
            'FFFFFF',
            'C62828',
            'F8F8F8',
            '6A1B9A',
            'E65100',
            'E8F5E9',
            'FFEBEE',
            'FFF3E0',
        ];

        return '<fills count="'.count($fills).'">'
            .collect($fills)->map(function (?string $fill): string {
                if ($fill === null) {
                    return '<fill><patternFill/></fill>';
                }

                if ($fill === 'gray125') {
                    return '<fill><patternFill patternType="gray125"/></fill>';
                }

                return '<fill><patternFill patternType="solid"><fgColor rgb="00'.$fill.'"/></patternFill></fill>';
            })->implode('')
            .'</fills>';
    }

    private function bordersXml(): string
    {
        return '<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border>'
            .'<left style="thin"><color rgb="00DDBBCC"/></left>'
            .'<right style="thin"><color rgb="00DDBBCC"/></right>'
            .'<top style="thin"><color rgb="00DDBBCC"/></top>'
            .'<bottom style="thin"><color rgb="00DDBBCC"/></bottom>'
            .'</border>'
            .'</borders>';
    }

    private function cellFormatsXml(): string
    {
        $formats = [
            [0, 0, 0, 'left', false],
            [1, 2, 0, 'center', true],
            [2, 3, 0, 'center', true],
            [3, 4, 0, 'center', true],
            [3, 5, 0, 'center', true],
            [3, 2, 0, 'left', false],
            [4, 6, 1, 'left', false],
            [5, 7, 1, 'left', true],
            [5, 8, 1, 'left', true],
            [3, 2, 1, 'center', true],
            [7, 13, 1, 'center', true],
            [7, 8, 1, 'center', true],
            [8, 15, 1, 'center', true],
            [6, 7, 1, 'left', false],
            [5, 7, 1, 'center', true],
            [5, 7, 1, 'left', true],
            [7, 7, 1, 'center', true],
            [9, 14, 1, 'center', true],
            [10, 10, 0, 'center', true],
            [11, 10, 0, 'center', true],
            [12, 10, 0, 'center', true],
            [13, 10, 0, 'center', true],
            [3, 9, 0, 'left', false],
            [9, 14, 1, 'left', true],
        ];

        return '<cellXfs count="'.count($formats).'">'
            .collect($formats)->map(function (array $format): string {
                [$fontId, $fillId, $borderId, $horizontal, $wrap] = $format;

                return '<xf numFmtId="0" fontId="'.$fontId.'" fillId="'.$fillId.'" borderId="'.$borderId.'" applyAlignment="1" xfId="0">'
                    .'<alignment horizontal="'.$horizontal.'" vertical="center"'.($wrap ? ' wrapText="1"' : '').'/>'
                    .'</xf>';
            })->implode('')
            .'</cellXfs>';
    }

    private function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $mod = ($column - 1) % 26;
            $name = chr(65 + $mod).$name;
            $column = intdiv($column - $mod - 1, 26);
        }

        return $name;
    }

    private function rangeEndColumnNumber(string $range): int
    {
        $cell = str_contains($range, ':') ? str($range)->after(':')->toString() : $range;

        if (! preg_match('/^([A-Z]+)/i', $cell, $matches)) {
            return 1;
        }

        $letters = strtoupper($matches[1]);
        $number = 0;

        for ($index = 0; $index < strlen($letters); $index++) {
            $number = ($number * 26) + (ord($letters[$index]) - 64);
        }

        return $number;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function cleanString(string $value): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';
    }
}
