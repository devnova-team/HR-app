<?php

namespace App\Support;

/**
 * Minimal one-page PDF writer (text only, built-in Helvetica fonts, no packages).
 *
 * Temporary solution until the team approves a PDF package (e.g. dompdf).
 * Only Latin-1 characters are supported: anything else is printed as "?".
 */
class SimplePdf
{
    private const TOP = 800;
    private const BOTTOM = 50;
    private const LEFT = 50;

    /** @var array<int, array{string, int, bool}> */
    private array $lines = [];

    public function line(string $text, int $size = 11, bool $bold = false): static
    {
        $this->lines[] = [$text, $size, $bold];

        return $this;
    }

    public function space(int $points = 8): static
    {
        $this->lines[] = ['', $points, false];

        return $this;
    }

    public function output(): string
    {
        $content = '';
        $y = self::TOP;

        foreach ($this->lines as [$text, $size, $bold]) {
            $y -= $text === '' ? $size : $size + 6;

            if ($y < self::BOTTOM) {
                break; // one page only
            }

            if ($text !== '') {
                $content .= sprintf(
                    "BT /%s %d Tf %d %d Td (%s) Tj ET\n",
                    $bold ? 'F2' : 'F1',
                    $size,
                    self::LEFT,
                    $y,
                    $this->escape($text)
                );
            }
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R '
                . '/Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>',
            4 => '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream",
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private function escape(string $text): string
    {
        $text = preg_replace('/[\x00-\x1F]/', ' ', $text) ?? '';
        $text = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
