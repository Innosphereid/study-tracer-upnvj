<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Questionnaire;
use Illuminate\Support\Collection;

/**
 * Class untuk menangani ekspor data kuesioner ke format Excel
 */
class QuestionnaireExport
{
    /**
     * @var Questionnaire
     */
    protected $questionnaire;

    /**
     * @var Collection
     */
    protected $responses;

    /**
     * @var array
     */
    protected $questionData;

    /**
     * @var array
     */
    protected $statistics;

    /**
     * Konstruktor
     *
     * @param Questionnaire $questionnaire
     * @param Collection $responses
     * @param array $questionData
     * @param array $statistics
     */
    public function __construct(
        Questionnaire $questionnaire,
        Collection $responses,
        array $questionData = [],
        array $statistics = []
    ) {
        $this->questionnaire = $questionnaire;
        $this->responses = $responses;
        $this->questionData = $questionData;
        $this->statistics = $statistics;
    }

    /**
     * Mengeksport data ke file Excel
     *
     * @param string $filePath Path file Excel yang akan dibuat
     * @return void
     */
    public function export(string $filePath): void
    {
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Informasi umum dan statistik
        $this->createSummarySheet($spreadsheet);
        
        // Sheet 2: Data respons lengkap
        $this->createResponsesSheet($spreadsheet);
        
        // Sheet untuk setiap tipe pertanyaan yang memiliki statistik
        $this->createQuestionSheets($spreadsheet);
        
        // Simpan spreadsheet ke file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    /**
     * Membuat sheet ringkasan kuesioner
     *
     * @param Spreadsheet $spreadsheet
     * @return void
     */
    protected function createSummarySheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');
        
        // Judul kuesioner
        $sheet->setCellValue('A1', 'Questionnaire Results');
        $sheet->setCellValue('A2', $this->questionnaire->title);
        $sheet->mergeCells('A2:D2');
        
        // Format judul
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        
        // Statistik dasar
        $sheet->setCellValue('A4', 'Statistics');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        
        $sheet->setCellValue('A5', 'Total Responses:');
        $sheet->setCellValue('B5', $this->statistics['total_responses'] ?? 0);
        
        $sheet->setCellValue('A6', 'Completion Rate:');
        $sheet->setCellValue('B6', ($this->statistics['completion_rate'] ?? 0) . '%');
        
        $sheet->setCellValue('A7', 'Average Completion Time:');
        $sheet->setCellValue('B7', $this->formatTime($this->statistics['average_time_seconds'] ?? 0));
        
        // Informasi kuesioner
        $sheet->setCellValue('A9', 'Questionnaire Information');
        $sheet->getStyle('A9')->getFont()->setBold(true);
        
        $sheet->setCellValue('A10', 'Created:');
        $sheet->setCellValue('B10', $this->questionnaire->created_at->format('Y-m-d H:i:s'));
        
        $sheet->setCellValue('A11', 'Status:');
        $sheet->setCellValue('B11', $this->questionnaire->status);
        
        $sheet->setCellValue('A12', 'Export Date:');
        $sheet->setCellValue('B12', now()->format('Y-m-d H:i:s'));
        
        // Auto-size kolom
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Membuat sheet dengan data respons detail
     *
     * @param Spreadsheet $spreadsheet
     * @return void
     */
    protected function createResponsesSheet(Spreadsheet $spreadsheet): void
    {
        // Buat sheet baru
        $responsesSheet = $spreadsheet->createSheet();
        $responsesSheet->setTitle('Responses');
        
        // HEADER TETAP - Kita definisikan secara eksplisit di awal
        $fixedHeaders = [
            'Response ID', 
            'Respondent', 
            'Created At', 
            'Completed At', 
            'IP Address'
        ];
        
        // Koleksi pertanyaan dan header-nya
        $questionHeaders = [];
        $questionColumns = [];
        
        // Kumpulkan semua pertanyaan terlebih dahulu dan abaikan section headers
        foreach ($this->questionnaire->sections as $section) {
            foreach ($section->questions as $question) {
                // Store the exact title as it should appear in the Excel header
                $questionHeaders[] = $question->title;
                
                // Store question data for later reference when filling in cell values
                $questionColumns[] = [
                    'id' => $question->id,
                    'title' => $question->title,
                    'type' => $question->question_type
                ];
            }
        }
        
        // Gabungkan semua header
        $allHeaders = array_merge($fixedHeaders, $questionHeaders);
        
        // Tulis semua header ke Excel
        $colIndex = 1; // Excel menggunakan 1-based indexing
        foreach ($allHeaders as $header) {
            $column = $this->getExcelColumn($colIndex);
            $responsesSheet->setCellValue($column.'1', $header);
            $colIndex++;
        }
        
        // Style header
        $headerRange = 'A1:' . $this->getExcelColumn(count($allHeaders)) . '1';
        $responsesSheet->getStyle($headerRange)->getFont()->setBold(true);
        $responsesSheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        
        // Tambahkan data respons
        $row = 2;
        foreach ($this->responses as $response) {
            // Kolom tetap - selalu posisi yang sama
            $responsesSheet->setCellValue('A'.$row, $response->id);
            $responsesSheet->setCellValue('B'.$row, $response->user ? $response->user->name : ($response->respondent_name ?? 'Anonymous'));
            $responsesSheet->setCellValue('C'.$row, $response->created_at->format('Y-m-d H:i:s'));
            $responsesSheet->setCellValue('D'.$row, $response->completed_at ? $response->completed_at->format('Y-m-d H:i:s') : 'Not completed');
            $responsesSheet->setCellValue('E'.$row, $response->ip_address);
            
            // Map question_id to answer for easy access
            $answerMap = [];
            foreach ($response->answerDetails as $detail) {
                $answerMap[$detail->question_id] = $detail;
            }
            
            // Kolom dinamis untuk pertanyaan - Gunakan indeks yang tepat!
            for ($i = 0; $i < count($questionColumns); $i++) {
                $question = $questionColumns[$i];
                $column = $this->getExcelColumn($i + count($fixedHeaders) + 1); // +1 karena Excel 1-based
                
                // Temukan jawaban untuk pertanyaan ini menggunakan answerMap
                $answer = $answerMap[$question['id']] ?? null;
                
                if ($answer) {
                    $value = $this->formatAnswerForExcel($answer->answer_value, $question['type']);
                    $responsesSheet->setCellValue($column.$row, $value);
                }
            }
            
            $row++;
        }
        
        // Auto-size kolom
        foreach (range('A', $this->getExcelColumn(count($allHeaders))) as $col) {
            $responsesSheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Membuat sheet untuk pertanyaan dengan statistik
     *
     * @param Spreadsheet $spreadsheet
     * @return void
     */
    protected function createQuestionSheets(Spreadsheet $spreadsheet): void
    {
        $sheetIndex = 3;
        
        foreach ($this->questionData as $questionId => $data) {
            if (empty($data['responses'])) {
                continue;
            }
            
            // Temukan pertanyaan berdasarkan ID
            $question = null;
            foreach ($this->questionnaire->sections as $section) {
                foreach ($section->questions as $q) {
                    if ($q->id == $questionId) {
                        $question = $q;
                        break 2;
                    }
                }
            }
            
            if (!$question) {
                continue;
            }
            
            // Buat sheet hanya untuk tipe pertanyaan yang memiliki statistik yang relevan
            if (in_array($question->question_type, ['yesno', 'multichoice', 'likert', 'numeric'])) {
                $sheetName = substr('Q' . $questionId, 0, 31); // Excel membatasi panjang nama sheet
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
                
                // Judul pertanyaan
                $sheet->setCellValue('A1', 'Question:');
                $sheet->setCellValue('B1', $question->title);
                $sheet->mergeCells('B1:D1');
                $sheet->getStyle('A1')->getFont()->setBold(true);
                
                // Tipe pertanyaan
                $sheet->setCellValue('A2', 'Type:');
                $sheet->setCellValue('B2', ucfirst($question->question_type));
                
                // Data statistik berdasarkan tipe pertanyaan
                switch ($question->question_type) {
                    case 'yesno':
                        $this->createYesNoStats($sheet, $data);
                        break;
                    
                    case 'multichoice':
                        $this->createMultichoiceStats($sheet, $data, $question);
                        break;
                    
                    case 'likert':
                        $this->createLikertStats($sheet, $data, $question);
                        break;
                    
                    case 'numeric':
                        $this->createNumericStats($sheet, $data);
                        break;
                }
                
                // Auto-size kolom
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                $sheetIndex++;
            }
        }
    }
    
    /**
     * Membuat statistik pertanyaan Ya/Tidak
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $data
     * @return void
     */
    protected function createYesNoStats($sheet, array $data): void
    {
        // Hitung jumlah jawaban Ya/Tidak
        $yesCount = 0;
        $noCount = 0;
        
        foreach ($data['responses'] as $response) {
            if (isset($response['value'])) {
                if ($response['value'] === true) {
                    $yesCount++;
                } else {
                    $noCount++;
                }
            }
        }
        
        $total = $yesCount + $noCount;
        
        // Tambahkan statistik
        $sheet->setCellValue('A4', 'Response');
        $sheet->setCellValue('B4', 'Count');
        $sheet->setCellValue('C4', 'Percentage');
        
        $sheet->setCellValue('A5', 'Yes');
        $sheet->setCellValue('B5', $yesCount);
        $sheet->setCellValue('C5', $total > 0 ? round(($yesCount / $total) * 100, 1) . '%' : '0%');
        
        $sheet->setCellValue('A6', 'No');
        $sheet->setCellValue('B6', $noCount);
        $sheet->setCellValue('C6', $total > 0 ? round(($noCount / $total) * 100, 1) . '%' : '0%');
        
        $sheet->setCellValue('A7', 'Total');
        $sheet->setCellValue('B7', $total);
        $sheet->setCellValue('C7', '100%');
        
        // Format table
        $sheet->getStyle('A4:C7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:C4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A7:C7')->getFont()->setBold(true);
    }
    
    /**
     * Membuat statistik pertanyaan multi-pilihan
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $data
     * @param object $question
     * @return void
     */
    protected function createMultichoiceStats($sheet, array $data, $question): void
    {
        // Options dari pertanyaan
        $options = json_decode($question->options, true);
        $optionCounts = [];
        
        // Inisialisasi counter untuk setiap option
        foreach ($options as $option) {
            $optionCounts[$option] = 0;
        }
        
        // Hitung jawaban per option
        $totalSelections = 0;
        foreach ($data['responses'] as $response) {
            if (isset($response['selected_options']) && is_array($response['selected_options'])) {
                foreach ($response['selected_options'] as $selected) {
                    if (isset($optionCounts[$selected])) {
                        $optionCounts[$selected]++;
                        $totalSelections++;
                    }
                }
            }
        }
        
        // Tambahkan statistik
        $sheet->setCellValue('A4', 'Option');
        $sheet->setCellValue('B4', 'Count');
        $sheet->setCellValue('C4', 'Percentage');
        
        $row = 5;
        foreach ($optionCounts as $option => $count) {
            $sheet->setCellValue('A'.$row, $option);
            $sheet->setCellValue('B'.$row, $count);
            $sheet->setCellValue('C'.$row, $totalSelections > 0 ? round(($count / $totalSelections) * 100, 1) . '%' : '0%');
            $row++;
        }
        
        $sheet->setCellValue('A'.$row, 'Total');
        $sheet->setCellValue('B'.$row, $totalSelections);
        $sheet->setCellValue('C'.$row, '100%');
        
        // Format table
        $sheet->getStyle('A4:C'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:C4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
    }
    
    /**
     * Membuat statistik pertanyaan skala Likert
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $data
     * @param object $question
     * @return void
     */
    protected function createLikertStats($sheet, array $data, $question): void
    {
        // Scale options dari pertanyaan
        $scaleOptions = json_decode($question->options, true);
        $scaleCounts = [];
        
        // Inisialisasi counter untuk setiap skala
        foreach (range(1, 5) as $scale) {
            $scaleCounts[$scale] = 0;
        }
        
        // Hitung jawaban per skala
        $totalResponses = count($data['responses']);
        foreach ($data['responses'] as $response) {
            if (isset($response['value']) && is_numeric($response['value'])) {
                $scale = (int)$response['value'];
                if (isset($scaleCounts[$scale])) {
                    $scaleCounts[$scale]++;
                }
            }
        }
        
        // Tambahkan statistik
        $sheet->setCellValue('A4', 'Scale');
        $sheet->setCellValue('B4', 'Label');
        $sheet->setCellValue('C4', 'Count');
        $sheet->setCellValue('D4', 'Percentage');
        
        $row = 5;
        foreach ($scaleCounts as $scale => $count) {
            $sheet->setCellValue('A'.$row, $scale);
            $sheet->setCellValue('B'.$row, $scaleOptions[$scale - 1] ?? "Scale $scale");
            $sheet->setCellValue('C'.$row, $count);
            $sheet->setCellValue('D'.$row, $totalResponses > 0 ? round(($count / $totalResponses) * 100, 1) . '%' : '0%');
            $row++;
        }
        
        $sheet->setCellValue('A'.$row, 'Total');
        $sheet->setCellValue('B'.$row, '');
        $sheet->setCellValue('C'.$row, $totalResponses);
        $sheet->setCellValue('D'.$row, '100%');
        
        // Format table
        $sheet->getStyle('A4:D'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:D4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
        
        // Tambahkan statistik rata-rata
        $sum = 0;
        $count = 0;
        foreach ($data['responses'] as $response) {
            if (isset($response['value']) && is_numeric($response['value'])) {
                $sum += (int)$response['value'];
                $count++;
            }
        }
        
        $average = $count > 0 ? round($sum / $count, 2) : 0;
        
        $row += 2;
        $sheet->setCellValue('A'.$row, 'Average Score:');
        $sheet->setCellValue('B'.$row, $average);
        $sheet->getStyle('A'.$row)->getFont()->setBold(true);
    }
    
    /**
     * Membuat statistik pertanyaan numerik
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $data
     * @return void
     */
    protected function createNumericStats($sheet, array $data): void
    {
        // Hitung statistik dasar
        $values = [];
        foreach ($data['responses'] as $response) {
            if (isset($response['value']) && is_numeric($response['value'])) {
                $values[] = (float)$response['value'];
            }
        }
        
        $count = count($values);
        $sum = array_sum($values);
        $average = $count > 0 ? $sum / $count : 0;
        
        // Temukan nilai min/max
        $min = $count > 0 ? min($values) : 0;
        $max = $count > 0 ? max($values) : 0;
        
        // Hitung median
        $median = 0;
        if ($count > 0) {
            sort($values);
            $mid = floor(($count - 1) / 2);
            if ($count % 2) {
                $median = $values[$mid];
            } else {
                $median = ($values[$mid] + $values[$mid + 1]) / 2;
            }
        }
        
        // Tambahkan statistik
        $sheet->setCellValue('A4', 'Statistic');
        $sheet->setCellValue('B4', 'Value');
        
        $sheet->setCellValue('A5', 'Count');
        $sheet->setCellValue('B5', $count);
        
        $sheet->setCellValue('A6', 'Sum');
        $sheet->setCellValue('B6', $sum);
        
        $sheet->setCellValue('A7', 'Average');
        $sheet->setCellValue('B7', round($average, 2));
        
        $sheet->setCellValue('A8', 'Median');
        $sheet->setCellValue('B8', round($median, 2));
        
        $sheet->setCellValue('A9', 'Minimum');
        $sheet->setCellValue('B9', $min);
        
        $sheet->setCellValue('A10', 'Maximum');
        $sheet->setCellValue('B10', $max);
        
        // Format table
        $sheet->getStyle('A4:B10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:B4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        
        // Tambahkan distribusi nilai jika ada data
        if ($count > 0) {
            $row = 12;
            $sheet->setCellValue('A'.$row, 'Distribution of Values');
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            
            $row++;
            $sheet->setCellValue('A'.$row, 'Value');
            $sheet->setCellValue('B'.$row, 'Count');
            
            // Kelompokkan nilai untuk distribusi
            $distribution = [];
            foreach ($values as $value) {
                if (!isset($distribution[$value])) {
                    $distribution[$value] = 0;
                }
                $distribution[$value]++;
            }
            
            // Urutkan berdasarkan nilai
            ksort($distribution);
            
            $row++;
            foreach ($distribution as $value => $count) {
                $sheet->setCellValue('A'.$row, $value);
                $sheet->setCellValue('B'.$row, $count);
                $row++;
            }
            
            // Format table
            $sheet->getStyle('A'.($row-count($distribution)-1).':B'.($row-1))
                  ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A'.($row-count($distribution)-1).':B'.($row-count($distribution)-1))
                  ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
        }
    }

    /**
     * Format nilai waktu dalam detik menjadi format manusia
     *
     * @param int $seconds
     * @return string
     */
    protected function formatTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($minutes < 60) {
            return $minutes . ' min ' . $remainingSeconds . ' sec';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $hours . ' hr ' . $remainingMinutes . ' min';
    }

    /**
     * Format nilai jawaban untuk Excel berdasarkan tipe pertanyaan
     *
     * @param mixed $value
     * @param string $questionType
     * @return string
     */
    protected function formatAnswerForExcel($value, string $questionType): string
    {
        if ($value === null) {
            return '';
        }
        
        // Jika nilai adalah string JSON, decode terlebih dahulu
        if (is_string($value) && (strpos($value, '[') === 0 || strpos($value, '{') === 0)) {
            try {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            } catch (\Exception $e) {
                // Jika gagal decode, gunakan nilai asli
            }
        }
        
        switch ($questionType) {
            case 'checkbox':
                // Handle array values
                if (is_array($value)) {
                    if (isset($value['checkboxResponses'])) {
                        // Matrix checkbox format
                        $formatted = [];
                        if (isset($value['rowLabels']) && isset($value['columnLabels'])) {
                            foreach ($value['checkboxResponses'] as $rowId => $columnIds) {
                                $rowLabel = $value['rowLabels'][$rowId] ?? 'Unknown Row';
                                $selections = [];
                                
                                foreach ($columnIds as $columnId) {
                                    $columnLabel = $value['columnLabels'][$columnId] ?? 'Unknown Column';
                                    $selections[] = $columnLabel;
                                }
                                
                                if (!empty($selections)) {
                                    $formatted[] = "{$rowLabel}: " . implode(', ', $selections);
                                }
                            }
                        }
                        return implode(' | ', $formatted);
                    } else {
                        // Regular checkbox format
                        return implode(', ', array_map(function($item) {
                            return is_string($item) ? trim($item, '"') : (string)$item;
                        }, $value));
                    }
                }
                return (string)$value;
                
            case 'radio':
            case 'radio_button':
                if (is_array($value)) {
                    // For radio with 'other' option
                    if (isset($value['value']) && isset($value['otherText'])) {
                        return $value['value'] === 'other' ? $value['otherText'] : $value['value'];
                    }
                    
                    // For matrix radio format
                    if (isset($value['responses'])) {
                        $formatted = [];
                        if (isset($value['rowLabels']) && isset($value['columnLabels'])) {
                            foreach ($value['responses'] as $rowId => $columnId) {
                                $rowLabel = $value['rowLabels'][$rowId] ?? 'Unknown Row';
                                $columnLabel = $value['columnLabels'][$columnId] ?? 'Unknown Column';
                                $formatted[] = "{$rowLabel}: {$columnLabel}";
                            }
                        }
                        return implode(' | ', $formatted);
                    }
                }
                return (string)$value;
                
            case 'multichoice':
                if (is_array($value)) {
                    return implode(', ', array_map(function($item) {
                        return is_string($item) ? trim($item, '"') : (string)$item;
                    }, $value));
                }
                return (string)$value;
            
            case 'file':
                if (is_array($value)) {
                    return implode(', ', array_map(function ($file) {
                        if (is_array($file)) {
                            return $file['name'] ?? $file['filename'] ?? 'Unknown file';
                        }
                        return (string)$file;
                    }, $value));
                }
                return (string)$value;
                
            case 'matrix':
                if (is_array($value)) {
                    $formatted = [];
                    
                    // Handle radio matrix
                    if (isset($value['responses']) && isset($value['rowLabels']) && isset($value['columnLabels'])) {
                        foreach ($value['responses'] as $rowId => $columnId) {
                            $rowLabel = $value['rowLabels'][$rowId] ?? 'Unknown Row';
                            $columnLabel = $value['columnLabels'][$columnId] ?? 'Unknown Column';
                            $formatted[] = "{$rowLabel}: {$columnLabel}";
                        }
                    }
                    
                    // Handle checkbox matrix
                    if (isset($value['checkboxResponses']) && isset($value['rowLabels']) && isset($value['columnLabels'])) {
                        foreach ($value['checkboxResponses'] as $rowId => $columnIds) {
                            $rowLabel = $value['rowLabels'][$rowId] ?? 'Unknown Row';
                            $selections = [];
                            
                            foreach ($columnIds as $columnId) {
                                $columnLabel = $value['columnLabels'][$columnId] ?? 'Unknown Column';
                                $selections[] = $columnLabel;
                            }
                            
                            if (!empty($selections)) {
                                $formatted[] = "{$rowLabel}: " . implode(', ', $selections);
                            }
                        }
                    }
                    
                    return implode(' | ', $formatted);
                }
                return (string)$value;
                
            case 'yesno':
                if (is_string($value) && strtolower($value) === 'true') {
                    return 'Yes';
                } elseif (is_string($value) && strtolower($value) === 'false') {
                    return 'No';
                }
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
                
            default:
                // For any other question type
                if (is_array($value)) {
                    // Check for formatted or humanReadable fields first
                    if (isset($value['formatted'])) {
                        return $value['formatted'];
                    } elseif (isset($value['humanReadable'])) {
                        if (is_array($value['humanReadable'])) {
                            return implode(' | ', $value['humanReadable']);
                        }
                        return (string)$value['humanReadable'];
                    }
                    
                    // Format JSON as last resort
                    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                return (string)$value;
        }
    }

    /**
     * Konversi nomor kolom menjadi referensi kolom Excel (A, B, C, ..., AA, AB, ...)
     *
     * @param int $columnNumber
     * @return string
     */
    protected function getExcelColumn(int $columnNumber): string
    {
        // Validasi input - Excel menggunakan angka >= 1
        if ($columnNumber <= 0) {
            return 'A'; // Default to A jika input tidak valid
        }
        
        $dividend = $columnNumber;
        $columnName = '';
        
        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $columnName = chr(65 + $modulo) . $columnName;
            $dividend = (int)(($dividend - $modulo) / 26);
        }
        
        return $columnName;
    }
} 