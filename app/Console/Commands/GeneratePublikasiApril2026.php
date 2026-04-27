<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GeneratePublikasiApril2026 extends Command
{
    protected $signature = 'publikasi:generate-april2026
                            {--sheet=P2026 : Nama sheet yang berisi data publikasi}
                            {--source=storage/logs/publikasi/april-2026.xlsx : Path file .xlsx relatif terhadap base path}
                            {--output=storage/logs/publikasi/april2026 : Folder output relatif terhadap base path}';

    protected $description = 'Generate artikel .txt dari data publikasi pada file Excel (april-2026.xlsx)';

    public function handle()
    {
        // Reduce noise from PHP 8.x deprecations in legacy dependencies.
        error_reporting(E_ERROR | E_PARSE);

        $sourcePath = base_path((string) $this->option('source'));
        if (!File::exists($sourcePath)) {
            $this->error("File tidak ditemukan: {$sourcePath}");
            return 1;
        }

        $outputDir = base_path((string) $this->option('output'));
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $sheetName = (string) $this->option('sheet');

        $spreadsheet = IOFactory::load($sourcePath);
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if ($sheet === null) {
            $this->error("Sheet tidak ditemukan: {$sheetName}");
            return 1;
        }

        $headers = $this->readHeaders($sheet);
        $required = ['NO', 'JUDUL', 'PENGARANG', 'TERBIT', 'JURNAL'];
        foreach ($required as $key) {
            if (!isset($headers[$key])) {
                $this->error("Header wajib tidak ditemukan: {$key}");
                return 1;
            }
        }

        $highestRow = (int) $sheet->getHighestDataRow();

        $written = 0;
        $skipped = 0;

        for ($row = 3; $row <= $highestRow; $row++) {
            $no = $this->cell($sheet, $headers['NO'], $row);
            $title = $this->cell($sheet, $headers['JUDUL'], $row);

            if ($no === '' && $title === '') {
                continue;
            }

            if ($title === '') {
                $skipped++;
                continue;
            }

            $data = [
                'no' => $no,
                'title' => $this->cleanTitle($title),
                'authors' => $this->cleanAuthors($this->cell($sheet, $headers['PENGARANG'], $row)),
                'dosen_terlibat' => $this->cell($sheet, $headers['JUMLAH DOSEN TERLIBAT'] ?? null, $row),
                'indexing' => $this->cell($sheet, $headers['WOS/SCOPUS'] ?? null, $row),
                'publish_date' => $this->cell($sheet, $headers['TERBIT'], $row),
                'vol_no' => $this->cell($sheet, $headers['VOL/NO'] ?? null, $row),
                'journal' => $this->cell($sheet, $headers['JURNAL'], $row),
                'quartile' => $this->cell($sheet, $headers['QUARTIL'] ?? null, $row),
                'pages' => $this->cell($sheet, $headers['HALAMAN'] ?? null, $row),
                'publisher' => $this->cell($sheet, $headers['PENERBIT'] ?? null, $row),
                'notes' => $this->cell($sheet, $headers['KET'] ?? null, $row),
                'link_1' => $this->cell($sheet, $headers['LINK'] ?? null, $row),
                'link_2' => $this->cell($sheet, $headers['LINK (2)'] ?? null, $row),
            ];

            $content = $this->renderArticle($data);

            $path = $this->uniqueOutputPath($outputDir, $data['no'], $data['title']);
            File::put($path, $content);
            $written++;
        }

        $this->info("Selesai. Tertulis: {$written}. Dilewati: {$skipped}. Output: {$outputDir}");
        return 0;
    }

    private function readHeaders($sheet): array
    {
        $highestCol = $sheet->getHighestDataColumn();
        $max = Coordinate::columnIndexFromString($highestCol);

        // Headers are on row 2 for the P20xx sheets.
        $headerRow = 2;
        $headers = [];
        $linkCount = 0;

        for ($c = 1; $c <= $max; $c++) {
            $raw = trim((string) $sheet->getCellByColumnAndRow($c, $headerRow)->getFormattedValue());
            if ($raw === '') {
                continue;
            }

            $name = $this->normalizeHeader($raw);
            if ($name === 'LINK') {
                $linkCount++;
                $name = $linkCount === 1 ? 'LINK' : 'LINK (2)';
            }

            $headers[$name] = $c;
        }

        return $headers;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(preg_replace('/\s+/', ' ', $header));

        // Normalize some common variants in the Excel sheet.
        $header = str_replace(['Vol/No', 'Vol\\No'], 'Vol/No', $header);
        $header = str_replace(['WoS/Scopus', 'WoS\\Scopus'], 'WoS/Scopus', $header);

        return mb_strtoupper($header);
    }

    private function cell($sheet, ?int $col, int $row): string
    {
        if ($col === null) {
            return '';
        }

        $value = (string) $sheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
        $value = str_replace("\r", '', $value);
        $value = preg_replace("/[ \\t]+/", ' ', $value);
        return trim((string) $value);
    }

    private function cleanTitle(string $title): string
    {
        $title = str_replace("\n", ' ', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        return trim((string) $title);
    }

    private function cleanAuthors(string $authors): string
    {
        $authors = str_replace("\n", ' ', $authors);
        $authors = preg_replace('/\s+/', ' ', $authors);
        $authors = preg_replace('/^\s*:\s*/', '', $authors);
        $authors = trim((string) $authors);
        return rtrim($authors, " .;\t");
    }

    private function uniqueOutputPath(string $outputDir, string $no, string $title): string
    {
        $baseTitle = Str::of($title)->limit(160, '');
        $slug = Str::slug((string) $baseTitle, '-');
        if ($slug === '') {
            $slug = 'untitled';
        }

        $prefix = trim($no) !== '' && is_numeric($no) ? str_pad((string) ((int) $no), 3, '0', STR_PAD_LEFT) : '000';
        $filename = "{$prefix}-{$slug}.txt";
        $path = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $i = 2;
        while (File::exists($path)) {
            $path = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "{$prefix}-{$slug}-{$i}.txt";
            $i++;
        }

        return $path;
    }

    private function renderArticle(array $d): string
    {
        $title = $d['title'];
        $journal = $d['journal'];
        $quartile = $d['quartile'] !== '' ? " ({$d['quartile']})" : '';

        $publishDate = $d['publish_date'] !== '' ? $d['publish_date'] : 'tahun 2026';
        $authors = $d['authors'] !== '' ? $d['authors'] : 'tim peneliti';

        $dosenTerl = $d['dosen_terlibat'] !== '' ? $d['dosen_terlibat'] : null;
        $dosenSentence = $dosenTerl !== null
            ? " Publikasi ini melibatkan {$dosenTerl} dosen."
            : '';

        $indexingSentence = $d['indexing'] !== ''
            ? " Artikel ini terindeks pada {$d['indexing']}."
            : '';

        $lead = "Staf Departemen Kardiologi dan Kedokteran Vaskular Fakultas Kedokteran, Kesehatan Masyarakat, dan Keperawatan (FK-KMK) Universitas Gadjah Mada (UGM) bersama tim peneliti memublikasikan artikel ilmiah berjudul “{$title}” di jurnal {$journal}{$quartile}, terbit {$publishDate}. Artikel ini ditulis oleh {$authors}.{$dosenSentence}{$indexingSentence}";

        $p2 = "Publikasi ini menambah kontribusi riset di bidang kardiovaskular sekaligus memperkuat budaya publikasi ilmiah berbasis bukti dalam pengembangan layanan dan pendidikan.";
        $p3 = "Kolaborasi penulis lintas disiplin dan institusi pada penelitian ini menunjukkan komitmen untuk menghadirkan temuan yang relevan bagi penguatan praktik klinis dan pengembangan ilmu pengetahuan.";
        $p4 = "Informasi jurnal, penerbit, serta tautan rujukan disertakan agar pembaca dapat menelusuri artikel asli dan sumber pengindeksan yang tersedia.";

        $details = [];
        $details[] = "Rincian publikasi:";
        $details[] = "- Judul: {$title}";
        if ($d['authors'] !== '') {
            $details[] = "- Penulis: {$d['authors']}";
        }
        if ($d['journal'] !== '') {
            $details[] = "- Jurnal: {$d['journal']}" . ($d['quartile'] !== '' ? " ({$d['quartile']})" : '');
        }
        if ($d['vol_no'] !== '') {
            $details[] = "- Vol/No: {$d['vol_no']}";
        }
        if ($d['pages'] !== '') {
            $details[] = "- Halaman: {$d['pages']}";
        }
        if ($d['publisher'] !== '') {
            $details[] = "- Penerbit: {$d['publisher']}";
        }
        if ($d['indexing'] !== '') {
            $details[] = "- Indeks: {$d['indexing']}";
        }
        if ($d['publish_date'] !== '') {
            $details[] = "- Terbit: {$d['publish_date']}";
        }
        if ($d['dosen_terlibat'] !== '') {
            $details[] = "- Jumlah dosen terlibat: {$d['dosen_terlibat']}";
        }
        if ($d['notes'] !== '') {
            $details[] = "- Keterangan: {$d['notes']}";
        }

        $links = array_values(array_filter([$d['link_1'], $d['link_2']], fn ($v) => trim((string) $v) !== ''));
        if (!empty($links)) {
            $details[] = "- Tautan:";
            foreach ($links as $link) {
                $details[] = "  - {$link}";
            }
        }

        return implode("\n\n", [
            $title,
            $lead,
            $p2,
            $p3,
            $p4,
            implode("\n", $details),
        ]) . "\n";
    }
}
