<?php

namespace App\Services;

use App\Models\ProductFile;
use App\Models\Purchase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class LicensedPdfService
{
    public function path(ProductFile $file, Purchase $purchase): string
    {
        if ($file->disk !== 'local' || $file->extension() !== 'pdf') {
            throw new RuntimeException('O arquivo de origem deve ser um PDF privado.');
        }

        $disk = Storage::disk('local');
        $source = $disk->path($file->path);
        $name = $purchase->licensed_name ?: $purchase->user()->value('name');

        if (! is_file($source) || blank($name)) {
            throw new RuntimeException('O PDF ou o nome do comprador não está disponível.');
        }

        $fingerprint = hash('sha256', hash_file('sha256', $source).'|'.$name.'|top-label-v2');
        $relativePath = "licenses/{$purchase->id}/{$file->id}-{$fingerprint}.pdf";

        if ($disk->exists($relativePath)) {
            return $relativePath;
        }

        $disk->makeDirectory("licenses/{$purchase->id}");
        $target = $disk->path($relativePath);
        $temporary = tempnam(dirname($target), 'license-');

        if ($temporary === false) {
            throw new RuntimeException('Não foi possível preparar o PDF personalizado.');
        }

        $previousMemoryLimit = ini_get('memory_limit');

        try {
            ini_set('memory_limit', '512M');
            $this->render($source, $temporary, (string) $name);

            if (! rename($temporary, $target) && ! is_file($target)) {
                throw new RuntimeException('Não foi possível armazenar o PDF personalizado.');
            }
        } finally {
            ini_set('memory_limit', $previousMemoryLimit);

            if (is_file($temporary)) {
                unlink($temporary);
            }
        }

        return $relativePath;
    }

    private function render(string $source, string $target, string $name): void
    {
        $pdf = new Fpdi;
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCompression(false);
        $pageCount = $pdf->setSourceFile($source);
        $label = iconv('UTF-8', 'Windows-1252//TRANSLIT', "Licenciado para {$name}");

        if ($label === false) {
            throw new RuntimeException('Não foi possível codificar o nome do comprador.');
        }

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $template = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);
            $pdf->SetTextColor(85, 91, 100);

            $fontSize = 7;
            do {
                $pdf->SetFont('Helvetica', '', $fontSize);
                $fontSize -= 0.5;
            } while ($pdf->GetStringWidth($label) > $size['width'] - 24 && $fontSize >= 5);

            $pdf->SetXY(12, 1);
            $pdf->Cell($size['width'] - 24, 4, $label, 0, 0, 'R');
        }

        $pdf->Output('F', $target);
    }
}
