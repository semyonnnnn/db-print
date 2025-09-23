<?php
namespace Services;
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

require_once __DIR__ . '/../utils.php';

class FormService
{
    public function generate(array $formData): void
    {
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => __DIR__ . '/../tmp',
        ]);

        // Convert raw data into rows
        $rows = array_map(fn($line) => explode('|', $line), $formData);

        // Optionally define headers
        $headers = ['Показатели', 'Ед. измерения', '2019']; // adjust to your table

        // Load the view and capture HTML
        $html = $this->renderView(__DIR__ . '/../views/pdf/form_table.php', compact('headers', 'rows'));

        $mpdf->WriteHTML($html);
        $mpdf->Output("form.pdf", "I");
    }
    private function renderView(string $path, array $data = []): string
    {
        extract($data); // $headers, $rows
        ob_start();
        include $path;
        return ob_get_clean();
    }

}