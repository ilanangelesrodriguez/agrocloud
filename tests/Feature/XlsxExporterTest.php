<?php

namespace Tests\Feature;

use App\Services\Exports\XlsxExporter;
use DOMDocument;
use Phar;
use PharData;
use Tests\TestCase;
use ZipArchive;

class XlsxExporterTest extends TestCase
{
    public function test_excel_de_empresas_contiene_archivos_y_xml_validos(): void
    {
        $path = app(XlsxExporter::class)->build(
            'Empresas registradas en AgroCloud AI',
            [
                ['key' => 'ruc', 'label' => 'RUC', 'width' => 15],
                ['key' => 'nombre', 'label' => 'Razón social', 'width' => 30],
                ['key' => 'usuarios', 'label' => 'Usuarios', 'type' => 'number'],
                ['key' => 'estado', 'label' => 'Estado', 'status' => true],
            ],
            [
                ['ruc' => '00123456789', 'nombre' => "Agro & Campo <Perú> \u{0001} S.A.", 'usuarios' => 3, 'estado' => 'Activa'],
                ['ruc' => '00234567890', 'nombre' => 'Empresa "Agrícola"', 'usuarios' => 0, 'estado' => 'Inactiva'],
            ],
            ['Filtros' => 'Búsqueda: Perú', 'Registros' => 2],
            'Empresas',
        );

        try {
            $this->assertFileExists($path);
            $files = $this->readArchive($path);
            $this->assertOpenXmlIsWellFormed($files);
            $this->assertStringContainsString('Agro &amp; Campo &lt;Perú&gt;  S.A.', $files['xl/worksheets/sheet1.xml']);
            $this->assertStringNotContainsString("\x01", $files['xl/worksheets/sheet1.xml']);
            $this->assertStringContainsString('<c r="C5" s="3" t="n"><v>3</v></c>', $files['xl/worksheets/sheet1.xml']);
            $this->assertStringContainsString('A4:D6', $files['xl/worksheets/sheet1.xml']);
        } finally {
            @unlink($path);
        }
    }

    public function test_excel_de_usuarios_vacio_y_nombre_hoja_se_crean_correctamente(): void
    {
        $path = app(XlsxExporter::class)->build(
            'Usuarios de empresa',
            [
                ['key' => 'nombre', 'label' => 'Nombre completo'],
                ['key' => 'correo', 'label' => 'Correo'],
            ],
            [],
            ['Registros' => 0],
            'Usuarios [Internos] con nombre demasiado largo',
        );

        try {
            $files = $this->readArchive($path);
            $this->assertOpenXmlIsWellFormed($files);
            $this->assertStringContainsString('name="Usuarios  Internos  con nombre', $files['xl/workbook.xml']);
            $this->assertStringNotContainsString('<autoFilter ', $files['xl/worksheets/sheet1.xml']);
        } finally {
            @unlink($path);
        }
    }

    /** @return array<string, string> */
    private function readArchive(string $path): array
    {
        $filenames = [
            '[Content_Types].xml', '_rels/.rels',
            'docProps/app.xml', 'docProps/core.xml',
            'xl/workbook.xml', 'xl/_rels/workbook.xml.rels',
            'xl/styles.xml', 'xl/worksheets/sheet1.xml',
        ];
        $files = [];

        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($path) === true);
            try {
                foreach ($filenames as $filename) {
                    $contents = $zip->getFromName($filename);
                    $this->assertNotFalse($contents, $filename);
                    $files[$filename] = $contents;
                }
            } finally {
                $zip->close();
            }
        } else {
            $phar = new PharData($path, 0, null, Phar::ZIP);
            foreach ($filenames as $filename) {
                $this->assertTrue(isset($phar[$filename]), $filename);
                $files[$filename] = $phar[$filename]->getContent();
            }
        }

        return $files;
    }

    /** @param array<string, string> $files */
    private function assertOpenXmlIsWellFormed(array $files): void
    {
        foreach ($files as $filename => $contents) {
            $xml = new DOMDocument();
            $previous = libxml_use_internal_errors(true);
            try {
                $this->assertTrue($xml->loadXML($contents), 'XML inválido: '.$filename);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        }
    }
}
