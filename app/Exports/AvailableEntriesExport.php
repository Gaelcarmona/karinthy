<?php

namespace App\Exports;

use App\Models\AvailableEntry;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AvailableEntriesExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        $data = [];

        foreach ($this->rows as $row) {
            // On divise la chaîne de caractères pour obtenir les valeurs de parent et d'enfant
            list($parent, $child) = explode(',', $row);
            // On enlève les // entourant les valeurs
            $parent = trim($parent, '/');
            $child = trim($child, '/');
            // On ajoute les valeurs dans un tableau associatif
            $data[] = [
                'parent' => $parent,
                'child' => $child,
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'parent',
            'child',
        ];
    }
}
