<?php

namespace App\Contracts\Services;

interface ResultExportServiceInterface
{
    /**
     * Export questionnaire results to CSV format
     *
     * @param int $questionnaireId ID of the questionnaire to export results for
     * @param string $format Export format (csv, excel, pdf)
     * @param array $options Additional export options
     * @return string Path to the exported file
     * 
     * @throws \InvalidArgumentException If the questionnaire does not exist
     * @throws \RuntimeException If the export fails
     */
    public function exportResults(int $questionnaireId, string $format = 'csv', array $options = []): string;
    
    /**
     * Get the URL for downloading a previously exported file
     *
     * @param string $path Path to the exported file
     * @return string Download URL
     * 
     * @throws \InvalidArgumentException If the file does not exist
     */
    public function getDownloadUrl(string $path): string;
    
    /**
     * Clean up old export files
     *
     * @param int $olderThanHours Delete files older than this many hours
     * @return void
     */
    public function cleanupOldExports(int $olderThanHours = 24): void;
} 