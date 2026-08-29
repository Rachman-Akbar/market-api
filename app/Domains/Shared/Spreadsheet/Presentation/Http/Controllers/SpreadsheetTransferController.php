<?php

declare(strict_types=1);

namespace App\Domains\Shared\Spreadsheet\Presentation\Http\Controllers;

use App\Domains\Catalog\CatalogGroup\Infrastructure\Persistence\Models\CatalogGroupModel;
use App\Domains\Catalog\Category\Infrastructure\Persistence\Models\CategoryModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductImageModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductModel;
use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductVariantModel;
use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use App\Domains\Shared\Spreadsheet\Application\SpreadsheetModuleRegistry;
use App\Domains\Shared\Spreadsheet\Application\Services\AdvancedSpreadsheetTransferService;
use App\Http\Controllers\Controller;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class SpreadsheetTransferController extends Controller
{
    private array $createdProductIds = [];

    public function __construct(private AdvancedSpreadsheetTransferService $advancedTransfer) {}
    public function template(Request $request, string $module): BinaryFileResponse|JsonResponse
    {
        try {
            $config = $this->authorizeModule($request, $module);
            if (($config['import_enabled'] ?? true) !== true) {
                throw new InvalidArgumentException('Modul ini hanya mendukung export dan tidak menyediakan template import.');
            }
            $spreadsheet = $this->createTemplateWorkbook($config);
            return $this->downloadSpreadsheet($spreadsheet, Str::slug($config['label']).'-template.xlsx');
        } catch (Throwable $exception) {
            return $this->error($exception);
        }
    }

    public function export(Request $request, string $module): BinaryFileResponse|JsonResponse
    {
        try {
            $config = $this->authorizeModule($request, $module);
            $ids = collect($request->input('ids', []))->filter(fn (mixed $id): bool => is_numeric($id))->map(fn (mixed $id): int => (int) $id)->unique()->values();
            $query = $this->scopedQuery($request, $config, $module);

            if ($ids->isNotEmpty()) {
                $query->whereIn('id', $ids->all());
            }

            $rows = $query->orderBy('id')->get();
            $spreadsheet = $this->createExportWorkbook($config, $module, $rows);
            return $this->downloadSpreadsheet($spreadsheet, Str::slug($config['label']).'-export-'.now()->format('Ymd-His').'.xlsx');
        } catch (Throwable $exception) {
            return $this->error($exception);
        }
    }

    public function previewImport(Request $request, string $module): JsonResponse
    {
        $spreadsheet = null;

        try {
            $this->prepareSpreadsheetRequest(180);
            $config = $this->authorizeModule($request, $module);
            if (($config['import_enabled'] ?? true) !== true) {
                throw new InvalidArgumentException('Modul ini hanya mendukung export karena datanya merupakan hasil transaksi atau histori audit.');
            }
            $validated = $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
                'import_mode' => ['required', 'in:create,update'],
            ]);
            $request->attributes->set('import_mode', $validated['import_mode']);
            $spreadsheet = $this->loadImportSpreadsheet($request->file('file'), true);
            $sheet = $spreadsheet->getSheetByName('Template Kosong') ?: $spreadsheet->getSheet(0);
            $headerRow = $this->findHeaderRow($sheet, $config['headers']);
            $headers = $this->sheetHeaders($sheet, $headerRow);
            $this->assertHeaders($headers, $config['headers']);
            $highestRow = $sheet->getHighestDataRow();
            $rows = [];

            for ($rowNumber = $headerRow + 1; $rowNumber <= $highestRow; $rowNumber++) {
                $row = $this->readRow($sheet, $headers, $rowNumber);
                if (! $this->rowIsEmpty($row)) {
                    $rows[] = ['row_number' => $rowNumber, 'data' => $row];
                }
            }

            $this->assertImportRows($request, $module, $rows);
            $analysis = $this->analyzeMissingRelations($request, $module, $rows);

            return response()->json([
                'success' => true,
                'message' => $analysis['requires_confirmation']
                    ? 'Ditemukan relasi yang belum tersedia. Pilih Lanjutkan atau Batal melalui antrean.'
                    : 'File siap diimport.',
                'data' => [
                    'total_rows' => count($rows),
                    ...$analysis,
                ],
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception);
        } finally {
            if ($spreadsheet instanceof Spreadsheet) {
                $spreadsheet->disconnectWorksheets();
            }
            unset($spreadsheet);
        }
    }

    public function import(Request $request, string $module): JsonResponse|BinaryFileResponse
    {
        $spreadsheet = null;

        try {
            $this->prepareSpreadsheetRequest(600);
            $config = $this->authorizeModule($request, $module);
            if (($config['import_enabled'] ?? true) !== true) {
                throw new InvalidArgumentException('Modul ini hanya mendukung export karena datanya merupakan hasil transaksi atau histori audit.');
            }
            $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
                'import_mode' => ['required', 'in:create,update'],
                'create_missing_relations' => ['sometimes', 'boolean'],
            ]);
            $request->attributes->set('import_mode', (string) $request->input('import_mode'));
            $request->attributes->set('create_missing_relations', $request->boolean('create_missing_relations'));
            $this->createdProductIds = [];
            $this->advancedTransfer->reset();
            $spreadsheet = $this->loadImportSpreadsheet($request->file('file'), false);
            $sheet = $spreadsheet->getSheetByName('Template Kosong') ?: $spreadsheet->getSheet(0);
            $headerRow = $this->findHeaderRow($sheet, $config['headers']);
            $headers = $this->sheetHeaders($sheet, $headerRow);
            $this->assertHeaders($headers, $config['headers']);
            $embeddedImages = $this->extractEmbeddedImages($sheet, $headers, $config, $module);
            $successful = 0;
            $errors = [];
            $highestRow = $sheet->getHighestDataRow();
            $rows = [];

            for ($rowNumber = $headerRow + 1; $rowNumber <= $highestRow; $rowNumber++) {
                $row = $this->readRow($sheet, $headers, $rowNumber);

                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                foreach ($embeddedImages[$rowNumber] ?? [] as $field => $path) {
                    if (empty($row[$field])) {
                        $row[$field] = $path;
                    }
                }

                $rows[] = ['row_number' => $rowNumber, 'data' => $row];
            }

            $this->assertImportRows($request, $module, $rows);

            if ($module === 'order') {
                $groups = collect($rows)->groupBy(function (array $item): string {
                    $orderNumber = trim((string) ($item['data']['order_number'] ?? ''));
                    return $orderNumber !== '' ? $orderNumber : '__row_'.$item['row_number'];
                });

                foreach ($groups as $group) {
                    $this->advancedTransfer->reset();
                    try {
                        DB::transaction(function () use ($request, $module, $group): void {
                            foreach ($group as $item) {
                                $this->persistRow($request, $module, $item['data']);
                            }
                        });
                        $successful += $group->count();
                    } catch (Throwable $exception) {
                        foreach ($group as $item) {
                            $errors[] = [...$item['data'], 'error_message' => $exception->getMessage()];
                        }
                    }
                }
            } else {
                foreach ($rows as $item) {
                    $row = $item['data'];
                    try {
                        DB::transaction(fn () => $this->persistRow($request, $module, $row));
                        $successful++;
                    } catch (Throwable $exception) {
                        $errors[] = [...$row, 'error_message' => $exception->getMessage()];
                    }
                }
            }

            if ($errors !== []) {
                $errorWorkbook = $this->createErrorWorkbook($config, $errors);
                $response = $this->downloadSpreadsheet($errorWorkbook, Str::slug($config['label']).'-import-error-'.now()->format('Ymd-His').'.xlsx');
                $response->headers->set('X-Import-Success-Count', (string) $successful);
                $response->headers->set('X-Import-Error-Count', (string) count($errors));
                return $response;
            }

            return response()->json([
                'success' => true,
                'message' => "{$successful} data berhasil diimport.",
                'data' => ['success_count' => $successful, 'error_count' => 0],
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception);
        } finally {
            if ($spreadsheet instanceof Spreadsheet) {
                $spreadsheet->disconnectWorksheets();
            }
            unset($spreadsheet);
        }
    }

    public function bulkDelete(Request $request, string $module): JsonResponse
    {
        try {
            $config = $this->authorizeModule($request, $module);
            if (($config['bulk_delete_enabled'] ?? true) !== true) {
                throw new InvalidArgumentException('Modul ini tidak mendukung bulk delete karena data bersifat histori, audit, atau relasi perhitungan.');
            }
            if (in_array($module, ['stock', 'raw-material-stock'], true)) {
                throw new InvalidArgumentException('Riwayat stok tidak dapat dihapus melalui bulk delete. Gunakan movement koreksi agar audit trail tetap utuh.');
            }
            if ($module === 'order' && $this->activeRole($request) === 'seller') {
                throw new InvalidArgumentException('Seller tidak dapat menghapus order melalui bulk delete.');
            }
            $data = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']]);
            $query = $this->scopedQuery($request, $config, $module)->whereIn('id', $data['ids']);
            $rows = $query->get();

            DB::transaction(function () use ($rows): void {
                foreach ($rows as $row) {
                    $row->delete();
                }
            });

            return response()->json([
                'success' => true,
                'message' => $rows->count().' data berhasil dihapus.',
                'data' => ['deleted_count' => $rows->count()],
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception);
        }
    }

    private function prepareSpreadsheetRequest(int $seconds): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit($seconds);
        }

        @ini_set('max_execution_time', (string) $seconds);
    }

    private function loadImportSpreadsheet(?UploadedFile $file, bool $readDataOnly): Spreadsheet
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            $error = $file?->getErrorMessage() ?: 'File Excel tidak diterima oleh server.';
            throw new InvalidArgumentException($error.' Periksa upload_max_filesize dan post_max_size pada php.ini.');
        }

        $path = $file->getRealPath();

        if (! is_string($path) || $path === '' || ! is_file($path)) {
            throw new InvalidArgumentException('File Excel sementara tidak ditemukan oleh server. Silakan pilih file kembali.');
        }

        $reader = IOFactory::createReaderForFile($path);

        if ($readDataOnly && method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        if (method_exists($reader, 'listWorksheetNames') && method_exists($reader, 'setLoadSheetsOnly')) {
            $sheetNames = $reader->listWorksheetNames($path);
            $sheetName = in_array('Template Kosong', $sheetNames, true)
                ? 'Template Kosong'
                : ($sheetNames[0] ?? null);

            if (is_string($sheetName) && $sheetName !== '') {
                $reader->setLoadSheetsOnly([$sheetName]);
            }
        }

        return $reader->load($path);
    }

    private function authorizeModule(Request $request, string $module): array
    {
        if (! class_exists(Spreadsheet::class)) {
            throw new InvalidArgumentException('Package phpoffice/phpspreadsheet belum terpasang. Jalankan composer require phpoffice/phpspreadsheet.');
        }

        if (! class_exists(\ZipArchive::class)) {
            throw new InvalidArgumentException('Ekstensi PHP zip belum aktif. Aktifkan extension=zip pada php.ini lalu restart Laravel.');
        }

        if (! class_exists(\XMLWriter::class)) {
            throw new InvalidArgumentException('Ekstensi PHP xmlwriter belum aktif. Aktifkan extension=xmlwriter pada php.ini lalu restart Laravel.');
        }

        $config = SpreadsheetModuleRegistry::get($module);
        SpreadsheetModuleRegistry::assertRoleAllowed($config, $this->activeRole($request));
        return $config;
    }

    private function scopedQuery(Request $request, array $config, string $module): Builder
    {
        if ($this->advancedTransfer->supports($module)) {
            return $this->advancedTransfer->scopedQuery($request, $module);
        }

        $query = SpreadsheetModuleRegistry::model($config)->newQuery();
        $role = $this->activeRole($request);

        if ($role === 'seller' && in_array($module, ['product', 'promotion', 'voucher', 'banner'], true)) {
            $query->where('store_id', $this->sellerStoreId($request));
        }

        return $query;
    }

    private function createTemplateWorkbook(array $config): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $columnCount = count($config['headers']);
        $lastColumn = Coordinate::stringFromColumnIndex(max(1, $columnCount));
        $guideLastColumn = $lastColumn;

        $empty = $spreadsheet->getActiveSheet();
        $empty->setTitle('Template Kosong');
        $this->writeHeader($empty, $config['headers'], 1);
        $this->formatSheet($empty, $columnCount, 1);

        $example = $spreadsheet->createSheet();
        $example->setTitle('Contoh Kasus Import');
        $exampleRows = array_values($config['examples'] ?? []);
        if (count($exampleRows) !== 10) {
            throw new InvalidArgumentException('Setiap modul wajib memiliki tepat 10 contoh kasus import.');
        }

        $example->mergeCells('A1:'.$guideLastColumn.'1');
        $example->setCellValue('A1', '10 CONTOH KASUS IMPORT '.strtoupper($config['label']));
        $example->mergeCells('A2:'.$guideLastColumn.'2');
        $example->setCellValue('A2', 'Setiap kasus dijelaskan dalam blok merge rata kiri, kemudian diikuti satu atau beberapa baris contoh data yang dapat disalin ke sheet Template Kosong.');
        $this->styleSheetTitle($example, $guideLastColumn, 'FF1D4ED8', true);

        $currentRow = 4;
        foreach ($exampleRows as $rowIndex => $row) {
            $metadata = $this->exampleMetadata($row, $rowIndex + 1);
            $caseNumber = str_pad((string) ($rowIndex + 1), 2, '0', STR_PAD_LEFT);
            $dataRows = array_values($row['data_rows'] ?? [$row]);

            $example->mergeCells('A'.$currentRow.':'.$guideLastColumn.$currentRow);
            $example->setCellValue('A'.$currentRow, 'KASUS '.$caseNumber.' — '.strtoupper($metadata['jenis_kasus']));
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF047857');
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FFFFFFFF');
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF065F46');
            $example->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;

            $detailRows = [
                ['SKENARIO', $metadata['skenario'], 'FFE0F2FE', 'FF0C4A6E'],
                ['CARA PENGISIAN', $metadata['cara_pengisian'], 'FFECFDF5', 'FF065F46'],
                ['VALIDASI SISTEM', $metadata['validasi_sistem'], 'FFFFF7ED', 'FF9A3412'],
                ['HASIL YANG DIHARAPKAN', $metadata['hasil_diharapkan'], 'FFF0FDF4', 'FF166534'],
                ['TINDAKAN PENGGUNA', $metadata['tindakan_pengguna'], 'FFF5F3FF', 'FF6D28D9'],
                ['CATATAN PENTING', $metadata['catatan_penting'], 'FFFFFBEB', 'FF92400E'],
            ];

            foreach ($detailRows as [$label, $value, $fillColor, $fontColor]) {
                $example->mergeCells('A'.$currentRow.':'.$guideLastColumn.$currentRow);
                $example->setCellValue('A'.$currentRow, $label);
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fillColor);
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFont()->setBold(true)->getColor()->setARGB($fontColor);
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
                $example->getRowDimension($currentRow)->setRowHeight(18);
                $currentRow++;

                $example->mergeCells('A'.$currentRow.':'.$guideLastColumn.$currentRow);
                $example->setCellValue('A'.$currentRow, $value);
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFont()->getColor()->setARGB('FF334155');
                $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
                $example->getRowDimension($currentRow)->setRowHeight(18);
                $currentRow++;
            }

            $example->mergeCells('A'.$currentRow.':'.$guideLastColumn.$currentRow);
            $example->setCellValue('A'.$currentRow, 'CONTOH DATA EXCEL — salin baris berikut ke Template Kosong');
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE');
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getFont()->setBold(true)->getColor()->setARGB('FF1E3A8A');
            $example->getStyle('A'.$currentRow.':'.$guideLastColumn.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $example->getRowDimension($currentRow)->setRowHeight(18);
            $currentRow++;

            $this->writeHeader($example, $config['headers'], $currentRow);
            $this->styleHeaderRow($example, $columnCount, $currentRow);
            $dataStartRow = $currentRow + 1;
            foreach ($dataRows as $dataIndex => $data) {
                $dataRow = $dataStartRow + $dataIndex;
                foreach ($config['headers'] as $columnIndex => $header) {
                    $example->setCellValue([$columnIndex + 1, $dataRow], $data[$header] ?? '');
                }
                $example->getStyle('A'.$dataRow.':'.$lastColumn.$dataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $example->getStyle('A'.$dataRow.':'.$lastColumn.$dataRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
                $example->getRowDimension($dataRow)->setRowHeight(18);
            }
            $currentRow = $dataStartRow + count($dataRows) + 2;
        }

        for ($column = 1; $column <= $columnCount; $column++) {
            $example->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(14);
        }
        $example->getColumnDimension('A')->setWidth(16);

        $explanation = $spreadsheet->createSheet();
        $explanation->setTitle('Penjelasan Kolom');

        $guideRows = array_values($config['guides'] ?? []);
        $guideTitleRow = 1;
        $explanation->setCellValue('A'.$guideTitleRow, 'PANDUAN MODUL');
        $explanation->getStyle('A'.$guideTitleRow.':F'.$guideTitleRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1D4ED8');
        $explanation->getStyle('A'.$guideTitleRow.':F'.$guideTitleRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');

        $guideHeaderRow = $guideTitleRow + 1;
        $this->writeHeader($explanation, ['Topik Panduan', 'Cara Pengisian dan Perilaku Sistem'], $guideHeaderRow);
        $this->styleHeaderRow($explanation, 6, $guideHeaderRow);

        $guideDataStart = $guideHeaderRow + 1;
        foreach ($guideRows as $index => $guide) {
            $rowNumber = $guideDataStart + $index;
            $explanation->setCellValue('A'.$rowNumber, $guide[0] ?? '');
            $explanation->setCellValue('B'.$rowNumber, $guide[1] ?? '');
            $explanation->getStyle('A'.$rowNumber.':F'.$rowNumber)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $explanation->getStyle('A'.$rowNumber.':F'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $explanation->getStyle('A'.$rowNumber)->getFont()->setBold(true)->getColor()->setARGB('FF1E40AF');
            $explanation->getRowDimension($rowNumber)->setRowHeight(18);
        }

        $columnTitleRow = $guideDataStart + count($guideRows) + 1;
        $explanation->setCellValue('A'.$columnTitleRow, 'PENJELASAN SETIAP KOLOM');
        $explanation->getStyle('A'.$columnTitleRow.':F'.$columnTitleRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F766E');
        $explanation->getStyle('A'.$columnTitleRow.':F'.$columnTitleRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');

        $descriptionHeaderRow = $columnTitleRow + 1;
        $columns = ['Kolom', 'Kewajiban', 'Format', 'Contoh', 'Aturan Validasi', 'Dampak / Catatan'];
        $this->writeHeader($explanation, $columns, $descriptionHeaderRow);
        $this->styleHeaderRow($explanation, 6, $descriptionHeaderRow);
        foreach ($config['descriptions'] as $index => $description) {
            foreach ($description as $column => $value) {
                $explanation->setCellValue([$column + 1, $descriptionHeaderRow + $index + 1], $value);
            }
        }

        $lastDescriptionRow = $descriptionHeaderRow + count($config['descriptions']);
        $explanation->getStyle('A'.($descriptionHeaderRow + 1).':F'.$lastDescriptionRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $explanation->getStyle('A'.$descriptionHeaderRow.':F'.$lastDescriptionRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $explanation->getColumnDimension('A')->setWidth(20);
        $explanation->getColumnDimension('B')->setWidth(24);
        $explanation->getColumnDimension('C')->setWidth(16);
        $explanation->getColumnDimension('D')->setWidth(20);
        $explanation->getColumnDimension('E')->setWidth(28);
        $explanation->getColumnDimension('F')->setWidth(28);

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    private function createExportWorkbook(array $config, string $module, iterable $models): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Export');
        $hasPreviewImage = ! empty($config['image_fields']);
        $headers = $hasPreviewImage ? [...$config['headers'], 'preview_image'] : $config['headers'];
        $this->writeHeader($sheet, $headers);
        $rowNumber = 2;

        $rows = $this->advancedTransfer->supports($module)
            ? $this->advancedTransfer->exportRows($module, $models)
            : collect($models)->map(fn (Model $model): array => $this->modelToRow($module, $model, $config['headers']))->all();

        foreach ($rows as $row) {
            foreach ($config['headers'] as $index => $header) {
                $sheet->setCellValue([$index + 1, $rowNumber], $row[$header] ?? '');
            }
            if ($hasPreviewImage) {
                $this->attachExportImage($sheet, $row, $config, $rowNumber, count($headers));
            }
            $rowNumber++;
        }

        $this->formatSheet($sheet, count($headers));
        if ($hasPreviewImage) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex(count($headers)))->setWidth(14);
        }
        return $spreadsheet;
    }

    private function createErrorWorkbook(array $config, array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Gagal Import');
        $headers = [...$config['headers'], 'error_message'];
        $this->writeHeader($sheet, $headers);

        foreach ($rows as $rowIndex => $row) {
            foreach ($headers as $columnIndex => $header) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 2], $row[$header] ?? '');
            }
        }

        $this->formatSheet($sheet, count($headers));
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle($lastColumn.'2:'.$lastColumn.$sheet->getHighestRow())->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE4E6');
        $sheet->getStyle($lastColumn.'2:'.$lastColumn.$sheet->getHighestRow())->getFont()->getColor()->setARGB('FFBE123C');
        $sheet->getColumnDimension($lastColumn)->setWidth(28);
        return $spreadsheet;
    }

    private function persistRow(Request $request, string $module, array $row): void
    {
        if ($this->advancedTransfer->supports($module)) {
            $this->advancedTransfer->persist($request, $module, $row);
            return;
        }

        match ($module) {
            'product' => $this->persistProduct($request, $row),
            'category' => $this->persistCategory($request, $row),
            'catalog-group' => $this->persistCatalogGroup($request, $row),
            'promotion' => $this->persistPromotion($request, $row),
            'voucher' => $this->persistVoucher($request, $row),
            'banner' => $this->persistBanner($request, $row),
            default => throw new InvalidArgumentException('Modul tidak didukung.'),
        };
    }

    private function persistProduct(Request $request, array $row): void
    {
        $allowCreate = $this->shouldCreateMissingRelations($request);
        $storeId = $this->activeRole($request) === 'seller'
            ? $this->sellerStoreId($request)
            : $this->resolveStoreId($row['store_name'] ?? null, true);
        $groupName = $this->cleanName($row['catalog_group_name'] ?? null);
        $primaryCategoryName = $this->cleanName($row['primary_category_name'] ?? null);
        $categoryNames = $this->splitNames($row['category_names'] ?? null);
        if ($primaryCategoryName !== '') {
            array_unshift($categoryNames, $primaryCategoryName);
        }
        $categoryNames = array_values(array_unique(array_filter($categoryNames)));
        $categoryIdsByName = [];

        foreach ($categoryNames as $categoryName) {
            $category = $this->resolveCategory($categoryName, $groupName, null, $allowCreate);
            $categoryIdsByName[$this->normalizedName($categoryName)] = (int) $category->id;
        }

        $primaryCategoryId = $primaryCategoryName !== ''
            ? ($categoryIdsByName[$this->normalizedName($primaryCategoryName)] ?? null)
            : (array_values($categoryIdsByName)[0] ?? null);
        $name = $this->cleanName($row['name'] ?? null);
        $data = [
            'store_id' => $storeId,
            'primary_category_id' => $primaryCategoryId,
            'name' => $name,
            'slug' => $this->cleanName($row['slug'] ?? null) ?: Str::slug($name),
            'description' => $this->nullableString($row['description'] ?? null),
            'brand' => $this->nullableString($row['brand'] ?? null),
            'thumbnail' => $this->normalizeImportedImage($row['thumbnail'] ?? null, 'products'),
            'status' => strtolower($this->cleanName($row['status'] ?? 'draft')),
            'is_active' => $this->boolValue($row['is_active'] ?? true),
        ];

        Validator::make($data, [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'primary_category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_active' => ['boolean'],
        ])->validate();

        $product = $this->resolveProductForImport($request, $storeId, $row, $name);
        $wasNewProduct = ! $product->exists;
        $product->fill($data)->save();

        if ($categoryIdsByName !== []) {
            $product->categories()->sync(collect($categoryIdsByName)->unique()->mapWithKeys(
                fn (int $id): array => [$id => ['is_primary' => $id === $primaryCategoryId]]
            )->all());
        }

        $galleryImage = $this->normalizeImportedImage($row['image_url'] ?? null, 'products');
        if ($galleryImage) {
            ProductImageModel::query()->updateOrCreate(
                ['product_id' => $product->id, 'url' => $galleryImage],
                ['alt_text' => $this->nullableString($row['image_alt'] ?? null) ?: $product->name, 'is_primary' => true, 'sort_order' => 0]
            );
        }

        if ($this->hasAnyValue($row, ['sku', 'variant_name', 'price'])) {
            $variantName = $this->cleanName($row['variant_name'] ?? null) ?: 'Default';
            $sku = $this->nullableString($row['sku'] ?? null);
            $price = $this->nullableFloat($row['price'] ?? null);
            Validator::make([
                'variant_name' => $variantName,
                'sku' => $sku,
                'price' => $price,
            ], [
                'variant_name' => ['required', 'string', 'max:255'],
                'sku' => ['nullable', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
            ])->validate();

            $variantQuery = ProductVariantModel::query()->where('product_id', $product->id);
            $mode = $this->importMode($request);
            $variant = null;

            if ($sku !== null) {
                $skuMatch = ProductVariantModel::query()
                    ->where('store_id', $storeId)
                    ->whereRaw('LOWER(TRIM(sku)) = ?', [$this->normalizedName($sku)])
                    ->first();

                if ($skuMatch && (int) $skuMatch->product_id !== (int) $product->id) {
                    throw new InvalidArgumentException('SKU sudah digunakan oleh Product lain pada toko yang sama: '.$sku);
                }
                if ($mode === 'create' && $skuMatch) {
                    throw new InvalidArgumentException('SKU sudah tersedia dan tidak boleh dipakai pada mode Import Data Baru: '.$sku);
                }
                $variant = $skuMatch;
            } elseif ($mode === 'update') {
                $variant = $this->firstByNormalizedName(clone $variantQuery, 'name', $variantName);
                $sku = $variant?->sku;
            }

            $variant ??= new ProductVariantModel();
            $sku ??= $this->generateUniqueSku($storeId, $name, (string) ($data['brand'] ?? ''), $variantName);

            $hasExistingDefault = (clone $variantQuery)
                ->when($variant->exists, fn (Builder $query) => $query->where($variant->getQualifiedKeyName(), '!=', $variant->getKey()))
                ->where('is_default', true)
                ->exists();
            $isDefault = $this->hasAnyValue($row, ['is_default'])
                ? $this->boolValue($row['is_default'])
                : ! $hasExistingDefault;

            if ($isDefault) {
                (clone $variantQuery)
                    ->when($variant->exists, fn (Builder $query) => $query->where($variant->getQualifiedKeyName(), '!=', $variant->getKey()))
                    ->update(['is_default' => false]);
            }

            $variant->fill([
                'product_id' => $product->id,
                'store_id' => $storeId,
                'sku' => $sku,
                'name' => $variantName,
                'price' => $price,
                'stock' => $variant->exists ? (int) $variant->stock : 0,
                'is_default' => $isDefault,
            ])->save();
        }

        if ($wasNewProduct && $this->importMode($request) === 'create') {
            $this->createdProductIds[$this->productImportKey($storeId, $name)] = (int) $product->id;
        }
    }

    private function persistCategory(Request $request, array $row): void
    {
        $allowCreate = $this->shouldCreateMissingRelations($request);
        $groupName = $this->cleanName($row['catalog_group_name'] ?? null);
        $group = $this->resolveCatalogGroup($groupName, $allowCreate);
        $parentName = $this->cleanName($row['parent_category_name'] ?? null);
        $parent = $parentName !== '' ? $this->resolveCategory($parentName, $groupName, null, $allowCreate) : null;
        $name = $this->cleanName($row['name'] ?? null);
        $slug = $this->cleanName($row['slug'] ?? null) ?: Str::slug($name);
        $level = $parent ? ((int) $parent->level + 1) : 1;

        if ($level > 3) {
            throw new InvalidArgumentException('Kategori hanya boleh sampai level 3.');
        }

        Validator::make([
            'catalog_group_id' => $group->id,
            'parent_id' => $parent?->id,
            'name' => $name,
        ], [
            'catalog_group_id' => ['required', 'integer', 'exists:catalog_groups,id'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        $query = CategoryModel::query()
            ->where('catalog_group_id', $group->id)
            ->where('parent_scope_id', $parent?->id ?: 0);
        $model = $this->resolveImportModel($request, $query, $row, 'name', $name, 'Category');
        $model->fill([
            'catalog_group_id' => $group->id,
            'parent_id' => $parent?->id,
            'parent_scope_id' => $parent?->id ?: 0,
            'level' => $level,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => $this->boolValue($row['is_active'] ?? true),
            'is_visible_in_menu' => $this->boolValue($row['is_visible_in_menu'] ?? true),
            'name' => $name,
            'slug' => $slug,
            'full_slug' => $parent ? trim($parent->full_slug.'/'.$slug, '/') : $slug,
            'image_url' => $this->normalizeImportedImage($row['image_url'] ?? null, 'categories'),
            'icon_url' => $this->normalizeImportedImage($row['icon_url'] ?? null, 'categories'),
        ])->save();
    }

    private function persistCatalogGroup(Request $request, array $row): void
    {
        $name = $this->cleanName($row['name'] ?? null);
        Validator::make(['name' => $name], ['name' => ['required', 'string', 'max:255']])->validate();
        $model = $this->resolveImportModel($request, CatalogGroupModel::query(), $row, 'name', $name, 'Catalog Group');
        $model->fill([
            'name' => $name,
            'slug' => $this->cleanName($row['slug'] ?? null) ?: $this->uniqueSlug(CatalogGroupModel::query(), Str::slug($name), $model->exists ? (int) $model->id : null),
            'is_active' => $this->boolValue($row['is_active'] ?? true),
        ])->save();
    }

    private function persistPromotion(Request $request, array $row): void
    {
        $role = $this->activeRole($request);
        $allowCreate = $this->shouldCreateMissingRelations($request);
        $storeId = $role === 'seller'
            ? $this->sellerStoreId($request)
            : $this->resolveStoreId($row['store_name'] ?? null, false);
        $name = $this->cleanName($row['name'] ?? null);
        $image = $this->normalizeImportedImage($row['image_url'] ?? null, 'promotions');
        $clickAction = strtolower($this->cleanName($row['click_action'] ?? 'none'));
        $targetName = $this->cleanName($row['target_name'] ?? null);
        $targetId = null;

        if ($clickAction === 'product') {
            $targetQuery = ProductModel::query();
            if ($storeId) {
                $targetQuery->where('store_id', $storeId);
            }
            $matches = $this->allByNormalizedName($targetQuery, 'name', $targetName);
            if ($matches->count() !== 1) {
                throw new InvalidArgumentException($matches->isEmpty()
                    ? 'Product target tidak ditemukan: '.$targetName
                    : 'Product target tidak unik. Gunakan nama yang lebih spesifik: '.$targetName);
            }
            $targetId = (int) $matches->first()->id;
        }

        if ($clickAction === 'category') {
            $targetGroupName = $this->cleanName($row['target_catalog_group_name'] ?? null);
            $targetId = (int) $this->resolveCategory($targetName, $targetGroupName, null, $allowCreate)->id;
        }

        Validator::make([
            'name' => $name,
            'image_url' => $image,
            'click_action' => $clickAction,
            'target_id' => $targetId,
            'target_url' => $this->nullableString($row['target_url'] ?? null),
        ], [
            'name' => ['required', 'string', 'max:150'],
            'image_url' => ['required', 'string', 'max:2048'],
            'click_action' => ['required', 'in:none,product,category,url'],
            'target_id' => ['required_if:click_action,product,category', 'nullable', 'integer'],
            'target_url' => ['required_if:click_action,url', 'nullable', 'url'],
        ])->validate();

        $query = \App\Domains\Catalog\Promotion\Infrastructure\Persistence\Models\PromotionModel::query();
        if ($storeId === null) {
            $query->whereNull('store_id');
        } else {
            $query->where('store_id', $storeId);
        }
        $model = $this->resolveImportModel($request, $query, $row, 'name', $name, 'Promotion');
        $model->fill([
            'store_id' => $storeId,
            'name' => $name,
            'image_url' => $image,
            'mobile_image_url' => $this->normalizeImportedImage($row['mobile_image_url'] ?? null, 'promotions'),
            'click_action' => $clickAction,
            'target_id' => $targetId,
            'target_url' => $this->nullableString($row['target_url'] ?? null),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => $this->boolValue($row['is_active'] ?? true),
            'approval_status' => $role === 'seller' ? 'pending' : strtolower($this->cleanName($row['approval_status'] ?? 'pending')),
            'submitted_at' => now(),
        ])->save();
    }

    private function persistVoucher(Request $request, array $row): void
    {
        // TODO(unify): voucher rules are duplicated across the Voucher domain entity,
        // CreateOrderUseCase::calculateVoucher, and this importer. Not unified yet because
        // this subset of imported rows overlaps with the (invalid) full set handled by
        // StoreVoucherRequest/ManageVoucherUseCase, which validates the whole input as a
        // mandatory create (unique code/name + used_count=0) and would break this importer's
        // find-by-code upsert semantics. Kept EXACTLY as-is to avoid regressions.
        $role = $this->activeRole($request);
        $scope = $role === 'seller' ? 'store' : strtolower($this->cleanName($row['voucher_scope'] ?? 'platform'));
        $storeId = $role === 'seller'
            ? $this->sellerStoreId($request)
            : ($scope === 'store' ? $this->resolveStoreId($row['store_name'] ?? null, true) : null);
        $payload = [
            'store_id' => $scope === 'store' ? $storeId : null,
            'voucher_scope' => $scope,
            'code' => strtoupper($this->cleanName($row['code'] ?? null)),
            'name' => $this->cleanName($row['name'] ?? null),
            'image' => $this->normalizeImportedImage($row['image'] ?? null, 'vouchers'),
            'discount_target' => strtolower($this->cleanName($row['discount_target'] ?? 'product')),
            'discount_type' => strtolower($this->cleanName($row['discount_type'] ?? 'fixed')),
            'discount_value' => (float) ($row['discount_value'] ?? 0),
            'min_spend' => (float) ($row['min_spend'] ?? 0),
            'max_discount' => $this->nullableFloat($row['max_discount'] ?? null),
            'starts_at' => $this->dateValue($row['starts_at'] ?? null),
            'ends_at' => $this->dateValue($row['ends_at'] ?? null),
            'usage_limit' => (int) ($row['usage_limit'] ?? 0),
            'is_active' => $this->boolValue($row['is_active'] ?? true),
        ];

        Validator::make($payload, [
            'voucher_scope' => ['required', 'in:platform,store'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'discount_target' => ['required', 'in:product,shipping'],
            'discount_type' => ['required', 'in:fixed,percentage'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'min_spend' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'usage_limit' => ['required', 'integer', 'min:0'],
        ])->validate();

        $query = Voucher::query();
        if ($payload['store_id'] === null) {
            $query->whereNull('store_id');
        } else {
            $query->where('store_id', $payload['store_id']);
        }
        $model = $this->resolveImportModel($request, $query, $row, 'code', $payload['code'], 'Voucher');
        $model->fill($payload)->save();
    }

    private function persistBanner(Request $request, array $row): void
    {
        $storeId = $this->activeRole($request) === 'seller'
            ? $this->sellerStoreId($request)
            : $this->resolveStoreId($row['store_name'] ?? null, true);
        $name = $this->cleanName($row['name'] ?? null);
        $image = $this->normalizeImportedImage($row['image_url'] ?? null, 'banners');
        Validator::make(['store_id' => $storeId, 'name' => $name, 'image_url' => $image], [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:150'],
            'image_url' => ['required', 'string', 'max:2048'],
        ])->validate();
        $model = $this->resolveImportModel(
            $request,
            \App\Domains\Catalog\Banner\Infrastructure\Persistence\Models\BannerModel::query()->where('store_id', $storeId),
            $row,
            'name',
            $name,
            'Banner'
        );
        $model->fill([
            'store_id' => $storeId,
            'name' => $name,
            'image_url' => $image,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => $this->boolValue($row['is_active'] ?? true),
        ])->save();
    }

    private function findModel(Builder $query, array $row, array $fallback): Model
    {
        if (is_numeric($row['id'] ?? null)) {
            return $query->findOrFail((int) $row['id']);
        }

        foreach ($fallback as $column => $value) {
            if (is_string($value)) {
                $query->whereRaw('LOWER(TRIM('.$column.')) = ?', [$this->normalizedName($value)]);
            } elseif ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        return $query->first() ?: $query->getModel()->newInstance();
    }

    private function modelToRow(string $module, Model $model, array $headers): array
    {
        $attributes = $model->getAttributes();
        $row = array_fill_keys($headers, '');
        foreach ($headers as $header) {
            $value = $attributes[$header] ?? null;
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $row[$header] = is_bool($value) ? ($value ? 1 : 0) : ($value ?? '');
        }

        if (array_key_exists('store_name', $row)) {
            $row['store_name'] = $model->getAttribute('store_id')
                ? (StoreModel::query()->find($model->getAttribute('store_id'))?->name ?: '')
                : '';
        }

        if ($module === 'product') {
            $model->loadMissing(['categories', 'variants', 'images']);
            $variant = $model->variants->sortByDesc('is_default')->first();
            $image = $model->images->sortByDesc('is_primary')->first();
            $primary = $model->categories->firstWhere('id', (int) $model->primary_category_id) ?: $model->categories->first();
            $group = $primary ? CatalogGroupModel::query()->find($primary->catalog_group_id) : null;
            $row['catalog_group_name'] = $group?->name ?: '';
            $row['primary_category_name'] = $primary?->name ?: '';
            $row['category_names'] = $model->categories->pluck('name')->filter()->implode(', ');
            $row['image_url'] = $image?->url ?: '';
            $row['image_alt'] = $image?->alt_text ?: '';
            $row['sku'] = $variant?->sku ?: '';
            $row['variant_name'] = $variant?->name ?: '';
            $row['price'] = $variant?->price ?: '';
            $row['is_default'] = $variant ? ($variant->is_default ? 1 : 0) : '';
        }

        if ($module === 'category') {
            $row['catalog_group_name'] = CatalogGroupModel::query()->find($model->catalog_group_id)?->name ?: '';
            $row['parent_category_name'] = $model->parent_id ? (CategoryModel::query()->find($model->parent_id)?->name ?: '') : '';
        }

        if ($module === 'promotion') {
            $action = strtolower((string) $model->click_action);
            if ($action === 'product') {
                $row['target_name'] = ProductModel::query()->find($model->target_id)?->name ?: '';
            } elseif ($action === 'category') {
                $category = CategoryModel::query()->find($model->target_id);
                $row['target_name'] = $category?->name ?: '';
                $row['target_catalog_group_name'] = $category ? (CatalogGroupModel::query()->find($category->catalog_group_id)?->name ?: '') : '';
            }
        }

        return $row;
    }

    private function analyzeMissingRelations(Request $request, string $module, array $rows): array
    {
        if ($this->advancedTransfer->supports($module)) {
            return $this->advancedTransfer->analyzeMissingRelations($request, $module, $rows);
        }

        $missing = [];
        $add = function (string $type, string $name, int $rowNumber, bool $canAutoCreate, string $context = '') use (&$missing): void {
            $displayName = $this->cleanName($name);
            if ($displayName === '') {
                return;
            }
            $key = $type.'|'.$this->normalizedName($displayName).'|'.$this->normalizedName($context);
            if (! isset($missing[$key])) {
                $missing[$key] = [
                    'type' => $type,
                    'name' => $displayName,
                    'context' => $context,
                    'row_numbers' => [],
                    'can_auto_create' => $canAutoCreate,
                ];
            }
            $missing[$key]['row_numbers'][] = $rowNumber;
        };

        foreach ($rows as $entry) {
            $rowNumber = (int) $entry['row_number'];
            $row = $entry['data'];
            $role = $this->activeRole($request);

            if (in_array($module, ['product', 'promotion', 'voucher', 'banner'], true) && $role !== 'seller') {
                $storeName = $this->cleanName($row['store_name'] ?? null);
                $storeRequired = in_array($module, ['product', 'banner'], true)
                    || ($module === 'voucher' && strtolower($this->cleanName($row['voucher_scope'] ?? 'platform')) === 'store');
                if ($storeName !== '' && ! $this->firstByNormalizedName(StoreModel::query(), 'name', $storeName)) {
                    $add('store', $storeName, $rowNumber, false);
                } elseif ($storeRequired && $storeName === '') {
                    $add('store', 'Nama toko wajib diisi', $rowNumber, false);
                }
            }

            if ($module === 'product') {
                $groupName = $this->cleanName($row['catalog_group_name'] ?? null);
                if ($groupName !== '' && ! $this->firstByNormalizedName(CatalogGroupModel::query(), 'name', $groupName)) {
                    $add('catalog_group', $groupName, $rowNumber, true);
                }
                $names = $this->splitNames($row['category_names'] ?? null);
                $primary = $this->cleanName($row['primary_category_name'] ?? null);
                if ($primary !== '') {
                    array_unshift($names, $primary);
                }
                foreach (array_unique($names) as $name) {
                    if (! $this->categoryExists($name, $groupName)) {
                        $add('category', $name, $rowNumber, $groupName !== '', $groupName);
                    }
                }
            }

            if ($module === 'category') {
                $groupName = $this->cleanName($row['catalog_group_name'] ?? null);
                if ($groupName !== '' && ! $this->firstByNormalizedName(CatalogGroupModel::query(), 'name', $groupName)) {
                    $add('catalog_group', $groupName, $rowNumber, true);
                }
                $parentName = $this->cleanName($row['parent_category_name'] ?? null);
                if ($parentName !== '' && ! $this->categoryExists($parentName, $groupName)) {
                    $add('category', $parentName, $rowNumber, $groupName !== '', $groupName);
                }
            }

            if ($module === 'promotion') {
                $action = strtolower($this->cleanName($row['click_action'] ?? 'none'));
                $targetName = $this->cleanName($row['target_name'] ?? null);
                if ($action === 'product' && $targetName !== '' && ! $this->firstByNormalizedName(ProductModel::query(), 'name', $targetName)) {
                    $add('product', $targetName, $rowNumber, false);
                }
                if ($action === 'category' && $targetName !== '') {
                    $groupName = $this->cleanName($row['target_catalog_group_name'] ?? null);
                    if ($groupName !== '' && ! $this->firstByNormalizedName(CatalogGroupModel::query(), 'name', $groupName)) {
                        $add('catalog_group', $groupName, $rowNumber, true);
                    }
                    if (! $this->categoryExists($targetName, $groupName)) {
                        $add('category', $targetName, $rowNumber, $groupName !== '', $groupName);
                    }
                }
            }
        }

        $rowsMissing = array_values(array_map(function (array $item): array {
            $item['row_numbers'] = array_values(array_unique($item['row_numbers']));
            return $item;
        }, $missing));
        $automatic = array_values(array_filter($rowsMissing, fn (array $item): bool => $item['can_auto_create']));
        $blocking = array_values(array_filter($rowsMissing, fn (array $item): bool => ! $item['can_auto_create']));

        return [
            'requires_confirmation' => $automatic !== [],
            'can_continue' => $blocking === [],
            'missing_relations' => $automatic,
            'blocking_relations' => $blocking,
        ];
    }

    private function shouldCreateMissingRelations(Request $request): bool
    {
        return (bool) $request->attributes->get('create_missing_relations', false);
    }

    private function resolveStoreId(mixed $value, bool $required): ?int
    {
        $name = $this->cleanName($value);
        if ($name === '') {
            if ($required) {
                throw new InvalidArgumentException('Nama toko wajib diisi.');
            }
            return null;
        }
        $matches = $this->allByNormalizedName(StoreModel::query(), 'name', $name);
        if ($matches->isEmpty()) {
            throw new InvalidArgumentException('Toko tidak ditemukan: '.$name);
        }
        if ($matches->count() > 1) {
            throw new InvalidArgumentException('Nama toko tidak unik: '.$name);
        }
        return (int) $matches->first()->id;
    }

    private function resolveCatalogGroup(string $name, bool $create): CatalogGroupModel
    {
        $displayName = $this->cleanName($name);
        if ($displayName === '') {
            throw new InvalidArgumentException('Nama Catalog Group wajib diisi.');
        }
        $group = $this->firstByNormalizedName(CatalogGroupModel::query(), 'name', $displayName);
        if ($group) {
            return $group;
        }
        if (! $create) {
            throw new InvalidArgumentException('Catalog Group belum tersedia: '.$displayName.'. Lakukan preview dan pilih Lanjutkan untuk membuatnya.');
        }
        $group = new CatalogGroupModel();
        $group->fill([
            'name' => $displayName,
            'slug' => $this->uniqueSlug(CatalogGroupModel::query(), Str::slug($displayName)),
            'is_active' => true,
        ])->save();
        return $group;
    }

    private function resolveCategory(string $name, string $groupName, ?CategoryModel $parent, bool $create): CategoryModel
    {
        $displayName = $this->cleanName($name);
        if ($displayName === '') {
            throw new InvalidArgumentException('Nama Category wajib diisi.');
        }
        $query = CategoryModel::query();
        $group = null;
        if ($groupName !== '') {
            $group = $this->firstByNormalizedName(CatalogGroupModel::query(), 'name', $groupName);
            if (! $group && $create) {
                $group = $this->resolveCatalogGroup($groupName, true);
            }
            if ($group) {
                $query->where('catalog_group_id', $group->id);
            }
        }
        if ($parent) {
            $query->where('parent_scope_id', $parent->id);
        }
        $matches = $this->allByNormalizedName($query, 'name', $displayName);
        if ($matches->count() === 1) {
            return $matches->first();
        }
        if ($matches->count() > 1) {
            throw new InvalidArgumentException('Nama Category tidak unik. Isi catalog_group_name yang lebih spesifik: '.$displayName);
        }
        if (! $create) {
            throw new InvalidArgumentException('Category belum tersedia: '.$displayName.'. Lakukan preview dan pilih Lanjutkan untuk membuatnya.');
        }
        if (! $group) {
            if ($groupName === '') {
                throw new InvalidArgumentException('catalog_group_name wajib diisi untuk membuat Category baru: '.$displayName);
            }
            $group = $this->resolveCatalogGroup($groupName, true);
        }
        $level = $parent ? ((int) $parent->level + 1) : 1;
        if ($level > 3) {
            throw new InvalidArgumentException('Kategori hanya boleh sampai level 3.');
        }
        $slug = Str::slug($displayName);
        $category = new CategoryModel();
        $category->fill([
            'catalog_group_id' => $group->id,
            'parent_id' => $parent?->id,
            'parent_scope_id' => $parent?->id ?: 0,
            'level' => $level,
            'sort_order' => 0,
            'is_active' => true,
            'is_visible_in_menu' => true,
            'name' => $displayName,
            'slug' => $slug,
            'full_slug' => $parent ? trim($parent->full_slug.'/'.$slug, '/') : $slug,
        ])->save();
        return $category;
    }

    private function categoryExists(string $name, string $groupName = ''): bool
    {
        $query = CategoryModel::query();
        if ($groupName !== '') {
            $group = $this->firstByNormalizedName(CatalogGroupModel::query(), 'name', $groupName);
            if (! $group) {
                return false;
            }
            $query->where('catalog_group_id', $group->id);
        }
        return $this->firstByNormalizedName($query, 'name', $name) !== null;
    }

    private function resolveImportModel(Request $request, Builder $query, array $row, string $column, string $value, string $label): Model
    {
        $mode = $this->importMode($request);
        $rawId = trim((string) ($row['id'] ?? ''));

        if ($mode === 'update') {
            if ($rawId !== '' && ctype_digit($rawId)) {
                $model = (clone $query)->find((int) $rawId);
                if (! $model) {
                    throw new InvalidArgumentException($label.' dengan ID '.$rawId.' tidak ditemukan atau tidak berada dalam akses pengguna.');
                }
                return $model;
            }

            $displayName = $this->cleanName($value);
            if ($displayName === '') {
                throw new InvalidArgumentException('Kolom id atau '.$column.' wajib diisi pada mode Import Update Data untuk '.$label.'.');
            }
            $matches = $this->allByNormalizedName(clone $query, $column, $displayName);
            if ($matches->isEmpty()) {
                throw new InvalidArgumentException($label.' dengan '.$column.' "'.$displayName.'" tidak ditemukan atau tidak berada dalam akses pengguna.');
            }
            if ($matches->count() > 1) {
                throw new InvalidArgumentException($column.' "'.$displayName.'" tidak unik untuk '.$label.'. Gunakan kolom id untuk mode Import Update Data.');
            }
            return $matches->first();
        }

        if ($rawId !== '') {
            throw new InvalidArgumentException('Kolom id harus kosong pada mode Import Data Baru.');
        }

        $matches = $this->allByNormalizedName(clone $query, $column, $value);
        if ($matches->isNotEmpty()) {
            throw new InvalidArgumentException($label.' sudah tersedia. Gunakan mode Import Update Data agar data lama tidak berubah tanpa sengaja: '.$value);
        }

        return $query->getModel()->newInstance();
    }

    private function resolveProductForImport(Request $request, int $storeId, array $row, string $name): ProductModel
    {
        $mode = $this->importMode($request);
        if ($mode === 'update') {
            $model = $this->resolveImportModel($request, ProductModel::query()->where('store_id', $storeId), $row, 'name', $name, 'Product');
            return $model instanceof ProductModel ? $model : new ProductModel();
        }

        $rawId = trim((string) ($row['id'] ?? ''));
        if ($rawId !== '') {
            throw new InvalidArgumentException('Kolom id harus kosong pada mode Import Data Baru.');
        }

        $key = $this->productImportKey($storeId, $name);
        if (isset($this->createdProductIds[$key])) {
            return ProductModel::query()->where('store_id', $storeId)->findOrFail($this->createdProductIds[$key]);
        }

        if ($this->firstByNormalizedName(ProductModel::query()->where('store_id', $storeId), 'name', $name)) {
            throw new InvalidArgumentException('Product sudah tersedia. Gunakan mode Import Update Data agar Product lama tidak ter-update tanpa sengaja: '.$name);
        }

        return new ProductModel();
    }

    private function productImportKey(int $storeId, string $name): string
    {
        return $storeId.'|'.$this->normalizedName($name);
    }

    private function importMode(Request $request): string
    {
        $mode = strtolower(trim((string) $request->attributes->get('import_mode', $request->input('import_mode', 'create'))));
        if (! in_array($mode, ['create', 'update'], true)) {
            throw new InvalidArgumentException('Jenis import tidak valid. Pilih Import Data Baru atau Import Update Data.');
        }
        return $mode;
    }

    private function assertImportRows(Request $request, string $module, array $rows): void
    {
        $mode = $this->importMode($request);
        $skuRows = [];
        $variantRows = [];
        $productRows = [];
        $productSignatures = [];
        $productFields = [
            'store_name', 'catalog_group_name', 'name', 'slug', 'description', 'brand',
            'primary_category_name', 'category_names', 'status', 'is_active',
            'thumbnail', 'image_url', 'image_alt',
        ];

        foreach ($rows as $item) {
            $rowNumber = (int) ($item['row_number'] ?? 0);
            $row = (array) ($item['data'] ?? []);
            $id = trim((string) ($row['id'] ?? ''));

            if ($mode === 'create' && $id !== '') {
                throw new InvalidArgumentException('Baris '.$rowNumber.': kolom id harus kosong pada mode Import Data Baru.');
            }
            if ($mode === 'update' && $id !== '' && ! ctype_digit($id)) {
                throw new InvalidArgumentException('Baris '.$rowNumber.': kolom id harus berisi angka pada mode Import Update Data.');
            }

            if ($module !== 'product') {
                continue;
            }

            $hasVariant = $this->hasAnyValue($row, ['sku', 'variant_name', 'price']);
            $productIdentity = $mode === 'update'
                ? 'id:'.$id
                : $this->normalizedName($row['store_name'] ?? '').'|'.$this->normalizedName($row['name'] ?? '');
            $signature = [];
            foreach ($productFields as $field) {
                $signature[$field] = $this->normalizedName($row[$field] ?? '');
            }
            $signature = json_encode($signature, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if (isset($productRows[$productIdentity])) {
                $first = $productRows[$productIdentity];
                if (! $hasVariant || ! $first['has_variant']) {
                    throw new InvalidArgumentException('Product kembar ditemukan pada baris '.$first['row'].' dan '.$rowNumber.'. Satu Product tanpa variant hanya boleh satu baris.');
                }
                if ($productSignatures[$productIdentity] !== $signature) {
                    throw new InvalidArgumentException('Data utama Product tidak konsisten pada baris '.$first['row'].' dan '.$rowNumber.'. Ulangi data utama yang sama untuk setiap baris variant.');
                }
            } else {
                $productRows[$productIdentity] = ['row' => $rowNumber, 'has_variant' => $hasVariant];
                $productSignatures[$productIdentity] = $signature;
            }

            $sku = $this->cleanName($row['sku'] ?? null);
            if ($sku !== '') {
                $skuKey = $this->normalizedName($sku);
                if (isset($skuRows[$skuKey])) {
                    throw new InvalidArgumentException('SKU kembar ditemukan pada file: '.$sku.' di baris '.$skuRows[$skuKey].' dan '.$rowNumber.'.');
                }
                $skuRows[$skuKey] = $rowNumber;
            }

            if ($hasVariant) {
                $variantIdentity = $sku !== ''
                    ? 'sku:'.$this->normalizedName($sku)
                    : 'name:'.$this->normalizedName($row['variant_name'] ?? 'Default');
                $variantKey = $productIdentity.'|'.$variantIdentity;
                if (isset($variantRows[$variantKey])) {
                    throw new InvalidArgumentException('Variant kembar ditemukan pada baris '.$variantRows[$variantKey].' dan '.$rowNumber.'. Bedakan SKU atau variant_name.');
                }
                $variantRows[$variantKey] = $rowNumber;
            }
        }
    }

    private function generateUniqueSku(int $storeId, string $productName, string $brand, string $variantName): string
    {
        $parts = array_filter([$productName, $brand, $variantName]);
        $base = Str::upper(Str::slug(implode('-', $parts)));
        $base = $base === '' ? 'PRODUCT' : Str::substr($base, 0, 40);
        $date = now()->format('ymd');
        $counter = 1;

        do {
            $sku = $base.'-'.$date.'-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $exists = ProductVariantModel::query()
                ->where('store_id', $storeId)
                ->whereRaw('LOWER(TRIM(sku)) = ?', [$this->normalizedName($sku)])
                ->exists();
            $counter++;
        } while ($exists);

        return $sku;
    }

    private function firstByNormalizedName(Builder $query, string $column, string $value): ?Model
    {
        return $this->allByNormalizedName($query, $column, $value)->first();
    }

    private function allByNormalizedName(Builder $query, string $column, string $value)
    {
        return $query->whereRaw('LOWER(TRIM('.$column.')) = ?', [$this->normalizedName($value)])->get();
    }

    private function cleanName(mixed $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }

    private function normalizedName(mixed $value): string
    {
        return Str::lower($this->cleanName($value));
    }

    private function splitNames(mixed $value): array
    {
        return array_values(array_filter(array_map(
            fn (string $name): string => $this->cleanName($name),
            preg_split('/\s*,\s*/u', (string) ($value ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: []
        )));
    }

    private function uniqueSlug(Builder $query, string $base, ?int $ignoreId = null): string
    {
        $root = trim($base) !== '' ? trim($base) : 'data';
        $slug = $root;
        $counter = 2;
        while ((clone $query)->when($ignoreId, fn (Builder $builder) => $builder->where($builder->getModel()->getQualifiedKeyName(), '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $root.'-'.$counter;
            $counter++;
        }
        return $slug;
    }

    private function exampleMetadata(array $row, int $number): array
    {
        $scenario = $this->cleanName($row['scenario'] ?? 'Kasus import standar.');
        $howTo = $this->cleanName($row['how_to'] ?? 'Isi data sesuai header Template Kosong dan aturan pada sheet Penjelasan Kolom.');
        $expected = $this->cleanName($row['expected'] ?? 'Data divalidasi dan diproses sesuai aturan modul.');
        $notes = $this->cleanName($row['notes'] ?? 'Gunakan nama relasi, bukan ID tabel lain.');
        $normalized = $this->normalizedName($scenario.' '.$howTo.' '.$expected.' '.$notes);
        $caseType = $this->cleanName($row['case_type'] ?? '');

        if ($caseType === '') {
            $caseType = match (true) {
                str_contains($normalized, 'variant') => 'Variant Product',
                str_contains($normalized, 'update') || str_contains($normalized, 'memperbarui') => 'Update Data',
                str_contains($normalized, 'gambar') || str_contains($normalized, 'image') => 'Gambar',
                str_contains($normalized, 'belum ada') || str_contains($normalized, 'relasi') || str_contains($normalized, 'target') => 'Relasi Antar Tabel',
                str_contains($normalized, 'nonaktif') || str_contains($normalized, 'draft') || str_contains($normalized, 'approved') => 'Status dan Publikasi',
                str_contains($normalized, 'ditolak') || str_contains($normalized, 'diblokir') || str_contains($normalized, 'salah') || str_contains($normalized, 'kosong') || str_contains($normalized, 'melebihi') => 'Validasi Gagal',
                str_contains($normalized, 'spasi') || str_contains($normalized, 'kapital') || str_contains($normalized, 'duplikat') => 'Normalisasi dan Duplikasi',
                default => 'Create Data',
            };
        }

        $normalizedType = $this->normalizedName($caseType);
        $validation = match (true) {
            str_contains($normalizedType, 'variant') => 'Sistem mencocokkan Product berdasarkan ID atau kombinasi toko dan nama, lalu mencocokkan variant berdasarkan SKU. SKU lama memperbarui variant, SKU baru menambah variant, dan hanya satu variant dipertahankan sebagai default.',
            str_contains($normalizedType, 'relasi') || str_contains($normalizedType, 'target category baru') => 'Sistem membersihkan nama relasi, mencocokkan LOWER(TRIM(name)) tanpa mengubah kapitalisasi yang tersimpan, memeriksa kemungkinan nama ambigu, lalu menempatkan relasi yang aman dibuat ke antrean konfirmasi.',
            str_contains($normalizedType, 'gambar') || str_contains($normalizedType, 'image') => 'Sistem memeriksa URL/path atau gambar embedded, format file, akses sumber, lalu menyimpan file ke storage Laravel sebelum data utama disimpan.',
            str_contains($normalizedType, 'update') => 'Sistem menggunakan ID milik modul ini hanya untuk menentukan data yang diperbarui. Semua relasi tetap dicocokkan melalui nama dan divalidasi ulang.',
            str_contains($normalizedType, 'status') || str_contains($normalizedType, 'nonaktif') || str_contains($normalizedType, 'approval') => 'Sistem menormalisasi nilai status/boolean, memastikan nilainya termasuk pilihan yang diizinkan, lalu menerapkan aturan visibilitas marketplace.',
            str_contains($normalizedType, 'validasi') || str_contains($normalizedType, 'gagal') => 'Sistem memeriksa field wajib, format, tanggal, level hierarki, nilai numerik, dan relasi. Baris tidak valid tidak disimpan dan dicatat ke file error.',
            str_contains($normalizedType, 'normalisasi') || str_contains($normalizedType, 'duplikasi') => 'Sistem menghapus spasi berlebih dan membandingkan nama secara case-insensitive. Nama tampilan asli tetap dipertahankan dan data yang sama tidak dibuat dua kali.',
            default => 'Sistem memeriksa header, field wajib, tipe data, keunikan, relasi berbasis nama, status, angka, dan gambar sebelum menjalankan transaksi penyimpanan.',
        };

        $action = $this->cleanName($row['user_action'] ?? '');
        if ($action === '') {
            $action = match (true) {
                str_contains($normalized, 'konfirmasi') || str_contains($normalized, 'lanjutkan') || str_contains($normalized, 'antrean') => 'Buka tab Antrean, periksa daftar relasi, lalu pilih Lanjutkan untuk membuat relasi dan mengimport atau Batal untuk menghentikan seluruh proses.',
                str_contains($normalized, 'ditolak') || str_contains($normalized, 'diblokir') || str_contains($normalized, 'file error') || str_contains($normalizedType, 'validasi') => 'Unduh file error otomatis, perbaiki kolom yang ditandai, lalu import ulang menggunakan Template Kosong.',
                default => 'Tidak ada tindakan tambahan. Periksa notifikasi Info untuk memastikan jumlah data yang berhasil diproses.',
            };
        }

        return [
            'no_kasus' => 'KASUS-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'jenis_kasus' => $caseType,
            'skenario' => $scenario,
            'cara_pengisian' => $howTo,
            'validasi_sistem' => $validation,
            'hasil_diharapkan' => $expected,
            'tindakan_pengguna' => $action,
            'catatan_penting' => $notes,
        ];
    }

    private function attachExportImage($sheet, array $row, array $config, int $rowNumber, int $previewColumn): void
    {
        $source = null;
        foreach ($config['image_fields'] as $field) {
            if (! empty($row[$field])) {
                $source = (string) $row[$field];
                break;
            }
        }
        if (! $source) {
            return;
        }

        $path = $this->resolveImageToLocalPath($source);
        if (! $path || ! is_file($path)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setPath($path);
        $drawing->setCoordinates(Coordinate::stringFromColumnIndex($previewColumn).$rowNumber);
        $drawing->setHeight(36);
        $drawing->setOffsetX(4);
        $drawing->setOffsetY(4);
        $drawing->setWorksheet($sheet);
        $sheet->getRowDimension($rowNumber)->setRowHeight(34);
    }

    private function extractEmbeddedImages($sheet, array $headers, array $config, string $module): array
    {
        $result = [];
        $imageFields = $config['image_fields'];
        if ($imageFields === []) {
            return $result;
        }

        foreach ($sheet->getDrawingCollection() as $drawing) {
            [$columnLetters, $rowNumber] = Coordinate::coordinateFromString($drawing->getCoordinates());
            $columnIndex = Coordinate::columnIndexFromString($columnLetters);
            $header = $headers[$columnIndex] ?? null;
            $field = in_array($header, $imageFields, true) ? $header : $imageFields[0];
            $extension = 'png';
            $bytes = null;

            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                $function = $drawing->getRenderingFunction();
                $function($drawing->getImageResource());
                $bytes = ob_get_clean();
                $extension = $drawing->getMimeType() === MemoryDrawing::MIMETYPE_JPEG ? 'jpg' : 'png';
            } elseif ($drawing instanceof Drawing) {
                $sourcePath = $drawing->getPath();
                if ($sourcePath) {
                    $content = @file_get_contents($sourcePath);
                    if ($content !== false) {
                        $bytes = $content;
                        $extension = pathinfo(parse_url($sourcePath, PHP_URL_PATH) ?: $sourcePath, PATHINFO_EXTENSION) ?: 'png';
                    }
                }
            }

            if (! $bytes) {
                continue;
            }

            $path = 'spreadsheet-import/'.$module.'/'.Str::uuid().'.'.strtolower($extension);
            Storage::disk('public')->put($path, $bytes);
            $result[(int) $rowNumber][$field] = $path;
        }

        return $result;
    }

    private function normalizeImportedImage(mixed $value, string $directory): ?string
    {
        $source = trim((string) ($value ?? ''));
        if ($source === '' || $this->isPlaceholderImageSource($source)) {
            return null;
        }

        if (str_starts_with($source, 'spreadsheet-import/') || str_starts_with($source, $directory.'/')) {
            return $source;
        }

        if (str_starts_with($source, '/storage/')) {
            return ltrim(Str::after($source, '/storage/'), '/');
        }

        if (preg_match('#^https?://#i', $source) !== 1) {
            return ltrim($source, '/');
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'image/*',
                'User-Agent' => 'MarketplaceSpreadsheetImporter/1.0',
            ])->timeout(20)->get($source);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'Gambar tidak dapat diakses. Periksa URL, koneksi internet, atau gunakan gambar yang ditempel langsung ke cell.'
            );
        }

        if (! $response->successful()) {
            throw new InvalidArgumentException(
                'Gambar tidak dapat diunduh. Server sumber mengembalikan status HTTP '.$response->status().'.'
            );
        }

        $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
        if (! str_starts_with($mime, 'image/')) {
            throw new InvalidArgumentException(
                'URL gambar tidak mengembalikan file gambar. Content-Type yang diterima: '.($mime !== '' ? $mime : 'tidak tersedia').'.'
            );
        }

        $body = $response->body();
        if ($body === '') {
            throw new InvalidArgumentException('File gambar dari URL kosong.');
        }

        if (strlen($body) > 10 * 1024 * 1024) {
            throw new InvalidArgumentException('Ukuran gambar dari URL melebihi batas 10 MB.');
        }

        $extension = match (true) {
            str_contains($mime, 'jpeg') => 'jpg',
            str_contains($mime, 'png') => 'png',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'gif') => 'gif',
            default => throw new InvalidArgumentException('Format gambar tidak didukung: '.$mime.'.'),
        };

        $path = $directory.'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $body);

        return $path;
    }

    private function resolveImageToLocalPath(string $source): ?string
    {
        $normalized = trim($source);
        if ($normalized === '' || $this->isPlaceholderImageSource($normalized)) {
            return null;
        }

        if (preg_match('#^https?://#i', $normalized) === 1) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'image/*',
                    'User-Agent' => 'MarketplaceSpreadsheetExporter/1.0',
                ])->timeout(10)->get($normalized);

                if (! $response->successful()) {
                    return null;
                }

                $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
                $extension = match (true) {
                    str_contains($mime, 'jpeg') => 'jpg',
                    str_contains($mime, 'png') => 'png',
                    str_contains($mime, 'webp') => 'webp',
                    str_contains($mime, 'gif') => 'gif',
                    default => null,
                };

                if (! $extension || $response->body() === '' || strlen($response->body()) > 10 * 1024 * 1024) {
                    return null;
                }

                $directory = storage_path('app/tmp');
                if (! is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                $path = $directory.'/'.Str::uuid().'.'.$extension;
                file_put_contents($path, $response->body());

                return $path;
            } catch (Throwable) {
                return null;
            }
        }

        $relative = str_starts_with($normalized, '/storage/')
            ? Str::after($normalized, '/storage/')
            : ltrim($normalized, '/');

        return Storage::disk('public')->exists($relative)
            ? Storage::disk('public')->path($relative)
            : null;
    }

    private function isPlaceholderImageSource(string $source): bool
    {
        $normalized = strtoupper(trim($source));
        if (in_array($normalized, [
            'TEMPEL_GAMBAR_ATAU_ISI_URL_VALID',
            'TEMPEL GAMBAR ATAU ISI URL VALID',
            'GANTI_DENGAN_URL_GAMBAR_VALID',
            'URL_GAMBAR_VALID',
        ], true)) {
            return true;
        }

        if (preg_match('#^https?://#i', $source) !== 1) {
            return false;
        }

        $host = strtolower((string) parse_url($source, PHP_URL_HOST));

        return in_array($host, [
            'example.com',
            'www.example.com',
            'example.org',
            'www.example.org',
            'example.net',
            'www.example.net',
        ], true);
    }


    private function findHeaderRow($sheet, array $allowedHeaders): int
    {
        $highestRow = min(30, max(1, $sheet->getHighestDataRow()));
        $bestRow = 0;
        $bestMatchCount = 0;

        for ($rowNumber = 1; $rowNumber <= $highestRow; $rowNumber++) {
            $headers = array_values($this->sheetHeaders($sheet, $rowNumber));
            $matchCount = count(array_intersect($headers, $allowedHeaders));
            if ($matchCount > $bestMatchCount) {
                $bestMatchCount = $matchCount;
                $bestRow = $rowNumber;
            }
        }

        if ($bestRow === 0 || $bestMatchCount === 0) {
            throw new InvalidArgumentException('Baris header Template Kosong tidak ditemukan. Jangan mengubah nama header hijau.');
        }

        return $bestRow;
    }

    private function sheetHeaders($sheet, int $rowNumber = 1): array
    {
        $headers = [];
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        for ($column = 1; $column <= $highestColumn; $column++) {
            $header = strtolower(trim((string) $sheet->getCell([$column, $rowNumber])->getValue()));
            if ($header !== '') {
                $headers[$column] = $header;
            }
        }
        return $headers;
    }

    private function assertHeaders(array $headers, array $allowed): void
    {
        $unknown = array_diff(array_values($headers), $allowed);
        if ($unknown !== []) {
            throw new InvalidArgumentException('Header Excel tidak dikenal: '.implode(', ', $unknown));
        }
    }

    private function readRow($sheet, array $headers, int $rowNumber): array
    {
        $row = [];
        foreach ($headers as $column => $header) {
            $value = $sheet->getCell([$column, $rowNumber])->getCalculatedValue();
            $row[$header] = $value instanceof DateTimeInterface ? $value->format('Y-m-d H:i:s') : $value;
        }
        return $row;
    }

    private function rowIsEmpty(array $row): bool
    {
        return collect($row)->every(fn (mixed $value): bool => trim((string) ($value ?? '')) === '');
    }

    private function writeHeader($sheet, array $headers, int $rowNumber = 1): void
    {
        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, $rowNumber], $header);
        }
    }

    private function styleSheetTitle($sheet, string $lastColumn, string $titleColor, bool $merged): void
    {
        $titleRange = $merged ? 'A1:'.$lastColumn.'1' : 'A1';
        $instructionRange = $merged ? 'A2:'.$lastColumn.'3' : 'A2:A3';

        $sheet->getStyle($titleRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($titleColor);
        $sheet->getStyle($titleRange)->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($titleRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($instructionRange)->getFont()->getColor()->setARGB('FF334155');
        $sheet->getStyle($instructionRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(18);
    }

    private function styleHeaderRow($sheet, int $columnCount, int $headerRow): void
    {
        $lastColumn = Coordinate::stringFromColumnIndex(max(1, $columnCount));
        $headerRange = 'A'.$headerRow.':'.$lastColumn.$headerRow;
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F766E');
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
        $sheet->getRowDimension($headerRow)->setRowHeight(20);
    }

    private function formatSheet($sheet, int $columnCount, int $headerRow = 1): void
    {
        $lastColumn = Coordinate::stringFromColumnIndex(max(1, $columnCount));
        $headerRange = 'A'.$headerRow.':'.$lastColumn.$headerRow;
        $sheet->setAutoFilter($headerRange);
        $this->styleHeaderRow($sheet, $columnCount, $headerRow);
        $sheet->getStyle('A'.$headerRow.':'.$lastColumn.max($headerRow, $sheet->getHighestRow()))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
        for ($column = 1; $column <= $columnCount; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(14);
        }
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename): BinaryFileResponse
    {
        $directory = sys_get_temp_dir();

        if ($directory === '' || ! is_dir($directory) || ! is_writable($directory)) {
            $directory = storage_path('framework/cache');

            if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
                throw new RuntimeException('Folder sementara untuk membuat file Excel tidak dapat dibuat.');
            }
        }

        $path = tempnam($directory, 'marketplace-xlsx-');

        if ($path === false) {
            throw new RuntimeException('File sementara untuk membuat template Excel tidak dapat dibuat.');
        }

        try {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($path);
            $spreadsheet->disconnectWorksheets();

            if (! is_file($path) || filesize($path) === 0) {
                throw new RuntimeException('Template Excel berhasil diproses tetapi file hasilnya kosong.');
            }
        } catch (Throwable $exception) {
            $spreadsheet->disconnectWorksheets();

            if (is_file($path)) {
                @unlink($path);
            }

            throw $exception;
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    private function activeRole(Request $request): string
    {
        return strtolower(trim((string) $request->attributes->get('active_role', 'admin')));
    }

    private function sellerStoreId(Request $request): int
    {
        $userId = (string) ($request->user()?->getAuthIdentifier() ?? '');
        $storeId = (int) ($request->attributes->get('seller_store_id') ?? 0);

        if ($storeId <= 0 && $userId !== '') {
            $storeId = (int) DB::table('stores')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->orderByDesc('is_active')
                ->orderBy('id')
                ->value('id');
        }

        if ($storeId <= 0) {
            throw new InvalidArgumentException('Akun seller belum terhubung dengan toko.');
        }

        $request->attributes->set('seller_store_id', $storeId);

        return $storeId;
    }

    private function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'ya', 'aktif', 'active'], true);
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text === '' ? null : $text;
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function dateValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '';
        }
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
        }
        return date('Y-m-d H:i:s', strtotime($text));
    }

    private function hasAnyValue(array $row, array $keys): bool
    {
        foreach ($keys as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return true;
            }
        }
        return false;
    }

    private function error(Throwable $exception): JsonResponse
    {
        report($exception);

        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $message = collect($errors)->flatten()->first() ?: 'Data yang dikirim tidak valid.';

            return response()->json([
                'success' => false,
                'message' => (string) $message,
                'errors' => $errors,
            ], 422);
        }

        if ($exception instanceof PostTooLargeException) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran request melebihi batas PHP. Atur upload_max_filesize minimal 25M dan post_max_size minimal 30M.',
            ], 413);
        }

        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : ($exception instanceof InvalidArgumentException ? 422 : 500);
        $message = trim($exception->getMessage());

        if ($message === '') {
            $message = 'Proses Excel gagal dijalankan oleh backend.';
        }

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
