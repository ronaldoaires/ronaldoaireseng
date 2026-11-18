<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * What kind of digital product is sold. Each type has its own URL prefix
 * (/ebooks/..., /planilhas/...) but shares the sales page, checkout and library.
 */
enum ProductType: string implements HasColor, HasIcon, HasLabel
{
    case Ebook       = 'ebook';
    case Spreadsheet = 'spreadsheet';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ebook       => 'Ebook',
            self::Spreadsheet => 'Planilha',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Ebook       => 'Ebooks',
            self::Spreadsheet => 'Planilhas',
        };
    }

    /**
     * First URL segment of the public pages.
     */
    public function segment(): string
    {
        return match ($this) {
            self::Ebook       => 'ebooks',
            self::Spreadsheet => 'planilhas',
        };
    }

    public static function fromSegment(string $segment): self
    {
        foreach (self::cases() as $case) {
            if ($case->segment() === $segment) {
                return $case;
            }
        }

        abort(404);
    }

    /**
     * Regex for route constraints, e.g. "ebooks|planilhas".
     */
    public static function segmentPattern(): string
    {
        return implode('|', array_map(fn (self $case) => $case->segment(), self::cases()));
    }

    public function headline(): string
    {
        return match ($this) {
            self::Ebook       => 'Ebooks de engenharia civil escritos por quem projeta, com versão em PDF e leitura online.',
            self::Spreadsheet => 'Planilhas de cálculo prontas para usar, seguindo as normas brasileiras.',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Ebook       => 'heroicon-o-book-open',
            self::Spreadsheet => 'heroicon-o-table-cells',
        };
    }

    public function iconName(): string
    {
        return str_replace('heroicon-o-', '', $this->getIcon());
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ebook       => 'info',
            self::Spreadsheet => 'success',
        };
    }
}
