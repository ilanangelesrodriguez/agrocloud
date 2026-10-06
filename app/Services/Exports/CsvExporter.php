<?php

namespace App\Services\Exports;

use DateTimeInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    /**
     * @param array<int, array{key:string,label:string}> $columns
     * @param array<int, array<string, mixed>> $rows
     */
    public function download(string $filename, array $columns, array $rows): StreamedResponse
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $filename) ?: 'export_agrocloud';

        return response()->streamDownload(function () use ($columns, $rows): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }

            // BOM UTF-8 para que Excel en Windows reconozca tildes y ñ correctamente.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, array_column($columns, 'label'), ';', '"', '');

            foreach ($rows as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $values[] = $this->stringValue($row[$column['key']] ?? null);
                }
                fputcsv($output, $values, ';', '"', '');
            }

            fclose($output);
        }, $filename.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function stringValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        return $value === null ? '' : (string) $value;
    }
}
