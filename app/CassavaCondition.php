<?php

namespace App;

enum CassavaCondition: string
{
    case Fresh = 'fresh';
    case Stale = 'stale';
    case Rotten = 'rotten';

    /** Label bahasa Indonesia untuk ditampilkan di UI. */
    public function label(): string
    {
        return match ($this) {
            self::Fresh => 'Segar',
            self::Stale => 'Mulai Busuk',
            self::Rotten => 'Busuk',
        };
    }

    /** Warna Tailwind CSS untuk badge. */
    public function color(): string
    {
        return match ($this) {
            self::Fresh => 'green',
            self::Stale => 'yellow',
            self::Rotten => 'red',
        };
    }

    /** @return array<string, string> map value → label untuk select option */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
